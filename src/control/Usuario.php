<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../model/admin-sesionModel.php');
require_once('../model/admin-usuarioModel.php');
require_once('../model/adminModel.php');

require '../../vendor/autoload.php';
require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

$tipo = $_GET['tipo'];

// Instanciar la clase categoria model
$objSesion = new SessionModel();
$objUsuario = new UsuarioModel();
$objAdmin = new AdminModel();

// Verificar si las claves existen en $_POST antes de acceder a ellas
$id_sesion = isset($_POST['sesion']) ? $_POST['sesion'] : '';
$token = isset($_POST['token']) ? $_POST['token'] : '';

if ($tipo == "validar_datos_reset_password") {
    $id_email = $_POST['id'];
    $token_email = $_POST['token'];

    $arr_Respuesta = array('status' => false, 'msg' => 'Link Caducado');
    $datos_usuario = $objUsuario->buscarUsuarioById($id_email);
    if ($datos_usuario) {
        if ($datos_usuario->reset_password == 1 && password_verify($datos_usuario->token_password, $token_email)) {
            $arr_Respuesta = array('status' => true, 'msg' => 'Ok');
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "actualizar_password") {
    $id = $_POST['id'];
    $password = $_POST['password'];

    $arr_Respuesta = array('status' => false, 'msg' => 'Error al actualizar la contraseña');

    // Validar que los datos no estén vacíos
    if (!empty($id) && !empty($password)) {
        $datos_usuario = $objUsuario->buscarUsuarioById($id);

        if ($datos_usuario) {
            // Verificar que el usuario tenga permiso para resetear contraseña
            if ($datos_usuario->reset_password == 1) {
                // Encriptar la nueva contraseña
                $password_secure = password_hash($password, PASSWORD_DEFAULT);

                // Actualizar contraseña
                $respuesta_password = $objUsuario->actualizarPassword($id, $password_secure);

                if ($respuesta_password) {
                    // Limpiar token y reset_password
                    $respuesta_reset = $objUsuario->updateResetPassword($id, '', 0);

                    if ($respuesta_reset) {
                        $arr_Respuesta = array('status' => true, 'msg' => 'Contraseña actualizada correctamente');
                        session_destroy(); // Cerrar la sesión
                    } else {
                        $arr_Respuesta = array('status' => false, 'msg' => 'Error al limpiar token de reset');
                    }
                } else {
                    $arr_Respuesta = array('status' => false, 'msg' => 'Error al actualizar la contraseña en BD');
                }
            } else {
                $arr_Respuesta = array('status' => false, 'msg' => 'No tiene permisos para resetear contraseña');
            }
        } else {
            $arr_Respuesta = array('status' => false, 'msg' => 'Usuario no encontrado');
        }
    } else {
        $arr_Respuesta = array('status' => false, 'msg' => 'Datos incompletos');
    }

    echo json_encode($arr_Respuesta);
}




if ($tipo == "listar_usuarios_ordenados_tabla") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        $pagina = $_POST['pagina'];
        $cantidad_mostrar = $_POST['cantidad_mostrar'];
        $busqueda_tabla_dni = $_POST['busqueda_tabla_dni'];
        $busqueda_tabla_nomap = $_POST['busqueda_tabla_nomap'];
        $busqueda_tabla_estado = $_POST['busqueda_tabla_estado'];
        //repuesta
        $arr_Respuesta = array('status' => false, 'contenido' => '');
        $busqueda_filtro = $objUsuario->buscarUsuariosOrderByApellidosNombres_tabla_filtro($busqueda_tabla_dni, $busqueda_tabla_nomap, $busqueda_tabla_estado);
        $arr_Usuario = $objUsuario->buscarUsuariosOrderByApellidosNombres_tabla($pagina, $cantidad_mostrar, $busqueda_tabla_dni, $busqueda_tabla_nomap, $busqueda_tabla_estado);
        $arr_contenido = [];
        if (!empty($arr_Usuario)) {
            // recorremos el array para agregar las opciones de las categorias
            for ($i = 0; $i < count($arr_Usuario); $i++) {
                // definimos el elemento como objeto
                $arr_contenido[$i] = (object) [];
                // agregamos solo la informacion que se desea enviar a la vista
                $arr_contenido[$i]->id = $arr_Usuario[$i]->id;
                $arr_contenido[$i]->dni = $arr_Usuario[$i]->dni;
                $arr_contenido[$i]->nombres_apellidos = $arr_Usuario[$i]->nombres_apellidos;
                $arr_contenido[$i]->correo = $arr_Usuario[$i]->correo;
                $arr_contenido[$i]->telefono = $arr_Usuario[$i]->telefono;
                $arr_contenido[$i]->estado = $arr_Usuario[$i]->estado;
                $opciones = '<button type="button" title="Editar" class="btn btn-primary waves-effect waves-light" data-toggle="modal" data-target=".modal_editar' . $arr_Usuario[$i]->id . '"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-info" title="Resetear Contraseña" onclick="reset_password(' . $arr_Usuario[$i]->id . ')"><i class="fa fa-key"></i></button>';
                $arr_contenido[$i]->options = $opciones;
            }
            $arr_Respuesta['total'] = count($busqueda_filtro);
            $arr_Respuesta['status'] = true;
            $arr_Respuesta['contenido'] = $arr_contenido;
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "registrar") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        //repuesta
        if ($_POST) {
            $dni = $_POST['dni'];
            $apellidos_nombres = $_POST['apellidos_nombres'];
            $correo = $_POST['correo'];
            $telefono = $_POST['telefono'];
            $password = $_POST['password'];

            if ($dni == "" || $apellidos_nombres == "" || $correo == "" || $telefono == "" || $password == "") {
                //repuesta
                $arr_Respuesta = array('status' => false, 'mensaje' => 'Error, campos vacíos');
            } else {
                $arr_Usuario = $objUsuario->buscarUsuarioByDni($dni);
                if ($arr_Usuario) {
                    $arr_Respuesta = array('status' => false, 'mensaje' => 'Registro Fallido, Usuario ya se encuentra registrado');
                } else {
                    $id_usuario = $objUsuario->registrarUsuario($dni, $apellidos_nombres, $correo, $telefono, $password);
                    if ($id_usuario > 0) {
                        // array con los id de los sistemas al que tendra el acceso con su rol registrado
                        // caso de administrador y director
                        $arr_Respuesta = array('status' => true, 'mensaje' => 'Registro Exitoso');
                    } else {
                        $arr_Respuesta = array('status' => false, 'mensaje' => 'Error al registrar producto');
                    }
                }
            }
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "actualizar") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        //repuesta
        if ($_POST) {
            $id = $_POST['data'];
            $dni = $_POST['dni'];
            $nombres_apellidos = $_POST['nombres_apellidos'];
            $correo = $_POST['correo'];
            $telefono = $_POST['telefono'];
            $estado = $_POST['estado'];

            if ($id == "" || $dni == "" || $nombres_apellidos == "" || $correo == "" || $telefono == "" || $estado == "") {
                //repuesta
                $arr_Respuesta = array('status' => false, 'mensaje' => 'Error, campos vacíos');
            } else {
                $arr_Usuario = $objUsuario->buscarUsuarioByDni($dni);
                if ($arr_Usuario) {
                    if ($arr_Usuario->id == $id) {
                        $consulta = $objUsuario->actualizarUsuario($id, $dni, $nombres_apellidos, $correo, $telefono, $estado);
                        if ($consulta) {
                            $arr_Respuesta = array('status' => true, 'mensaje' => 'Actualizado Correctamente');
                        } else {
                            $arr_Respuesta = array('status' => false, 'mensaje' => 'Error al actualizar registro');
                        }
                    } else {
                        $arr_Respuesta = array('status' => false, 'mensaje' => 'dni ya esta registrado');
                    }
                } else {
                    $consulta = $objUsuario->actualizarUsuario($id, $dni, $nombres_apellidos, $correo, $telefono, $estado);
                    if ($consulta) {
                        $arr_Respuesta = array('status' => true, 'mensaje' => 'Actualizado Correctamente');
                    } else {
                        $arr_Respuesta = array('status' => false, 'mensaje' => 'Error al actualizar registro');
                    }
                }
            }
        }
    }
    echo json_encode($arr_Respuesta);
}
if ($tipo == "reiniciar_password") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        //print_r($_POST);
        $id_usuario = $_POST['id'];
        $password = $objAdmin->generar_llave(10);
        $pass_secure = password_hash($password, PASSWORD_DEFAULT);
        $actualizar = $objUsuario->actualizarPassword($id_usuario, $pass_secure);
        if ($actualizar) {
            $arr_Respuesta = array('status' => true, 'mensaje' => 'Contraseña actualizado correctamente a: ' . $password);
        } else {
            $arr_Respuesta = array('status' => false, 'mensaje' => 'Hubo un problema al actualizar la contraseña, intente nuevamente');
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "send_email_password") {
    $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
       
$datos_sesion = $objSesion->buscarSesionLoginById($id_sesion);
$datos_usuario = $objUsuario->buscarUsuarioById($datos_sesion->id_usuario);
$llave = $objAdmin->generar_llave(30);
$token = password_hash($llave, PASSWORD_DEFAULT);
$update = $objUsuario->updateResetPassword($datos_sesion->id_usuario, $llave, 1)  ;
if ($update) {
    
//Load Composer's autoloader (created by composer, not included with PHPMailer)

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'mail.importecsolutions.com';        // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                    // Enable SMTP authentication
    $mail->Username   = 'inventario_diego@importecsolutions.com';  // SMTP username
    $mail->Password   = 'inventariopass';        // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients

    $mail->setFrom('inventario_diego@importecsolutions.com', 'Sistema Inventario Diego');
    $mail->addAddress($datos_usuario->correo, $datos_usuario->nombres_apellidos);     //Add a recipient


    //Content
    $mail->isHTML(true);     
    $mail->CharSet = 'UTF-8';                  
    $mail->Subject = 'Cambio de Contraseña - Sistema de inventario'; //Set email format to HTML
    $mail->Body = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Contraseña - Sistema Inventario Diego</title>
</head>
<body style="margin: 0; padding: 0; background: #f5f7fa; width: 100%; min-width: 100%; line-height: 1.6; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
    <div style="background: #f5f7fa; padding: 40px 20px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
            <tr>
                <td>
                    <div style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">

                        <!-- Header -->
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 50%, #a93226 100%); position: relative;">
                            <tr>
                                <td>
                                    <div style="position: relative; z-index: 2; padding: 50px 40px; text-align: center;">
                                        <div style="margin-bottom: 30px;">
                                            <div style="display: inline-block; background: rgba(255,255,255,0.15); padding: 20px 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2);">
                                                <span style="display: inline-block; width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; text-align: center; line-height: 60px; font-size: 28px; margin-right: 20px; vertical-align: middle; border: 2px solid rgba(255,255,255,0.3);">&#128274;</span>
                                                <span style="display: inline-block; font-size: 32px; font-weight: 900; color: #ffffff; letter-spacing: 3px; vertical-align: middle; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">SUPERTEC SYSTEMS</span>
                                            </div>
                                        </div>
                                        <h1 style="color: #ffffff; font-size: 28px; font-weight: 700; text-align: center; margin: 20px 0 8px 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Restablecer Contraseña</h1>
                                        <p style="color: rgba(255,255,255,0.9); font-size: 16px; text-align: center; margin: 0; font-weight: 400; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Solicitud de cambio de contraseña segura</p>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- Content -->
                        <div style="padding: 50px 40px;">
                            <div style="font-size: 22px; color: #2c3e50; margin-bottom: 25px; font-weight: 600; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
                                ¡Hola, ' . $datos_usuario->nombres_apellidos . '! &#128075;
                            </div>

                            <div style="color: #5a6c7d; font-size: 16px; line-height: 1.7; margin-bottom: 30px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
                                Hemos recibido una solicitud para <strong style="color: #2c3e50; font-weight: 600;">restablecer la contraseña</strong> de tu cuenta en el Sistema de Inventario Diego. Para garantizar la seguridad de tu información, hemos generado un enlace temporal y seguro.
                            </div>

                            <div style="text-align: center; margin: 40px 0; padding: 20px; background: #f8f9fa; border-radius: 16px; border: 1px solid #dee2e6;">
                                <div style="color: #6c757d; font-size: 14px; margin-bottom: 15px; font-weight: 500; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Haz clic en el botón para continuar</div>
                                <a href="'.BASE_URL.'reset-password/?data='.$datos_usuario->id.'&data2='.urlencode($token).'" style="display: inline-block; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: #ffffff; text-decoration: none; padding: 18px 45px; border-radius: 50px; font-size: 17px; font-weight: 700; border: none; box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3); text-transform: uppercase; letter-spacing: 1px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Cambiar Contraseña</a>
                            </div>

                            <div style="display: table; width: 100%; margin: 35px 0; background: #f8f9fa; border-radius: 16px; padding: 25px; border: 1px solid #e9ecef;">
                                <div style="display: table-cell; text-align: center; width: 33.33%; vertical-align: top;">
                                    <span style="font-size: 36px; font-weight: 900; color: #e74c3c; margin-bottom: 5px; display: block; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">24</span>
                                    <span style="color: #6c757d; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Horas válido</span>
                                </div>
                                <div style="display: table-cell; text-align: center; width: 33.33%; vertical-align: top;">
                                    <span style="font-size: 36px; font-weight: 900; color: #e74c3c; margin-bottom: 5px; display: block; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">1</span>
                                    <span style="color: #6c757d; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Solo uso</span>
                                </div>
                                <div style="display: table-cell; text-align: center; width: 33.33%; vertical-align: top;">
                                    <span style="font-size: 36px; font-weight: 900; color: #e74c3c; margin-bottom: 5px; display: block; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">256</span>
                                    <span style="color: #6c757d; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Bits encriptación</span>
                                </div>
                            </div>

                            <div style="background: #fff3cd; border-left: 5px solid #f39c12; padding: 25px; margin: 30px 0; border-radius: 12px; position: relative; box-shadow: 0 4px 15px rgba(243, 156, 18, 0.1);">
                                <div style="display: table; width: 100%; margin-bottom: 12px;">
                                    <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                        <div style="color: #e67e22; font-size: 22px; font-weight: bold; width: 30px; height: 30px; background: rgba(230, 126, 34, 0.1); border-radius: 50%; display: table-cell; text-align: center; vertical-align: middle;">&#9888;</div>
                                    </div>
                                    <div style="display: table-cell; vertical-align: middle;">
                                        <h3 style="color: #d68910; font-size: 17px; font-weight: 700; margin: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Aviso Importante de Seguridad</h3>
                                    </div>
                                </div>
                                <p style="color: #b7950b; font-size: 15px; line-height: 1.6; margin: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
                                    Si <strong>NO solicitaste</strong> este cambio de contraseña, puedes ignorar este email de forma segura. Tu contraseña actual permanecerá activa y segura. Te recomendamos revisar la actividad reciente de tu cuenta.
                                </p>
                            </div>

                            <div style="display: table; width: 100%; margin: 30px 0;">
                                <div style="display: table-cell; width: 48%; vertical-align: top; padding-right: 2%;">
                                    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; border: 1px solid #e9ecef; text-align: center;">
                                        <span style="font-size: 24px; margin-bottom: 10px; display: block;">&#128337;</span>
                                        <div style="color: #495057; font-size: 14px; font-weight: 600; margin-bottom: 5px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Tiempo límite</div>
                                        <div style="color: #e74c3c; font-size: 18px; font-weight: 700; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">24:00 hrs</div>
                                    </div>
                                </div>
                                <div style="display: table-cell; width: 48%; vertical-align: top; padding-left: 2%;">
                                    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; border: 1px solid #e9ecef; text-align: center;">
                                        <span style="font-size: 24px; margin-bottom: 10px; display: block;">&#128510;</span>
                                        <div style="color: #495057; font-size: 14px; font-weight: 600; margin-bottom: 5px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Nivel seguridad</div>
                                        <div style="color: #e74c3c; font-size: 18px; font-weight: 700; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">Máximo</div>
                                    </div>
                                </div>
                            </div>

                            <div style="height: 2px; background: #e9ecef; margin: 40px 0; border-radius: 2px;"></div>

                            <div style="color: #5a6c7d; font-size: 16px; line-height: 1.7; margin-bottom: 30px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
                                <strong style="color: #2c3e50; font-weight: 600;">¿Necesitas ayuda?</strong> Nuestro equipo de soporte está disponible para asistirte con cualquier consulta sobre tu cuenta o este proceso de restablecimiento.
                            </div>

                            <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin: 25px 0; border: 1px solid #e9ecef;">
                                <div style="color: #495057; font-size: 16px; font-weight: 600; margin-bottom: 10px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">&#128279; Enlace alternativo (copia y pega en tu navegador):</div>
                                <div style="color: #e74c3c; word-break: break-all; font-family: \'Courier New\', monospace; background: #ffffff; padding: 12px; border-radius: 8px; border: 1px solid #dee2e6; font-size: 14px;">' . $reset_url . '</div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: #ecf0f1;">
                            <tr>
                                <td>
                                    <div style="padding: 40px; text-align: center;">
                                        <p style="color: #bdc3c7; font-size: 14px; margin-bottom: 15px; line-height: 1.6; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
                                            Este email fue enviado desde un sistema automatizado seguro.<br>
                                            Para tu protección, no respondas a este mensaje.
                                        </p>
                                        <p style="color: #bdc3c7; font-size: 14px; margin-bottom: 15px; line-height: 1.6; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
                                            ¿Necesitas ayuda? Contacta a nuestro equipo de soporte
                                        </p>

                                        <p style="color: #95a5a6; font-size: 12px; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Arial, Helvetica, sans-serif;">
                                            &copy; 2025 <strong>Sistema Inventario Diego</strong><br>
                                            Todos los derechos reservados | Política de Privacidad
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
    

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
}else {
    echo "fallo al actualizar";
}
//print_r($token);

    }

}


