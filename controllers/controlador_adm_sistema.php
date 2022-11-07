<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_sistema;


class controlador_adm_sistema extends controlador_base {
    public function __construct($link){
        $modelo = new adm_sistema($link);
        parent::__construct(link: $link,modelo:  $modelo);
    }
}