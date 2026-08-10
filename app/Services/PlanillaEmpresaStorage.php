<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PlanillaEmpresaStorage
{
    public static function guardar(int $empresaId, UploadedFile $archivo): string
    {
        return $archivo->store("planillas/empresas/{$empresaId}", 'public');
    }

    public static function eliminar(?string $ruta): void
    {
        if ($ruta === null || $ruta === '') {
            return;
        }

        Storage::disk('public')->delete($ruta);
    }

    public static function urlPublica(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $ruta), '/');
    }
}
