<?php
namespace tests\base\frontend;

use base\frontend\html;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class htmlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_crea_elemento_encabezado(){
        errores::$error = false;
        $html = new html();
        $inicializacion = new liberator($html);

        $contenido = '';
        $label = '';

        $resultado = $inicializacion->crea_elemento_encabezado(contenido: $contenido,label: $label);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el label no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $contenido = '';
        $label = 'x';

        $resultado = $inicializacion->crea_elemento_encabezado(contenido: $contenido,label: $label);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("
            <div class='col-md-3'>
            <label>
                x
            </label>
            <br>
            
            </div>
            ", $resultado);

        errores::$error = false;

        $contenido = 'x';
        $label = 'x';

        $resultado = $inicializacion->crea_elemento_encabezado(contenido: $contenido,label: $label);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("
            <div class='col-md-3'>
            <label>
                x
            </label>
            <br>
            x
            </div>
            ", $resultado);

        errores::$error = false;

    }

    public function test_html_fecha(){
        errores::$error = false;
        $html = new html();
        $html = new liberator($html);

        $campo = '';
        $campo_capitalize = '';
        $params = new stdClass();
        $size = '';
        $tipo = '';
        $value = '';

        $resultado = $html->html_fecha(campo:  $campo, size: $size, tipo: $tipo, value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tipo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $campo_capitalize = '';
        $params = new stdClass();
        $size = '';
        $tipo = 'x';
        $value = '';

        $resultado = $html->html_fecha(campo:  $campo, size: $size, tipo: $tipo, value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $size no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $campo_capitalize = '';
        $params = new stdClass();
        $size = 'x';
        $tipo = 'x';
        $value = '';

        $resultado = $html->html_fecha(campo:  $campo, size: $size, tipo: $tipo, value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $campo_capitalize = '';
        $params = new stdClass();
        $size = 'x';
        $tipo = 'x';
        $value = '';

        $resultado = $html->html_fecha(campo:  $campo, size: $size, tipo: $tipo, value: $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<input  type='x'  class='form-control-x form-control input-x '  name='x'   placeholder='Ingresa '   title='Ingrese una x'   value=''   > ", $resultado);

        errores::$error = false;
    }
}