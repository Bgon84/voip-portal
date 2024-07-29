{{-- MODAL EDIT TRACK --}}
<x-modal id="modal_edit_track">
	<x-slot:title>
		Edit Track
	</x-slot:title>

	<x-slot:content>
		<form action="{{route('pbxadmin.tracks.update')}}" method="POST" enctype="multipart/form-data"
			  id="track_edit_form">
			@csrf

			<div class="fv-row mb-10">
				<label class="d-flex align-items-center fs-5 fw-bold mb-2">
					<span class="required">Name / Label</span>
					<i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
					   title="Required: The name of this on-hold Track."
					   data-bs-original-title="Required: The name of this on-hold Track."
					   aria-label="Required: The name of this on-hold Track."></i>
				</label>
				<input id="nameInput" class="form-control block mt-1 w-full" type="text" name="name" maxlength="128" required/>
			</div>

			<div class="fv-row mb-10">
				<label class="d-flex align-items-center fs-5 fw-bold mb-2">
					WAV File
					<i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
					   title="Select a file from your computer to upload."
					   data-bs-original-title="Select a file from your computer to upload."
					   aria-label="Select a file from your computer to upload."></i>
				</label>
				<input id="editFile" class="form-control block mt-1 w-full" type="file" name="editFile"
					   accept=".wav, .mp3"/>
				<input type="hidden" name="album_id" value="{{$album->id}}"/>
				<input type="hidden" name="track_id" id="track_id"/>
			</div>

		</form>
	</x-slot:content>

	<x-slot:footer>
		<x-jet-secondary-button data-bs-dismiss="modal">
			{{ __('Close') }}
		</x-jet-secondary-button>
		<x-jet-button type="button" class="px-6" form="track_edit_form" id="submit-button-edit-track"
					  onclick="markCustomerChangesPending('track_edit_form')">
			{{ __('Submit') }}
		</x-jet-button>
	</x-slot:footer>

</x-modal>
{{-- END EDIT TRACK MODAL--}}
