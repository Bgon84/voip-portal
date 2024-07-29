<script>
	$('#tab_choose_link').on('show.bs.tab', function(){
		$('#phonebook_entry_select').prop('disabled', false);
		$('#fax_send_to_number_value').prop('disabled', true);
	})

	$('#tab_enter_link').on('show.bs.tab', function(){
		$('#phonebook_entry_select').prop('disabled', true);
		$('#fax_send_to_number_value').prop('disabled', false);
	})

	$('#phonebook_select').select2({
		dropdownParent: $('#modal_add_fax')
	})

	$('#phonebook_select').on('change', function(){
		getPhonebookEntries($(this).val());
	})

	function getPhonebookEntries(id){
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$.ajax({
			type: "GET",
			url: "{{route('pbxuser.faxes.get-phonebook-entries')}}",
			data: {
				phonebookId : id
			},
			success: function (response) {

				$('#phonebook_entry_select option').each(function() {
					$(this).remove();
				})

				if (response.length < 1) {
					$('#phonebook_entry_select').append($('<option>', {
						value: 0,
						text: 'No items to display'
					}));
				} else {
					$.each(response, function (i, item) {
						$('#phonebook_entry_select').append($('<option>', {
							value: item['phone_number'],
							text: item['label'] + ': ' + item['phone_number'],
						}));
					});
				}
				$('#phonebook_entry_select').select2({
					dropdownParent: $('#modal_add_fax')
				})
				$('#phonebook_entry_div').show();
			}
		})
	}

	function clearTelInput(){
		$('input[data-phone-input-id=fax_send_to_number]').val('');
		$('#fax_send_to_number').val('');
		$('#fax_send_to_number_value').val('')
	}
</script>
