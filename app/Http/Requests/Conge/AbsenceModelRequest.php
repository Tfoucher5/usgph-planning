<?php

namespace App\Http\Requests\Conge;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class AbsenceModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return App::environment('testing')
            ? true
            : Auth::user()->can('absence-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @example https://laravel.com/docs/validation#available-validation-rules
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'motif_id' => ['required', 'exists:motifs,id'],
            'date_debut' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'date_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'after_or_equal:date_debut'],
        ];
    }

    /**
     * @return array<string>
     */
    public function messages()
    {
        return [
            'motif_id.required' => 'Le motif est obligatoire.',
            'motif_id.exists' => 'Le motif sélectionné est invalide.',

            'date_debut.required' => 'La date de début est obligatoire.',
            'date_debut.date_format' => 'Le format de la date de début doit être YYYY-MM-DD.',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être dans le passé.',

            'date_fin.required' => 'La date de fin est obligatoire.',
            'date_fin.date_format' => 'Le format de la date de fin doit être YYYY-MM-DD.',
            'date_fin.after_or_equal' => 'La date de fin ne peut pas être dans le passé.',
            'date_fin.after' => 'La date de fin doit être après la date de début.',
        ];
    }
}
