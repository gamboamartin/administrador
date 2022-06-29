<?php
namespace tests\base\orm;

use base\orm\elementos;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;



class elementosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_campo_tabla_externa(): void
    {
        errores::$error = false;
        $elementos = new elementos();
        //$elementos = new liberator($elementos);

        $campo = array();
        $campo['adm_elemento_lista_campo'] = 'a';
        $resultado = $elementos->campo_tabla_externa($campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);
        errores::$error = false;
    }

    public function test_columnas_elemento_lista(): void
    {
        errores::$error = false;
        $elementos = new elementos();
        //$elementos = new liberator($elementos);

        $campo = array();
        $resultado = $elementos->columnas_elemento_lista($campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }

    public function test_data_campo_tabla_externa(): void
    {
        errores::$error = false;
        $elementos = new elementos();
        $elementos = new liberator($elementos);

        $campo = array();

        $resultado = $elementos->data_campo_tabla_externa($campo);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campo', $resultado['mensaje']);

        errores::$error = false;
        $campo = array();
        $campo['adm_elemento_lista_campo_tabla_externa'] = 'a';

        $resultado = $elementos->data_campo_tabla_externa($campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);


        errores::$error = false;

    }

    public function test_llaves_valores(): void
    {
        errores::$error = false;
        $elementos = new elementos();
        //$elementos = new liberator($elementos);

        $campo = array();
        $resultado = $elementos->llaves_valores($campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }

    public function test_pattern(): void
    {
        errores::$error = false;
        $elementos = new elementos();
        //$elementos = new liberator($elementos);

        $campo = array();

        $resultado = $elementos->pattern($campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }

}