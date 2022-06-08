<?php
namespace models;
use base\orm\modelo;

use PDO;

class adm_bitacora extends modelo{
    /**
     * DEBUG INI
     * bitacora constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'seccion_menu'=>$tabla,'usuario'=>$tabla);
        $campos_obligatorios = array('seccion_menu_id','registro','usuario_id','transaccion','sql_data','valor_id');
        parent::__construct(link: $link, tabla: $tabla,columnas: $columnas);
    }
}