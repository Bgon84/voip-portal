{{-- Modal DELETE Phonebook --}}
<x-modal id="modal_delete_phonebook">
	<x-slot:title>Delete Phonebook</x-slot:title>

	<x-slot:content>
		<form method="POST" id="modal_form_delete_phonebook">
			@method('delete')
			@csrf
			<h4>Are you sure you want to delete this Phonebook?</h4>
			<p>Any Notifications using any of this Phonebook's entries will be deleted and cannot be restored.</p>

		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="button" class="btn-danger px-6" form="modal_form_delete_phonebook"
					  onclick="markCustomerChangesPending('modal_form_delete_phonebook')">
			{{ __('Delete Phonebook') }}
		</x-jet-button>
	</x-slot:footer>
</x-modal>
{{-- END Delete Phonebook Modal --}}
