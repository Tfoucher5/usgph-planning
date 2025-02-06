<?php

namespace App\Http\Repositories\Conge;

use App\Models\Conge\Motif;
use Auth;

class MotifRepository
{
    /**
     * @var Motif
     */
    protected $motif;

    /**
     * Constructor
     *
     * @param  Motif  $motif
     */
    public function __construct(Motif $motif)
    {
        $this->motif = $motif;
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
        $motif = new $this->motif();
        $motif->user_id_creation = (int) Auth::id();

        $this->save($motif, $inputs);

        return $motif;
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
        $motif->user_id_modification = (int) Auth::id();

        $this->save($motif, $inputs);

        return $motif;
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
    //     $motif->user_id_suppression = (int) Auth::id();
    //     $motif->save();

    //     return $motif->delete();
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
    //     $motif->restore();
    // }

    /**
     * Return a JSON for index datatable
     *
     * @return string|false|void — a JSON encoded string on success or FALSE on failure
     */
    public function json()
    {
        return json_encode(
            Motif::all()
        );
    }

    /**
     * Save the model instance
     *
     * @param  Motif  $motif
     * @param  array<mixed>  $inputs
     *
     * @return Motif
     */
    private function save(Motif $motif, array $inputs): Motif
    {
        $motif->nom = $inputs['nom'];
        $motif->save();

        return $motif;
    }
}
