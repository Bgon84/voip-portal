{{-- MODAL CREATE NEW FAX --}}
<x-modal id="modal_add_fax">
	<x-slot:title>
		Send Fax
	</x-slot:title>

	<x-slot:content>
		<form action="{{route('pbxuser.faxes.store')}}" method="POST" id="modal_add_fax_form" enctype="multipart/form-data">
			@csrf

			<div class="mb-10">
				<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="fax_file">
					<span class="required">File(s)</span>
					<x-tooltip title="Required: the file(s) to be faxed to the recipient."/>
				</label>
				<input type="file" multiple id="fax_file" class="form-control" name="fax_file[]" accept=".pdf" required/>
			</div>

			<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="fax_name">
				<span class="required">Destination Phone Number</span>
				<x-tooltip title="Required: the phone number of the intended recipient of the fax."/>
			</label>

			@if(count($phonebooks) > 0)
				<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-bold mb-8" id="send_fax_tabMenu">
					<li class="nav-item">
						<a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" id="tab_enter_link"
						   href="#tab_enter">Enter</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_choose_link"
						   href="#tab_choose">Choose</a>
					</li>
				</ul>
			@endif

			<div class="tab-content" id="myTabContent">

				<div class="tab-pane fade show active" id="tab_enter" role="tabpanel">
					<div class="mb-10">
						@if(count($phonebooks) > 0)
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="fax_name">
								Enter a Phone Number
							</label>
						@endif
						<x-tel-input class="form-control form-control-lg" id="fax_send_to_number" />
						<input type="hidden" class="tel-input-hidden" id="fax_send_to_number_value" name="fax_to_phone_number">
						<span class="btn btn-sm btn-secondary mt-2 clear-tel-btn" onclick="clearTelInput()">
						Clear
					</span>
					</div>
				</div>

				@if(count($phonebooks) > 0)
					<div class="tab-pane fade show" id="tab_choose" role="tabpanel">
						<div class="mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="phonebook_select">
								Choose a Phonebook to view its entries
							</label>
							<select class="form-select" id="phonebook_select">
								<option value="0">Select a Phonebook</option>
								@foreach($phonebooks as $phonebook)
									<option value="{{$phonebook->id}}">{{$phonebook->name}}</option>
								@endforeach
							</select>
						</div>
						<div class="mb-10" id="phonebook_entry_div"  style="display:none;">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="phonebook_entry_select">
								Choose an entry to send this fax to
							</label>
							<select class="form-select" id="phonebook_entry_select" name="fax_to_phone_number" disabled>
							</select>
						</div>
					</div>
				@endif

				@if($needSubject === 'true')
					<div class="mb-10">
						<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="cover_page_dynamic_subject">
							Enter a subject for this fax
							<x-tooltip title="This will be added to your dynamic coversheet defined on the Settings tab."/>
						</label>
						<input type="text" id="cover_page_dynamic_subject" class="form-control"
							   name="cover_page_dynamic_subject" maxlength="128"/>
					</div>
				@endif

			</div>
			<input type="hidden" name="phone_number_id" value="{{$phone->id}}">
		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="submit" class="px-6" form="modal_add_fax_form" id="add_fax_submit">
			{{ __('Submit') }}
		</x-jet-button>
	</x-slot:footer>

</x-modal>
{{-- END ADD FAX MODAL--}}
