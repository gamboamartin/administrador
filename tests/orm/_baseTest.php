<?php
namespace gamboamartin\administrador\tests\orm;

use base\orm\_base;
use gamboamartin\administrador\models\_base_accion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _baseTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_asigna_status_alta(): void
    {

        errores::$error = false;
        $modelo = new adm_seccion(link: $this->link);
        $modelo = new liberator($modelo);

        $_SESSION = array();
        $_SESSION['usuario_id'] = 2;

        $registro = array();
        $keys = array();
        $keys[] = 'a';
        $resultado = $modelo->asigna_status_alta($keys, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('activo',$resultado['a']);


        errores::$error = false;
    }




}

