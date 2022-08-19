<?php
namespace base\controller;
use base\seguridad;
use gamboamartin\errores\errores;
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

    private function css_existe(stdClass $init): stdClass
    {
        if(file_exists($init->file_base.'.php')){
            $init->existe_php = true;
        }
        if(file_exists($init->file_base.'.css')){
            $init->existe_css = true;
        }

        return $init;
    }

    public function data(seguridad $seguridad): array|stdClass
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
        $js_view = (new custom())->js_view(seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error('Error al generar js', $js_view);
        }
        $data = new stdClass();
        $data->css = $css_custom;
        $data->js_seccion = $js_seccion;
        $data->js_accion = $js_accion;
        $data->js_view = $js_view;

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
     * @param seguridad $seguridad Clase de seguridad donde se obtienen los datos de accion y seccion
     * @return string
     */
    public function js_view(seguridad $seguridad): string
    {
        $js = '';
        $ruta_js = './js/'.$seguridad->seccion.'/'.$seguridad->accion.'.js';
        if(file_exists($ruta_js)){
            $js = "<script type='text/javascript' src='$ruta_js'></script>";
        }
        return $js;
    }

    /**
     * Inicializa los datos para salida css custom
     * @param seguridad $seguridad Seguridad inicializada
     * @return stdClass
     */
    private function init_css(seguridad $seguridad): stdClass
    {

        $file_base = "./css/'.$seguridad->seccion.'.'.$seguridad->accion";
        $data = new stdClass();
        $data->css = '';
        $data->existe_php = false;
        $data->existe_css = false;
        $data->file_base = $file_base;

        return $data;
    }

    private function init_data_css(seguridad $seguridad): array|stdClass
    {
        $init = $this->init_css(seguridad:$seguridad);
        if(errores::$error){
            return $this->error->error('Error al inicializa css', $init);

        }

        $init = $this->css_existe(init:$init);
        if(errores::$error){
            return $this->error->error('Error al inicializa css existe file', $init);

        }

        return $init;
    }

    private function out_css(stdClass $init, seguridad $seguridad): stdClass
    {
        if($init->existe_php){
            $init->css = "./css/$seguridad->seccion.$seguridad->accion.php";
        }
        elseif($init->existe_css){
            $init->css = "<link rel='stylesheet' href='$init->file_base.css'>";
        }

        return $init;
    }

}
