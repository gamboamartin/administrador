<?php
namespace gamboamartin\administrador\tests\orm;

use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_session;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class adm_usuarioTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_data_grupo(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $_SESSION['usuario_id'] = 2;

        $filtro = array();
        $filtro['adm_grupo.id'] = 2;
        $resultado = $modelo->data_grupo($filtro);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2, $resultado['adm_grupo_id']);
        errores::$error = false;

    }

    public function test_data_permiso(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = -1;

        $adm_accion = 'a';
        $adm_seccion = 'v';
        $resultado = $modelo->data_permiso($adm_accion, $adm_seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('v',$resultado->adm_seccion);
        $this->assertEquals('a',$resultado->adm_accion);
        errores::$error = false;
    }

    public function test_elimina_bd(): void
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $del = (new adm_usuario(link: $this->link))->elimina_todo();
        if (errores::$error) {
            $error = (new errores())->error('Error al eliminar usuario', $del);
            print_r($error);
            die('Error');
        }

        $id = 1;

        $usuario_ins['id'] = 1;
        $usuario_ins['adm_grupo_id'] = 2;
        $usuario_ins['user'] = 1;
        $usuario_ins['password'] = 2;
        $usuario_ins['email'] = 'a@a.com';
        $usuario_ins['telefono'] = 1235487596;
        $usuario_ins['nombre'] = 1235487596;
        $usuario_ins['ap'] = 1235487596;
        $r_alta_usuario = (new adm_usuario($this->link))->alta_registro($usuario_ins);
        if (errores::$error) {
            $error = (new errores())->error('Error al dar de alta usuario', $r_alta_usuario);
            print_r($error);
            die('Error');
        }

        $resultado = $modelo->elimina_bd($id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('DELETE FROM adm_usuario WHERE id = 1',$resultado->sql);
        errores::$error = false;

    }

    public function test_filtro(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = -1;

        $adm_accion = 'a';
        $adm_seccion = 'b';
        $adm_grupo_id = 1;
        $resultado = $modelo->filtro($adm_accion, $adm_grupo_id, $adm_seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['adm_grupo.id']);
        errores::$error = false;

        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $adm_accion = "accionValida";
        $adm_grupo_id = 1;
        $adm_seccion = "seccionValida";

        $expectedFilter = [
            'adm_grupo.id' => $adm_grupo_id,
            'adm_accion.descripcion' => $adm_accion,
            'adm_grupo.status' => 'activo',
            'adm_accion.status' => 'activo',
            'adm_seccion.descripcion' => $adm_seccion,
            'adm_seccion.status' => 'activo'
        ];

        $resultFilter = $modelo->filtro($adm_accion, $adm_grupo_id, $adm_seccion);

        $this->assertEquals($expectedFilter, $resultFilter);

        errores::$error = false;


    }

    public function test_filtro_seguridad(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $_SESSION['usuario_id'] = 2;

        $del = (new adm_usuario(link: $this->link))->elimina_todo();
        if (errores::$error) {
            $error = (new errores())->error('Error al eliminar usuario', $del);
            print_r($error);
            die('Error');
        }


        $usuario_ins['id'] = 2;
        $usuario_ins['adm_grupo_id'] = 2;
        $usuario_ins['user'] = 1;
        $usuario_ins['password'] = 2;
        $usuario_ins['email'] = 'a@a.com';
        $usuario_ins['telefono'] = 1235487596;
        $usuario_ins['nombre'] = 1235487596;
        $usuario_ins['ap'] = 1235487596;
        $r_alta_usuario = (new adm_usuario($this->link))->alta_registro($usuario_ins);
        if (errores::$error) {
            $error = (new errores())->error('Error al dar de alta usuario', $r_alta_usuario);
            print_r($error);
            die('Error');
        }


        $resultado = $modelo->filtro_seguridad('');
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_get_data_permiso(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $adm_accion = 'b';
        $adm_grupo_id = 1;
        $adm_seccion = 'a';

        $resultado = $modelo->get_data_permiso($adm_accion, $adm_grupo_id, $adm_seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_get_val_session(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $adm_grupo_id = 1;
        $data_permiso = new stdClass();
        $data_permiso->adm_accion = 'a';
        $data_permiso->adm_seccion = 'b';
        $resultado = $modelo->get_val_session($adm_grupo_id, $data_permiso);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado->existe);
        $this->assertEquals(0,$resultado->val_session);

        errores::$error = false;

        $adm_grupo_id = 2;
        $data_permiso = new stdClass();
        $data_permiso->adm_accion = 'lista';
        $data_permiso->adm_seccion = 'adm_accion';
        $resultado = $modelo->get_val_session($adm_grupo_id, $data_permiso);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado->existe);
        $this->assertEquals(1,$resultado->val_session);
        errores::$error = false;
    }

    public function test_registro(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $_SESSION['usuario_id'] = 2;

        $del = (new base_test())->del_adm_usuario(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $resultado = $modelo->registro(registro_id: 2, columnas_en_bruto: true, retorno_obj: true);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al obtener registro', $resultado['mensaje_limpio']);

        errores::$error = false;

        $alta = (new base_test())->alta_adm_usuario(link: $this->link, id: 2);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->registro(registro_id: 2, columnas_en_bruto: true, retorno_obj: true);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2, $resultado->id);
        $this->assertEquals('password', $resultado->password);
        $this->assertEquals('admin@test.com', $resultado->email);
        errores::$error = false;


    }

    public function test_tengo_permiso(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $_SESSION['usuario_id'] = -1;

        $adm_accion = '';
        $adm_seccion = '';
        $resultado = $modelo->tengo_permiso($adm_accion, $adm_seccion);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;

        $adm_accion = 'a';
        $adm_seccion = 'b';
        $resultado = $modelo->tengo_permiso($adm_accion, $adm_seccion);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

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
        $usuario_ins['user'] = 2;
        $usuario_ins['password'] = 2;
        $usuario_ins['email'] = 'a@a.com';
        $usuario_ins['telefono'] = 1235487596;
        $usuario_ins['nombre'] = 1235487596;
        $usuario_ins['ap'] = 1235487596;
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

    public function test_val_session(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $existe = false;
        $resultado = $modelo->val_session($existe);
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0,$resultado);

        errores::$error = false;



        $existe = true;
        $resultado = $modelo->val_session($existe);
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado);

        errores::$error = false;

    }

    public function test_val_session_existe(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $filtro = array('adm_grupo.id'=>1);
        $resultado = $modelo->val_session_existe($filtro);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_valida_datos_permiso(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $adm_accion = '';
        $adm_grupo_id = -1;
        $adm_seccion = '';

        $resultado = $modelo->valida_datos_permiso($adm_accion, $adm_grupo_id, $adm_seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error adm_seccion esta vacia',$resultado['mensaje_limpio']);
        errores::$error = false;

        $adm_accion = '';
        $adm_grupo_id = -1;
        $adm_seccion = 'a';

        $resultado = $modelo->valida_datos_permiso($adm_accion, $adm_grupo_id, $adm_seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error adm_accion esta vacia',$resultado['mensaje_limpio']);

        errores::$error = false;

        $adm_accion = 'b';
        $adm_grupo_id = -1;
        $adm_seccion = 'a';

        $resultado = $modelo->valida_datos_permiso($adm_accion, $adm_grupo_id, $adm_seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error adm_grupo_id debe ser mayor a 0',$resultado['mensaje_limpio']);

        errores::$error = false;

        $adm_accion = 'b';
        $adm_grupo_id = 1;
        $adm_seccion = 'a';

        $resultado = $modelo->valida_datos_permiso($adm_accion, $adm_grupo_id, $adm_seccion);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }

    public function test_valida_usuario_password(): void
    {

        errores::$error = false;
        $modelo = new adm_usuario($this->link);
        //$inicializacion = new liberator($inicializacion);

        $password = 'b';
        $usuario = 'a';

        $resultado = $modelo->valida_usuario_password($password, $usuario);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al validar usuario y pass',$resultado['mensaje_limpio']);
        errores::$error = false;
    }





}

