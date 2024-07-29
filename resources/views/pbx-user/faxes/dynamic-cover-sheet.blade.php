<style>
	* {
		font-size: 24px;
	}
	h1 {
		font-size: 36px;
	}
	table, th, td {
		border: 1px solid;
	}
	th, td {
		padding: 5px;
	}
	table {
		border: 1px solid black;
		border-collapse: collapse;
		width: 100%;
	}
	td, div {
		text-align: center;
	}
</style>

<div>
	<h1>Fax Cover Sheet</h1>

	<table style="">
		<tr>
			<td><b>To:</b> {{$destNumber}}</td>
		</tr>
		<tr>
			<td>
				<b>From:</b> <br/>
				{{$company_name}} <br/>
				{{$address_line_1}} {{$address_line_2}} <br/>
				{{$address_city}}@if($address_city !== '' && $address_state !== ''), @endif{{$address_state}} {{$address_zip}} <br/>
				{{$email}} <br>
				{{$website}}
			</td>
		</tr>
		<tr>
			<td>
				<b>Re:</b> {{$subject}}
			</td>
		</tr>
	</table>
</div>
