<?php

namespace App\Http\Controllers\Conge;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conge\MotifModelRequest;
use App\Http\Services\Conge\MotifService;
use App\Models\Conge\Motif;
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

class MotifController extends Controller
{
    private const ABILITY = 'motif';

    private const PATH_VIEWS = 'motif';

    /**
     * @var MotifService
     */
    private $service;

    /**
     * Constructor
     *
     * @param  MotifService  $service
     */
    public function __construct(MotifService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
        Session::put('level_menu_1', 'Conges');
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
            $motifs = Motif::paginate(10);

            return view(self::PATH_VIEWS . '.index', compact('motifs'));
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
     * @param  MotifModelRequest  $request
     *
     * @return RedirectResponse|void
     */
    public function store(MotifModelRequest $request)
    {
        if ($this->can(self::ABILITY . '-create')) {
            $data = $request->all();

            $motif = $this->service->store($data);
            Session::put('ok', 'Création effectuée');

            return redirect(self::PATH_VIEWS);
        }

        return abort(401);
    }

    /**
     * @param  Motif  $motif
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
    public function show(Motif $motif)
    {
        return $this->model($motif, 'retrieve');
    }

    /**
     * @param  Motif  $motif
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
    public function edit(Motif $motif)
    {
        return $this->model($motif, 'update');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  MotifModelRequest  $request
     * @param  Motif  $motif
     *
     * @return RedirectResponse|void
     */
    public function update(MotifModelRequest $request, Motif $motif)
    {
        if ($this->can(self::ABILITY . '-update')) {
            $this->service->update($motif, $request->all());
            Session::put('ok', 'Mise à jour effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  Motif  $motif
    //  *
    //  * @return RedirectResponse|void
    //  */
    // public function destroy(Motif $motif)
    // {
    //     if ($this->can(self::ABILITY . '-delete')) {
    //         $this->service->destroy($motif);
    //         Session::put('ok', 'Suppression effectuée');

    //         return redirect(route(self::PATH_VIEWS . '.index'));
    //     }

    //     return abort(401);
    // }

    /**
     * Summary of corbeille
     *
     * @return View
     */
    public function corbeille()
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $deletedMotifs = motif::onlyTrashed()
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view(self::PATH_VIEWS . '.corbeille', compact('deletedMotifs'));
        }

        return abort(401);
    }

    // /**
    //  * Restaure un �l�ment supprim�
    //  *
    //  * @example Penser � utiliser un bind dans le web.php
    //  *          Route::bind('motif_id', function ($motif_id) {
    //  *              return Motif::onlyTrashed()->find($motif_id);
    //  *          });
    //  *
    //  * @param  Motif  $motif
    //  *
    //  * @return RedirectResponse|void
    //  */
    // public function undelete(Motif $motif)
    // {
    //     if ($this->can(self::ABILITY . '-delete')) {
    //         $this->service->undelete($motif);
    //         Session::put('ok', 'Restauration effectuée');

    //         return redirect(route(self::PATH_VIEWS . '.index'));
    //     }

    //     return abort(401);
    // }

    /**
     * Renvoie la liste des Motif au format JSON pour leur gestion
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
     * @param  Motif  $motif|null
     * @param  string  $ability
     *
     * @return array<string, mixed>
     */
    private function data(?Motif $motif, string $ability): array
    {
        return [
            'motif' => $motif,
            // variables � ajouter
            'disabled' => $ability === 'retrieve',
        ];
    }

    /**
     * @param  Motif  $motif|null
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
    private function model(?Motif $motif, string $ability)
    {
        if ($this->can(self::ABILITY . '-' . $ability)) {
            return view(
                self::PATH_VIEWS . '.model',
                $this->data($motif, $ability)
            );
        }

        return abort(401);
    }
}
