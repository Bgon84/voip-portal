{{-- Modal DELETE bulk faxes --}}
<x-modal id="modal_delete_bulk_fax">
    <x-slot:title>Delete Faxes</x-slot:title>

    <x-slot:content>
        <form method="POST" id="modal_form_delete_bulk_fax" action="{{route('pbxuser.faxes.inbox.bulk-destroy')}}">
{{--            @method('delete')--}}
            @csrf
            <h4>Are you sure you want to delete all selected Faxes?</h4>
			<input type="hidden" id="bulk_delete_fax_ids" name="bulk_delete_fax_ids">
        </form>
    </x-slot:content>

    <x-slot:footer>
        <x-jet-secondary-button data-bs-dismiss="modal">
            {{ __('Close') }}
        </x-jet-secondary-button>
        <x-jet-button type="submit" class="btn-danger px-6" form="modal_form_delete_bulk_fax">
            {{ __('Delete Faxes') }}
        </x-jet-button>
    </x-slot:footer>
</x-modal>
{{-- END Delete bulk faxes Modal --}}
