<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_accion;


use PDO;

class controlador_adm_accion extends controlador_base
{

    /**
     * @param PDO $link Conexion a la base de datos
     */
    public function __construct(PDO $link)
    {
        $modelo = new adm_accion($link);
        parent::__construct($link, $modelo);
        // $this->directiva = new html_accion();
    }
}