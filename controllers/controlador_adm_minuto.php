<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\adm_minuto;


class controlador_adm_minuto extends controlador_base{
    public function __construct($link){
        $modelo = new adm_minuto($link);
        parent::__construct($link, $modelo);
    }
}