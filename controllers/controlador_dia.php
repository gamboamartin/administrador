<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\dia;

class controlador_dia extends controlador_base{
    public function __construct($link){
        $modelo = new dia($link);
        parent::__construct($link, $modelo);
    }
}