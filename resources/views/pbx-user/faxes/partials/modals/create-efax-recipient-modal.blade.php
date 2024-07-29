
<x-modal id="modal_add_recipient">
	<x-slot:title>
		Add Fax Recipient
	</x-slot:title>

	<x-slot:content>
		<form action="{{route('pbxuser.faxes.settings.recipients.store')}}" method="POST" id="form_add_recipient">
			@csrf

			<div class="mb-10">
				<label class="d-flex align-items-center fs-5 fw-bold mb-2">
					<span class="required">Email Address</span>
				</label>
				<input type="email" id="email_address" class="form-control block mt-1 w-full" name="email_address"
						placeholder="Email Address" autofocus required maxlength="256"/>
			</div>

			<div class="mb-10">
				<x-form.checkbox
					name="outbound_fax_confirmations"
					label="Receive Fax Confirmation Pages"
					:checked="true"
				/>
			</div>

			<div class="mb-10">
				<x-form.checkbox
					name="attach_pdf"
					label="Receive a copy of each fax sent"
					:checked="true"
				/>
			</div>

			<input type="hidden" name="add_recipient_phone_id" value="{{$phone->id}}">
		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="submit" class="px-6" form="form_add_recipient" id="form_add_recipient_btn">
			{{ __('Submit') }}
		</x-jet-button>
	</x-slot:footer>

</x-modal>

