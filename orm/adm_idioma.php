<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class adm_idioma extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
    }
}