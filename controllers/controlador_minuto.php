<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\minuto;

class controlador_minuto extends controlador_base{
    public function __construct($link){
        $modelo = new minuto($link);
        parent::__construct($link, $modelo);
    }
}