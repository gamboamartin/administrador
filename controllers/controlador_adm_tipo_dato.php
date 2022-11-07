<?php
namespace gamboamartin\controllers;

use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_tipo_dato;


class controlador_adm_tipo_dato extends controlador_base{
    public function __construct($link){
        $modelo = new adm_tipo_dato($link);
        parent::__construct($link, $modelo);
    }
}
