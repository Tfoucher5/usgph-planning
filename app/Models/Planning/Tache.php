<?php

namespace App\Models\Planning;

use App\Models\Admin\Lieu;
use App\Models\User;
use App\Traits\LogAction;
use App\Traits\WhoActs;
use Database\Factories\Planning\TacheFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property string $nom
 * @property int|null $lieu_id
 * @property int|null $user_id
 * @property string|null $heure_debut
 * @property string|null $heure_fin
 * @property int|null $jour Jour de la semaine : 1 pour lundi, 7 pour dimanche
 * @property \Illuminate\Support\Carbon $created_at
 * @property int $user_id_creation
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id_modification
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_id_suppression
 * @property int $categorie_id
 * @property-read \App\Models\Planning\Categorie $categorie
 * @property-read string $actions
 * @property-read Lieu|null $lieu
 * @property-read User|null $user
 * @property-read User $userCreation
 * @property-read User|null $userModification
 * @property-read User|null $userSuppression
 * @method static \Database\Factories\Planning\TacheFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereCategorieId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereHeureDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereHeureFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereJour($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereLieuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereUserIdCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereUserIdModification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache whereUserIdSuppression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tache withoutTrashed()
 * @mixin \Eloquent
 */
class Tache extends Model
{
    /**
     * @use HasFactory<TacheFactory>
     */
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

    protected $fillable = [
        'user_id',
        'nom',
        'heure_debut',
        'heure_fin',
        'jour',
        'lieu_id',
        'user_id_creation',
    ];

    /** @return string */
    public function getActionsAttribute(): string
    {
        return '';
    }

    /** @return BelongsTo<Lieu, $this>  */
    public function lieu()
    {
        return $this->belongsTo(Lieu::class);
    }

    /** @return BelongsTo<User, $this>  */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Categorie, $this> */
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }
}
