<?php
namespace models;
use gamboamartin\orm\modelo;
use PDO;

class campo extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'seccion'=>$tabla,'tipo_dato'=>$tabla);
        parent::__construct(link: $link,tabla:  $tabla,columnas: $columnas);
    }
}