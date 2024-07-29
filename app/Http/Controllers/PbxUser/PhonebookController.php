<?php

namespace App\Http\Controllers\PbxUser;

use App\Constants\RolesDefault;
use App\Exports\PbxAdmin\PhonebooksExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PbxAdmin\CustomerSwitchController;
use App\Models\AdminCustomer;
use App\Models\Customer;
use App\Models\PbxAdmin\Phonebook;
use App\Models\PbxAdmin\Site;
use App\Models\UserPermissions\UserPermissionsPhonebook;
use App\Services\LoggerCustom;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PhonebookController extends Controller
{
	public LoggerCustom $logger;
	public CustomerSwitchController $customerSwitch;

	public function __construct()
	{
		$this->logger = new LoggerCustom();
		$this->customerSwitch = new CustomerSwitchController();
	}

	public function index()
	{
		$auth_user = Auth::user();
		$currentCustomerName = $this->customerSwitch->getCurrentCustomerName($auth_user->viewing_customer_id, $auth_user->reseller_id);
		$currentSiteName = $this->customerSwitch->getCurrentSiteName($auth_user->viewing_site_id);
		$currentSiteId = ($auth_user->viewing_site_id == null) ? 'all' : $auth_user->viewing_site_id ;
		$sites = $this->customerSwitch->getCustomerSites();

		if($auth_user->isUserLevelUser()){
			$phonebookPerms = UserPermissionsPhonebook::where('user_id', $auth_user->id)->get();
			if(count($phonebookPerms) < 1){
				return redirect()->back()->withErrorMsg('You have no Phonebooks assigned to you.');
			}
		}

		$title = $auth_user->isUserLevelUser() ? 'PBX User' : 'PBX User ' . $currentCustomerName . ' ' . $currentSiteName;

		return view('pbx-user.phonebooks.index', [
			'section'       		=> 'pbx-user',
			'title'         		=> $title,
			'subtitle'      		=> 'Phonebooks',
			'sites'         		=> $sites,
			'currentSiteId' 		=> $currentSiteId,
			'auth_user'				=> $auth_user,
		]);
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

		$auth_user = Auth::user();
		$currentCustomerId = $this->customerSwitch->getCurrentCustomerId(); // We don't need to account for user-level users here, customerId is used to get sites which a user-level cannot change.
		$currentCustomerName = $this->customerSwitch->getCurrentCustomerName($auth_user->viewing_customer_id, $auth_user->reseller_id);
		$currentSiteName = $this->customerSwitch->getCurrentSiteName($auth_user->viewing_site_id);
		$title = $auth_user->isUserLevelUser() ? 'PBX User' : 'PBX User ' . $currentCustomerName . ' ' . $currentSiteName;
		$sites = Site::where('customer_id', $currentCustomerId)->get();

		return view('pbx-user.phonebooks.edit', [
			'section'       => 'pbx-user',
			'title'         => $title,
			'phonebook' 	=> $phonebook,
			'sites'         => $sites,
		]);
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
		} elseif(current_user_is_reseller()){
			$auth_users_phonebooks = Phonebook::whereIn('site_id',
				Site::select('id')->whereIn('customer_id',
					Customer::select('id')->where('reseller_id', $auth_user->reseller_id)))->get();

			return $auth_users_phonebooks->contains($phonebook);
		} elseif($auth_user->roleMatches(RolesDefault::CUSTOMER_ADMIN_ID)) {
			$auth_users_phonebooks = Phonebook::whereIn('site_id',
				Site::select('id')->whereIn('customer_id',
					AdminCustomer::select('customer_id')->where('user_id', $auth_user->id)))->get();

			return $auth_users_phonebooks->contains($phonebook);
		} elseif($auth_user->isUserLevelUser()){
			$auth_users_phonebooks = Phonebook::whereIn('id',
				UserPermissionsPhonebook::select('phonebook_id')->where('user_id', $auth_user->id))->get();

			return $auth_users_phonebooks->contains($phonebook);
		}
		return false;
	}
}
