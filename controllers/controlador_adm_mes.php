<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\adm_mes;
use stdClass;


class controlador_adm_mes extends controlador_base{
    public function __construct($link, stdClass $paths_conf = new stdClass()){
        $modelo = new adm_mes($link);
        parent::__construct(link: $link,modelo:  $modelo, paths_conf: $paths_conf);
    }
}