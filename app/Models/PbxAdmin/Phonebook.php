<?php

namespace App\Models\PbxAdmin;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Contracts\Auditable;

class Phonebook extends Model implements Auditable
{
	use HasFactory;
	use HasUuids;
	use SoftDeletes;
	use \OwenIt\Auditing\Auditable;

	protected $fillable = [
		'site_id',
		'name',
		'description',
		'notes',
	];

	public function site(): BelongsTo
	{
		return $this->belongsTo(Site::class);
	}

	public function entries(): HasMany
	{
		return $this->hasMany(PhonebookEntry::class);
	}

	public function transformAudit(array $data): array
	{
		if (Arr::has($data, 'new_values.site_id') || Arr::has($data, 'old_values.site_id')) {

			if(!is_null($this->getOriginal('site_id'))){
				$data['old_values']['site'] = Site::find($this->getOriginal('site_id'))->name;
			}
			$data['new_values']['site'] = Site::find($this->getAttribute('site_id'))->name;

			unset($data['old_values']['site_id']);
			unset($data['new_values']['site_id']);
		}

		return $data;
	}
}
