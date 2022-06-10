<?php
namespace tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use models\elemento_lista;
use models\usuario;


class usuarioTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_usuario(): void
    {

        errores::$error = false;
        $modelo = new usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $usuario_id = -1;

        $resultado = usuario::usuario($usuario_id, $this->link);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error usuario_id debe ser mayor a 0', $resultado['mensaje']);

        errores::$error = false;


        $usuario_id = 9999999999999999;

        $resultado = usuario::usuario($usuario_id, $this->link);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener usuario', $resultado['mensaje']);
        errores::$error = false;

        $_SESSION['usuario_id'] = 1;

        $existe_usuario = $modelo->existe(array('usuario.id'=>2));
        if(errores::$error){
            $error = (new errores())->error('Error al validar usuario', $existe_usuario);
            print_r($error);
            die('Error');
        }

        if($existe_usuario) {

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

        $resultado = usuario::usuario($usuario_id, $this->link);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('2', $resultado['usuario_id']);



        errores::$error = false;


    }





}

