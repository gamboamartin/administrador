<?php
namespace tests\base\frontend;

use base\frontend\inicializacion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class inicializacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_acciones(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);
        $acciones_asignadas = array();
        $resultado = $inicializacion->acciones($acciones_asignadas);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;
        $acciones_asignadas = array();
        $acciones_asignadas[] = '';
        $resultado = $inicializacion->acciones($acciones_asignadas);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $acciones_asignadas[] debe ser un array', $resultado['mensaje']);

        errores::$error = false;
        $acciones_asignadas = array();
        $acciones_asignadas[] = array();
        $resultado = $inicializacion->acciones($acciones_asignadas);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;
        $acciones_asignadas = array();
        $acciones_asignadas[0]['adm_accion_descripcion'] = 'a';
        $resultado = $inicializacion->acciones($acciones_asignadas);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado[0]);


        errores::$error = false;

    }

    public function test_asigna_datos_campo(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);
        $elementos_lista = array();
        $resultado = $inicializacion->asigna_datos_campo($elementos_lista);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error elemento_lista no puede venir vacio', $resultado['mensaje']);


        errores::$error = false;
        $elementos_lista = array();
        $elementos_lista[] = '';
        $resultado = $inicializacion->asigna_datos_campo($elementos_lista);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $elementos_lista[] debe ser un array', $resultado['mensaje']);

        errores::$error = false;
        $elementos_lista = array();
        $elementos_lista[] = array();
        $resultado = $inicializacion->asigna_datos_campo($elementos_lista);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;
        $elementos_lista = array();
        $elementos_lista[0]['elemento_lista_descripcion'] = 'a';
        $elementos_lista[0]['elemento_lista_tipo'] = 'b';
        $elementos_lista[0]['elemento_lista_representacion'] = 'c';
        $elementos_lista[0]['elemento_lista_etiqueta'] = 'd';

        $resultado = $inicializacion->asigna_datos_campo($elementos_lista);
        $this->assertIsObject( $resultado);
        $this->assertNotEmpty($resultado->campos);
        $this->assertNotEmpty($resultado->etiqueta_campos);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('d', $resultado->etiqueta_campos[0]);



        errores::$error = false;

    }

    public function test_campos_lista(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);
        $elementos_lista = array();
        $resultado = $inicializacion->campos_lista($elementos_lista);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $elementos_lista = array();
        $elementos_lista[] = '';
        $resultado = $inicializacion->campos_lista($elementos_lista);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar $datos', $resultado['mensaje']);

        errores::$error = false;

        $elementos_lista = array();
        $elementos_lista[0]['elemento_lista_descripcion'] = 'h';
        $elementos_lista[0]['elemento_lista_tipo'] = 'h';
        $elementos_lista[0]['elemento_lista_representacion'] = 'h';
        $elementos_lista[0]['elemento_lista_etiqueta'] = 'h';
        $resultado = $inicializacion->campos_lista($elementos_lista);
        $this->assertIsObject( $resultado);
        $this->assertNotEmpty($resultado->campos);
        $this->assertNotEmpty($resultado->etiqueta_campos);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('h', $resultado->etiqueta_campos[0]);



        errores::$error = false;

    }

    public function test_datos_campo(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);
        $registro = array();
        $resultado = $inicializacion->datos_campo($registro);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $registro = array();
        $registro['elemento_lista_descripcion'] = 'prueba';
        $registro['elemento_lista_tipo'] = 'prueba';
        $registro['elemento_lista_representacion'] = 'prueba';
        $resultado = $inicializacion->datos_campo($registro);
        $this->assertIsArray( $resultado);
        $this->assertStringContainsStringIgnoringCase('prueba', $resultado['nombre_campo']);
        $this->assertStringContainsStringIgnoringCase('prueba', $resultado['tipo']);
        $this->assertStringContainsStringIgnoringCase('prueba', $resultado['representacion']);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

    }

}