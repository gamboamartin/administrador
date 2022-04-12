<?php
namespace models;
use gamboamartin\errores\errores;
use gamboamartin\orm\modelo;
use PDO;

class bitacora extends modelo{
    /**
     * DEBUG INI
     * bitacora constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'seccion_menu'=>$tabla,'usuario'=>$tabla);
        $campos_obligatorios = array('seccion_menu_id','registro','usuario_id','transaccion','sql_data','valor_id');
        parent::__construct($link, $tabla,$columnas_extra = array(),$campos_obligatorios,
            $tipo_campos = array(),$columnas);
    }
}