<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\atributo;

class controlador_atributo extends controlador_base{
    public function __construct($link){
        $modelo = new atributo($link);
        parent::__construct($link, $modelo);
    }

}