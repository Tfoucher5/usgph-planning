<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LieuModelRequest;
use App\Http\Services\Admin\LieuService;
use App\Models\Admin\Lieu;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\NotLocaleAwareException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Session;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Translation\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;

class LieuController extends Controller
{
    private const ABILITY = 'lieu';

    private const PATH_VIEWS = 'lieu';

    /**
     * @var LieuService
     */
    private $service;

    /**
     * Constructor
     *
     * @param  LieuService  $service
     */
    public function __construct(LieuService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
        Session::put('level_menu_1', 'Admins');
        Session::put('level_menu_2', self::ABILITY);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|RedirectResponse|View|void
     */
    public function index()
    {
        Session::put('level_menu_2', self::ABILITY);

        if ($this->can(self::ABILITY . '-retrieve')) {
            $lieux = Lieu::paginate(10);

            return view(self::PATH_VIEWS . '.index', compact('lieux'));
        }

        return abort(401);
    }

    /**
     * @return View|Factory|string
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
     * @param  LieuModelRequest  $request
     *
     * @return RedirectResponse|void
     */
    public function store(LieuModelRequest $request)
    {
        if ($this->can(self::ABILITY . '-create')) {
            $data = $request->all();

            $lieu = $this->service->store($data);
            Session::put('ok', 'Création effectuée');

            return redirect(self::PATH_VIEWS);
        }

        return abort(401);
    }

    /**
     * @param  Lieu  $lieu
     *
     * @return View|Factory|string
     *
     * @throws BindingResolutionException
     * @throws RouteNotFoundException
     * @throws InvalidFormatException
     * @throws NotLocaleAwareException
     * @throws ExceptionInvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function show(Lieu $lieu)
    {
        return $this->model($lieu, 'retrieve');
    }

    /**
     * @param  Lieu  $lieu
     *
     * @return View|Factory|string
     *
     * @throws BindingResolutionException
     * @throws RouteNotFoundException
     * @throws InvalidFormatException
     * @throws NotLocaleAwareException
     * @throws ExceptionInvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function edit(Lieu $lieu)
    {
        return $this->model($lieu, 'update');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  LieuModelRequest  $request
     * @param  Lieu  $lieu
     *
     * @return RedirectResponse|void
     */
    public function update(LieuModelRequest $request, Lieu $lieu)
    {
        if ($this->can(self::ABILITY . '-update')) {
            $this->service->update($lieu, $request->all());
            Session::put('ok', 'Mise à jour effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Lieu  $lieu
     *
     * @return RedirectResponse|void
     */
    public function destroy(Lieu $lieu)
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $this->service->destroy($lieu);
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
            $deletedLieux = Lieu::onlyTrashed()
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view(self::PATH_VIEWS . '.corbeille', compact('deletedLieux'));
        }

        return abort(401);
    }

    /**
     * Restaure un �l�ment supprim�
     *
     * @example Penser � utiliser un bind dans le web.php
     *          Route::bind('lieu_id', function ($lieu_id) {
     *              return Lieu::onlyTrashed()->find($lieu_id);
     *          });
     *
     * @param  Lieu  $lieu
     *
     * @return RedirectResponse|void
     */
    public function undelete(Lieu $lieu)
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $this->service->undelete($lieu);
            Session::put('ok', 'Restauration effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    // /**
    //  * Renvoie la liste des Lieu au format JSON pour leur gestion
    //  * @return string|false|void � a JSON encoded string on success or FALSE on failure
    //  */
    // public function json()
    // {
    //     if ($this->can(self::ABILITY . '-retrieve')) {
    //         return $this->service->json();
    //     }
    // }

    /**
     * Rempli un tableau avec les données nécessaires aux vues
     *
     * @param  Lieu  $lieu|null
     * @param  string  $ability
     *
     * @return array<string, mixed>
     */
    private function data(?Lieu $lieu, string $ability): array
    {
        return [
            'lieu' => $lieu,
            // variables � ajouter
            'disabled' => $ability === 'retrieve',
        ];
    }

    /**
     * @param  Lieu  $lieu|null
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
    private function model(?Lieu $lieu, string $ability)
    {
        if ($this->can(self::ABILITY . '-' . $ability)) {
            return view(
                self::PATH_VIEWS . '.model',
                $this->data($lieu, $ability)
            );
        }

        return abort(401);
    }
}
