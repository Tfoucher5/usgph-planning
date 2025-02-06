<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\TacheModelRequest;
use App\Http\Services\Planning\PlanningService;
use App\Http\Services\Planning\TacheService;
use App\Models\Planning\Tache;
use App\Models\User;
use Auth;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\NotLocaleAwareException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Session;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Translation\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;

class TacheController extends Controller
{
    private const ABILITY = 'tache';

    private const PATH_VIEWS = 'tache';

    /**
     * @var TacheService
     */
    private $service;

    /**
     * @var PlanningService
     */
    private $planningService;

    /**
     * Constructor
     *
     * @param  TacheService  $service
     */
    public function __construct(TacheService $service, PlanningService $planningService)
    {
        $this->middleware('auth');
        $this->service = $service;
        $this->planningService = $planningService;
        Session::put('level_menu_1', 'Plannings');
        Session::put('level_menu_2', self::ABILITY);
    }

    /** @return View|Factory|null */
    public function index()
    {
        Session::put('level_menu_2', self::ABILITY);

        /**
         * @var User
         */
        $user = Auth::user();

        if ($this->isA('admin')) {
            $salaries = $this->planningService->getSalaries();
            $tachesThisWeek = $this->service->getTachesPerWeek($user);

            return view(
                self::PATH_VIEWS . '.index',
                compact('salaries', 'tachesThisWeek')
            );
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
        if ($this->can(self::ABILITY . '-create')) {
            return $this->model(null, 'create');
        }

        return abort(401);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  TacheModelRequest  $request
     *
     * @return RedirectResponse|void
     */
    public function store(TacheModelRequest $request)
    {
        if ($this->can(self::ABILITY . '-create')) {
            $data = $request->all();

            $userSelected = User::where('id', $request['user_id'])->first();

            $tache = $this->service->store($data);

            Session::put('ok', 'Création effectuée');

            return redirect()->route('tache.salarie', ['salarie_id' => $userSelected->id, 'event_id' => $tache->id]);
        }

        return abort(401);
    }

    /**
     * @param  Tache  $tache
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
    public function show(Tache $tache)
    {
        if ($this->can(self::ABILITY . '-view')) {
            return $this->model($tache, 'retrieve');
        }

        return abort(401);
    }

    /**
     * @param  Tache  $tache
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
    public function edit(Tache $tache)
    {
        if ($this->can(self::ABILITY . '-edit')) {
            return $this->model($tache, 'update');
        }

        return abort(401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  TacheModelRequest  $request
     * @param  Tache  $tache
     *
     * @return RedirectResponse|JsonResponse|void
     */
    public function update(TacheModelRequest $request, Tache $tache)
    {
        if ($this->can(self::ABILITY . '-update')) {
            $this->service->update($tache, $request->all());

            $userSelected = User::where('id', $request['user_id'])->first();

            if ($request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Mise à jour effectuée',
                ]);
            }

            Session::put('ok', 'Mise à jour effectuée');

            return redirect()->route('tache.salarie', ['salarie_id' => $userSelected->id, 'event_id' => $tache->id]);
        }

        return abort(401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Tache  $tache
     *
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Tache $tache)
    {
        $user = Auth::user();

        if ($this->can(self::ABILITY . '-delete') && $this->isA('admin')) {
            $this->service->destroy($tache);

            if (request()->header('Accept') === 'application/json') {
                return response()->json(['success' => true, 'message' => 'Événement supprimé']);
            }

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return response()->json(['message' => 'Vous n\'avez pas les droits nécessaires pour cette action'], 401);
    }

    // /**
    //  * Restaure un �l�ment supprim�
    //  *
    //  * @example Penser � utiliser un bind dans le web.php
    //  *          Route::bind('tache_id', function ($tache_id) {
    //  *              return Tache::onlyTrashed()->find($tache_id);
    //  *          });
    //  *
    //  * @param  Tache  $tache
    //  *
    //  * @return RedirectResponse|void
    //  */
    // public function undelete(Tache $tache)
    // {
    //     if ($this->can(self::ABILITY . '-delete')) {
    //         $this->service->undelete($tache);
    //         Session::put('ok', 'Restauration effectuée');

    //         return redirect(route(self::PATH_VIEWS . '.index'));
    //     }

    //     return abort(401);
    // }

    // /**
    //  * Renvoie la liste des Tache au format JSON pour leur gestion
    //  *
    //  * @return string|false|void
    //  */
    // public function json()
    // {
    //     if ($this->can(self::ABILITY . '-retrieve')) {
    //         return $this->service->json();
    //     }

    //     return abort(401);
    // }

    /**
     * @param  Tache|null  $tache
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
    public function model(?Tache $tache, string $ability)
    {
        if ($this->can(self::ABILITY . '-' . $ability) && $this->isA('admin')) {
            return view(
                self::PATH_VIEWS . '.model',
                $this->data($tache, $ability)
            );
        }

        abort(403);
    }

    /**
     * @param  User  $user
     *
     * @return View
     */
    public function showTacheBySalarie(User $user): View
    {
        if (! Auth::user()->isA('admin')) {
            Session::put('error', 'Vous n\'avez pas les droits nécessaires pour cette action');

            return view(self::PATH_VIEWS . '.index');
        }

        $salaries = $this->planningService->getSalaries();
        $tachesThisWeek = $this->service->getTachesPerWeek($user);

        $isEmpty = $tachesThisWeek->isEmpty();

        return view(self::PATH_VIEWS . '.index', compact('tachesThisWeek', 'user', 'isEmpty', 'salaries'));
    }

    /**
     * Rempli un tableau avec les données nécessaires aux vues
     *
     * @param  Tache|null  $tache
     * @param  string  $ability
     *
     * @return array<string, mixed>
     */
    private function data(?Tache $tache, string $ability): array
    {
        return [
            'tache' => $tache,
            'users' => $this->planningService->getSalaries(),
            'disabled' => $ability === 'retrieve',
            'taches' => $this->planningService->getTaches(),
            'lieux' => $this->planningService->getlieux(),
        ];
    }
}
