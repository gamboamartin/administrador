<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\adm_estado_civil;
use PDO;
use stdClass;

class controlador_adm_estado_civil extends controlador_base{
    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $modelo = new adm_estado_civil($link);
        parent::__construct(link: $link, modelo: $modelo, paths_conf: $paths_conf);
    }
}