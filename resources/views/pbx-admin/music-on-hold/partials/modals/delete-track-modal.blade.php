{{-- Modal DELETE TRACK --}}
<x-modal id="model_delete_track">
	<x-slot:title>Delete Track</x-slot:title>

	<x-slot:content>
		<form method="POST" id="modal_form_delete_track">
			@method('delete')
			@csrf
			<h4>Are you sure you want to delete this Track? </h4>
			<p>This is permanent and cannot be undone.</p>
		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="button" class="btn-danger px-6" form="modal_form_delete_track"
					  onclick="markCustomerChangesPending('modal_form_delete_track')">
			{{ __('Delete Track') }}
		</x-jet-button>
	</x-slot:footer>
</x-modal>
{{-- END Delete TRACK Modal --}}
