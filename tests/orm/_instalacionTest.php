<?php
namespace gamboamartin\administrador\tests\orm;

use base\orm\_base;
use gamboamartin\administrador\models\_base_accion;
use gamboamartin\administrador\models\_instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _instalacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_campo_double(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $campos = new stdClass();
        $name_campo = 'a';
        $resultado = $ins->campo_double($campos, $name_campo);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('double',$resultado->a->tipo_dato);
        $this->assertEquals('0',$resultado->a->default);
        $this->assertEquals('100,2',$resultado->a->longitud);

        errores::$error = false;
    }
    public function test_campos_double_default(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $campos = new stdClass();
        $name_campos = array();
        $name_campos[] = 'a';
        $resultado = $ins->campos_double_default($campos, $name_campos);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('double',$resultado->a->tipo_dato);
        $this->assertEquals('0',$resultado->a->default);
        $this->assertEquals('100,2',$resultado->a->longitud);

        errores::$error = false;
    }
    public function test_describe_table(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'a';
        $resultado = $ins->describe_table($table);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }
    public function test_existe_campo_origen(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $campo_integrar = 'a';
        $campos_origen = array();
        $resultado = $ins->existe_campo_origen($campo_integrar, $campos_origen);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);
        errores::$error = false;

        $campo_integrar = 'a';
        $campos_origen[]['Field'] = 'a';
        $resultado = $ins->existe_campo_origen($campo_integrar, $campos_origen);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }
    public function test_existe_indice_by_name(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $table = 'adm_seccion';
        $name_index = 'PRIMARY';
        $resultado = $ins->existe_indice_by_name(name_index: $name_index,table: $table);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);


        errores::$error = false;
    }
    public function test_ver_indices(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $table = 'adm_seccion';
        $resultado = $ins->ver_indices($table);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PRIMARY', $resultado->registros[0]['Key_name']);

        errores::$error = false;
    }





}

