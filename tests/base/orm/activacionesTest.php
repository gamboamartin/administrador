<?php
namespace tests\base;

use base\orm\activaciones;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_dia;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;


class activacionesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }



    public function test_init_activa()
    {
        errores::$error = false;
        $act = new activaciones();
        //$act = new liberator($act);

        $modelo = new adm_accion_grupo($this->link);
        $modelo->registro_id = 1;
        $reactiva = true;
        $resultado = $act->init_activa($modelo, $reactiva);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_maqueta_activacion()
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $act = new activaciones();
        $act = new liberator($act);
        $modelo = new adm_campo($this->link);

        $modelo->registro_id = 1;


        $existe = $modelo->existe(array('adm_campo.id'=>1));
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar adm campo ', data: $existe);
            print_r($error);
            die('Error');
        }


        if($existe) {

            $r_del_campo = $modelo->elimina_bd(1);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar dia ', data: $r_del_campo);
                print_r($error);
                die('Error');
            }
        }

        $registro['id'] = 1;
        $registro['descripcion'] = 1;
        $registro['adm_seccion_id'] = 1;
        $registro['adm_tipo_dato_id'] = 1;
        $r_alta_campo = $modelo->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar campo', data: $r_alta_campo);
            print_r($error);
            die('Error');
        }

        $resultado = $act->maqueta_activacion($modelo, false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("UPDATE adm_campo SET status = 'activo' WHERE id = 1", $resultado->consulta);
        $this->assertEquals("ACTIVA", $resultado->transaccion);
        errores::$error = false;
    }

    public function test_normaliza_name_model(){
        errores::$error = false;
        $act = new activaciones();
        $act = new liberator($act);

        $modelo = new adm_accion_grupo($this->link);
        $resultado = $act->normaliza_name_model($modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion_grupo', $resultado);

        errores::$error = false;

        $modelo->tabla = '';
        $resultado = $act->normaliza_name_model($modelo);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error el atributo tabla del modelo  Esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $modelo->tabla = 'x';
        $resultado = $act->normaliza_name_model($modelo);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;

        $modelo->tabla = 'models\\x';
        $resultado = $act->normaliza_name_model($modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);


        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_sql_activa()
    {
        errores::$error = false;
        $act = new activaciones();
        $act = new liberator($act);

        $resultado = $act->sql_activa(1, 'x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("UPDATE x SET status = 'activo' WHERE id = 1", $resultado);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_valida_activacion(){
        errores::$error = false;
        $act = new activaciones();

        $_SESSION['usuario_id'] = 1;
        $act = new liberator($act);

        $modelo = new adm_dia($this->link);
        $modelo->registro_id = 1;



        $r_del_dia = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar dia', data: $r_del_dia);
            print_r($error);
            die('Error');
        }

        $modelo->aplica_transaccion_inactivo = false;

        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $r_alta_dia = $modelo->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar dia', data: $r_alta_dia);
            print_r($error);
            die('Error');
        }

        $r_desactiva_dia = $modelo->desactiva_bd();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al desactivar dia', data: $r_desactiva_dia);
            print_r($error);
            die('Error');
        }


        $resultado = $act->valida_activacion($modelo);


        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar transaccion activa en adm_dia", $resultado['mensaje']);


        errores::$error = false;
        $modelo->aplica_transaccion_inactivo = true;

        $resultado = $act->valida_activacion($modelo);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }

    /**
     * @throws JsonException
     */
    public function test_verifica_reactivacion()
    {
        errores::$error = false;
        $act = new activaciones();

        $act = new liberator($act);

        $modelo = new adm_dia($this->link);
        $modelo->registro_id = 1;
        $modelo->aplica_transaccion_inactivo = false;
        $resultado = $act->verifica_reactivacion($modelo, false);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar transaccion activa en adm_dia", $resultado['mensaje']);

        errores::$error = false;

        $modelo = new adm_dia($this->link);
        $modelo->registro_id = 1;
        $modelo->aplica_transaccion_inactivo = true;
        $resultado = $act->verifica_reactivacion($modelo, false);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }


}