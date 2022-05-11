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
    public function css(seguridad $seguridad): string
    {
        $css = '';
        if(file_exists('./css/'.$seguridad->seccion.'.'.$seguridad->accion.'.css')){
            $css = "<link rel='stylesheet' href='./css/$seguridad->seccion.$seguridad->accion.css'>";
        }
        return $css;
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

}
