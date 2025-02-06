<?php

namespace App\Http\Repositories\Admin;

use App\Models\Admin\Archive;
use App\Models\Planning\Planning;
use Auth;
use Carbon\Carbon;

class ArchiveRepository
{
    /**
     * @var Archive
     */
    protected $archive;

    /**
     * Constructor
     *
     * @param  Archive  $archive
     */
    public function __construct(Archive $archive)
    {
        $this->archive = $archive;
    }

    // /**
    //  * Summary of store
    //  *
    //  * @param  array<mixed>  $inputs
    //  *
    //  * @return \App\Models\Admin\Archive
    //  */
    // public function store(array $inputs): Archive
    // {
    //     $archive = new $this->archive();
    //     $archive->user_id_creation = (int) Auth::id();

    //     $this->save($archive, $inputs);

    //     return $archive;
    // }

    /**
     * Summary of archivate
     *
     * @param  \App\Models\Planning\Planning  $planning
     *
     * @return \App\Models\Admin\Archive
     */
    public function archivate(Planning $planning): Archive
    {
        $archive = new $this->archive();
        $archive->user_id_creation = (int) Auth::id();

        $this->save($archive, $planning);

        return $archive;
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
    //     $archive->user_id_modification = (int) Auth::id();

    //     $this->save($archive, $inputs);

    //     return $archive;
    // }

    /**
     * Delete the model instance
     *
     * @param  Archive  $archive
     *
     * @return bool|null
     */
    public function destroy(Archive $archive)
    {
        $archive->user_id_suppression = (int) Auth::id();
        $archive->save();

        return $archive->delete();
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
        $archive->restore();
    }

    /**
     * Return a JSON for index datatable
     *
     * @return string|false|void — a JSON encoded string on success or FALSE on failure
     */
    public function json()
    {
        return json_encode(
            Archive::all()
        );
    }

    /**
     * Summary of save
     *
     * @param  \App\Models\Admin\Archive  $archive
     *
     * @return \App\Models\Admin\Archive
     */
    private function save(Archive $archive, ?Planning $planning = null): Archive
    {
        $archive->planning_id = $planning->id;
        $archive->user_id = $planning->user_id;
        $archive->nom = $planning->nom;
        $archive->categorie_nom = $planning->tache->categorie->nom;
        $archive->categorie_couleur = $planning->tache->categorie->couleur;
        $archive->lieu = $planning->lieu->nom;
        $archive->plannifier_le = $planning->plannifier_le;
        $archive->heure_debut = $planning->heure_debut;
        $archive->heure_fin = $planning->heure_fin;
        $archive->duree_tache = (string) Carbon::parse($planning->heure_debut)->diffInMinutes(Carbon::parse($planning->heure_fin));
        $archive->save();

        return $archive;
    }
}
