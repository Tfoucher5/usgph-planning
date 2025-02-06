<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\LieuRepository;
use App\Models\Admin\Lieu;

class LieuService
{
    /**
     * @var LieuRepository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param  LieuRepository  $repository
     */
    public function __construct(LieuRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a new model instance
     *
     * @param  array<mixed>  $inputs
     *
     * @return Lieu
     */
    public function store(array $inputs): Lieu
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->store($inputs);
    }

    /**
     * Update the model instance
     *
     * @param  Lieu  $lieu
     * @param  array<mixed>  $inputs
     *
     * @return Lieu
     */
    public function update(Lieu $lieu, array $inputs): Lieu
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->update($lieu, $inputs);
    }

    /**
     * Delete the model instance
     *
     * @param  Lieu  $lieu
     *
     * @return bool|null
     */
    public function destroy(Lieu $lieu)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->destroy($lieu);
    }

    /**
     * Undelete the model instance
     *
     * @param  Lieu  $lieu
     *
     * @return void
     */
    public function undelete(Lieu $lieu)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        $this->repository->undelete($lieu);
    }

    // /**
    //  * Return a JSON for index datatable
    //  *
    //  * @return string|false|void — a JSON encoded string on success or FALSE on failure
    //  */
    // public function json()
    // {
    //     //
    //     // Règles de gestion à appliquer avant l'enregistrement en base
    //     //

    //     return $this->repository->json();
    // }
}
