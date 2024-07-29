{{-- Modal DELETE all faxes --}}
<x-modal id="modal_perm_delete_all_fax">
    <x-slot:title>Empty Trash</x-slot:title>

    <x-slot:content>
		<form method="POST" id="modal_form_delete_all_fax">
			@method('delete')
			@csrf
			<h4>Are you sure you want to permanently delete all Faxes in Trash?</h4>
			<p>This is permanent and cannot be undone.</p>
		</form>
    </x-slot:content>

    <x-slot:footer>
        <x-jet-secondary-button data-bs-dismiss="modal">
            {{ __('Close') }}
        </x-jet-secondary-button>
        <x-jet-button type="submit" class="btn-danger px-6" form="modal_form_delete_all_fax">
            {{ __('Delete Faxes') }}
        </x-jet-button>
    </x-slot:footer>
</x-modal>
{{-- END Delete all faxes Modal --}}
