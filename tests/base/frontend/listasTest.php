<?php
namespace tests\base\frontend;

use base\frontend\listas;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class listasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }



    public function test_footer_registro(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $registro = array();
        $seccion = '';
        $resultado = $ls->footer_registro($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $seccion = '';
        $registro[] = '';
        $resultado = $ls->footer_registro($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $seccion = 'a';
        $registro[] = '';
        $resultado = $ls->footer_registro($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $seccion = 'adm_seccion';
        $registro[] = '';
        $resultado = $ls->footer_registro($registro, $seccion);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inactivo', $resultado->registro['adm_seccion_status']);

        errores::$error = false;
    }



    public function test_obten_panel(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $status = '';

        $resultado = $ls->obten_panel($status);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error status debe tener datos', $resultado['mensaje']);

        errores::$error = false;
        $status = 'a';

        $resultado = $ls->obten_panel($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('bg-danger', $resultado);

        errores::$error = false;
        $status = 'inactivo';

        $resultado = $ls->obten_panel($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('bg-danger', $resultado);

        errores::$error = false;
        $status = 'activo';

        $resultado = $ls->obten_panel($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }



    public function test_registro_status(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $registro = array();
        $seccion = '';
        $resultado = $ls->registro_status($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error seccion esta vacia', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $seccion = 'a';
        $resultado = $ls->registro_status($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inactivo', $resultado['a_status']);

        errores::$error = false;

        $registro = array();
        $registro['a_status'] = 'activo';
        $seccion = 'a';
        $resultado = $ls->registro_status($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('activo', $resultado['a_status']);
        errores::$error = false;
    }



    public function test_td_acciones(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $resultado = $ls->td_acciones();
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;


    }


    public function test_tr_data(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $campos = array();
        $registro = array();
        $seccion = '';
        $resultado = $ls->tr_data($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $registro = array();
        $seccion = '';
        $registro[] = '';
        $resultado = $ls->tr_data($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $registro = array();
        $seccion = 'a';
        $registro[] = '';
        $resultado = $ls->tr_data($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $registro = array();
        $seccion = 'adm_seccion';
        $registro[] = '';
        $resultado = $ls->tr_data( $registro, $seccion);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('menu_acciones_lista', $resultado);
        errores::$error = false;
    }



}