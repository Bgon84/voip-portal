{{-- EXPORT MODAL --}}
@if (Auth::user()->hasPermissionTo('pbxadmin.call-parking-lot.store'))
	<x-modal id="modal_export">
		<x-slot:title>
			Export Parking Lots
		</x-slot:title>

		<x-slot:content>
			<div class="row mb-10">
				<h3>Please select the data to export:</h3>
			</div>

			<div class="row mb-10">
				<div class="col">
					<a href="{{route('pbxadmin.call-parking.export', ['option' => 'currentSite'])}}"
					   class="btn btn-light-primary exportBtn">All Call Parking Lots from the current Site</a>
				</div>
				<div class="col">
					<a href="{{route('pbxadmin.call-parking.export', ['option' => 'currentCustomer'])}}"
					   class="btn btn-light-primary exportBtn">All Call Parking Lots from the current Customer</a>
				</div>
			</div>
			<div class="row mb-10">
				<div class="col text-center">
					<a href="{{route('pbxadmin.call-parking.export', ['option' => 'allCustomers'])}}"
					   class="btn btn-light-primary exportBtn">All Call Parking Lots from all Customers</a>
				</div>
			</div>
		</x-slot:content>

		<x-slot:footer>
			<x-jet-secondary-button data-bs-dismiss="modal">
				{{ __('Close') }}
			</x-jet-secondary-button>
		</x-slot:footer>

	</x-modal>
@endif
{{-- END EXPORT MODAL--}}
