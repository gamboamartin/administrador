<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_accion_basica;


class controlador_adm_accion_basica extends controlador_base{
    public function __construct($link){
        $modelo = new adm_accion_basica($link);
        parent::__construct($link, $modelo);
    }
}