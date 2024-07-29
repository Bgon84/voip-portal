<?php

namespace App\Http\Controllers\PbxUser;

use App\Constants\Constants;
use App\HelperFunctions;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PbxAdmin\CustomerSwitchController;
use App\Http\Controllers\UserPermissionsController;
use App\Models\PbxAdmin\Phonebook;
use App\Models\PhoneNumber\PhoneNumber;
use App\Services\AWSS3Service;
use App\Services\LoggerCustom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FaxController extends Controller
{
	public CustomerSwitchController $customerSwitch;
	public PhoneNumberSwitchController $phoneSwitch;
	public UserPermissionsController $userPermissionsController;
	public LoggerCustom $logger;

	public function __construct()
	{
		$this->customerSwitch = new CustomerSwitchController();
		$this->phoneSwitch = new PhoneNumberSwitchController();
		$this->userPermissionsController = new UserPermissionsController();
		$this->logger = new LoggerCustom();
	}

	public function index()
	{
		$auth_user = Auth::user();
		$currentCustomerName = $this->customerSwitch->getCurrentCustomerName($auth_user->viewing_customer_id, $auth_user->reseller_id);
		$currentSiteName = $this->customerSwitch->getCurrentSiteName($auth_user->viewing_site_id);
		$currentPhoneNumberId = $this->phoneSwitch->getCurrentPhoneNumberId("fax");

		if($currentPhoneNumberId == ''){
			return redirect()->back()->withErrorMsg('You do not have any Phone Numbers assigned to you.');
		}

		$currentPhone = $this->getCurrentPhone($currentPhoneNumberId);
		$needSubject = $currentPhone->cover_page_enabled == 0 ? 'false' : 'true';
		$canSendFax = $currentPhone->virtual == 0 ? 'true' : 'false';
		$title = $auth_user->isUserLevelUser() ? 'PBX User' : 'PBX User ' . $currentCustomerName . ' ' . $currentSiteName;
		$siteIds = HelperFunctions::getSiteIdsArray();

		$faxPerms = [];
		if($auth_user->isUserLevelUser()){
			$faxPerms = $this->userPermissionsController->getUsersExistingEfaxPerms($auth_user->id);
			$phonebookPerms = $this->userPermissionsController->getUsersExistingPhoneBookPerms($auth_user->id)['phonebook_ids'];
			$phonebooks = Phonebook::select('id', 'name')->whereIn('id', $phonebookPerms)->get();
		} else {
			$phonebooks = Phonebook::select('id', 'name')->whereIn('site_id', $siteIds)->get();
		}

		return view('pbx-user.faxes.index', [
			'section'       => 'pbx-user',
			'title'         => $title,
			'subtitle'      => 'Digital Faxing',
			'auth_user'     => $auth_user,
			'userPerms'     => $faxPerms,
			'phone'			=> $currentPhone,
			'phonebooks'	=> $phonebooks,
			'needSubject'	=> $needSubject,
			'canSendFax'	=> $canSendFax,
		]);
	}

	public function storeCoverPageSettings(Request $request): RedirectResponse
	{
		$data['company_name']   = $request['company_name'];
		$data['address_line_1']	= $request['address_line_1'];
		$data['address_line_2'] = $request['address_line_2'];
		$data['city']    		= $request['city'];
		$data['zip']       		= $request['zip'];
		$data['email']       	= $request['cover_page_email'];
		$data['website']       	= $request['cover_page_website'];

		$rules = [
			'company_name'      => ['nullable', 'string', 'max:128'],
			'address_line_1'    => ['nullable', 'string', 'max:128'],
			'address_line_2'	=> ['nullable', 'string', 'max:128'],
			'city'				=> ['nullable', 'string', 'max:128'],
			'zip'   			=> ['nullable', 'string', 'max:5'],
			'email'      		=> ['nullable', 'string', 'max:128'],
			'website'      		=> ['nullable', 'string', 'max:128'],
		];

		$validator = Validator::make($data, $rules);

		if ($validator->fails())
		{
			return redirect()->back()->withErrorMsg($validator->messages()->first());
		}

		try {
			DB::transaction(function () use ($request) {
				$phone = PhoneNumber::find($request['phone_number_id']);

				if($request['delete_cover_sheet'] == 1){
					if(AWSS3Service::delete($phone->cover_page_file_name, 'coversheets') == ''){
						DB::rollBack();
						return redirect()->back()->withErrorMsg('Cannot delete Coversheet: an error occurred.');
					}
					$phone->cover_page_file_name = null;
					$phone->save();
				}

				if(!is_null($request['efax_cover_page_file'])){
					$acceptedTypes = Constants::acceptedCoverSheetMimes;
					$mimeType = mime_content_type($_FILES['efax_cover_page_file']['tmp_name']);
					$fileName = str_replace('-', '', $phone->id) . '.pdf';

					if(!in_array($mimeType, $acceptedTypes)){
						DB::rollBack();
						return redirect()->back()->withErrorMsg('Fax Cover Sheet must be .PDF');
					}

					if(AWSS3Service::store($request['efax_cover_page_file'], $fileName, 'coversheets') == ''){
						DB::rollBack();
						return redirect()->back()->withErrorMsg('Cannot save Coversheet: an error occurred.');
					}
					$phone->cover_page_file_name = $fileName;
				}

				$phone->cover_page_dynamic_company_name 	= $request['company_name'];
				$phone->cover_page_dynamic_address_line_1 	= $request['address_line_1'];
				$phone->cover_page_dynamic_address_line_2 	= $request['address_line_2'];
				$phone->cover_page_dynamic_address_city 	= $request['city'];
				$phone->cover_page_dynamic_address_state 	= ($request['state'] != "0") ? $request['state'] : null;
				$phone->cover_page_dynamic_address_zip 		= $request['zip'];
				$phone->cover_page_dynamic_email 			= $request['cover_page_email'];
				$phone->cover_page_dynamic_website 			= $request['cover_page_website'];
				$phone->cover_page_enabled					= $request['cover_page_enabled'];

				$phone->save();
			}, 3);

			return redirect()->back()->withSuccessMsg('Cover Page settings were saved successfully');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot save Cover Page settings: an error occurred.');
		}
	}

	private function getCurrentPhone(string $currentPhoneNumberId)
	{
		return PhoneNumber::select([
			'id',
			'name',
			'phone_number',
			'virtual',
			'cover_page_file_name',
			'cover_page_enabled',
			'cover_page_dynamic_company_name',
			'cover_page_dynamic_address_line_1',
			'cover_page_dynamic_address_line_2',
			'cover_page_dynamic_address_city',
			'cover_page_dynamic_address_state',
			'cover_page_dynamic_address_zip',
			'cover_page_dynamic_email',
			'cover_page_dynamic_website'
		])->where('id', $currentPhoneNumberId)->first();
	}
}
