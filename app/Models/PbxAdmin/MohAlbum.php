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

class MohAlbum extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'notes',
        'site_id',
		'streaming_url'
    ];

    public function site(): BelongsTo
    {
        return $this->BelongsTo(Site::class, 'site_id');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(MohAlbumTrack::class, 'moh_album_id');
    }

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'new_values.site_id') || Arr::has($data, 'old_values.site_id')) {

            $data['old_values']['site'] = !is_null($this->getOriginal('site_id'))
                ? Site::find($this->getOriginal('site_id'))->name : '';

            $data['new_values']['site'] = !is_null($this->getAttribute('site_id'))
                ? Site::find($this->getAttribute('site_id'))->name : '';

            unset($data['old_values']['site_id']);
            unset($data['new_values']['site_id']);
        }

        return $data;
    }
}
