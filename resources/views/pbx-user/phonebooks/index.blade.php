<x-app-layout :section="$section" :title="$title" :subtitle="$subtitle">

	<div class="card shadow-sm">
		<div class="card-header">
			<h2 class="card-title">Phonebooks</h2>
			<div class="card-toolbar">
				@if(!$auth_user->isUserLevelUser())
					<x-jet-button class="btn-sm btn-info" data-bs-toggle="modal"
								  data-bs-target="#modal_customer_change">
						Switch Customer
					</x-jet-button>
				@endif
			</div>
		</div>

		<div class="card-body">
			<livewire:pbx-user.phonebooks/>
		</div>

		<div class="card-footer">
			@if(!$auth_user->isUserLevelUser())
				<x-jet-button class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#modal_export">
					<i class="fa fa-download"></i> Export
				</x-jet-button>
			@endif
		</div>
	</div>

	@push('modals')
		@include('pbx-user.partials.modals.customer-switcher-modal', ['for' => 'phonebooks'])
		@include('pbx-user.phonebooks.partials.modals.export-phonebooks-modal')
	@endpush

	@push('scripts')
		<script>
			$('#user_customer_select').select2({
				dropdownParent: $('#modal_customer_change'),
			});

			$('.exportBtn').on('click', function () {
				$('#modal_export').modal('toggle');
			})
		</script>
	@endpush
</x-app-layout>
