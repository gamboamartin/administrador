<?php
namespace tests\base\orm;

use base\orm\modelo_base;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\accion;
use models\seccion;


class modelo_baseTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }



    public function test_asigna_registros_hijo(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $name_modelo = '';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,
            nombre_estructura: $nombre_estructura,row:  $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar entrada para modelo', $resultado['mensaje']);

        errores::$error = false;
        $name_modelo = 'x';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,
            nombre_estructura: $nombre_estructura,row:  $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar entrada para modelo', $resultado['mensaje']);
        $this->assertStringContainsStringIgnoringCase('Error modelo', $resultado['data']['mensaje']);

        errores::$error = false;
        $name_modelo = 'pais';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,
            nombre_estructura: $nombre_estructura,row:  $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar entrada para modelo', $resultado['mensaje']);



        errores::$error = false;
        $name_modelo = 'seccion';
        $filtro = array();
        $row = array();
        $nombre_estructura = 'seccion';

        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,
            nombre_estructura: $nombre_estructura,row:  $row);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['seccion'][0]['seccion_id']);

        errores::$error = false;


    }




    public function test_ejecuta_sql()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->ejecuta_sql('');
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error consulta vacia', $resultado['mensaje']);

        errores::$error = false;


        $resultado = $mb->ejecuta_sql('a');
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql', $resultado['mensaje']);

        errores::$error = false;
        $consulta = 'SELECT *FROM seccion';

        $resultado = $mb->ejecuta_sql($consulta);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_filtro_hijo(){
        errores::$error = false;
        $modelo_base = new modelo_base($this->link);
        $modelo_base = new liberator($modelo_base);
        $filtro = array();
        $row = array();
        $resultado = $modelo_base->filtro_hijo('','',$filtro,$row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;

        $resultado = $modelo_base->filtro_hijo('x','',$filtro,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $modelo_base->filtro_hijo('x','x',$filtro,$row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado['x']);

        errores::$error = false;
    }

    public function test_filtro_para_hijo(){

        errores::$error = false;
        $modelo_base = new modelo_base($this->link);
        $modelo_base = new liberator($modelo_base);

        $filtros = array();
        $row = array();
        $resultado = $modelo_base->filtro_para_hijo($filtros,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        $filtros[] = '';
        $resultado = $modelo_base->filtro_para_hijo($filtros,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);



        errores::$error = false;
        $filtros = array();
        $filtros['x'] = 'x';
        $resultado = $modelo_base->filtro_para_hijo($filtros,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertArrayHasKey('x',$resultado);

    }

    public function test_genera_consulta_base(){

        errores::$error = false;
        $modelo = new accion($this->link);
        $modelo = new liberator($modelo);
        $columnas = array('accion_id');
        $resultado = $modelo->genera_consulta_base($columnas);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT accion.id AS accion_id   FROM accion AS accion LEFT JOIN seccion AS seccion ON seccion.id = accion.seccion_id LEFT JOIN menu AS menu ON menu.id = seccion.menu_id', $resultado);
        errores::$error = false;
    }

    public function test_genera_modelo()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);

        $modelo = '';
        $resultado = $mb->genera_modelo($modelo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelo = 'a';
        $resultado = $mb->genera_modelo($modelo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelo = 'seccion';
        $resultado = $mb->genera_modelo($modelo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

    }

    public function test_genera_modelos_hijos()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->genera_modelos_hijos();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;

    }

    public function test_genera_registro_hijo(){

        errores::$error = false;
        $data_modelo = array();
        $row = array();

        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('nombre_estructura', $resultado['mensaje']);


        errores::$error = false;


        $data_modelo['nombre_estructura'] = '';
        $data_modelo['filtros'] = '';
        $data_modelo['filtros_con_valor'] = '';
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);

        errores::$error = false;
        $data_modelo['nombre_estructura'] = '';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar registros de hijo', $resultado['mensaje']);


        errores::$error = false;
        $data_modelo['nombre_estructura'] = 'x';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: 'x',row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar registros de hijo', $resultado['mensaje']);


        errores::$error = false;
        $data_modelo['nombre_estructura'] = 'x';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: 'seccion',row: $row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['x'][0]['seccion_id']);
        errores::$error = false;

    }

    public function test_genera_registros_hijos(){

        errores::$error = false;

        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $modelos_hijos = array();
        $row = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = '';
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error en datos', $resultado['mensaje']);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('nombre_estructura', $resultado['mensaje']);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $modelos_hijos[0]['nombre_estructura'] = 'ne';
        $modelos_hijos[0]['filtros'] = 'ne';
        $modelos_hijos[0]['filtros_con_valor'] = 'ne';
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('generar registros de hijo', $resultado['mensaje']);

        errores::$error = false;
        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $modelos_hijos[0]['nombre_estructura'] = 'ne';
        $modelos_hijos[0]['filtros'] = array();
        $modelos_hijos[0]['filtros_con_valor'] = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('generar registros de hijo', $resultado['mensaje']);

        errores::$error = false;
        $modelos_hijos = array();
        $row = array();
        $modelos_hijos['seccion'] = array();
        $modelos_hijos['seccion']['nombre_estructura'] = 'ne';
        $modelos_hijos['seccion']['filtros'] = array();
        $modelos_hijos['seccion']['filtros_con_valor'] = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['ne'][0]['seccion_id']);
        errores::$error = false;


    }


    public function test_limpia_moneda_value(){

        errores::$error = false;
        $modelo = new accion($this->link);
        $modelo = new liberator($modelo);
        $value = '';
        $resultado = $modelo->limpia_moneda_value($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $value = '$$,,00';
        $resultado = $modelo->limpia_moneda_value($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('00', $resultado);
        errores::$error = false;
    }


    public function test_maqueta_arreglo_registros(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $r_sql =  $this->link->query(/** @lang text */ "SELECT *FROM seccion");
        $modelos_hijos = array();
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado[0]['id']);

        errores::$error = false;

    }

    public function test_obten_columnas(){

        errores::$error = false;
        $seccion = new seccion($this->link);
        $lib = (new liberator($seccion));

        $resultado = $lib->obten_columnas('seccion');

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('descripcion',$resultado->columnas_parseadas[1]);
        $this->assertCount(11, $resultado->columnas_parseadas);
        errores::$error = false;

    }

    public function test_obten_filtro_para_hijo(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $data_modelo = array();
        $row = array();
        $resultado = $mb->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);


        errores::$error = false;

        $data_modelo['filtros_con_valor'] = '';
        $data_modelo['filtros'] = '';

        $resultado = $mb->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);


        errores::$error = false;

        $data_modelo['filtros_con_valor'] = array();
        $data_modelo['filtros'] = array();

        $resultado = $mb->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado);

        errores::$error = false;

        $data_modelo['filtros'][] = '';
        $resultado = $mb->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['data']['mensaje']);

        errores::$error = false;

        $data_modelo['filtros'][0] = 'x';
        $resultado = $mb->obten_filtro_para_hijo($data_modelo,$row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertArrayHasKey(0, $resultado);



    }

    public function test_obten_nombre_tabla(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);
        $tabla_renombrada = '';
        $tabla_original = '';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no pueden venir vacios todos los parametros', $resultado['mensaje']);

        errores::$error = false;
        $tabla_renombrada = 'x';
        $tabla_original = '';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;
        $tabla_renombrada = 'x';
        $tabla_original = 'x';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;
        $tabla_renombrada = 'y';
        $tabla_original = 'x';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);
        errores::$error = false;
    }

    public function test_order_sql(){
        errores::$error = false;

        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);
        $resultado = $mb->order_sql(array());
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
        $resultado = $mb->order_sql(array('x'));
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo debe ser txt', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $mb->order_sql(array('x'=>'x'));
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('ORDER BY x x', $resultado);
        errores::$error = false;

    }

    public function test_parsea_registros_envio(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $r_sql = $this->link->query(/** @lang text */ 'SELECT *FROM seccion');
        $resultado = $mb->parsea_registros_envio($r_sql);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado[0]['id']);

        errores::$error = false;

    }

    public function test_str_replace_first(){
        errores::$error = false;
        $modelo = new modelo_base($this->link);
        $resultado = $modelo->str_replace_first('','','');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al content esta vacio', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $modelo->str_replace_first('','','x');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al content esta vacio', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $modelo->str_replace_first('x','x','x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty( $resultado);

        errores::$error = false;
        $resultado = $modelo->str_replace_first('x','x','x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);
        errores::$error = false;

    }








}