<?php
// Evitar múltiples session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

// CONSULTA A LA API
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Usuario.php?tipo=listar_todos_usuarios&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => "GET"
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "Error cURL: $err";
    exit;
}

// Limpiar la respuesta de warnings y notices de PHP
$json_start = strpos($response, '{');
if ($json_start !== false) {
    $clean_response = substr($response, $json_start);
} else {
    echo "No se encontró JSON válido en la respuesta";
    exit;
}

$data = json_decode($clean_response);

// Verificar si la decodificación JSON fue exitosa
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error al decodificar JSON: " . json_last_error_msg();
    exit;
}

if (!$data || !isset($data->status) || !$data->status) {
    echo "No se encontraron usuarios o error en la respuesta.";
    if ($data && isset($data->msg)) {
        echo " Mensaje: " . $data->msg;
    }
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
$pdf->AddPage('P'); // Orientación vertical para usuarios

// TÍTULO Y FECHA
$html = "<h2 style='text-align:center;'>LISTADO DE USUARIOS DEL SISTEMA</h2>";
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
    padding: 3px;
}
td {
    border: 1px solid #000;
    font-size: 8px;
    padding: 3px;
    vertical-align: middle;
    text-align: center;
}
.left-align {
    text-align: left;
}
.center-align {
    text-align: center;
}
</style>

<table cellspacing="0" cellpadding="3">
    <thead>
        <tr>
            <th width="8%">#</th>
            <th width="15%">DNI</th>
            <th width="35%">Nombres y Apellidos</th>
            <th width="27%">Correo Electrónico</th>
            <th width="15%">Estado</th>
        </tr>
    </thead>
    <tbody>';

// LLENADO DE FILAS
$contador = 1;
foreach ($data->data as $usuario) {
    // Determinar el estado en texto
    $estado_texto = '';
    $estado_color = '';
    if ($usuario->estado == '1') {
        $estado_texto = 'ACTIVO';
        $estado_color = 'color: green; font-weight: bold;';
    } elseif ($usuario->estado == '0') {
        $estado_texto = 'INACTIVO';
        $estado_color = 'color: red; font-weight: bold;';
    } else {
        $estado_texto = 'N/D';
        $estado_color = 'color: gray;';
    }

    $html .= '<tr>';
    $html .= '<td width="8%">' . $contador . '</td>';
    $html .= '<td width="15%">' . htmlspecialchars($usuario->dni ?: 'S/DNI') . '</td>';
    $html .= '<td width="35%" class="left-align">' . htmlspecialchars($usuario->nombres_apellidos ?: 'Sin nombre') . '</td>';
    $html .= '<td width="27%" class="left-align">' . htmlspecialchars($usuario->correo ?: 'Sin correo') . '</td>';
    $html .= '<td width="15%" style="' . $estado_color . '">' . $estado_texto . '</td>';
    $html .= '</tr>';
    $contador++;
}

$html .= '
    </tbody>
</table>';

// AGREGAR RESUMEN
$total_usuarios = count($data->data);
$usuarios_activos = 0;
$usuarios_inactivos = 0;

foreach ($data->data as $usuario) {
    if ($usuario->estado == '1') {
        $usuarios_activos++;
    } elseif ($usuario->estado == '0') {
        $usuarios_inactivos++;
    }
}

$html .= "<br><br>";
$html .= "<table style='width:100%;'>";
$html .= "<tr>";
$html .= "<td style='text-align:right; padding: 5px;'><strong>Total de Usuarios: $total_usuarios</strong></td>";
$html .= "</tr>";
$html .= "<tr>";
$html .= "<td style='text-align:right; padding: 2px; color: green;'><strong>Usuarios Activos: $usuarios_activos</strong></td>";
$html .= "</tr>";
$html .= "<tr>";
$html .= "<td style='text-align:right; padding: 2px; color: red;'><strong>Usuarios Inactivos: $usuarios_inactivos</strong></td>";
$html .= "</tr>";
$html .= "</table>";

// ESCRIBIR HTML EN EL PDF
$pdf->writeHTML($html, true, false, true, false, '');
ob_clean();

$pdf->Output("listado-usuarios-sistema.pdf", "I");
?>