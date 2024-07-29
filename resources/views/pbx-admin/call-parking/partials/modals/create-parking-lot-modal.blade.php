{{-- MODAL CREATE NEW Call Parking Lot --}}
@if (Auth::user()->hasPermissionTo('pbxadmin.call-parking-lot.store'))
	<x-modal id="modal_create_parking_lot">
		<x-slot:title>
			Create Parking Lot
		</x-slot:title>

		<x-slot:content>
			<form action="{{route('pbxadmin.call-parking.store')}}" method="POST" id="modal-form" enctype="multipart/form-data">
				@csrf

				<div class="fv-row mb-10">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2">
						<span class="required">Parking Lot Name</span>
						<x-tooltip title="Required: The name of the Parking Lot to create."/>
					</label>
					<input type="text" id="name" class="form-control block mt-1 w-full" name="name" required
						   autofocus autocomplete="name" maxlength="64"/>
				</div>

				<div class="fv-row mb-10" id="site_modal_element">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2">
						<span class="required">Site</span>
						<x-tooltip title="Required: The Site this is assigned to."/>
					</label>
					<select class="form-select" aria-label="Select Site" name="site_id" id="site-select">
						@foreach($sites as $site)
							<option value="{{$site->id}}" @if($site->id == $currentSiteId) selected @endif>
								{{$site->name}}
							</option>
						@endforeach
					</select>
				</div>
				<div class="mb-10">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2">
						Bulk Upload
						<x-tooltip title="Upload a CSV of values to bulk create. You must still select a Site." />
					</label>
					<input type="file" accept="text/csv" name="csv_file" id="csv_file"/>
					<br>
					<a href="{{asset('assets/samples/sample_bulk_uploads.csv')}}" download="call_parking_bulk_upload.csv"="bulk_upload.csv">Download the Sample CSV</a>
				</div>
			</form>
		</x-slot:content>

		<x-slot:footer>
			<x-jet-secondary-button data-bs-dismiss="modal">
				{{ __('Close') }}
			</x-jet-secondary-button>
			<x-jet-button type="button" class="px-6" form="modal-form" id="submit-button-create-customer"
						  onclick="markCustomerChangesPending('modal-form')">
				{{ __('Submit') }}
			</x-jet-button>
		</x-slot:footer>

	</x-modal>
@endif
{{-- END ADD Call Parking Lot MODAL--}}
