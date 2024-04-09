<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;

use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_bitacora extends modelo{
    /**
     * DEBUG INI
     * bitacora constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        
        $tabla = 'adm_bitacora';
        $columnas = array($tabla=>false,'adm_seccion'=>$tabla,'adm_usuario'=>$tabla);
        $campos_obligatorios = array('adm_seccion_id','registro','adm_usuario_id','transaccion','sql_data','valor_id');
        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(): array|stdClass
    {
        if(!isset($this->registro['codigo'])){
            $codigo = $this->get_codigo_aleatorio(longitud: 20);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo',data:  $codigo);
            }
            $this->registro['codigo'] = $codigo;
        }
        if(!isset($this->registro['descripcion'])){
            $descripcion = date('Y-m-d i:s').mt_rand(10000,99999);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar descripcion',data:  $descripcion);
            }
            $this->registro['descripcion'] = $descripcion;
        }
        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar codigo',data:  $r_alta_bd);
        }
        return $r_alta_bd;

    }
}