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


    public function test_fecha(): void
    {
        errores::$error = false;
        $dir = new directivas();
        //$inicializacion = new liberator($inicializacion);

        $campo = 'a';
        $resultado = $dir->fecha($campo);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("'><label class='col-form-label-md' for='a'>A</label><input  type='", $resultado);

        errores::$error = false;

        $campo = 'a';
        $resultado = $dir->fecha(campo:$campo,value: '2000-01-01');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_genera_input_numero(): void
    {
        errores::$error = false;
        $dir = new directivas();
        //$inicializacion = new liberator($inicializacion);

        $campo = 'a';

        $cols = 1;

        $ln = false;

        $required = false;
        $tipo_letra = '';
        $value = '';
        $resultado = $dir->genera_input_numero(campo: $campo, cols:  $cols,ln:  $ln,
            required:  $required,tipo_letra:  $tipo_letra, value: $value);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("lass='form-control text-center numero_inpu", $resultado);


        errores::$error = false;
    }

    public function test_password(): void
    {
        errores::$error = false;
        $dir = new directivas();
        //$inicializacion = new liberator($inicializacion);

        $campo = 'a';

        $cols = 1;

        $value = '';
        $resultado = $dir->password($campo, $cols, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("type='password' clas", $resultado);
        errores::$error = false;

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