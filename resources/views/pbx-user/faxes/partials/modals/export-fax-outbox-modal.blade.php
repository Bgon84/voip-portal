{{-- EXPORT MODAL --}}
<x-modal id="modal_export_outbox">
	<x-slot:title>
		Export Outgoing Faxes
	</x-slot:title>

	<x-slot:content>
		<div class="row mb-10">
			<h3>Please select the data to export:</h3>
		</div>

		<div class="row mb-10">
			<div class="col">
				<a href="{{route('pbxuser.faxes.outbox.export', ['option' => 'currentPhone'])}}"
				   class="btn btn-light-primary export-outbox-btn">Faxes from current Phone Number</a>
			</div>
			<div class="col">
				<a href="{{route('pbxuser.faxes.outbox.export', ['option' => 'allPhones'])}}"
				   class="btn btn-light-primary export-outbox-btn">Faxes from all Phone Numbers</a>
			</div>
		</div>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
	</x-slot:footer>
</x-modal>
{{-- END EXPORT MODAL--}}
