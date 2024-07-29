<script>
	$('#phone_number_switcher_customer_select').select2({
		dropdownParent: $('#modal_phone_number_switch')
	})

	$('#phone_number_switcher_select').select2({
		dropdownParent: $('#modal_phone_number_switch')
	})

	$('.phone-cust-select').on('change', function(){
		let cust_id = $(this).val();
		let this_id = $(this).attr('id');
		let phone_select_id = this_id.replace('_customer', '');

		getPhoneNumbers(cust_id, phone_select_id);
	})

	function getPhoneNumbers(customer_id, select_id) {
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$.ajax({
			type: "GET",
			url: "{{route('pbxuser.phone-number-switch.get')}}",
			data: {
				customer_id: customer_id,
				type: 'fax'
			},
			success: function (response) {
				response = JSON.parse(response);

				$('#' + select_id + ' option').each(function() {
					$(this).remove();
				})

				if(response.length > 0) {
					$.each(response, function (i, item) {
						$('#' + select_id).append($('<option>', {
							value: item.id,
							text : item.phone_number + ' ' + (item.name ?? ''),
						}));
					});
				} else {
					$('#' + select_id).append($('<option>', {
						value: "0",
						text : 'None Available',
					}));
				}
			}
		})
	}

</script>
