<?php

namespace App\Http\Controllers\PbxAdmin;

use App\Constants\RolesDefault;
use App\Exports\PbxAdmin\MohAlbumsExport;
use App\Http\Controllers\Controller;
use App\Models\AdminCustomer;
use App\Models\Customer;
use App\Models\PbxAdmin\MohAlbum;
use App\Models\PbxAdmin\MohAlbumTrack;
use App\Models\PbxAdmin\Site;
use App\Services\BulkCreationService;
use App\Services\LoggerCustom;
use App\Services\AWSS3Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MohAlbumController extends Controller
{
    public LoggerCustom $logger;
    public CustomerSwitchController $customerSwitch;
	public DependencyController $dependencyController;

    public function __construct()
    {
        $this->logger = new LoggerCustom();
        $this->customerSwitch = new CustomerSwitchController();
		$this->dependencyController = new DependencyController();
    }
    public function index(): View
    {
        $auth_user = Auth::user();
        $currentCustomerName = $this->customerSwitch->getCurrentCustomerName($auth_user->viewing_customer_id, $auth_user->reseller_id);
        $currentSiteName = $this->customerSwitch->getCurrentSiteName($auth_user->viewing_site_id);
        $currentSiteId = ($auth_user->viewing_site_id == null) ? 'all' : $auth_user->viewing_site_id ;
        $sites = $this->customerSwitch->getCustomerSites();

        return view('pbx-admin.music-on-hold.index', [
            'section'       => 'pbx-admin',
            'title'         => 'PBX Admin ' . $currentCustomerName . $currentSiteName,
            'subtitle'      => 'Music on Hold',
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
			// send file to be processed
			$bulkService = new BulkCreationService();
			$uploaded = $bulkService->createBulkMusicOnHoldAlbumFromCsv($_FILES['csv_file']['tmp_name'], $request['site_id']);
			if($uploaded){
				return redirect()->back()->withSuccessMsg('Albums was created successfully!');
			} else {
				return redirect()->back()->withErrorMsg('Cannot create Albums: an error occurred.');
			}
		}

        try {
            MohAlbum::create([
                'name'      => $request['name'],
                'site_id'   => $request['site_id'],
            ]);

			return redirect()->back()->withSuccessMsg('Album was created successfully!');
		} catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot create Album: an error occurred.');
        }
    }

    public function edit(MohAlbum $album): mixed
    {
        if(!$this->canAuthUserEditThisAlbum($album)){
			return redirect()->back()->withErrorMsg('You are not authorized to edit that Album!');
		}

        $albumCustomerId = Site::select('customer_id')->where('id', $album->site_id)->first();

        if(session('currentCustomerId') !== $albumCustomerId->customer_id ||
            session('currentSiteId') !== $album->site_id){
            $this->customerSwitch->quickSwitch($albumCustomerId->customer_id, $album->site_id);
        }

        $user = Auth::user();
		$currentCustomerId= $this->customerSwitch->getCurrentCustomerId();
        $currentCustomerName = $this->customerSwitch->getCurrentCustomerName($user->viewing_customer_id, $user->reseller_id);
        $sites = Site::where('customer_id', $currentCustomerId)->get();
		$dependencies = $this->dependencyController->getDependencies($album->id, 'moh_albums');

        return view('pbx-admin.music-on-hold.edit', [
            'section'       => 'pbx-admin',
            'title'         => 'PBX Admin ' . $currentCustomerName,
            'album'        	=> $album,
            'sites'         => $sites,
			'dependencies' 	=> $dependencies,
        ]);
    }

    public function update(Request $request, MohAlbum $album): RedirectResponse
    {
       $data['name']        	= $request['name'];
       $data['description'] 	= $request['description'];
       $data['notes']       	= $request['notes'];
       $data['streaming_url']	= $request['streaming_url'];

        $rules = [
            'name'          => ['required', 'string', 'max:64'],
            'description'   => ['nullable', 'string', 'max:256'],
            'notes'         => ['nullable', 'string', 'max:1024'],
            'streaming_url'	=> ['nullable', 'string', 'max:2048'],
        ];

        $validator = Validator::make($data, $rules);

        if($validator->fails())
        {
			return redirect()->back()->withErrorMsg($validator->messages()->first());
        }

        try {
			$this->updateAlbum($request, $album);

			return redirect()->back()->withSuccessMsg('Album was updated successfully!');
		} catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot update Album: an error occurred.');
        }
    }

    private function updateAlbum($request, MohAlbum $album): void
    {
        $album->name            = $request['name'];
        $album->description     = $request['description'];
        $album->notes           = $request['notes'];
        $album->site_id         = $request['site_id'];
        $album->streaming_url   = $request['streaming_url'];

        $album->save();
    }

    public function destroy(MohAlbum $album): RedirectResponse
    {
        try {
            $tracks = MohAlbumTrack::where('moh_album_id', $album->id)->get();

            foreach ($tracks as $track) {
                AWSS3Service::delete($track->file_name, 'musiconhold');
            }

            $album->delete();

			return redirect()->back()->withSuccessMsg('Album was deleted successfully!');
        } catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Album: an error occurred.');
        }
    }

    public function export(String $option): BinaryFileResponse
    {
		$export = Excel::download(new MohAlbumsExport($option), 'music-on-hold.csv', \Maatwebsite\Excel\Excel::CSV);
		ob_end_clean();

		return $export;
    }

    private function canAuthUserEditThisAlbum(MohAlbum $album)
    {
        $auth_user = Auth::user();

        if ( current_user_is_super() ) {
            return true;
        }

        if(current_user_is_reseller()){
            $auth_users_webhooks = MohAlbum::whereIn('site_id',
                                        Site::select('id')->whereIn('customer_id',
                                            Customer::select('id')->where('reseller_id', $auth_user->reseller_id)->get()->toArray())
                                            ->get()->toArray())->get();

            return $auth_users_webhooks->contains($album);
        }

        if($auth_user->roleMatches(RolesDefault::CUSTOMER_ADMIN_ID)) {
            $auth_users_webhooks = MohAlbum::whereIn('site_id',
                                        Site::select('id')->whereIn('customer_id',
                                            AdminCustomer::select('customer_id')->where('user_id', $auth_user->id)->get()->toArray())
                                            ->get()->toArray())->get();

            return $auth_users_webhooks->contains($album);
        }
        return false;
    }
}
