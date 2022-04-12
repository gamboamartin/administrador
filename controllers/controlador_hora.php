<?php
namespace controllers;
use controller\controlador_base;
use models\hora;

class controlador_hora extends controlador_base{
    public function __construct($link){
        $modelo = new hora($link);
        parent::__construct($link, $modelo);
    }
}