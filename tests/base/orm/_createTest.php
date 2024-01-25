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

    public function test_atributos()
    {
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $atributos = new stdClass();
        $result = $_create->atributos($atributos);
        $this->assertEquals('VARCHAR', $result->tipo_dato);
        $this->assertEquals('255', $result->longitud);
        $this->assertEquals('NOT NULL', $result->not_null);

        errores::$error = false;

        $atributos = new stdClass();
        $atributos->tipo_dato = 'TIMESTAMP';
        $result = $_create->atributos($atributos);
        $this->assertEquals('TIMESTAMP', $result->tipo_dato);
        $this->assertEquals('', $result->longitud);
        $this->assertEquals('NOT NULL', $result->not_null);
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

    public function test_atributos_fecha_base()
    {
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $campos = new stdClass();
        $result = $_create->atributos_fecha_base($campos);
        //print_r($result);exit;
        $this->assertEquals('TIMESTAMP', $result->fecha_alta->tipo_dato);
        $this->assertEquals('CURRENT_TIMESTAMP', $result->fecha_alta->default);

        $this->assertEquals('TIMESTAMP', $result->fecha_update->tipo_dato);
        $this->assertEquals('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', $result->fecha_update->default);


        errores::$error = false;

    }

    public function test_atributos_integer()
    {
        errores::$error = false;
        $_create = new _create();
        $_create = new liberator($_create);

        $campos = new stdClass();
        $campos_integer = array();
        $campos_integer[] = 'a';
        $result = $_create->atributos_integer($campos, $campos_integer);
        $this->assertEquals('INT', $result->a->tipo_dato);

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

    public function test_campos_base(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        //$_create = new liberator($_create);

        $campos = new stdClass();
        $result = $_create->campos_base($campos);
        //print_r($result);exit;
        $this->assertTrue( $result->codigo->unique);
        $this->assertIsObject( $result->descripcion);
        $this->assertEquals('activo', $result->status->default);
        $this->assertEquals('INT', $result->usuario_alta_id->tipo_dato);
        $this->assertEquals('INT', $result->usuario_update_id->tipo_dato);
        $this->assertEquals('TIMESTAMP', $result->fecha_alta->tipo_dato);
        $this->assertEquals('CURRENT_TIMESTAMP', $result->fecha_alta->default);
        $this->assertEquals('TIMESTAMP', $result->fecha_update->tipo_dato);
        $this->assertEquals('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', $result->fecha_update->default);
        $this->assertEquals('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', $result->fecha_update->default);
        $this->assertIsObject( $result->descripcion_select);
        $this->assertIsObject( $result->alias);
        $this->assertIsObject( $result->codigo_bis);
        $this->assertEquals( 'inactivo',$result->predeterminado->default);

        errores::$error = false;
    }

    public function test_integra_longitud(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $atributos_base = new stdClass();
        $result = $_create->integra_longitud($atributos_base);
        $this->assertEquals("(255)", $result);

        $atributos_base = new stdClass();
        $atributos_base->tipo_dato = 'TIMESTAMP';
        $result = $_create->integra_longitud($atributos_base);
        $this->assertEquals("", $result);

        errores::$error = false;
    }

    public function test_longitud_sql(){
        errores::$error = false;
        // Arrange (Organizar)
        $_create = new _create();
        $_create = new liberator($_create);

        $atributos_base = new stdClass();
        //$atributos_base->longitud = 'a';
        $result = $_create->longitud_sql($atributos_base);
        $this->assertEquals("(255)", $result);

        errores::$error = false;

        $atributos_base = new stdClass();
        $atributos_base->longitud = 'a';
        $result = $_create->longitud_sql($atributos_base);
        $this->assertEquals("(a)", $result);

        errores::$error = false;

        $atributos_base = new stdClass();
        $atributos_base->longitud = 'a';
        $atributos_base->tipo_dato = 'TIMESTAMP';
        $result = $_create->longitud_sql($atributos_base);
        $this->assertEquals("", $result);

        errores::$error = false;
    }

}
