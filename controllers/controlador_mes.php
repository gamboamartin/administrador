<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\mes;

class controlador_mes extends controlador_base{
    public function __construct($link){
        $modelo = new mes($link);
        parent::__construct(link: $link,modelo:  $modelo);
    }
}