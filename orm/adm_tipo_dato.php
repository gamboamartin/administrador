<?php
namespace gamboamartin\administrador\models;


use base\orm\_modelo_parent;
use base\orm\modelo;
use PDO;

class adm_tipo_dato extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'adm_tipo_dato';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla,columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
}