<?php
namespace base\controller;
use base\seguridad;
use controllers\controlador_session;
use gamboamartin\errores\errores;
use models\accion;
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
     */
    public function controller(PDO $link, string $seccion):controler|array{
        $name_ctl = $this->name_controler(seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al obtener nombre de controlador', $name_ctl);

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