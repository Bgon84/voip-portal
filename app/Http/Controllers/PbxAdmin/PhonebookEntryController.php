<?php

namespace App\Http\Controllers\PbxAdmin;

use App\Http\Controllers\Controller;
use App\Models\PbxAdmin\ExtensionVoicemailNotifyMultiple;
use App\Models\PbxAdmin\ExtensionVoicemailNotifyOnetime;
use App\Models\PbxAdmin\PhonebookEntry;
use App\Services\LoggerCustom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PhonebookEntryController extends Controller
{
	public LoggerCustom $logger;

	public function __construct()
	{
		$this->logger = new LoggerCustom();
	}

	public function store(Request $request): RedirectResponse
	{
		$data['phonebook_id']   = $request['add_entry_phonebook_id'];
		$data['phone_number']	= $request['add_entry_phone_value'];
		$data['label']       	= $request['add_entry_label'];

		$rules = [
			'phonebook_id'  => ['required', 'string', 'max:36'],
			'phone_number'  => ['required', 'string', 'max:15'],
			'label'      	=> ['required', 'string', 'max:128'],
		];

		$validator = Validator::make($data, $rules);

		if($validator->fails())
		{
			return redirect()->back()->withErrorMsg($validator->messages()->first());
		}

		try {
			PhonebookEntry::create([
				'phonebook_id'  => $request['add_entry_phonebook_id'],
				'phone_number'  => $request['add_entry_phone_value'],
				'label'     	=> $request['add_entry_label']
			]);

			return redirect()->back()->withSuccessMsg('Phonebook Entry was added successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot create Phonebook Entry: an error occurred.');
		}
	}

	public function update(Request $request): RedirectResponse
	{
		$data['entry_id']   	= $request['edit_entry_id'];
		$data['phone_number']	= $request['edit_entry_phone_value'];
		$data['label']       	= $request['edit_entry_label'];

		$rules = [
			'entry_id'  	=> ['required', 'string', 'max:36'],
			'phone_number'  => ['required', 'string', 'max:15'],
			'label'      	=> ['required', 'string', 'max:128'],
		];

		$validator = Validator::make($data, $rules);

		if($validator->fails())
		{
			return redirect()->back()->withErrorMsg($validator->messages()->first());
		}

		$entry = PhonebookEntry::where('id', $request['edit_entry_id'])->first();

		try {
			$entry->label 			= $request['edit_entry_label'];
			$entry->phone_number 	= $request['edit_entry_phone_value'];

			$entry->save();

			return redirect()->back()->withSuccessMsg('Phonebook Entry was updated successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot update Phonebook Entry: an error occurred.');
		}
	}

	public function destroy(PhonebookEntry $entry): RedirectResponse
	{
		$entryId = $entry->id;
		ExtensionVoicemailNotifyOnetime::where('sms_pb_entry_id', $entryId)->delete();
		ExtensionVoicemailNotifyMultiple::where('sms_pb_entry_id', $entryId)
			->orWhere('voice_pb_entry_id', $entryId)->delete();

		try {
			$entry->delete();
			return redirect()->back()->withSuccessMsg('Phonebook Entry was deleted successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot delete Phonebook Entry: an error occurred.');
		}
	}

	public function restore(PhonebookEntry $entry): RedirectResponse
	{
		try {
			$entry->restore();
			return redirect()->back()->withSuccessMsg('Phonebook Entry was restored successfully!');
		} catch (\Throwable $th) {
			$this->logger->error($th);
			return redirect()->back()->withErrorMsg('Cannot restore Phonebook Entry: an error occurred.');
		}
	}
}
