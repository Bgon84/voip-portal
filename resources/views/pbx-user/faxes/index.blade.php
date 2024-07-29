<x-app-layout :section="$section" :title="$title" :subtitle="$subtitle">
	<div>
		<div class="card shadow-sm">
			<div class="card-header">
				<h2 class="mt-10">
					Fax Number: {{formatPhoneNumber($phone->phone_number)}}
				</h2>

				<div class="card-toolbar">
					@if(!$auth_user->isUserLevelUser() || count($userPerms['phone_ids']) > 1)
						<x-jet-button class="btn-sm btn-info" data-bs-toggle="modal"
									  data-bs-target="#modal_phone_number_switch">
							Choose Fax Line
						</x-jet-button>
					@endif
					&nbsp; &nbsp;
					@if($canSendFax === 'true')
						<x-jet-button class="btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal_add_fax">
							Send Fax
						</x-jet-button>
					@endif
				</div>
			</div>

			<div class="card-body">
				<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-bold mb-8" id="tabMenu">
					<li class="nav-item">
						<a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" id="tab_inbox_link"
						   href="#tab_inbox">Inbox</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_outbox_link"
						   href="#tab_outbox">Outbox</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_trash_link"
						   href="#tab_trash">Trash</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_fax_link"
						   href="#tab_fax">Settings</a>
					</li>
				</ul>

				<div class="tab-content" id="myTabContent">

					{{-- Inbox Tab --}}
					<div class="tab-pane fade show active" id="tab_inbox" role="tabpanel">
						<livewire:pbx-user.fax-inbox :phoneId="$phone->id"/>

						<button type="button" class="btn btn-light-primary mt-5" style="float:right;" onclick="exportInbox()">
							<i class="fa fa-download"></i> Export
						</button>
						<form action="{{route('pbxuser.faxes.inbox.export')}}" method="POST" id="inbox_export_form">
							@csrf
							<input type="hidden" id="export_ids" name="export_ids"/>
							<input type="hidden" name="export_phone_id" id="export_phone_id" value="{{$phone->id}}">
						</form>
					</div>
					{{-- End Inbox Tab --}}

					{{-- Outbox Tab --}}
					<div class="tab-pane fade show" id="tab_outbox" role="tabpanel">
						<livewire:pbx-user.fax-outbox :phoneId="$phone->id"/>

						<form action="{{route('pbxuser.faxes.outbox.export')}}" method="POST" id="outbox_export_form">
							@csrf
							<input type="hidden" id="outbox_export_ids" name="outbox_export_ids"/>
							<input type="hidden" name="outbox_export_phone_id" id="outbox_export_phone_id" value="{{$phone->id}}">
						</form>
						<button type="button" class="btn btn-light-primary mt-5" style="float:right;" onclick="exportOutbox()">
							<i class="fa fa-download"></i> Export
						</button>
					</div>
					{{-- End Outbox Tab --}}

					{{-- Trash Tab --}}
					<div class="tab-pane fade show" id="tab_trash" role="tabpanel">
						<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-bold mb-8" id="tabMenu">
							<li class="nav-item">
								<a class="nav-link active" data-bs-toggle="tab" data-toggle="tab" id="tab_received_link"
								   href="#tab_received">Received</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-bs-toggle="tab" data-toggle="tab" id="tab_sent_link"
								   href="#tab_sent">Sent</a>
							</li>
						</ul>
						<div class="tab-content" id="trashTabContent">
							<div class="tab-pane fade show active" id="tab_received" role="tabpanel">
								<livewire:pbx-user.fax-inbox-trash :phoneId="$phone->id"/>
							</div>
							<div class="tab-pane fade show" id="tab_sent" role="tabpanel">
								<livewire:pbx-user.fax-outbox-trash :phoneId="$phone->id"/>
							</div>
						</div>
					</div>
					{{-- End Trash Tab --}}

					{{-- Settings Tab --}}
					<div class="tab-pane fade show" id="tab_fax" role="tabpanel">
						@include('pbx-user.faxes.partials.settings-tab')
					</div>
					{{-- End Settings Tab --}}
				</div>
			</div>
		</div>
	</div>

	@push('modals')
		@include('pbx-user.faxes.partials.modals.fax-line-switcher-modal')
		@include('pbx-user.faxes.partials.modals.delete-fax-modal')
		@include('pbx-user.faxes.partials.modals.create-fax-modal')
		@include('pbx-user.faxes.partials.modals.export-fax-inbox-modal')
		@include('pbx-user.faxes.partials.modals.export-fax-outbox-modal')
		@include('pbx-user.faxes.partials.modals.perm-delete-fax-modal')
		@include('pbx-user.faxes.partials.modals.create-efax-recipient-modal')
		@include('pbx-user.faxes.partials.modals.delete-efax-recipient-modal')
		@include('pbx-user.faxes.partials.modals.edit-efax-recipient-modal')
		@include('pbx-user.faxes.partials.modals.delete-bulk-faxes-modal')
		@include('pbx-user.faxes.partials.modals.perm-delete-all-faxes-modal')
	@endpush

	@push('scripts')
		@include('pbx-user.faxes.partials.scripts.phone-number-switcher-script')
		@include('pbx-user.faxes.partials.scripts.inbox-script')
		@include('pbx-user.faxes.partials.scripts.outbox-script')
		@include('pbx-user.faxes.partials.scripts.create-fax-script')
		@include('pbx-user.faxes.partials.scripts.settings-script')
		@include('pbx-admin.partials.scripts.tel-inputs-hidden-input-script')
		@include('layouts.partials.scripts.return-to-tab-script')
	@endpush
</x-app-layout>
