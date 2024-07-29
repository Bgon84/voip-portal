<script>
	function exportOutbox(){
		let fax_ids = [];

		$('.outbox_id').each(function(){
			fax_ids.push($(this).val());
		})

		$('#outbox_export_ids').val(fax_ids);
		$('#outbox_export_form').submit();
	}

	function deleteOutboxFax(faxID) {
		const modal = new bootstrap.Modal(document.getElementById('modal_delete_fax'), {keyboard: false});
		const form = document.getElementById('modal_form_delete_fax');

		form.action = `/pbxuser/faxes/outbox/delete/${faxID}`;

		modal.show();
	}

	function permDeleteOutboxFax(faxID) {
		const modal = new bootstrap.Modal(document.getElementById('modal_perm_delete_fax'), {keyboard: false});
		const form = document.getElementById('modal_form_perm_delete_fax');

		form.action = `/pbxuser/faxes/trash/perm-delete-outgoing/${faxID}`;

		modal.show();
	}

	function permDeleteAllOutboxFaxes(phoneID) {
		const modal = new bootstrap.Modal(document.getElementById('modal_perm_delete_all_fax'), {keyboard: false});
		const form = document.getElementById('modal_form_delete_all_fax');

		form.action = `/pbxuser/faxes/trash/perm-delete-all-outgoing/${phoneID}`;

		modal.show();
	}
</script>
