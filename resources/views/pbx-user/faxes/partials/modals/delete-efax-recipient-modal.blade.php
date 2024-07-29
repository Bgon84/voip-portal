{{-- Modal DELETE recipient --}}
<x-modal id="modal_delete_recipient">
    <x-slot:title>Delete Fax Recipient</x-slot:title>

    <x-slot:content>
        <form method="POST" id="modal_form_delete_recipient">
            @method('delete')
            @csrf
            <h4>Are you sure you want to delete this Fax Recipient?</h4>
            <p>This is permanent and cannot be undone.</p>
        </form>
    </x-slot:content>

    <x-slot:footer>
        <x-jet-secondary-button data-bs-dismiss="modal">
            {{ __('Close') }}
        </x-jet-secondary-button>
        <x-jet-button type="submit" class="btn-danger px-6" form="modal_form_delete_recipient">
            {{ __('Delete Recipient') }}
        </x-jet-button>
    </x-slot:footer>
</x-modal>
{{-- END Delete recipient Modal --}}
