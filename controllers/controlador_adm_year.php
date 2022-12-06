<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_year;


class controlador_adm_year extends controlador_base{
    public function __construct($link){
        $modelo = new adm_year($link);
        parent::__construct($link, $modelo);
    }
}