<?php
namespace models;
use base\orm\modelo;
use PDO;

class atributo extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'tipo_dato'=>$tabla,'seccion_menu'=>$tabla);
        $campos_obligatorios = array('tipo_dato_id','seccion_menu_id');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}