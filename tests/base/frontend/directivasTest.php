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
        $this->assertStringContainsStringIgnoringCase("<div class='form-group col-md-4'><label class='col-form-label-md' for='a'>A</label><input  type='date'  class='form-control-md form-control input-md '  name='a'   id='a'   placeholder='Ingresa A'   required   title='Ingrese una a'   value='2000-01-01'         >   </div> ", $resultado);

        errores::$error = false;
    }

    public function test_genera_input_numero(): void
    {
        errores::$error = false;
        $dir = new directivas();
        //$inicializacion = new liberator($inicializacion);

        $campo = 'a';
        $css_id = '';
        $cols = 1;
        $data_extra = array();
        $disabled = false;
        $etiqueta = '';
        $ln = false;
        $pattern = '';
        $required = false;
        $tipo_letra = '';
        $value = '';
        $resultado = $dir->genera_input_numero($campo, $css_id, $cols, $data_extra, $disabled, $etiqueta, $ln,
            $pattern, $required, $tipo_letra, $value);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("lass='form-control text-center numero_inpu", $resultado);


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
        $resultado = $dir->upload_file($campo, $cols, $disabled, $required);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<input type='file' class='custom-file-input input-file ' name='a'   id='a'>", $resultado);
        errores::$error = false;
    }


}