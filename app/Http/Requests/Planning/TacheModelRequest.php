<?php

namespace App\Http\Requests\Planning;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class TacheModelRequest extends FormRequest
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
            : Auth::user()->can('tache-create');
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
            'lieu_id' => ['nullable', 'exists:lieux,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'heure_debut' => ['nullable', 'date_format:H:i'],
            'heure_fin' => ['nullable', 'date_format:H:i', 'after:heure_debut'],
            'jour' => ['nullable', 'integer'],

        ];
    }

    /**
     * @return array<string>
     */
    public function messages()
    {
        return [
            'nom.nullable' => 'Le nom de la tâche est facultatif.',
            'nom.string' => 'Le nom de la tâche doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de la tâche ne peut pas dépasser 255 caractères.',
            'lieu_id.nullable' => 'Le lieu est facultatif.',
            'lieu_id.exists' => 'Le lieu sélectionné n\'existe pas.',
            'user_id.nullable' => 'L\'identifiant du salarié est facultatif.',
            'user_id.exists' => 'L\'utilisateur sélectionné n\'existe pas.',
            'heure_debut.nullable' => 'L\'heure de début est facultative.',
            'heure_debut.date_format' => 'L\'heure de début doit être au format HH:mm.',
            'heure_fin.nullable' => 'L\'heure de fin est facultative.',
            'heure_fin.date_format' => 'L\'heure de fin doit être au format HH:mm.',
            'heure_fin.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'jour.nullable' => 'Le jour est facultatif.',
            'jour.integer' => 'Le jour doit être un nombre entier.',

        ];
    }
}
