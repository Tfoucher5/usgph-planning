<?php

namespace App\Http\Services\Conge;

use App\Http\Repositories\Conge\MotifRepository;
use App\Models\Conge\Motif;
use Illuminate\Support\Facades\Session;

class MotifService
{
    /**
     * @var MotifRepository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param  MotifRepository  $repository
     */
    public function __construct(MotifRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a new model instance
     *
     * @param  array<mixed>  $inputs
     *
     * @return Motif
     */
    public function store(array $inputs): Motif
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->store($inputs);
    }

    /**
     * Update the model instance
     *
     * @param  Motif  $motif
     * @param  array<mixed>  $inputs
     *
     * @return Motif
     */
    public function update(Motif $motif, array $inputs): Motif
    {
        //
        // Règles de gestion à appliquer avant l'enregistrement en base
        //

        return $this->repository->update($motif, $inputs);
    }

    // /**
    //  * Delete the model instance
    //  *
    //  * @param  Motif  $motif
    //  *
    //  * @return bool|null
    //  */
    // public function destroy(Motif $motif)
    // {
    //     //
    //     // Règles de gestion à appliquer avant l'enregistrement en base
    //     //
    //     if ($motif['cannot_delete'] === 0) {
    //         return $this->repository->destroy($motif);
    //     }
    //     Session::put('error', 'Ce motif ne peut pas être supprimé');

    //     return false;
    // }

    // /**
    //  * Undelete the model instance
    //  *
    //  * @param  Motif  $motif
    //  *
    //  * @return void
    //  */
    // public function undelete(Motif $motif)
    // {
    //     //
    //     // Règles de gestion à appliquer avant l'enregistrement en base
    //     //

    //     $this->repository->undelete($motif);
    // }

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
