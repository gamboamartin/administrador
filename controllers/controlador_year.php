<?php
namespace controllers;
use controller\controlador_base;
use models\year;

class controlador_year extends controlador_base{
    public function __construct($link){
        $modelo = new year($link);
        parent::__construct($link, $modelo);
    }
}