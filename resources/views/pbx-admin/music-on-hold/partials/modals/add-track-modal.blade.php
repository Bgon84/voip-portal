{{-- MODAL CREATE TRACK --}}
@if (Auth::user()->hasPermissionTo('pbxadmin.track.store'))
	<x-modal id="modal_create_track">
		<x-slot:title>
			Upload On-Hold Track
		</x-slot:title>

		<x-slot:content>
			<form action="{{route('pbxadmin.tracks.store')}}" method="POST" enctype="multipart/form-data"
				  id="modal-form">
				@csrf

				<div class="fv-row mb-10">

					<label class="d-flex align-items-center fs-5 fw-bold mb-2">
						<span class="required">Name / Label</span>
						<i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
						   title="Required: The name of this on-hold Track."
						   data-bs-original-title="Required: The name of this on-hold Track."
						   aria-label="Required: The name of this on-hold Track."></i>
					</label>
					<input id="name" class="form-control block mt-1 w-full" type="text" name="name" maxlength="128" required/>
				</div>

				<div class="fv-row mb-10">
					<label class="d-flex align-items-center fs-5 fw-bold mb-2">
						<span class="required">WAV File</span>
						<i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
						   title="Required: Select a file from your computer to upload."
						   data-bs-original-title="Required: Select a file from your computer to upload."
						   aria-label="Required: Select a file from your computer to upload."></i>
					</label>
					<input id="file" class="form-control block mt-1 w-full" type="file" name="file" required
						   accept=".wav, .mp3"/>
					<input type="hidden" name="album_id" value="{{$album->id}}"/>
				</div>
			</form>
		</x-slot:content>

		<x-slot:footer>
			<x-jet-secondary-button data-bs-dismiss="modal">
				{{ __('Close') }}
			</x-jet-secondary-button>
			<x-jet-button type="button" class="px-6" form="modal-form" id="submit-button-create-track"
						  onclick="markCustomerChangesPending('modal-form')">
				{{ __('Submit') }}
			</x-jet-button>
		</x-slot:footer>
	</x-modal>
@endif
{{-- END ADD TRACK MODAL--}}
