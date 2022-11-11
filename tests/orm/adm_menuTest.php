<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_menu;
use gamboamartin\errores\errores;
use gamboamartin\test\test;



class adm_menuTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_registro(){

        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);


        $resultado = $modelo->registro(registro_id: 1);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(9,$resultado['adm_menu_n_secciones']);
        errores::$error = false;
    }

    public function test_registros(){

        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);


        $resultado = $modelo->registros();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(9,$resultado[0]['adm_menu_n_secciones']);
        errores::$error = false;
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

