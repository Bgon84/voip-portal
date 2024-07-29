<x-app-layout :section="$section" :title="$title" :breadcrumbs="[
    [route('pbxadmin.call-parking.index'), 'Parking Lots'],
    'Editing ' . $parkingLot->name
    ]">

	<div class="card shadow-sm">
		<div class="card-header">
			<h3 class="card-title fw-bold">
				Editing
				{{$parkingLot->name}}
			</h3>
		</div>

		<div class="card-body">

			<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-bold mb-8" id="tabMenu">
				<li class="nav-item">
					<a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" id="tab_general_link"
					   href="#tab_general">General</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_call_flow_link"
					   href="#tab_call_flow">Call Flow</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_advanced_link"
					   href="#tab_advanced">Advanced</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_ownership_link"
					   href="#tab_ownership">Ownership</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_audit_link"
					   href="#tab_audit">Audit</a>
				</li>
			</ul>

			<form action="{{route('pbxadmin.call-parking.update', ['parkingLot' => $parkingLot])}}"
				  method="POST"
				  id="formulario" enctype="multipart/form-data">
				@csrf

				<div class="tab-content" id="myTabContent">

					{{-- General Tab --}}
					<div class="tab-pane fade show active" id="tab_general" role="tabpanel">

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="name">
								<span class="required">Name</span>
								<x-tooltip title="Required: The Parking Lot name."/>
							</label>
							<input type="text"
								   class="form-control form-control-lg"
								   name="name"
								   id="name"
								   maxlength="256"
								   placeholder="Name"
								   value="{{$parkingLot->name}}"
								   required>
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="description">
								Description
								<x-tooltip title="A description of this Parking Lot."/>
							</label>
							<input type="text"
								   class="form-control form-control-lg"
								   name="description"
								   id="description"
								   maxlength="256"
								   placeholder="Description"
								   value="{{$parkingLot->description}}">
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="notes">
								Notes
								<x-tooltip title="Notes about this Parking Lot for other technicians and admins."/>
							</label>
							<textarea class="form-control form-control-lg" rows="3" name="notes" id="notes"
									  maxlength="1024" placeholder="Notes">{{$parkingLot->notes}}</textarea>
						</div>

						<div class="fv-row mb-10">
							@include('pbx-admin.partials.ext-number-input', ['extensionNumber' => $parkingLot->ext_number, 'entity' => 'Parking Lot'])
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="number_of_slots">
								Number of Parking Spaces
							</label>
							<div class="row">
								<div class="col-md-1">
									<input type="number"
										   class="form-control"
										   id="number_of_slots"
										   name="number_of_slots"
										   min="1"
										   max="200"
										   value="{{$parkingLot->number_of_slots}}"
									>
								</div>
								<div class="col-md-3 mt-3">
									<span>Spaces</span>
								</div>
							</div>
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="moh_album_id">
								Music on Hold Album
								<x-tooltip title="You can change/set the Music on Hold album used for the remainder of the call."/>
							</label>
							<select class="form-select" aria-label="Select Site" name="moh_album_id"
									id="moh_album_id_select">
								<option value="0">No Change / Inherit</option>
								@foreach($albums as $album)
									<option value="{{$album->id}}"
											@if($album->id == $parkingLot->moh_album_id) selected @endif>
										{{$album->name}}
									</option>
								@endforeach
							</select>
						</div>

					</div> <!-- End General Tab-->

					<!-- Call Flow Tab -->
					<div class="tab-pane fade show" id="tab_call_flow" role="tabpanel">

						<div class="mb-10">
							<label class="form-label d-flex align-items-center fs-5 fw-bold mb-2" for="timeout_seconds">
							   Maximum Hold Time
								<x-tooltip title="The maximum number of seconds a caller will be on hold before being
									sent to the Timeout Destination defined below."/>
							</label>
							<div class="row">
								<div class="col-md-1">
									<input type="number"
										   class="form-control"
										   id="timeout_seconds"
										   name="timeout_seconds"
										   min="1"
										   max="1800"
										   value="{{$parkingLot->timeout_seconds}}"
									>
								</div>
								<div class="col-md-3 mt-3">
									<span>Seconds</span>
								</div>
							</div>
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="destination">
								Timeout Destination
								<x-tooltip title="Where to send callers if the hold time reaches the Maximum Hold Time defined above."/>
							</label>
							<select class="form-select" aria-label="Select Destination Type" name="destination"
									id="destination_select">
								@foreach($destinations as $key => $value)
									@if($key == "0")
										<option value="0"
												@if($parkingLot->dest_timeout_enum == 0) selected @endif>
											Default Action (Ring Back to Original Parker)
										</option>
										@else
										<option value="{{$key}}"
												@if($parkingLot->dest_timeout_enum == $key) selected @endif>
											{{$value}}
										</option>
									@endif
								@endforeach
							</select>
							@include('pbx-admin.partials.destination-selects', [
										'name'                  => 'dest_timeout_id',
										'hiddenInputId'         => 'phone_value',
										'phoneInputId'          => 'phone',
										'selectId'              => 'destination_id_select',
										'selectDivId'           => 'dest_select_div',
										'phoneInputDivId'       => 'dest_input_div',
										'currentDestId'         => $parkingLot->dest_timeout_id,
										'currentDestEnum'       => $parkingLot->dest_timeout_enum,
									])
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="destination">
								Overflow Destination
								<x-tooltip title="Where to send callers if all parking spaces are full."/>
							</label>
							<select class="form-select" aria-label="Select Destination Type" name="overflow_destination"
									id="overflow_destination_select">
								@foreach($destinations as $key => $value)
									<option value="{{$key}}"
											@if($parkingLot->dest_overflow_enum == $key) selected @endif>
										{{$value}}
									</option>
								@endforeach
							</select>
							@include('pbx-admin.partials.destination-selects', [
										'name'                  => 'dest_overflow_id',
										'hiddenInputId'         => 'dest_overflow_phone_value',
										'phoneInputId'          => 'dest_overflow_phone',
										'selectId'              => 'dest_overflow_dest_id_select',
										'selectDivId'           => 'dest_overflow_select_div',
										'phoneInputDivId'       => 'dest_overflow_input_div',
										'currentDestId'         => $parkingLot->dest_overflow_id,
										'currentDestEnum'       => $parkingLot->dest_overflow_enum,
									])
						</div>

					</div><!-- End Call Flow Tab -->

					<!-- Advanced Tab -->
					<div class="tab-pane fade show" id="tab_advanced" role="tabpanel">

						<div class="mb-10">
							<label class="form-label d-flex align-items-center fs-5 fw-bold mb-2">
								<span>CDR Tags</span>
								<x-tooltip title="Add tags to the call, which can be analyzed with our Call History
									APIs and Portal. They do not show up on callerID or on phones/apps."/>
							</label>
							<input class="form-control" value="{{$parkingLot->tags_cdr}}" id="tags_cdr"
								   name="tags_cdr" tabindex="-1" maxlength="256">
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="webhook-select">
								Webhook
								<x-tooltip title="Send real-time events to a particular webhook subscription."/>
							</label>
							<select class="form-select" aria-label="Select Site" name="webhook" id="webhook_select">
								<option value="0">None</option>
								@foreach($webhooks as $webhook)
									<option value="{{$webhook->id}}"
											@if($webhook->id == $parkingLot->webhook_id) selected @endif>
										{{$webhook->name}} ({{$webhook->site->name}})
									</option>
								@endforeach
							</select>
						</div>

					</div> <!-- End Advanced Tab -->

					<!-- Ownership Tab-->
					<div class="tab-pane fade show" id="tab_ownership" role="tabpanel">

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="site-select">
								<span class="required">Site</span>
								<x-tooltip title="Required: The site this is Parking Lot assigned to."/>
							</label>
							<select class="form-select" aria-label="Select Site" name="site" id="site-select">
								@foreach($sites as $site)
									<option value="{{$site->id}}"
											@if($site->id == $parkingLot->site_id) selected @endif>
										{{$site->name}}
									</option>
								@endforeach
							</select>
						</div>

					</div> <!-- End Ownership Tab -->

					<!-- Audit Tab-->
					<div class="tab-pane fade show" id="tab_audit" role="tabpanel">
						@livewire('audits', ['auditableId' => $parkingLot->id, 'auditableType' => 'App\Models\PbxAdmin\CallParkingLot'])
					</div> <!-- End Audit Tab -->

				</div>
				<input type="hidden" name="tab_id" id="tab_id" value="#tab_general">
			</form>
		</div>

		<div class="card-footer">
			<a href="{{route('pbxadmin.call-parking.index')}}" class="btn btn-light-primary"><i
					class="fa fa-arrow-left"></i> Back</a>
			<a onclick="location.reload();" class="btn btn-light-info" data-bs-toggle="tooltip" tabindex="0"
			   title="Reloads the current page, discarding any changes you might have made."
			   data-bs-original-title="Reloads the current page, discarding any changes you might have made.">
				<i class="fa fa-redo"></i> Discard Changes
			</a>
			<button type="button" class="btn btn-light-success" id="save-button" style="float:right">
				<i class="fa fa-check fw-bolder"></i>
				Save
			</button>
		</div>
	</div>

	@push('scripts')
		@include('pbx-admin.partials.scripts.ext-number-check-script', ['siteId' => $parkingLot->site_id, 'currentEntityId' => $parkingLot->id])
		@include('pbx-admin.partials.scripts.destination-selects-script', [
																			'destinationSelectId'   => 'destination_select',
																			'modalId'               => '',
																			'hiddenInputId'         => 'phone_value',
																			'phoneInputId'          => 'phone',
																			'selectId'              => 'destination_id_select',
																			'selectDivId'           => 'dest_select_div',
																			'phoneInputDivId'       => 'dest_input_div',
																			'currentDestId'         => $parkingLot->dest_timeout_id,
																			'currentDestEnum'       => $parkingLot->dest_timeout_enum,
																		 ])
		@include('pbx-admin.partials.scripts.destination-selects-script', [
																			'destinationSelectId'   => 'overflow_destination_select',
																			'modalId'               => '',
																			'hiddenInputId'         => 'dest_overflow_phone_value',
																			'phoneInputId'          => 'dest_overflow_phone',
																			'selectId'              => 'dest_overflow_dest_id_select',
																			'selectDivId'           => 'dest_overflow_select_div',
																			'phoneInputDivId'       => 'dest_overflow_input_div',
																			'currentDestId'         => $parkingLot->dest_overflow_id,
																			'currentDestEnum'       => $parkingLot->dest_overflow_enum,
																		 ])


		<script>
			$('#save-button').on('click', () => {
				markCustomerChangesPending('formulario');
			})

			let input = document.querySelector('input[name=tags_cdr]');
			new Tagify(input);

			$('.form-select').select2();
		</script>
	@endpush
</x-app-layout>

