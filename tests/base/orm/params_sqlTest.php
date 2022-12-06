<?php
namespace tests\base\orm;

use base\orm\params_sql;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class params_sqlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_group_by_sql(): void
    {
        errores::$error = false;
        $ps = new params_sql();
        $ps = new liberator($ps);


        $group_by = array();
        $resultado = $ps->group_by_sql($group_by);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $group_by = array();
        $group_by[] = '';
        $resultado = $ps->group_by_sql($group_by);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $group_by = array();
        $group_by[] = 'a';
        $resultado = $ps->group_by_sql($group_by);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' GROUP BY a ', $resultado);
        errores::$error = false;
    }

    public function test_limit_sql(): void
    {
        errores::$error = false;
        $ps = new params_sql();
        $ps = new liberator($ps);


        $limit = '1';
        $resultado = $ps->limit_sql($limit);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' LIMIT 1', $resultado);
        errores::$error = false;
    }

    public function test_offset_sql(): void
    {
        errores::$error = false;
        $ps = new params_sql();
        $ps = new liberator($ps);
        $offset = '1';
        $resultado = $ps->offset_sql($offset);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' OFFSET 1', $resultado);
        errores::$error = false;
    }



    public function test_order_sql(){
        errores::$error = false;

        $ps = new params_sql();
        $ps = new liberator($ps);

        $resultado = $ps->order_sql(array());
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
        $resultado = $ps->order_sql(array('x'));
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo debe ser txt', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $ps->order_sql(array('x'=>'x'));
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('ORDER BY x x', $resultado);
        errores::$error = false;

    }

    public function test_params_sql(): void
    {
        errores::$error = false;
        $ps = new params_sql();
        //$ps = new liberator($ps);

        $group_by = array();
        $limit = 1;
        $offset = 1;
        $order = array();
        $aplica_seguridad = false;
        $modelo = new adm_accion($this->link);
        $sql_where_previo = '';
        $resultado = $ps->params_sql($aplica_seguridad, $group_by, $limit, $modelo, $offset, $order, $sql_where_previo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $ps = new params_sql();
        //$ps = new liberator($ps);

        $group_by = array();
        $limit = 1;
        $offset = 1;
        $order = array('x'=>'a');
        $aplica_seguridad = false;
        $modelo = new adm_accion($this->link);
        $sql_where_previo = '';
        $resultado = $ps->params_sql($aplica_seguridad, $group_by, $limit, $modelo, $offset, $order, $sql_where_previo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' ORDER BY x a',$resultado->order);

        errores::$error = false;
    }

    public function test_seguridad(): void
    {
        errores::$error = false;
        $ps = new params_sql();
        $ps = new liberator($ps);

        $aplica_seguridad = false;
        $modelo = new adm_accion($this->link);
        $sql_where_previo = '';
        $resultado = $ps->seguridad($aplica_seguridad, $modelo, $sql_where_previo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty(trim($resultado));

        errores::$error = false;

        $aplica_seguridad = true;
        $modelo = new adm_accion($this->link);
        $modelo->columnas_extra['usuario_permitido_id'] = 1;
        $_SESSION['usuario_id'] = 1;
        $sql_where_previo = '';
        $resultado = $ps->seguridad($aplica_seguridad, $modelo, $sql_where_previo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('  WHERE  (1) = 1 ',$resultado);

        errores::$error = false;

        $aplica_seguridad = true;
        $modelo = new adm_accion($this->link);
        $modelo->columnas_extra['usuario_permitido_id'] = 1;
        $_SESSION['usuario_id'] = 1;
        $sql_where_previo = 'x';
        $resultado = $ps->seguridad($aplica_seguridad, $modelo, $sql_where_previo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('  (1) = 1 ',$resultado);
        errores::$error = false;
    }

    public function test_where(): void
    {
        errores::$error = false;
        $ps = new params_sql();
        $ps = new liberator($ps);


        $sql_where_previo = '';
        $resultado = $ps->where($sql_where_previo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' WHERE ',$resultado);

        errores::$error = false;

        $sql_where_previo = 'a';
        $resultado = $ps->where($sql_where_previo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);
        errores::$error = false;
    }



}