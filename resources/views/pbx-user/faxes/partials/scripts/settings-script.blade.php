<script>
	$('#state_select').select2();

	function deleteRecipient(recipientID) {
		const modal = new bootstrap.Modal(document.getElementById('modal_delete_recipient'), {keyboard: false});
		const form = document.getElementById('modal_form_delete_recipient');
		form.action = `/pbxuser/faxes/settings/fax-recipients/${recipientID}`
		modal.show();
	}

	function populate_edit_efax_modal(id, email, outbound_confirms, attach_pdfs) {
		$('#edit_recipient_id').val(id);
		$('#edit_email_address').val(email);
		$('#edit_outbound_fax_confirmations').val(outbound_confirms);
		$('#edit_attach_pdf').val(attach_pdfs);

		$('#edit_outbound_fax_confirmations_checkbox').prop('checked', (outbound_confirms == 1));
		$('#edit_attach_pdf_checkbox').prop('checked', (attach_pdfs == 1));
	}
</script>
