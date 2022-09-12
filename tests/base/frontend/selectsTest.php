<?php
namespace tests\base\frontend;

use base\frontend\selects;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class selectsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_columnas_base_select(): void
    {
        errores::$error = false;
        $val = new selects();
        $val = new liberator($val);

        $tabla = '';
        $resultado = $val->columnas_base_select($tabla);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $tabla = 'a';
        $resultado = $val->columnas_base_select($tabla);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_id', $resultado[0]);
        $this->assertEquals('a_codigo', $resultado[1]);
        $this->assertEquals('a_descripcion', $resultado[2]);



        errores::$error = false;


    }

    public function test_columnas_input_select(): void
    {
        errores::$error = false;
        $val = new selects();
        $val = new liberator($val);

        $tabla = '';
        $columnas = array();
        $resultado = $val->columnas_input_select($columnas, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $tabla = 'b';
        $columnas = array();
        $resultado = $val->columnas_input_select($columnas, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('b_id', $resultado[0]);
        $this->assertEquals('b_codigo', $resultado[1]);
        $this->assertEquals('b_descripcion', $resultado[2]);

        errores::$error = false;
        $tabla = 'b';
        $columnas = array();
        $columnas['a'] = 1;
        $resultado = $val->columnas_input_select($columnas, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['a']);


    }

    public function test_data_bd(){
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);

        $filtro = array();
        $name_modelo = '';

        $resultado = $sl->data_bd($filtro, $this->link, $name_modelo, false  );

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar modelo', $resultado['mensaje']);

        errores::$error = false;

        $filtro = array();
        $name_modelo = 'a';

        $resultado = $sl->data_bd($filtro, $this->link, $name_modelo, false );
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos', $resultado['mensaje']);

        errores::$error = false;

        $filtro = array();
        $name_modelo = 'adm_seccion';

        $resultado = $sl->data_bd($filtro, $this->link, $name_modelo, false );

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);

        errores::$error = false;

        $filtro = array();
        $name_modelo = 'adm_seccion';

        $resultado = $sl->data_bd($filtro, $this->link, $name_modelo, false );
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);
        errores::$error = false;
    }

    public function test_data_for_select(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);

        $valor = '';
        $tabla = '';
        $data_extra = array();
        $data_con_valor = array();
        $columnas = array();
        $resultado = $sl->data_for_select($columnas,$data_con_valor, $data_extra,$tabla,$valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $valor = '';
        $tabla = 'adm_mes';
        $data_extra = array();
        $data_con_valor = array();
        $columnas = array();
        $resultado = $sl->data_for_select($columnas,$data_con_valor, $data_extra,$tabla,$valor);
        $this->assertIsObject( $resultado);


        errores::$error = false;
        $valor = '';
        $tabla = 'prueba';
        $data_extra = array();
        $data_con_valor = array();
        $columnas = array();
        $resultado = $sl->data_for_select($columnas,$data_con_valor, $data_extra,$tabla,$valor);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('prueba_id', $resultado->columnas['0']);
        $this->assertEquals('prueba_codigo', $resultado->columnas['1']);
        $this->assertEquals('prueba_descripcion', $resultado->columnas['2']);
        errores::$error = false;
    }

    public function test_data_option(): void
    {
        errores::$error = false;
        $val = new selects();
        $val = new liberator($val);

        $columna = '';
        $i = '1';
        $value = array();
        $separador_select_columnas = '';
        $resultado = $val->data_option($columna, $i, $separador_select_columnas, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la columna esta vacia', $resultado['mensaje']);

        errores::$error = false;

        $columna = 'a';
        $i = '1';
        $value = array();
        $separador_select_columnas = '';
        $resultado = $val->data_option($columna, $i, $separador_select_columnas, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe dato en registro columna a', $resultado['mensaje']);

        errores::$error = false;

        $columna = 'a';
        $i = '1';
        $value = array();
        $separador_select_columnas = '';
        $value['a'] = 1;
        $resultado = $val->data_option($columna, $i, $separador_select_columnas, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' 1 ', $resultado);
        errores::$error = false;
    }

    public function test_data_options_select(): void
    {
        errores::$error = false;
        $val = new selects();
        $val = new liberator($val);

        $columnas = array();
        $i = '1';
        $value = array();
        $separador_select_columnas = '';
        $columnas[] = 'a';
        $value['a'] = 'z';
        $resultado = $val->data_options_select($columnas, $i, $separador_select_columnas, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' z ', $resultado);
        errores::$error = false;
    }

    public function test_data_select(){
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);

        $filtro = array();
        $name_modelo = 'adm_accion';
        $link = $this->link;
        $todos = true;

        $resultado = $sl->data_select($filtro, $link, $name_modelo, $todos);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_elemento_select_fijo(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);
        $llave_json = '';
        $resultado = $sl->elemento_select_fijo($llave_json);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $llave_json esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $llave_json = 'a';
        $resultado = $sl->elemento_select_fijo($llave_json);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('$llaves_valores debe venir en formato json string', $resultado['mensaje']);

        errores::$error = false;
        $llave_json = 'a:';
        $resultado = $sl->elemento_select_fijo($llave_json);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->key);
        $this->assertEquals('', $resultado->dato);

        errores::$error = false;
        $llave_json = 'a:1';
        $resultado = $sl->elemento_select_fijo($llave_json);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->key);
        $this->assertEquals('1', $resultado->dato);

        errores::$error = false;
    }

    public function test_obten_registros_select(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);
        $filtro = array();
        $link = $this->link;
        $name_modelo = 'adm_menu';
        $select_vacio_alta = false;
        $resultado = $sl->obten_registros_select($filtro, $link, $name_modelo, $select_vacio_alta);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_registros_activos(){
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);

        $filtro = array();
        $name_modelo = 'adm_mes';
        $link = $this->link;


        $resultado = $sl->registros_activos($filtro, $link, $name_modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);


    }

    public function test_selected_value(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);

        $value_base = '';
        $value_tabla = '';

        $resultado = $sl->selected_value($value_base, $value_tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $value_base esta vacio', $resultado['mensaje']);

        errores::$error = false;
        $value_base = 'a';
        $value_tabla = '';

        $resultado = $sl->selected_value($value_base, $value_tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $value_base = 'a';
        $value_tabla = 'a';

        $resultado = $sl->selected_value($value_base, $value_tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('selected', $resultado);
        errores::$error = false;
    }


}