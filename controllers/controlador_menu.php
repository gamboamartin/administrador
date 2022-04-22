<?php
namespace controllers;
use base\controller\controlador_base;
use models\menu;

class controlador_menu extends controlador_base{
    public function __construct($link){
        $modelo = new menu($link);
        parent::__construct($link, $modelo);
    }

}