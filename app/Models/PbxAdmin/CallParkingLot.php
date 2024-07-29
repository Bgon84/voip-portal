<?php

namespace App\Models\PbxAdmin;

use App\Constants\Constants;
use App\Http\Controllers\PbxAdmin\DestinationController;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Contracts\Auditable;
/**
 * @method static Builder select( string|array $column, string ...$columns )
 */
class CallParkingLot extends Model implements Auditable
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
        'ext_number',
        'number_of_slots',
        'timeout_seconds',
        'dest_timeout_enum',
        'dest_timeout_id',
		'dest_overflow_enum',
        'dest_overflow_id',
        'tags_cdr',
        'moh_album_id',
        'webhook_id',
    ];

    public function site(): BelongsTo
    {
        return $this->BelongsTo(Site::class, 'site_id');
    }

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'new_values.site_id') || Arr::has($data, 'old_values.dest_enum')) {

            if(!is_null($this->getOriginal('site_id'))){
                $data['old_values']['site'] = Site::find($this->getOriginal('site_id'))->name;
            }
            $data['new_values']['site'] = Site::find($this->getAttribute('site_id'))->name;

            unset($data['old_values']['site_id']);
            unset($data['new_values']['site_id']);
        }

        if (Arr::has($data, 'new_values.dest_timeout_enum') || Arr::has($data, 'old_values.dest_timeout_enum')) {

            $data['old_values']['destination_type'] = $this->getOriginal('dest_timeout_enum') == 0
                ? 'Default Action (Ring Back to Original Parker)': Constants::destinations[$this->getOriginal('dest_timeout_enum')];
            $data['new_values']['destination_type'] = $this->getAttribute('dest_timeout_enum') == 0
                ? 'Default Action (Ring Back to Original Parker)' : Constants::destinations[$this->getAttribute('dest_timeout_enum')];

            unset($data['old_values']['dest_timeout_enum']);
            unset($data['new_values']['dest_timeout_enum']);
        }

        if (Arr::has($data, 'new_values.dest_timeout_id') || Arr::has($data, 'old_values.dest_timeout_id')) {
            $data['old_values']['destination'] = DestinationController::getCurrentDestination(
                $this->getOriginal('dest_timeout_enum') ?? 0, $this->getOriginal('dest_timeout_id') ?? '');
            $data['new_values']['destination'] = DestinationController::getCurrentDestination(
                $this->getAttribute('dest_timeout_enum') ?? 0, $this->getAttribute('dest_timeout_id') ?? '');

            unset($data['old_values']['dest_timeout_id']);
            unset($data['new_values']['dest_timeout_id']);
        }

        if (Arr::has($data, 'new_values.tags_cdr') || Arr::has($data, 'old_values.tags_cdr')) {
            $data['old_values']['tags'] = formatCdrTags($this->getOriginal('tags_cdr') ?? '');
            $data['new_values']['tags'] = formatCdrTags($this->getAttribute('tags_cdr') ?? '');

            unset($data['old_values']['tags_cdr']);
            unset($data['new_values']['tags_cdr']);
        }

        if (Arr::has($data, 'new_values.moh_album_id') || Arr::has($data, 'old_values.moh_album_id')) {
            $data['old_values']['moh_album'] = !is_null($this->getOriginal('moh_album_id')) ? MohAlbum::find($this->getOriginal('moh_album_id'))->name : '';
            $data['new_values']['moh_album'] = !is_null($this->getAttribute('moh_album_id')) ? MohAlbum::find($this->getAttribute('moh_album_id'))->name: '';

            unset($data['old_values']['moh_album_id']);
            unset($data['new_values']['moh_album_id']);
        }

        if (Arr::has($data, 'new_values.webhook_id') || Arr::has($data, 'old_values.webhook_id')) {
            $data['old_values']['webhook'] = !is_null($this->getOriginal('webhook_id')) ? Webhook::find($this->getOriginal('webhook_id'))->name : '';
            $data['new_values']['webhook'] = !is_null($this->getAttribute('webhook_id')) ? Webhook::find($this->getAttribute('webhook_id'))->name : '';

            unset($data['old_values']['webhook_id']);
            unset($data['new_values']['webhook_id']);
        }

        return $data;
    }
}
