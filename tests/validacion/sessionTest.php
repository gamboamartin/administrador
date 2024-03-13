<?php
namespace tests\validacion;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use validacion\session;


class sessionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }


    public function test_valida_datos_recepcion(){
        errores::$error = false;
        $val = new session();
        //$inicializacion = new liberator($inicializacion);


        $resultado = $val->valida_datos_recepcion();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir user', $resultado['mensaje']);

        errores::$error = false;
        //$inicializacion = new liberator($inicializacion);

        $_POST['user'] = 'a';
        $resultado = $val->valida_datos_recepcion();

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir password', $resultado['mensaje']);

        errores::$error = false;
        //$inicializacion = new liberator($inicializacion);

        $_POST['user'] = 'a';
        $_POST['password'] = 'a';
        $resultado = $val->valida_datos_recepcion();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);


        errores::$error = false;
    }


}