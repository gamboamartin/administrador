<?php
namespace base\controller;
use base\conexion;
use base\seguridad;
use config\generales;
use controllers\controlador_session;
use gamboamartin\errores\errores;
use models\accion;
use models\session;
use PDO;
use stdClass;

class init{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * P INT P ORDER
     * @param PDO $link
     * @param string $seccion
     * @return controler|array
     * @throws \JsonException
     */
    public function controller(PDO $link, string $seccion):controler|array{
        $name_ctl = $this->name_controler(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre de controlador', data: $name_ctl, params: get_defined_vars());

        }

        if($seccion === 'session') {
            $controlador = new controlador_session(link: $link);
        }
        else{
            $controlador = new $name_ctl(link:$link);
        }
        return $controlador;
    }

    public function include_action(seguridad $seguridad): string
    {
        $include_action = './views/'.$seguridad->seccion.'/'.$seguridad->accion.'.php';
        if(file_exists($include_action)){
            include($include_action);
        }
        elseif($seguridad->accion === 'lista') {
            $include_action = './views/vista_base/lista.php';
        }
        elseif ($seguridad->accion ==='modifica'){
            $include_action = './views/vista_base/modifica.php';
        }
        elseif ($seguridad->accion ==='alta'){
            $include_action = './views/vista_base/alta.php';
        }
        return $include_action;
    }

    /**
     * @throws \JsonException
     */
    public function index(): array|stdClass
    {
        $con = new conexion();
        $link = conexion::$link;

        $session = (new session($link))->carga_data_session();
        if(errores::$error){
            return $this->error->error('Error al asignar session',$session);

        }

        $conf_generales = new generales();
        $seguridad = new seguridad();
        $_SESSION['tiempo'] = time();

        $seguridad = $this->permiso( link: $link,seguridad:   $seguridad);
        if(errores::$error){
            return $this->error->error('Error al verificar seguridad', $seguridad);

        }

        $controlador = $this->controller(link:  $link,seccion:  $seguridad->seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar controlador', data: $controlador, params: get_defined_vars());

        }

        $include_action = (new init())->include_action(seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error('Error al generar include', $include_action);

        }

        $out_ws = (new salida_data())->salida_ws(controlador:$controlador, include_action: $include_action,seguridad:  $seguridad);
        if(errores::$error){
            return $this->error->error('Error al generar salida', $out_ws);

        }

        $mensajeria = (new mensajes())->data();
        if(errores::$error){
            return $this->error->error('Error al generar mensajes', $mensajeria);

        }

        $data_custom = (new custom())->data(seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error('Error al generar datos custom', $data_custom);

        }

        $data = new stdClass();
        $data->css_custom = $data_custom->css;
        $data->js_seccion = $data_custom->js_seccion;
        $data->js_accion = $data_custom->js_accion;

        $data->menu = $seguridad->menu;

        $data->link = $link;
        $data->path_base = $conf_generales->path_base;

        $data->error_msj = $mensajeria->error_msj;
        $data->exito_msj = $mensajeria->exito_msj;

        $data->breadcrumbs = $controlador->breadcrumbs;

        $data->include_action = $include_action;

        $data->controlador = $controlador;

        $data->conf_generales = $conf_generales;


        return $data;
    }

    /**
     * P INT P ORDER
     * @param string $seccion
     * @return string|array
     */
    private function name_controler(string $seccion): string|array
    {
        $name_ctl = 'controlador_'.$seccion;
        $name_ctl = str_replace('controllers\\','',$name_ctl);
        $name_ctl = 'controllers\\'.$name_ctl;

        if(!class_exists($name_ctl)){
            return $this->error->error('Error no existe la clase '.$name_ctl,$name_ctl);
        }

        return $name_ctl;
    }

    /**
     * P INT P ORDER
     * @return stdClass
     */
    public function params_controler(): stdClass
    {
        $ws = false;
        $header = true;
        $view = false;
        if(isset($_GET['ws'])){
            $header = false;
            $ws = true;

        }
        if(isset($_GET['view'])){
            $header = false;
            $ws = false;
            $view = true;
        }

        $data = new stdClass();
        $data->ws = $ws;
        $data->header = $header;
        $data->view = $view;
        return $data;
    }

    /**
     * P INT
     * @param PDO $link
     * @param seguridad $seguridad
     * @return array|seguridad
     */
    public function permiso(PDO $link, seguridad $seguridad): array|seguridad
    {
        $modelo_accion = new accion($link);
        if (isset($_SESSION['grupo_id'])) {
            $permiso = $modelo_accion->permiso(accion: $seguridad->accion, seccion: $seguridad->seccion);
            if(errores::$error){
                session_destroy();
                return $this->error->error('Error al validar permisos',$permiso);
            }

            if (!$permiso) {
                $seguridad->seccion = 'session';
                $seguridad->accion = 'denegado';
            }

            $n_acciones = $modelo_accion->cuenta_acciones();
            if(errores::$error){
                session_destroy();
                return $modelo_accion->error->error('Error al contar acciones permitidas',$n_acciones);
            }
            if ((int)$n_acciones === 0) {
                session_destroy();
            }
        }
        return $seguridad;
    }
}