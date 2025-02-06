<?php

namespace App\Http\Repositories\Conge;

use App\Enums\ValidationStatus;
use App\Models\Conge\Absence;
use Auth;

class AbsenceRepository
{
    /**
     * @var Absence
     */
    protected $absence;

    /**
     * Constructor
     *
     * @param  Absence  $absence
     */
    public function __construct(Absence $absence)
    {
        $this->absence = $absence;
    }

    /**
     * Store a new model instance
     *
     * @param  array<mixed>  $inputs
     *
     * @return Absence
     */
    public function store(array $inputs): Absence
    {
        $absence = new $this->absence();
        $absence->user_id_creation = (int) Auth::id();

        $this->save($absence, $inputs);

        return $absence;
    }

    /**
     * Update the model instance
     *
     * @param  Absence  $absence
     * @param  array<mixed>  $inputs
     *
     * @return Absence
     */
    public function update(Absence $absence, array $inputs): Absence
    {
        $absence->user_id_modification = (int) Auth::id();

        $this->save($absence, $inputs);

        return $absence;
    }

    /**
     * Delete the model instance
     *
     * @param  Absence  $absence
     *
     * @return bool|null
     */
    public function destroy(Absence $absence)
    {
        $absence->user_id_suppression = (int) Auth::id();
        $absence->status = ValidationStatus::REFUSED;
        $absence->save();

        return $absence->delete();
    }

    /**
     * Undelete the model instance
     *
     * @param  Absence  $absence
     *
     * @return void
     */
    public function undelete(Absence $absence)
    {
        $absence->status = ValidationStatus::WAITING;
        $absence->restore();
    }

    /**
     * Return a JSON for index datatable
     *
     * @return string|false|void — a JSON encoded string on success or FALSE on failure
     */
    public function json()
    {
        return json_encode(
            Absence::all()
        );
    }

    /**
     * Summary of confirm
     *
     * @param  \App\Models\Conge\Absence  $absence
     *
     * @return Absence
     */
    public function confirm(Absence $absence)
    {
        $absence->user_id_modification = (int) Auth::id();
        $absence->status = ValidationStatus::VALIDATED;
        $absence->save();

        return $absence;
    }

    /**
     * Summary of refuse
     *
     * @param  \App\Models\Conge\Absence  $absence
     *
     * @return Absence
     */
    public function refuse(Absence $absence)
    {
        // dd($absence->motif_id);
        $absence->user_id_modification = (int) Auth::id();
        $absence->status = ValidationStatus::REFUSED;
        $absence->save();

        return $absence;
    }

    /**
     * Save the model instance
     *
     * @param  Absence  $absence
     * @param  array<mixed>  $inputs
     *
     * @return Absence
     */
    private function save(Absence $absence, array $inputs): Absence
    {
        $absence->user_id = (int) Auth::id();
        $absence->motif_id = $inputs['motif_id'];
        $absence->date_debut = $inputs['date_debut'];
        $absence->date_fin = $inputs['date_fin'];
        $absence->nb_of_work_days = $inputs['nb_of_work_days'];
        $absence->save();

        return $absence;
    }
}
