<?php

namespace App\Http\Controllers\Conge;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conge\AbsenceModelRequest;
use App\Http\Services\Conge\AbsenceService;
use App\Models\Conge\Absence;
use App\Models\Conge\Motif;
use App\Models\User;
use App\Notifications\AbsenceNotification;
use Auth;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\NotLocaleAwareException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Session;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Translation\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;

class AbsenceController extends Controller
{
    private const ABILITY = 'absence';

    private const PATH_VIEWS = 'absence';

    /**
     * @var AbsenceService
     */
    private $service;

    /**
     * Constructor
     *
     * @param  AbsenceService  $service
     */
    public function __construct(AbsenceService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
        Session::put('level_menu_1', 'Conges');
        Session::put('level_menu_2', self::ABILITY);
    }

    /**
     * Summary of index
     *
     * @return View|void
     */
    public function index()
    {
        /**
         * @var User
         */
        $user = Auth::user();
        Session::put('level_menu_2', self::ABILITY);
        if ($this->isA('salarie')) {
            $absences = Absence::with('motif')
                ->where('user_id', '=', $user->id)
                ->orderByDesc('created_at')
                ->paginate(10);

            return view(self::PATH_VIEWS . '.index', compact('absences'));
        }
        if ($this->isA('admin')) {
            $salaries = User::whereHas('roles', function ($query) {
                $query->where('name', 'salarie');
            })->get();
            $absences = Absence::with('motif')
                ->orderByDesc('created_at')
                ->paginate(10);

            return view(self::PATH_VIEWS . '.index', compact('absences', 'salaries'));
        }

        return abort(401);
    }

    /**
     * @return View|Factory|null
     *
     * @throws BindingResolutionException
     * @throws RouteNotFoundException
     * @throws InvalidFormatException
     * @throws NotLocaleAwareException
     * @throws ExceptionInvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function create()
    {
        return $this->model(null, 'create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  AbsenceModelRequest  $request
     *
     * @return RedirectResponse|void
     */
    public function store(AbsenceModelRequest $request)
    {
        /**
         * @var User
         */
        $user = Auth::user();
        if ($this->can(self::ABILITY . '-create')) {
            $data = $request->all();
            $this->service->store($data, $user);
            Session::put('ok', 'Création effectuée');
            $details = [
                'subject' => "déclaration absence / {$user->identity}",
                'message' => "{$user->identity} vient de déclarer une nouvelle absence.",
                'actionText' => 'Cliquer ici pour la consulter',
                'actionUrl' => url('/absence'),
            ];
            $admin = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->first();

            $admin->notify(new AbsenceNotification($details));

            return redirect(self::PATH_VIEWS);
        }

        return abort(401);
    }

    /**
     * @param  Absence  $absence
     *
     * @return View|Factory|null
     *
     * @throws BindingResolutionException
     * @throws RouteNotFoundException
     * @throws InvalidFormatException
     * @throws NotLocaleAwareException
     * @throws ExceptionInvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function show(Absence $absence)
    {
        if ($this->isA('admin')) {
            return $this->model($absence, 'retrieve');
        }

        return abort(401);
    }

    /**
     * @param  Absence  $absence
     *
     * @return View|Factory|null
     *
     * @throws BindingResolutionException
     * @throws RouteNotFoundException
     * @throws InvalidFormatException
     * @throws NotLocaleAwareException
     * @throws ExceptionInvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function edit(Absence $absence)
    {
        return $this->model($absence, 'update');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  AbsenceModelRequest  $request
     * @param  Absence  $absence
     *
     * @return RedirectResponse|void
     */
    public function update(AbsenceModelRequest $request, Absence $absence)
    {
        /**
         * @var User
         */
        $user = Auth::user();
        if ($this->isA('salarie') && $user->id === $absence->user_id) {
            $this->service->update($absence, $request->all(), $user);
            Session::put('ok', 'Mise à jour effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Absence  $absence
     *
     * @return RedirectResponse|void
     */
    public function destroy(Absence $absence)
    {
        /**
         * @var User
         */
        $user = Auth::user();
        if ($this->can(self::ABILITY . '-delete') && $user->id === $absence->user_id) {
            $this->service->destroy($absence);
            Session::put('ok', 'Suppression effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Summary of corbeille
     *
     * @return View
     */
    public function corbeille()
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $deletedAbsences = Absence::onlyTrashed()
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view(self::PATH_VIEWS . '.corbeille', compact('deletedAbsences'));
        }

        return abort(401);
    }

    /**
     * Restaure un �l�ment supprim�
     *
     * @example Penser � utiliser un bind dans le web.php
     *          Route::bind('absence_id', function ($absence_id) {
     *              return Absence::onlyTrashed()->find($absence_id);
     *          });
     *
     * @param  Absence  $absence
     *
     * @return RedirectResponse|void
     */
    public function undelete(Absence $absence)
    {
        /**
         * @var User
         */
        $user = Auth::user();
        if ($this->can(self::ABILITY . '-delete') && $user->id === $absence->user_id) {
            $this->service->undelete($absence);
            Session::put('ok', 'Restauration effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Renvoie la liste des Absence au format JSON pour leur gestion
     *
     * @return string|false|void � a JSON encoded string on success or FALSE on failure
     */
    public function json()
    {
        if ($this->can(self::ABILITY . '-retrieve')) {
            return $this->service->json();
        }

        return abort(401);
    }

    /**
     * Summary of validate
     *
     * @param  \App\Models\Conge\Absence  $absence
     *
     * @return RedirectResponse|void
     */
    public function confirm(Absence $absence)
    {
        if ($this->can(self::ABILITY . '-confirm')) {
            $this->service->confirm($absence);
            Session::put('ok', 'Absence acceptée');
            if ($absence->motif_id !== 5) {
                $details = [
                    'subject' => 'validation absence',
                    'message' => 'Votre absence à été acceptée.',
                    'actionText' => 'Cliquer ici pour la consulter',
                    'actionUrl' => url('/absence/index'),
                ];
                $user = User::where('id', $absence->user_id)->first();
                $user->notify(new AbsenceNotification($details));
            }

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Summary of confirm
     *
     * @param  \App\Models\Conge\Absence  $absence
     *
     * @return RedirectResponse|void
     */
    public function refuse(Absence $absence)
    {
        if ($this->can(self::ABILITY . '-refuse')) {
            $absence->load(['user', 'motif']);
            $this->service->refuse($absence);
            Session::put('ok', 'Absence refusée');
            if ($absence->motif_id !== 5) {
                $details = [
                    'subject' => 'Refus absence',
                    'message' => 'Votre absence à été refusée.',
                    'actionText' => 'Cliquer ici pour la consulter',
                    'actionUrl' => url('/absence'),
                ];
                $user = User::where('id', $absence->user_id)->first();

                $user->notify(new AbsenceNotification($details));
            }

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * @param  User  $user
     *
     * @return View
     */
    public function showAbsenceBySalarie(user $user): View
    {
        if (! Auth::user()->isA('admin')) {
            return view(self::PATH_VIEWS . '.index')->with('error', 'Vous n\'avez pas les droits nécessaires pour cette action');
        }
        $currentUserId = $user->id;
        $absences = Absence::with('user', 'motif')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(perPage: 10);
        $salaries = User::whereHas('roles', function ($query) {
            $query->where('name', 'salarie');
        })->get();

        // Vérification explicite du chemin de la vue
        return view(self::PATH_VIEWS . '.index', compact('absences', 'salaries', 'currentUserId'));
    }

    /**
     * Summary of getTableauData
     *
     * @param  \App\Models\User|null  $salarie
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed|RedirectResponse|View
     */
    public function getTableauData(?User $salarie, Request $request)
    {
        if (auth()->user()->isA('admin')) {
            if ($salarie->id === null) {
                $salarie = User::whereHas('roles', function ($query) {
                    $query->where('name', 'salarié');
                })->first();
            }

            if (isset($request['annee'])) {
                $annee = Carbon::parse($request['annee']);
            } else {
                $annee = Carbon::now()->format('Y-m-d');
            }
            $annee = Carbon::parse($annee);

            $result = $this->service->getTableauData($salarie, $annee);
            $tableauData = $result['tableauData'];
            $joursParMotif = $result['joursParMotif'];
            $periodes = $this->service->getPeriodesDisponibles($salarie);
            $salaries = User::whereHas('roles', function ($query) {
                $query->where('name', 'salarie');
            })->get();

            $nbConges = $this->service->getCongesPayesOfUserForYear($salarie, $annee);

            return view(self::PATH_VIEWS . '.tableau', [
                'tableauData' => $tableauData,
                'joursParMotif' => $joursParMotif,
                'annees' => $periodes,
                'user' => $salarie,
                'annee' => $annee,
                'salaries' => $salaries,
                'nbConges' => $nbConges,
            ]);
        }
        Session::put('erreur', 'Vous n\'avez pas la permission d\'accéder à cette page.');

        return redirect()->back();
    }

    /**
     * Rempli un tableau avec les données nécessaires aux vues
     *
     * @param  Absence  $absence|null
     * @param  string  $ability
     *
     * @return array<string, mixed>
     */
    private function data(?Absence $absence, string $ability): array
    {
        return [
            'absence' => $absence,
            'motifs' => Motif::all(),
            'disabled' => $ability === 'retrieve',
        ];
    }

    /**
     * @param  Absence  $absence|null
     * @param  string  $ability
     *
     * @return View|Factory
     *
     * @throws BindingResolutionException
     * @throws RouteNotFoundException
     * @throws InvalidFormatException
     * @throws NotLocaleAwareException
     * @throws ExceptionInvalidArgumentException
     * @throws InvalidArgumentException
     */
    private function model(?Absence $absence, string $ability)
    {
        /**
         * @var User
         */
        $user = Auth::user();
        if ($this->can(self::ABILITY . '-' . $ability)) {
            $absences = Absence::with('motif')->where('user_id', $user->id)->get();

            return view(
                self::PATH_VIEWS . '.model',
                $this->data($absence, $ability),
                compact('absences')
            );
        }

        return abort(401);
    }
}
