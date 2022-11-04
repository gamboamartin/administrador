<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo;
use PDO;

class adm_accion_basica extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_accion_basica';
        $columnas = array($tabla=> false);
        $campos_obligatorios=array('visible','inicio','lista');
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
}
