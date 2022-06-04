<?php
namespace tests\base\controller;

use base\controller\normalizacion;
use base\orm\activaciones;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion_grupo;


class activacionesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_normaliza_name_model(){
        errores::$error = false;
        $act = new activaciones();
        $act = new liberator($act);

        $modelo = new adm_accion_grupo($this->link);
        $resultado = $act->normaliza_name_model($modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion_grupo', $resultado);

        errores::$error = false;

        $modelo->tabla = '';
        $resultado = $act->normaliza_name_model($modelo);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error el atributo tabla del modelo  Esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $modelo->tabla = 'x';
        $resultado = $act->normaliza_name_model($modelo);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;

        $modelo->tabla = 'models\\x';
        $resultado = $act->normaliza_name_model($modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);


        errores::$error = false;
    }


}