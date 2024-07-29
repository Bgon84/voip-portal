
<x-modal id="modal_delete_entry">
	<x-slot:title>Delete Contact</x-slot:title>

	<x-slot:content>
		<form method="POST" id="modal_form_delete_entry">
			@method('delete')
			@csrf
			<h4>Are you sure you want to delete this Contact?</h4>
			<p>This is permanent and cannot be undone.</p>
		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="submit" class="btn-danger px-6" form="modal_form_delete_entry">
			{{ __('Delete Contact') }}
		</x-jet-button>
	</x-slot:footer>
</x-modal>
