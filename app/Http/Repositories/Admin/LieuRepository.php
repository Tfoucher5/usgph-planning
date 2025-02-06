<?php

namespace App\Http\Repositories\Admin;

use App\Models\Admin\Lieu;
use Auth;

class LieuRepository
{
    /**
     * @var Lieu
     */
    protected $lieu;

    /**
     * Constructor
     *
     * @param  Lieu  $lieu
     */
    public function __construct(Lieu $lieu)
    {
        $this->lieu = $lieu;
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
        $lieu = new $this->lieu();
        $lieu->user_id_creation = (int) Auth::id();

        $this->save($lieu, $inputs);

        return $lieu;
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
        $lieu->user_id_modification = (int) Auth::id();

        $this->save($lieu, $inputs);

        return $lieu;
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
        $lieu->user_id_suppression = (int) Auth::id();
        $lieu->save();

        return $lieu->delete();
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
        $lieu->restore();
    }

    // /**
    //  * Return a JSON for index datatable
    //  *
    //  * @return string|false|void — a JSON encoded string on success or FALSE on failure
    //  */
    // public function json()
    // {
    //     return json_encode(
    //         Lieu::all()
    //     );
    // }

    /**
     * Save the model instance
     *
     * @param  Lieu  $lieu
     * @param  array<mixed>  $inputs
     *
     * @return Lieu
     */
    private function save(Lieu $lieu, array $inputs): Lieu
    {
        $lieu->nom = $inputs['nom'];
        $lieu->save();

        return $lieu;
    }
}
