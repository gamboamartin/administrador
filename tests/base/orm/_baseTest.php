<?php
namespace tests\base;


use base\orm\_base;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

class _baseTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_status()
    {

        errores::$error = false;
        $tabla = 'adm_mes';
        $base = new _base($this->link, $tabla);
        $base = new liberator($base);

        $registro = array();
        $key = 'a';
        $resultado = $base->asigna_status($key, $registro);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('activo',$resultado['a']);
        errores::$error = false;

    }



    public function test_status_alta()
    {

        errores::$error = false;
        $tabla = 'adm_mes';
        $base = new _base($this->link, $tabla);
        $base = new liberator($base);

        $registro = array();
        $key = 'a';
        $resultado = $base->status_alta($key, $registro);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('activo',$resultado['a']);
        errores::$error = false;
    }

    public function test_valida_alta_bd()
    {

        errores::$error = false;
        $tabla = 'adm_mes';
        $base = new _base($this->link, $tabla);
        $base = new liberator($base);

        $registro = array();
        $registro['descripcion'] = 'xyz';
        $resultado = $base->valida_alta_bd($registro);

        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

}