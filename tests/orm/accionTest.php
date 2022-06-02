<?php
namespace tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\accion;
use models\session;
use stdClass;


class accionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_filtro_permiso(){

        errores::$error = false;
        $modelo = new accion($this->link);
        $modelo = new liberator($modelo);
        $accion = '';
        $grupo_id= -1;
        $seccion= '';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error accion esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $accion = 'a';
        $grupo_id= -1;
        $seccion= '';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $grupo_id debe ser mayor a 0', $resultado['mensaje']);

        errores::$error = false;
        $accion = 'a';
        $grupo_id= 1;
        $seccion= '';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $seccion esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $accion = 'a';
        $grupo_id= 1;
        $seccion= 'z';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['adm_accion_grupo.grupo_id']);



        errores::$error = false;

    }





}

