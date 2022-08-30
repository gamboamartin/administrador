<?php
namespace tests\base\orm;

use base\orm\modelo_base;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\accion;
use models\adm_accion;
use models\seccion;
use stdClass;


class modelo_baseTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_agrega_usuario_session(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb->usuario_id = 2;
        $mb->campos_sql = 1;
        $mb = new liberator($mb);



        $resultado = $mb->agrega_usuario_session();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_ajusta_row_select(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $campos_encriptados = array('z');
        $modelos_hijos = array();
        $modelos_hijos['adm_dia']['nombre_estructura'] = 'adm_accion';
        $modelos_hijos['adm_dia']['filtros'] = array();
        $modelos_hijos['adm_dia']['filtros_con_valor'] = array();
        $row = array();
        $row['z'] = 'PHDA/NloYgF1lc+UHzxaUw==';
        $resultado = $mb->ajusta_row_select($campos_encriptados, $modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
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
        $name_modelo = 'adm_accion_grupo';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,
            nombre_estructura: $nombre_estructura,row:  $row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nombre estructura no puede venir vacia', $resultado['mensaje']);


        errores::$error = false;
        $name_modelo = 'pais';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,
            nombre_estructura: $nombre_estructura,row:  $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nombre estructura no puede venir vacia', $resultado['mensaje']);



        errores::$error = false;
        $name_modelo = 'adm_seccion';
        $filtro = array();
        $row = array();
        $nombre_estructura = 'adm_seccion';

        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,
            nombre_estructura: $nombre_estructura,row:  $row);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['adm_seccion'][0]['adm_seccion_id']);

        errores::$error = false;


    }

    public function test_codigo_alta(): void
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb->usuario_id = 2;
        $mb->campos_sql = 1;
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
        $mb = new modelo_base($this->link);
        $mb->usuario_id = 2;
        $mb->campos_sql = 1;
        $mb = new liberator($mb);



        $resultado = $mb->codigo_random();
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);

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
        $consulta = 'SELECT *FROM adm_seccion';

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

    public function test_genera_codigo(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
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

    public function test_genera_consulta_base(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $columnas = array('adm_accion_id');
        $resultado = $modelo->genera_consulta_base($columnas);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_accion.id AS adm_accion_id   FROM adm_accion AS adm_accion LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_accion.adm_seccion_id LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id', $resultado);
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
        $modelo = 'adm_accion';
        $resultado = $mb->genera_modelo($modelo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
        $modelo = 'adm_seccion';
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
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos', $resultado['mensaje']);


        errores::$error = false;
        $data_modelo['nombre_estructura'] = 'x';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: 'adm_seccion',row: $row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['x'][0]['adm_seccion_id']);
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
        $modelos_hijos['adm_seccion'] = array();
        $modelos_hijos['adm_seccion']['nombre_estructura'] = 'ne';
        $modelos_hijos['adm_seccion']['filtros'] = array();
        $modelos_hijos['adm_seccion']['filtros_con_valor'] = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['ne'][0]['adm_seccion_id']);
        errores::$error = false;


    }


    public function test_maqueta_arreglo_registros(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $r_sql =  $this->link->query(/** @lang text */ "SELECT *FROM adm_seccion");
        $modelos_hijos = array();
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado[0]['id']);

        errores::$error = false;

        $r_sql =  $this->link->query(/** @lang text */ "SELECT *FROM adm_seccion");
        $modelos_hijos = array();
        $campos_encriptados = array('descripcion');
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ajustar rows', $resultado['mensaje']);

        errores::$error = false;

        $vacio = (new encriptador())->encripta('');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al encriptar vacio', data: $vacio);
            print_r($error);exit;
        }


        $r_sql =  $this->link->query(/** @lang text */ "SELECT '$vacio' as descripcion FROM adm_seccion");
        $modelos_hijos = array();
        $campos_encriptados = array('descripcion');
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado[0]['descripcion']);

        errores::$error = false;

        $descripcion = (new encriptador())->encripta('esto es una descripcion');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al encriptar vacio', data: $vacio);
            print_r($error);exit;
        }


        $r_sql =  $this->link->query(/** @lang text */ "SELECT '$descripcion' as descripcion FROM adm_seccion");
        $modelos_hijos = array();
        $campos_encriptados = array('descripcion');
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('esto es una descripcion', $resultado[0]['descripcion']);


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



    public function test_parsea_registros_envio(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $r_sql = $this->link->query(/** @lang text */ 'SELECT *FROM adm_seccion');
        $resultado = $mb->parsea_registros_envio($r_sql);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado[0]['id']);

        errores::$error = false;

    }

    public function test_registro_por_id()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        //$mb = new liberator($mb);


        $id = 1;
        $entidad = new adm_accion($this->link);
        $resultado = $mb->registro_por_id($entidad, $id);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado->adm_accion_id);
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

    public function test_usuario_existente(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $mb->usuario_id = 100;
        $resultado = $mb->usuario_existente();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_valida_codigo_aut(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $keys_registro = array();
        $key = '';
        $registro = array();
        $resultado = $mb->valida_codigo_aut($key, $keys_registro, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar key', $resultado['mensaje']);

        errores::$error = false;

        $keys_registro = array();
        $key = 'a';
        $registro = array();
        $resultado = $mb->valida_codigo_aut($key, $keys_registro, $registro);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_valida_key_vacio(): void
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb->usuario_id = 2;
        $mb->campos_sql = 1;
        $mb = new liberator($mb);
        $key = 'a';
        $resultado = $mb->valida_key_vacio($key);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_valida_registro_modelo()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);


        $registro = array();
        $modelo = new adm_accion($this->link);
        $resultado = $mb->valida_registro_modelo($modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['adm_accion_id'] = -1;
        $modelo = new adm_accion($this->link);
        $resultado = $mb->valida_registro_modelo($modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['adm_accion_id'] = 1;
        $modelo = new adm_accion($this->link);
        $resultado = $mb->valida_registro_modelo($modelo, $registro);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }







}