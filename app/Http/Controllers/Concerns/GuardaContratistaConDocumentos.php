<?php

namespace App\Http\Controllers\Concerns;

use App\Services\ContratistaDocumentoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait GuardaContratistaConDocumentos
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $datos
     */
    protected function crearContratistaConDocumentos(string $modelClass, string $tipoStorage, array $datos, Request $request, string $filePrefix = ''): Model
    {
        $fileKeys = array_keys($this->archivosContratistaMap());
        $datos = collect($datos)->except($fileKeys)->all();

        /** @var Model $contratista */
        $contratista = $modelClass::query()->create($datos);

        $archivos = $this->recogerArchivosContratista($request, $filePrefix);
        $rutas = ContratistaDocumentoStorage::guardarPara($tipoStorage, (int) $contratista->getKey(), $archivos);

        if ($rutas !== []) {
            $contratista->update($rutas);
        }

        return $contratista;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    protected function actualizarContratistaConDocumentos(Model $contratista, string $tipoStorage, array $datos, Request $request, string $filePrefix = ''): Model
    {
        $fileKeys = array_keys($this->archivosContratistaMap());
        $datos = collect($datos)->except($fileKeys)->all();

        if (($datos['licencia_conduccion'] ?? false) === false) {
            foreach ($fileKeys as $campo) {
                $datos[$campo] = null;
            }
        }

        $contratista->update($datos);

        $archivos = $this->recogerArchivosContratista($request, $filePrefix);
        $rutas = ContratistaDocumentoStorage::guardarPara($tipoStorage, (int) $contratista->getKey(), $archivos);

        if ($rutas !== []) {
            $contratista->update($rutas);
        }

        return $contratista->fresh();
    }

    /**
     * @return array<string, UploadedFile>
     */
    protected function recogerArchivosContratista(Request $request, string $filePrefix = ''): array
    {
        $archivos = [];

        foreach ($this->archivosContratistaMap() as $campo => $nombreRequest) {
            $clave = $filePrefix === '' ? $nombreRequest : "{$filePrefix}.{$nombreRequest}";

            if ($request->hasFile($clave)) {
                $archivos[$campo] = $request->file($clave);
            }
        }

        return $archivos;
    }

    /**
     * @return array<string, string>
     */
    protected function archivosContratistaMap(): array
    {
        return [
            'licencia_archivo' => 'licencia_archivo',
            'licencia_vencimiento_archivo' => 'licencia_vencimiento_archivo',
            'tarjeta_propiedad_archivo' => 'tarjeta_propiedad_archivo',
        ];
    }
}
