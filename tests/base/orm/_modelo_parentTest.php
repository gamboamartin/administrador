<?php
namespace tests\src;

use base\orm\_modelo_parent;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\test\liberator;
use stdClass;


class _modelo_parentTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
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



}