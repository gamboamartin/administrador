<?php
namespace tests\base\orm;

use base\orm\data_format;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;



class data_formatTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_ajusta_campos_moneda(){
        errores::$error = false;
        $df = new data_format();
        //$df = new liberator($df);

        $tipo_campos = array();
        $registro = array();

        $tipo_campos[] = 'z';
        $registro[] = '';
        $resultado = $df->ajusta_campos_moneda($registro, $tipo_campos);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_asigna_campo_moneda(){
        errores::$error = false;
        $df = new data_format();
        $df = new liberator($df);

        $campo = '';
        $registro = array();
        $resultado = $df->asigna_campo_moneda($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $registro = array();
        $resultado = $df->asigna_campo_moneda($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $registro[a] no existe',$resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $registro = array();
        $registro['a'] = 'a';
        $resultado = $df->asigna_campo_moneda($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['a']);

        errores::$error = false;

        $campo = 'a';
        $registro = array();
        $registro['a'] = '$1,000.90';
        $resultado = $df->asigna_campo_moneda($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1000.90',$resultado['a']);

        errores::$error = false;
    }
    public function test_asignacion_campo_moneda(){
        errores::$error = false;
        $df = new data_format();
        $df = new liberator($df);

        $campo = '';
        $registro = array();
        $tipo_dato = '';
        $resultado = $df->asignacion_campo_moneda($campo, $registro, $tipo_dato);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $campo = 'z';
        $registro = array();
        $tipo_dato = '';
        $resultado = $df->asignacion_campo_moneda($campo, $registro, $tipo_dato);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el tipo_dato esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $campo = 'z';
        $registro = array();
        $tipo_dato = 'z';
        $resultado = $df->asignacion_campo_moneda($campo, $registro, $tipo_dato);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $campo = 'z';
        $registro = array();
        $registro['z'] = '1,00.0';
        $tipo_dato = 'moneda';
        $resultado = $df->asignacion_campo_moneda($campo, $registro, $tipo_dato);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('100.0', $resultado['z']);
        errores::$error = false;
    }

}