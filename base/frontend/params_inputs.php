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
     * @param string $pattern Regex para ser integrado en validacion de input via html5
     * @param array $clases_css Clases de estilos para ser utilizas en css y/o java
     * @param bool $disabled si disabled input queda inhabilitado en front
     * @param bool $required si required input es requerido y se validara via html5
     * @param array $ids_css Id de estilos para ser utilizas en css y/o java
     * @param string $campo nombre de input
     * @param array $data_extra datos para ser utilizados en javascript
     * @param string $value valor inicial del input puede ser vacio
     * @return array|stdClass Valor en un objeto para ser integrados en un input
     */
    private function base_input(string $campo, array $clases_css, array $data_extra, bool $disabled,
                                array $ids_css, string $pattern, bool $required, string $value): array|stdClass
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

        $disabled_html = $this->disabled_html(disabled: $disabled);
        if(errores::$error){
            return $this->error->error('Error al generar disabled html', $disabled_html);
        }

        $required_html = $this->required_html(required: $required);
        if(errores::$error){
            return $this->error->error('Error al generar required html', $required_html);
        }

        $ids_css_html = $this->ids_html(campo: $campo, ids_css: $ids_css);
        if(errores::$error){
            return $this->error->error('Error al generar ids html', $ids_css_html);
        }

        $data_extra_html = (new extra_params())->data_extra_html(data_extra: $data_extra);
        if(errores::$error){
            return $this->error->error('Error al generar data extra html', $data_extra_html);
        }

        $value = str_replace("'","`",$value);

        $datas = new stdClass();
        $datas->pattern = $html_pattern;
        $datas->class = $class_css_html;
        $datas->disabled = $disabled_html;
        $datas->required = $required_html;
        $datas->ids = $ids_css_html;
        $datas->data_extra = $data_extra_html;
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
     * PROBADO-PARAMS ORDER P INT
     * @param string $valor
     * @return string|array
     */
    private function checked(string $valor): string|array
    {
        $valor = trim($valor);

        $checked_html = '';
        if($valor==='activo'){
            $checked_html = 'checked';
        }
        return $checked_html;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $value
     * @param string $tabla
     * @param int $valor_envio
     * @param array $data_extra
     * @param array $data_con_valor
     * @return array|stdClass
     */
    public function data_content_option(array $data_con_valor, array $data_extra, string $tabla, int $valor_envio,
                                        array $value): array|stdClass
    {
        $selected = $this->validacion->valida_selected(id: $valor_envio, tabla: $tabla, value: $value);
        if(errores::$error){
            return $this->error->error('Error al validar selected', $selected);
        }

        $data_extra_html = (new extra_params())->datas_extra(data_con_valor:$data_con_valor,data_extra: $data_extra,
            value: $value);
        if(errores::$error){
            return $this->error->error('Error al generar datas extra', $data_extra_html);
        }

        $value_html = (new values())->content_option_value(tabla: $tabla, value: $value);
        if(errores::$error){
            return $this->error->error('Error al generar value', $data_extra_html);
        }

        $datas = new stdClass();
        $datas->selected = $selected;
        $datas->data_extra_html = $data_extra_html;
        $datas->value_html = $value_html;

        return $datas;
    }

    /**
     * Genera disabled html para inputs
     * @version 1.86.19
     * @param bool $disabled Si disabled retorna text disabled
     * @return string
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
     * PROBADO-PARAMS ORDER P INT
     * @param string $id_css
     * @return string
     */
    private function id_html(string $id_css): string
    {
        $id_html = '';
        if($id_css !==''){
            $id_html = " id = '$id_css' ";
        }
        return $id_html;
    }

    /**
     * PROBADO-PARAMS ORDER P INT
     * @param array $ids_css
     * @param string $campo
     * @return string|array
     */
    public function ids_html(string $campo, array $ids_css): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error el campo esta vacio', $campo);
        }
        $ids_css_html = $campo;
        foreach($ids_css as $id_css){
            $ids_css_html.=' '.$id_css;
        }
        return $ids_css_html;
    }

    /**
     * FULL
     * @param array $keys
     * @param stdClass $params
     * @return stdClass
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
            return $this->error->error(mensaje: 'Error al limpiar params', data: $params, params: get_defined_vars());
        }
        return $params;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param stdClass $params
     * @return array|stdClass
     */
    public function limpia_obj_input(stdClass $params): array|stdClass
    {
        $keys = array('class','ids','required','data_extra','disabled');
        $params = $this->limpia_obj(keys: $keys, params: $params);
        if (errores::$error) {
            return $this->error->error('Error al limpiar params', $params);
        }
        return $params;
    }

    /**
     * PROBADO-PARAMS ORDER P INT
     * @param bool $ln
     * @param string $size
     * @return string|array
     */
    public function ln(bool $ln, string $size): string|array
    {
        $size = trim($size);
        if($size === ''){
            return $this->error->error('Error size no puede venir vacio',$size);
        }
        $html = '';
        if($ln){
            $html = "<div class='col-$size-12'></div>";
        }
        return $html;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param bool $multiple
     * @return stdClass
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
     * PROBADO-PARAMS ORDER P INT
     * @param string $valor
     * @param bool $ln
     * @param string $css_id
     * @return array|stdClass $data->[string checked_html,string salto,string id_html]
     */
    public function params_chk(string $css_id, bool $ln, string $valor): array|stdClass
    {
        $checked_html = $this->checked(valor: $valor);
        if(errores::$error){
            return $this->error->error('Error al validar checked',$checked_html);
        }


        $salto = $this->salto(ln: $ln);
        if(errores::$error){
            return $this->error->error('Error al generar ln',$salto);
        }

        $id_html = $this->id_html(id_css: $css_id);
        if(errores::$error){
            return $this->error->error('Error al generar $id_html',$id_html);
        }

        $data = new stdClass();
        $data->checked_html = $checked_html;
        $data->salto = $salto;
        $data->id_html = $id_html;
        return $data;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param bool $disabled
     * @param bool $required
     * @param array $data_extra
     * @param array $css
     * @param array $ids
     * @param string $campo
     * @return array|stdClass
     */
    public function params_fecha(string $campo, array $css, array $data_extra, bool $disabled, array $ids,
                                 bool $required): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error el campo esta vacio', $campo);
        }
        $disabled_html = $this->disabled_html(disabled: $disabled);
        if (errores::$error) {
            return $this->error->error('Error al generar disabled html',$disabled_html);
        }

        $required_html = $this->required_html(required: $required);
        if (errores::$error) {
            return $this->error->error('Error al generar required html',$required_html);
        }

        $data_extra_html = (new extra_params())->data_extra_html(data_extra: $data_extra);
        if (errores::$error) {
            return $this->error->error('Error al generar data html',$data_extra_html);
        }

        $css_html = (new class_css())->class_css_html(clases_css: $css);
        if (errores::$error) {
            return $this->error->error('Error al generar class html',$css_html);
        }
        $ids_html = $this->ids_html(campo:  $campo, ids_css: $ids);
        if (errores::$error) {
            return $this->error->error('Error al generar ids html',$ids_html);
        }

        $params = new stdClass();
        $params->disabled = $disabled_html;
        $params->required = $required_html;
        $params->data_extra = $data_extra_html;
        $params->class = $css_html;
        $params->ids = $ids_html;

        return $params;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $etiqueta
     * @param string $campo
     * @param string $pattern
     * @param array $clases_css
     * @param bool $disabled
     * @param bool $required
     * @param array $ids_css
     * @param array $data_extra
     * @param string $value
     * @return array|stdClass
     */
    public function params_input(string $campo, array $clases_css, array $data_extra, bool $disabled, string $etiqueta,
                                 array $ids_css, string $pattern, bool $required, string $value): array|stdClass
    {
        $campo = trim($campo);

        if($campo === ''){
            return $this->error->error('Error el campo no puede venir vacio', $campo);
        }

        $base_input_dinamic = $this->base_input_dinamic(campo:  $campo, etiqueta: $etiqueta);
        if(errores::$error){
            return $this->error->error('Error al genera base input', $base_input_dinamic);
        }

        $data_base_input = $this->base_input(campo: $campo, clases_css: $clases_css, data_extra:  $data_extra,
            disabled:  $disabled, ids_css: $ids_css, pattern: $pattern, required:  $required, value: $value);

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
     * PROBADO-PARAMS ORDER P INT
     * @param bool $required
     * @return string
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
     * PROBADO-PARAMS ORDER P INT
     * @param bool $ln
     * @return string
     */
    private function salto(bool $ln): string
    {
        $salto = '';
        if($ln){
            $salto = "<div class='col-md-12'></div>";
        }
        return $salto;
    }



}
