<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\Admin\ArchiveService;
use App\Models\Admin\Archive;
use App\Models\Planning\Planning;
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

class ArchiveController extends Controller
{
    private const ABILITY = 'archive';

    private const PATH_VIEWS = 'archive';

    /**
     * @var ArchiveService
     */
    private $service;

    /**
     * Constructor
     *
     * @param  ArchiveService  $service
     */
    public function __construct(ArchiveService $service)
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

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  ArchiveModelRequest  $request
    //  *
    //  * @return RedirectResponse|void
    //  */
    // public function store(ArchiveModelRequest $request)
    // {
    //     if ($this->can(self::ABILITY . '-create')) {
    //         $data = $request->all();
    //         $this->service->store($data);
    //         Session::put('ok', 'Création effectuée');

    //         return redirect(self::PATH_VIEWS);
    //     }

    //     return abort(401);
    // }

    /**
     * @param  Archive  $archive
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
    public function show(Archive $archive)
    {
        return $this->model($archive, 'retrieve');
    }

    /**
     * @param  Archive  $archive
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
    public function edit(Archive $archive)
    {
        return $this->model($archive, 'update');
    }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  ArchiveModelRequest  $request
    //  * @param  Archive  $archive
    //  *
    //  * @return RedirectResponse|void
    //  */
    // public function update(ArchiveModelRequest $request, Archive $archive)
    // {
    //     if ($this->can(self::ABILITY . '-update')) {
    //         $this->service->update($archive, $request->all());
    //         Session::put('ok', 'Mise à jour effectuée');

    //         return redirect(route(self::PATH_VIEWS . '.index'));
    //     }

    //     return abort(401);
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Archive  $archive
     *
     * @return RedirectResponse|void
     */
    public function destroy(Archive $archive)
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $this->service->destroy($archive);
            Session::put('ok', 'Suppression effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Restaure un �l�ment supprim�
     *
     * @example Penser � utiliser un bind dans le web.php
     *          Route::bind('archive_id', function ($archive_id) {
     *              return Archive::onlyTrashed()->find($archive_id);
     *          });
     *
     * @param  Archive  $archive
     *
     * @return RedirectResponse|void
     */
    public function undelete(Archive $archive)
    {
        if ($this->can(self::ABILITY . '-delete')) {
            $this->service->undelete($archive);
            Session::put('ok', 'Restauration effectuée');

            return redirect(route(self::PATH_VIEWS . '.index'));
        }

        return abort(401);
    }

    /**
     * Renvoie la liste des Archive au format JSON pour leur gestion
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
     * Summary of archiver
     *
     * @param  \App\Models\Planning\Planning  $planning
     *
     * @return RedirectResponse|void
     */
    public function archivate(Planning $planning)
    {
        if ($this->isA('admin')) {
            /**
             * @var Planning
             */
            $planning = $planning::with('lieu')->find($planning->id);
            $this->service->archivate($planning);
            Session::put('ok', 'Archivage effectuée');

            return redirect()->back();
        }

        return abort(401);
    }

    /**
     * Rempli un tableau avec les données nécessaires aux vues
     *
     * @param  Archive  $archive|null
     * @param  string  $ability
     *
     * @return array<string, mixed>
     */
    private function data(?Archive $archive, string $ability): array
    {
        return [
            'archive' => $archive,
            // variables � ajouter
            'disabled' => $ability === 'retrieve',
        ];
    }

    /**
     * @param  Archive  $archive|null
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
    private function model(?Archive $archive, string $ability)
    {
        if ($this->can(self::ABILITY . '-' . $ability)) {
            return view(
                self::PATH_VIEWS . '.model',
                $this->data($archive, $ability)
            );
        }

        return abort(401);
    }
}
