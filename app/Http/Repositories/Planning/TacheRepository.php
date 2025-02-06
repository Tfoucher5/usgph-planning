<?php

namespace App\Http\Repositories\Planning;

use App\Models\Planning\Tache;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TacheRepository
{
    /**
     * @var Tache
     */
    protected $tache;

    /**
     * Constructor
     *
     * @param  Tache  $tache
     */
    public function __construct(Tache $tache)
    {
        $this->tache = $tache;
    }

    /**
     * Store a new model instance
     *
     * @param  array<mixed>  $inputs
     *
     * @return Tache
     */
    public function store(array $inputs): Tache
    {
        $tache = new $this->tache();
        $tache->user_id_creation = (int) Auth::id();

        $this->save($tache, $inputs);

        return $tache;
    }

    /**
     * Update the model instance
     *
     * @param  Tache  $tache
     * @param  array<mixed>  $inputs
     *
     * @return Tache
     */
    public function update(Tache $tache, array $inputs): Tache
    {
        $tache->user_id_modification = (int) Auth::id();

        $this->save($tache, $inputs);

        return $tache;
    }

    /**
     * Delete the model instance
     *
     * @param  Tache  $tache
     *
     * @return bool|null
     */
    public function destroy(Tache $tache)
    {
        $tache->user_id_suppression = (int) Auth::id();
        $tache->save();

        return $tache->delete();
    }

    /**
     * Get tasks for a given user
     *
     * @param  User  $user
     *
     * @return array<int, array<string, mixed>> La collection de tâches formatées pour le calendrier
     */
    public function getTachesByUser(User $user): array
    {
        $plannings = Tache::with(['lieu', 'user'])
            ->where('user_id', $user->id)
            ->get();

        return $this->mapTachesForCalendar($plannings);
    }

    /**
     * Map des tâches pour le calendrier
     *
     * @param  Collection<int, Tache>  $plannings  Collection d'objets Tache
     *
     * @return array<int, array<string, mixed>> Retourne un tableau formaté pour le calendrier
     */
    public function mapTachesForCalendar(Collection $plannings): array
    {
        return $plannings->map(function (Tache $tache) {
            $jourSemaine = $tache->jour;

            $dateReference = Carbon::now()->startOfWeek();

            $dateDebut = $dateReference->copy()->addDays($jourSemaine - 1);
            $dateFin = $dateDebut->copy();

            $dateDebut->setTimeFromTimeString($tache->heure_debut);
            $dateFin->setTimeFromTimeString($tache->heure_fin);

            return [
                'id' => $tache->id,
                'title' => $tache->nom,
                'start' => $dateDebut->toIso8601String(),
                'end' => $dateFin->toIso8601String(),
                'location' => $tache->lieu->nom ?? 'Lieu non spécifié',
                'user' => $tache->user->identity ?? 'Utilisateur non spécifié',
                'userId' => $tache->user->id,
                'description' => $tache->description ?? '',
            ];
        })->toArray();
    }

    // /**
    //  * Undelete the model instance
    //  *
    //  * @param  Tache  $tache
    //  *
    //  * @return void
    //  */
    // public function undelete(Tache $tache)
    // {
    //     $tache->restore();
    // }

    // /**
    //  * Return a JSON for index datatable
    //  *
    //  * @return string|false|void — a JSON encoded string on success or FALSE on failure
    //  */
    // public function json()
    // {
    //     return json_encode(
    //         Tache::all()
    //     );
    // }

    /**
     * Save the model instance
     *
     * @param  Tache  $tache
     * @param  array<mixed>  $inputs
     *
     * @return Tache
     */
    private function save(Tache $tache, array $inputs): Tache
    {
        if (isset($inputs['nom'])) {
            $tache->nom = $inputs['nom'];
        }
        if (isset($inputs['lieu_id'])) {
            $tache->lieu_id = $inputs['lieu_id'];
        }
        if (isset($inputs['user_id'])) {
            $tache->user_id = $inputs['user_id'];
        }
        if (isset($inputs['categorie_id'])) {
            $tache->categorie_id = $inputs['categorie_id'];
        }
        if (isset($inputs['heure_debut'])) {
            $tache->heure_debut = $inputs['heure_debut'];
        }
        if (isset($inputs['heure_fin'])) {
            $tache->heure_fin = $inputs['heure_fin'];
        }
        if (isset($inputs['jour'])) {
            $tache->jour = $inputs['jour'];
        }
        $tache->save();

        return $tache;
    }
}
