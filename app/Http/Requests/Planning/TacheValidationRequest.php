<?php

namespace App\Http\Requests\Planning;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class TacheValidationRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return App::environment('testing')
            ? true
            : Auth::user()->can('tache-create');
    }

    /**
     * Règles de validation appliquées à la requête.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_validated' => 'required|boolean',
        ];
    }

    /**
     * Messages personnalisés pour chaque règle de validation.
     *
     * @return array<string>
     */
    public function messages(): array
    {
        return [
        ];
    }
}
