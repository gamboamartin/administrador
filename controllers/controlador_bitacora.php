<?php
namespace controllers;
use controller\controlador_base;
use models\bitacora;

class controlador_bitacora extends controlador_base{
    public function __construct($link){
        $modelo = new bitacora($link);
        parent::__construct($link, $modelo);
    }

}