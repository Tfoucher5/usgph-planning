<?php

namespace App\Models\Planning;

use App\Traits\LogAction;
use Database\Factories\Planning\CategorieFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property string $nom
 * @property string $couleur
 * @property \Illuminate\Support\Carbon $created_at
 * @property int $user_id_creation
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id_modification
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_id_suppression
 * @property-read string $actions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Planning\Tache> $taches
 * @property-read int|null $taches_count
 * @method static \Database\Factories\Planning\CategorieFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereCouleur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereUserIdCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereUserIdModification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie whereUserIdSuppression($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categorie withoutTrashed()
 * @mixin \Eloquent
 */
class Categorie extends Model
{
    /**
     * @use HasFactory<CategorieFactory>
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
     * Summary of taches
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Tache, $this>
     */
    public function taches()
    {
        return $this->hasMany(Tache::class);
    }
}
