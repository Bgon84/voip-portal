<?php

namespace App\Http\Controllers\PbxAdmin;

use App\Constants\Constants;
use App\Constants\RolesDefault;
use App\Exports\PbxAdmin\CallParkingLotsExport;
use App\HelperFunctions;
use App\Http\Controllers\Controller;
use App\Models\AdminCustomer;
use App\Models\Customer;
use App\Models\PbxAdmin\CallParkingLot;
use App\Models\PbxAdmin\MohAlbum;
use App\Models\PbxAdmin\Site;
use App\Models\PbxAdmin\Webhook;
use App\Services\BulkCreationService;
use App\Services\LoggerCustom;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CallParkingLotController extends Controller
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

        return view('pbx-admin.call-parking.index', [
            'section'       => 'pbx-admin',
            'title'         => 'PBX Admin ' . $currentCustomerName . $currentSiteName,
            'subtitle'      => 'Parking Lots',
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
			$uploaded = $bulkService->createBulkPbxAdminEntityFromCsv($_FILES['csv_file']['tmp_name'], $request['site_id'], 'call_parking_lots');
			if($uploaded){
				return redirect()->back()->withSuccessMsg('Parking Lots were created successfully!');
			} else {
				return redirect()->back()->withErrorMsg('Cannot create Parking Lots: an error occurred.');
			}
		}

        try {
            CallParkingLot::create([
                'name'      => $request['name'],
                'site_id'   => $request['site_id'],
            ]);

			return redirect()->back()->withSuccessMsg('Parking Lot was created successfully!');
        } catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot create Parking Lot: an error occurred.');
		}
    }

    public function edit(CallParkingLot $parkingLot): mixed
    {
        if(!$this->canAuthUserEditThisParkingLot($parkingLot)){
			return redirect()->back()->withErrorMsg('You are not authorized to edit that Parking Lot!');
		}

        $parkingLotCustomerId = Site::select('customer_id')->where('id', $parkingLot->site_id)->first();

        if(session('currentCustomerId') !== $parkingLotCustomerId->customer_id ||
            session('currentSiteId') !== $parkingLot->site_id){
            $this->customerSwitch->quickSwitch($parkingLotCustomerId->customer_id, $parkingLot->site_id);
        }

        $user = Auth::user();
		$currentCustomerId = $this->customerSwitch->getCurrentCustomerId();
        $currentCustomerName = $this->customerSwitch->getCurrentCustomerName($user->viewing_customer_id, $user->reseller_id);
        $sites = Site::where('customer_id', $currentCustomerId)->get();
		$siteIds = HelperFunctions::getSiteIdsArray($currentCustomerId);
        $albums = MohAlbum::select('id', 'name')->whereIn('site_id', $siteIds)->get();
        $webhooks = Webhook::select('id', 'site_id', 'name')->whereIn('site_id', $siteIds)->with('site')->get();

        return view('pbx-admin.call-parking.edit', [
            'section'       => 'pbx-admin',
            'title'         => 'PBX Admin ' . $currentCustomerName,
            'parkingLot'   	=> $parkingLot,
            'destinations'  => Constants::destinations,
            'albums'        => $albums,
            'webhooks'      => $webhooks,
            'sites'         => $sites,
        ]);
    }

    public function update(Request $request, CallParkingLot $parkingLot): RedirectResponse
    {
       $data['name']            = $request['name'];
       $data['site']            = $request['site'];
       $data['notes']           = $request['notes'];
       $data['description']     = $request['description'];
       $data['number_of_slots'] = $request['number_of_slots'];
       $data['timeout_seconds'] = $request['timeout_seconds'];
       $data['tags_cdr']        = $request['tags_cdr'];

        $rules = [
            'name'              => ['required', 'string', 'max:64'],
            'site'              => ['required', 'string'],
            'notes'             => ['nullable', 'string', 'max:1024'],
            'description'       => ['nullable', 'string', 'max:256'],
            'number_of_slots'   => ['numeric', 'min:0', 'max:200'],
            'timeout_seconds'   => ['numeric', 'min:0', 'max:1800'],
            'tags_cdr'          => ['nullable', 'string', 'max:256'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails())
        {
			return redirect()->back()->withErrorMsg($validator->messages()->first());
        }

        $message = 'Parking Lot updated successfully!';
        $alertType = 'success';
        $saveExtensionNumber = true;

        $requestedExtNum = $request['ext_number'] ?? '';
        $extCheck = json_decode($this->extensionVerification->checkExtNumber($request['site'], $requestedExtNum, $parkingLot->id, $request['number_of_slots']));
        if($extCheck->result == 'error'){
            $message = $extCheck->message;
            $alertType = 'error';
            $saveExtensionNumber = false;
        };

        try {
			$this->updateParkingLot($request, $parkingLot, $saveExtensionNumber);

			if($alertType != 'error'){
				return redirect()->back()->withSuccessMsg($message);
			} else {
				return redirect()->back()->withErrorMsg($message);
			}

        } catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot update Parking Lot : an error occurred.');
        }
    }

    private function updateParkingLot($request, CallParkingLot $parkingLot, bool $saveExtensionNumber): void
    {
        $parkingLot->name                       = $request['name'];
        $parkingLot->description                = $request['description'];
        $parkingLot->notes                      = $request['notes'];
        $parkingLot->timeout_seconds            = $request['timeout_seconds'];
        $parkingLot->dest_timeout_enum          = $request['destination'];
        $parkingLot->dest_timeout_id            = $request['dest_timeout_id'];
		$parkingLot->dest_overflow_enum         = $request['overflow_destination'];
        $parkingLot->dest_overflow_id           = $request['dest_overflow_id'];
        $parkingLot->tags_cdr                   = $request['tags_cdr'];
        $parkingLot->site_id                    = $request['site'];

        if($saveExtensionNumber){
            $parkingLot->ext_number         = $request['ext_number'];
            $parkingLot->number_of_slots    = $request['number_of_slots'];
        }

        if($request['moh_album_id'] !== "0"){
            $parkingLot->moh_album_id = $request['moh_album_id'];
        } else {
            $parkingLot->moh_album_id = null;
        }

        if($request['webhook'] !== "0"){
            $parkingLot->webhook_id = $request['webhook'];
        } else {
            $parkingLot->webhook_id = null;
        }

        $parkingLot->save();
    }

    public function destroy(CallParkingLot $parkingLot): RedirectResponse
    {
        try {
            $parkingLot->delete();
			return redirect()->back()->withSuccessMsg('Parking Lot was deleted successfully!');
        } catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Parking Lot: an error occurred.');
		}
    }

    public function restore(CallParkingLot $parkingLot): RedirectResponse
    {
        try {
            $parkingLot->restore();
			return redirect()->back()->withSuccessMsg('Parking Lot was restored successfully!');
        } catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot restore Parking Lot: an error occurred.');
		}
    }

    public function export(String $option): BinaryFileResponse
    {
		$export = Excel::download(new CallParkingLotsExport($option), 'parking-lots.csv', \Maatwebsite\Excel\Excel::CSV);
		ob_end_clean();

		return $export;
    }

    private function canAuthUserEditThisParkingLot(CallParkingLot $parkingLot):bool
    {
        $auth_user = request()->user();

        if ( current_user_is_super() ) {
            return true;
        }

        if(current_user_is_reseller()){
            $auth_users_parking_lots = CallParkingLot::whereIn('site_id',
                Site::select('id')->whereIn('customer_id',
                    Customer::select('id')->where('reseller_id', $auth_user->reseller_id)->get()->toArray())
                    ->get()->toArray())->get();

            return $auth_users_parking_lots->contains($parkingLot);
        }

        if($auth_user->roleMatches(RolesDefault::CUSTOMER_ADMIN_ID)) {
            $auth_users_parking_lots = CallParkingLot::whereIn('site_id',
                Site::select('id')->whereIn('customer_id',
                    AdminCustomer::select('customer_id')->where('user_id', $auth_user->id)->get()->toArray())
                    ->get()->toArray())->get();

            return $auth_users_parking_lots->contains($parkingLot);
        }
        return false;
    }
}
