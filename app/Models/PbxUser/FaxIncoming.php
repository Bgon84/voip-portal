<?php

namespace App\Models\PbxUser;

use App\Models\PhoneNumber\PhoneNumber;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FaxIncoming extends Model implements Auditable
{
	use HasFactory;
	use HasUuids;
	use SoftDeletes;
	use \OwenIt\Auditing\Auditable;

	protected $table = 'fax_inbox';

	protected $fillable = [
		'phone_number_id',
		'date_received',
		'datetime_read',
		'callerid_name',
		'callerid_number',
		'pages',
		"flagged",
		'file_name',
	];

	public function phoneNumber(): BelongsTo
	{
		return $this->BelongsTo(PhoneNumber::class);
	}
}
