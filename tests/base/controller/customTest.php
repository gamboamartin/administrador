<?php
namespace tests\base\controller;

use base\controller\custom;
use base\seguridad;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class customTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_init_css(): void
    {

        errores::$error = false;

        $custom = new custom();
        $custom = new liberator($custom);

        $seguridad = new seguridad();
        $resultado = $custom->init_css($seguridad);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->css);
        $this->assertEquals(false,$resultado->existe_php);
        $this->assertEquals(false,$resultado->existe_css);
        $this->assertEquals("./css/'.adm_session.'.'.login",$resultado->file_base);
        errores::$error = false;
    }



}