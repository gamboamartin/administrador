<?php
namespace tests\src;


use base\orm\_result;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _resultTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_ajusta_row_select(){


        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $modelo_base = new adm_accion(link: $this->link);
        $campos_encriptados = array('z');
        $modelos_hijos = array();
        $modelos_hijos['adm_dia']['nombre_estructura'] = 'adm_accion';
        $modelos_hijos['adm_dia']['namespace_model'] = 'gamboamartin\\administrador\\models';
        $modelos_hijos['adm_dia']['filtros'] = array();
        $modelos_hijos['adm_dia']['filtros_con_valor'] = array();
        $row = array();
        $row['z'] = 'PHDA/NloYgF1lc+UHzxaUw==';
        $resultado = $mb->ajusta_row_select(campos_encriptados: $campos_encriptados,modelos_hijos:  $modelos_hijos,
            row:  $row,modelo_base: $modelo_base);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_asigna_registros_hijo(){


        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $modelo_base = new adm_seccion(link: $this->link);

        $name_modelo = '';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $namespace_model = 'gamboamartin\\administrador\\models';
        $resultado = $mb->asigna_registros_hijo(filtro: $filtro, name_modelo: $name_modelo,
            namespace_model: $namespace_model, nombre_estructura: $nombre_estructura, row: $row,modelo_base: $modelo_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar entrada para modelo', $resultado['mensaje']);

        errores::$error = false;
        $name_modelo = 'adm_accion_grupo';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo, namespace_model: $namespace_model,
            nombre_estructura: $nombre_estructura,row:  $row,modelo_base: $modelo_base);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nombre estructura no puede venir vacia', $resultado['mensaje']);


        errores::$error = false;
        $name_modelo = 'pais';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,namespace_model: $namespace_model,
            nombre_estructura: $nombre_estructura,row:  $row,modelo_base: $modelo_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nombre estructura no puede venir vacia', $resultado['mensaje']);



        errores::$error = false;
        $name_modelo = 'adm_seccion';
        $filtro = array();
        $row = array();
        $nombre_estructura = 'adm_seccion';

        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,namespace_model: $namespace_model,
            nombre_estructura: $nombre_estructura,row:  $row,modelo_base: $modelo_base);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric('1', $resultado['adm_seccion'][0]['adm_seccion_id']);

        errores::$error = false;


    }

    public function test_data_result()
    {


        errores::$error = false;
        $mb = new _result();
        //$mb = new liberator($mb);

        $modelo = new adm_seccion(link: $this->link);

        $campos_encriptados = array();
        $consulta = 'SELECT 1 as a FROM adm_seccion';

        $resultado = $mb->data_result(campos_encriptados: $campos_encriptados,columnas_totales:  array(),consulta:  $consulta,modelo: $modelo);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->n_registros);
        errores::$error = false;

    }

    public function test_genera_modelos_hijos()
    {
        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $modelo = new adm_seccion(link: $this->link);
        $resultado = $mb->genera_modelos_hijos(modelo: $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;

    }

    public function test_genera_registro_hijo(){

        errores::$error = false;
        $data_modelo = array();
        $row = array();

        $mb = new _result();
        $mb = new liberator($mb);

        $modelo_base= new adm_seccion(link: $this->link);

        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row,modelo_base: $modelo_base);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);


        errores::$error = false;


        $data_modelo['nombre_estructura'] = '';
        $data_modelo['filtros'] = '';
        $data_modelo['filtros_con_valor'] = '';
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row,modelo_base: $modelo_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;
        $data_modelo['nombre_estructura'] = '';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row,modelo_base: $modelo_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);


        errores::$error = false;
        $data_modelo['nombre_estructura'] = 'x';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: 'x',row: $row,modelo_base: $modelo_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);


        errores::$error = false;
        $data_modelo['nombre_estructura'] = 'x';
        $data_modelo['namespace_model'] = 'gamboamartin\\administrador\\models';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: 'adm_seccion',row: $row,modelo_base: $modelo_base);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric('1', $resultado['x'][0]['adm_seccion_id']);
        errores::$error = false;

    }

    public function test_genera_registros_hijos(){

        errores::$error = false;

        $mb = new _result();
        $mb = new liberator($mb);

        $modelos_base = new adm_seccion(link: $this->link);

        $modelos_hijos = array();
        $row = array();
        $resultado = $mb->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row,modelo_base: $modelos_base);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = '';
        $resultado = $mb->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row,modelo_base: $modelos_base);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error en datos', $resultado['mensaje']);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $resultado = $mb->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row,modelo_base: $modelos_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $modelos_hijos[0]['nombre_estructura'] = 'ne';
        $modelos_hijos[0]['filtros'] = 'ne';
        $modelos_hijos[0]['filtros_con_valor'] = 'ne';
        $resultado = $mb->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row,modelo_base: $modelos_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $modelos_hijos[0]['nombre_estructura'] = 'ne';
        $modelos_hijos[0]['filtros'] = array();
        $modelos_hijos[0]['filtros_con_valor'] = array();
        $resultado = $mb->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row,modelo_base: $modelos_base);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelos_hijos = array();
        $row = array();
        $modelos_hijos['adm_seccion'] = array();
        $modelos_hijos['adm_seccion']['nombre_estructura'] = 'ne';
        $modelos_hijos['adm_seccion']['namespace_model'] = 'gamboamartin\\administrador\\models';
        $modelos_hijos['adm_seccion']['filtros'] = array();
        $modelos_hijos['adm_seccion']['filtros_con_valor'] = array();
        $resultado = $mb->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row,modelo_base: $modelos_base);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric('1', $resultado['ne'][0]['adm_seccion_id']);
        errores::$error = false;


    }

    public function test_init_result_base()
    {

        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $modelo = new adm_seccion(link: $this->link);

        $consulta = '';
        $n_registros = '-1';
        $new_array = array();

        $resultado = $mb->init_result_base(consulta: $consulta, modelo: $modelo, n_registros: $n_registros, new_array: $new_array, totales_rs: new stdClass());


        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->sql);
        errores::$error = false;
    }



    public function test_maqueta_arreglo_registros(){

        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $modelo_base = new adm_seccion(link: $this->link);

        $r_sql =  $this->link->query(/** @lang text */ "SELECT *FROM adm_seccion");
        $modelos_hijos = array();
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,modelo_base: $modelo_base,campos_encriptados: array());

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric('1', $resultado[0]['id']);

        errores::$error = false;

        $r_sql =  $this->link->query(/** @lang text */ "SELECT *FROM adm_seccion");
        $modelos_hijos = array();
        $campos_encriptados = array('descripcion');
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados,modelo_base: $modelo_base);
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
            campos_encriptados: $campos_encriptados,modelo_base: $modelo_base);
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
            campos_encriptados: $campos_encriptados, modelo_base: $modelo_base);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('esto es una descripcion', $resultado[0]['descripcion']);


        errores::$error = false;

    }

    public function test_maqueta_result()
    {


        errores::$error = false;
        $mb = new _result($this->link);
        $mb = new liberator($mb);

        $modelo = new adm_seccion(link: $this->link);

        $consulta = '';
        $n_registros = '-1';
        $new_array = array();

        $resultado = $mb->maqueta_result(consulta: $consulta,n_registros:  $n_registros,new_array:  $new_array,totales_rs:  new stdClass(),modelo: $modelo);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->sql);

        errores::$error = false;
    }

    public function test_parsea_registros_envio(){

        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $modelo_base = new adm_seccion(link: $this->link);

        $r_sql = $this->link->query(/** @lang text */ 'SELECT *FROM adm_seccion');
        $resultado = $mb->parsea_registros_envio(r_sql: $r_sql,modelo_base: $modelo_base,campos_encriptados: array());
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric(1, $resultado[0]['id']);

        errores::$error = false;

    }

    public function test_result()
    {


        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $modelo = new adm_seccion(link: $this->link);

        $consulta = '';
        $n_registros = -1;
        $new_array = array();
        $resultado = $mb->result(consulta: $consulta,n_registros:  $n_registros,new_array:  $new_array,totales_rs:  new stdClass(),modelo: $modelo);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('-1', $resultado->n_registros);
        errores::$error = false;

    }

    public function test_result_sql()
    {


        errores::$error = false;
        $mb = new _result();
        $mb = new liberator($mb);

        $_SESSION['usuario_id'] = 2;

        $modelo= new adm_seccion(link: $this->link);

        $campos_encriptados = array();
        $consulta = 'SELECT adm_grupo.id FROM adm_grupo';
        $resultado = $mb->result_sql(campos_encriptados: $campos_encriptados,columnas_totales:  array(),consulta:  $consulta,modelo: $modelo);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_total_rs_campo(){
        errores::$error = false;
        $rs = new _result();
        $rs = new liberator($rs);

        $campo = 'z';
        $new_array = array();
        $totales_rs = new stdClass();
        $resultado = $rs->total_rs_campo(campo: $campo,new_array:  $new_array,totales_rs:  $totales_rs);
        // print_r($resultado);exit;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0, $resultado->z);

        errores::$error = false;

        $campo = 'z';
        $new_array = array();
        $totales_rs = new stdClass();
        $new_array[0]['z'] = 10;
        $new_array[1]['z'] = 1;
        $new_array[2]['z'] = 10.55;
        $resultado = $rs->total_rs_campo($campo, $new_array, $totales_rs);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(21.55, $resultado->z);
        errores::$error = false;

    }

    public function test_totales_rs(){
        errores::$error = false;
        $modelo = new _result();
        $modelo = new liberator($modelo);


        $new_array = array();
        $new_array[]['a'] = '1';
        $new_array[]['a'] = '2';
        $columnas_totales = array();
        $columnas_totales[] = 'a';
        $resultado = $modelo->totales_rs($columnas_totales, $new_array);
        //print_r($resultado);exit;
        // print_r($resultado);exit;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(3, $resultado->a);

        errores::$error = false;


    }

    public function test_totales_rs_acumula(){
        errores::$error = false;
        $modelo = new _result($this->link);
        $modelo = new liberator($modelo);

        $campo = 'a';
        $new_array = array();
        $new_array[]['a'] = '1';
        $new_array[]['a'] = '5';
        $totales_rs = new stdClass();
        $resultado = $modelo->totales_rs_acumula($campo, $new_array, $totales_rs);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(6, $resultado->a);

        errores::$error = false;


    }

    public function test_valida_totales()
    {


        errores::$error = false;
        $mb = new _result($this->link);
        $mb = new liberator($mb);


        $row = array();
        $campo = '';
        $resultado = $mb->valida_totales($campo, $row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error campo esta vacio', $resultado['mensaje_limpio']);

        errores::$error = false;

        $row = array();
        $campo = 'a';
        $resultado = $mb->valida_totales($campo, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error row[a] NO EXISTE', $resultado['mensaje_limpio']);

        errores::$error = false;

        $row = array();
        $row['a'] = '';
        $campo = 'a';
        $resultado = $mb->valida_totales($campo, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error row[a] no es un numero valido', $resultado['mensaje_limpio']);

        errores::$error = false;

        $row = array();
        $row['a'] = '11';
        $campo = 'a';
        $resultado = $mb->valida_totales($campo, $row);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }


}