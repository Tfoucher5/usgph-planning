<?php

namespace App\Http\Services\Planning;

use App\Http\Repositories\Admin\ArchiveRepository;
use App\Http\Repositories\Planning\PlanningRepository;
use App\Models\Planning\Planning;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Log;
use Session;
use Throwable;

class PlanningService
{
    /**
     * @var PlanningRepository
     */
    protected $repository;

    /**
     * @var ArchiveRepository
     */
    protected $ArchiveRepository;

    /**
     * Summary of __construct
     *
     * @param  \App\Http\Repositories\Planning\PlanningRepository  $repository
     * @param  \App\Http\Repositories\Admin\ArchiveRepository  $ArchiveRepository
     */
    public function __construct(PlanningRepository $repository, ArchiveRepository $ArchiveRepository)
    {
        $this->repository = $repository;
        $this->ArchiveRepository = $ArchiveRepository;
    }

    /**
     * Summary of convertDate
     *
     * @param  mixed  $inputs
     *
     * @return mixed
     */
    public function convertDate($inputs)
    {
        if (
            isset($inputs['heure_debut_heure']) &&
            isset($inputs['heure_debut_minute']) &&
            isset($inputs['heure_fin_heure']) &&
            isset($inputs['heure_fin_minute'])
        ) {
            $inputs['heure_debut_minute'] = str_pad($inputs['heure_debut_minute'], 2, '0', STR_PAD_LEFT);
            $inputs['heure_fin_minute'] = str_pad($inputs['heure_fin_minute'], 2, '0', STR_PAD_LEFT);

            $inputs['heure_debut'] = $inputs['heure_debut_heure'] . ':' . $inputs['heure_debut_minute'];
            $inputs['heure_fin'] = $inputs['heure_fin_heure'] . ':' . $inputs['heure_fin_minute'];

            $heureDebut = Carbon::createFromFormat('H:i', $inputs['heure_debut']);
            $heureFin = Carbon::createFromFormat('H:i', $inputs['heure_fin']);

            $inputs['heure_debut'] = $heureDebut->format('H:i');
            $inputs['heure_fin'] = $heureFin->format('H:i');

            return $inputs;
        }

        return $inputs;
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
        $inputs = $this->convertDate($inputs);

        $inputs = $this->handleOverlappingPlans($inputs);

        if (! empty($inputs['error'])) {
            Session::put('erreur', 'Vos tâches ne peuvent pas se chevaucher');
            throw ValidationException::withMessages([
                'erreur' => 'Vos tâches ne peuvent pas se chevaucher',
            ])->redirectTo(route('planning.index'));
        }

        return $this->repository->store($inputs);
    }

    /**
     * Update the model instance
     *
     * @param  Planning  $planning
     * @param  array<mixed>  $inputs
     *
     * @return Planning
     */
    public function update(Planning $planning, array $inputs, bool $error = false): Planning
    {
        $inputs = $this->convertDate($inputs);

        $inputs = $this->handleOverlappingPlans($inputs, $planning);

        if (! empty($inputs['error'])) {
            Session::put('erreur', 'Vos tâches ne peuvent pas se chevaucher');
            throw ValidationException::withMessages([
                'erreur' => 'Vos tâches ne peuvent pas se chevaucher',
            ])->redirectTo(route('planning.index'));
        }

        return $this->repository->update($planning, $inputs);
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
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->destroy($planning);
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
    //     //
    //     // Règles de gestion à appliquer avant l'enregistrement en base
    //     //

    //     $this->repository->undelete($planning);
    // }

    // /**
    //  * Return a JSON for index datatable
    //  *
    //  * @return string|false|void — a JSON encoded string on success or FALSE on failure
    //  */
    // public function json()
    // {
    //     //
    //     // Règles de gestion à appliquer avant l'enregistrement en base
    //     //

    //     return $this->repository->json();
    // }

    /**
     * @param  User  $user
     *
     * @return Collection<int, mixed>|Collection<string, string>
     */
    public function importerPlanning(User $user, Carbon $start, Carbon $end)
    {
        return $this->repository->importerPlanning($user, $start, $end);
    }

    /**
     * @param  Planning  $planning
     * @param  User  $user
     * @param  int  $is_validated
     *
     * @return array<string, mixed>
     *
     * @throws Throwable
     */
    public function validateTache(Planning $planning, User $user, int $is_validated): array
    {
        try {
            if ($planning->is_validated) {
                return ['success' => false, 'message' => 'Cette tâche a déjà été validée.'];
            }

            $inputs = [
                'is_validated' => $is_validated,
            ];
            DB::beginTransaction();

            $this->repository->save($planning, $inputs);

            $planning->refresh();

            $hoursThisWeek = $this->repository->getHoursThisWeek($user);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tâche validée avec succès.',
                'hoursThisWeek' => $hoursThisWeek,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de la tâche : ' . $e->getMessage());

            return ['success' => false, 'message' => 'Une erreur est survenue.'];
        }
    }

    /**
     * Récupère les tâches de l'utilisateur pour cette semaine ou cette année selon son rôle.
     *
     * @param  \App\Models\User|null  $user
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTachesThisWeek(?User $user): Collection
    {
        $startOfWeek = Carbon::now()->setTimezone('Europe/Paris')->startOfWeek();
        $endOfWeek = Carbon::now()->setTimezone('Europe/Paris')->endOfMonth();

        if (! $user) {
            return collect([]);
        }

        return $this->repository->getTachesByUserAndDateRange($user, $startOfWeek, $endOfWeek);
    }

    /**
     * @param  User  $user
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function getHoursThisWeek(User $user)
    {
        return $this->repository->getHoursThisWeek($user);
    }

    /**
     * Récupère les heures prévues pour cette semaine
     *
     * @param  User  $user
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function getHeuresPrevuesCetteSemaine(User $user)
    {
        return $this->repository->getHeuresPrevuesCetteSemaine($user);
    }

    /**
     * Récupère les heures prévues pour cette semaine
     *
     * @param  User  $user
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function getTachesList(User $user)
    {
        $minDate = Planning::where('user_id', $user->id)
            ->min('plannifier_le');
        $maxDate = Planning::where('user_id', $user->id)
            ->max('plannifier_le');

        $startDate = Carbon::parse($minDate)->startOfWeek()->setTimezone('Europe/Paris');
        $endDate = Carbon::parse($maxDate)->endOfWeek()->setTimezone('Europe/Paris');

        return $this->repository->getTachesByUserAndDateRange($user, $startDate, $endDate);
    }

    /**
     * Récupère la liste des salaries
     *
     * @return Collection<int, \App\Models\User>
     */
    public function getSalaries(): Collection
    {
        return $this->repository->getSalaries();
    }

    /**
     * Handle overlapping plans and adjust their times if necessary.
     *
     * @param  array<mixed>  $inputs
     * @param  Planning|null  $planning
     * @param  int  $duree
     *
     * @return array<mixed> The updated inputs.
     */
    public function handleOverlappingPlans(array $inputs, ?Planning $planning = null, int $duree = 0): array
    {
        $ogHeureDebut = $inputs['heure_debut'] ?? $planning->heure_debut;
        $ogHeureFin = $inputs['heure_fin'] ?? $planning->heure_fin;

        $plannings = Planning::where('user_id', $inputs['user_id'] ?? $planning->user_id)
            ->where('plannifier_le', $inputs['plannifier_le'] ?? $planning->plannifier_le)
            ->where('id', '!=', $planning->id ?? null)
            ->where(function ($query) use ($inputs, $planning) {
                $query->whereBetween('heure_debut', [$inputs['heure_debut'] ?? $planning->heure_debut, $inputs['heure_fin'] ?? $planning->heure_fin])
                    ->orWhereBetween('heure_fin', [$inputs['heure_debut'] ?? $planning->heure_debut, $inputs['heure_fin'] ?? $planning->heure_fin]);
            })
            ->orderBy('heure_debut')
            ->get();

        if ($plannings->isNotEmpty()) {
            return [
                'error' => true,
                'message' => 'Overlapping plans detected',
            ];
        }
        $inputs['heure_debut'] = $ogHeureDebut;
        $inputs['heure_fin'] = $ogHeureFin;

        return $inputs;
    }

    /**
     * Récupère la liste des lieux
     *
     * @return Collection<int, \App\Models\Admin\Lieu>
     */
    public function getLieux(): Collection
    {
        return $this->repository->getLieux();
    }

    /**
     * Récupère la liste des Taches sans doublons sur 'nom'
     *
     * @return Collection<int, \App\Models\Planning\Tache>
     */
    public function getTaches(): Collection
    {
        return $this->repository->getTaches();
    }

    /**
     * Summary of getTachesByDateRange
     *
     * @param  \Carbon\Carbon  $start
     * @param  \Carbon\Carbon  $end
     *
     * @return array<int, array<string, mixed>> Array of raw data
     */
    public function getTachesByDateRange(Carbon $start, Carbon $end)
    {
        return $this->repository->getTachesByDateRange($start, $end);
    }

    /**
     * Summary of getJoursFeries
     *
     * @param  string|null  $startYear
     * @param  string|null  $endYear
     *
     * @return \Illuminate\Support\Collection<string, array{date: string, title: string, class: string}>
     */
    public function getJoursFeries(?string $startYear = null, ?string $endYear = null): Collection
    {
        $currentYear = date('Y');

        if (! $startYear) {
            $startYear = $currentYear - 10;
        }
        if (! $endYear) {
            $endYear = $currentYear;
        }

        $client = new Client();
        $response = $client->get('https://calendrier.api.gouv.fr/jours-feries/metropole.json');

        $joursFeries = json_decode($response->getBody()->getContents(), true);

        $joursFeriesInRange = array_filter($joursFeries, function ($date) use ($startYear, $endYear) {
            $year = substr($date, 0, 4);

            return $year >= $startYear && $year <= $endYear;
        }, ARRAY_FILTER_USE_KEY);

        return collect($joursFeriesInRange)->map(function ($joursFeriesName, $date): array {
            return [
                'date' => (string) $date,
                'title' => (string) $joursFeriesName,
                'class' => 'holiday',
            ];
        });
    }
}
