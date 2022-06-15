<?php
namespace tests\base\frontend;

use base\frontend\templates;
use gamboamartin\errores\errores;

use gamboamartin\test\test;




class templatesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_campos_lista()
    {
        errores::$error = false;
        $tmp = new templates($this->link);
        //$inicializacion = new liberator($inicializacion);
        $elementos_lista = array();
        $resultado = $tmp->campos_lista($elementos_lista);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
        $elementos_lista = array();
        $elementos_lista[] = '';
        $resultado = $tmp->campos_lista($elementos_lista);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar campos lista', $resultado['mensaje']);

        errores::$error = false;
        $elementos_lista = array();
        $elementos_lista[0] = array();
        $resultado = $tmp->campos_lista($elementos_lista);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar campos lista', $resultado['mensaje']);

        errores::$error = false;
        $elementos_lista = array();
        $elementos_lista[0]['adm_elemento_lista_descripcion'] = 'a';
        $elementos_lista[0]['adm_elemento_lista_tipo'] = 'a';
        $elementos_lista[0]['adm_elemento_lista_representacion'] = 'a';
        $elementos_lista[0]['adm_elemento_lista_etiqueta'] = 'a';
        $resultado = $tmp->campos_lista($elementos_lista);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->campos[0]['nombre_campo']);


    }


}