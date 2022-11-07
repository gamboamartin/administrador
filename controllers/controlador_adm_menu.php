<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_menu;


class controlador_adm_menu extends controlador_base{
    public function __construct($link){
        $modelo = new adm_menu($link);
        parent::__construct($link, $modelo);
    }

}