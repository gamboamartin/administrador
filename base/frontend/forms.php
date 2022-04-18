<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use gamboamartin\orm\validaciones;
use JetBrains\PhpStorm\Pure;

class forms{
    private errores $error;
    private validaciones_directivas $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }

    /**
     *
     * @param string $seccion
     * @param string $accion
     * @param array $var_get_extra
     * @return array|string
     */
    public function action_form(string $seccion, string $accion, array $var_get_extra): array|string
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error('Error $seccion no puede venir vacio',$seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error('Error $accion no puede venir vacio',$accion);
        }

        if(!defined('SESSION_ID')){
            return $this->error->error('Error SESSION_ID no existe','');
        }

        $vars = $this->vars_get($var_get_extra);
        if(errores::$error){
            return $this->error->error('Error al generar $var_get_extra',$vars);
        }
        $vars = (string)$vars;

        return "index.php?seccion=$seccion&accion=$accion&session_id=".SESSION_ID.$vars;
    }

    /**
     *
     * @param string $seccion Seccion del controlador a ejecutar
     * @param string $accion Accion del controlador a ejecutar
     * @param int $registro_id Identificador del registro
     * @param array $var_get_extra Variables por get que se naden extra forma es array(name_variable=>value)
     * @return string|array
     */
    public function action_form_id(string $seccion, string $accion, int $registro_id, array $var_get_extra): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error('Error $seccion no puede venir vacio',$seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error('Error $accion no puede venir vacio',$accion);
        }
        if($registro_id <=0){
            return $this->error->error('Error $registro_id debe ser mayor a 0',$registro_id);
        }
        if(!defined('SESSION_ID')){
            return $this->error->error('Error SESSION_ID no existe','');
        }
        $vars = '';
        foreach ($var_get_extra as $key=>$value){
            $vars.="&$key=$value";
        }
        $action_form = $this->action_form($seccion, $accion, $var_get_extra);
        if(errores::$error){
            return $this->error->error('Error al generar form',$action_form);
        }

        return $action_form."&registro_id=$registro_id";

    }

    /**
     *
     * @return string
     */
    public function data_form_base(): string
    {
        return 'method="POST" enctype="multipart/form-data"';
    }

    /**
     * PROBADO-PARAMS ORDER P INT
     * @param int $cols Columnas para mostrar en formulario 1-12
     * @param string $campo Nombre del campo para html en un input
     * @return array|string
     */
    public function genera_form_group(string $campo, int $cols):array|string{

        $valida = $this->validacion->valida_elementos_base_input(cols:$cols, tabla: $campo);
        if(errores::$error){
            return $this->error->error("Error al validar campo", $valida);
        }
        $html = "<div class='form-group col-md-$cols selector_$campo'>";
        $html .= '|label|';
        $html .='|input|';
        $html .= '</div>';


        return $html;
    }

    /**
     * PROBADO P INT P ORDER
     * @param string $seccion
     * @param string $accion
     * @param string $accion_request
     * @param string $session_id
     * @return string|array
     */
    public function header_form( string $accion, string $accion_request, string $seccion, string $session_id): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error('Error $seccion no puede venir vacio', $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error('Error $accion no puede venir vacio', $accion);
        }
        $accion_request = trim($accion_request);
        if($accion_request === ''){
            return $this->error->error('Error $accion_request no puede venir vacio', $accion_request);
        }
        $session_id = trim($session_id);
        if($session_id === ''){
            return $this->error->error('Error $session_id no puede venir vacio', $session_id);
        }

        return "<form id='form-$seccion-$accion' name='form-$seccion-alta' method='POST' 
            action='./index.php?seccion=$seccion&session_id=$session_id&accion=$accion_request' 
            enctype='multipart/form-data'>";
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param int $cols
     * @return string|array
     */
    public function header_form_group(int $cols): string|array
    {
        $valida = (new validaciones_directivas())->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error("Error al validar cols", $valida);
        }

        return '<div class="form-group col-md-'.$cols.'">';
    }

    private function var_get(string $key, string $value): array|string
    {
        $valida = $this->validacion->valida_vars_get($key, $value);
        if(errores::$error){
            return $this->error->error('Error al validar $var_get_extra',$valida);
        }

        return "&$key=$value";
    }
    private function vars_get(array $var_get_extra): array|string
    {
        $vars = '';
        foreach ($var_get_extra as $key=>$value){

            $valida = $this->validacion->valida_vars_get($key, $value);
            if(errores::$error){
                return $this->error->error('Error al validar $var_get_extra',$valida);
            }

            $var_get = $this->var_get($key, $value);
            if(errores::$error){
                return $this->error->error('Error al generar $var_get_extra',$var_get);
            }

            $vars.=$var_get;
        }
        return $vars;
    }

}
