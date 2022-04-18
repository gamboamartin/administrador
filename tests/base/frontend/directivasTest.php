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

    public function test_checkbox(): void
    {
        errores::$error = false;
        $dir = new directivas();
        //$inicializacion = new liberator($inicializacion);

        $campo = '';
        $resultado = $dir->checkbox($campo);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $resultado = $dir->checkbox($campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<input type='checkbox'", $resultado);
        errores::$error = false;



    }

    public function test_recaptura(): void
    {
        errores::$error = false;
        $dir = new directivas();
        //$inicializacion = new liberator($inicializacion);

        $resultado = $dir->recaptura();

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<input type='checkbox'", $resultado);
        $this->assertStringContainsStringIgnoringCase("type='button'>Recaptura", $resultado);

        errores::$error = false;



    }


}