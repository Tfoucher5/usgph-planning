<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Authorize the request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Permet à tout utilisateur d'effectuer cette requête (tu peux restreindre ça si nécessaire)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'annee' => 'nullable|date',
            'mois' => 'nullable|integer|between:1,12',
            'semaine' => 'nullable|integer|between:1,53',
            'date_debut' => 'nullable|date|before_or_equal:date_fin',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'annee.integer' => 'L\'année doit être un nombre entier.',
            'annee.digits' => 'L\'année doit être composée de 4 chiffres.',
            'mois.integer' => 'Le mois doit être un nombre entier.',
            'mois.between' => 'Le mois doit être compris entre 1 et 12.',
            'semaine.integer' => 'La semaine doit être un nombre entier.',
            'semaine.between' => 'La semaine doit être comprise entre 1 et 53.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_debut.before_or_equal' => 'La date de début ne peut pas être après la date de fin.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after_or_equal' => 'La date de fin ne peut pas être avant la date de début.',
        ];
    }

    /**
     * Get the filters to apply to the request.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $filters = [];

        // Récupérer les données valides
        if ($this->filled('annee')) {
            $filters['annee'] = $this->input('annee');
        }

        if ($this->filled('mois')) {
            $filters['mois'] = $this->input('mois');
        }

        if ($this->filled('semaine')) {
            $filters['semaine'] = $this->input('semaine');
        }

        if ($this->filled('date_debut')) {
            $filters['date_debut'] = $this->input('date_debut');
        }

        if ($this->filled('date_fin')) {
            $filters['date_fin'] = $this->input('date_fin');
        }

        return $filters;
    }
}
