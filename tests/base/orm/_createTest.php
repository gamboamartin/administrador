<?php
namespace tests\base;
use base\orm\_create;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use PHPUnit\Framework\TestCase;
use stdClass;


class _createTest extends TestCase
{

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
