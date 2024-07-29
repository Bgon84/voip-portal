
<x-modal id="modal_edit_recipient">
	<x-slot:title>
		Edit Fax Recipient
	</x-slot:title>

	<x-slot:content>
		<form action="{{route('pbxuser.faxes.settings.recipients.update')}}" method="POST" id="form_edit_recipient">
			@csrf

			<div class="mb-10">
				<label class="d-flex align-items-center fs-5 fw-bold mb-2">
					<span class="required">Email Address</span>
				</label>
				<input type="email" id="edit_email_address" class="form-control block mt-1 w-full" name="edit_email_address"
						placeholder="Email Address" autofocus required maxlength="256"/>
			</div>

			<div class="mb-10">
				<x-form.checkbox
					name="edit_outbound_fax_confirmations"
					id="edit_outbound_fax_confirmations"
					label="Receive Fax Confirmation Pages"/>
			</div>

			<div class="mb-10">
				<x-form.checkbox
					name="edit_attach_pdf"
					id="edit_attach_pdf"
					label="Receive a copy of each fax sent"/>
			</div>

			<input type="hidden" name="edit_recipient_id" id="edit_recipient_id">
		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="submit" class="px-6" form="form_edit_recipient" id="form_edit_recipient_btn">
			{{ __('Submit') }}
		</x-jet-button>
	</x-slot:footer>

</x-modal>

