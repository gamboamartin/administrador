<?php
namespace controllers;
use controller\controlador_base;
use models\accion_basica;

class controlador_accion_basica extends controlador_base{
    public function __construct($link){
        $modelo = new accion_basica($link);
        parent::__construct($link, $modelo);
    }
}