<?php
namespace base\controller;
use base\seguridad;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class custom{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }
    public function css(seguridad $seguridad): stdClass
    {

        $init = $this->init_data_css(seguridad:$seguridad);
        if(errores::$error){
            return $this->error->error('Error al inicializa css', $init);

        }

        $init = $this->out_css(init:$init,seguridad:  $seguridad);
        if(errores::$error){
            return $this->error->error('Error al obtener css', $init);

        }

        return $init;
    }

    /**
     * Valida si existe algun archivo para css
     * @param stdClass $init Inicializacion data css
     * @return stdClass|array salida con validacion de existencia de archivos
     * @version 1.366.41
     */
    private function css_existe(stdClass $init): stdClass|array
    {
        $keys = array('file_base');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar init',data:  $valida);
        }
        if(file_exists($init->file_base.'.php')){
            $init->existe_php = true;
        }
        if(file_exists($init->file_base.'.css')){
            $init->existe_css = true;
        }

        return $init;
    }

    public function data(controler $controlador, seguridad $seguridad): array|stdClass
    {
        $css_custom = (new custom())->css(seguridad: $seguridad);
        if(errores::$error){
           return $this->error->error('Error al generar css', $css_custom);

        }
        $js_seccion = (new custom())->js_seccion(seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error('Error al generar js', $js_seccion);

        }
        $js_accion = (new custom())->js_accion(seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error('Error al generar js', $js_accion);
        }
        $js_view = (new custom())->js_view(controlador: $controlador, seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error('Error al generar js', $js_view);
        }
        $data = new stdClass();
        $data->css = $css_custom;
        $data->js_seccion = $js_seccion;
        $data->js_accion = $js_accion;
        $data->js_view = $js_view->js;
        $data->js_view_aplica_include = $js_view->aplica_include;

        return $data;
    }
    public function js_accion(seguridad $seguridad): string
    {
        $js = '';
        if(file_exists('./js/'.$seguridad->accion.'.js')){
            $js = "<script type='text/javascript' src='./js/$seguridad->accion.js'></script>";
        }
        return $js;
    }
    public function js_seccion(seguridad $seguridad): string
    {
        $js = '';
        if(file_exists('./js/'.$seguridad->seccion.'.js')){
            $js = "<script type='text/javascript' src='./js/$seguridad->seccion.js'></script>";
        }
        return $js;
    }

    /**
     * Obtiene el js si existe el doc dentro de js/seccion/accion.js
     * @param controler $controlador Controlador en ejecucion
     * @param seguridad $seguridad Clase de seguridad donde se obtienen los datos de accion y seccion
     * @return string|stdClass
     */
    public function js_view(controler $controlador, seguridad $seguridad): string|stdClass
    {
        /**
         * REFACTORIZAR
         */
        $js = '';
        $aplica_include = false;
        $ruta_js = './js/'.$seguridad->seccion.'/'.$seguridad->accion.'.js';
        if(file_exists($ruta_js)){
            $js = "<script type='text/javascript' src='$ruta_js'></script>";
        }
        else{
            $ruta_js = './js/'.$seguridad->seccion.'/'.$seguridad->accion.'.js.php';
            if(file_exists($ruta_js)){
                $js = $ruta_js;
                $aplica_include = true;
            }
        }

        $data = new stdClass();
        $data->js = $js;
        $data->aplica_include = $aplica_include;
        return $data;
    }

    /**
     * Inicializa los datos para salida css custom
     * @param seguridad $seguridad Seguridad inicializada
     * @return stdClass
     * @version 1.365.41
     */
    private function init_css(seguridad $seguridad): stdClass
    {

        $file_base = "./css/$seguridad->seccion.$seguridad->accion";
        $data = new stdClass();
        $data->css = '';
        $data->existe_php = false;
        $data->existe_css = false;
        $data->file_base = $file_base;

        return $data;
    }

    /**
     * Inicializa los elementos de un css
     * @param seguridad $seguridad Seguridad inicializada
     * @return array|stdClass
     * @version 1.367.42
     */
    private function init_data_css(seguridad $seguridad): array|stdClass
    {
        $init = $this->init_css(seguridad:$seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa css', data: $init);

        }

        $init = $this->css_existe(init:$init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa css existe file',data:  $init);

        }

        return $init;
    }

    /**
     * Salida de css
     * @param stdClass $init Init de css
     * @param seguridad $seguridad seguridad inicializada
     * @return stdClass|array
     */
    private function out_css(stdClass $init, seguridad $seguridad): stdClass|array
    {
        $keys = array('existe_php','existe_css');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar init',data:  $valida);
        }
        if($init->existe_php){
            $init->css = "./css/$seguridad->seccion.$seguridad->accion.php";
        }
        elseif($init->existe_css){
            $init->css = "<link rel='stylesheet' href='$init->file_base.css'>";
        }

        return $init;
    }

}
