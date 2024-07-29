<x-app-layout :section="$section" :title="$title" :breadcrumbs="[[route('pbxadmin.phonebooks.index'), 'Phonebooks'], 'Editing ' . $phonebook->name]">

	<div class="card shadow-sm">
		<div class="card-header">
			<h3 class="card-title fw-bold">
				Editing
				{{$phonebook->name}}
			</h3>
		</div>

		<div class="card-body">

			<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-bold mb-8" id="tabMenu">
				<li class="nav-item">
					<a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" id="tab_general_link"
					   href="#tab_general">General</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_contacts_link"
					   href="#tab_contacts">Contacts</a>
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

			<form action="{{route('pbxadmin.phonebooks.update', ['phonebook' => $phonebook])}}"
				  method="POST"
				  id="formulario" enctype="multipart/form-data">
				@csrf

				<div class="tab-content" id="myTabContent">

					{{-- General Tab --}}
					<div class="tab-pane fade show active" id="tab_general" role="tabpanel">

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="name">
								<span class="required">Name</span>
								<x-tooltip title="Required: The name of the List"/>
							</label>
							<input type="text"
								   class="form-control form-control-lg"
								   name="name"
								   id="name"
								   maxlength="64"
								   placeholder="Name"
								   value="{{$phonebook->name}}"
								   required>
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="description">
								Description
								<x-tooltip title="A description of this List"/>
							</label>
							<input type="text"
								   class="form-control form-control-lg"
								   name="description"
								   id="description"
								   maxlength="256"
								   placeholder="Description"
								   value="{{$phonebook->description}}">
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="notes">
								Notes
								<x-tooltip title="Notes about this List for other technicians and admins."/>
							</label>
							<textarea class="form-control form-control-lg" rows="3" name="notes" id="notes"
									  maxlength="1024" placeholder="Notes">{{$phonebook->notes}}</textarea>
						</div>

					</div> <!-- End General Tab-->

					<!-- Contacts Tab -->
					<div class="tab-pane fade show" id="tab_contacts" role="tabpanel">
						<div class="fv-row mb-10 text-center">
							<x-jet-button type="button" class="btn-sm" data-bs-toggle="modal"
										  data-bs-target="#modal_create_entry">
								<i class="fa fa-plus"></i> Add Contact
							</x-jet-button>
						</div>
						@livewire('pbx-admin.phonebook-entries', ['phonebook' => $phonebook])
					</div> <!-- End Contacts Tab-->

					<!-- Ownership Tab-->
					<div class="tab-pane fade show" id="tab_ownership" role="tabpanel">

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="site_select">
								<span class="required">Site</span>
								<x-tooltip title="Required: The site this List is assigned to."/>
							</label>
							<select class="form-select select-two" aria-label="Select Site" name="site" id="site_select">
								@foreach($sites as $site)
									<option value="{{$site->id}}"
											@if($site->id == $phonebook->site_id) selected @endif>
										{{$site->name}}
									</option>
								@endforeach
							</select>
						</div>

					</div> <!-- End Ownership Tab -->

					<!-- Audit Tab-->
					<div class="tab-pane fade show" id="tab_audit" role="tabpanel">
						@livewire('pbx-admin.audits.phonebooks', ['auditableId' => $phonebook->id, 'auditableType' => 'App\Models\PbxAdmin\Phonebook'])
					</div> <!-- End Audit Tab -->

				</div>
				<input type="hidden" name="tab_id" id="tab_id" value="#tab_general">
			</form>
		</div>

		<div class="card-footer">
			<a href="{{route('pbxadmin.phonebooks.index')}}" class="btn btn-light-primary"><i
					class="fa fa-arrow-left"></i> Back</a>
			<a onclick="location.reload();" class="btn btn-light-info" data-bs-toggle="tooltip" tabindex="0"
			   title="Reloads the current page, discarding any changes you might have made.">
				<i class="fa fa-redo"></i> Discard Changes
			</a>
			<button type="button" class="btn btn-light-success" id="save-button" style="float:right">
				<i class="fa fa-check fw-bolder"></i>
				Save
			</button>
		</div>
	</div>

	@push('modals')
		@include('pbx-admin.phonebooks.partials.modals.create-phonebook-entry-modal')
		@include('pbx-admin.phonebooks.partials.modals.delete-phonebook-entry-modal')
		@include('pbx-admin.phonebooks.partials.modals.edit-phonebook-entry-modal')
	@endpush

	@push('scripts')
		@include('layouts.partials.scripts.return-to-tab-script')
		@include('pbx-admin.partials.scripts.tel-inputs-hidden-input-script')

		<script>
			$('#save-button').on('click', () => {
				markCustomerChangesPending('formulario');
			})

			$('.select-two').select2();

			function deleteEntry(entryID) {
				const modal = new bootstrap.Modal(document.getElementById('modal_delete_entry'), {keyboard: false});
				const form = document.getElementById('modal_form_delete_entry');

				form.action = `/pbxadmin/phonebook-entries/${entryID}`;

				modal.show();
			}

			function populateEditModal(id, label, phone){
				$('#edit_entry_id').val(id);
				$('#edit_entry_label').val(label);

				$('#edit_entry_phone_div .iti--laravel-tel-input').val(phone)
				$('#edit_entry_phone_value').val(phone);
				document.dispatchEvent(new Event('telDOMChanged'));
			}
		</script>
	@endpush

</x-app-layout>
