<?php
namespace gamboamartin\controllers;

use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_campo;


class controlador_adm_campo extends controlador_base{
    public function __construct($link){
        $modelo = new adm_campo($link);
        parent::__construct($link, $modelo);
    }


}