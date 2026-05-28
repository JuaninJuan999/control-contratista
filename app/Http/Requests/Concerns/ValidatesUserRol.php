<?php

namespace App\Http\Requests\Concerns;

use App\Support\UserRol;
use Illuminate\Validation\Rule;

trait ValidatesUserRol
{
    /**
     * @return array<string, mixed>
     */
    protected function reglasRol(): array
    {
        $asignables = array_keys(UserRol::asignablesPara($this->user()));

        return [
            'rol' => ['required', 'string', Rule::in($asignables)],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function atributosRol(): array
    {
        return [
            'rol' => 'rol',
        ];
    }
}
