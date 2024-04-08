<?php
namespace tests\src;


use base\orm\_result;
use base\orm\_where;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _whereTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_genera_where_seguridad(): void
    {
        errores::$error = false;
        $wh = new _where();
        $wh = new liberator($wh);

        $modelo = new adm_seccion(link: $this->link);
        $where = '';
        $resultado = $wh->genera_where_seguridad(where: $where,modelo: $modelo);
        //print_r($resultado);exit;
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;

        $where = 'x';
        $resultado = $wh->genera_where_seguridad(where: $where,modelo: $modelo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;
    }

    public function test_integra_where_seguridad(): void
    {
        errores::$error = false;
        $wh = new _where();
        $wh = new liberator($wh);

        $modelo = new adm_seccion(link: $this->link);

        $where = '';
        $consulta = '';
        $resultado = $wh->integra_where_seguridad(consulta: $consulta, modelo: $modelo, where: $where);
        // print_r($resultado);exit;

        //print_r($resultado);exit;

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;


    }

    public function test_where_campo_llave(): void
    {
        errores::$error = false;
        $wh = new _where();
        $wh = new liberator($wh);

        $registro_id = -1;
        $campo_llave = 'd';
        $tabla = 'z';
        $resultado = $wh->where_campo_llave($campo_llave, $registro_id, $tabla);
        // print_r($resultado);exit;
        //print_r($resultado);exit;
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(" WHERE z.d = -1 ",$resultado);
        errores::$error = false;
    }

    public function test_where_inicial(): void
    {
        errores::$error = false;
        $wh = new _where($this->link);
        $wh = new liberator($wh);

        $registro_id = -1;
        $campo_llave = '';
        $tabla = 'z';
        $resultado = $wh->where_inicial($campo_llave, $registro_id, $tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' WHERE z.id = -1 ',$resultado);
        errores::$error = false;

        $registro_id = -1;
        $campo_llave = 'd';
        $tabla = 'z';
        $resultado = $wh->where_inicial($campo_llave, $registro_id, $tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' WHERE z.d = -1 ',$resultado);
        errores::$error = false;

    }

    public function test_where_seguridad(): void
    {
        errores::$error = false;
        $wh = new _where($this->link);
        $wh = new liberator($wh);

        $modelo = new adm_seccion(link: $this->link);

        $seguridad = '';
        $where = '';
        $resultado = $wh->where_seguridad(seguridad: $seguridad,where:  $where,modelo: $modelo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;
        $wh = new _where($this->link);
        $modelo->aplica_seguridad = true;
        $wh = new liberator($wh);
        $seguridad = 'x';
        $where = '';
        $resultado = $wh->where_seguridad(seguridad: $seguridad,where:  $where,modelo: $modelo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('  WHERE x  ',$resultado);

        errores::$error = false;
        $wh = new _where($this->link);
        $modelo->aplica_seguridad = true;
        $wh = new liberator($wh);
        $seguridad = 'x';
        $where = 'b';
        $resultado = $wh->where_seguridad(seguridad: $seguridad,where:  $where,modelo: $modelo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' b AND x  ',$resultado);
        errores::$error = false;
    }


}