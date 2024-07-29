<?php

namespace App\Http\Controllers\PbxUser;

use App\Constants\Constants;
use App\Exports\PbxUser\FaxOutboxExport;
use App\Http\Controllers\Controller;
use App\Models\PbxAdmin\PhonebookEntry;
use App\Models\PbxUser\FaxOutgoing;
use App\Models\PhoneNumber\PhoneNumber;
use App\Services\AWSS3Service;
use App\Services\LoggerCustom;
use Barryvdh\DomPDF\Facade\Pdf;
use Clegginabox\PDFMerger\PDFMerger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FaxOutgoingController extends Controller
{
	public LoggerCustom $logger;

	public function __construct()
	{
		$this->logger = new LoggerCustom();
	}

	/**
	 * @throws \Exception
	 */
	public function store(Request $request): RedirectResponse
	{
		$data['phone_number'] 		= $request['fax_to_phone_number'];
		$data['phone_number_id']    = $request['phone_number_id'];
		$data['file']    			= $request['fax_file'];
		$data['subject']    		= $request['cover_page_dynamic_subject'];

		$rules = [
			'phone_number'  	=> ['required', 'string', 'max:20'],
			'phone_number_id'  	=> ['required', 'string', 'max:36'],
			'file'   			=> ['required'],
			'subject'  			=> ['nullable', 'string', 'max:128'],
		];

		$validator = Validator::make($data, $rules);

		if($validator->fails())
		{
			return redirect()->back()->withErrorMsg($validator->messages()->first());
		}

		$message = 'Fax was created successfully.';
		$alertType = 'success';
		$staticPath = $dynamicPath = '';
		$destNumber = $request['fax_to_phone_number'];

		if(!is_null($request['fax_file'])) {

			$acceptedTypes = Constants::acceptedFaxFileMimes;
			$count = count($_FILES['fax_file']['name']);
			$phoneNumberId = $request['phone_number_id'];
			$subject = $request['cover_page_dynamic_subject'] ?? '';
			$pdf = new PDFMerger;

			$coverSheetData = $this->getCoverSheetData($phoneNumberId);
			$phone = $coverSheetData['phone'];
			$hasDynamic = $coverSheetData['hasDynamic'];
			$hasStatic = $coverSheetData['hasStatic'];

			if($hasDynamic){
				$dynamicPath = $this->createDynamicCoverSheet($phone, $destNumber, $subject);
				$pdf->addPDF($dynamicPath);
			}

			if($hasStatic){
				$filename = $phone->cover_page_file_name;
				$staticFile = AWSS3Service::get($filename, Constants::s3FaxCoverSheetFolder);
				$staticPath = 'temp/pdfs/' . $filename;
				Storage::disk('public')->put($staticPath, $staticFile, 'public');

				$pdf->addPDF('storage/' . $staticPath);
			}

			for($i = 0; $i < $count; $i++) {
				$mimeType = mime_content_type($_FILES['fax_file']['tmp_name'][$i]);

				if (!in_array($mimeType, $acceptedTypes)) {
					$message = 'Fax files must be .PDF';
					$alertType = 'error';
					break;
				}

				$pdf->addPDF($_FILES['fax_file']['tmp_name'][$i]);
			}

			$path = 'storage/temp/pdfs/' . uniqid() . '.pdf';
			$pdf->merge('file', $path);

		} else {
			return redirect()->back()->withErrorMsg('Cannot create Fax: an error occurred.');
		}

		try {
			if($alertType != 'error'){
				$newFax = FaxOutgoing::create([
					'phone_number_id'   			=> $request['phone_number_id'],
					'destination_phone_number'  	=> $destNumber,
					'cover_page_dynamic_subject'	=> $request['cover_page_dynamic_subject'],
					'file_name_pdf'   				=> '',
					'file_name_tiff'   				=> '',
				]);

				$fileNamePdf = str_replace('-', '', $newFax->id) . '.pdf';
				$fileNameTiff = str_replace('-', '', $newFax->id) . '.tif';

				// Run conversion of PDF file to TIFF file
				exec("gs -q -dSAFER -dNOPAUSE -sPAPERSIZE=a4 -dMaxStripSize=0 -dPDFSETTINGS=/printer -dPreserveAnnots=false -dFIXEDMEDIA -dPDFFitPage -sOutputFile="
					.str_replace('.pdf', '.tif', $path)." -r204x196 -dDITHER=300 -sDEVICE=tiffg4 -Ilib stocht.ps -c \"{ dup .5 lt { pop 0 } if dup .7 gt { pop 1 } if } settransfer\" -f ".$path);

				// Store the original PDF
				if(AWSS3Service::store($path, $fileNamePdf, 'efax/outbox') == ''){
					$message = 'File failed to save.';
					$alertType = 'error';
				}

				// Store the converted TIFF file
				if(AWSS3Service::store(str_replace('.pdf', '.tif', $path), $fileNameTiff, 'efax/outbox') == ''){
					$message = 'File failed to save.';
					$alertType = 'error';
				}

				$newFax->file_name_pdf = $fileNamePdf;
				$newFax->file_name_tiff = $fileNameTiff;
				$newFax->save();

				unlink($path);
				unlink(str_replace('.pdf', '.tif', $path));
				if($staticPath != ''){
					Storage::disk('public')->delete($staticPath);
				}
				if($dynamicPath != ''){
					Storage::disk('public')->delete(str_replace('storage/', '', $dynamicPath));
				}
			}

			if($alertType != 'error'){
				return redirect()->back()->withSuccessMsg($message);
			} else {
				return redirect()->back()->withErrorMsg($message);
			}
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot create Fax: an error occurred.');
		}
	}

	public function getPhonebookEntries()
	{
		return PhonebookEntry::select('id', 'label', 'phone_number')->where('phonebook_id', $_GET['phonebookId'])->get()->toArray();
	}

	public function getFile(Request $request)
	{
		if($request->file == '' || is_null($request->file)){
			return redirect()->back()->withErrorMsg('Cannot retrieve File: an error occurred.');
		}
		return AWSS3Service::get($request->file, Constants::s3FaxFileFolder . '/outbox');
	}

	public function justGetFile(Request $request)
	{

		if($request->file == '' || is_null($request->file)){
			return response(['message' => 'No file found.'], 404);
		}
		return AWSS3Service::get($request->file, Constants::s3FaxFileFolder. '/outbox');
	}

	public function export(Request $request): BinaryFileResponse
	{
		$phone = PhoneNumber::find($request['outbox_export_phone_id']);
		$name = $phone->phone_number . '_' . now() . '_fax_outbox.csv';
		$export = Excel::download(new FaxOutboxExport($request['outbox_export_ids']), $name, \Maatwebsite\Excel\Excel::CSV);
		ob_end_clean();

		return $export;
	}

	public function destroy(FaxOutgoing $message): RedirectResponse
	{
		try {
			$message->delete();
			return redirect()->back()->withSuccessMsg('Fax was deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Fax: an error occurred.');
		}
	}

	public function permDestroy(string $messageId): RedirectResponse
	{
		$message = FaxOutgoing::withTrashed()->where('id', $messageId)->first();

		try {
			AWSS3Service::delete($message->file_name_pdf, 'efax/outbox');
			$message->forceDelete();

			return redirect()->back()->withSuccessMsg('Fax was deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Fax: an error occurred.');
		}
	}

	public function permDestroyAll(PhoneNumber $phone): RedirectResponse
	{
		$faxes = FaxOutgoing::onlyTrashed()->where('phone_number_id', $phone->id)->get();

		try {
			foreach($faxes as $fax){
				AWSS3Service::delete($fax->file_name_pdf, 'efax/outbox');
				$fax->forceDelete();
			}

			return redirect()->back()->withSuccessMsg('Faxes were deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Faxes: an error occurred.');
		}
	}

	public function restore(FaxOutgoing $message): RedirectResponse
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
		$faxes = FaxOutgoing::onlyTrashed()->where('phone_number_id', $phone->id)->get();

		try {
			foreach($faxes as $fax){
				$fax->restore();
			}

			return redirect()->back()->withSuccessMsg('Faxes were restored successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot restore Faxes: an error occurred.');
		}
	}

	private function getCoverSheetData(string $phoneNumberId): array
	{
		$hasStatic = false;
		$hasDynamic = false;

		$phone = PhoneNumber::select(
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
		)->where('id', $phoneNumberId)->first();

		if($phone->cover_page_enabled == 1){
			$hasStatic = !is_null($phone->cover_page_file_name);

			$dynamicSheetFields = [
				'cover_page_dynamic_company_name',
				'cover_page_dynamic_address_line_1',
				'cover_page_dynamic_address_line_2',
				'cover_page_dynamic_address_city',
				'cover_page_dynamic_address_state',
				'cover_page_dynamic_address_zip',
				'cover_page_dynamic_email',
				'cover_page_dynamic_website'
			];

			foreach($dynamicSheetFields as $field)
			{
				if(!is_null($phone->$field) && $phone->$field !== ''){
					$hasDynamic = true;
					break;
				}
			}
		}
		return ['phone' => $phone,'hasDynamic' => $hasDynamic, 'hasStatic' => $hasStatic];
	}

	private function createDynamicCoverSheet(PhoneNumber $phone, string $destNumber, string $subject): string
	{
		$data = [
			'company_name' => $phone->cover_page_dynamic_company_name ?? '',
			'address_line_1' => $phone->cover_page_dynamic_address_line_1 ?? '',
			'address_line_2' => $phone->cover_page_dynamic_address_line_2 ?? '',
			'address_city' => $phone->cover_page_dynamic_address_city ?? '',
			'address_state' => $phone->cover_page_dynamic_address_state ?? '',
			'address_zip' => $phone->cover_page_dynamic_address_zip ?? '',
			'email' => $phone->cover_page_dynamic_email ?? '',
			'website' => $phone->cover_page_dynamic_website ?? '',
			'subject' => $subject,
			'destNumber' => $destNumber
		];

		$filename = 'dynamic_' . $phone->cover_page_file_name;
		$path = 'storage/temp/pdfs/' . $filename;

		//create the PDF and store it locally
		Pdf::loadView('pbx-user.faxes.dynamic-cover-sheet', $data)->save($path);

		return $path;
	}
}
