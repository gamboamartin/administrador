<?php
namespace tests\base\orm;

use base\orm\codigos;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class codigosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_codigo_alta(): void
    {


        errores::$error = false;
        $mb = new codigos();

        $mb = new liberator($mb);


        $keys_registro = array();
        $keys_row = array();
        $row = new stdClass();
        $registro = array();
        $resultado = $mb->codigo_alta($keys_registro, $keys_row, $row, $registro);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $keys_registro = array();
        $keys_row = array();
        $row = new stdClass();
        $registro = array();
        $keys_registro[] = '';
        $resultado = $mb->codigo_alta($keys_registro, $keys_row, $row, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar codigo', $resultado['mensaje']);

        errores::$error = false;

        $keys_registro = array();
        $keys_row = array();
        $row = new stdClass();
        $registro = array();
        $keys_registro[] = 'a';
        $resultado = $mb->codigo_alta($keys_registro, $keys_row, $row, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar codigo', $resultado['mensaje']);

        errores::$error = false;

        $keys_registro = array();
        $keys_row = array();
        $row = new stdClass();
        $registro = array();
        $keys_registro[] = 'a';
        $registro['a'] = 'z';
        $resultado = $mb->codigo_alta($keys_registro, $keys_row, $row, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('z-', $resultado);

        errores::$error = false;

        $keys_registro = array();
        $keys_row = array();
        $row = new stdClass();
        $registro = array();
        $keys_registro[] = 'a';
        $registro['a'] = 'z';
        $keys_row[] = '';
        $resultado = $mb->codigo_alta($keys_registro, $keys_row, $row, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar codigo', $resultado['mensaje']);

        errores::$error = false;

        $keys_registro = array();
        $keys_row = array();
        $row = new stdClass();
        $registro = array();
        $keys_registro[] = 'a';
        $registro['a'] = 'z';
        $keys_row[] = 'b';
        $resultado = $mb->codigo_alta($keys_registro, $keys_row, $row, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar codigo', $resultado['mensaje']);

        errores::$error = false;

        $keys_registro = array();
        $keys_row = array();
        $row = new stdClass();
        $registro = array();
        $keys_registro[] = 'a';
        $registro['a'] = 'z';
        $keys_row[] = 'b';
        $row->b = 'a';
        $resultado = $mb->codigo_alta($keys_registro, $keys_row, $row, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('z-a-', $resultado);

        errores::$error = false;
    }

    public function test_codigo_random(): void
    {


        errores::$error = false;
        $cods = new codigos();
        $cods = new liberator($cods);



        $resultado = $cods->codigo_random();
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_genera_codigo(){


        errores::$error = false;
        $mb = new codigos();
        $mb = new liberator($mb);

        $keys_registro = array();
        $keys_row = array();
        $modelo = new adm_accion($this->link);
        $registro_id = 1;
        $registro = array();
        $resultado = $mb->genera_codigo($keys_registro, $keys_row, $modelo, $registro_id, $registro);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_valida_codigo_aut(){


        errores::$error = false;
        $cods = new codigos($this->link);
        $cods = new liberator($cods);

        $keys_registro = array();
        $key = '';
        $registro = array();
        $resultado = $cods->valida_codigo_aut($key, $keys_registro, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar key', $resultado['mensaje']);

        errores::$error = false;

        $keys_registro = array();
        $key = 'a';
        $registro = array();
        $resultado = $cods->valida_codigo_aut($key, $keys_registro, $registro);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_valida_key_vacio(): void
    {


        errores::$error = false;
        $cods = new codigos($this->link);
        $cods = new liberator($cods);


        $key = 'a';
        $resultado = $cods->valida_key_vacio($key);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }





}