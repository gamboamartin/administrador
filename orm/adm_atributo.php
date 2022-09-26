<?php
namespace models;
use base\orm\modelo;
use PDO;

class adm_atributo extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'adm_tipo_dato'=>$tabla,'adm_seccion'=>$tabla);
        $campos_obligatorios = array('adm_tipo_dato_id','adm_seccion_id');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}