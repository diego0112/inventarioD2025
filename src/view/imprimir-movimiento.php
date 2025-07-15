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
        
        $contenido_pdf = '';
        $contenido_pdf .= '
            <!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de Bienes</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }
    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 2px solid #333;
      padding-bottom: 15px;
    }
    .header-title {
      font-size: 14px;
      font-weight: bold;
      color: #666;
      margin: 5px 0;
    }
    .header-subtitle {
      font-size: 12px;
      color: #666;
      margin: 3px 0;
    }
    .anexo {
      font-size: 16px;
      font-weight: bold;
      color: #333;
      margin-top: 15px;
      border-top: 1px solid #333;
      border-bottom: 1px solid #333;
      padding: 8px 0;
    }
    .logos {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .logo {
      width: 80px;
      height: 80px;
      border: 1px solid #ccc;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      text-align: center;
      color: #666;
    }
    h2 {
      text-align: center;
      font-weight: bold;
      margin-top: 30px;
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
    ORIGEN: <span class="label"></span>'. $respuesta->amb_origen->codigo.'-'.$respuesta->amb_origen->detalle.'<br>
    DESTINO: <span class="label"></span>'.$respuesta->amb_destino->codigo.'-'.$respuesta->amb_destino->detalle.'<br><br>
    <strong>MOTIVO (*): </strong><span class="label">'.$respuesta->movimiento->descripcion.'</span>
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
        ';
        
        $contador = 1;
        foreach ($respuesta->detalle as $bien) {
            $contenido_pdf.= "<tr>";
            $contenido_pdf.= "<td>".$contador . "</td>";
            $contenido_pdf.= "<td>".$bien->cod_patrimonial . "</td>";
            $contenido_pdf.= "<td>".$bien->denominacion . "</td>";
            $contenido_pdf.= "<td>".$bien->marca . "</td>";
            $contenido_pdf.= "<td>".$bien->color . "</td>";
            $contenido_pdf.= "<td>".$bien->modelo . "</td>";
            $contenido_pdf.= "<td>".$bien->estado_conservacion . "</td>";
            $contenido_pdf.= "</tr>";
            $contador++;
        }
        
        $contenido_pdf.='
    </tbody>
  </table>
  <div class="lugar-fecha">
    Ayacucho, '."$dia de $mes del $anio".'
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
';

        require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
        
        // Crear clase personalizada para el encabezado
        class MYPDF extends TCPDF {
            public function Header() {
              // URLs de las imágenes (reemplaza con tus URLs reales)
                $logo_izquierdo = 'https://oportunidadeslaborales.uladech.edu.pe/wp-content/uploads/2021/09/GOBIERNO-REGIONAL-DE-AYACUCHO.jpg';
                $logo_derecho = 'https://gra.regionayacucho.gob.pe/_next/image?url=%2Flogos%2Fdrea.png&w=640&q=75';
                // Configurar el encabezado personalizado
                $html = '
                <table style="width: 100%; border-bottom: 2px solid #333;">
                    <tr>
                        <td style="width: 15%; text-align: center; vertical-align: middle;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; 
                                        display: table-cell; vertical-align: middle; text-align: center; 
                                        font-size: 8px; color: #666;">
                                <img src="'.$logo_izquierdo.'" width="60" height="60" style="border-radius: 50%;">
                            </div>
                        </td>
                        <td style="width: 70%; text-align: center; vertical-align: middle;">
                            <div style="font-size: 10px; font-weight: bold; color: #666; margin: 1px 0;">
                                GOBIERNO REGIONAL DE AYACUCHO
                            </div>
                            <div style="font-size: 12px; font-weight: bold; color: #666; margin: 2px 0;">
                                DIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO
                            </div>
                            <div style="font-size: 8px; color: #666; margin: 2px 0;">
                                DIRECCIÓN DE ADMINISTRACIÓN
                            </div>
                        </td>
                        <td style="width: 15%; text-align: center; vertical-align: middle;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; 
                                        display: table-cell; vertical-align: middle; text-align: center; 
                                        font-size: 8px; color: #666;">
                                <img src="'.$logo_derecho.'" width="60" height="60" style="border-radius: 50%;">
                            </div>
                        </td>
                    </tr>
                </table>
                ';
                
                $this->writeHTML($html, true, false, true, false, '');
            }

            
        }
        
        // Usar la clase personalizada
        $pdf = new MYPDF();
        
        // Configurar información del documento
        $pdf->SetCreator('GOGO');
        $pdf->SetAuthor('Diego Yalico');
        $pdf->SetTitle('Reporte de Movimiento');
        
        // Configurar márgenes (aumentar margen superior para el encabezado)
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Configurar salto de página automático
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Configurar fuente predeterminada
        $pdf->SetFont('helvetica', 'B', 12);
        
        // Agregar página
        $pdf->AddPage();
        
        // Generar contenido HTML
        $pdf->writeHTML($contenido_pdf);
        
        // Limpiar buffer
        ob_clean();
        
        // Generar y mostrar PDF
        $pdf->Output('papeleta_rotacion.pdf', 'I');
    }
?>