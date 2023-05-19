<?php
namespace tests\src;

use base\orm\_modelo_parent;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\test\liberator;
use stdClass;


class _modelo_parent_sin_codigoTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
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






}