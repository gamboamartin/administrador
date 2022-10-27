<?php
namespace tests\base\frontend;

use base\frontend\inputs_files;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class inputs_filesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_contains_input_file(){
        errores::$error = false;
        $inputs = new inputs_files();
        $inputs = new liberator($inputs);

        $campo = '';
        $class_css_html = '';
        $disable_html = '';
        $ids_html = '';
        $labels = new stdClass();
        $required_html = '';

        $resultado = $inputs->contains_input_file(campo: $campo, class_css_html: $class_css_html,
            labels:  $labels, required_html: $required_html);


        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error campo vacio", $resultado['data']['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $class_css_html = '';
        $disable_html = '';
        $ids_html = '';
        $labels = new stdClass();
        $required_html = '';

        $resultado = $inputs->contains_input_file(campo: $campo, class_css_html: $class_css_html, labels:  $labels, required_html: $required_html);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="custom-file"><input type="file" class="custom-file-input" id="" name="x" multiple></div>',
            $resultado);

        errores::$error = false;

        $campo = 'x';
        $class_css_html = 'x';
        $disable_html = 'x';
        $ids_html = '';
        $labels = new stdClass();
        $required_html = '';

        $resultado = $inputs->contains_input_file(campo: $campo, class_css_html: $class_css_html, labels:  $labels, required_html: $required_html);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_content_input_multiple(){
        errores::$error = false;
        $inputs = new inputs_files();
        $inputs = new liberator($inputs);

        $input_upload_multiple = '';
        $label_input_upload = '';
        $resultado = $inputs->content_input_multiple(input_upload_multiple: $input_upload_multiple,
            label_input_upload: $label_input_upload);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="custom-file"></div>', $resultado);

        errores::$error = false;

        $input_upload_multiple = 'x';
        $label_input_upload = '';
        $resultado = $inputs->content_input_multiple(input_upload_multiple: $input_upload_multiple,
            label_input_upload: $label_input_upload);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="custom-file">x</div>', $resultado);

        errores::$error = false;

        $input_upload_multiple = 'x';
        $label_input_upload = 'x';
        $resultado = $inputs->content_input_multiple(input_upload_multiple: $input_upload_multiple,
            label_input_upload: $label_input_upload);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="custom-file">xx</div>', $resultado);

        errores::$error = false;
    }

    public function test_data_contains_input_file(){
        errores::$error = false;
        $inputs = new inputs_files();
        $inputs = new liberator($inputs);

        $campo = '';
        $class_css_html = '';
        $codigo = '';
        $disable_html = '';
        $etiqueta = '';
        $ids_html = '';
        $required_html = '';

        $resultado = $inputs->data_contains_input_file(campo: $campo, class_css_html:  $class_css_html,
            codigo:  $codigo, etiqueta:  $etiqueta,
            required_html:  $required_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error codigo esta vacio", $resultado['data']['mensaje']);

        errores::$error = false;

        $campo = '';
        $class_css_html = '';
        $codigo = 'x';
        $disable_html = '';
        $etiqueta = '';
        $ids_html = '';
        $required_html = '';

        $resultado = $inputs->data_contains_input_file(campo: $campo, class_css_html:  $class_css_html,
            codigo:  $codigo, etiqueta:  $etiqueta,
            required_html:  $required_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error etiqueta esta vacio", $resultado['data']['mensaje']);

        errores::$error = false;

        $campo = '';
        $class_css_html = '';
        $codigo = 'x';
        $disable_html = '';
        $etiqueta = 'x';
        $ids_html = '';
        $required_html = '';

        $resultado = $inputs->data_contains_input_file(campo: $campo, class_css_html:  $class_css_html,
            codigo:  $codigo, etiqueta:  $etiqueta,
            required_html:  $required_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al obtener inpu", $resultado['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $class_css_html = '';
        $codigo = 'x';
        $disable_html = '';
        $etiqueta = 'x';
        $ids_html = '';
        $required_html = '';

        $resultado = $inputs->data_contains_input_file(campo: $campo, class_css_html:  $class_css_html,
            codigo:  $codigo, etiqueta:  $etiqueta,
            required_html:  $required_html);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="input-group-prepend"><span class="input-group-text" >x</span></div>',
            $resultado->label_upload);

        errores::$error = false;

        $campo = 'a';
        $class_css_html = '';
        $codigo = 'b';
        $disable_html = '';
        $etiqueta = 'c';
        $ids_html = '';
        $required_html = 'd';

        $resultado = $inputs->data_contains_input_file(campo: $campo, class_css_html:  $class_css_html,
            codigo:  $codigo, etiqueta:  $etiqueta,
            required_html:  $required_html);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<label class="custom-file-label" for="c">c</label>',
            $resultado->label_input_upload);

        errores::$error = false;
    }

    public function test_input_file_multiple(){
        errores::$error = false;
        $inputs = new inputs_files();
        //$inputs = new liberator($inputs);

        $campo = '';
        $class_css_html = '';
        $codigo = '';
        $disable_html = '';
        $etiqueta = '';
        $ids_html = '';
        $required_html = '';
        $resultado = $inputs->input_file_multiple(campo: $campo, class_css_html: $class_css_html, codigo: $codigo,
            etiqueta:  $etiqueta, required_html: $required_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al obtener input", $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $class_css_html = '';
        $codigo = 'a';
        $disable_html = '';
        $etiqueta = '';
        $ids_html = '';
        $required_html = '';
        $resultado = $inputs->input_file_multiple(campo: $campo, class_css_html: $class_css_html, codigo: $codigo,
            etiqueta:  $etiqueta, required_html: $required_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al obtener input", $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $class_css_html = '';
        $codigo = 'a';
        $disable_html = '';
        $etiqueta = 'b';
        $ids_html = '';
        $required_html = '';
        $resultado = $inputs->input_file_multiple(campo: $campo, class_css_html: $class_css_html, codigo: $codigo,
            etiqueta:  $etiqueta, required_html: $required_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al obtener input", $resultado['mensaje']);

        errores::$error = false;

        $campo = 'c';
        $class_css_html = '';
        $codigo = 'a';
        $disable_html = '';
        $etiqueta = 'b';
        $ids_html = '';
        $required_html = '';
        $resultado = $inputs->input_file_multiple(campo: $campo, class_css_html: $class_css_html, codigo: $codigo,
            etiqueta:  $etiqueta, required_html: $required_html);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="input-group mb-3"><div class="input-group-prepend"><span class="input-group-text" >a</span></div><div class="custom-file"><input type="file" class="custom-file-input" id="" name="c" multiple><label class="custom-file-label" for="b">b</label></div></div>'
            , $resultado);

        errores::$error = false;
    }

    public function test_input_multiple_file(){
        errores::$error = false;
        $inputs = new inputs_files();
        $inputs = new liberator($inputs);

        $content_input = '';
        $label_upload = '';
        $resultado = $inputs->input_multiple_file(content_input: $content_input, label_upload: $label_upload);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="input-group mb-3"></div>', $resultado);

        errores::$error = false;

        $content_input = 'x';
        $label_upload = '';
        $resultado = $inputs->input_multiple_file(content_input: $content_input, label_upload: $label_upload);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="input-group mb-3">x</div>', $resultado);

        errores::$error = false;

        $content_input = 'x';
        $label_upload = 'x';
        $resultado = $inputs->input_multiple_file(content_input: $content_input, label_upload: $label_upload);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="input-group mb-3">xx</div>', $resultado);

        errores::$error = false;
    }

    public function test_input_upload_multiple()
    {
        errores::$error = false;
        $inputs = new inputs_files();
        $inputs = new liberator($inputs);

        $campo = '';
        $class_css_html = '';
        $disable_html = '';
        $ids_html = '';
        $required_html = '';
        $resultado = $inputs->input_upload_multiple(campo: $campo, class_css_html: $class_css_html, required_html: $required_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error campo vacio", $resultado['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $class_css_html = '';
        $disable_html = '';
        $ids_html = '';
        $required_html = '';
        $resultado = $inputs->input_upload_multiple(campo: $campo, class_css_html: $class_css_html, required_html: $required_html);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<input type="file" class="custom-file-input" id="" name="x" multiple>',
            $resultado);

        errores::$error = false;
    }
}