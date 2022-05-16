<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-16
 * @final En proceso
 *
 */
namespace gamboamartin\controllers;

use base\controller\init;
use base\seguridad;
use config\generales;
use base\controller\controlador_base;
use gamboamartin\errores\errores;
use JsonException;
use models\session;
use models\usuario;
use PDO;
use stdClass;


class controlador_session extends controlador_base{
    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $modelo = new session($link);
        parent::__construct(link: $link, modelo: $modelo,paths_conf:  $paths_conf);

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $datos_usuario
     * @return array|stdClass
     */
    public function alta_session(array $datos_usuario): array|stdClass
    { //FIN PROT
        if(count($datos_usuario) === 0){
            return $this->errores->error(mensaje: 'Error datos de usuario estan vacios',data: $datos_usuario);
        }
        if(!isset($datos_usuario['usuario_id'])){
            return $this->errores->error(mensaje:'Error datos de usuario_id no existe',data:$datos_usuario);
        }
        if((int)$datos_usuario['usuario_id']<=0){
            return $this->errores->error(mensaje:'Error datos de usuario_id debe ser mayor a 0',data:$datos_usuario);
        }
        if(!isset($datos_usuario['usuario_id'])){
            return $this->errores->error(mensaje:'Error datos de usuario_id no existe',data:$datos_usuario);
        }
        if(!isset($datos_usuario['grupo_id'])){
            return $this->errores->error(mensaje:'Error datos de grupo_id no existe',data:$datos_usuario);
        }
        if((int)$datos_usuario['grupo_id']<=0){
            return $this->errores->error(mensaje:'Error datos de grupo_id debe ser mayor a 0',data:$datos_usuario);
        }
        $session_modelo = new session($this->link);
        $session_insertar['name'] = (new generales())->session_id;
        $session_insertar['usuario_id'] = $datos_usuario['usuario_id'];
        $session_insertar['fecha'] = date('Y-m-d');
        $session_insertar['numero_empresa'] = 1;
        $session_insertar['fecha_ultima_ejecucion'] = time();
        $session_insertar['status'] = 'activo';
        $session_modelo->registro = $session_insertar;
        $r_alta = $session_modelo->alta_bd();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al dar de alta session',data: $r_alta);
        }
        return $r_alta;
    }


    /**
     * PRUEBAS FINALIZADAS
     * @param bool $header
     * @param bool $ws
     * @return array
     * @throws JsonException
     */
    public function denegado(bool $header, bool $ws = false):array{

        $error = $this->errores->error('Acceso denegado ',array());
        if(isset($_GET['ws'])){
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error, JSON_THROW_ON_ERROR);
            exit;
        }
        if($header) {
            print_r($error);
            die('Error');
        }
        return $error;
    }



    public function header_error($datos_validos, $web_service){ // Finalizado
        if(!is_array($datos_validos)){
            return array('error'=>1,'Los datos no son validos','line'=>__LINE__,'file'=>__FILE__);
        }
        if(!isset($datos_validos['error'])){
            return array('error'=>1,'Debe de existir key error como bool true o false','line'=>__LINE__,'file'=>__FILE__);
        }
        if($datos_validos['error'] == 1){
            if (!isset($_SESSION)) {
                session_destroy();
            }
            if(isset($_GET['prueba'])){
                return $datos_validos;
            }
            if($web_service) {
                header("Content-Type: application/json");
                $json = json_encode($datos_validos);
                echo $json;
                exit;
            }
            header("Location: ./index.php?seccion=session&accion=login&mensaje=$datos_validos[mensaje]&tipo_mensaje=error");
            exit;
        }
        return false;
    }

    /**
     * DEBUG INI
     * @throws JsonException
     */
    public function inicio(bool $aplica_template = true, bool $header = true, bool $ws = false): string|array
    {
        $template = '';
        if($aplica_template) {
            $template = $this->alta(header: false);
            if (errores::$error) {
                return $this->retorno_error('Error al generar template', $template, $header, $ws);
            }
        }
        return $template;

    }

    /**
     * DEBUG
     */
    public function login(bool $header = true, bool $ws = false): stdClass|array
    {
        $data = new stdClass();
        $data->header = $header;
        $data->ws = $ws;

        return $data;
    }

    public function logout(bool $header = true, bool $ws = false){
        $seguridad = new seguridad();
        $del = $seguridad->elimina_session($this->link);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al eliminar session', data: $del, header: $header,
                ws: $ws);
        }
        header('Location: index.php?seccion=session&accion=login');
        exit;
    }

    /**
     *
     */
    public function loguea(bool $header, bool $ws = false, string $accion_header = '', string $seccion_header = ''){

        $datos_validos = (new \validacion\session())->valida_datos_recepcion();
        if(errores::$error){
            if($seccion_header !== '' && $accion_header !== '' && $header) {
                $mensaje = $datos_validos['mensaje'];
                header("Location: ./index.php?seccion=$seccion_header&accion=$accion_header&mensaje=$mensaje&error=1");
                exit;
            }
            $this->retorno_error('Error al validar datos', $datos_validos, $header, $ws);

        }

        $_SESSION['numero_empresa'] = 1;

        $modelo_usuario = new usuario($this->link);
        $usuario = $modelo_usuario->valida_usuario_password(password:  $_POST['password'], usuario: $_POST['user']);
        if(errores::$error){
            if($seccion_header !== '' && $accion_header !== '' && $header) {
                $mensaje = $usuario['mensaje'];
                header("Location: ./index.php?seccion=$seccion_header&accion=$accion_header&mensaje=$mensaje&error=1");
                exit;
            }
            return $this->retorno_error(mensaje: 'Error al validar usuario',data:  $usuario, header: $header,ws:  $ws);
        }

        $_SESSION['activa'] = 1;
        $_SESSION['grupo_id'] = $usuario['grupo_id'];
        $_SESSION['usuario_id'] = $usuario['usuario_id'];


        $data_get = (new init())->asigna_session_get();
        if(errores::$error){
            if($seccion_header !== '' && $accion_header !== '' && $header) {
                $mensaje = $data_get['mensaje'];
                header("Location: ./index.php?seccion=$seccion_header&accion=$accion_header&mensaje=$mensaje&error=1");
                exit;
            }
            return $this->retorno_error(mensaje: 'Error al generar session_id', data: $data_get, header: $header,
                ws: $ws);
        }

        $r_alta  = $this->alta_session($usuario);
        if(errores::$error){
            if($seccion_header !== '' && $accion_header !== '' && $header) {
                $mensaje = $r_alta['mensaje'];
                header("Location: ./index.php?seccion=$seccion_header&accion=$accion_header&mensaje=$mensaje&error=1");
                exit;
            }
            return $this->retorno_error('Error al dar de alta session', $r_alta, $header, $ws);
        }

        header("Location: ./index.php?seccion=session&accion=inicio&mensaje=Bienvenido&tipo_mensaje=exito&session_id=".(new generales())->session_id);
        exit;
    }



    public function srv_login(){
        $datos_validos = (new \validacion\session())->valida_datos_recepcion();
        if(errores::$error){
            $this->header_error($datos_validos,true);
        }

        $_SESSION['numero_empresa'] = 1;

        $modelo_usuario = new usuario($this->link);
        $usuarios = $modelo_usuario->valida_usuario_password(password: $_POST['password'] , usuario: $_POST['user']);

        if($usuarios['error']){
            $resultado['mensaje'] = $usuarios['mensaje'];
            $resultado['error'] = true;
            session_destroy();
        }
        else{
            $datos_usuario = $usuarios['registros'];
            $_SESSION['activa'] = 1;
            $_SESSION['grupo_id'] = $datos_usuario[0]['grupo_id'];
            $_SESSION['usuario_id'] = $datos_usuario[0]['id'];



            ob_clean();
            $r_alta  = $this->alta_session($datos_usuario);
            if(isset($r_alta['error'])){
                $error =  $this->errores->error('Error al dar de alta session',$r_alta);

                header("Content-Type: application/json");
                $json = json_encode($r_alta);

                echo $json;
                exit;
            }

            $resultado['session_id'] = SESSION_ID;
        }
        header("Content-Type: application/json");

        $json = json_encode($resultado);

        echo $json;
        exit;
    }



}