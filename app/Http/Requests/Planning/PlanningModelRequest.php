<?php

namespace App\Http\Requests\Planning;

use App;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class PlanningModelRequest extends FormRequest
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
        : Auth::user()->can('planning-create');
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
            'nom' => ['nullable', 'string', 'max:255'],
            'plannifier_le' => ['nullable', 'date_format:Y-m-d'],
            'heure_debut_heure' => ['nullable', 'regex:/^(0[0-9]|1[0-9]|2[0-3])$/'],
            'heure_debut_minute' => ['nullable', 'regex:/^(00|15|30|45)$/'],
            'heure_fin_heure' => ['nullable', 'regex:/^(0[0-9]|1[0-9]|2[0-3])$/'],
            'heure_fin_minute' => ['nullable', 'regex:/^(00|15|30|45)$/'],
            'is_validated' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

            'plannifier_le.required' => 'La date de planification est obligatoire.',
            'plannifier_le.date_format' => 'La date de planification doit être au format AAAA-MM-JJ.',

            'heure_debut_heure.required' => "L'heure de début (heure) est obligatoire.",
            'heure_debut_heure.regex' => 'L\'heure de début doit être comprise entre "00" to "23".',

            'heure_debut_minute.required' => "L'heure de début (minute) est obligatoire.",
            'heure_debut_minute.regex' => 'Les minutes fonctionne au quart d\'heure.',

            'heure_fin_heure.required' => "L'heure de fin (heure) est obligatoire.",
            'heure_fin_heure.regex' => 'L\'heure de début doit être comprise entre "00" to "23".',

            'heure_fin_minute.required' => "L'heure de fin (minute) est obligatoire.",
            'heure_fin_minute.regex' => 'Les minutes fonctionne au quart d\'heure.',

            'is_validated.boolean' => 'La validation doit être vraie ou fausse.',
        ];
    }
}
