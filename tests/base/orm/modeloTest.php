<?php
namespace tests\src;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_seccion;



class modeloTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }







    public function test_data_sentencia(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);


        $campo = '';
        $value = '';
        $sentencia = '';
        $where = '';
        $resultado = $modelo->data_sentencia($campo, $sentencia, $value, $where);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $value = '';
        $sentencia = '';
        $where = '';
        $resultado = $modelo->data_sentencia($campo, $sentencia, $value, $where);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_filtro_or(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->filtro_or();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }





    public function test_obten_data(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $resultado = $modelo->obten_data();
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el id debe ser mayor a 0 en el modelo adm_seccion', $resultado['mensaje']);

        errores::$error = false;
        $modelo->registro_id = 1;
        $resultado = $modelo->obten_data();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_obten_por_id(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $resultado = $modelo->obten_por_id();
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el id debe ser mayor a 0', $resultado['mensaje']);

        errores::$error = false;
        $modelo->registro_id = 1;
        $resultado = $modelo->obten_por_id();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_obten_registros_filtro_and_ordenado(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);


        $campo = '';
        $filtros = array();
        $orden = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo,false, $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error los filtros no pueden venir vacios', $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $filtros = array();
        $orden = '';
        $filtros[] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no pueden venir vacios', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $filtros = array();
        $orden = '';
        $filtros['a'] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'adm_seccion.id';
        $filtros = array();
        $orden = '';
        $filtros['adm_seccion.id'] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, $filtros, $orden);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }





    public function test_registro(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $resultado = $modelo->registro(registro_id: 1);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }


    public function test_seccion_menu_id(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $seccion = '';
        $resultado = $modelo->seccion_menu_id($seccion);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error seccion no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $seccion = 'a';
        $resultado = $modelo->seccion_menu_id($seccion);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener seccion menu no existe', $resultado['mensaje']);

        errores::$error = false;

        $seccion = 'adm_seccion';
        $resultado = $modelo->seccion_menu_id($seccion);
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(13, $resultado);

        errores::$error = false;


    }

    public function test_sentencia_or(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $campo = '';
        $sentencia = '';
        $value = '';
        $resultado = $modelo->sentencia_or($campo, $sentencia, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $sentencia = '';
        $value = '';
        $resultado = $modelo->sentencia_or($campo, $sentencia, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  a = ''", $resultado);
        errores::$error = false;
    }




}