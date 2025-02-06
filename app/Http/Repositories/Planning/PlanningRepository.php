<?php

namespace App\Http\Repositories\Planning;

use App\Models\Admin\Lieu;
use App\Models\Planning\Planning;
use App\Models\Planning\Tache;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PlanningRepository
{
    /**
     * @var Planning
     */
    protected $planning;

    /**
     * Constructor
     *
     * @param  Planning  $planning
     */
    public function __construct(Planning $planning)
    {
        $this->planning = $planning;
    }

    /**
     * Store a new model instance
     *
     * @param  array<mixed>  $inputs
     *
     * @return Planning
     */
    public function store(array $inputs): Planning
    {
        $planning = new $this->planning();
        $planning->user_id_creation = (int) Auth::id();

        $this->save($planning, $inputs);

        return $planning;
    }

    /**
     * Summary of importerPlanning
     *
     * @return Collection<int, mixed>|Collection<string, string>
     */
    public function importerPlanning(User $user, Carbon $start, Carbon $end)
    {
        $dateDebutSemaine = Carbon::parse($start)->startOfWeek()->toDateString();
        $dateFinSemaine = Carbon::parse($end)->endOfWeek()->toDateString();

        $existingPlanning = Planning::where('user_id', $user->id)
            ->whereBetween('plannifier_le', [$dateDebutSemaine, $dateFinSemaine])
            ->exists();

        if ($existingPlanning) {
            return collect(['error' => 'Planning déjà importé pour cette semaine.']);
        }

        $taches = Tache::where('user_id', $user->id)
            ->with(['lieu', 'user'])
            ->get();

        return $taches->map(function ($tache) use ($start, $user) {
            $dateDebut = Carbon::parse($start)->startOfWeek()->addDays($tache->jour - 1);
            $dateDebut->setTimeFromTimeString(Carbon::parse($tache->heure_debut)->toTimeString());

            $dateFin = Carbon::parse($start)->startOfWeek()->addDays($tache->jour - 1);
            $dateFin->setTimeFromTimeString(Carbon::parse($tache->heure_fin)->toTimeString());

            $inputs = [
                'user_id' => $user->id,
                'nom' => $tache->nom,
                'heure_debut' => $tache->heure_debut,
                'heure_fin' => $tache->heure_fin,
                'plannifier_le' => $dateDebut->toDateString(),
                'lieu_id' => $tache->lieu->id ?? null,
            ];

            $this->store($inputs);

            return [
                'id' => $tache->id,
                'title' => $tache->nom,
                'start' => $dateDebut->toDateTimeString(),
                'end' => $dateFin->toDateTimeString(),
                'day' => $tache->jour,
                'user' => $tache->user->identity,
                'location' => $tache->lieu->nom,
            ];
        });
    }

    /**
     * Update the model instance
     *
     * @param  Planning  $planning
     * @param  array<mixed>  $inputs
     *
     * @return Planning
     */
    public function update(Planning $planning, array $inputs): Planning
    {
        $planning->user_id_modification = (int) Auth::id();
        $this->save($planning, $inputs);

        return $planning;
    }

    /**
     * Delete the model instance
     *
     * @param  Planning  $planning
     *
     * @return bool|null
     */
    public function destroy(Planning $planning)
    {
        $planning->user_id_suppression = (int) Auth::id();
        $planning->save();

        return $planning->delete();
    }

    // /**
    //  * Undelete the model instance
    //  *
    //  * @param  Planning  $planning
    //  *
    //  * @return void
    //  */
    // public function undelete(Planning $planning)
    // {
    //     $planning->restore();
    // }

    // /**
    //  * Return a JSON for index datatable
    //  *
    //  * @return string|false|void — a JSON encoded string on success or FALSE on failure
    //  */
    // public function json()
    // {
    //     return json_encode(
    //         Planning::all()
    //     );
    // }

    /**
     * Get the tasks by date range.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTachesByDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $plannings = Planning::with(['tache', 'tache.lieu', 'tache.user'])
            ->whereBetween('plannifier_le', [$startDate, $endDate])
            ->get();

        return $plannings->map(function ($planning) {
            $dateDebut = Carbon::parse($planning->heure_debut)->setTimezone('Europe/Paris');
            $dateFin = Carbon::parse($planning->heure_fin)->setTimezone('Europe/Paris');

            $startDateTime = Carbon::parse($planning->plannifier_le)->setTimezone('Europe/Paris')
                ->setTime($dateDebut->hour, $dateDebut->minute);

            $endDateTime = Carbon::parse($planning->plannifier_le)->setTimezone('Europe/Paris')
                ->setTime($dateFin->hour, $dateFin->minute);

            return [
                'id' => $planning->id,
                'title' => $planning->nom,
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'location' => $planning->lieu->nom ?? 'Lieu non spécifié',
                'user' => $planning->tache->user->identity ?? 'Utilisateur non spécifié',
            ];
        })->toArray();
    }

    /**
     * Récupère les tâches d'un utilisateur pour une plage de dates donnée.
     *
     * @param  \App\Models\User|null  $user
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTachesByUserAndDateRange(?User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        $plannings = Planning::with(['tache', 'lieu', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('plannifier_le', [$startDate, $endDate])
            ->get();

        return collect($this->mapTachesForCalendar($plannings));
    }

    /**
     * Map des tâches pour le calendrier
     *
     * @param  Collection<int, \App\Models\Planning\Planning>  $plannings
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapTachesForCalendar(Collection $plannings): array
    {
        return $plannings->map(function ($planning) {
            $dateDebut = Carbon::parse($planning->heure_debut)->setTimezone('Europe/Paris');
            $dateFin = Carbon::parse($planning->heure_fin)->setTimezone('Europe/Paris');

            $startDateTime = Carbon::parse($planning->plannifier_le)->setTimezone('Europe/Paris')
                ->setTime($dateDebut->hour, $dateDebut->minute);

            $endDateTime = Carbon::parse($planning->plannifier_le)->setTimezone('Europe/Paris')
                ->setTime($dateFin->hour, $dateFin->minute);

            return [
                'id' => $planning->id,
                'title' => $planning->nom,
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'location' => $planning->lieu->nom ?? 'Lieu non spécifié',
                'user' => $planning->tache->user->identity ?? 'Utilisateur non spécifié',
                'description' => $planning->tache->description ?? '',
            ];
        })->toArray();
    }

    /**
     * @param  User  $user
     *
     * @return int|float
     *
     * @throws InvalidArgumentException
     */
    public function getHoursThisWeek(User $user)
    {
        $startOfWeek = Carbon::now('Europe/Paris')->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::now('Europe/Paris')->endOfWeek()->format('Y-m-d');

        return Planning::whereBetween('plannifier_le', [$startOfWeek, $endOfWeek])
            ->where('user_id', $user->id)
            ->where('is_validated', 1)
            ->get()
            ->reduce(function ($carry, $planning) {
                $heureDebut = Carbon::parse($planning->heure_debut);
                $heureFin = Carbon::parse($planning->heure_fin);

                return $carry + $heureDebut->diffInMinutes($heureFin);
            }, 0);
    }

    /**
     * Summary of getHeuresPrevuesCetteSemaine
     *
     * @param  \App\Models\User  $user
     *
     * @return mixed
     */
    public function getHeuresPrevuesCetteSemaine(User $user)
    {
        $startOfWeek = Carbon::now('Europe/Paris')->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now('Europe/Paris')->endOfWeek()->toDateString();

        return Planning::whereBetween('plannifier_le', [$startOfWeek, $endOfWeek])
            ->where('user_id', $user->id)
            ->get()
            ->sum(function ($planning) {
                $heureDebut = Carbon::parse($planning->heure_debut);
                $heureFin = Carbon::parse($planning->heure_fin);

                return $heureDebut->diffInMinutes($heureFin);
            });
    }

    /**
     * Récupère la liste des salaries
     *
     * @return Collection<int, \App\Models\User>
     */
    public function getSalaries(): Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'salarie');
        })->get();
    }

    /**
     * Récupère la liste des lieux
     *
     * @return Collection<int, Lieu>
     */
    public function getLieux(): Collection
    {
        return Lieu::all();
    }

    /**
     * Récupère la liste des Taches sans doublons sur 'nom'
     *
     * @return Collection<int, \App\Models\Planning\Tache>
     */
    public function getTaches(): Collection
    {
        return Tache::selectRaw('MAX(id) as id, nom')
            ->groupBy('nom')
            ->orderBy('nom')
            ->get();
    }

    /**
     * Save the model instance
     *
     * @param  planning  $planning
     * @param  array<mixed>  $inputs
     *
     * @return planning
     */
    public function save(planning $planning, array $inputs): planning
    {
        if (isset($inputs['tache_id'])) {
            $planning->tache_id = $inputs['tache_id'];
        }
        if (isset($inputs['user_id'])) {
            $planning->user_id = $inputs['user_id'];
        }
        if (isset($inputs['lieu_id'])) {
            $planning->lieu_id = $inputs['lieu_id'];
        }
        if (isset($inputs['nom'])) {
            $planning->nom = $inputs['nom'];
        }
        if (isset($inputs['plannifier_le'])) {
            $planning->plannifier_le = $inputs['plannifier_le'];
        }
        if (isset($inputs['heure_debut'])) {
            $planning->heure_debut = $inputs['heure_debut'];
        }
        if (isset($inputs['heure_fin'])) {
            $planning->heure_fin = $inputs['heure_fin'];
        }
        if (isset($inputs['is_validated'])) {
            $planning->is_validated = $inputs['is_validated'];
        }

        $planning->save();

        return $planning;
    }
}
