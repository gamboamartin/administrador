<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;

use PDO;

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
}