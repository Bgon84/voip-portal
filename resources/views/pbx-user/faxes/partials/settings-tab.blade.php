
<div class="accordion" id="faxSettings">
	<div class="accordion-item">
		<h2 class="accordion-header" id="headingOne">
			<button class="accordion-button fs-4 fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
					aria-expanded="true" aria-controls="collapseOne">
				Cover Page
			</button>
		</h2>
		<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
			 data-bs-parent="#faxSettings">
			<div class="accordion-body">
				@include('pbx-user.faxes.partials.cover-page-form')
			</div>
		</div>
	</div>
	<div class="accordion-item">
		<h2 class="accordion-header" id="headingTwo">
			<button class="accordion-button fs-4 fw-bold collapsed" type="button" data-bs-toggle="collapse"
					data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
				Fax to Email
			</button>
		</h2>
		<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faxSettings">
			<div class="accordion-body">
				<div class="mb-10 text-center">
					<x-jet-button type="button" class="btn-sm" data-bs-toggle="modal"
								  data-bs-target="#modal_add_recipient">
						<i class="fa fa-plus"></i> Add Recipient
					</x-jet-button>
				</div>
				<div class="mb-10">
					<livewire:pbx-user.efax-recipients :phone="$phone"/>
				</div>
			</div>
		</div>
	</div>
	<div class="accordion-item">
		<h2 class="accordion-header" id="headingThree">
			<button class="accordion-button fs-4 fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree"
					aria-expanded="false" aria-controls="collapseThree">
				Email to Fax
			</button>
		</h2>
		<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faxSettings">
			<div class="accordion-body">
				<h4>Coming Soon!</h4>
			</div>
		</div>
	</div>

</div>
