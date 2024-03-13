<?php
namespace tests\validacion;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\validacion\validacion;
use validacion\accion;
use validacion\session;


class accionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }


    public function test_valida_accion_permitida(){
        errores::$error = false;
        $val = new accion();
        //$inicializacion = new liberator($inicializacion);

        $accion = '';
        $seccion = '';
        $resultado = $val->valida_accion_permitida($accion, $seccion);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $seccion debe tener info', $resultado['mensaje']);

        errores::$error = false;

        $accion = '';
        $seccion = 'a';
        $resultado = $val->valida_accion_permitida($accion, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $accion debe tener info', $resultado['mensaje']);

        errores::$error = false;

        unset($_SESSION['grupo_id']);
        $accion = 'b';
        $seccion = 'a';
        $resultado = $val->valida_accion_permitida($accion, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir grupo_id en SESSION', $resultado['mensaje']);

        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $accion = 'b';
        $seccion = 'a';
        $resultado = $val->valida_accion_permitida($accion, $seccion);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;


    }


}