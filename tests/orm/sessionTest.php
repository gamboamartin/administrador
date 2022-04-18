<?php
namespace tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\session;
use stdClass;


class sessionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_init_session(){

        errores::$error = false;
        $session = new session($this->link);
        $session = new liberator($session);
        $session_id = '';
        $resultado = $session->init_session($session_id);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error session_id esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $session_id = 'a';
        $resultado = $session->init_session($session_id);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al iniciar session', $resultado['mensaje']);
        errores::$error = false;

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

    public function test_session_activa(){

        errores::$error = false;
        unset($_GET['session_id']);
        $session = new session($this->link);
        $session = new liberator($session);
        $resultado = $session->session_activa();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;
        $_GET['session_id'] = 1;
        $resultado = $session->session_activa();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        unset($_GET['session_id']);
        errores::$error = false;
    }


}

