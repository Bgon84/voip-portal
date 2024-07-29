<x-modal id="modal_phone_number_switch">
    <x-slot:title>
       Switch Fax Line
    </x-slot:title>

    <x-slot:content>
        <form action="{{route('pbxuser.phone-number-switch.store')}}" method="POST" id="modal-form-fax-line-switch">
            @csrf

            <div class="mb-10">
				@livewire('pbx-user.phone-number-select', [
					"label" => "Select a Fax Line",
					"tooltip" => "Easily switch between your fax lines from this searchable dropdown.",
					"type"	=> "fax"
				])
            </div>

        </form>
    </x-slot:content>

    <x-slot:footer>
        <x-jet-secondary-button data-bs-dismiss="modal">
            {{ __('Close') }}
        </x-jet-secondary-button>
        <x-jet-button type="submit" class="px-6" form="modal-form-fax-line-switch" id="submit-button-fax-line-switcher">
            {{ __('Submit') }}
        </x-jet-button>
    </x-slot:footer>

</x-modal>
