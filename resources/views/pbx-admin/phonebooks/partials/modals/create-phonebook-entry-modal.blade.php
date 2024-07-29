
@if (Auth::user()->hasPermissionTo('pbxadmin.extension.edit'))
	<x-modal id="modal_create_entry">
		<x-slot:title>
			Add Phonebook Contact
		</x-slot:title>

		<x-slot:content>
			<form action="{{route('pbxadmin.phonebook-entries.store')}}" method="POST" id="form_create_entry">
				@csrf

				<div class="mb-10">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="add_entry_label">
						<span class="required">Name / Label</span>
					</label>
					<input type="text"
						   class="form-control form-control-lg"
						   name="add_entry_label"
						   id="add_entry_label"
						   maxlength="64"
						   placeholder="Label"
						   required>
				</div>

				<div class="mb-10">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="add_entry_phone">
						<span class="required">Phone Number</span>
						<x-tooltip title="Required: The phone number of phonebook entry (contact)."/>
					</label>
					<x-tel-input id="add_entry_phone" maxlength="15" class="form-control form-control-lg tel-input phone-input"/>
					<input type="hidden" class="tel-input-hidden" id="add_entry_phone_value" name="add_entry_phone_value">
					<span class="btn btn-sm btn-secondary mt-2 clear-tel-btn" onclick="
						$('input[data-phone-input-id=add_entry_phone]').val('');
						$('#add_entry_phone').val('');
						$('#add_entry_phone_value').val('');
					">
						Clear
					</span>

				</div>

				<input type="hidden" name="add_entry_phonebook_id" value="{{$phonebook->id}}">
			</form>
		</x-slot:content>

		<x-slot:footer>
			<x-jet-secondary-button data-bs-dismiss="modal">
				{{ __('Close') }}
			</x-jet-secondary-button>
			<x-jet-button type="button" class="px-6" form="form_create_entry" id="submit-button-create-entry"
						  onclick="markCustomerChangesPending('form_create_entry')">
				{{ __('Submit') }}
			</x-jet-button>
		</x-slot:footer>

	</x-modal>
@endif
