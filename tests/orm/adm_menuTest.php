<?php
namespace tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;
use models\adm_menu;


class adm_menuTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_secciones(){

        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);

        $adm_menu_id= 1;
        $resultado = $modelo->secciones($adm_menu_id);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }



}

