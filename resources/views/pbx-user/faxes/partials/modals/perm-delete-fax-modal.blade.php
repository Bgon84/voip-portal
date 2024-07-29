{{-- Modal Perm DELETE fax --}}
<x-modal id="modal_perm_delete_fax">
    <x-slot:title>Permanently Delete Fax</x-slot:title>

    <x-slot:content>
        <form method="POST" id="modal_form_perm_delete_fax">
            @method('delete')
            @csrf
            <h4>Are you sure you want to delete this Fax?</h4>
			<p>This is permanent and cannot be undone.</p>
        </form>
    </x-slot:content>

    <x-slot:footer>
        <x-jet-secondary-button data-bs-dismiss="modal">
            {{ __('Close') }}
        </x-jet-secondary-button>
        <x-jet-button type="submit" class="btn-danger px-6" form="modal_form_perm_delete_fax">
            {{ __('Delete Fax') }}
        </x-jet-button>
    </x-slot:footer>
</x-modal>
{{-- END Perm Delete fax Modal --}}
