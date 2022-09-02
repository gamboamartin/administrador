<?php
namespace tests\base;

use base\controller\normalizacion;
use base\orm\activaciones;
use base\orm\atributos;
use base\orm\rows;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\adm_accion_grupo;
use models\adm_campo;
use models\adm_dia;
use models\atributo;


class rowsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_filtro_hijo(){
        errores::$error = false;
        $rows = new rows();
        $rows = new liberator($rows);
        $filtro = array();
        $row = array();
        $resultado = $rows->filtro_hijo('','',$filtro,$row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;

        $resultado = $rows->filtro_hijo('x','',$filtro,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $rows->filtro_hijo('x','x',$filtro,$row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado['x']);

        errores::$error = false;
    }

    public function test_filtro_para_hijo(){

        errores::$error = false;
        $rows = new rows();
        $rows = new liberator($rows);

        $filtros = array();
        $row = array();
        $resultado = $rows->filtro_para_hijo($filtros,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        $filtros[] = '';
        $resultado = $rows->filtro_para_hijo($filtros,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);



        errores::$error = false;
        $filtros = array();
        $filtros['x'] = 'x';
        $resultado = $rows->filtro_para_hijo($filtros,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertArrayHasKey('x',$resultado);

    }

    public function test_obten_filtro_para_hijo(){

        errores::$error = false;
        $rows = new rows();
        //$mb = new liberator($mb);

        $data_modelo = array();
        $row = array();
        $resultado = $rows->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);


        errores::$error = false;

        $data_modelo['filtros_con_valor'] = '';
        $data_modelo['filtros'] = '';

        $resultado = $rows->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);


        errores::$error = false;

        $data_modelo['filtros_con_valor'] = array();
        $data_modelo['filtros'] = array();

        $resultado = $rows->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado);

        errores::$error = false;

        $data_modelo['filtros'][] = '';
        $resultado = $rows->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['data']['mensaje']);

        errores::$error = false;

        $data_modelo['filtros'][0] = 'x';
        $resultado = $rows->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertArrayHasKey(0, $resultado);



    }




}