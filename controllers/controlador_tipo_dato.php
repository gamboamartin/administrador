<?php
namespace controllers;

use base\controller\controlador_base;
use models\tipo_dato;

class controlador_tipo_dato extends controlador_base{
    public function __construct($link){
        $modelo = new tipo_dato($link);
        parent::__construct($link, $modelo);
    }
}
