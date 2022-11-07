<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_bitacora;


class controlador_adm_bitacora extends controlador_base{
    public function __construct($link){
        $modelo = new adm_bitacora($link);
        parent::__construct($link, $modelo);
    }

}