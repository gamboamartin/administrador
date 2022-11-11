<?php
namespace gamboamartin\controllers;

use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_grupo;


class controlador_adm_grupo extends controlador_base{

    public function __construct($link){
        $modelo = new adm_grupo($link);
        parent::__construct($link, $modelo);
    }

}
