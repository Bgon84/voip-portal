{{-- Modal DELETE fax --}}
<x-modal id="modal_delete_fax">
    <x-slot:title>Delete Fax</x-slot:title>

    <x-slot:content>
        <form method="POST" id="modal_form_delete_fax">
            @method('delete')
            @csrf
            <h4>Are you sure you want to delete this Fax?</h4>
        </form>
    </x-slot:content>

    <x-slot:footer>
        <x-jet-secondary-button data-bs-dismiss="modal">
            {{ __('Close') }}
        </x-jet-secondary-button>
        <x-jet-button type="submit" class="btn-danger px-6" form="modal_form_delete_fax">
            {{ __('Delete Fax') }}
        </x-jet-button>
    </x-slot:footer>
</x-modal>
{{-- END Delete fax Modal --}}
