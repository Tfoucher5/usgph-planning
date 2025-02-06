<?php

namespace App\Models\Admin;

use App\Models\Planning\Planning;
use App\Models\User;
use App\Traits\LogAction;
use App\Traits\WhoActs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int $planning_id
 * @property int $user_id
 * @property string $nom
 * @property string $lieu
 * @property string $plannifier_le
 * @property string $heure_debut
 * @property string $heure_fin
 * @property string $duree_tache
 * @property \Illuminate\Support\Carbon $created_at
 * @property int $user_id_creation
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id_modification
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_id_suppression
 * @property string $categorie_nom
 * @property string $categorie_couleur
 * @property-read string $actions
 * @property-read Planning $planning
 * @property-read User $user
 * @property-read User $userCreation
 * @property-read User|null $userModification
 * @property-read User|null $userSuppression
 * @method static \Database\Factories\Admin\ArchiveFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereCategorieCouleur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereCategorieNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereDureeTache($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereHeureDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereHeureFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereLieu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive wherePlannifierLe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive wherePlanningId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereUserIdCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereUserIdModification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive whereUserIdSuppression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Archive withoutTrashed()
 * @mixin \Eloquent
 */
class Archive extends Model
{
    /** @use HasFactory<\Database\Factories\Admin\ArchiveFactory> */
    use HasFactory;

    use LogAction;
    use SoftDeletes;
    use WhoActs;

    /**
     * @var list<string>
     */
    protected $appends = [
        'actions',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'planning_id',
        'user_id',
        'nom',
        'lieu',
        'plannifier_le',
        'heure_debut',
        'heure_fin',
        'duree_tache',
        'user_id_creation',
    ];

    /**
     * Get the actions attribute.
     *
     * @return string
     */
    public function getActionsAttribute(): string
    {
        return 'Task ID: ' . $this->planning_id . ', User: ' . $this->user_id;
    }

    /** @return BelongsTo<Planning, $this> */
    public function planning()
    {
        return $this->belongsTo(Planning::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
