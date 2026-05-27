<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $idUsuario = (int) session('usuario_id');

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['nullable', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuarios', 'correo')->ignore($idUsuario, 'id_usuario'),
            ],
            'ci' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'foto' => ['nullable', 'string', 'max:3000000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $foto = $this->input('foto');

            if (! empty($foto) && ! preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $foto)) {
                $validator->errors()->add('foto', 'La foto debe ser una imagen valida.');
            }
        });
    }
}
