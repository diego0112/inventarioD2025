<?php
session_start();
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

// CONSULTA A LA API
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Movimiento.php?tipo=listar_todos_movimientos&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'] . "&ies=1",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => "GET"
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "Error: $err";
    exit;
}
$data = json_decode($response);
if (!$data->status) {
    echo "No se encontraron movimientos.";
    exit;
}

// FECHA ACTUAL
$meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
$fecha = new DateTime();
$dia = $fecha->format('d');
$mes = $meses[(int)$fecha->format('m')];
$anio = $fecha->format('Y');

// TCPDF PERSONALIZADO
class MYPDF extends TCPDF {
    public function Header() {
        $logo_izq = 'https://oportunidadeslaborales.uladech.edu.pe/wp-content/uploads/2021/09/GOBIERNO-REGIONAL-DE-AYACUCHO.jpg';
        $logo_der = 'https://gra.regionayacucho.gob.pe/_next/image?url=%2Flogos%2Fdrea.png&w=640&q=75';
        $html = '
        <table style="width:100%;border-bottom:2px solid #333;">
            <tr>
                <td width="15%" align="center"><img src="' . $logo_izq . '" width="60"/></td>
                <td width="70%" align="center">
                    <div style="font-size:10px;"><strong>GOBIERNO REGIONAL DE AYACUCHO</strong></div>
                    <div style="font-size:12px;"><strong>DIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO</strong></div>
                    <div style="font-size:8px;">DIRECCIÓN DE ADMINISTRACIÓN</div>
                </td>
                <td width="15%" align="center"><img src="' . $logo_der . '" width="60"/></td>
            </tr>
        </table>';
        $this->writeHTML($html, true, false, true, false, '');
    }
}

$pdf = new MYPDF();
$pdf->SetMargins(10, 40, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

// TÍTULO Y FECHA
$html = "<h2 style='text-align:center;'>REPORTE GENERAL DE MOVIMIENTOS DE BIENES</h2>";
$html .= "<p style='text-align:right;'>Ayacucho, $dia de $mes del $anio</p>";

// TABLA PROFESIONAL
$html .= '
<style>
th {
    background-color: #f2f2f2;
    font-weight: bold;
    border: 1px solid #000;
    text-align: center;
    vertical-align: middle;
    font-size: 8px;
}
td {
    border: 1px solid #000;
    font-size: 8px;
    padding: 2px;
    vertical-align: middle;
    text-align: center;
}
</style>

<table cellspacing="0" cellpadding="3">
    <thead>
        <tr>
            <th width="3%">#</th>
            <th width="11%">Fecha</th>
            <th width="13%">Origen</th>
            <th width="13%">Destino</th>
            <th width="11%">Responsable</th>
            <th width="14%">Descripción</th>
            <th width="10%">Cod. Patrimonial</th>
            <th width="15%">Bien</th>
            <th width="5%">Marca</th>
            <th width="5%">Estado</th>
        </tr>
    </thead>
    <tbody>';

// LLENADO DE FILAS
$contador = 1;
foreach ($data->data as $mov) {
    foreach ($mov->detalle as $bien) {
        $html .= '<tr>';
        $html .= '<td>' . $contador . '</td>';
        $html .= '<td>' . $mov->movimiento->fecha_registro . '</td>';
        $html .= '<td>' . $mov->origen->codigo . ' - ' . $mov->origen->detalle . '</td>';
        $html .= '<td>' . $mov->destino->codigo . ' - ' . $mov->destino->detalle . '</td>';
        $html .= '<td>' . $mov->usuario->nombres_apellidos . '</td>';
        $html .= '<td>' . $mov->movimiento->descripcion . '</td>';
        $html .= '<td>' . $bien->cod_patrimonial . '</td>';
        $html .= '<td>' . $bien->denominacion . '</td>';
        $html .= '<td>' . $bien->marca . '</td>';
        $html .= '<td>' . $bien->estado_conservacion . '</td>';
        $html .= '</tr>';
        $contador++;
    }
}

$html .= '
    </tbody>
</table>';

// ESCRIBIR HTML EN EL PDF
$pdf->writeHTML($html, true, false, true, false, '');
ob_clean();

$pdf->Output("imprimir-movimientos-todos.pdf", "I");
