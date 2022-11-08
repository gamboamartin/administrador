<?php
namespace tests\base\orm;

use base\orm\sumas;
use base\orm\where;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class sumasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_columnas_suma(){
        errores::$error = false;
        $sum = new sumas();
        //$sum = new liberator($sum);

        $campos = array();
        $campos['x'] = 'z';
        $resultado = $sum->columnas_suma($campos);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' IFNULL( SUM(z) ,0)AS x', $resultado);
        errores::$error = false;
    }

    public function test_data_campo_suma(){
        errores::$error = false;
        $sum = new sumas();
        $sum = new liberator($sum);

        $alias = '';
        $campo = '';
        $columnas = '';
        $resultado = $sum->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $alias = '';
        $campo = 'x';
        $columnas = '';
        $resultado = $sum->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $alias no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $alias = 'x';
        $campo = 'x';
        $columnas = '';
        $resultado = $sum->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('IFNULL( SUM(x) ,0)AS x', $resultado->column);

        errores::$error = false;

        $alias = 'x';
        $campo = 'x';
        $columnas = 'x';
        $resultado = $sum->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' , ', $resultado->coma);

        errores::$error = false;
    }




}