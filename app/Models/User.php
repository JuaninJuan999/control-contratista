<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\UserRol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'nombre', 'apellido', 'username', 'email', 'password', 'activo', 'rol'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function esSuperadmin(): bool
    {
        return $this->rol === UserRol::SUPERADMIN;
    }

    public function puedeAccederModuloUsabilidad(): bool
    {
        return $this->esSuperadmin();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<UserUsabilidadSesion, $this>
     */
    public function usabilidadSesiones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserUsabilidadSesion::class);
    }

    public function soloConsulta(): bool
    {
        return $this->rol === UserRol::CONSULTA;
    }

    public function puedeEditar(): bool
    {
        return in_array($this->rol, [UserRol::SUPERADMIN, UserRol::ADMIN, UserRol::OPERATIVO], true);
    }

    public function puedeAccederModuloUsuarios(): bool
    {
        return in_array($this->rol, [UserRol::SUPERADMIN, UserRol::ADMIN], true);
    }

    public function puedeEliminarUsuarios(): bool
    {
        return $this->rol === UserRol::SUPERADMIN;
    }

    public function puedeSerGestionadoPor(?self $actor): bool
    {
        if ($actor === null) {
            return false;
        }

        if ($this->esSuperadmin()) {
            return $actor->esSuperadmin();
        }

        return $actor->puedeAccederModuloUsuarios();
    }

    public function etiquetaRol(): string
    {
        return UserRol::etiqueta($this->rol);
    }
}
