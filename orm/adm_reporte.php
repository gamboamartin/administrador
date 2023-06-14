<?php
namespace gamboamartin\administrador\models;

use base\orm\_modelo_parent_sin_codigo;

use PDO;


class adm_reporte extends _modelo_parent_sin_codigo {
    public function __construct(PDO $link){
        $tabla = 'adm_reporte';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();
        $childrens =array();

        $columnas_extra = array();

        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;

        $this->etiqueta = 'Reportes';

    }

}