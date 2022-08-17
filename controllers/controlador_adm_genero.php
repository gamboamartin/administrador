<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use models\adm_genero;
use PDO;
use stdClass;

class controlador_adm_genero extends controlador_base{
    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $modelo = new adm_genero($link);
        parent::__construct(link: $link, modelo: $modelo, paths_conf: $paths_conf);
    }
}