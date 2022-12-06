<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_elemento_lista;


class controlador_adm_elemento_lista extends controlador_base{
    public function __construct($link){
        $modelo = new adm_elemento_lista($link);
        parent::__construct($link, $modelo);
    }
}