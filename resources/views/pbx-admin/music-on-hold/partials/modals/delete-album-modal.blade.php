{{-- Modal DELETE Site --}}
<x-modal id="model_delete_album">
	<x-slot:title>Delete Album</x-slot:title>

	<x-slot:content>
		<form method="POST" id="modal_form_delete_album">
			@method('delete')
			@csrf
			<h4>Are you sure you want to delete this Album? </h4>
			<p>
				All Tracks associated with this Album will also be deleted.
				This is permanent and cannot be undone.
			</p>
		</form>
		@include('pbx-admin.partials.modal-dependency-tree')
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="button" class="btn-danger px-6" form="modal_form_delete_album"
					  onclick="markCustomerChangesPending('modal_form_delete_album')">
			{{ __('Delete Album') }}
		</x-jet-button>
	</x-slot:footer>
</x-modal>
{{-- END Delete Site Modal --}}
