<?php

namespace App\Models\PbxAdmin;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MohAlbumTrack extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'moh_album_id',
        'file_name'
    ];

    public function album() : HasOne
    {
        return $this->hasOne(MohAlbum::class, 'moh_album_id');
    }

    public function transformAudit(array $data): array
    {
        $data['old_values']['track_id'] = $this->getAttribute('id');
        $data['new_values']['track_id'] = $this->getAttribute('id');

        return $data;
    }
}
