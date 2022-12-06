<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_menu;
use PDO;
use stdClass;


class controlador_adm_menu extends controlador_base{
    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $modelo = new adm_menu(link: $link);
        parent::__construct(link: $link,modelo:  $modelo, paths_conf: $paths_conf);
    }

}