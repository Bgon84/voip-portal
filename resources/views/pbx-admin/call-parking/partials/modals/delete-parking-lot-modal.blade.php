{{-- Modal DELETE Parking Lot --}}
<x-modal id="model_delete_parkingLot">
	<x-slot:title>Delete Parking Lot</x-slot:title>

	<x-slot:content>
		<form method="POST" id="modal_form_delete_parkingLot">
			@method('delete')
			@csrf
			<h4>Are you sure you want to delete this Parking Lot?</h4>
		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="button" class="btn-danger px-6" form="modal_form_delete_parkingLot"
					  onclick="markCustomerChangesPending('modal_form_delete_parkingLot')">
			{{ __('Delete Parking Lot') }}
		</x-jet-button>
	</x-slot:footer>
</x-modal>
{{-- END Delete Parking Lot Modal --}}
