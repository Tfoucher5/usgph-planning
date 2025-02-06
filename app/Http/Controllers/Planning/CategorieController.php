<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\CategorieModelRequest;
use App\Http\Services\Planning\CategorieService;
use App\Models\Planning\Categorie;
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

class CategorieController extends Controller
{
    private const ABILITY = 'categorie';

    private const PATH_VIEWS = 'categorie';

    /**
     * @var CategorieService
     */
    private $service;

    /**
     * Constructor
     *
     * @param  CategorieService  $service
     */
    public function __construct(CategorieService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
        Session::put('level_menu_1', 'Plannings');
        Session::put('level_menu_2', self::ABILITY);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|RedirectResponse|View|void
     */
    public function index()
    {
        if ($this->can(self::ABILITY . '-retrieve')) {
            return view(self::PATH_VIEWS . '.index');
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
     * @param  CategorieModelRequest  $request
     *
     * @return RedirectResponse|void
     */
    public function store(CategorieModelRequest $request)
    {
        if ($this->can(self::ABILITY . '-create')) {
            $data = $request->all();

            $categorie = $this->service->store($data);
            Session::put('ok', 'Création effectuée');

            return redirect(self::PATH_VIEWS);
        }

        return abort(401);
    }

    /**
     * @param  Categorie  $categorie
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
    public function show(Categorie $categorie)
    {
        return $this->model($categorie, 'retrieve');
    }

    /**
     * @param  Categorie  $categorie
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
    public function edit(Categorie $categorie)
    {
        return $this->model($categorie, 'update');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CategorieModelRequest  $request
     * @param  Categorie  $categorie
     *
     * @return RedirectResponse|void
     */
    public function update(CategorieModelRequest $request, Categorie $categorie)
    {
        if ($this->can(self::ABILITY . '-update')) {
            $this->service->update($categorie, $request->all());
            Session::put('ok', 'Mise à jour effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Categorie  $categorie
     *
     * @return RedirectResponse|void
     */
    public function destroy(Categorie $categorie)
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $this->service->destroy($categorie);
            Session::put('ok', 'Suppression effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Restaure un �l�ment supprim�
     *
     * @example Penser � utiliser un bind dans le web.php
     *          Route::bind('categorie_id', function ($categorie_id) {
     *              return Categorie::onlyTrashed()->find($categorie_id);
     *          });
     *
     * @param  Categorie  $categorie
     *
     * @return RedirectResponse|void
     */
    public function undelete(Categorie $categorie)
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $this->service->undelete($categorie);
            Session::put('ok', 'Restauration effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Renvoie la liste des Categorie au format JSON pour leur gestion
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
     * Rempli un tableau avec les données nécessaires aux vues
     *
     * @param  Categorie  $categorie|null
     * @param  string  $ability
     *
     * @return array<string, mixed>
     */
    private function data(?Categorie $categorie, string $ability): array
    {
        return [
            'categorie' => $categorie,
            // variables � ajouter
            'disabled' => $ability === 'retrieve',
        ];
    }

    /**
     * @param  Categorie  $categorie|null
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
    private function model(?Categorie $categorie, string $ability)
    {
        if ($this->can(self::ABILITY . '-' . $ability)) {
            return view(
                self::PATH_VIEWS . '.model',
                $this->data($categorie, $ability)
            );
        }

        return abort(401);
    }
}
