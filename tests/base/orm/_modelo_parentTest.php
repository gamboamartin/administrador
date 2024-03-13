<?php
namespace tests\src;

use base\orm\_modelo_parent;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\test\liberator;
use stdClass;


class _modelo_parentTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_alta_bd(): void
    {
        $_SESSION['usuario_id'] = 1;
        errores::$error = false;

        $link = $this->link;
        $tabla = 'adm_menu';
        $modelo = new adm_menu($link);
        //$modelo = new liberator($modelo);

        $modelo->registro['descripcion'] = 'a';

        $resultado = $modelo->alta_bd();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->registro['adm_menu_descripcion']);
        errores::$error = false;
    }


    public function test_existe_attr(): void
    {
        errores::$error = false;

        $link = $this->link;
        $tabla = 'adm_menu';
        $modelo = new _modelo_parent($link, $tabla);
        $modelo = new liberator($modelo);


        $campo = 'a';
        $resultado = $modelo->existe_attr($campo);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(false, $resultado);

        $campo = 'a';
        $modelo->atributos->a = '';
        $resultado = $modelo->existe_attr($campo);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(true, $resultado);

        errores::$error = false;
    }

    public function test_limpiar_attr(): void
    {
        errores::$error = false;

        $link = $this->link;
        $tabla = 'adm_menu';
        $modelo = new _modelo_parent($link, $tabla);
        $modelo = new liberator($modelo);


        $campo = 'a';
        $resultado = $modelo->limpiar_attr($campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado);

        errores::$error = false;
    }

    public function test_limpiar_attrs(): void
    {
        errores::$error = false;

        $link = $this->link;
        $tabla = 'adm_menu';
        $modelo = new _modelo_parent($link, $tabla);
        $modelo = new liberator($modelo);


        $resultado = $modelo->limpiar_attrs();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado);

        errores::$error = false;

    }

    public function test_modifica_bd(): void
    {
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 1;
        errores::$error = false;

        $link = $this->link;
        $tabla = 'adm_menu';
        $modelo = new adm_menu($link);
        //$modelo = new liberator($modelo);

        $registro['descripcion'] = 'a';

        $resultado = $modelo->modifica_bd(registro: $registro,id: 1);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

    }



}