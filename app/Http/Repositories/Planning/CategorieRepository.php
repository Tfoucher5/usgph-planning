<?php

namespace App\Http\Repositories\Planning;

use App\Models\Planning\Categorie;
use Auth;

class CategorieRepository
{
    /**
     * @var Categorie
     */
    protected $categorie;

    /**
     * Constructor
     *
     * @param  Categorie  $categorie
     */
    public function __construct(Categorie $categorie)
    {
        $this->categorie = $categorie;
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
        $categorie = new $this->categorie();
        $categorie->user_id_creation = (int) Auth::id();

        $this->save($categorie, $inputs);

        return $categorie;
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
        $categorie->user_id_modification = (int) Auth::id();

        $this->save($categorie, $inputs);

        return $categorie;
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
        $categorie->user_id_suppression = (int) Auth::id();
        $categorie->save();

        return $categorie->delete();
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
        $categorie->restore();
    }

    /**
     * Return a JSON for index datatable
     *
     * @return string|false|void — a JSON encoded string on success or FALSE on failure
     */
    public function json()
    {
        return json_encode(
            Categorie::all()
        );
    }

    /**
     * Save the model instance
     *
     * @param  Categorie  $categorie
     * @param  array<mixed>  $inputs
     *
     * @return Categorie
     */
    private function save(Categorie $categorie, array $inputs): Categorie
    {
        $categorie->nom = $inputs['nom'];
        $categorie->couleur = $inputs['couleur'];
        $categorie->save();

        return $categorie;
    }
}
