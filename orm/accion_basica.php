<?php
namespace models;

use base\orm\modelo;
use PDO;

class accion_basica extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=> false);
        $campos_obligatorios=array('visible','inicio','lista');
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas);
    }
}
