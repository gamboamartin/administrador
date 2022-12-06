<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_hora;


class controlador_hora extends controlador_base{
    public function __construct($link){
        $modelo = new adm_hora($link);
        parent::__construct($link, $modelo);
    }
}