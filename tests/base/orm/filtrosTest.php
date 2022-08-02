<?php
namespace tests\base\orm;

use base\orm\filtros;

use gamboamartin\errores\errores;

use gamboamartin\test\test;
use models\adm_seccion;
use stdClass;


class filtrosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_complemento_sql(){
        errores::$error = false;
        $filtros = new filtros();
        //$inicializacion = new liberator($inicializacion);

        $modelo = new adm_seccion($this->link);
        $aplica_seguridad = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array();
        $offset = 0;
        $order = array();
        $sql_extra = '';
        $tipo_filtro = '';
        $resultado = $filtros->complemento_sql($aplica_seguridad, $filtro, $filtro_especial, $filtro_extra,
            $filtro_rango, $group_by, $limit, $modelo, $not_in, $offset, $order, $sql_extra, $tipo_filtro);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;


    }

    public function test_consulta_full_and(){
        errores::$error = false;
        $filtros = new filtros();
        //$inicializacion = new liberator($inicializacion);

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = '';
        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error $consulta no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = 'a';

        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a            ',$resultado);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = 'a';
        $complemento->sql_extra = 'b';

        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a       b     ',$resultado);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = 'a';
        $complemento->sql_extra = 'b';
        $complemento->filtro_fecha = 'c';

        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a     c  b     ',$resultado);
        errores::$error = false;
    }



}