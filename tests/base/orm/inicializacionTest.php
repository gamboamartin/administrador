<?php
namespace tests\base\orm;

use gamboamartin\errores\errores;

use base\orm\inicializacion;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\seccion;
use stdClass;



class inicializacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_valor_encriptado(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campo_limpio = new stdClass();
        $registro = array();

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campo_limpio', $resultado['mensaje']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = '';
        $campo_limpio->campo = '';
        $registro = array();

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campo_limpio', $resultado['mensaje']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = '';
        $campo_limpio->campo = 'a';
        $registro = array();

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = '';
        $campo_limpio->campo = 'a';
        $registro = array();
        $registro['a'] = 'z';

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PHDA/NloYgF1lc+UHzxaUw==', $resultado['a']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = 'z';
        $campo_limpio->campo = 'a';
        $registro = array();
        $registro['a'] = 'z';

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('65RRm7OkwNx4LtwV7rJRnA==', $resultado['a']);
        errores::$error = false;
    }

    public function test_encripta_valor_registro(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campo = '';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;


        $campo = 'a';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';
        $registro['a'] = 'prueba';

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('prueba', $resultado['a']);

        errores::$error = false;

        $campo = 'a';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';
        $registro['a'] = 'prueba';
        $campos_encriptados = array('z','a');

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PHDA/NloYgF1lc+UHzxaUw==', $resultado['a']);
        errores::$error = false;
    }

    public function test_init_bools(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);

        $bools = array();
        $bools[] = '';
        $resultado = $inicializacion->init_bools($bools);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(6, $resultado);
        $this->assertEquals('', $resultado['con_label']);
        $this->assertEquals('', $resultado['required']);
        $this->assertEquals('', $resultado['ln']);

        errores::$error = false;

        $bools = array();
        $bools['con_label'] = 'x';
        $resultado = $inicializacion->init_bools($bools);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(5, $resultado);
        $this->assertEquals('x', $resultado['con_label']);
        $this->assertEquals('', $resultado['required']);
        $this->assertEquals('', $resultado['ln']);

        errores::$error = false;
    }

    public function test_init_campo(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);
        $campo = array();
        $resultado = $inicializacion->init_campo($campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(7, $resultado);
        $this->assertEquals('', $resultado['elemento_lista_cols']);
        $this->assertEquals('', $resultado['elemento_lista_tipo']);
        $this->assertEquals('', $resultado['elemento_lista_tabla_externa']);
        $this->assertEquals('', $resultado['elemento_lista_etiqueta']);
        $this->assertEquals('', $resultado['elemento_lista_campo']);
        $this->assertEquals('', $resultado['elemento_lista_descripcion']);
        $this->assertEquals('', $resultado['elemento_lista_id']);

        errores::$error = false;
        $campo = array();
        $campo['elemento_lista_cols'] = 7;
        $resultado = $inicializacion->init_campo($campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(7, $resultado);
        $this->assertEquals('7', $resultado['elemento_lista_cols']);
        $this->assertEquals('', $resultado['elemento_lista_tipo']);
        $this->assertEquals('', $resultado['elemento_lista_tabla_externa']);
        $this->assertEquals('', $resultado['elemento_lista_etiqueta']);
        $this->assertEquals('', $resultado['elemento_lista_campo']);
        $this->assertEquals('', $resultado['elemento_lista_descripcion']);
        $this->assertEquals('', $resultado['elemento_lista_id']);


        errores::$error = false;

    }

    public function test_init_datos(): void
    {
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);
        $datos = new stdClass();
        $resultado = $inicializacion->init_datos($datos);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $datos = new stdClass();
        $datos->columnas = 'x';
        $resultado = $inicializacion->init_datos($datos);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $datos->columnas);
        errores::$error = false;
    }

    public function test_limpia_valores(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campo = '';
        $valor = '';
        $resultado = $inicializacion->limpia_valores($campo, $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;


        $campo = 'a';
        $valor = ' z ';
        $resultado = $inicializacion->limpia_valores($campo, $valor);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado->campo);
        $this->assertEquals('z',$resultado->valor);
        errores::$error = false;
    }

    public function test_status(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);

        $registro = array();
        $status_default = '';
        $resultado = $inicializacion->status($registro, $status_default);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error status_default no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $status_default = 'a';
        $resultado = $inicializacion->status($registro, $status_default);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['status']);

        errores::$error = false;


        $registro = array();
        $status_default = 'a';
        $registro['status'] = 'cv';
        $resultado = $inicializacion->status($registro, $status_default);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('cv', $resultado['status']);
        errores::$error = false;
    }

    public function test_tablas_select(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);

        $modelo = new seccion($this->link);
        $resultado = $inicializacion->tablas_select($modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado['models\\seccion']);
        errores::$error = false;
    }

}