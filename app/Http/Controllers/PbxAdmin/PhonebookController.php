<?php

namespace App\Http\Controllers\PbxAdmin;

use App\Constants\RolesDefault;
use App\Exports\PbxAdmin\PhonebooksExport;
use App\Http\Controllers\Controller;
use App\Models\AdminCustomer;
use App\Models\Customer;
use App\Models\PbxAdmin\ExtensionVoicemailNotifyMultiple;
use App\Models\PbxAdmin\ExtensionVoicemailNotifyOnetime;
use App\Models\PbxAdmin\Phonebook;
use App\Models\PbxAdmin\PhonebookEntry;
use App\Models\PbxAdmin\Site;
use App\Services\BulkCreationService;
use App\Services\LoggerCustom;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PhonebookController extends Controller
{
	public LoggerCustom $logger;
	public CustomerSwitchController $customerSwitch;
	public ExtensionVerificationController $extensionVerification;
	public DependencyController $dependencyController;

	public function __construct()
	{
		$this->logger = new LoggerCustom();
		$this->customerSwitch = new CustomerSwitchController();
		$this->extensionVerification = new ExtensionVerificationController();
		$this->dependencyController = new DependencyController();
	}

	public function index(): View
	{
		$auth_user = Auth::user();
		$currentCustomerName = $this->customerSwitch->getCurrentCustomerName($auth_user->viewing_customer_id, $auth_user->reseller_id);
		$currentSiteName = $this->customerSwitch->getCurrentSiteName($auth_user->viewing_site_id);
		$currentSiteId = ($auth_user->viewing_site_id == null) ? 'all' : $auth_user->viewing_site_id ;
		$sites = $this->customerSwitch->getCustomerSites();

		return view('pbx-admin.phonebooks.index', [
			'section'       => 'pbx-admin',
			'title'         => 'PBX Admin ' . $currentCustomerName . $currentSiteName,
			'subtitle'      => 'Phonebooks',
			'sites'         => $sites,
			'currentSiteId' => $currentSiteId
		]);
	}

	public function store(Request $request): RedirectResponse
	{
		if($_FILES['csv_file']['tmp_name'] != ''){
			$data['site_id'] = $request['site_id'];
			$rules = [
				'site_id'	=> ['required', 'string', 'max:36'],
			];

		} else {
			$data['name']       = $request['name'];
			$data['site_id']    = $request['site_id'];

			$rules = [
				'name'      => ['required', 'string', 'max:64'],
				'site_id'   => ['required', 'string', 'max:36'],
			];
		}

		$validator = Validator::make($data, $rules);

		if($validator->fails())
		{
			return redirect()->back()->withErrorMsg($validator->messages()->first());
		}

		if($_FILES['csv_file']['tmp_name'] != '') {
			$bulkService = new BulkCreationService();
			$uploaded = $bulkService->createBulkPbxAdminEntityNoExtNumberFromCsv($_FILES['csv_file']['tmp_name'], $request['site_id'], 'phonebooks');
			if($uploaded){
				return redirect()->back()->withSuccessMsg('Phonebooks were created successfully!');
			} else {
				return redirect()->back()->withErrorMsg('Cannot create Phonebooks: an error occurred.');
			}
		}

		try {
			Phonebook::create([
				'name'      => $request['name'],
				'site_id'   => $request['site_id'],
			]);

			return redirect()->back()->withSuccessMsg('Phonebook was created successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot create Phonebook: an error occurred.');
		}
	}

	public function edit(Phonebook $phonebook): mixed
	{
		if(!$this->canAuthUserEditThisPhonebook($phonebook)){
			return redirect()->back()->withErrorMsg('You are not authorized to edit that Phonebook!');
		}

		$phonebookCustomerId = Site::select('customer_id')->where('id', $phonebook->site_id)->first();

		if(session('currentCustomerId') !== $phonebookCustomerId->customer_id ||
			session('currentSiteId') !== $phonebook->site_id){
			$this->customerSwitch->quickSwitch($phonebookCustomerId->customer_id, $phonebook->site_id);
		}

		$user = Auth::user();
		$currentCustomerId = $this->customerSwitch->getCurrentCustomerId();
		$currentCustomerName = $this->customerSwitch->getCurrentCustomerName($user->viewing_customer_id, $user->reseller_id);
		$sites = Site::where('customer_id', $currentCustomerId)->get();

		return view('pbx-admin.phonebooks.edit', [
			'section'       => 'pbx-admin',
			'title'         => 'PBX Admin ' . $currentCustomerName,
			'phonebook' 	=> $phonebook,
			'sites'         => $sites,
		]);
	}

	public function update(Request $request, Phonebook $phonebook): RedirectResponse
	{
		$data['name']           = $request['name'];
		$data['site']           = $request['site'];
		$data['notes']          = $request['notes'];
		$data['description']    = $request['description'];

		$rules = [
			'name'          => ['required', 'string', 'max:64'],
			'site'          => ['required', 'string', 'max:36'],
			'notes'         => ['nullable', 'string', 'max:1024'],
			'description'   => ['nullable', 'string', 'max:256'],
		];

		$validator = Validator::make($data, $rules);

		if ($validator->fails())
		{
			return redirect()->back()->withErrorMsg($validator->messages()->first());
		}

		try {
			$this->updatePhonebook($request, $phonebook);

			return redirect()->back()->withSuccessMsg('Phonebook was updated successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot create Phonebook: an error occurred.');
		}
	}

	private function updatePhonebook($request, Phonebook $phonebook): void
	{
		$phonebook->site_id              = $request['site'];
		$phonebook->name                 = $request['name'];
		$phonebook->description          = $request['description'];
		$phonebook->notes                = $request['notes'];

		$phonebook->save();
	}

	public function destroy(Phonebook $phonebook): RedirectResponse
	{
		$this->deleteNotifs($phonebook->id);

		try {
			$phonebook->delete();
			return redirect()->back()->withSuccessMsg('Phonebook was deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Phonebook: an error occurred.');
		}
	}

	public function restore(Phonebook $phonebook): RedirectResponse
	{
		try {
			$phonebook->restore();
			return redirect()->back()->withSuccessMsg('Phonebook was restored successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot restore Phonebook: an error occurred.');
		}
	}

	public function export(String $option): BinaryFileResponse
	{
		$export = Excel::download(new PhonebooksExport($option), 'phonebooks.csv', \Maatwebsite\Excel\Excel::CSV);
		ob_end_clean();

		return $export;
	}

	private function canAuthUserEditThisPhonebook(Phonebook $phonebook)
	{
		$auth_user = Auth::user();

		if ( current_user_is_super() ) {
			return true;
		}

		if(current_user_is_reseller()){
			$auth_users_phonebooks = Phonebook::whereIn('site_id',
				Site::select('id')->whereIn('customer_id',
					Customer::select('id')->where('reseller_id', $auth_user->reseller_id)->get()->toArray())
					->get()->toArray())->get();

			return $auth_users_phonebooks->contains($phonebook);
		}

		if($auth_user->roleMatches(RolesDefault::CUSTOMER_ADMIN_ID)) {
			$auth_users_phonebooks = Phonebook::whereIn('site_id',
				Site::select('id')->whereIn('customer_id',
					AdminCustomer::select('customer_id')->where('user_id', $auth_user->id)->get()->toArray())
					->get()->toArray())->get();

			return $auth_users_phonebooks->contains($phonebook);
		}
		return false;
	}

	private function deleteNotifs(string $phonebookId)
	{
		$entries = PhonebookEntry::select('id')->where('phonebook_id', $phonebookId)->get();

		foreach($entries as $entry){
			$entryId = $entry->id;
			ExtensionVoicemailNotifyOnetime::where('sms_pb_entry_id', $entryId)->delete();
			ExtensionVoicemailNotifyMultiple::where('sms_pb_entry_id', $entryId)
					->orWhere('voice_pb_entry_id', $entryId)->delete();
		}
	}
}
