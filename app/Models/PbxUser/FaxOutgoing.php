<?php

namespace App\Models\PbxUser;

use App\Models\PhoneNumber\PhoneNumber;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FaxOutgoing extends Model implements Auditable
{
	use HasFactory;
	use HasUuids;
	use SoftDeletes;
	use \OwenIt\Auditing\Auditable;

	protected $table = 'fax_outbox';

	protected $fillable = [
		'phone_number_id',
		'destination_phone_number',
		'cover_page_dynamic_subject',
		'file_name_pdf',
		'file_name_tiff',
		'date_started',
		'date_completed',
		'status',
		'number_of_retries',
		'retry_last_date',
		'retry_last_error',
	];

	public function phoneNumber(): BelongsTo
	{
		return $this->BelongsTo(PhoneNumber::class);
	}
}
