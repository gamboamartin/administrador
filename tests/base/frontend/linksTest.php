<?php
namespace tests\base\frontend;

use base\frontend\links;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class linksTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_boton_icon(){
        errores::$error = false;
        $links = new links();
        $links = new liberator($links);

        $icon = '';
        $class_css = array();
        $data_toggle = '';
        $datas = array();
        $label = '';
        $size = '';
        $tarjet = '';
        $resultado = $links->boton_icon(icon: $icon,class_css: $class_css, data_toggle: $data_toggle, datas: $datas,
            label: $label, size: $size, tarjet: $tarjet);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<button type="button" class="btn btn-primary " data-target=""
        data-toggle = ""  > </button>', $resultado);

        errores::$error = false;

        $icon = 'x';
        $class_css = array();
        $data_toggle = 'a';
        $datas = array();
        $label = 'a';
        $size = '';
        $tarjet = '';
        $resultado = $links->boton_icon(icon: $icon,class_css: $class_css, data_toggle: $data_toggle, datas: $datas,
            label: $label, size: $size, tarjet: $tarjet);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<button type="button" class="btn btn-primary " data-target=""
        data-toggle = "a"  >a <i class="x"></i></button>', $resultado);

        errores::$error = false;
    }


    public function test_link_accion(){
        errores::$error = false;
        $links = new links();
        //$links = new liberator($links);

        $accion = '';
        $icon = '';
        $registro_id = 0;
        $seccion = '';
        $session_id = '';
        $styles = array();
        $resultado = $links->link_accion(accion: $accion, icon: $icon, registro_id: $registro_id, seccion: $seccion,
            session_id: $session_id, styles: $styles);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<a href=index.php?seccion=&accion=&registro_id=0&session_id=><button type="button" class="btn btn-primary " data-target=""
        data-toggle = ""  > </button></a>', $resultado);

        errores::$error= false;

        $accion = 'x';
        $icon = 'x';
        $registro_id = 0;
        $seccion = '';
        $session_id = '';
        $styles = array();
        $resultado = $links->link_accion(accion: $accion, icon: $icon, registro_id: $registro_id, seccion: $seccion,
            session_id: $session_id, styles: $styles);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<a href=index.php?seccion=&accion=x&registro_id=0&session_id=><button type="button" class="btn btn-primary " data-target=""
        data-toggle = ""  > <i class="x"></i></button></a>', $resultado);

        errores::$error= false;
    }



    public function test_link_menu(){
        errores::$error = false;
        $links = new links();
        $links = new liberator($links);

        $_SESSION = array();

        $menus = array();
        $seccion_menu_descripcion = '';
        $session_id = '';
        $resultado = $links->link_menu(menus: $menus,seccion_menu_descripcion: $seccion_menu_descripcion,
            session_id: $session_id);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir grupo_id', $resultado['mensaje']);

        errores::$error = false;

        $_SESSION['grupo_id'] = 1;

        $menus = array();
        $seccion_menu_descripcion = '';
        $session_id = '';
        $resultado = $links->link_menu(menus: $menus,seccion_menu_descripcion: $seccion_menu_descripcion,
            session_id: $session_id);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $_SESSION['grupo_id'] = 1;

        $menus[] = array('accion_etiqueta_label' => '', 'accion_descripcion' => '');
        $seccion_menu_descripcion = '';
        $session_id = '';
        $resultado = $links->link_menu(menus: $menus,seccion_menu_descripcion: $seccion_menu_descripcion,
            session_id: $session_id);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<li><a href='index.php?seccion=&accion=&session_id='></a></li>", $resultado);

        errores::$error = false;
    }


}