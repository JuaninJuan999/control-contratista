<?php

require __DIR__.'/../vendor/autoload.php';

use App\Services\PlanillaContratistas\GeneradorPlantillaExcel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dir = __DIR__.'/../storage/app/plantillas';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$ruta = $dir.'/plantilla-importacion-contratistas.xlsx';
(new Xlsx(GeneradorPlantillaExcel::crearLibro()))->save($ruta);

echo "Plantilla generada: {$ruta}\n";
