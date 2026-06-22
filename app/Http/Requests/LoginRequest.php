<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo' => 'required|email',
            'contrasena' => 'required',
            'rol' => ['required', Rule::in(['estudiante', 'profesor', 'director', 'admin', 'padre'])],
        ];
    }
}
