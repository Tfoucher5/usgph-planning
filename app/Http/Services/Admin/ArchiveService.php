<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\ArchiveRepository;
use App\Models\Admin\Archive;
use App\Models\Planning\Planning;
use Session;

class ArchiveService
{
    /**
     * @var ArchiveRepository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param  ArchiveRepository  $repository
     */
    public function __construct(ArchiveRepository $repository)
    {
        $this->repository = $repository;
    }

    // /**
    //  * Update the model instance
    //  *
    //  * @param  Archive  $archive
    //  * @param  array<mixed>  $inputs
    //  *
    //  * @return Archive
    //  */
    // public function update(Archive $archive, array $inputs): Archive
    // {
    //     //
    //     // Règles de gestion à appliquer avant l'enregistrement en base
    //     //

    //     return $this->repository->update($archive, $inputs);
    // }

    // /**
    //  * Summary of store
    //  *
    //  * @param  array<mixed>  $inputs
    //  *
    //  * @return \App\Models\Admin\Archive
    //  */
    // public function store(array $inputs): Archive
    // {
    //     return $this->repository->store($inputs);
    // }

    /**
     * Archiver une tâche
     *
     * @param  \App\Models\Planning\Planning  $planning
     *
     * @return \App\Models\Admin\Archive
     */
    public function archivate(Planning $planning): Archive
    {
        $archive = Archive::where('planning_id', $planning->id)->first();

        if ($archive) {
            Session::put('erreur', 'Cette tache à déjà été archivée');

            return $archive;
        }

        return $this->repository->archivate($planning);
    }

    /**
     * Delete the model instance
     *
     * @param  Archive  $archive
     *
     * @return bool|null
     */
    public function destroy(Archive $archive)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->destroy($archive);
    }

    /**
     * Undelete the model instance
     *
     * @param  Archive  $archive
     *
     * @return void
     */
    public function undelete(Archive $archive)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        $this->repository->undelete($archive);
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
}
