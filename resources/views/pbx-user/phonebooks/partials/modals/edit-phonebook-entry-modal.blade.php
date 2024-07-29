@if (Auth::user()->hasPermissionTo('pbxuser.phonebook.edit'))
	<x-modal id="modal_edit_entry">
		<x-slot:title>
			Add Phonebook Contact
		</x-slot:title>

		<x-slot:content>
			<form action="{{route('pbxadmin.phonebook-entries.update')}}" method="POST" id="form_edit_entry">
				@csrf

				<div class="mb-10">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="edit_entry_label">
						<span class="required">Name / Label</span>
					</label>
					<input type="text"
						   class="form-control form-control-lg"
						   name="edit_entry_label"
						   id="edit_entry_label"
						   maxlength="64"
						   placeholder="Label"
						   required>
				</div>

				<div class="mb-10" id="edit_entry_phone_div">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="edit_entry_phone">
						<span class="required">Phone Number</span>
						<x-tooltip title="Required: The phone number of phonebook entry (contact)."/>
					</label>
					<x-tel-input id="edit_entry_phone" maxlength="15" class="form-control form-control-lg tel-input phone-input"/>
					<input type="hidden" class="tel-input-hidden" id="edit_entry_phone_value" name="edit_entry_phone_value">
					<span class="btn btn-sm btn-secondary mt-2 clear-tel-btn" onclick="
						$('input[data-phone-input-id=edit_entry_phone]').val('');
						$('#edit_entry_phone').val('');
						$('#edit_entry_phone_value').val('');
					">
						Clear
					</span>
				</div>

				<input type="hidden" name="edit_entry_id" id="edit_entry_id">
			</form>
		</x-slot:content>

		<x-slot:footer>
			<x-jet-secondary-button data-bs-dismiss="modal">
				{{ __('Close') }}
			</x-jet-secondary-button>
			<x-jet-button type="submit" class="px-6" form="form_edit_entry" id="submit-button-edit-entry">
				{{ __('Submit') }}
			</x-jet-button>
		</x-slot:footer>

	</x-modal>
@endif
