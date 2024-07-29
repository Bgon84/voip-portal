<?php

namespace App\Models\PbxAdmin;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PhonebookEntry extends Model implements Auditable
{
	use HasFactory;
	use HasUuids;
	use SoftDeletes;
	use \OwenIt\Auditing\Auditable;

	protected $fillable = [
		'phonebook_id',
		'label',
		'phone_number',
	];

	public function phonebook(): BelongsTo
	{
		return $this->belongsTo(Phonebook::class);
	}

	public function transformAudit(array $data): array
	{
		$data['old_values']['entry_id'] = $this->getAttribute('id');
		$data['new_values']['entry_id'] = $this->getAttribute('id');

		return $data;
	}
}
