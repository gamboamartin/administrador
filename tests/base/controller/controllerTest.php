<?php
namespace tests\base\controller;

use base\controller\base_html;
use base\controller\controler;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_atributo;


class controllerTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_filtro_get(): void
    {

        errores::$error = false;

        $ctl = new controler();
        $ctl = new liberator($ctl);

        $keys = array();
        $resultado = $ctl->asigna_filtro_get($keys);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $keys = array();
        $keys['campo'] = 'a';
        $resultado = $ctl->asigna_filtro_get($keys);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);


        errores::$error = false;

        $keys = array();
        $keys['pais'] = 'id';
        $_GET['pais_id'] = 1;
        $resultado = $ctl->asigna_filtro_get($keys);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;



        $keys = array();
        $keys['pais'] = array();
        $_GET['pais_id'] = 1;
        $resultado = $ctl->asigna_filtro_get($keys);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;



        $keys = array();
        $keys['pais'] = array('id');
        $_GET['pais_id'] = 1;
        $resultado = $ctl->asigna_filtro_get($keys);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['pais.id']);

        errores::$error = false;

    }

    public function test_get_out(): void
    {

        errores::$error = false;

        $ctl = new controler();
        $ctl = new liberator($ctl);

        $keys = array();
        $header = false;
        $ws = false;
        $ctl->modelo = new adm_atributo($this->link);
        $resultado = $ctl->get_out($header, $keys, $ws);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }




}