<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_dia;


class controlador_adm_dia extends controlador_base{
    public function __construct($link){
        $modelo = new adm_dia($link);
        parent::__construct($link, $modelo);
    }
}