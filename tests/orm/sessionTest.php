<?php
namespace tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use models\session;


class sessionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }


    public function test_obten_filtro_session(){

        errores::$error = false;
        $session = new session($this->link);
//$inicializacion = new liberator($inicializacion);
        $resultado = $session->obten_filtro_session('');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $resultado = $session->obten_filtro_session('x');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $resultado = $session->obten_filtro_session('grupo');
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

    }


}

