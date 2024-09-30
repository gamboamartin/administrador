<?php

namespace gamboamartin\administrador\models;

use base\orm\_modelo_parent_sin_codigo;
use PDO;

class adm_tipo_evento extends _modelo_parent_sin_codigo
{
    public function __construct(PDO $link)
    {
        $tabla = 'adm_tipo_evento';
        $columnas = array($tabla => false);

        $campos_obligatorios = array();

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;

        $this->etiqueta = 'Tipo Evento';
    }

}