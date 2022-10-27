<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;


class params_inputs{
    private errores $error;
    private validaciones_directivas $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * Asigna los parametros de un input para ser utilizados en java o css
     * @param string $campo nombre de input
     * @param array $clases_css Clases de estilos para ser utilizas en css y/o java
     * @param string $pattern Regex para ser integrado en validacion de input via html5
     * @param bool $required si required input es requerido y se validara via html5
     * @param string $value valor inicial del input puede ser vacio
     * @return array|stdClass Valor en un objeto para ser integrados en un input
     */
    private function base_input(string $campo, array $clases_css, string $pattern, bool $required,
                                string $value): array|stdClass
    {
        $campo = trim($campo);

        if($campo === ''){
            return $this->error->error('Error el campo no puede venir vacio', $campo);
        }

        $html_pattern = $this->pattern_html(pattern: $pattern);
        if(errores::$error){
            return $this->error->error('Error al generar pattern css', $html_pattern);
        }

        $class_css_html = (new class_css())->class_css_html(clases_css: $clases_css);
        if(errores::$error){
            return $this->error->error('Error al generar clases css', $class_css_html);
        }


        $required_html = $this->required_html(required: $required);
        if(errores::$error){
            return $this->error->error('Error al generar required html', $required_html);
        }



        $value = str_replace("'","`",$value);

        $datas = new stdClass();
        $datas->pattern = $html_pattern;
        $datas->class = $class_css_html;
        $datas->required = $required_html;
        $datas->value = $value;

        return $datas;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $etiqueta
     * @param string $campo
     * @return stdClass|array
     */
    private function base_input_dinamic( string $campo, string $etiqueta): stdClass|array
    {
        $etiqueta = trim($etiqueta);
        $campo = trim($campo);

        if($campo === ''){
            return $this->error->error('Error el campo no puede venir vacio', $campo);
        }

        $campo_mostrable = $etiqueta;
        $place_holder = $campo_mostrable;
        $name = $campo;

        $data = new stdClass();
        $data->campo_mostrable = $etiqueta;
        $data->place_holder = $place_holder;
        $data->name = $name;
        return $data;
    }


    /**
     * Integra los extra params a un option
     * @param array $value Valor  integrar
     * @param string $tabla tabla en ejecucion
     * @param int $valor_envio Valor de option
     * @param array $data_extra extra params
     * @param array $data_con_valor extra params
     * @return array|stdClass
     * @version 1.509.51
     */
    public function data_content_option(array $data_con_valor, array $data_extra, string $tabla, int $valor_envio,
                                        array $value): array|stdClass
    {
        $selected = $this->validacion->valida_selected(id: $valor_envio, tabla: $tabla, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar selected', data: $selected);
        }

        $data_extra_html = (new extra_params())->datas_extra(data_con_valor:$data_con_valor,data_extra: $data_extra,
            value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar datas extra', data: $data_extra_html);
        }

        $value_html = (new values())->content_option_value(tabla: $tabla, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar value',data:  $data_extra_html);
        }

        $datas = new stdClass();
        $datas->selected = $selected;
        $datas->data_extra_html = $data_extra_html;
        $datas->value_html = $value_html;

        return $datas;
    }

    /**
     * Si disabled retorna attr disabled  en string
     * @stable true
     * @version 1.588.52
     * @param bool $disabled Si disabled retorna attr disabled
     * @return string
     *
     */
    public function disabled_html(bool $disabled): string
    {
        $disabled_html = '';
        if($disabled){
            $disabled_html = 'disabled';
        }
        return $disabled_html;
    }





    /**
     * Limpia los elementos de un objeto basado en sus atributos
     * @param array $keys Keys de parametros a limpiar
     * @param stdClass $params Parametros a limpiar
     * @return stdClass
     * @version 1.309.41
     */
    private function limpia_obj(array $keys, stdClass $params): stdClass
    {
        foreach($keys as $key){
            if(!isset($params->$key)){
                $params->$key = '';
            }
        }
        return $params;
    }

    /**
     * FULL
     * @param stdClass $params
     * @return stdClass
     */
    public function limpia_obj_btn(stdClass $params): stdClass
    {
        $keys = array('class','data_extra','icon');
        $params = $this->limpia_obj(keys: $keys,params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar params', data: $params);
        }
        return $params;
    }

    /**
     * Limpia un conjunto de objetos a vacio
     * @param stdClass $params Inicializa parametros
     * @return array|stdClass
     * @version 1.352.41
     */
    public function limpia_obj_input(stdClass $params): array|stdClass
    {
        $keys = array('class','ids','required','data_extra','disabled');
        $params = $this->limpia_obj(keys: $keys, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar params', data: $params);
        }
        return $params;
    }

    /**
     * Genera un salto de linea html
     * @param bool $ln Si salto genera un div col 12
     * @param string $size sm lg etc
     * @return string|array
     * @version 1.310.41
     */
    public function ln(bool $ln, string $size): string|array
    {
        $size = trim($size);
        if($size === ''){
            return $this->error->error(mensaje: 'Error size no puede venir vacio',data: $size);
        }
        $html = '';
        if($ln){
            $html = "<div class='col-$size-12'></div>";
        }
        return $html;
    }

    /**
     * Aplica un multiple al input
     * @param bool $multiple si multiple hace el el input se integre para multiples selecciones
     * @return stdClass
     * @version 1.455.49
     */
    #[Pure] public function multiple_html(bool $multiple): stdClass
    {
        $multiple_html = '';
        $data_array ='';
        if($multiple){
            $multiple_html = 'multiple';
            $data_array = '[]';
        }
        $data = new stdClass();
        $data->multiple = $multiple_html;
        $data->data = $data_array;
        return $data;
    }


    /**
     * Genera los parametros de una fecha input
     * @param string $campo nombre del campo
     * @param array $css conjunto de css a integrar
     * @param bool $required Si required deja input como requerido
     * @return array|stdClass
     * @version 1.332.41
     */
    public function params_fecha(string $campo, array $css, bool $required): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio', data: $campo);
        }

        $required_html = $this->required_html(required: $required);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar required html',data: $required_html);
        }

        $css_html = (new class_css())->class_css_html(clases_css: $css);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar class html',data: $css_html);
        }

        $params = new stdClass();
        $params->required = $required_html;
        $params->class = $css_html;

        return $params;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $campo
     * @param array $clases_css
     * @param string $etiqueta
     * @param string $pattern
     * @param bool $required
     * @param string $value
     * @return array|stdClass
     */
    public function params_input(string $campo, array $clases_css, string $etiqueta, string $pattern, bool $required,
                                 string $value): array|stdClass
    {
        $campo = trim($campo);

        if($campo === ''){
            return $this->error->error('Error el campo no puede venir vacio', $campo);
        }

        $base_input_dinamic = $this->base_input_dinamic(campo:  $campo, etiqueta: $etiqueta);
        if(errores::$error){
            return $this->error->error('Error al genera base input', $base_input_dinamic);
        }

        $data_base_input = $this->base_input(campo: $campo, clases_css: $clases_css, pattern: $pattern,
            required:  $required, value: $value);

        if(errores::$error){
            return $this->error->error('Error al genera base input', $data_base_input);
        }

        $obj = new stdClass();
        foreach ($base_input_dinamic as $name=>$base){
            $obj->$name = $base;
        }
        foreach ($data_base_input as $name=>$base){
            $obj->$name = $base;
        }
        return $obj;
    }

    /**
     * PROBADO - PARAMS-ORDER PARAMS INT
     * @param string $pattern
     * @return string
     */
    private function pattern_html(string $pattern): string
    {
        $html_pattern = '';
        if($pattern){
            $html_pattern = "pattern='$pattern'";
        }
        return $html_pattern;
    }

    /**
     * Genera required en forma html para ser integrado en un input
     * @version 1.87.19
     * @param bool $required indica si es requerido o no
     * @return string required en caso true o vacio en false
     */
    public function required_html(bool $required): string
    {
        $required_html = '';
        if($required){
            $required_html = 'required';
        }
        return $required_html;
    }

    /**
     * Genera un salto de linea aplicando div 12
     * @param bool $ln Si true aplica div 12
     * @return string
     * @version 1.246.39
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-08-01 17:06

    private function salto(bool $ln): string
    {
        $salto = '';
        if($ln){
            $salto = "<div class='col-md-12'></div>";
        }
        return $salto;
    }*/



}
