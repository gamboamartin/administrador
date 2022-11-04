<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use PDO;

class adm_genero extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_genero';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
}