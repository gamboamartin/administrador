<?php
namespace tests\base\orm;

use base\orm\sql;
use base\orm\sql_bass;
use base\orm\upd;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;
use models\adm_seccion;
use stdClass;


class updTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_agrega_usuario_session(){


        errores::$error = false;
        $upd = new upd();

        $upd = new liberator($upd);

        $modelo = new adm_accion($this->link);
        $modelo->usuario_id = 2;
        $modelo->campos_sql = 1;



        $resultado = $upd->agrega_usuario_session($modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_campos(): void
    {
        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);

        $modelo = new adm_seccion($this->link);
        $modelo->registro_upd['x'] = '';
        $resultado = $upd->campos($modelo);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("x = ''",$resultado);
        errores::$error = false;
    }

    public function test_ejecuta_upd(): void
    {
        errores::$error = false;
        $upd = new upd();
        //$sql = new liberator($sql);

        $id = 1;
        $modelo = new adm_accion($this->link);
        $resultado = $upd->ejecuta_upd($id, $modelo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Info no hay elementos a modificar',$resultado->resultado->mensaje);

        errores::$error = false;
        $upd = new upd();
        //$sql = new liberator($sql);

        $id = 1;

        $modelo = new adm_accion($this->link);
        $modelo->registro_upd['status'] = 'activo';
        $resultado = $upd->ejecuta_upd($id, $modelo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado->ejecuta_upd);
        $this->assertEquals('activo',$modelo->registro_upd['status']);

        errores::$error = false;
        $upd = new upd();
        //$sql = new liberator($sql);

        $id = 1;

        $modelo = new adm_accion($this->link);
        $modelo->registro_upd = array();
        $resultado = $upd->ejecuta_upd($id, $modelo);



        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado->ejecuta_upd);


        errores::$error = false;
    }

    public function test_maqueta_rows_upd(): void
    {
        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);

        $campo = 'a';
        $campos = '';
        $modelo = new adm_seccion($this->link);
        $value = '';
        $resultado = $upd->maqueta_rows_upd($campo, $campos, $modelo, $value);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a = ''", $resultado);
        errores::$error = false;

    }

    public function test_params_data_update(){


        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);

        $modelo = new adm_seccion($this->link);

        //$modelo->usuario_id = 100;
        $campo = 'a';
        $value = '';
        $resultado = $upd->params_data_update($campo, $modelo, $value);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_reactiva(): void
    {
        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);

        $reactiva = false;
        $modelo = new adm_accion($this->link);
        $registro = array();
        $resultado = $upd->reactiva($modelo, $reactiva, $registro);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_rows_update(): void
    {
        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);

        $campos = '';
        $params = new stdClass();
        $params->campo = 'a';
        $params->value = 'x';
        $resultado = $upd->rows_update($campos, $params);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a = x', $resultado);

        errores::$error = false;


        $campos = 'z';
        $params = new stdClass();
        $params->campo = 'a';
        $params->value = 'x';
        $resultado = $upd->rows_update($campos, $params);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z, a = x', $resultado);
        errores::$error = false;

    }

    public function test_slaches_value(): void
    {


        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);

        $campo = '"a';
        $value = "b'";
        $resultado = $upd->slaches_value($campo, $value);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('\"a', $resultado->campo);
        $this->assertEquals("b\'", $resultado->value);
        errores::$error = false;
    }

    public function test_usuario_existente(){


        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);

        $modelo = new adm_seccion($this->link);

        $modelo->usuario_id = 100;
        $resultado = $upd->usuario_existente($modelo);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_value_null()
    {


        errores::$error = false;
        $upd = new upd();
        $upd = new liberator($upd);


        $value = '';

        $resultado = $upd->value_null($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $value = 'x';

        $resultado = $upd->value_null($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $value = null;

        $resultado = $upd->value_null($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('NULL', $resultado);
        errores::$error = false;
    }



}