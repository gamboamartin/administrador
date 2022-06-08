<?php
namespace models;
use base\orm\modelo;
use PDO;

class adm_campo extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'seccion'=>$tabla,'tipo_dato'=>$tabla);
        $campos_obligatorios = array('seccion_id','tipo_dato_id');
        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,columnas: $columnas);
    }
}