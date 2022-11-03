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



    public function test_asigna_datos_campo(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);
        $elementos_lista = array();
        $resultado = $inicializacion->asigna_datos_campo($elementos_lista);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);



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
        $elementos_lista[0]['adm_elemento_lista_descripcion'] = 'a';
        $elementos_lista[0]['adm_elemento_lista_tipo'] = 'b';
        $elementos_lista[0]['adm_elemento_lista_representacion'] = 'c';
        $elementos_lista[0]['adm_elemento_lista_etiqueta'] = 'd';

        $resultado = $inicializacion->asigna_datos_campo($elementos_lista);

        $this->assertIsObject( $resultado);
        $this->assertEmpty($resultado->campos);
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

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $elementos_lista = array();
        $elementos_lista[] = '';
        $resultado = $inicializacion->campos_lista($elementos_lista);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar $datos', $resultado['mensaje']);

        errores::$error = false;

        $elementos_lista = array();
        $elementos_lista[0]['adm_elemento_lista_descripcion'] = 'h';
        $elementos_lista[0]['adm_elemento_lista_tipo'] = 'h';
        $elementos_lista[0]['adm_elemento_lista_representacion'] = 'h';
        $elementos_lista[0]['adm_elemento_lista_etiqueta'] = 'h';
        $resultado = $inicializacion->campos_lista($elementos_lista);
        $this->assertIsObject( $resultado);
        $this->assertEmpty($resultado->campos);
        $this->assertNotEmpty($resultado->etiqueta_campos);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('h', $resultado->etiqueta_campos[0]);



        errores::$error = false;

    }


}