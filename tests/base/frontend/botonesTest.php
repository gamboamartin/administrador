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

    public function test_boton_acciones_list(){
        errores::$error = false;
        $btn = new botones();
        //$inicializacion = new liberator($inicializacion);


        $resultado = $btn->boton_acciones_list();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<button class='btn btn-outline-info btn-sm'><i class='bi bi-chevron-down'></i> Acciones </button>", $resultado);


        errores::$error = false;
    }

    public function test_boton_pestana(){
        errores::$error = false;
        $btn = new botones();
        //$inicializacion = new liberator($inicializacion);

        $class_btn = '';
        $target = '';
        $resultado = $btn->boton_pestana(class_btn: $class_btn, target: $target);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error class_btn vacio', $resultado['mensaje']);

        errores::$error = false;

        $class_btn = 'a';
        $target = '';
        $resultado = $btn->boton_pestana(class_btn: $class_btn, target: $target);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error target vacio', $resultado['mensaje']);

        errores::$error = false;

        $class_btn = 'b';
        $target = 'd';
        $resultado = $btn->boton_pestana(class_btn: $class_btn, target: $target);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<button class="nav-link active  btn-b"data-toggle="collapse"  data-target="#d" aria-expanded="true"aria-controls="d" >D</button>', $resultado);

        errores::$error = false;
    }

    public function test_btn_html(){
        errores::$error = false;
        $btn = new botones();
        $inicializacion = new liberator($btn);

        $id_css = '';
        $label = '';
        $name = '';
        $params = new stdClass();
        $stilo = '';
        $type = '';
        $value = '';
        $resultado = $inicializacion->btn_html(id_css:  $id_css, label: $label,name:  $name,params:  $params,
            stilo: $stilo, type: $type, value:  $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error type esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $id_css = '';
        $label = '';
        $name = '';
        $params = new stdClass();
        $stilo = '';
        $type = 'x';
        $value = '';
        $resultado = $inicializacion->btn_html(id_css:  $id_css, label: $label,name:  $name,params:  $params,
            stilo: $stilo, type: $type, value:  $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $stilo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $id_css = '';
        $label = '';
        $name = '';
        $params = new stdClass();
        $stilo = 'x';
        $type = 'x';
        $value = '';
        $resultado = $inicializacion->btn_html(id_css:  $id_css, label: $label,name:  $name,params:  $params,
            stilo: $stilo, type: $type, value:  $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsString("<button type='x'", $resultado);

        errores::$error = false;
    }

    public function test_button(){
        errores::$error = false;
        $btn = new botones();
        $inicializacion = new liberator($btn);

        $cols = '0';
        $id_css = '';
        $label = '';
        $name = '';
        $params =  new stdClass();
        $stilo = '';
        $type = '';
        $value = '';
        $resultado =  $inicializacion->button(cols: $cols, id_css: $id_css,label: $label, name: $name, params: $params,
            stilo: $stilo, type: $type,value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error type esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $cols = '0';
        $id_css = '';
        $label = '';
        $name = '';
        $params =  new stdClass();
        $stilo = '';
        $type = 'x';
        $value = '';
        $resultado =  $inicializacion->button(cols: $cols, id_css: $id_css,label: $label, name: $name, params: $params,
            stilo: $stilo, type: $type,value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $stilo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $cols = '-1';
        $id_css = '';
        $label = '';
        $name = '';
        $params =  new stdClass();
        $stilo = 'x';
        $type = 'x';
        $value = '';
        $resultado =  $inicializacion->button(cols: $cols, id_css: $id_css,label: $label, name: $name, params: $params,
            stilo: $stilo, type: $type,value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar container', $resultado['mensaje']);

        errores::$error = false;

        $cols = '1';
        $id_css = '';
        $label = '';
        $name = '';
        $params =  new stdClass();
        $stilo = 'x';
        $type = 'x';
        $value = '';
        $resultado =  $inicializacion->button(cols: $cols, id_css: $id_css,label: $label, name: $name, params: $params,
            stilo: $stilo, type: $type,value: $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div class='col-md-1'><button type='x' name='' value='' id=''  
                    class='btn btn-x col-md-12 ' > </button></div>", $resultado);

        errores::$error = false;
    }

    public function test_container_html(){
        errores::$error = false;
        $btn = new botones();
        $inicializacion = new liberator($btn);

        $cols = '-1';
        $contenido = '';
        $resultado = $inicializacion->container_html(cols: $cols, contenido: $contenido);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error cols debe ser mayor a 0',
            $resultado['data']['mensaje']);

        errores::$error = false;

        $cols = '13';
        $contenido = 'a';
        $resultado = $inicializacion->container_html(cols: $cols, contenido: $contenido);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error cols debe ser menor a 13',
            $resultado['data']['mensaje']);

        errores::$error = false;

        $cols = '12';
        $contenido = 'b';
        $resultado = $inicializacion->container_html(cols: $cols, contenido: $contenido);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div class='col-md-12'>b</div>", $resultado);

        errores::$error = false;
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