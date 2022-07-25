<?php
namespace tests\base\orm;

use base\orm\validaciones;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class validacionesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_existe_tabla(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $name_bd = 'administrador';
        $tabla = 'adm_seccion';
        $resultado = $val->existe_tabla($this->link, $name_bd, $tabla);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_alta_bd(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $registro = array();
        $tabla = '';
        $resultado = $val->valida_alta_bd($registro, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error registro no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $tabla = '';
        $registro[] = 'a';
        $resultado = $val->valida_alta_bd($registro, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla esta vacia', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $tabla = 'b';
        $registro[] = 'a';
        $resultado = $val->valida_alta_bd($registro, $tabla);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_campo_envio(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $bools = array();
        $campo = array();
        $campo['adm_elemento_lista_campo'] = 'a';
        $campo['adm_elemento_lista_cols'] = 'a';
        $campo['adm_elemento_lista_tipo'] = 'a';
        $campo['adm_elemento_lista_tabla_externa'] = 'a';
        $campo['adm_elemento_lista_etiqueta'] = 'a';
        $campo['adm_elemento_lista_descripcion'] = 'a';
        $campo['adm_elemento_lista_id'] = 'a';
        $campo['con_label'] = 'a';

        $bools['con_label'] = 'a';
        $bools['required'] = 'a';
        $bools['ln'] = 'a';
        $bools['select_vacio_alta'] = 'a';

        $resultado = $val->valida_campo_envio($bools, $campo);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_data_columna(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $data = array();
        $tabla = '';
        $resultado = $val->valida_data_columna($data, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data', $resultado['mensaje']);

        errores::$error = false;
        $data = array();
        $tabla = '';
        $data['nombre_original'] = 'a';
        $resultado = $val->valida_data_columna($data, $tabla);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
        $data = array();
        $tabla = '';
        $data['nombre_original'] = 'seccion';
        $resultado = $val->valida_data_columna($data, $tabla);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_data_filtro_especial(): void
    {
        errores::$error = false;
        $val = new validaciones();


        $campo = '';
        $filtro = array();

        $resultado = $val->valida_data_filtro_especial($campo, $filtro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $filtro = array();

        $resultado = $val->valida_data_filtro_especial($campo, $filtro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir $filtro[campo][operador]', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $filtro = array();
        $filtro['a']['operador'] = 'b';

        $resultado = $val->valida_data_filtro_especial($campo, $filtro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }

    public function test_valida_dato_filtro_especial(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $filtro_esp = array();
        $campo = '';
        $resultado = $val->valida_dato_filtro_especial($campo, $filtro_esp);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;

        $filtro_esp = array();
        $campo = 'z';
        $resultado = $val->valida_dato_filtro_especial($campo, $filtro_esp);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $filtro_esp[z] debe existir', $resultado['mensaje']);

        errores::$error = false;

        $filtro_esp = array();
        $campo = 'z';
        $filtro_esp['z'] = 'a';
        $resultado = $val->valida_dato_filtro_especial($campo, $filtro_esp);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $filtro_esp[z] debe ser un array', $resultado['mensaje']);

        errores::$error = false;

        $filtro_esp = array();
        $campo = 'z';
        $filtro_esp['z'] = array();
        $resultado = $val->valida_dato_filtro_especial($campo, $filtro_esp);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $filtro_esp[z][valor] debe existir', $resultado['mensaje']);

        errores::$error = false;

        $filtro_esp = array();
        $campo = 'z';
        $filtro_esp['z']['valor'] = 'd';
        $resultado = $val->valida_dato_filtro_especial($campo, $filtro_esp);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_keys_renombre(): void
    {
        errores::$error = false;
        $val = new validaciones();


        $data = array();
        $tabla_renombrada = '';

        $resultado = $val->valida_keys_renombre($data, $tabla_renombrada);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error data[enlace] debe existir', $resultado['mensaje']);

        errores::$error = false;

        $data = array();
        $tabla_renombrada = 'a';
        $data['enlace'] = 'a';
        $data['nombre_original'] = 'a';

        $resultado = $val->valida_keys_renombre($data, $tabla_renombrada);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_keys_sql(): void
    {
        errores::$error = false;
        $val = new validaciones();


        $data = array();
        $tabla = '';

        $resultado = $val->valida_keys_sql($data, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error data[key] debe existir en', $resultado['mensaje']);

        errores::$error = false;

        $data = array();
        $tabla = '';
        $data['key'] = 'a';

        $resultado = $val->valida_keys_sql($data, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error data[enlace] debe existir', $resultado['mensaje']);

        errores::$error = false;

        $data = array();
        $tabla = '';
        $data['key'] = 'a';
        $data['enlace'] = 'b';

        $resultado = $val->valida_keys_sql($data, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error data[key_enlace] debe existir', $resultado['mensaje']);

        errores::$error = false;

        $data = array();
        $tabla = '';
        $data['key'] = 'a';
        $data['enlace'] = 'b';
        $data['key_enlace'] = 'c';

        $resultado = $val->valida_keys_sql($data, $tabla);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_pattern_campo(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $key = '';
        $registro = array();
        $tipo_campo = '';
        $resultado = $val->valida_pattern_campo(key: $key,registro:  $registro,tipo_campo:  $tipo_campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el registro no no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $key = '';
        $registro = array();
        $tipo_campo = '';
        $registro[] = '';
        $resultado = $val->valida_pattern_campo(key: $key,registro:  $registro,tipo_campo:  $tipo_campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $key = 'a';
        $registro = array();
        $tipo_campo = '';
        $registro[] = '';
        $resultado = $val->valida_pattern_campo(key: $key,registro:  $registro,tipo_campo:  $tipo_campo);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_pattern_model(): void
    {
        errores::$error = false;
        $val = new validaciones();
        $val = new liberator($val);

        $key = '';
        $registro = array();
        $tipo_campo = '';
        $resultado = $val->valida_pattern_model(key:$key, registro: $registro,tipo_campo:  $tipo_campo);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $key = 'a';
        $registro = array();
        $tipo_campo = '';
        $resultado = $val->valida_pattern_model(key:$key, registro: $registro,tipo_campo:  $tipo_campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe el campo a', $resultado['mensaje']);

        errores::$error = false;

        $key = 'a';
        $registro = array();
        $tipo_campo = '';
        $registro['a'] = '';
        $resultado = $val->valida_pattern_model(key:$key, registro: $registro,tipo_campo:  $tipo_campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe el pattern', $resultado['mensaje']);

        errores::$error = false;

        $key = 'a';
        $registro = array();
        $tipo_campo = 'id';
        $registro['a'] = '';
        $resultado = $val->valida_pattern_model(key:$key, registro: $registro,tipo_campo:  $tipo_campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo a es invalido', $resultado['mensaje']);

        errores::$error = false;

        $key = 'a';
        $registro = array();
        $tipo_campo = 'id';
        $registro['a'] = '1';
        $resultado = $val->valida_pattern_model(key:$key, registro: $registro,tipo_campo:  $tipo_campo);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);



        errores::$error = false;


    }

    public function test_valida_renombres(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $campo_renombrado = '';
        $tabla = '';
        $class = '';
        $class_enlace = '';
        $join = '';
        $renombrada = '';
        $tabla_enlace = '';
        $resultado = $val->valida_renombres($campo_renombrado, $class, $class_enlace, $join, $renombrada,
            $tabla, $tabla_enlace);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('La tabla no puede ir vacia', $resultado['mensaje']);

        errores::$error = false;


        $campo_renombrado = '';
        $tabla = 'a';
        $class = '';
        $class_enlace = '';
        $join = '';
        $renombrada = '';
        $tabla_enlace = '';
        $resultado = $val->valida_renombres($campo_renombrado, $class, $class_enlace, $join, $renombrada,
            $tabla, $tabla_enlace);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('La tabla no puede ir vacia', $resultado['mensaje']);

        errores::$error = false;


        $campo_renombrado = '';
        $tabla = 'a';
        $class = '';
        $class_enlace = '';
        $join = 'b';
        $renombrada = '';
        $tabla_enlace = '';
        $resultado = $val->valida_renombres($campo_renombrado, $class, $class_enlace, $join, $renombrada,
            $tabla, $tabla_enlace);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('El join no puede ir vacio', $resultado['mensaje']);

        errores::$error = false;


        $campo_renombrado = '';
        $tabla = 'a';
        $class = '';
        $class_enlace = '';
        $join = 'b';
        $renombrada = 'c';
        $tabla_enlace = 'd';
        $resultado = $val->valida_renombres($campo_renombrado, $class, $class_enlace, $join, $renombrada,
            $tabla, $tabla_enlace);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('El join no puede ir vacio', $resultado['mensaje']);

        errores::$error = false;


        $campo_renombrado = 'e';
        $tabla = 'models\\adm_seccion';
        $class = 'models\\adm_seccion';
        $class_enlace = 'models\\adm_seccion';
        $join = 'INNER';
        $renombrada = 'c';
        $tabla_enlace = 'd';
        $resultado = $val->valida_renombres($campo_renombrado, $join, $renombrada, $tabla, $tabla_enlace);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }

    public function test_valida_tabla_join(): void
    {
        errores::$error = false;
        $val = new validaciones();


        $key = '';
        $tabla_join = '';

        $resultado = $val->valida_tabla_join($key, $tabla_join);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $key = 'a';
        $tabla_join = '';

        $resultado = $val->valida_tabla_join($key, $tabla_join);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_join esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $key = 'a';
        $tabla_join = 'c';

        $resultado = $val->valida_tabla_join($key, $tabla_join);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_upd_base(): void
    {
        errores::$error = false;
        $val = new validaciones();
        //$val = new liberator($val);

        $registro_upd = array();
        $id = -1;
        $resultado = $val->valida_upd_base($id, $registro_upd);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('id', $resultado['mensaje']);

        errores::$error = false;

        $registro_upd = array();
        $id = 1;
        $resultado = $val->valida_upd_base($id, $registro_upd);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('El registro no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $registro_upd = array();
        $id = 1;
        $registro_upd[] = '';
        $resultado = $val->valida_upd_base($id, $registro_upd);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

}