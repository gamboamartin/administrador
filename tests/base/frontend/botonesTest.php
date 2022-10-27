<?php
namespace tests\base\orm;

use base\frontend\botones;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class botonesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }


    public function test_data_btn(){
        errores::$error = false;
        $btn = new botones();
        $inicializacion = new liberator($btn);

        $class_css = array();
        $datas = array();
        $icon = '';
        $resultado = $btn->data_btn(class_css: $class_css, datas: $datas, icon: $icon);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->icon);

        errores::$error = false;

        $class_css = array();
        $datas = array();
        $icon = 'x';
        $resultado = $btn->data_btn(class_css: $class_css, datas: $datas, icon: $icon);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<i class="x"></i>', $resultado->icon);

        errores::$error = false;

        $class_css = array();
        $datas = array();
        $datas[] = '';
        $icon = '';
        $resultado = $btn->data_btn(class_css: $class_css, datas: $datas, icon: $icon);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

        $class_css = array();
        $datas = array();
        $datas['x'] = '';
        $icon = '';
        $resultado = $btn->data_btn(class_css: $class_css, datas: $datas, icon: $icon);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

        $class_css = array();
        $datas = array();
        $datas['x'] = 'x';
        $icon = '';
        $resultado = $btn->data_btn(class_css: $class_css, datas: $datas, icon: $icon);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

    }

    public function test_icon_html(){
        errores::$error = false;
        $btn = new botones();
        $inicializacion = new liberator($btn);

        $icon = '';
        $resultado = $inicializacion->icon_html(icon: $icon);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $icon = 'x';
        $resultado = $inicializacion->icon_html(icon: $icon);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<i class="x"></i>', $resultado);

        errores::$error = false;
    }
}