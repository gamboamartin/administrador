<?php
namespace gamboamartin\administrador\models;


use base\orm\_modelo_parent_sin_codigo;
use PDO;

class adm_tipo_dato extends _modelo_parent_sin_codigo {
    public function __construct(PDO $link, array $childrens = array(), array $columnas_extra = array()){
        $tabla = 'adm_tipo_dato';
        $columnas = array($tabla=>false);

        $childrens['adm_campo'] = "gamboamartin\\administrador\\models";

        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas,
            columnas_extra: $columnas_extra, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;
    }

}