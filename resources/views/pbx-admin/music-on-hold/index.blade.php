<x-app-layout :section="$section" :title="$title" :subtitle="$subtitle">

    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="card-title">Music on Hold Albums</h2>
            <div class="card-toolbar">
                @if (Auth::user()->hasPermissionTo('pbxadmin.album.store'))
                    <x-jet-button class="btn-sm" data-bs-toggle="modal" data-bs-target="#modal_create_webhook">
                        <i class="fa fa-plus"></i> Create Album
                    </x-jet-button>
                @endif
            </div>
        </div>

        <div class="card-body">
            <livewire:pbx-admin.moh-albums/>
        </div>

        <div class="card-footer">
            <x-jet-button class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#modal_export">
                <i class="fa fa-download"></i> Export
            </x-jet-button>
        </div>
    </div>

    @push('modals')
        @include('pbx-admin.music-on-hold.partials.modals.create-album-modal')
        @include('pbx-admin.music-on-hold.partials.modals.export-albums-modal')
        @include('pbx-admin.music-on-hold.partials.modals.delete-album-modal')
    @endpush

	@push('scripts')
		@include('pbx-admin.partials.scripts.dependency-ajax-script')

		<script>
			$('#site-select').select2({
				dropdownParent: $('#modal_create_webhook'),
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


