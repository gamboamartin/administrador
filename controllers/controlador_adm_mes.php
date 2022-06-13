<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\adm_mes;


class controlador_adm_mes extends controlador_base{
    public function __construct($link){
        $modelo = new adm_mes($link);
        parent::__construct(link: $link,modelo:  $modelo);
    }
}