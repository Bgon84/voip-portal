<script>

	$('.fax-select-box').on('change', function(){
		let checked = getBulkActionIds().length;

		if(checked < 1){
			$('#bulk_actions_div').hide();
		} else {
			$('#bulk_actions_div').show();
		}
	})

	$('.checkbox-column').on('click', function(){
		if($('#select-type').val() === 'none'){
			$('.fax-select-box').prop("checked", true);
			$('#select-type').val('all')
			$('#bulk_actions_div').show();
			$('.fa-check-square').removeClass('fa').addClass('fa-regular')

		} else {
			$('.fax-select-box').prop("checked", false);
			$('#select-type').val('none')
			$('#bulk_actions_div').hide();
			$('.fa-check-square').removeClass('fa-regular').addClass('fa')
		}
	})

	function exportInbox(){
		let fax_ids = [];
		$('.fax-select-box').each(function(){
			fax_ids.push($(this).val());
		})
		$('#export_ids').val(fax_ids);
		$('#inbox_export_form').submit();
	}

	function deleteFax(faxID) {
		const modal = new bootstrap.Modal(document.getElementById('modal_delete_fax'), {keyboard: false});
		const form = document.getElementById('modal_form_delete_fax');

		form.action = `/pbxuser/faxes/inbox/${faxID}`;

		modal.show();
	}

	function deleteBulkFaxes() {
		let fax_ids = getBulkActionIds()
		$('#bulk_delete_fax_ids').val(fax_ids)
		$('#modal_delete_bulk_fax').modal('show');
	}

	function permDeleteFax(faxID) {
		const modal = new bootstrap.Modal(document.getElementById('modal_perm_delete_fax'), {keyboard: false});
		const form = document.getElementById('modal_form_perm_delete_fax');

		form.action = `/pbxuser/faxes/trash/perm-delete-incoming/${faxID}`;

		modal.show();
	}

	function permDeleteAllFaxes(phoneID) {
		const modal = new bootstrap.Modal(document.getElementById('modal_perm_delete_all_fax'), {keyboard: false});
		const form = document.getElementById('modal_form_delete_all_fax');

		form.action = `/pbxuser/faxes/trash/perm-delete-all-incoming/${phoneID}`;

		modal.show();
	}

	function bulkDownload(){
		let fax_ids = getBulkActionIds();
		$('#fax_ids').val(fax_ids);
		$('#bulk_download_form').submit();
	}

	function flagFax(fax_id, flag){
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$.ajax({
			type: "GET",
			url: "{{route('pbxuser.faxes.inbox.flag')}}",
			data: {
				fax_id : fax_id,
				flag : flag,
			},
			success: function (response) {
				response = JSON.parse(response);

				if (response.result === 'error') {
					toastr.error(response.message);
				}

				if (response.result === 'success') {
					toastr.success(response.message);
				}
			}
		})
	}

	function toggleMessageStatus(msg_id){
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$.ajax({
			type: "GET",
			url: "{{route('pbxuser.faxes.inbox.update-status')}}",
			data: {
				message_id : msg_id
			},
			success: function (response) {
				response = JSON.parse(response);

				if (response.result === 'error') {
					toastr.error(response.message);
				}

				if (response.result === 'success') {
					toastr.success(response.message);
				}
			}
		})
	}

	function toggleBulkMessagesStatus(status){
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		let fax_ids = getBulkActionIds();

		$.ajax({
			type: "GET",
			url: "{{route('pbxuser.faxes.inbox.bulk-update-status')}}",
			data: {
				status : status,
				fax_ids: fax_ids,
			},
			success: function (response) {
				response = JSON.parse(response);

				if (response.result === 'error') {
					toastr.error(response.message);
				}

				if (response.result === 'success') {
					toastr.success(response.message);
				}
				$('.fax-select-box').prop("checked", false);
			}
		})
	}

	function getBulkActionIds(){
		let fax_ids = [];
		$('.fax-select-box').each(function(){
			if($(this).is(':checked')){
				fax_ids.push($(this).val());
			}
		})
		return fax_ids;
	}
</script>
