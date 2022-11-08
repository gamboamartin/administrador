<?php
namespace tests\base;

use base\seguridad;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class seguridadTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_init(){

        errores::$error = false;

        if(isset($_SESSION['activa'])){
            unset($_SESSION['activa']);
        }
        $seg = new seguridad();
        //$seg = new liberator($seg);

        $resultado = $seg->init();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_init_accion(){

        errores::$error = false;

        if(isset($_SESSION['activa'])){
            unset($_SESSION['activa']);
        }
        $seg = new seguridad();
        $seg = new liberator($seg);

        $resultado = $seg->init_accion();

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('login',$resultado);

        errores::$error = false;

        $_SESSION['activa'] = 1;

        $resultado = $seg->init_accion();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inicio',$resultado);
        errores::$error = false;
    }

    public function test_init_menu_login(){

        errores::$error = false;

        if(isset($_SESSION['activa'])){
            unset($_SESSION['activa']);
        }
        $seg = new seguridad();
        $seg = new liberator($seg);

        $resultado = $seg->init_menu_login();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_session',$resultado->seccion);
        $this->assertEquals('login',$resultado->accion);
        $this->assertNotTrue($resultado->menu);
        errores::$error = false;
    }





}