<?php
namespace gamboamartin\administrador\models;
use base\orm\_modelo_parent;
use PDO;

class adm_categoria_usuario extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'adm_categoria_usuario';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
}