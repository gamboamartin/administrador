<?php
namespace tests\base\orm;

use base\orm\modelo_base;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_dia;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class modelo_baseTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }


    public function test_aplica_desactivacion_dependencias(): void
    {


        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->aplica_desactivacion_dependencias();

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_asigna_codigo(): void
    {


        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $mb = new modelo_base($this->link);
        $mb->usuario_id = 2;
        $mb->campos_sql = 1;
        $mb = new liberator($mb);
        $keys_registro = array();
        $keys_row = array();
        $modelo = new adm_dia($this->link);

        $r = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $r);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;

        $r = $modelo->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $r);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['adm_dia_id'] = 1;
        $resultado = $mb->asigna_codigo($keys_registro, $keys_row, $modelo, $registro);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_asigna_descripcion()
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;

        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);
        $modelo = new adm_mes($this->link);

        $del = (new adm_mes($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $mes_ins = array();
        $mes_ins['id'] = 1;
        $mes_ins['codigo'] = 1;
        $mes_ins['descripcion'] = 1;
        $alta = (new adm_mes($this->link))->alta_registro($mes_ins);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al dar de alta', data: $alta);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['adm_mes_id'] = 1;
        $resultado = $mb->asigna_descripcion($modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['adm_mes_id']);
        $this->assertEquals(1,$resultado['descripcion']);
        errores::$error = false;
    }



    public function test_campos_base(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);
        $modelo = new adm_seccion(link: $this->link);
        $data = array();
        $data['descripcion'] = 'z';
        $resultado = $mb->campos_base($data, $modelo);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z', $resultado['codigo']);
        $this->assertEquals('z', $resultado['descripcion']);
        $this->assertEquals('z', $resultado['codigo_bis']);
        $this->assertEquals('z Z', $resultado['descripcion_select']);
        $this->assertEquals('z', $resultado['alias']);
        errores::$error = false;
    }

    public function test_columnas_data(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $columnas_extra_sql = '';
        $columnas_sql = '';
        $sub_querys_sql = '';
        $resultado = $mb->columnas_data($columnas_extra_sql, $columnas_sql, $sub_querys_sql);
        //PRINT_R($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->columnas_sql);
        $this->assertEquals('', $resultado->sub_querys_sql);
        $this->assertEquals('', $resultado->columnas_extra_sql);

        errores::$error = false;

        $columnas_extra_sql = '';
        $columnas_sql = 'a';
        $sub_querys_sql = '';
        $resultado = $mb->columnas_data($columnas_extra_sql, $columnas_sql, $sub_querys_sql);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->columnas_sql);
        $this->assertEquals('', $resultado->sub_querys_sql);
        $this->assertEquals('', $resultado->columnas_extra_sql);
        errores::$error = false;
    }

    public function test_columns_final(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $column_data = '';
        $columns_final = '';

        $resultado = $mb->columns_final($column_data, $columns_final);
        //print_r($resultado);exit;
        //PRINT_R($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;

        $column_data = 'a';
        $columns_final = '';

        $resultado = $mb->columns_final($column_data, $columns_final);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);
        errores::$error = false;

        $column_data = 'a';
        $columns_final = 'v';

        $resultado = $mb->columns_final($column_data, $columns_final);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('v,a', $resultado);
        errores::$error = false;


    }

    public function test_data_base(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $data = array();
        $data['codigo'] = 'a';
        $data['descripcion'] = 'b';
        $resultado = $mb->data_base($data);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['codigo']);
        $this->assertEquals('b', $resultado['descripcion']);
        $this->assertEquals('a B', $resultado['descripcion_select']);
        $this->assertEquals('a', $resultado['alias']);
        errores::$error = false;
    }



    public function test_ds_init()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $data = array();
        $key = 'codigo   ';
        $data['codigo'] = '   xx hgfs';

        $resultado = $mb->ds_init($data, $key);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('xx hgfs', $resultado);
        errores::$error = false;

    }

    public function test_ejecuta_consulta()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        //$mb = new liberator($mb);


        $consulta = 'SELECT 1 AS a FROM adm_seccion';

        $resultado = $mb->ejecuta_consulta($consulta);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado->registros_obj[0]->a);
        errores::$error = false;
    }

    public function test_extra_columns()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);


        $columnas = array();
        $columnas_seleccionables = array();
        $columnas_sql = '';
        $con_sq = true;

        $resultado = $mb->extra_columns($columnas, $columnas_seleccionables, $columnas_sql, $con_sq);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->sub_querys_sql);
        $this->assertEquals('', $resultado->columnas_extra_sql);
        errores::$error = false;


        $mb = new adm_accion($this->link);
        $mb->columnas_extra['a'] = 'a';
        $mb = new liberator($mb);

        $columnas = array();
        $columnas_seleccionables = array();
        $columnas_sql = '';
        $con_sq = true;

        $resultado = $mb->extra_columns($columnas, $columnas_seleccionables, $columnas_sql, $con_sq);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->sub_querys_sql);
        $this->assertEquals('(SELECT COUNT(*) FROM adm_accion_grupo WHERE adm_accion_grupo.adm_accion_id = adm_accion.id) AS adm_accion_n_permisos,a AS a', $resultado->columnas_extra_sql);
        errores::$error = false;

    }

    public function test_ds_init_no_codigo()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $data = array();
        $key = 'a';
        $data['a'] = 'x__hJlxIUJ';
        $resultado = $mb->ds_init_no_codigo($data, $key);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('X  HJlxIUJ', $resultado);
        errores::$error = false;
    }

    public function test_descripcion_alta()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $_SESSION['usuario_id'] = 2;
        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_campo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_bitacora($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }



        $registro['id'] = 1;
        $registro['descripcion'] = 'adm_seccion';
        $registro['adm_menu_id'] = '1';
        $registro['adm_namespace_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['titulo'] = 'test';
        $adm_accion['adm_seccion_id'] = '1';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $modelo = new adm_accion($this->link);
        $registro = array();
        $registro['adm_accion_id'] = 1;
        $resultado = $mb->descripcion_alta($modelo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('test', $resultado);
        errores::$error = false;
    }

    public function test_descripcion_select()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $_SESSION['usuario_id'] = 2;


        $data = array();
        $keys_integra_ds = array();
        $keys_integra_ds[] = 'x';

        $data['x'] = 'z';

        $resultado = $mb->descripcion_select($data, $keys_integra_ds);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Z',$resultado);

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

    public function test_es_namespace_especial()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $modelo = 'a';
        $namespaces = array();
        $namespaces[] = 'b';

        $resultado = $mb->es_namespace_especial($modelo, $namespaces);
        //print_r($resultado);exit;
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertFalse($resultado);

        errores::$error = false;

        $modelo = 'ba';
        $namespaces = array();
        $namespaces[] = 'b';

        $resultado = $mb->es_namespace_especial($modelo, $namespaces);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }

    public function test_genera_columns_final(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $columns_data = new stdClass();
        $columns_data->a = 'a';
        $resultado = $modelo->genera_columns_final($columns_data);
        //print_r($resultado);exit;
        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        $this->assertEquals('a', $resultado);
        errores::$error = false;
    }

    public function test_genera_consulta_base(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $columnas = array('adm_accion_id');
        $resultado = $modelo->genera_consulta_base($columnas);
        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        //$this->assertEquals('SELECT adm_accion.id AS adm_accion_id   FROM adm_accion AS adm_accion LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_accion.adm_seccion_id LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id', $resultado);
        $this->assertEquals('SELECT adm_accion.id AS adm_accion_id FROM adm_accion AS adm_accion LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_accion.adm_seccion_id LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id', $resultado);
        errores::$error = false;
    }

    public function test_genera_descripcion(){


        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $modelo = new adm_accion($this->link);

        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_campo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_bitacora($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = '1';
        $adm_seccion['adm_namespace_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['titulo'] = 'test';
        $adm_accion['adm_seccion_id'] = '1';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $registro = array();
        $registro['adm_accion_id'] = 1;
        $resultado = $mb->genera_descripcion($modelo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('test', $resultado);
        errores::$error = false;
    }

    public function test_genera_modelo()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);

        $modelo = '';
        $resultado = $mb->genera_modelo($modelo);
        //print_r($resultado);exit;
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al maquetar name modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelo = 'adm_accion';
        $resultado = $mb->genera_modelo($modelo,'gamboamartin\\administrador\\models');
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
        $modelo = 'adm_seccion';
        $resultado = $mb->genera_modelo($modelo,'gamboamartin\\administrador\\models');
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

    }



    public function test_genera_name_modelo()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $modelo = 'a';
        $namespace_model= '';
        $resultado = $mb->genera_name_modelo($modelo, $namespace_model);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('models\a', $resultado);

        errores::$error = false;

    }


    public function test_integra_ds()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $data = array();
        $key = 'a';
        $ds = '';
        $data['a'] = 'x';
        $resultado = $mb->integra_ds($data, $ds, $key);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('X ', $resultado);
        errores::$error = false;
    }

    public function test_integra_columns_final()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $columnas = array();
        $columnas[] = '';
        $columnas_seleccionables = array();
        $columnas_seleccionables[] = '';
        $columnas_sql = 'a';
        $con_sq = false;
        $count = true;
        $resultado = $mb->integra_columns_final($columnas, $columnas_seleccionables, $columnas_sql, $con_sq, $count);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('COUNT(*) AS total_registros', $resultado);
        errores::$error = false;
    }

    public function test_key_tmp()
    {


        errores::$error = false;
        $mb = new adm_seccion($this->link);
        $mb = new liberator($mb);

        $consulta = 'SELECTcatsatisridAScatsatisridcatsatisrdescripcionAScatsatisrdescripcioncatsatisrcodigoAScatsatisrcodigocatsatisrstatusAScatsatisrstatuscatsatisrusuarioaltaidAScatsatisrusuarioaltaidcatsatisrusuarioupdateidAScatsatisrusuarioupdateidcatsatisrfechaaltaAScatsatisrfechaaltacatsatisrfechaupdateAScatsatisrfechaupdatecatsatisrdescripcionselectAScatsatisrdescripcionselectcatsatisraliasAScatsatisraliascatsatisrcodigobisAScatsatisrcodigobiscatsatisrlimiteinferiorAScatsatisrlimiteinferiorcatsatisrlimitesuperiorAScatsatisrlimitesuperiorcatsatisrcuotafijaAScatsatisrcuotafijacatsatisrporcentajeexcedenteAScatsatisrporcentajeexcedentecatsatisrcatsatperiodicidadpagonomidAScatsatisrcatsatperiodicidadpagonomidcatsatisrfechainicioAScatsatisrfechainiciocatsatisrfechafinAScatsatisrfechafincatsatperiodicidadpagonomidAScatsatperiodicidadpagonomidcatsatperiodicidadpagonomdescripcionAScatsatperiodicidadpagonomdescripcioncatsatperiodicidadpagonomcodigoAScatsatperiodicidadpagonomcodigocatsatperiodicidadpagonomstatusAScatsatperiodicidadpagonomstatuscatsatperiodicidadpagonomusuarioaltaidAScatsatperiodicidadpagonomusuarioaltaidcatsatperiodicidadpagonomusuarioupdateidAScatsatperiodicidadpagonomusuarioupdateidcatsatperiodicidadpagonomfechaaltaAScatsatperiodicidadpagonomfechaaltacatsatperiodicidadpagonomfechaupdateAScatsatperiodicidadpagonomfechaupdatecatsatperiodicidadpagonomdescripcionselectAScatsatperiodicidadpagonomdescripcionselectcatsatperiodicidadpagonomaliasAScatsatperiodicidadpagonomaliascatsatperiodicidadpagonomcodigobisAScatsatperiodicidadpagonomcodigobiscatsatperiodicidadpagonomndiasAScatsatperiodicidadpagonomndiasFROMcatsatisrAScatsatisrLEFTJOINcatsatperiodicidadpagonomAScatsatperiodicidadpagonomONcatsatperiodicidadpagonomid=catsatisrcatsatperiodicidadpagonomidWHEREcatsatisrid=1';

        $resultado = $mb->key_tmp($consulta);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('75d700445ebf129b3195e1af7d6df67e', $resultado);
    }





    public function test_name_modelo()
    {


        errores::$error = false;
        $mb = new adm_seccion($this->link);
        $mb = new liberator($mb);

        $es_namespace_especial = false;
        $modelo = 'a';
        $namespace_model = '';
        $resultado = $mb->name_modelo($es_namespace_especial, $modelo, $namespace_model);
        //print_r($resultado);exit;

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("models\a", $resultado);

        errores::$error = false;

        $es_namespace_especial = false;
        $modelo = 'a';
        $namespace_model = 'f';
        $resultado = $mb->name_modelo($es_namespace_especial, $modelo, $namespace_model);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("f\a", $resultado);

        errores::$error = false;

        $es_namespace_especial = true;
        $modelo = 'a';
        $namespace_model = '';
        $resultado = $mb->name_modelo($es_namespace_especial, $modelo, $namespace_model);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado);

        errores::$error = false;
    }
    public function test_name_modelo_ajustado()
    {


        errores::$error = false;
        $mb = new adm_seccion($this->link);
        $mb = new liberator($mb);

        $modelo = 'v';
        $namespace_model = 'a';
        $resultado = $mb->name_modelo_ajustado($modelo, $namespace_model);
        //print_r($resultado);exit;

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a\\v", $resultado);

        errores::$error = false;


        $modelo = 'v';
        $namespace_model = 'models\\a';
        $resultado = $mb->name_modelo_ajustado($modelo, $namespace_model);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("models\a\\v", $resultado);
        errores::$error = false;
    }

    public function test_name_modelo_base()
    {


        errores::$error = false;
        $mb = new adm_seccion($this->link);
        $mb = new liberator($mb);

        $modelo = 'a';
        $resultado = $mb->name_modelo_base($modelo);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("models\a", $resultado);

        errores::$error = false;
    }

    public function test_namespaces()
    {


        errores::$error = false;
        $mb = new adm_seccion($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->namespaces();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("gamboamartin\\administrador\models\\", $resultado[0]);
        $this->assertEquals("gamboamartin\\empleado\models\\", $resultado[1]);
        $this->assertEquals("gamboamartin\\facturacion\models\\", $resultado[2]);
        $this->assertEquals("gamboamartin\organigrama\models\\", $resultado[3]);
        $this->assertEquals("gamboamartin\direccion_postal\models\\", $resultado[4]);
        $this->assertEquals("gamboamartin\cat_sat\models\\", $resultado[5]);
        $this->assertEquals("gamboamartin\comercial\models\\", $resultado[6]);


        errores::$error = false;
    }

    public function test_obten_nombre_tabla(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        //$mb = new liberator($mb);
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



    public function test_registro_descripcion_select()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $data = array();
        $keys_integra_ds = array();
        $keys_integra_ds['a'] = 'a';
        $data['a'] = 'x';
        $resultado = $mb->registro_descripcion_select($data, $keys_integra_ds);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('X', $resultado['descripcion_select']);
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