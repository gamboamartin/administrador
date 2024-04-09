<?php
namespace tests\base;

use base\orm\bitacoras;
use gamboamartin\administrador\instalacion\instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use stdClass;


class bitacorasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_aplica_bitacora(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = -1;
        $consulta = '';
        $funcion = '';
        $registro_id = 1;
        $tabla = 'adm_seccion';


        $resultado = $bitacora->aplica_bitacora($consulta, $funcion, $modelo, $registro_id, $tabla);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_asigna_registro_para_bitacora(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = 1;
        $consulta = 'b';
        $funcion = 'a';
        $registro = array();
        $seccion_menu = array();
        $seccion_menu['adm_seccion_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $resultado = $bitacora->asigna_registro_para_bitacora($consulta, $funcion, $modelo, $registro, $seccion_menu);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['transaccion']);
        errores::$error = false;
    }

    public function test_bitacora(){

        errores::$error = false;
        $bitacora = new bitacoras();
        //$bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = -1;
        $consulta = '';
        $funcion = '';
        $registro =  array();


        $resultado = $bitacora->bitacora($consulta, $funcion, $modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
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

    public function test_ejecuta_transaccion(){

        errores::$error = false;
        $bitacora = new bitacoras();
        //$bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $modelo->consulta = 'SELECT 1 FROM adm_seccion';
        $modelo->registro_id  = -1;
        $consulta = '';
        $funcion = '';
        $registro_id = 1;
        $tabla = 'adm_seccion';


        $resultado = $bitacora->ejecuta_transaccion($tabla, $funcion , $modelo, $registro_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_genera_bitacora(){

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;

        $instala = (new instalacion())->instala(link: $this->link);
        if(errores::$error){
            $error =  (new errores())->error(mensaje: 'Error al instalar',data:  $instala);
            print_r($error);
            exit;
        }



        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_campo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }



        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_bitacora($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 3;
        $adm_seccion['descripcion'] = 'adm_accion_grupo';
        $adm_seccion['adm_menu_id'] = '1';
        $adm_seccion['adm_namespace_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = 1;
        $consulta = 'b';
        $funcion = 'a';
        $registro = array();

        $_SESSION['usuario_id'] = 2;
        $resultado = $bitacora->genera_bitacora($consulta, $funcion, $modelo, $registro);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_maqueta_data_bitacora(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = 1;
        $consulta = 'b';
        $funcion = 'a';
        $registro = array();

        $_SESSION['usuario_id'] = 2;
        $resultado = $bitacora->maqueta_data_bitacora($consulta, $funcion, $modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

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

    public function test_valida_data_bitacora(){

        errores::$error = false;
        $bitacora = new bitacoras();
        $bitacora = (new liberator($bitacora));
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = -1;
        $consulta = '';
        $funcion = '';
        $data_ns = new stdClass();



        $resultado = $bitacora->valida_data_bitacora($consulta, $data_ns, $funcion, $modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al al validar data_ns', $resultado['mensaje']);

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = -1;
        $consulta = '';
        $funcion = '';
        $data_ns = new stdClass();
        $data_ns->tabla = 'x';

        errores::$error = false;

        $resultado = $bitacora->valida_data_bitacora($consulta, $data_ns, $funcion, $modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $funcion no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = -1;
        $consulta = '';
        $funcion = 'x';
        $data_ns = new stdClass();
        $data_ns->tabla = 'x';
        $resultado = $bitacora->valida_data_bitacora($consulta, $data_ns, $funcion, $modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $consulta no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = -1;
        $consulta = 'y';
        $funcion = 'x';
        $data_ns = new stdClass();
        $data_ns->tabla = 'x';
        $resultado = $bitacora->valida_data_bitacora($consulta, $data_ns, $funcion, $modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el id de $this->registro_id no puede ser menor a 0', $resultado['mensaje']);

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id  = 1;
        $consulta = 'y';
        $funcion = 'x';
        $data_ns = new stdClass();
        $data_ns->tabla = 'x';
        $resultado = $bitacora->valida_data_bitacora($consulta, $data_ns, $funcion, $modelo);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }


}