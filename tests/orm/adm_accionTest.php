<?php
namespace tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;


class adm_accionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_accion_seccion(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);

        $accion= 'a';
        $seccion= 'b';
        $resultado = $modelo->accion_seccion($accion, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_asigna_status(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $key = 'a';
        $registro= array();
        $resultado = $modelo->asigna_status($key, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('activo', $resultado['a']);
        errores::$error = false;
    }

    public function test_acciones_permitidas(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);
        $accion = '';
        $seccion= '';
        $resultado = $modelo->acciones_permitidas($accion, $modelo, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir grupo_id', $resultado['mensaje']);

        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $accion = 'a';
        $seccion= 'a';
        $resultado = $modelo->acciones_permitidas($accion, $modelo, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error obtener seccion_menu_id', $resultado['mensaje']);

        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $accion = 'a';
        $seccion= 'adm_accion';
        $resultado = $modelo->acciones_permitidas($accion, $modelo, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;
    }

    public function test_filtro_accion_seccion(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $accion = '';
        $seccion= '';
        $resultado = $modelo->filtro_accion_seccion($accion, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar seccion', $resultado['mensaje']);

        errores::$error = false;

        $accion = 'c';
        $seccion= 'a';
        $resultado = $modelo->filtro_accion_seccion($accion, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['adm_seccion.descripcion']);
        $this->assertEquals('c', $resultado['adm_accion.descripcion']);
        errores::$error = false;
    }

    public function test_filtro_permiso(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
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
        $this->assertEquals(1, $resultado['adm_accion_grupo.adm_grupo_id']);



        errores::$error = false;

    }

    public function test_filtro_seccion_grupo(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);

        $adm_grupo_id= 1;
        $adm_seccion_id= -1;
        $resultado = $modelo->filtro_seccion_grupo($adm_grupo_id, $adm_seccion_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['adm_grupo.id']);
        errores::$error = false;
    }

    public function test_grupos_id_por_accion(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);

        $adm_accion_id= 2;
        $resultado = $modelo->grupos_id_por_accion($adm_accion_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2, $resultado[0]);

        errores::$error = false;
    }





}

