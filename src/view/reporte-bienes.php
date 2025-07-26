<?php
// =================== INICIA cURL ===================
$curl = curl_init();

// Preparar los datos POST para la API
$postData = array(
    'sesion' => $_SESSION['sesion_id'],
    'token' => $_SESSION['sesion_token'],
    'ies' => $_SESSION['ies'] ?? 1, // ID de la institución desde la sesión
    'pagina' => 1,
    'cantidad_mostrar' => 10000, // Gran cantidad para obtener todos los registros
    'busqueda_tabla_codigo' => '',
    'busqueda_tabla_ambiente' => '',
    'busqueda_tabla_denominacion' => ''
);

curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Bien.php?tipo=listar_bienes_ordenados_tabla",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HTTPHEADER => array(
        "Content-Type: application/x-www-form-urlencoded",
        "x-rapidapi-host: " . BASE_URL_SERVER,
        "x-rapidapi-key: XXXX"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
// =================== FIN cURL ===================

if ($err) {
    echo "cURL Error #:" . $err;
    exit;
}

// Decodificar la respuesta JSON
$responseData = json_decode($response, true);

if (!$responseData || !$responseData['status']) {
    echo "Error: No se pudieron obtener los datos de bienes.";
    exit;
}

require './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Crear un nuevo documento
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("Sistema de Gestión de Bienes")
    ->setLastModifiedBy("Sistema de Gestión de Bienes")
    ->setTitle("Reporte de Bienes")
    ->setDescription("Listado completo de bienes registrados en el sistema");

$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setTitle('Reporte de Bienes');

// Definir los encabezados de las columnas
$headers = [
    'A' => 'ID',
    'B' => 'Código Patrimonial',
    'C' => 'Denominación',
    'D' => 'Marca',
    'E' => 'Modelo',
    'F' => 'Tipo',
    'G' => 'Color',
    'H' => 'Serie',
    'I' => 'Dimensiones',
    'J' => 'Valor',
    'K' => 'Situación',
    'L' => 'Estado de Conservación',
    'M' => 'Observaciones',
    'N' => 'ID Ambiente'
];

// Configurar encabezados con mejor formato
$fila = 1;
foreach ($headers as $columna => $titulo) {
    $activeWorksheet->setCellValue($columna . $fila, $titulo);
    
    // Aplicar estilo a los encabezados
    $activeWorksheet->getStyle($columna . $fila)->getFont()
        ->setBold(true)
        ->setSize(12)
        ->setName('Arial');
    
    $activeWorksheet->getStyle($columna . $fila)->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);
    
    $activeWorksheet->getStyle($columna . $fila)->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_MEDIUM);
    
    // Color de fondo para encabezados
    $activeWorksheet->getStyle($columna . $fila)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E8E8E8');
}

// Llenar los datos de los bienes con mejor formato
$bienes = $responseData['contenido'] ?? [];
$fila = 2; // Comenzar desde la fila 2 (después de los encabezados)

foreach ($bienes as $bien) {
    $activeWorksheet->setCellValue('A' . $fila, $bien['id'] ?? '');
    $activeWorksheet->setCellValue('B' . $fila, $bien['cod_patrimonial'] ?? '');
    $activeWorksheet->setCellValue('C' . $fila, $bien['denominacion'] ?? '');
    $activeWorksheet->setCellValue('D' . $fila, $bien['marca'] ?? '');
    $activeWorksheet->setCellValue('E' . $fila, $bien['modelo'] ?? '');
    $activeWorksheet->setCellValue('F' . $fila, $bien['tipo'] ?? '');
    $activeWorksheet->setCellValue('G' . $fila, $bien['color'] ?? '');
    $activeWorksheet->setCellValue('H' . $fila, $bien['serie'] ?? '');
    $activeWorksheet->setCellValue('I' . $fila, $bien['dimensiones'] ?? '');
    
    // Formatear valor como número
    $valor = floatval($bien['valor'] ?? 0);
    $activeWorksheet->setCellValue('J' . $fila, $valor);
    $activeWorksheet->getStyle('J' . $fila)->getNumberFormat()
        ->setFormatCode('#,##0.00');
    
    $activeWorksheet->setCellValue('K' . $fila, $bien['situacion'] ?? '');
    $activeWorksheet->setCellValue('L' . $fila, $bien['estado_conservacion'] ?? '');
    $activeWorksheet->setCellValue('M' . $fila, $bien['observaciones'] ?? '');
    $activeWorksheet->setCellValue('N' . $fila, $bien['id_ambiente'] ?? '');
    
    // Aplicar formato a las celdas de datos
    foreach ($headers as $columna => $titulo) {
        $activeWorksheet->getStyle($columna . $fila)->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        
        $activeWorksheet->getStyle($columna . $fila)->getFont()
            ->setName('Arial')
            ->setSize(10);
        
        // Alineación específica por columna
        if ($columna == 'A' || $columna == 'J' || $columna == 'N') {
            $activeWorksheet->getStyle($columna . $fila)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            $activeWorksheet->getStyle($columna . $fila)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
        
        $activeWorksheet->getStyle($columna . $fila)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
    }
    
    $fila++;
}

// Ajustar el ancho de las columnas de forma específica
$activeWorksheet->getColumnDimension('A')->setWidth(8);   // ID
$activeWorksheet->getColumnDimension('B')->setWidth(20);  // Código Patrimonial
$activeWorksheet->getColumnDimension('C')->setWidth(35);  // Denominación
$activeWorksheet->getColumnDimension('D')->setWidth(15);  // Marca
$activeWorksheet->getColumnDimension('E')->setWidth(15);  // Modelo
$activeWorksheet->getColumnDimension('F')->setWidth(12);  // Tipo
$activeWorksheet->getColumnDimension('G')->setWidth(10);  // Color
$activeWorksheet->getColumnDimension('H')->setWidth(15);  // Serie
$activeWorksheet->getColumnDimension('I')->setWidth(15);  // Dimensiones
$activeWorksheet->getColumnDimension('J')->setWidth(12);  // Valor
$activeWorksheet->getColumnDimension('K')->setWidth(12);  // Situación
$activeWorksheet->getColumnDimension('L')->setWidth(18);  // Estado Conservación
$activeWorksheet->getColumnDimension('M')->setWidth(30);  // Observaciones
$activeWorksheet->getColumnDimension('N')->setWidth(12);  // ID Ambiente

// Configurar altura de filas
$activeWorksheet->getDefaultRowDimension()->setRowHeight(20);
$activeWorksheet->getRowDimension(1)->setRowHeight(25); // Fila de encabezados más alta

// Agregar información adicional
$filaInfo = $fila + 2;
$activeWorksheet->setCellValue('A' . $filaInfo, 'Total de bienes registrados:');
$activeWorksheet->setCellValue('B' . $filaInfo, count($bienes));
$activeWorksheet->getStyle('A' . $filaInfo)->getFont()->setBold(true);
$activeWorksheet->getStyle('B' . $filaInfo)->getFont()->setBold(true);

$filaInfo++;
$activeWorksheet->setCellValue('A' . $filaInfo, 'Fecha de generación:');
$activeWorksheet->setCellValue('B' . $filaInfo, date('d/m/Y H:i:s'));
$activeWorksheet->getStyle('A' . $filaInfo)->getFont()->setBold(true);

// Configurar headers para descarga directa
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_bienes.xlsx"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');

// Guardar directamente en la salida (descarga)
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>