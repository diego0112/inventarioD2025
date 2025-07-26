<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once('../model/admin-sesionModel.php');
require_once('../model/admin-institucionModel.php');
require_once('../model/admin-usuarioModel.php');
require_once('../model/adminModel.php');

$tipo = $_GET['tipo'] ?? '';

// Instanciar clases
$objSesion = new SessionModel();
$objInstitucion = new InstitucionModel();
$objUsuario = new UsuarioModel();

// Obtener variables de sesión (GET o POST según el caso)
$id_sesion = $_POST['sesion'] ?? $_GET['sesion'] ?? '';
$token = $_POST['token'] ?? $_GET['token'] ?? '';

if ($tipo == "listar") {
    $arr_Respuesta = ['status' => false, 'msg' => 'Error_Sesion'];
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        $arr_Respuesta = ['status' => false, 'contenido' => ''];
        $arr_Institucion = $objInstitucion->buscarInstitucionOrdenado();
        $arr_contenido = [];
        foreach ($arr_Institucion as $institucion) {
            $item = (object)[
                'id' => $institucion->id,
                'nombre' => $institucion->nombre
            ];
            $arr_contenido[] = $item;
        }
        if (!empty($arr_contenido)) {
            $arr_Respuesta['status'] = true;
            $arr_Respuesta['contenido'] = $arr_contenido;
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "listar_instituciones") {
    $arr_Respuesta = ['status' => false, 'msg' => 'Error_Sesion'];
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        $pagina = $_POST['pagina'];
        $cantidad_mostrar = $_POST['cantidad_mostrar'];
        $codigo = $_POST['busqueda_tabla_codigo'];
        $ruc = $_POST['busqueda_tabla_ruc'];
        $insti = $_POST['busqueda_tabla_insti'];

        $arr_Respuesta = ['status' => false, 'contenido' => ''];
        $busqueda_filtro = $objInstitucion->buscarInstitucionOrderByApellidosNombres_tabla_filtro($codigo, $ruc, $insti);
        $arr_Institucion = $objInstitucion->buscarInstitucionOrderByApellidosNombres_tabla($pagina, $cantidad_mostrar, $codigo, $ruc, $insti);

        $arr_contenido = [];
        foreach ($arr_Institucion as $inst) {
            $item = (object)[
                'id' => $inst->id,
                'beneficiario' => $inst->beneficiario,
                'cod_modular' => $inst->cod_modular,
                'ruc' => $inst->ruc,
                'nombre' => $inst->nombre,
                'options' => '
                    <button type="button" title="Editar" class="btn btn-primary waves-effect waves-light" data-toggle="modal" data-target=".modal_editar' . $inst->id . '">
                        <i class="fa fa-edit"></i>
                    </button>
'
            ];
            $arr_contenido[] = $item;
        }

        if (!empty($arr_contenido)) {
            $arr_Respuesta['total'] = count($busqueda_filtro);
            $arr_Respuesta['status'] = true;
            $arr_Respuesta['contenido'] = $arr_contenido;
        }

        // usuarios
        $arr_Usuario = $objUsuario->buscarUsuariosOrdenados();
        $arr_usuarios = [];
        foreach ($arr_Usuario as $usuario) {
            $arr_usuarios[] = (object)[
                'id' => $usuario->id,
                'nombre' => $usuario->nombres_apellidos
            ];
        }
        $arr_Respuesta['usuarios'] = $arr_usuarios;
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "registrar") {
    $arr_Respuesta = ['status' => false, 'msg' => 'Error_Sesion'];
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        if ($_POST) {
            $beneficiario = $_POST['beneficiario'];
            $cod_modular = $_POST['cod_modular'];
            $ruc = $_POST['ruc'];
            $nombre = $_POST['nombre'];

            if ($cod_modular === "" || $ruc === "" || $nombre === "" || $beneficiario === "") {
                $arr_Respuesta = ['status' => false, 'mensaje' => 'Error, campos vacíos'];
            } else {
                $existe = $objInstitucion->buscarInstitucionByCodigo($ruc);
                if ($existe) {
                    $arr_Respuesta = ['status' => false, 'mensaje' => 'Registro Fallido, ya existe'];
                } else {
                    $id = $objInstitucion->registrarInstitucion($beneficiario, $cod_modular, $ruc, $nombre);
                    $arr_Respuesta = $id > 0
                        ? ['status' => true, 'mensaje' => 'Registro Exitoso']
                        : ['status' => false, 'mensaje' => 'Error al registrar'];
                }
            }
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "actualizar") {
    $arr_Respuesta = ['status' => false, 'msg' => 'Error_Sesion'];
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        if ($_POST) {
            $id = $_POST['data'];
            $beneficiario = $_POST['beneficiario'];
            $cod_modular = $_POST['cod_modular'];
            $ruc = $_POST['ruc'];
            $nombre = $_POST['nombre'];

            if ($id === "" || $cod_modular === "" || $ruc === "" || $nombre === "" || $beneficiario === "") {
                $arr_Respuesta = ['status' => false, 'mensaje' => 'Error, campos vacíos'];
            } else {
                $existe = $objInstitucion->buscarInstitucionByCodigo($cod_modular);
                if ($existe && $existe->id != $id) {
                    $arr_Respuesta = ['status' => false, 'mensaje' => 'Código modular ya está registrado'];
                } else {
                    $ok = $objInstitucion->actualizarInstitucion($id, $beneficiario, $cod_modular, $ruc, $nombre);
                    $arr_Respuesta = $ok
                        ? ['status' => true, 'mensaje' => 'Actualizado Correctamente']
                        : ['status' => false, 'mensaje' => 'Error al actualizar'];
                }
            }
        }
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "datos_registro") {
    $arr_Respuesta = ['status' => false, 'msg' => 'Error_Sesion'];
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        $arr_Usuario = $objUsuario->buscarUsuariosOrdenados();
        $contenido = [];
        foreach ($arr_Usuario as $usuario) {
            $contenido[] = (object)[
                'id' => $usuario->id,
                'nombre' => $usuario->nombres_apellidos
            ];
        }
        $arr_Respuesta = ['status' => true, 'contenido' => $contenido, 'msg' => 'Datos encontrados'];
    }
    echo json_encode($arr_Respuesta);
}

if ($tipo == "buscar_institucion_id") {
    $arr_Respuesta = ['status' => false, 'msg' => 'Error_Sesion'];
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        $id_institucion = $_GET['data'] ?? '';
        $info_institucion = $objInstitucion->buscarInstitucionById($id_institucion);
        if ($info_institucion) {
            $arr_Respuesta = [
                'status' => true,
                'institucion' => $info_institucion
            ];
        } else {
            $arr_Respuesta['msg'] = 'Institución no encontrada';
        }
    }
    echo json_encode($arr_Respuesta);
    exit;
}
// Agregar este bloque al final del controlador, antes del cierre del archivo

if ($tipo == "listar_todas_instituciones") {
    $arr_Respuesta = ['status' => false, 'msg' => 'Error_Sesion'];
    if ($objSesion->verificar_sesion_si_activa($id_sesion, $token)) {
        $arr_Respuesta = ['status' => false, 'data' => []];
        $arr_Instituciones = $objInstitucion->listarTodasInstituciones();
        
        if (!empty($arr_Instituciones)) {
            $arr_Respuesta['status'] = true;
            $arr_Respuesta['data'] = $arr_Instituciones;
        } else {
            $arr_Respuesta['msg'] = 'No se encontraron instituciones';
        }
    }
    echo json_encode($arr_Respuesta);
}
