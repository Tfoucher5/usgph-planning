<?php

namespace App\Http\Services\Conge;

use App\Http\Repositories\Conge\AbsenceRepository;
use App\Http\Services\Planning\PlanningService;
use App\Models\Conge\Absence;
use App\Models\Planning\Planning;
use App\Models\User;
use Carbon\Carbon;
use DateUSGPH;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AbsenceService
{
    /**
     * @var AbsenceRepository
     */
    protected $repository;

    /**
     * Summary of planningRepository
     *
     * @var \App\Http\Services\Planning\PlanningService
     */
    protected $planningService;

    /**
     * Constructor
     *
     * @param  AbsenceRepository  $repository
     * @param  PlanningService  $planningService
     */
    public function __construct(AbsenceRepository $repository, PlanningService $planningService)
    {
        $this->repository = $repository;
        $this->planningService = $planningService;
    }

    /**
     * Summary of store
     *
     * @param  array<mixed>  $inputs
     * @param  \App\Models\User  $user
     *
     * @return \App\Models\Conge\Absence
     */
    public function store(array $inputs, User $user): Absence
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //
        $inputs = $this->convert_date($inputs);
        if ($inputs['motif_id'] === 1) {
            $duration = $this->getCongesPayesOfUserForYear($user);
            $reste = 25 - $duration;
        } else {
            $duration = $this->getHeuresSuppInDaysOfUserForYear($user);
            $repos = $this->getReposOfUserForYear($user);
            $reste = $duration - $repos;
        }

        if ($reste < Carbon::parse($inputs['date_debut'])->diffInDays($inputs['date_fin'])) {
            session()->flash('error', 'Vous n\'avez pas assez de droit pour poser ce congé');
            throw ValidationException::withMessages([
                'error' => 'Vous n\'avez pas assez de droit pour poser ce congé',
            ])->redirectTo(route('absence.create'));
        }
        $inputs['nb_of_work_days'] = $this->getNbOfWorkDays($inputs);

        // dd($inputs["nb_of_work_days"]);
        // dd($inputs);
        return $this->repository->store($inputs);
    }

    /**
     * Update the model instance
     *
     * @param  Absence  $absence
     * @param  array<mixed>  $inputs
     * @param  User  $user
     *
     * @return Absence
     */
    public function update(Absence $absence, array $inputs, User $user): Absence
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //
        $inputs = $this->convert_date($inputs);
        if ($inputs['motif_id'] === 1) {
            $duration = $this->getCongesPayesOfUserForYear($user);
            $reste = 30 - $duration;
        } else {
            $duration = $this->getHeuresSuppInDaysOfUserForYear($user);
            $repos = $this->getReposOfUserForYear($user);
            $reste = $duration - $repos;
        }

        if ($reste < Carbon::parse($inputs['date_debut'])->diffInDays($inputs['date_fin'])) {
            session()->flash('error', 'Vous n\'avez pas assez de droit pour poser ce congé');
            throw ValidationException::withMessages([
                'error' => 'Vous n\'avez pas assez de droit pour poser ce congé',
            ])->redirectTo(route('absence.update', ['absence' => $absence->id]));
        }

        $absence['nb_of_work_days'] = $this->getNbOfWorkDays($inputs);

        return $this->repository->update($absence, $inputs);
    }

    /**
     * Delete the model instance
     *
     * @param  Absence  $absence
     *
     * @return bool|null
     */
    public function destroy(Absence $absence)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->destroy($absence);
    }

    /**
     * Undelete the model instance
     *
     * @param  Absence  $absence
     *
     * @return void
     */
    public function undelete(Absence $absence)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        $this->repository->undelete($absence);
    }

    /**
     * Return a JSON for index datatable
     *
     * @return string|false|void — a JSON encoded string on success or FALSE on failure
     */
    public function json()
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->json();
    }

    /**
     * Summary of confirm
     *
     * @param  \App\Models\Conge\Absence  $absence
     *
     * @return Absence
     */
    public function confirm(Absence $absence)
    {
        $absence->nb_of_work_days = $this->getNbOfWorkDays(null, $absence);

        return $this->repository->confirm($absence);
    }

    /**
     * Summary of refuse
     *
     * @param  \App\Models\Conge\Absence  $absence
     *
     * @return mixed
     */
    public function refuse(Absence $absence)
    {
        $absence->nb_of_work_days = $this->getNbOfWorkDays(null, $absence);

        return $this->repository->refuse($absence);
    }

    /**
     * Summary of convert_date
     *
     * @param  array<mixed>  $inputs
     *
     * @return array<mixed>
     */
    public function convert_date(array $inputs)
    {
        $inputs['date_debut'] = Carbon::parse($inputs['date_debut'])->format('Y-m-d');
        $inputs['date_fin'] = Carbon::parse($inputs['date_fin'])->format('Y-m-d');

        return $inputs;
    }

    /**
     * Summary of getCongesPayesOfUserForYear
     *
     * @param  \App\Models\User  $user
     * @param  \Carbon\Carbon|null  $today
     *
     * @return float|int
     */
    public function getCongesPayesOfUserForYear(User $user, ?Carbon $today = null)
    {
        $nbCongesPayes = 0;
        $today = $today ??= Carbon::now();

        $USPGH = DateUSGPH::getUSGPHYear($today);

        $absences = Absence::where('user_id', $user->id)
            ->where('motif_id', 1)
            ->where('date_debut', '>=', Carbon::parse($USPGH['start'])->format('Y-m-d'))
            ->where('date_fin', '<=', Carbon::parse($USPGH['end'])->format('Y-m-d'))
            ->pluck('nb_of_work_days');
        foreach ($absences as $absence) {
            $nbCongesPayes += $absence;
        }

        return $nbCongesPayes;
    }

    /**
     * Summary of getReposOfUserForYear
     *
     * @param  \App\Models\User  $user
     * @param  \Carbon\Carbon|null  $today
     *
     * @return float|int
     */
    public function getReposOfUserForYear(User $user, ?Carbon $today = null)
    {
        $nbRepos = 0;

        $today = $today ??= Carbon::now();

        $USPGH = DateUSGPH::getUSGPHYear($today);

        $absences = Absence::where('user_id', $user->id)
            ->whereNot('motif_id', 1)
            ->where('date_debut', '>=', $USPGH['start'])
            ->where('date_fin', '<=', $USPGH['end'])
            ->pluck('nb_of_work_days');
        foreach ($absences as $absence) {
            $nbRepos += $absence;
        }

        return $nbRepos;
    }

    /**
     * Summary of getHeuresSuppInDaysOfUserForYear
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Support\Carbon|null  $today
     *
     * @return float|int
     */
    public function getHeuresSuppInDaysOfUserForYear(User $user, ?Carbon $today = null)
    {
        $today = $today ??= Carbon::now();

        $USPGH = DateUSGPH::getUSGPHYear($today);

        $plannings = Planning::where('user_id', $user->id)
            ->whereBetween('plannifier_le', [$USPGH['start'], $USPGH['end']])
            ->get();
        $duration = 0;
        foreach ($plannings as $planning) {
            $duration += Carbon::parse($planning->heure_debut)->diffInHours(Carbon::parse($planning->heure_fin));
        }
        // dd($duration);
        $duration /= 7;

        return $duration;
    }

    /**
     * Summary of getNbOfWorkDays
     *
     * @param  array<mixed>|null  $inputs
     * @param  \App\Models\Conge\Absence|null  $absence
     *
     * @return float|int
     */
    public function getNbOfWorkDays(?array $inputs = null, ?Absence $absence = null)
    {
        $startDate = Carbon::parse($inputs['date_debut'] ?? $absence->date_debut);
        $endDate = Carbon::parse($inputs['date_fin'] ?? $absence->date_fin);

        $nbJours = $startDate->equalTo($endDate) ? 1 : $startDate->diffInDays($endDate);

        $joursFeries = $this->planningService->getJoursFeries(
            $startDate->format('Y'),
            $endDate->format('Y')
        );

        $workDays = 0;

        for ($i = 0; $i < $nbJours; $i++) {
            $jour = $startDate->copy()->addDays($i);
            $jour->locale('fr');

            $jourRepos = $jour->dayName;
            if ($jourRepos === 'lundi' || $jourRepos === 'dimanche') {
                continue;
            }

            $isHoliday = $joursFeries->contains(function ($ferie) use ($jour) {
                return Carbon::parse($ferie['date'])->isSameDay($jour);
            });

            if (! $isHoliday) {
                $workDays++;
            }
        }

        return $workDays;
        // if (Carbon::parse($inputs['date_debut']??$absence->date_debut)->equalTo($inputs['date_debut']??$absence->date_fin)){
        //     $nbJours = 1;
        // }else {
        //     $nbJours = Carbon::parse($inputs['date_debut']??$absence->date_debut)->diffInDays($inputs['date_debut']??$absence->date_fin);
        // }
        // for( $i = 0; $i < $nbJours; $i++ ) {
        //     $jour = Carbon::parse($inputs['date_debut']??$absence->date_debut)->addDays($i);
        //     $jourRepos = $jour->locale('fr')->dayName;
        //     if($jourRepos === 'lundi' || $jourRepos === 'dimanche'){
        //         $nbJours--;
        //     }
        //     $joursFeries = $this->planningService->getJoursFeries(
        //     Carbon::parse($inputs['date_debut']??$absence->date_debut)->format('Y'),
        //     Carbon::parse($inputs['date_fin']??$absence->date_fin)->format('Y')
        //     );
        //     $estJourFerie = $joursFeries->contains(function (array $value) use ($jour): bool {
        //         return $value['date'] == $jour;
        //     });
        //     if ($estJourFerie) {
        //         $nbJours--;
        //     }
        // }
        // return $nbJours;
    }

    /**
     * Résumé de getTableauData
     *
     * @param  \App\Models\User  $user
     * @param  mixed  $anneeSelected
     *
     * @return array{
     *   joursParMotif: array{
     *     'Congé Annuel': 0|float,
     *     'Congé Exceptionnel': 0|float,
     *     'Congé Maladie': 0|float,
     *     'Repos Compensateur': 0|float
     *   },
     *   tableauData: array<int, non-empty-array<int, array{
     *     week_start: non-falsy-string,
     *     days: non-empty-array<int<min, -2>|int<0, max>, list<array{
     *       id: int,
     *       motif: string,
     *       statut: \App\Enums\ValidationStatus
     *     }>>
     *   }>>
     * }
     */
    public function getTableauData(User $user, $anneeSelected)
    {
        $anneeSelected = Carbon::parse($anneeSelected);
        $annee = DateUSGPH::getUSGPHYear($anneeSelected);
        $startDate = $annee['start']->format('Y-m-d');
        $endDate = $annee['end']->format('Y-m-d');

        $absences = Absence::where('user_id', $user->id)
            ->where('status', '!=', 'refusée')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_debut', [$startDate, $endDate])
                    ->orWhereBetween('date_fin', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('date_debut', '<', $startDate)
                            ->where('date_fin', '>', $endDate);
                    });
            })
            ->orderBy('date_debut', 'asc')
            ->get();

        $tableauData = [];

        $currentWeekDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentWeekDate->lte($endDateCarbon)) {
            $weekStart = $currentWeekDate->copy()->startOfWeek(Carbon::MONDAY);
            $year = $weekStart->year;
            $weekNumber = $currentWeekDate->weekOfYear;

            if ($weekStart->between($startDate, $endDate)) {
                $tableauData[$year][$weekNumber] = [
                    'week_start' => $weekStart->format('Y-m-d'),
                    'days' => array_fill(0, 7, []),
                ];
            }

            $currentWeekDate->addWeek();
        }

        // Initialiser les compteurs
        $joursParMotif = [
            'Congé Annuel' => 0,
            'Repos Compensateur' => 0,
            'Congé Exceptionnel' => 0,
            'Congé Maladie' => 0,
        ];

        foreach ($absences as $absence) {
            $motif = $absence->motif->nom ?? 'Inconnu';
            $status = $absence->status;

            $dateDebut = Carbon::parse($absence->date_debut)->max($startDate);
            $dateFin = Carbon::parse($absence->date_fin)->min($endDate);

            if ($dateDebut->gt($dateFin)) {
                continue;
            }

            $dureeEnJours = $dateDebut->diffInDays($dateFin) + 1;
            if (isset($joursParMotif[$motif])) {
                $joursParMotif[$motif] += $dureeEnJours;
            }
        }

        foreach ($absences as $absence) {
            $dateDebut = Carbon::parse($absence->date_debut);
            $dateFin = Carbon::parse($absence->date_fin);

            $dateDebut = $dateDebut->max($startDate);
            $dateFin = $dateFin->min($endDate);

            $currentDate = $dateDebut->copy();
            while ($currentDate->lte($dateFin)) {
                $year = $currentDate->year;
                $weekNumber = $currentDate->weekOfYear;
                $dayOfWeek = $currentDate->dayOfWeek;

                $dayIndex = $dayOfWeek === 0 ? 6 : $dayOfWeek - 1;

                if (isset($tableauData[$year][$weekNumber])) {
                    $tableauData[$year][$weekNumber]['days'][$dayIndex][] = [
                        'id' => $absence->id,
                        'motif' => $absence->motif->nom ?? 'Inconnu',
                        'statut' => $absence->status,
                    ];
                }

                $currentDate->addDay();
            }
        }

        krsort($tableauData);

        foreach ($tableauData as &$weeks) {
            krsort($weeks);
        }

        return [
            'tableauData' => $tableauData,
            'joursParMotif' => $joursParMotif,
        ];
    }

    /**
     * Récupère les périodes disponibles pour les absences d'un utilisateur.
     *
     * @param  mixed  $user
     *
     * @return \Illuminate\Support\Collection<string, array{value: string, label: string}>
     */
    public function getPeriodesDisponibles($user): Collection
    {
        $datesAbsences = Absence::where('user_id', $user->id)
            ->where('status', '!=', 'refusée')
            ->get()
            ->map(function ($absence) {
                $date = Carbon::parse($absence->date_debut);

                return $date->month < 9 ? $date->year - 1 : $date->year;
            })
            ->unique();

        $periodes = collect();

        foreach ($datesAbsences as $annee) {
            $startDate = Carbon::createFromDate($annee, 9, 1);
            $endDate = Carbon::createFromDate($annee + 1, 8, 31);

            $periode = $annee . '-' . ($annee + 1);

            $existeAbsences = Absence::where('user_id', $user->id)
                ->where('status', '!=', 'refusée')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('date_debut', [
                            $startDate->format('Y-m-d'),
                            $endDate->format('Y-m-d'),
                        ]);
                    })
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('date_fin', [
                                $startDate->format('Y-m-d'),
                                $endDate->format('Y-m-d'),
                            ]);
                        });
                })
                ->exists();

            if ($existeAbsences && ! $periodes->has($periode)) {
                $periodes->put($periode, [
                    'value' => $endDate->toDateString(),
                    'label' => $periode,
                ]);
            }
        }

        return $periodes->sortKeysDesc();
    }
}
