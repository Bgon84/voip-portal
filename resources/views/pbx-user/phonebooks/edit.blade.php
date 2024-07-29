<x-app-layout :section="$section" :title="$title" :breadcrumbs="[[route('pbxuser.phonebooks.index'), 'Phonebooks'], 'Editing ' . $phonebook->name]">

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
					   href="#tab_general">Information</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_contacts_link"
					   href="#tab_contacts">Contacts</a>
				</li>
			</ul>

			<div class="tab-content" id="myTabContent">

				{{-- General Tab --}}
				<div class="tab-pane fade show active" id="tab_general" role="tabpanel">

					<div class="mb-10">
						<h4>Name</h4>
						<p>{{$phonebook->name}}</p>
					</div>

					<div class="mb-10">
						<h4>Description</h4>
						<p>{{$phonebook->description}}</p>
					</div>

					<div class="fv-row mb-10">
						<h4>Notes</h4>
						<p>{{$phonebook->notes}}</p>
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
					<livewire:pbx-admin.phonebook-entries :phonebook="$phonebook"/>
				</div> <!-- End Contacts Tab-->

			</div>
		</div>

		<div class="card-footer">
			<a href="{{route('pbxuser.phonebooks.index')}}" class="btn btn-light-primary">
				<i class="fa fa-arrow-left"></i>
				Back
			</a>
		</div>
	</div>

	@push('modals')
		@include('pbx-user.phonebooks.partials.modals.create-phonebook-entry-modal')
		@include('pbx-user.phonebooks.partials.modals.delete-phonebook-entry-modal')
		@include('pbx-user.phonebooks.partials.modals.edit-phonebook-entry-modal')
	@endpush

	@push('scripts')
		@include('pbx-admin.partials.scripts.tel-inputs-hidden-input-script')
		@include('layouts.partials.scripts.return-to-tab-script')

		<script>
			$('.form-select').select2();

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
