<?php
namespace tests\base\orm;

use base\orm\codigos;
use base\orm\filtros;

use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_seccion;
use stdClass;


class codigosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
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