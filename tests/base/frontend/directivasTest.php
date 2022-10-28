<?php
namespace tests\base\frontend;

use base\frontend\directivas;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class directivasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }



    public function test_upload_file(): void
    {
        errores::$error = false;
        $dir = new directivas();
        //$inicializacion = new liberator($inicializacion);

        $campo = 'a';
        $cols = 1;
        $disabled = false;
        $required = false;
        $resultado = $dir->upload_file($campo, $cols, $required);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


}