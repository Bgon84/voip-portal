
<form action="{{route('pbxuser.faxes.settings.cover.store')}}" method="POST" id="fax_cover_page_form" enctype="multipart/form-data">
	@csrf
	<div>
		<div class="mb-10">
			<x-form.checkbox
				name="cover_page_enabled"
				:object="$phone"
				label="Enable Fax Cover Page"
				tooltip="Choose whether faxes from this line contain a cover page."
				:checked="$phone->cover_page_enabled == 1"
			/>
		</div>

		<hr class="mb-10">

		<h4>Static Cover Sheet</h4>
		<div class="mb-10">
			@if(!is_null($phone->cover_page_file_name))
				<a href="{{route('pbxuser.faxes.settings.cover.get-coversheet', ['file' => $phone->cover_page_file_name])}}" target="_blank">
					View Current Cover Sheet
				</a>

				<div class="my-10">
					<x-form.checkbox name="delete_cover_sheet" tooltip="Delete the current Cover Sheet."/>
				</div>

			@else
				There is currently no Static Cover Sheet for this Phone Number.
			@endif
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="efax_cover_page_file">
				Upload Fax Cover Sheet
				<x-tooltip title="This is your static Cover Sheet. You may have both a Static and Dynamic Cover Sheet. PDF only."/>
			</label>
			<input class="form-control form-control-lg" type="file" id="efax_cover_page_file" name="efax_cover_page_file"
				   accept=".pdf">
		</div>

		<hr class="mb-10">

		<h4>Dynamic Cover Sheet</h4>
		<p>Fill in these fields to have a dynamically created Cover Page attached to all faxes from this Phone Number.</p>
		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="company_name">
				Company Name
			</label>
			<input type="text" class="form-control" name="company_name" id="company_name" maxlength="128"
				   placeholder="Company Name" value="{{$phone->cover_page_dynamic_company_name}}">
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="address_line_1">
				Address Line 1
			</label>
			<input type="text" class="form-control" name="address_line_1" id="address_line_1" maxlength="128"
				   placeholder="Address Line 1" value="{{$phone->cover_page_dynamic_address_line_1}}">
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="address_line_2">
				Address Line 2
			</label>
			<input type="text" class="form-control" name="address_line_2" id="address_line_2" maxlength="128"
				   placeholder="Address Line 2" value="{{$phone->cover_page_dynamic_address_line_2}}">
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="city">
				City
			</label>
			<input type="text" class="form-control" name="city" id="city" maxlength="128" placeholder="City"
					value="{{$phone->cover_page_dynamic_address_city }}">
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="state_select">
				State
			</label>
			<select class="form-select" id="state_select" name="state">
				<option value="0">-- Select a state --</option>
				@foreach(config('states') as $state)
					<option value="{{$state[0]}}"
							@if($phone->cover_page_dynamic_address_state == $state[0]) selected @endif>
						{{$state[1]}}
					</option>
				@endforeach
			</select>
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="zip">
				Zip Code
			</label>
			<input type="text" class="form-control" name="zip" id="zip" maxlength="5" placeholder="ZIP (5 digits)"
				   value="{{$phone->cover_page_dynamic_address_zip }}">
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="cover_page_email">
				Email
			</label>
			<input type="email" class="form-control" name="cover_page_email" id="cover_page_email" maxlength="128"
				   placeholder="Email" value="{{$phone->cover_page_dynamic_email}}">
		</div>

		<div class="mb-10">
			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="cover_page_website">
				Website
			</label>
			<input type="text" class="form-control" name="cover_page_website" id="cover_page_website" maxlength="128"
				   placeholder="www.example.com" value="{{$phone->cover_page_dynamic_website}}">
		</div>

	</div>
	<input type="hidden" name="phone_number_id" value="{{$phone->id}}">
</form>

<div class="card-footer">
	<a onclick="location.reload();" class="btn btn-light-info" data-bs-toggle="tooltip" tabindex="0"
	   title="Reloads the current page, discarding any changes you might have made."
	   data-bs-original-title="Reloads the current page, discarding any changes you might have made.">
		<i class="fa fa-redo"></i> Discard Changes
	</a>
	<button type="submit" class="btn btn-light-success" id="cover-page-save-button" form="fax_cover_page_form" style="float:right">
		<i class="fa fa-check fw-bolder"></i>
		Save
	</button>
</div>
