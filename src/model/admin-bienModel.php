<?php
require_once "../library/conexion.php";

class BienModel
{

    private $conexion;
    function __construct()
    {
        $this->conexion = new Conexion();
        $this->conexion = $this->conexion->connect();
    }
    public function registrarBien($ambiente, $cod_patrimonial, $denominacion, $marca, $modelo, $tipo, $color, $serie, $dimensiones, $valor, $situacion, $estado_conservacion, $observaciones, $id_usuario,$id_ingreso)
    {
        $sql = $this->conexion->query("INSERT INTO bienes (id_ingreso_bienes ,id_ambiente,cod_patrimonial, denominacion, marca,modelo,tipo,color,serie,dimensiones,valor,situacion,estado_conservacion,observaciones,usuario_registro ) VALUES ('$id_ingreso','$ambiente', '$cod_patrimonial','$denominacion', '$marca', '$modelo', '$tipo', '$color', '$serie', '$dimensiones', '$valor', '$situacion', '$estado_conservacion', '$observaciones', '$id_usuario')");
        if ($sql) {
            $sql = $this->conexion->insert_id;
        } else {
            $sql = 0;
        }
        return $sql;
    }
    public function actualizarBien($id, $cod_patrimonial, $denominacion, $marca, $modelo, $tipo, $color, $serie, $dimensiones, $valor, $situacion, $estado_conservacion, $observaciones)
    {
        $sql = $this->conexion->query("UPDATE bienes SET cod_patrimonial='$cod_patrimonial',denominacion='$denominacion',marca='$marca',modelo='$modelo',tipo='$tipo',color='$color',serie='$serie',dimensiones='$dimensiones',valor='$valor',situacion='$situacion',estado_conservacion='$estado_conservacion',observaciones='$observaciones' WHERE id='$id'");
        return $sql;
    }
    public function actualizarBien_Ambiente($id, $nuevo_ambiente)
    {
        $sql = $this->conexion->query("UPDATE bienes SET id_ambiente='$nuevo_ambiente'WHERE id='$id'");
        return $sql;
    }
    public function buscarBienById($id)
    {
        $sql = $this->conexion->query("SELECT * FROM bienes WHERE id='$id'");
        $sql = $sql->fetch_object();
        return $sql;
    }
    public function buscarBienes_filtro($filtro, $ambiente)
    {
        $arrRespuesta = array();
        $sql = $this->conexion->query("SELECT * FROM bienes WHERE (cod_patrimonial LIKE '$filtro%' OR denominacion LIKE '%$filtro%') AND id_ambiente='$ambiente'");
        while ($objeto = $sql->fetch_object()) {
            array_push($arrRespuesta, $objeto);
        }
        return $arrRespuesta;
    }
    public function buscarBienByCodigoPatrimonial($codigo)
    {
        $sql = $this->conexion->query("SELECT * FROM bienes WHERE cod_patrimonial ='$codigo'");
        $sql = $sql->fetch_object();
        return $sql;
    }
    public function buscarBienByCpdigoInstitucion($codigo, $institucion)
    {
        $sql = $this->conexion->query("SELECT * FROM bienes WHERE codigo='$codigo' AND id_ies='$institucion'");
        $sql = $sql->fetch_object();
        return $sql;
    }

    public function buscarBienesOrderByDenominacion_tabla_filtro($busqueda_tabla_codigo, $busqueda_tabla_ambiente, $busqueda_tabla_denominacion, $ies)
    {
        //condicionales para busqueda
        $condicion = "";
        $condicion .= " cod_patrimonial LIKE '$busqueda_tabla_codigo%' AND denominacion LIKE '$busqueda_tabla_denominacion%'";
        if ($busqueda_tabla_ambiente > 0) {
            $condicion .= " AND id_ambiente='$busqueda_tabla_ambiente'";
        }
        $arrRespuesta = array();
        $respuesta = $this->conexion->query("SELECT bienes.id FROM bienes
                INNER JOIN ambientes_institucion ON bienes.id_ambiente = ambientes_institucion.id AND (ambientes_institucion.id_ies = '$ies') WHERE $condicion ORDER BY detalle");
        while ($objeto = $respuesta->fetch_object()) {
            array_push($arrRespuesta, $objeto);
        }
        return $arrRespuesta;
    }
    public function buscarBienesOrderByDenominacion_tabla($pagina, $cantidad_mostrar, $busqueda_tabla_codigo, $busqueda_tabla_ambiente, $busqueda_tabla_denominacion, $ies)
    {
        //condicionales para busqueda
        $condicion = "";
        $condicion .= " cod_patrimonial LIKE '$busqueda_tabla_codigo%' AND denominacion LIKE '$busqueda_tabla_denominacion%'";
        if ($busqueda_tabla_ambiente > 0) {
            $condicion .= " AND id_ambiente='$busqueda_tabla_ambiente'";
        }
        $iniciar = ($pagina - 1) * $cantidad_mostrar;
        $arrRespuesta = array();
        $respuesta = $this->conexion->query("SELECT bienes.id, bienes.id_ambiente,bienes.cod_patrimonial ,bienes.denominacion,bienes.marca,bienes.modelo,bienes.tipo,bienes.color,bienes.serie, bienes.dimensiones, bienes.valor, bienes.situacion, bienes.estado_conservacion,bienes.observaciones FROM bienes
            INNER JOIN ambientes_institucion ON bienes.id_ambiente = ambientes_institucion.id AND (ambientes_institucion.id_ies = '$ies') WHERE $condicion  ORDER BY detalle LIMIT $iniciar, $cantidad_mostrar");
        while ($objeto = $respuesta->fetch_object()) {
            array_push($arrRespuesta, $objeto);
        }
        return $arrRespuesta;
    }
}
