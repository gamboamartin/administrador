<?php
namespace tests\base;
use base\orm\_create;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use PHPUnit\Framework\TestCase;
use stdClass;


class _createTest extends TestCase
{

    public function test_atributo_codigo(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $campos = new stdClass();
        $result = $_create->atributo_codigo($campos);
        $this->assertTrue($result->codigo->unique);

        errores::$error = false;
    }

    public function test_atributo_integer(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $campos = new stdClass();
        $campo = 'A';
        $result = $_create->atributo_integer($campos, $campo);
        $this->assertEquals('INT', $result->A->tipo_dato);

        errores::$error = false;
    }
    public function test_atributos_base(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $atributos = new stdClass();
        $atributos_base = new stdClass();
        $atributos_base->tipo_dato = 'a';
        $atributos_base->longitud = 'a';
        $atributos_base->not_null = 'a';
        $result = $_create->atributos_base(atributos: $atributos,atributos_base:  $atributos_base);
        $this->assertEquals('a', $result->tipo_dato);

        errores::$error = false;
    }

    public function test_atributo_status(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $campos = new stdClass();
        $result = $_create->atributo_status($campos);
        $this->assertEquals('activo',$result->status->default);

        errores::$error = false;
    }

    /**
     * Prueba la función atributos_iniciales de la clase _create
     *
     */
    public function test_atributos_iniciales(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);
        $expected = new stdClass();
        $expected->tipo_dato = "VARCHAR";
        $expected->longitud = "255";
        $expected->not_null = "NOT NULL";

        // Act (Actuar)
        $result = $_create->atributos_iniciales();

        // Assert (Afirma)
        $this->assertEquals($expected, $result);

        errores::$error = false;
    }

}
