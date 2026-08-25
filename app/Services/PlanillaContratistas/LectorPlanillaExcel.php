<?php

namespace App\Services\PlanillaContratistas;

use App\Support\NumeroDocumento;
use App\Support\PlanillaContratistasColumnas;
use App\Support\TiposDocumento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LectorPlanillaExcel
{
    /**
     * @return list<PlanillaFilaContratista>
     */
    public function leer(UploadedFile $archivo): array
    {
        $ruta = $archivo->getRealPath();
        if ($ruta === false) {
            throw new \InvalidArgumentException('No se pudo leer el archivo Excel.');
        }

        $hoja = IOFactory::load($ruta)->getActiveSheet();
        $filas = $hoja->toArray(null, true, true, false);

        if ($filas === []) {
            throw new \InvalidArgumentException('El archivo Excel está vacío.');
        }

        $encabezados = array_shift($filas);
        if (! is_array($encabezados)) {
            throw new \InvalidArgumentException('No se encontraron encabezados en la primera fila.');
        }

        $mapa = $this->mapearColumnas($encabezados);
        if (! isset($mapa['documento'])) {
            throw new \InvalidArgumentException('Falta la columna obligatoria "Documento" en la primera fila.');
        }

        $resultado = [];
        $vistos = [];

        foreach ($filas as $indice => $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $numeroFila = $indice + 2;
            $documento = $this->valorCelda($fila, $mapa['documento'] ?? null);

            if ($documento === '') {
                continue;
            }

            $tipoDocumento = $this->normalizarTipoDocumento($this->valorCelda($fila, $mapa['tipo_documento'] ?? null));
            $documento = $this->normalizarDocumento($documento, $tipoDocumento);
            $clave = mb_strtoupper($tipoDocumento, 'UTF-8').'|'.$documento;

            if (isset($vistos[$clave])) {
                continue;
            }
            $vistos[$clave] = true;

            $nombre = $this->valorCelda($fila, $mapa['nombres_apellidos'] ?? null);
            $arl = $this->valorCelda($fila, $mapa['arl'] ?? null);

            $resultado[] = new PlanillaFilaContratista(
                numeroFila: $numeroFila,
                numeroDocumento: $documento,
                tipoDocumento: $tipoDocumento,
                nombresApellidos: $nombre !== '' ? $nombre : null,
                tipoContratista: $this->normalizarTipoContratista($this->valorCelda($fila, $mapa['tipo_contratista'] ?? null)),
                arl: $arl !== '' ? $arl : null,
            );
        }

        if ($resultado === []) {
            throw new \InvalidArgumentException('No se encontraron filas con documento en el Excel.');
        }

        return $resultado;
    }

    /**
     * @param  array<int, mixed>  $encabezados
     * @return array<string, int>
     */
    private function mapearColumnas(array $encabezados): array
    {
        $mapa = [];
        $alias = PlanillaContratistasColumnas::aliasEncabezados();

        foreach ($encabezados as $indice => $titulo) {
            $normalizado = $this->normalizarEncabezado(is_scalar($titulo) ? (string) $titulo : '');

            if ($normalizado === '') {
                continue;
            }

            foreach ($alias as $campo => $variantes) {
                if (isset($mapa[$campo])) {
                    continue;
                }

                foreach ($variantes as $variante) {
                    if ($normalizado === $this->normalizarEncabezado($variante)) {
                        $mapa[$campo] = (int) $indice;
                        break 2;
                    }
                }
            }
        }

        return $mapa;
    }

    private function normalizarEncabezado(string $texto): string
    {
        $texto = Str::ascii(mb_strtolower(trim($texto), 'UTF-8'));

        return preg_replace('/\s+/', ' ', $texto) ?? '';
    }

    /**
     * @param  array<int, mixed>  $fila
     */
    private function valorCelda(array $fila, ?int $indice): string
    {
        if ($indice === null || ! array_key_exists($indice, $fila)) {
            return '';
        }

        $valor = $fila[$indice];

        if ($valor === null) {
            return '';
        }

        return trim((string) $valor);
    }

    private function normalizarDocumento(string $documento, string $tipoDocumento): string
    {
        return NumeroDocumento::normalizar($documento, $tipoDocumento) ?? '';
    }

    private function normalizarTipoDocumento(string $tipo): string
    {
        $tipo = mb_strtoupper(trim($tipo), 'UTF-8');

        if ($tipo === '') {
            return 'CC';
        }

        if (in_array($tipo, TiposDocumento::valores(), true)) {
            return $tipo;
        }

        return match ($tipo) {
            'CEDULA', 'C.C.', 'C.C', 'CÉDULA' => 'CC',
            'C.E.', 'C.E', 'CÉDULA EXTRANJERÍA', 'CEDULA EXTRANJERIA' => 'CE',
            'PASAPORTE' => 'PAS',
            'T.I.', 'T.I' => 'TI',
            default => 'CC',
        };
    }

    private function normalizarTipoContratista(string $valor): string
    {
        $valor = mb_strtolower(trim($valor), 'UTF-8');

        if (in_array($valor, ['interno', 'int', 'i', 'internos'], true)) {
            return 'interno';
        }

        return 'externo';
    }
}
