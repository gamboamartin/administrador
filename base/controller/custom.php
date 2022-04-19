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
        $data = new stdClass();
        $data->css = $css_custom;
        $data->js_seccion = $js_seccion;
        $data->js_accion = $js_accion;

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

}
