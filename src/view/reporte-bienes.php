<?php

require './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()->setCreator("yo")->setLastModifiedBy("yo")->setTitle("yo")->setDescription("yo");
$activeWorksheet = $spreadsheet->getActiveSheet();


$row = 1;
for ($tabla = 1; $tabla <= 12; $tabla++) {
    for ($multiplicador = 1; $multiplicador <= 12; $multiplicador++) {
        $resultado = $tabla * $multiplicador;
        
        $activeWorksheet->setCellValue('A' . $row, $tabla);
        
        $activeWorksheet->setCellValue('B' . $row, '*');
        
        $activeWorksheet->setCellValue('C' . $row, $multiplicador);
        
        $activeWorksheet->setCellValue('D' . $row, '=');
        
        $activeWorksheet->setCellValue('E' . $row, $resultado);
        
        $row++;
    }
}

$writer = new Xlsx($spreadsheet);
$writer->save('tabla.xlsx');

echo "Archivo Excel generado exitosamente con la tabla de multiplicar del 1 al 9\n";

?>