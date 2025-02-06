<?php

namespace App\Models\Admin;

use App\Models\Planning\Planning;
use App\Models\Planning\Tache;
use App\Traits\LogAction;
use App\Traits\WhoActs;
use Database\Factories\Admin\LieuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property string $nom
 * @property \Illuminate\Support\Carbon $created_at
 * @property int $user_id_creation
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id_modification
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_id_suppression
 * @property-read string $actions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Planning> $plannings
 * @property-read int|null $plannings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tache> $taches
 * @property-read int|null $taches_count
 * @property-read \App\Models\User $userCreation
 * @property-read \App\Models\User|null $userModification
 * @property-read \App\Models\User|null $userSuppression
 * @method static \Database\Factories\Admin\LieuFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereUserIdCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereUserIdModification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu whereUserIdSuppression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lieu withoutTrashed()
 * @mixin \Eloquent
 */
class Lieu extends Model
{
    /**
     * @use HasFactory<LieuFactory>
     */
    use HasFactory;

    use LogAction;
    use SoftDeletes;
    use WhoActs;

    protected $fillable = [
        'nom',
        'user_id_creation',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'actions',
    ];

    protected $table = 'lieux';

    /** @return string  */
    public function getActionsAttribute(): string
    {
        return 'Lieu: ' . $this->nom . ', Created by User ID: ' . $this->user_id_creation;
    }

    /**
     * Summary of taches
     *
     * @return HasMany<Tache, $this>
     */
    public function taches()
    {
        return $this->hasMany(Tache::class);
    }

    /**
     * Summary of plannings
     *
     * @return HasMany<Planning, $this>
     */
    public function plannings()
    {
        return $this->hasMany(Planning::class);
    }
}
