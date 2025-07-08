<?php
$ruta = explode("/", $_GET['views']);
if (!isset($ruta[1])|| $ruta[1]=="") {
    header("location: ".BASE_URL."movimientos");
}

$curl = curl_init(); //inicia la sesión cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Movimiento.php?tipo=buscar_movimiento_id&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token']."&data=".$ruta[1], //url a la que se conecta
        CURLOPT_RETURNTRANSFER => true, //devuelve el resultado como una cadena del tipo curl_exec
        CURLOPT_FOLLOWLOCATION => true, //sigue el encabezado que le envíe el servidor
        CURLOPT_ENCODING => "", // permite decodificar la respuesta y puede ser"identity", "deflate", y "gzip", si está vacío recibe todos los disponibles.
        CURLOPT_MAXREDIRS => 10, // Si usamos CURLOPT_FOLLOWLOCATION le dice el máximo de encabezados a seguir
        CURLOPT_TIMEOUT => 30, // Tiempo máximo para ejecutar
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // usa la versión declarada
        CURLOPT_CUSTOMREQUEST => "GET", // el tipo de petición, puede ser PUT, POST, GET o Delete dependiendo del servicio
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), //configura las cabeceras enviadas al servicio
    )); //curl_setopt_array configura las opciones para una transferencia cURL

    $response = curl_exec($curl); // respuesta generada
    $err = curl_error($curl); // muestra errores en caso de existir

    curl_close($curl); // termina la sesión 

    if ($err) {
        echo "cURL Error #:" . $err; // mostramos el error
    } else {
        $respuesta = json_decode($response);
        //print_r($respuesta);
        
        ?>
<!--
        <!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de Bienes</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
    }
    h2 {
      text-align: center;
      font-weight: bold;
    }
    .info {
      margin-top: 30px;
      line-height: 2;
    }
    .underline {
      display: inline-block;
      border-bottom: 1px solid black;
      min-width: 150px;
      height: 1em;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table, th, td {
      border: 1px solid black;
    }
    th, td {
      padding: 6px;
      text-align: center;
    }
    .firma {
      margin-top: 60px;
      display: flex;
      justify-content: space-between;
      padding: 0 60px;
    }
    .firma div {
      text-align: center;
    }
    .lugar-fecha {
      text-align: right;
      margin-top: 30px;
    }
  </style>
</head>
<body>

  <h2>PAPELETA DE ROTACIÓN DE BIENES</h2>

  <div class="info">
    ENTIDAD: <span class="label"></span> DIRECCION REGIONAL DE EDUCACION - AYACUCHO <br>
    AREA: <span class="label"></span> OFICINA DE ADMINISTRACIÓN <br>
    ORIGEN: <span class="label"></span><?php echo $respuesta->amb_origen->codigo.'-'.$respuesta->amb_origen->detalle;?><br>
    DESTINO: <span class="label"></span><?php echo $respuesta->amb_destino->codigo.'-'.$respuesta->amb_destino->detalle;?><br><br>
    <strong>MOTIVO (*): </strong><span class="label"><?php echo $respuesta->movimiento->descripcion;?></span>
  </div>

  <table>
    
    <thead>
      <tr>
        <th>ITEM</th>
        <th>CODIGO PATRIMONIAL</th>
        <th>NOMBRE DEL BIEN</th>
        <th>MARCA</th>
        <th>COLOR</th>
        <th>MODELO</th>
        <th>ESTADO</th>
      </tr>
    </thead>
    <tbody>
    <?php 
    $contador = 1;
    foreach ($respuesta->detalle as $bien) {
        echo "<tr>";
        echo "<td>".$contador . "</td>";
        echo "<td>".$bien->cod_patrimonial . "</td>";
        echo "<td>".$bien->denominacion . "</td>";
        echo "<td>".$bien->marca . "</td>";
        echo "<td>".$bien->color . "</td>";
            echo "<td>".$bien->modelo . "</td>";
            echo "<td>".$bien->estado_conservacion . "</td>";
        echo "</tr>";
                $contador++;
    }
    ?>

    </tbody>
  </table>

    <?php
    
    // Definir meses en español manualmente
    $meses = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];

    // Crear objeto DateTime con la fecha actual
    $fecha = new DateTime();

    // Obtener componentes
    $dia = $fecha->format('d');
    $mes = $meses[(int)$fecha->format('m')];
    $anio = $fecha->format('Y');
    ?>

  <div class="lugar-fecha">
    Ayacucho, <?php echo "$dia de $mes del $anio"; ?>
  </div>

  <div class="firma">
    <div>
      ------------------------------<br>
      ENTREGUE CONFORME
    </div>
    <div>
      ------------------------------<br>
      RECIBI CONFORME
    </div>
  </div>

</body>
</html>
-->

        <?php
        require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

        $pdf = new TCPDF(); //SI DESEAS CAMBIAR ALGO DEBES DE CAMABIAR O INSERTAR EL DATO DESDE EL PRIMERO HASTA EL QUE  DESEAS CAMBIAR

        // set document information
$pdf->SetCreator(GOGO);
$pdf->SetAuthor('Diego Yalico');
$pdf->SetTitle('Reporte de Movimiento');

// ASIGNAR LOS MARGENES PARA LA PAGINA
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// ASIGNAR SALTO DE PAGINA AUTOMÁTICO
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    }