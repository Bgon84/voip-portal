<?php

namespace App\Http\Controllers\PbxUser;

use App\Constants\Constants;
use App\Exports\PbxUser\FaxInboxExport;
use App\HelperFunctions;
use App\Http\Controllers\Controller;
use App\Models\PbxUser\FaxIncoming;
use App\Models\PhoneNumber\PhoneNumber;
use App\Services\AWSS3Service;
use App\Services\LoggerCustom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FaxIncomingController extends Controller
{
	public LoggerCustom $logger;

	public function __construct()
	{
		$this->logger = new LoggerCustom();
	}

	public function updateStatus(string $id = '', string $markAs = ''): false|string
	{
		$messageId = $_GET['message_id'] ?? $id;

		$message = FaxIncoming::where('id', $messageId)->first();

		try {
			$message->datetime_read = (is_null($message->datetime_read) || $markAs === 'read') ? now() : null;
			$message->save();

			return json_encode([
				'message' => 'Fax status was updated successfully!',
				'result' => 'success'
			]);
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return json_encode([
				'message' => 'Cannot update Fax status: an error occurred.',
				'result' => 'error'
			]);
		}
	}

	public function updateStatusBulk(): false|string
	{
		$faxIds = $_GET['fax_ids'];
		$status = $_GET['status'] == 'read' ? now() : null;

		try {
			foreach($faxIds as $id){
				FaxIncoming::where('id', $id)->update(['datetime_read' => $status]);
			}

			return json_encode([
				'message' => 'Fax statuses were updated successfully!',
				'result' => 'success'
			]);
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return json_encode([
				'message' => 'Cannot update Fax statuses: an error occurred.',
				'result' => 'error'
			]);
		}
	}

	public function destroy(FaxIncoming $message): RedirectResponse
	{
		try {
			$message->flagged = 0;
			$message->save();

			$message->delete();

			return redirect()->back()->withSuccessMsg('Fax was deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Fax: an error occurred.');
		}
	}

	public function bulkDestroy(Request $request)
	{
		$ids = explode(',', $request['bulk_delete_fax_ids']);
		try {
			foreach($ids as $id){
				FaxIncoming::where('id', $id)->update(['deleted_at' => now(), 'flagged' => 0]);
			}

			return redirect()->back()->withSuccessMsg('Faxes were deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Faxes: an error occurred.');
		}
	}

	public function permDestroy(string $messageId): RedirectResponse
	{
		try {
			$message = FaxIncoming::withTrashed()->where('id', $messageId)->first();
			AWSS3Service::delete($message->file_name, Constants::s3FaxFileFolder .'/inbox');

			$message->forceDelete();

			return redirect()->back()->withSuccessMsg('Fax was permanently deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot permanently delete Fax: an error occurred.');
		}
	}

	public function permDestroyAll(PhoneNumber $phone): RedirectResponse
	{
		try {
			$faxes = FaxIncoming::onlyTrashed()->where('phone_number_id', $phone->id)->get();

			foreach($faxes as $fax){
				AWSS3Service::delete($fax->file_name, 'efax/inbox');
				$fax->forceDelete();
			}

			return redirect()->back()->withSuccessMsg('Faxes was permanently deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot permanently delete Faxes: an error occurred.');
		}
	}

	public function restore(FaxIncoming $message): RedirectResponse
	{
		try {
			$message->restore();

			return redirect()->back()->withSuccessMsg('Fax was restored successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot restore Fax: an error occurred.');
		}
	}

	public function restoreAll(PhoneNumber $phone): RedirectResponse
	{
		$faxes = FaxIncoming::onlyTrashed()->where('phone_number_id', $phone->id)->get();

		try {
			foreach($faxes as $fax){
				$fax->restore();
			}

			return redirect()->back()->withSuccessMsg('Faxes was restored successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot restore Faxes: an error occurred.');
		}
	}

	public function getFile(Request $request)
	{

		if($request->file == '' || is_null($request->file)){
			return redirect()->back()->withErrorMsg('Cannot retrieve File: an error occurred.');
		}

		$this->updateStatus($request->fax, 'read');

		return AWSS3Service::get($request->file, Constants::s3FaxFileFolder. '/inbox');
	}

	public function justGetFile(Request $request)
	{
		if($request->file == '' || is_null($request->file)){
			return response(['message' => 'No file found.'], 404);
		}
		return AWSS3Service::get($request->file, Constants::s3FaxFileFolder. '/inbox');
	}

	/**
	 * @throws \Exception
	 */
	public function bulkDownload(Request $request)
	{
		if(is_null($request['fax_ids'])){
			return redirect()->back()->withErrorMsg('You must select faxes to be downloaded.');
		}

		if (!file_exists('storage/temp/')) {
			mkdir('storage/temp/', 0777, true);
		}

		if (!file_exists('storage/temp/zips/')) {
			mkdir('storage/temp/zips/', 0777, true);
		}

		if (!file_exists('storage/temp/pdfs/')) {
			mkdir('storage/temp/pdfs/', 0777, true);
		}

		$faxIds = explode(',', $request['fax_ids']);
		$phone = PhoneNumber::select('phone_number')->where('id','=', $request['phone_id'])->first();
		$downloadName =  $phone->phone_number  . '_' . date('Y_m_d') . '_Fax_inbox.zip';
		$zipFilePath = 'storage/temp/zips/' . $downloadName;
		$filesToDelete = [];

		$zip = new \ZipArchive();

		if($zip->open(public_path($zipFilePath), \ZipArchive::CREATE)){

			foreach($faxIds as $id){
				$fax = FaxIncoming::find($id);

				if(HelperFunctions::calcFaxExpiry($fax->date_received) == 'expired'){
					break;
				}

				$this->updateStatus($id, 'read');
				$file = Storage::disk('s3')->get('efax/inbox/' . $fax->file_name);

				if(is_null($file)){
					$faxLabel = $fax->callerid_name . "'s fax received on " . date('Y-m-d', strtotime($fax->date_received));

					$zip->close();
					$this->deletePdfs($filesToDelete);
					Storage::disk('public')->delete('temp/zips/' . $downloadName);

					return redirect()->back()->with([
						'message' => 'File for ' . $faxLabel . ' could not be found. Bulk Download cancelled.',
						'alert-type' => 'error'
					]);
				}

				$identifier = str_replace(' ', '_', ucwords($fax->callerid_name)) . '_'
					. date('Ymd_His', strtotime($fax->date_received)) . '.pdf';
				$path = 'temp/pdfs/' . $identifier;
				Storage::disk('public')->put($path, $file, 'public');
				$filesToDelete[] = $path;

				$zip->addFile('storage/' . $path, basename($path));
			}
			$zip->close();
		}
		$this->deletePdfs($filesToDelete);

		return response()->download(public_path($zipFilePath))->deleteFileAfterSend(true);
	}

	public function flagFax(): false|string
	{
		$faxId = $_GET['fax_id'];
		$flag = $_GET['flag'];

		$fax = FaxIncoming::find($faxId);

		if(HelperFunctions::calcFaxExpiry($fax->date_received) == 'expired'){
			return json_encode([
				'message' => 'Fax is expired and cannot be flagged!',
				'result' => 'error'
			]);
		}

		try {
			$fax->flagged = ($flag !== 'unflag') ? 1 : 0;
			$fax->save();

			return json_encode([
				'message' => 'Fax was flagged successfully!',
				'result' => 'success'
			]);
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return json_encode([
				'message' => 'Cannot flag Fax: an error occurred.',
				'result' => 'error'
			]);
		}
	}

	public function export(Request $request): BinaryFileResponse
	{
		$phone = PhoneNumber::find($request['export_phone_id']);
		$name = $phone->phone_number . '_' . now() . '_fax_inbox.csv';
		$export = Excel::download(new FaxInboxExport($request['export_ids']), $name, \Maatwebsite\Excel\Excel::CSV);
		ob_end_clean();

		return $export;
	}

	private function deletePdfs(array $filesToDelete): void
	{
		foreach($filesToDelete as $file){
			Storage::disk('public')->delete($file);
		}
	}

}
