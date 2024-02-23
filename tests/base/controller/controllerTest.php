<?php
namespace tests\base\controller;

use base\controller\controler;
use gamboamartin\administrador\models\adm_atributo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class controllerTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_inputs(): void
    {

        errores::$error = false;

        $ctl = new controler($this->link);
        //$ctl = new liberator($ctl);


        $ctl->modelo = new adm_atributo($this->link);
        $inputs = new stdClass();
        $ctl->inputs = new stdClass();
        $resultado = $ctl->asigna_inputs($inputs);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

    }



    public function test_get_out(): void
    {

        errores::$error = false;

        $ctl = new controler($this->link);
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

    public function test_retorno_error(): void
    {

        errores::$error = false;

        $ctl = new controler($this->link);
        //$ctl = new liberator($ctl);

        $mensaje = 'a';
        $data = '';
        $header = false;
        $ws = false;
        $ctl->modelo = new adm_atributo($this->link);
        $resultado = $ctl->retorno_error($mensaje, $data, $header, $ws);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;
    }





}