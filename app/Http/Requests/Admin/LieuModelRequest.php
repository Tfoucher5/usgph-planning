<?php

namespace App\Http\Requests\Admin;

use App;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class LieuModelRequest extends FormRequest
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
            : Auth::user()->can('lieu-create');
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
        $rules = [];
        $rules['xxx'] = '';

        return $rules;
    }

    /**
     * @return array<string>
     */
    public function messages()
    {
        return [];
    }
}
