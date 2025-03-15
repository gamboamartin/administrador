<?php
namespace tests\base\orm;

use base\orm\val_sql;
use gamboamartin\administrador\models\adm_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class val_sqlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_campo_existe(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $campo = '';
        $keys_ids = array();
        $resultado = $val->campo_existe($campo, $keys_ids, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key invalido', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $campo = 'a';
        $keys_ids = array();
        $resultado = $val->campo_existe($campo, $keys_ids, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        errores::$error = false;

        $registro = array();
        $campo = 'a';
        $keys_ids = array();
        $registro[] = '';
        $resultado = $val->campo_existe($campo, $keys_ids, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        errores::$error = false;

        $registro = array();
        $campo = 'a';
        $keys_ids = array();
        $registro[] = '';
        $keys_ids[] = '';
        $resultado = $val->campo_existe($campo, $keys_ids, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar si existe', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $campo = 'a';
        $keys_ids = array();
        $registro[] = '';
        $keys_ids[] = 'a';
        $resultado = $val->campo_existe($campo, $keys_ids, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar si existe', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $campo = 'a';
        $keys_ids = array();
        $registro['a'] = '';

        $keys_ids[] = 'a';
        $resultado = $val->campo_existe($campo, $keys_ids, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);
        errores::$error = false;
    }

    public function test_checked(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $keys_checked = array();
        $resultado = $val->checked($keys_checked, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $registro = array();
        $keys_checked = array();
        $keys_checked[] = '';
        $resultado = $val->checked($keys_checked, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar campo', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_checked = array();
        $keys_checked[] = 'a';
        $resultado = $val->checked($keys_checked, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar campo', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_checked = array();
        $keys_checked[] = 'a';
        $registro['a'] ='';
        $resultado = $val->checked($keys_checked, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar campo', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_checked = array();
        $keys_checked[] = 'a';
        $registro['a'] ='activo';
        $resultado = $val->checked($keys_checked, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_data_vacio(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $keys_obligatorios = array();
        $campo = 'a';
        $resultado = $val->data_vacio($campo, $keys_obligatorios, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);
        errores::$error = false;
    }

    public function test_existe(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $keys_obligatorios = array();
        $resultado = $val->existe($keys_obligatorios, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = '';
        $resultado = $val->existe($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar si existe campo', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios['a'] = '';
        $resultado = $val->existe($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar si existe campo', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios['a'] = 'a';
        $resultado = $val->existe($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar si existe campo', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios['a'] = 'a';
        $registro['a'] = '1';
        $resultado = $val->existe($keys_obligatorios, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_existe_duplicado(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $modelo = new adm_grupo($this->link);
        $campo = 'status';
        $registro = array();
        $tabla = 'adm_grupo';
        $registro['status'] = 'activo';
        $resultado = $val->existe_duplicado($campo,$modelo,$registro, $tabla);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error ya existe un registro con el campo status',$resultado['mensaje_limpio']);
        errores::$error = false;
    }

    public function test_filtro_no_duplicado(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $campo = 'a';
        $registro = array();
        $tabla = 'b';
        $registro['a'] = 'a';
        $resultado = $val->filtro_no_duplicado($campo,$registro,$tabla);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['b.a']);
        errores::$error = false;
    }

    public function test_ids(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $keys_ids = array();
        $resultado = $val->ids($keys_ids, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $registro = array();
        $keys_ids = array();
        $keys_ids[] = '';
        $resultado = $val->ids($keys_ids, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar campo ids', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_ids = array();
        $keys_ids[] = 'a';
        $resultado = $val->ids($keys_ids, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar campo ids', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_ids = array();
        $keys_ids[] = 'a';
        $registro['a'] = 'a';
        $resultado = $val->ids($keys_ids, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar campo ids', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $keys_ids = array();
        $keys_ids[] = 'a';
        $registro['a'] = '10';
        $resultado = $val->ids($keys_ids, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_limpia_data_tipo_campo(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $tipo_campo = '';
        $key = '';
        $resultado = $val->limpia_data_tipo_campo($key, $tipo_campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error en key de tipo campo',$resultado['mensaje']);

        errores::$error = false;

        $tipo_campo = '';
        $key = 'a';
        $resultado = $val->limpia_data_tipo_campo($key, $tipo_campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error en $tipo_campo de tipo campo',$resultado['mensaje']);

        errores::$error = false;

        $tipo_campo = 'a';
        $key = 'a';
        $resultado = $val->limpia_data_tipo_campo($key, $tipo_campo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_obligatorios(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $keys_obligatorios = array();
        $resultado = $val->obligatorios($keys_obligatorios, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = '';
        $resultado = $val->obligatorios($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campos no existe', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = 'a';
        $resultado = $val->obligatorios($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campos no existe', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = 'a';
        $registro[] = '';
        $resultado = $val->obligatorios($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campos no existe', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = 'a';
        $registro['a'] = '';
        $resultado = $val->obligatorios($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('>Error al validar campo vacio', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = 'a';
        $registro['a'] = 'x';
        $resultado = $val->obligatorios($keys_obligatorios, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_tabla(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $resultado = $val->tabla('a');
        $this->assertTrue( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }


    public function test_tipo_campos(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $tipo_campos = array();
        $resultado = $val->tipo_campos($registro, $tipo_campos);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $registro = array();
        $tipo_campos = array();
        $tipo_campos[] = '';
        $resultado = $val->tipo_campos($registro, $tipo_campos);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campos', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $tipo_campos = array();
        $tipo_campos['a'] = '';
        $resultado = $val->tipo_campos($registro, $tipo_campos);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campos', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $tipo_campos = array();
        $tipo_campos['a'] = 'a';
        $resultado = $val->tipo_campos($registro, $tipo_campos);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campos', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $tipo_campos = array();
        $tipo_campos['a'] = 'a';
        $registro[] = '';
        $resultado = $val->tipo_campos($registro, $tipo_campos);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

    }

    public function test_txt_valido(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $txt = '';
        $resultado = $val->txt_valido($txt);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $txt no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $txt = '9.9';
        $resultado = $val->txt_valido($txt);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el $txt es numero debe se un string', $resultado['mensaje']);

        errores::$error = false;

        $txt = '9za_a';
        $resultado = $val->txt_valido($txt);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('9za_a', $resultado);
        errores::$error = false;
    }

    public function test_vacio(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $keys_obligatorios = array();
        $resultado = $val->vacio($keys_obligatorios, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = '';
        $resultado = $val->vacio($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar vacio', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = 'a';
        $resultado = $val->vacio($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar vacio', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = 'a';
        $registro['a'] = '';
        $resultado = $val->vacio($keys_obligatorios, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar vacio', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $keys_obligatorios = array();
        $keys_obligatorios[] = 'a';
        $registro['a'] = 'x';
        $resultado = $val->vacio($keys_obligatorios, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_base_alta(): void
    {
        errores::$error = false;
        $val = new val_sql();
        //$val = new liberator($val);

        $registro = array();
        $tipo_campos = array();
        $campos_obligatorios = array();
        $no_duplicados = array();
        $modelo = new adm_seccion($this->link);
        $tabla = 'a';
        $registro[] = '';
        $resultado = $val->valida_base_alta($campos_obligatorios, $modelo, $no_duplicados, $registro, $tabla,
            $tipo_campos, array());
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_estructura_campos(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $tipo_campos = array();
        $resultado = $val->valida_estructura_campos($registro, $tipo_campos);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_verifica_base(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $campo = 'x';
        $keys = array();
        $registro['x'] = '1';
        $pattern_rev = 'id';
        $resultado = $val->verifica_base($campo,$keys,$pattern_rev,$registro);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);


        $registro = array();
        $campo = 'x';
        $keys = array();
        $registro['x'] = 'AAA';
        $pattern_rev = 'cod_3_letras_mayusc';
        $resultado = $val->verifica_base($campo,$keys,$pattern_rev,$registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }

    public function test_verifica_chk(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $campo = 'a';
        $keys_checked = array();
        $registro = array();
        $registro['a'] = 'activo';
        $resultado = $val->verifica_chk($campo, $keys_checked, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_verifica_cod_3_mayusc(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $campo = 'a';
        $keys_cod_3_mayus = array();
        $registro['a'] = 'AAA';
        $resultado = $val->verifica_cod_3_mayusc($campo,$keys_cod_3_mayus,$registro);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);


        errores::$error = false;
    }

    public function test_verifica_estructura(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $campos_obligatorios = array();
        $tipo_campos = array();
        $tabla = '';
        $resultado = $val->verifica_estructura($campos_obligatorios, $registro, $tabla, $tipo_campos);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_verifica_existe(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $campo = '';
        $resultado = $val->verifica_existe($campo, $registro);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al limpiar campo invalido',$resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $campo = 'z';
        $resultado = $val->verifica_existe($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $registro[z] debe existir',$resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $campo = 'z';
        $registro['z'] ='a';
        $resultado = $val->verifica_existe($campo, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_verifica_id(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $campo = 'x';
        $keys_ids = array();
        $registro['x'] = '1';
        $resultado = $val->verifica_id($campo, $keys_ids, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }


    public function test_verifica_no_duplicado(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $modelo = new adm_grupo($this->link);
        $registro = array();
        $tabla = 'adm_grupo';
        $no_duplicados = array();
        $no_duplicados[] = 'id';
        $registro['id'] = '-1';
        $resultado = $val->verifica_no_duplicado($modelo,$no_duplicados,$registro, $tabla);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_verifica_parent(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $modelo = new adm_seccion($this->link);
        $parent = 'gamboamartin\administrador\models\adm_menu';
        $registro = array();
        $registro['adm_menu_id'] = 1;
        $resultado = $val->verifica_parent($modelo,$parent,$registro);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_verifica_parents(): void
    {
        errores::$error = false;
        $val = new val_sql();
        //$val = new liberator($val);

        $modelo = new adm_seccion($this->link);
        $parents = array();
        $registro = array();
        $registro['adm_menu_id'] = 1;
        $parents[] = 'gamboamartin\administrador\models\adm_menu';
        $resultado = $val->verifica_parents($modelo,$parents,$registro);
        //print_r($resultado);exit;

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_verifica_tipo_dato(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array('');
        $key = 'B';
        $tipo_campo = 'A';
        $resultado = $val->verifica_tipo_dato($key, $registro, $tipo_campo);


        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_verifica_vacio(): void
    {
        errores::$error = false;
        $val = new val_sql();
        $val = new liberator($val);

        $registro = array();
        $keys_obligatorios = array();
        $campo = 'a';
        $registro['a'] = 'x';
        $resultado = $val->verifica_vacio($campo, $keys_obligatorios, $registro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }


}