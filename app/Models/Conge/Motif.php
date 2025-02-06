<?php

namespace App\Models\Conge;

use App\Traits\LogAction;
use Database\Factories\Conge\MotifFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property string $nom
 * @property string $cannot_delete
 * @property \Illuminate\Support\Carbon $created_at
 * @property int $user_id_creation
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id_modification
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_id_suppression
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conge\Absence> $absences
 * @property-read int|null $absences_count
 * @property-read string $actions
 * @method static \Database\Factories\Conge\MotifFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereCannotDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereUserIdCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereUserIdModification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif whereUserIdSuppression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Motif withoutTrashed()
 * @mixin \Eloquent
 */
class Motif extends Model
{
    /**
     * @use HasFactory<MotifFactory>
     */
    use HasFactory;

    use LogAction;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $appends = [
        'actions',
    ];

    /** @return string  */
    public function getActionsAttribute()
    {
        return '';
    }

    /**
     * Summary of absences
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Absence, $this>
     */
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}
