<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;

use PDO;

class adm_estado_civil extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_estado_civil';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
}