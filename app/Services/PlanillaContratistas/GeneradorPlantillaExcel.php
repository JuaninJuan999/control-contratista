<?php

namespace App\Services\PlanillaContratistas;

use App\Support\PlanillaContratistasColumnas;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneradorPlantillaExcel
{
    public static function descargar(): StreamedResponse
    {
        $spreadsheet = self::crearLibro();
        $nombre = PlanillaContratistasColumnas::ARCHIVO_PLANTILLA;

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public static function crearLibro(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('Planilla');

        foreach (PlanillaContratistasColumnas::ENCABEZADOS as $indice => $encabezado) {
            $columna = chr(ord('A') + $indice);
            $celda = $columna.'1';
            $hoja->setCellValue($celda, $encabezado);
            $hoja->getStyle($celda)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $hoja->getStyle($celda)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF047857');
            $hoja->getStyle($celda)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        $ejemplos = [
            ['1234567890', 'CC', 'JUAN PÉREZ GÓMEZ', 'EXTERNO', 'SURA'],
            ['9876543210', 'CC', 'MARÍA RODRÍGUEZ LÓPEZ', 'INTERNO', 'POSITIVA'],
            ['5554443330', 'CE', 'CARLOS DÍAZ MEJÍA', 'EXTERNO', 'COLMENA'],
        ];

        foreach ($ejemplos as $filaIndice => $fila) {
            $numeroFila = $filaIndice + 2;
            foreach ($fila as $colIndice => $valor) {
                $columna = chr(ord('A') + $colIndice);
                $hoja->setCellValue($columna.$numeroFila, $valor);
            }
        }

        $hoja->getColumnDimension('A')->setWidth(16);
        $hoja->getColumnDimension('B')->setWidth(18);
        $hoja->getColumnDimension('C')->setWidth(32);
        $hoja->getColumnDimension('D')->setWidth(18);
        $hoja->getColumnDimension('E')->setWidth(14);
        $hoja->freezePane('A2');

        $instrucciones = $spreadsheet->createSheet();
        $instrucciones->setTitle('Instrucciones');
        $texto = [
            ['Plantilla de importación — Control Contratista'],
            [''],
            ['Columnas (hoja Planilla):'],
            ['Documento', 'Obligatorio. Número sin puntos ni espacios.'],
            ['Tipo de Documento', 'Opcional. CC, CE, TI, PAS, NIT o PPT. Si vacío: CC.'],
            ['Nombre y Apellido', 'Obligatorio solo para contratistas nuevos.'],
            ['Interno / Externo', 'Opcional. EXTERNO o INTERNO. Si vacío: EXTERNO.'],
            ['ARL', 'Obligatorio para contratistas nuevos. Ej: SURA, POSITIVA, COLMENA.'],
            [''],
            ['Reglas al importar:'],
            ['• Cédula en Excel y en la empresa → se activa y marca el mes de la fecha límite en verde.'],
            ['• Cédula en la empresa pero NO en Excel → se inactiva y el mes queda en rojo.'],
            ['• Cédula nueva con nombre y ARL → se crea sin fecha de I/R (aparece en el dashboard como pendiente).'],
            ['• Cédula nueva sin nombre o ARL → no se crea (pendiente de completar datos).'],
            ['• La fecha de inducción/reinducción se registra después en el módulo de contratistas.'],
            ['• Borre las filas de ejemplo antes de importar su planilla real.'],
        ];

        foreach ($texto as $indice => $linea) {
            $instrucciones->fromArray($linea, null, 'A'.($indice + 1));
        }
        $instrucciones->getColumnDimension('A')->setWidth(22);
        $instrucciones->getColumnDimension('B')->setWidth(72);
        $instrucciones->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
