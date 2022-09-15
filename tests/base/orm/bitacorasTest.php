<?php
namespace tests\base;

use base\controller\normalizacion;
use base\orm\activaciones;
use base\orm\atributos;
use base\orm\bitacoras;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\adm_accion;
use models\adm_accion_grupo;
use models\adm_campo;
use models\adm_dia;
use models\atributo;


class bitacorasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_clase_namespace(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $tabla = '';
        $resultado = $bitacora->clase_namespace($tabla);


        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsString('Error tabla vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $resultado = $bitacora->clase_namespace($tabla);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado->tabla);
        $this->assertEquals('models\a',$resultado->clase);

        errores::$error = false;

        $tabla = 'models\\';
        $resultado = $bitacora->clase_namespace($tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsString('Error tabla vacia o mal escrita',$resultado['mensaje']);


        errores::$error = false;

    }
    public function test_data_ns_val(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $tabla = '';
        $resultado = $bitacora->data_ns_val($tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsString('Error tabla vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'x';
        $resultado = $bitacora->data_ns_val($tabla);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado->tabla);
        $this->assertEquals('models\x',$resultado->clase);

        errores::$error = false;

        $tabla = 'models\\';
        $resultado = $bitacora->data_ns_val($tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsString('Error al generar namespace modelo',$resultado['mensaje']);

        errores::$error = false;
    }

    public function test_obten_seccion_bitacora(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $resultado = $bitacora->obten_seccion_bitacora($modelo);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_val_bitacora(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $consulta = 'x';
        $funcion = 'x';
        $modelo = new adm_accion($this->link);
        $modelo->registro_id = 1;
        $resultado = $bitacora->val_bitacora($consulta, $funcion, $modelo);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }


}