<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\SyntheseRepository;
use App\Http\Services\Planning\PlanningService;
use App\Models\Conge\Absence;
use App\Models\Planning\Planning;
use App\Models\User;
use Carbon\Carbon;
use DateUSGPH;
use Illuminate\Support\Collection;

class SyntheseService
{
    /**
     * @var SyntheseRepository
     */
    protected $repository;

    /**
     * Summary of planningService
     *
     * @var PlanningService
     */
    protected $planningService;

    /**
     * Constructor
     *
     * @param  SyntheseRepository  $repository
     * @param  PlanningService  $planningService
     */
    public function __construct(SyntheseRepository $repository, PlanningService $planningService)
    {
        $this->repository = $repository;
        $this->planningService = $planningService;
    }

    /**
     * Summary of getInfosUtilisateurs
     *
     * @return array<int, array<string, mixed>>
     */
    public function getInfosUtilisateurs()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'salarie');
        })->get();

        return $users->map(function ($user) {
            return $this->getInfosParUtilisateur($user->id);
        })->toArray();
    }

    /**
     * Summary of getInfosParUtilisateur
     *
     * @param  mixed  $userId
     *
     * @return array<string, mixed>
     */
    public function getInfosParUtilisateur($userId)
    {
        $user = User::where('id', $userId)->first();

        return [
            'id' => $user->id,
            'name' => $user->identity,
            'email' => $user->email,
        ];
    }

    /**
     * Summary of getHeuresParSemaine
     *
     * @param  mixed  $data
     * @param  mixed  $user
     * @param  mixed  $anneeEnCours
     *
     * @return \Illuminate\Support\Collection<string, array{annee: int, semaine: int, jours: array<int, array{heures: float, ferie: bool, repos: bool, absence?: bool}>, total: float, heures_supp: float}>
     *
     * @codeCoverageIgnore //Car testé via le test de récupération des heures par utilisateurs en fonction des filtres
     */
    public function getHeuresParSemaine($data, $user, $anneeEnCours = null): Collection
    {
        if (! isset($anneeEnCours) && isset($data['annee'])) {
            $anneeEnCours = Carbon::parse($data['annee']);
        }

        $annee = DateUSGPH::getUSGPHYear($anneeEnCours);
        $startDate = $annee['start']->format('Y-m-d');
        $endDate = $annee['end']->format('Y-m-d');
        $query = \DB::table('plannings')
            ->where('user_id', $user)
            ->where('is_validated', true)
            ->whereBetween('plannifier_le', [$startDate, $endDate]);

        $this->applyFilters($query, $data, $anneeEnCours);

        $plannings = $query
            ->select(
                \DB::raw('YEAR(DATE(plannifier_le)) AS annee'),
                \DB::raw('WEEK(plannifier_le) AS semaine'),
                \DB::raw('MONTH(plannifier_le) AS mois'),
                \DB::raw('DAYOFWEEK(plannifier_le) AS jourSemaine'),
                \DB::raw('DAY(plannifier_le) AS jour'),
                \DB::raw('SUM(TIMESTAMPDIFF(MINUTE, heure_debut, heure_fin)) / 60 AS heures_travaillees')
            )
            ->groupBy(
                \DB::raw('YEAR(DATE(plannifier_le))'),
                \DB::raw('WEEK(plannifier_le)'),
                \DB::raw('MONTH(plannifier_le)'),
                \DB::raw('DAYOFWEEK(plannifier_le)'),
                \DB::raw('DAY(plannifier_le)')
            )
            ->orderByDesc('annee')
            ->orderByDesc('semaine')
            ->orderByDesc('jourSemaine')
            ->get();

        $resultat = $plannings->filter(function ($planning) use ($startDate, $endDate) {
            $date = Carbon::createFromFormat('Y-m-d', $planning->annee . '-' . $planning->mois . '-' . $planning->jour);

            return $date->between($startDate, $endDate);
        });

        $resultat = $this->formatHeuresParSemaine($resultat)->toArray();
        $resultat = $this->checkAbsences($resultat, $user);

        return collect($resultat);
    }

    /**
     * Résumé de la fonction formatHeuresParSemaine
     *
     * @param  mixed  $plannings
     *
     * @return \Illuminate\Support\Collection<string, array{annee: int, semaine: int, jours: non-empty-array<int, array{heures: float, ferie: bool, repos: bool}>, total: float, heures_supp: float}>
     *
     * @codeCoverageIgnore //Car testé via le test de récupération des heures par utilisateurs en fonction des filtres
     */
    public function formatHeuresParSemaine($plannings): Collection
    {
        $semaines = [];
        $joursFeries = $this->planningService->getJoursFeries();

        foreach ($plannings as $planning) {
            $carbonDate = Carbon::createFromDate((int) $planning->annee, (int) $planning->mois, (int) $planning->jour);
            $semaineISO = $carbonDate->isoWeek;

            $key = 'S' . $semaineISO;

            if (! isset($semaines[$key])) {
                $semaines[$key] = [
                    'annee' => (int) $planning->annee,
                    'semaine' => $semaineISO,
                    'jours' => [
                        1 => ['heures' => 0.0, 'ferie' => false, 'repos' => true],
                        2 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                        3 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                        4 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                        5 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                        6 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                        0 => ['heures' => 0.0, 'ferie' => false, 'repos' => true],
                    ],
                    'total' => 0.0,
                    'heures_supp' => 0.0,
                ];
            }

            $jour = $planning->jourSemaine;
            $jour = $jour === 1 ? 0 : $jour - 1; // Lundi = 1, Dimanche = 0

            $dateJour = $carbonDate->format('Y-m-d');
            $estJourFerie = $joursFeries->contains(function (array $value) use ($dateJour): bool {
                return $value['date'] === $dateJour;
            });

            $heuresTravaillees = (float) $planning->heures_travaillees;

            if ($estJourFerie || $planning->jourSemaine === 1) {
                $heuresTravaillees *= 1.5;
            }

            $semaines[$key]['jours'][$jour]['heures'] += $heuresTravaillees;
            $semaines[$key]['jours'][$jour]['ferie'] = $estJourFerie;

            $semaines[$key]['total'] += $heuresTravaillees;
            $semaines[$key]['heures_supp'] = max(0.0, $semaines[$key]['total'] - 35);
        }

        return collect($semaines);
    }

    /**
     * Récupère les années scolaires disponibles pour un utilisateur.
     *
     * @param  mixed  $user
     *
     * @return \Illuminate\Support\Collection<string, array{value: string, label: string}>
     *
     * @codeCoverageIgnore //Car testé via le test de récupération des heures par utilisateurs en fonction des filtres
     */
    public function getAnneesDisponibles($user): Collection
    {
        $anneesAvecDonnees = Planning::where('user_id', $user)
            ->where('is_validated', true)
            ->get()
            ->pluck('plannifier_le')
            ->map(fn ($date) => Carbon::parse($date)->year)
            ->unique();

        $anneesScolaires = collect();

        foreach ($anneesAvecDonnees as $annee) {
            $carbonDate = Carbon::createFromDate($annee, 1, 1);

            $startDate = $carbonDate->copy()->subYear()->startOfYear()->addMonths(8);
            $endDate = $carbonDate->copy()->subYear()->endOfYear()->addMonths(8);

            $anneeScolaire = $startDate->year . '-' . $endDate->year;

            $existePlannings = Planning::where('user_id', $user)
                ->where('is_validated', true)
                ->whereBetween('plannifier_le', [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                ])
                ->exists();

            if ($existePlannings && ! $anneesScolaires->has($anneeScolaire)) {
                $anneesScolaires->put($anneeScolaire, [
                    'value' => $endDate->toDateString(),
                    'label' => $anneeScolaire,
                ]);
            }
        }

        return $anneesScolaires->sortKeysDesc();
    }

    /**
     * @param  array<string, array{annee: int, semaine: int, jours: array<int, array{heures: float, ferie: bool, repos: bool, absence?: bool}>, total: float, heures_supp: float}>  $plannings
     * @param  mixed  $user_id
     *
     * @return \Illuminate\Support\Collection<string, array{annee: int, semaine: int, jours: array<int, array{heures: float, ferie: bool, repos: bool, absence?: bool}>, total: float, heures_supp: float}>
     */
    public function checkAbsences($plannings, $user_id): Collection
    {
        $absences = Absence::where('user_id', $user_id)
            ->get();

        foreach ($plannings as $key => $semaine) {
            $debutSemaine = Carbon::now()->setISODate($semaine['annee'], $semaine['semaine'])->startOfWeek();

            foreach ($semaine['jours'] as $jourIndex => &$jour) {
                $dateJour = clone $debutSemaine;
                if ($jourIndex === 0) {
                    $dateJour->addDays(6);  // Dimanche
                } else {
                    $dateJour->addDays($jourIndex - 1);  // Lundi à Samedi
                }

                foreach ($absences as $absence) {
                    $debutAbsence = Carbon::parse($absence->date_debut);
                    $finAbsence = Carbon::parse($absence->date_fin);

                    if ($dateJour->between($debutAbsence, $finAbsence)) {
                        $jour['absence'] = $absence->motif->nom;
                        $semaine['total'] -= $jour['heures'];
                        $jour['heures'] = 0;
                        break;
                    }
                }
            }

            $plannings[$key] = $semaine;
        }

        return collect($plannings);
    }

    /**
     * Récupère les tâches à valider pour un utilisateur
     *
     * @param  \App\Models\User  $user
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\App\Models\Planning\Planning>
     */
    public function getTacheToValidate(User $user)
    {
        return Planning::where('user_id', $user->id)
            ->where('is_validated', false)
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('archives')
                    ->whereRaw('archives.planning_id = plannings.id');
            })
            ->paginate(10);
    }

    /**
     * Summary of applyFilters
     *
     * @param  mixed  $query
     * @param  mixed  $data
     * @param  mixed  $anneeEnCours
     *
     * @return void
     *
     * @codeCoverageIgnore //Car testé via le test de récupération des heures par utilisateurs en fonction des filtres
     */
    private function applyFilters($query, $data, $anneeEnCours)
    {
        if (isset($anneeEnCours)) {
            $annee = DateUSGPH::getUSGPHYear($anneeEnCours);
        } elseif (isset($data['annee'])) {
            $annee = DateUSGPH::getUSGPHYear(Carbon::parse($data['annee']));
        } else {
            $annee = DateUSGPH::getUSGPHYear();
        }

        if (isset($data['semaine'])) {
            $query->whereRaw('WEEK(plannifier_le, 1) = ?', [$data['semaine']]);
        }

        if (isset($data['mois']) && $annee) {
            $query->whereBetween('plannifier_le', [
                $annee['start']->format('Y-m-d'),
                $annee['end']->format('Y-m-d'),
            ])->whereMonth('plannifier_le', $data['mois']);
        }

        if ($annee && ! isset($data['mois'])) {
            $query->whereBetween('plannifier_le', [
                $annee['start']->format('Y-m-d'),
                $annee['end']->format('Y-m-d'),
            ]);
        }
    }
}
