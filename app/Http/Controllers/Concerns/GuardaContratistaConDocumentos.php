<?php

namespace App\Http\Controllers\Concerns;

use App\Services\ContratistaDocumentoStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

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

        $manipuladorActivo = $this->valorBooleanoContratista(
            $datos,
            $request,
            $filePrefix,
            'manipulador_alimentos',
            (bool) $contratista->manipulador_alimentos
        );
        $datos['manipulador_alimentos'] = $manipuladorActivo;

        $licenciaActiva = $this->valorBooleanoContratista(
            $datos,
            $request,
            $filePrefix,
            'licencia_conduccion',
            (bool) $contratista->licencia_conduccion
        );
        $datos['licencia_conduccion'] = $licenciaActiva;

        if (! $manipuladorActivo) {
            $datos['manipulador_vigencia'] = null;
            ContratistaDocumentoStorage::eliminar($contratista->manipulador_archivo);
            $datos['manipulador_archivo'] = null;
        }

        if (! $licenciaActiva) {
            $datos['licencia_categoria'] = null;
            $datos['licencia_vencimientos'] = null;
            ContratistaDocumentoStorage::eliminar($contratista->licencia_archivo);
            ContratistaDocumentoStorage::eliminar($contratista->cedula_archivo);
            $datos['licencia_archivo'] = null;
            $datos['cedula_archivo'] = null;
        }

        $archivos = $this->recogerArchivosContratista($request, $filePrefix);
        $rutas = ContratistaDocumentoStorage::reemplazarPara(
            $tipoStorage,
            (int) $contratista->getKey(),
            $archivos,
            $contratista
        );

        $contratista->update(array_merge($datos, $rutas));

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
            'manipulador_archivo' => 'manipulador_archivo',
            'licencia_archivo' => 'licencia_archivo',
            'cedula_archivo' => 'cedula_archivo',
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function valorBooleanoContratista(
        array $datos,
        Request $request,
        string $filePrefix,
        string $campo,
        bool $valorExistente
    ): bool {
        $clave = $filePrefix === '' ? $campo : "{$filePrefix}.{$campo}";

        if (! $request->has($clave)) {
            return $valorExistente;
        }

        return filter_var($datos[$campo] ?? $request->input($clave), FILTER_VALIDATE_BOOLEAN);
    }
}
