<x-app-layout :section="$section" :title="$title" :subtitle="$subtitle">

    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="card-title">Phonebooks</h2>
            <div class="card-toolbar">
                @if (Auth::user()->hasPermissionTo('pbxadmin.phonebook.store'))
                    <x-jet-button class="btn-sm" data-bs-toggle="modal" data-bs-target="#modal_create_phonebook">
                        <i class="fa fa-plus"></i> Create Phonebook
                    </x-jet-button>
                @endif
            </div>
        </div>

        <div class="card-body">
            <livewire:pbx-admin.phonebooks/>
        </div>

        <div class="card-footer">
            <x-jet-button class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#modal_export">
                <i class="fa fa-download"></i> Export
            </x-jet-button>
        </div>
    </div>

    @push('modals')
        @include('pbx-admin.phonebooks.partials.modals.create-phonebook-modal')
        @include('pbx-admin.phonebooks.partials.modals.export-phonebooks-modal')
    @endpush

	@push('scripts')
		<script>
			$('#site-select').select2({
				dropdownParent: $('#modal_create_phonebook'),
			});

			$('#customer-site-select').select2({
				dropdownParent: $('#modal-form-customer-switch'),
			});

			$('.exportBtn').on('click', function () {
				$('#modal_export').modal('toggle');
			})
		</script>
	@endpush
</x-app-layout>
