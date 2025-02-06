<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\PlanningModelRequest;
use App\Http\Requests\Planning\TacheValidationRequest;
use App\Http\Services\Planning\PlanningService;
use App\Models\Planning\Planning;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\NotLocaleAwareException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Session;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Translation\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;
use Throwable;

class PlanningController extends Controller
{
    private const ABILITY = 'planning';

    private const PATH_VIEWS = 'planning';

    /**
     * @var PlanningService
     */
    private $service;

    /**
     * Constructor
     *
     * @param  PlanningService  $service
     */
    public function __construct(PlanningService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
        Session::put('level_menu_1', 'Plannings');
        Session::put('level_menu_2', self::ABILITY);
    }

    /**
     * Summary of index
     *
     * @return mixed|RedirectResponse|View
     */
    public function index()
    {
        Session::put('level_menu_2', self::ABILITY);
        /**
         * @var User
         */
        $user = Auth::user();

        if ($this->can(self::ABILITY . '-retrieve')) {
            if ($user->isA('admin')) {
                $salaries = $this->service->getSalaries();

                $year = (string) now()->year;
                $joursFeries = $this->service->getJoursFeries($year);

                $joursFeriesJson = $joursFeries->toJson();

                return view(self::PATH_VIEWS . '.index', compact('salaries', 'joursFeriesJson'));
            }
            $hoursThisWeek = $this->service->getHoursThisWeek($user);
            $heuresPrevues = $this->service->getHeuresPrevuesCetteSemaine($user);
            $tachesList = $this->service->getTachesList($user);

            $year = (string) now()->year;
            $joursFeries = $this->service->getJoursFeries($year);
            $joursFeriesJson = $joursFeries->toJson();

            return view(self::PATH_VIEWS . '.index', compact('hoursThisWeek', 'tachesList', 'heuresPrevues', 'joursFeriesJson'));
        }

        Session::put('erreur', 'Vous n\'avez pas l\'autorisation d\'accéder à cette page');

        return redirect()->back();
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
        if ($this->can(self::ABILITY . '-create')) {
            return $this->model(null, 'create');
        }

        return abort(401);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PlanningModelRequest  $request
     *
     * @return RedirectResponse|void
     */
    public function store(PlanningModelRequest $request)
    {
        /**
         * @var User
         */
        $user = Auth::user();

        if ($this->can(self::ABILITY . '-create') || $this->isA('admin')) {
            $data = $request->all();

            $planning = $this->service->store($data);
            Session::put('ok', 'Tâche créée');

            if ($user->isA('admin')) {
                $userSelected = User::where('id', $request['user_id'])->first();

                return redirect()->route('planning.salarie', [
                    'salarie_id' => $userSelected->id,
                    'event_id' => $planning->id,
                ]);
            }

            return redirect(route(self::PATH_VIEWS . '.index', ['event_id' => $planning->id]));
        }

        return abort(401);
    }

    /**
     * @param  Planning  $planning
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
    public function show(Planning $planning)
    {
        if ($this->can(self::ABILITY . '-view')) {
            return $this->model($planning, 'retrieve');
        }

        return abort(401);
    }

    /**
     * @param  Planning  $planning
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
    public function edit(Planning $planning)
    {
        if ($this->can(self::ABILITY . '-edit')) {
            return $this->model($planning, 'update');
        }

        return abort(401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PlanningModelRequest  $request
     * @param  Planning  $planning
     *
     * @return RedirectResponse|JsonResponse|\Illuminate\Http\Exceptions\HttpResponseException
     */
    public function update(PlanningModelRequest $request, Planning $planning)
    {
        /**
         * @var User
         */
        $user = Auth::user();

        if ($this->can(self::ABILITY . '-update') || $this->isA('admin')) {
            $this->service->update($planning, $request->all());

            if ($request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Mise à jour effectuée',
                ]);
            }

            Session::put('ok', 'Tâche mise à jour');

            if ($user->isA('admin')) {
                $userSelected = User::where('id', $request['user_id'])->first();

                return redirect()->route('planning.salarie', [
                    'salarie_id' => $userSelected->id,
                    'event_id' => $planning->id,
                ]);
            }

            return redirect(route(self::PATH_VIEWS . '.index', ['event_id' => $planning->id]));
        }

        return response()->json(['message' => 'Pas Autorisé'], 401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Planning  $planning
     *
     * @return JsonResponse|void
     */
    public function destroy(Planning $planning)
    {
        /**
         * @var User
         */
        $user = Auth::user();
        if ($this->can(self::ABILITY . '-delete') && $user->id === $planning->user_id || $this->isA('admin')) {
            $this->service->destroy($planning);
            Session::put('ok', 'Suppression effectuée');

            return response()->json(['success' => true, 'message' => 'Événement supprimé']);
        }

        return response()->json(['message' => 'Vous n\'avez pas les droits nécessaires pour cette action'], 403);
    }

    // /**
    //  * Restaure un �l�ment supprim�
    //  *
    //  * @example Penser � utiliser un bind dans le web.php
    //  *          Route::bind('planning_id', function ($planning_id) {
    //  *              return Planning::onlyTrashed()->find($planning_id);
    //  *          });
    //  *
    //  * @param  Planning  $planning
    //  *
    //  * @return RedirectResponse|void
    //  */
    // public function undelete(Planning $planning)
    // {
    //     if ($this->can(self::ABILITY . '-delete')) {
    //         $this->service->undelete($planning);
    //         Session::put('ok', 'Restauration effectuée');

    //         return redirect(route(self::PATH_VIEWS . '.index'));
    //     }
    //     return abort(401);

    // }

    // /**
    //  * Renvoie la liste des Planning au format JSON pour leur gestion
    //  *
    //  * @return string|false|void � a JSON encoded string on success or FALSE on failure
    //  */
    // public function json()
    // {
    //     if ($this->can(self::ABILITY . '-retrieve')) {
    //         return $this->service->json();
    //     }
    // }

    /**
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @throws BadRequestException
     * @throws BindingResolutionException
     */
    public function importerPlanning(Request $request): JsonResponse
    {
        /**
         * @var User
         */
        $user = Auth::user();
        if ($this->can(self::ABILITY . '-importer') && $user->isA('salarie')) {
            $startDate = Carbon::parse($request['start']);

            $endDate = Carbon::parse($request['end']);

            $events = $this->service->importerPlanning($user, $startDate, $endDate);

            if (isset($events['error'])) {
                return response()->json(['error' => 'Une erreur est survenue']);
            }

            return response()->json($events);
        }

        return response()->json(['error' => 'Vous n\'avez pas les droits']);
    }

    /**
     * @param  Planning  $planning
     *
     * @return JsonResponse
     *
     * @throws BindingResolutionException
     */
    public function isTacheValidated(Planning $planning)
    {
        return response()->json(['is_validated' => $planning->is_validated]);
    }

    /**
     * @param  TacheValidationRequest  $request
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function validateTache(TacheValidationRequest $request, Planning $planning): JsonResponse
    {
        if ($this->can(self::ABILITY . '-validate')) {
            $data = $request->validated();

            /**
             * @var User
             */
            $user = Auth::user();

            $result = $this->service->validateTache($planning, $user, $data['is_validated']);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'hoursThisWeek' => $result['hoursThisWeek'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'Vous n\'avez pas les droits nécessaires pour cette action',
        ], 403);
    }

    /**
     * @param  User  $user
     *
     * @return View
     */
    public function showPlanningBySalarie(User $user): View
    {
        if (! Auth::user()->isA('admin')) {
            return view(self::PATH_VIEWS . '.index')->with('error', 'Vous n\'avez pas les droits nécessaires pour cette action');
        }

        $currentUserId = $user->id;
        $salaries = $this->service->getSalaries();
        $tachesThisWeek = $this->service->getTachesThisWeek($user);
        $tachesList = $this->service->getTachesList($user);

        $year = (string) now()->year;
        $joursFeries = $this->service->getJoursFeries($year);

        $joursFeriesJson = $joursFeries->toJson();
        $isEmpty = $tachesThisWeek->isEmpty();

        // Vérification explicite du chemin de la vue
        return view(self::PATH_VIEWS . '.index', compact('tachesList', 'isEmpty', 'salaries', 'currentUserId'));
    }

    /**
     * Summary of getEventsForRange
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return JsonResponse|mixed
     */
    public function getEventsForRange(Request $request)
    {
        $startDate = Carbon::parse($request['start'])
            ->startOfWeek()
            ->setTimezone('Europe/Paris');

        $endDate = Carbon::parse($request['end'])
            ->endOfWeek()
            ->setTimezone('Europe/Paris');

        $taches = $this->service->getTachesByDateRange($startDate, $endDate);

        if (empty($taches)) {
            $afficherBouton = true;
        } else {
            $afficherBouton = false;
        }

        return response()->json(['data' => $afficherBouton]);
    }

    /**
     * Rempli un tableau avec les données nécessaires aux vues
     *
     * @param  Planning|null  $planning
     * @param  string  $ability
     *
     * @return array<string, mixed>
     */
    private function data(?Planning $planning, string $ability): array
    {
        return [
            'planning' => $planning,
            'disabled' => $ability === 'retrieve',
            'users' => $this->service->getSalaries(),
            'lieux' => $this->service->getLieux(),
            'taches' => $this->service->getTaches(),
        ];
    }

    /**
     * @param  Planning  $planning|null
     * @param  string  $ability
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
    private function model(?Planning $planning, string $ability)
    {
        if ($this->can(self::ABILITY . '-' . $ability)) {
            return view(
                self::PATH_VIEWS . '.model',
                $this->data($planning, $ability)
            );
        }

        return null;
    }
}
