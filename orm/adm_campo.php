<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use PDO;

class adm_campo extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_campo';
        $columnas = array($tabla=>false,'adm_seccion'=>$tabla,'adm_tipo_dato'=>$tabla);
        $campos_obligatorios = array('adm_seccion_id','adm_tipo_dato_id');
        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
}