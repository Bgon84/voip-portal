<x-app-layout :section="$section" :title="$title" :breadcrumbs="[[route('pbxadmin.albums.index'), 'Albums'], 'Editing ' . $album->name]">

	<div class="card shadow-sm">
		<div class="card-header">
			<h3 class="card-title fw-bold">
				Editing
				{{$album->name}}
			</h3>
		</div>

		<div class="card-body">

			<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-bold mb-8" id="tabMenu">
				<li class="nav-item">
					<a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" id="tab_general_link"
					   href="#tab_general">General</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_tracks_link"
					   href="#tab_tracks">Tracks</a>
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

			<form action="{{route('pbxadmin.albums.update', ['album' => $album])}}" method="POST" id="formulario"
				  enctype="multipart/form-data">
				@csrf

				<div class="tab-content" id="myTabContent">

					{{-- General Tab --}}
					<div class="tab-pane fade show active" id="tab_general" role="tabpanel">

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="name">
								<span class="required">Name</span>
								<x-tooltip title="Required: The Album name."/>
							</label>
							<input type="text"
								   class="form-control form-control-lg"
								   name="name"
								   id="name"
								   maxlength="64"
								   placeholder="Name"
								   value="{{$album->name}}"
								   required>
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="description">
								Description
								<x-tooltip title="A description of this Album."/>
							</label>
							<input type="text"
								   class="form-control form-control-lg"
								   name="description"
								   id="description"
								   maxlength="256"
								   placeholder="Description"
								   value="{{$album->description}}">
						</div>

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="notes">
								Notes
								<x-tooltip title="Notes about this Album for other technicians and admins."/>
							</label>
							<textarea class="form-control form-control-lg" rows="3" name="notes" id="notes"
									  maxlength="1024" placeholder="Notes">{{$album->notes}}</textarea>
						</div>
						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="streaming_url">
							Streaming URL
								<x-tooltip title="Instead of uploading music files, enter a streaming URL here.
									Uploaded tracks will be ignored if you supply a streaming URL."/>
							</label>
							<input type="text"
								   class="form-control form-control-lg"
								   name="streaming_url"
								   id="streaming_url"
								   maxlength="2048"
								   placeholder="Streaming URL"
								   value="{{$album->streaming_url}}">
						</div>

					</div> <!-- End General Tab-->

					<!-- Tracks Tab -->
					<div class="tab-pane fade show" id="tab_tracks" role="tabpanel">
						@if(!is_null($album->streaming_url))
							<div class="fv-row mb-10 text-center">
								<span class="badge bg-danger" style="font-size: 1.25em">
									This Album has a Streaming URL defined.
									<br>
									All uploaded Tracks will be ignored.
								</span>
							</div>
						@endif
						<div class="fv-row mb-10 text-center">
							<x-jet-button type="button" class="btn-sm" data-bs-toggle="modal"
										  data-bs-target="#modal_create_track">
								<i class="fa fa-plus"></i> Upload Track
							</x-jet-button>
						</div>
						<div class="fv-row mb-10">
							@livewire('pbx-admin.moh-tracks', ['album' => $album])
						</div>
					</div>
					<!-- End Tracks Tab -->

					<!-- Ownership Tab-->
					<div class="tab-pane fade show" id="tab_ownership" role="tabpanel">

						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2" for="headerValue">
								<span class="required">Site</span>
								<x-tooltip title="Required: The site this is Album assigned to."/>
							</label>
							<select class="form-select" aria-label="Select Site" name="site_id" id="site-select">
								@foreach($sites as $site)
									<option value="{{$site->id}}"
											@if($site->id == $album->site_id) selected @endif>{{$site->name}}</option>
								@endforeach
							</select>
						</div>
						<div class="fv-row mb-10">
							<label class="d-flex align-items-center fs-5 fw-bold mb-2">
								Dependencies ({{$dependencies['count']}})
								<x-tooltip title="This tree will show you where this object is being used in the phone system.."/>
							</label>
							@include('pbx-admin.partials.dependency-tree')
						</div>
					</div> <!-- End Ownership Tab -->

					<!-- Audit Tab-->
					<div class="tab-pane fade show" id="tab_audit" role="tabpanel">
						@livewire('pbx-admin.audits.moh-albums', ['auditableId' => $album->id, 'auditableType' => 'App\Models\PbxAdmin\MohAlbum'])
					</div> <!-- End Audit Tab -->

				</div>
				<input type="hidden" name="tab_id" id="tab_id" value="#tab_general">
			</form>
		</div>

		<div class="card-footer">
			<a href="{{route('pbxadmin.albums.index')}}" class="btn btn-light-primary"><i
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

    @push('modals')
		@include('pbx-admin.music-on-hold.partials.modals.add-track-modal')
		@include('pbx-admin.music-on-hold.partials.modals.edit-track-modal')
		@include('pbx-admin.music-on-hold.partials.modals.delete-track-modal')
    @endpush

	@push('scripts')
		@include('layouts.partials.scripts.return-to-tab-script')
		@include('pbx-admin.partials.scripts.initialize-dependency-tree-script')

		<script>
			$('#save-button').on('click', () => {
				markCustomerChangesPending('formulario');
			})

			function deleteTrack(trackID) {
				const modal = new bootstrap.Modal(document.getElementById('model_delete_track'), {keyboard: false});
				const form = document.getElementById('modal_form_delete_track');
				form.action = `/pbxadmin/track/${trackID}`
				modal.show();
			}

			function populate_modal(trackID, name) {
				$('#nameInput').val(name);
				$('#track_id').val(trackID);
			}

			$('button.page-link').attr('type', 'button');

			Livewire.on('render', function () {
				$('button.page-link').attr('type', 'button');
			})

			$('#site-select').select2();
		</script>
	@endpush
</x-app-layout>




