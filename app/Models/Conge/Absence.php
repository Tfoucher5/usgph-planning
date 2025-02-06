<?php

namespace App\Models\Conge;

use App\Enums\ValidationStatus;
use App\Models\User;
use App\Traits\LogAction;
use Database\Factories\Conge\AbsenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property int $motif_id
 * @property string $date_debut
 * @property string $date_fin
 * @property ValidationStatus $status
 * @property float $nb_of_work_days
 * @property \Illuminate\Support\Carbon $created_at
 * @property int $user_id_creation
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id_modification
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_id_suppression
 * @property-read string $actions
 * @property-read \App\Models\Conge\Motif $motif
 * @property-read User $user
 * @method static \Database\Factories\Conge\AbsenceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereDateDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereDateFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereMotifId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereNbOfWorkDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereUserIdCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereUserIdModification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereUserIdSuppression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence withoutTrashed()
 * @mixin \Eloquent
 */
class Absence extends Model
{
    /**
     * @use HasFactory<AbsenceFactory>
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

    protected $fillable = [
        'user_id',
        'motif_id',
        'date_debut',
        'date_fin',
        'status',
        'user_id_modification',
    ];

    /**
     * Summary of casts
     *
     * @var array<string, string>
     */
    protected $casts = ['status' => ValidationStatus::class];

    /** @return string  */
    public function getActionsAttribute()
    {
        return '';
    }

    /**
     * Summary of user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Summary of motif
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Motif, $this>
     */
    public function motif()
    {
        return $this->belongsTo(Motif::class);
    }
}
