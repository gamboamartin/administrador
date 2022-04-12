<?php
namespace controllers;
use controller\controlador_base;
use models\elemento_lista;
class controlador_elemento_lista extends controlador_base{
    public function __construct($link){
        $modelo = new elemento_lista($link);
        parent::__construct($link, $modelo);
    }
}