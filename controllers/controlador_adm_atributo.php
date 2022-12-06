<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_atributo;


class controlador_atributo extends controlador_base{
    public function __construct($link){
        $modelo = new adm_atributo($link);
        parent::__construct($link, $modelo);
    }

}