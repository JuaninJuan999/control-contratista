<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesUserRol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use ValidatesUserRol;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9._-]+$/i',
                Rule::unique('users', 'username'),
            ],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ], $this->reglasRol());
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge([
            'nombre' => 'nombre',
            'apellido' => 'apellido',
            'username' => 'username',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ], $this->atributosRol());
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'El username solo puede contener letras, números, puntos, guiones y guiones bajos.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $username = $this->input('username');

        $this->merge([
            'nombre' => is_string($this->input('nombre')) ? trim($this->input('nombre')) : $this->input('nombre'),
            'apellido' => is_string($this->input('apellido')) ? trim($this->input('apellido')) : $this->input('apellido'),
            'username' => is_string($username) ? mb_strtolower(trim($username), 'UTF-8') : $username,
            'email' => is_string($this->input('email')) ? trim($this->input('email')) : $this->input('email'),
        ]);
    }
}
