<?php
namespace base\frontend;

use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;

use stdClass;

class html  {

    private errores $error;
    public validaciones_directivas $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $label
     * @param string $contenido
     * @return array|string
     */
    protected function crea_elemento_encabezado(string $contenido, string $label):array|string{
        $label = trim($label);
        if($label === ''){
            return $this->error->error('Error el label no puede venir vacio',$label);
        }

        return "
            <div class='col-md-3'>
            <label>
                $label
            </label>
            <br>
            $contenido
            </div>
            ";

    }


    /**
     * P INT
     * Genera un input para ser mostrado en html del front
     *
     * @param string $campo Nombre del campo
     * @param string $value Valor default a mostrar en el input
     * @param bool $required si el elemento es requerido asigna required al html
     * @param string $pattern expresion regular a validar en el evento submit
     * @param bool $disabled si el elemento es true asigna disabled al html
     * @param string $tipo tipo de input ej  text, file
     * @param string $etiqueta Etiqueta a mostrar en input es un label
     * @param array $clases_css base de tipo css que se asignaran al input
     * @param array $ids_css ids de tipo css que se asignaran al input
     * @param bool $aplica_etiqueta si aplica etiqueta mostrar el label del campo si no no integra label
     * @param array $data_extra arreglo con datas extra
     * @param string $size Tamano del input sm md lg
     * @return string|array html con info del input a mostrar
     * @example
     *      $input = $this->genera_input($campo, $value, $required, $pattern,$disabled,'text',$etiqueta,$clases_css,$ids_css);
     *
     */
    protected function genera_input(string $campo, string $value, bool $required, string $pattern,
                                  bool $disabled, string $tipo, string $etiqueta, array $clases_css, array $ids_css,
                                  bool $aplica_etiqueta = true, array $data_extra = array(), string $size = 'sm'):string|array{


        $valida = $this->validacion->valida_input_text(aplica_etiqueta: $aplica_etiqueta, etiqueta: $etiqueta, campo: $campo, tipo: $tipo);
        if(errores::$error){
            return $this->error->error('Error al validar', $valida);
        }

        $params = (new params_inputs())->params_input(campo: $campo,clases_css: $clases_css, data_extra: $data_extra,
            disabled: $disabled, etiqueta: $etiqueta, ids_css: $ids_css, pattern: $pattern, required: $required,
            value: $value);

        if(errores::$error){
            return $this->error->error('Error al genera base input', $params);
        }

        $html = $this->integra_html_input(tipo: $tipo, size: $size, params: $params);
        if(errores::$error){
            return $this->error->error('Error al integrar html', $html);
        }

        return $html;
    }


    /**
     *
     * @param string $tipo
     * @param string $size
     * @param stdClass $params
     * @param string $campo
     * @param string $campo_capitalize
     * @param string $value
     * @return string|array
     */
    protected function html_fecha(string $tipo, string $size, stdClass $params, string $campo, string $campo_capitalize,
                                string $value): string|array
    {

        $params_r = (new params_inputs())->limpia_obj_input($params);
        if (errores::$error) {
            return $this->error->error('Error al limpiar params', $params_r);
        }

        $tipo = trim($tipo);
        if($tipo === ''){
            return $this->error->error('Error tipo no puede venir vacio', $tipo);
        }

        $size = trim($size);
        if($size === ''){
            return $this->error->error('Error $size no puede venir vacio', $size);
        }

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio', $campo);
        }

        $html ="<input ";
        $html.=" type='$tipo' ";
        $html.=" class='form-control-$size form-control input-$size $params_r->class' ";
        $html.=" name='$campo' ";
        $html.="  id='$params_r->ids' ";
        $html.="  placeholder='Ingresa $campo_capitalize' ";
        $html.="  $params_r->required ";
        $html.="  title='Ingrese una $campo' ";
        $html.="  value='$value' ";
        $html.="  $params_r->disabled ";
        $html.="  $params_r->data_extra ";
        $html.="  > ";

        return $html;
    }


    /**
     * P INT
     * @param string $tipo
     * @param string $size
     * @param stdClass $params
     * @return string|array
     */
    private function integra_html_input(string $tipo, string $size, stdClass $params): string|array
    {
        $tipo = trim($tipo);
        if($tipo === ''){
            return $this->error->error('Error tipo no puede venir vacio', $tipo);
        }
        $size = trim($size);
        if($size === ''){
            return $this->error->error('Error $size no puede venir vacio', $size);
        }

        $keys = array('class','ids','name','place_holder','required','campo_mostrable','pattern','value','disabled','data_extra');
        foreach($keys as $key){
            if(!isset($params->$key)){
                $params->$key = '';
            }
        }

        $html = "<input type='$tipo' ";
        $html .= " class='form-control form-control-$size $params->class"."' ";
        $html .= " id='$params->ids"."' ";
        $html .= " name='".$params->name."' ";
        $html .= " placeholder='$params->place_holder' ";
        $html .= " $params->required ";
        $html .= " title='$params->campo_mostrable' ";
        $html .= " $params->pattern ";
        $html .= " value='$params->value' ";
        $html .= " $params->disabled ";
        $html .= " $params->data_extra ";
        $html .= " > ";
        return $html;
    }

}
