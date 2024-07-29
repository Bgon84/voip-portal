<?php

namespace App\Http\Controllers\PbxAdmin;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Models\PbxAdmin\MohAlbumTrack;
use App\Services\LoggerCustom;
use App\Services\AWSS3Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MohAlbumTrackController extends Controller
{
    public LoggerCustom $logger;
    public CustomerSwitchController $customerSwitch;

    public function __construct()
    {
        $this->logger = new LoggerCustom();
        $this->customerSwitch = new CustomerSwitchController();
    }

    public function store(Request $request): RedirectResponse
    {
        $data['album_id']   = $request['album_id'];
        $data['name']       = $request['name'];
        $data['file']       = $request['file'];

        $rules = [
            'album_id'  => ['required', 'string', 'max:36'],
            'name'      => ['required', 'string', 'max:128'],
            'file'      => ['required']
        ];

        $validator = Validator::make($data, $rules);

        if($validator->fails())
        {
			return redirect()->back()->withErrorMsg($validator->messages()->first());
        }

        // Validate audio file
        $acceptedTypes = Constants::acceptedAudioMimes;
        $mimeType = mime_content_type($_FILES['file']['tmp_name']);

        if(!in_array($mimeType, $acceptedTypes)){
			return redirect()->back()->withErrorMsg('Track must be .wav or .mp3!');
		}

        try {
            DB::beginTransaction();

            $newTrack = MohAlbumTrack::create([
                'name'          => $request['name'],
                'moh_album_id'  => $request['album_id'],
                'file_name'     => ''
            ]);

            $fileName = $newTrack->file_name = str_replace('-', '', $newTrack->id) . '.wav';
            $newTrack->save();

            if(AWSS3Service::store($request['file'], $fileName, Constants::s3MusicOnHoldFolder) == ''){
                DB::rollBack();
				return redirect()->back()->withErrorMsg('Cannot add track: an error occurred.');
			}

            DB::commit();
			return redirect()->back()->withSuccessMsg('Track was added successfully!');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot add track: an error occurred.');
        }
    }

    public function update(Request $request): RedirectResponse
    {
        $data['track_id']   = $request['track_id'];
        $data['name']       = $request['name'];

        $rules = [
            'track_id'  => ['required', 'string', 'max:36'],
            'name'      => ['required', 'string', 'max:128'],
        ];

        $validator = Validator::make($data, $rules);

        if($validator->fails())
        {
			return redirect()->back()->withErrorMsg($validator->messages()->first());
        }

        $track = MohAlbumTrack::where('id', $request['track_id'])->first();

        $message = 'Track updated successfully!';
        $alertType = 'success';

        // Validate and save audio file
        if(isset($request['editFile'])){
            $acceptedTypes = Constants::acceptedAudioMimes;
            $mimeType = mime_content_type($_FILES['editFile']['tmp_name']);
            $fileName = str_replace('-', '', $track->id) . '.wav';

            if(!in_array($mimeType, $acceptedTypes)){
                $message = 'File must be .wav or .mp3';
                $alertType = 'error';
            }

            if($alertType != 'error'){
                if(AWSS3Service::store($request['editFile'], $fileName, Constants::s3MusicOnHoldFolder) == ''){
                    $message = 'File failed to save.';
                    $alertType = 'error';
                }
            }
        } else {
            $fileName = $track->file_name;
        }

        try {
			$track->name = $request['name'];
			$track->file_name = $fileName;

			$track->save();

			if($alertType != 'error'){
				return redirect()->back()->withSuccessMsg($message);
			} else {
				return redirect()->back()->withErrorMsg($message);
			}
        } catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot update Track: an error occurred.');
        }
    }

    public function destroy(MohAlbumTrack $track): RedirectResponse
    {
        try {
            AWSS3Service::delete($track->file_name, Constants::s3MusicOnHoldFolder);

            $track->delete();

			return redirect()->back()->withSuccessMsg('Track was deleted successfully!');
        } catch (\Throwable $th) {
            $this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Track: an error occurred.');
        }
    }

    public function getFile(Request $request)
    {
        if($request->file == '' || is_null($request->file)){
            return false;
        }
        return AWSS3Service::get($request->file, Constants::s3MusicOnHoldFolder);
    }
}
