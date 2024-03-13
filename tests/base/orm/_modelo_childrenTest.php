<?php
namespace tests\src;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\test\liberator;
use stdClass;


class _modelo_childrenTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }


    public function test_activa(): void
    {
        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);

        $key = 'a';
        $registro = array();
        $registro_previo = new stdClass();
        $registro_previo->adm_accion_a = 'x';
        $resultado = $modelo->integra_campo_necesario($key, $registro, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado['a']);

        errores::$error = false;
    }



}