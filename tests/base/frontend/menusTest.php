<?php
namespace tests\base\frontend;

use base\frontend\menus;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class menusTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_breadcrumb_active(){
        errores::$error = false;
        $m = new menus();
        $m = new liberator($m);

        $etiqueta = '';
        $resultado = $m->breadcrumb_active(etiqueta: $etiqueta);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al $etiqueta no puede venir vacia',
            $resultado['mensaje']);

        errores::$error = false;

        $etiqueta = 'x';
        $resultado = $m->breadcrumb_active(etiqueta: $etiqueta);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_data_menu(){
        errores::$error = false;
        $m = new menus();
        $m = new liberator($m);

        $etiqueta = array();
        $resultado = $m->data_menu($etiqueta);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar etiqueta", $resultado['mensaje']);

        errores::$error = false;

        $etiqueta = array();
        $etiqueta['adm_accion_descripcion'] = 'a';
        $etiqueta['adm_accion_icono'] = 'a';
        $resultado = $m->data_menu($etiqueta);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado['etiqueta']);

        errores::$error = false;


    }

    public function test_etiquetas_array(){
        errores::$error = false;
        $m = new menus();
        $m = new liberator($m);

        $etiquetas_accion = array();
        $resultado = $m->etiquetas_array($etiquetas_accion);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}