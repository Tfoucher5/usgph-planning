<?php

namespace App\Http\Services\Planning;

use App\Http\Repositories\Planning\CategorieRepository;
use App\Models\Planning\Categorie;

class CategorieService
{
    /**
     * @var CategorieRepository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param  CategorieRepository  $repository
     */
    public function __construct(CategorieRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a new model instance
     *
     * @param  array<mixed>  $inputs
     *
     * @return Categorie
     */
    public function store(array $inputs): Categorie
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->store($inputs);
    }

    /**
     * Update the model instance
     *
     * @param  Categorie  $categorie
     * @param  array<mixed>  $inputs
     *
     * @return Categorie
     */
    public function update(Categorie $categorie, array $inputs): Categorie
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->update($categorie, $inputs);
    }

    /**
     * Delete the model instance
     *
     * @param  Categorie  $categorie
     *
     * @return bool|null
     */
    public function destroy(Categorie $categorie)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->destroy($categorie);
    }

    /**
     * Undelete the model instance
     *
     * @param  Categorie  $categorie
     *
     * @return void
     */
    public function undelete(Categorie $categorie)
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        $this->repository->undelete($categorie);
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
