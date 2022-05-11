<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\sistema;

class controlador_sistema extends controlador_base {
    public function __construct($link){
        $modelo = new sistema($link);
        parent::__construct(link: $link,modelo:  $modelo);
    }
}