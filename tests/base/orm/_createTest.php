<?php

use base\orm\_create;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use PHPUnit\Framework\TestCase;


class _createTest extends TestCase
{
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
