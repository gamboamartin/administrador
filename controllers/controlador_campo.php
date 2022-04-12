<?php
namespace controllers;

use base\controller\controlador_base;
use models\campo;

class controlador_campo extends controlador_base{
    public function __construct($link){
        $modelo = new campo($link);
        parent::__construct($link, $modelo);
    }


}