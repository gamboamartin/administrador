<?php
namespace tests\base\orm;

use base\orm\estructuras;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class estructurasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_modelos(){
        errores::$error = false;
        $st = new estructuras($this->link);
        //$joins = new liberator($joins);
        $resultado = $st->modelos();
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('campo',$resultado[5]);

        errores::$error = false;

    }



}