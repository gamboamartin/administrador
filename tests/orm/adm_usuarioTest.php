<?php
namespace tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use models\adm_bitacora;
use models\adm_session;
use models\adm_usuario;


class adm_usuarioTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_filtro_seguridad(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $_SESSION['usuario_id'] = 2;

        $resultado = $modelo->filtro_seguridad('');
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_usuario(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $usuario_id = -1;

        $resultado = adm_usuario::usuario($usuario_id, $this->link);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error usuario_id debe ser mayor a 0', $resultado['mensaje']);

        errores::$error = false;


        $usuario_id = 9999999999999999;

        $resultado = adm_usuario::usuario($usuario_id, $this->link);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener usuario', $resultado['mensaje']);
        errores::$error = false;

        $_SESSION['usuario_id'] = 1;

        $existe_usuario = $modelo->existe(array('adm_usuario.id'=>2));
        if(errores::$error){
            $error = (new errores())->error('Error al validar usuario', $existe_usuario);
            print_r($error);
            die('Error');
        }

        if($existe_usuario) {

            $del_session = (new adm_session($this->link))->elimina_todo();
            if (errores::$error) {
                $error = (new errores())->error('Error al eliminar $del_session', $del_session);
                print_r($error);
                die('Error');
            }

            $del_adm_bitacora = (new adm_bitacora($this->link))->elimina_todo();
            if (errores::$error) {
                $error = (new errores())->error('Error al eliminar bitacoras', $del_adm_bitacora);
                print_r($error);
                die('Error');
            }

            $del_usuario = $modelo->elimina_bd(2);
            if (errores::$error) {
                $error = (new errores())->error('Error al eliminar usuario', $del_usuario);
                print_r($error);
                die('Error');
            }
        }

        $usuario_ins['id'] = 2;
        $usuario_ins['adm_grupo_id'] = 2;
        $r_alta_usuario = $modelo->alta_registro($usuario_ins);
        if (errores::$error) {
            $error = (new errores())->error('Error al dar de alta usuario', $r_alta_usuario);
            print_r($error);
            die('Error');
        }


        $usuario_id = 2;

        $resultado = adm_usuario::usuario($usuario_id, $this->link);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('2', $resultado['adm_usuario_id']);



        errores::$error = false;


    }

    public function test_usuario_activo(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $_SESSION['usuario_id'] = 2;

        $resultado = $modelo->usuario_activo();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('2', $resultado['adm_usuario_id']);
        errores::$error = false;
    }

    public function test_usuarios_por_grupo(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $_SESSION['usuario_id'] = 2;
        $adm_grupo_id = 1;

        $resultado = $modelo->usuarios_por_grupo($adm_grupo_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $adm_grupo_id = 2;

        $resultado = $modelo->usuarios_por_grupo($adm_grupo_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);

        errores::$error = false;
    }





}

