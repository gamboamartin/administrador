<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

/**
 * PARAMS ORDER-PARAMS INT PROBADO
 */
class checkboxes{
    private errores $error;
    private validaciones_directivas $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }

    /**
     * Genera el html de un checkbox
     * @param string $data_input Datos de input
     * @param string $div_chk Div de integracion de checkbox
     * @param string $etiqueta Etiqueta de checkbox
     * @return string Html formado
     * @version 1.309.41
     */
    public function checkbox(string $data_input, string $div_chk, string $etiqueta): string
    {
        $html = " $div_chk";

        if($etiqueta === ''){
            $html = $data_input;
        }
        return $html;
    }

    /**
     * Integra el dato de un checkbox en html
     * @param string $campo Campo de input
     * @param string $class Clase css
     * @param int $cols Columnas para css
     * @param string $data_etiqueta Datos de la etiqueta del chk
     * @param string $data_extra_html extra params en forma html
     * @param string $disabled_html atributo disabled en html
     * @param string $valor Valor del checkbox
     * @return array|stdClass
     * @version 1.309.41
     */
    public function data_chk(string $campo, string $class,int $cols, string $data_etiqueta,
                             string $data_extra_html, string $disabled_html,string $valor): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data:  $campo);
        }
        $valida = $this->validacion->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $data_span = $this->data_span_chk(campo: $campo,class:  $class,
            data_extra_html: $data_extra_html, disabled_html:  $disabled_html,valor: $valor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar span',data: $data_span);
        }

        $div_chk = $this->etiqueta_chk(cols: $cols, data_etiqueta:$data_etiqueta,span_chk: $data_span->span_chk);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar div',data: $div_chk);
        }

        $data = new stdClass();
        $data->data_span = $data_span;
        $data->div_chk = $div_chk;
        $data->data_input = $data_span->data_input;


        return $data;
    }

    /**
     * Genera el html de un checkbox
     * @param string $campo Campo de input
     * @param string $class class css
     * @param string $data_extra_html extra params
     * @param string $disabled_html atributo disabled
     * @param string $valor Valor activo o inactivo
     * @return string|array
     * @version 1.279.41
     * @verfuncion  1.1.0
     * @author mgamboa
     * @fecha 2022-08-08 14:27
     */
    private function data_input_chk(string $campo, string $class, string $data_extra_html,
                                    string $disabled_html, string $valor): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data:  $campo);
        }
        $valor = trim($valor);
        $class = trim($class);

        $data_extra_html = trim($data_extra_html);
        $disabled_html = trim($disabled_html);

        if($valor !== 'activo'){
            $valor = 'inactivo';
        }

        $html = "<input type='checkbox' $disabled_html ";
        $html .= "name='$campo' value='$valor' $class  $data_extra_html>";
        return $html;
    }

    /**
     * Genera los datos a integrar un checkbox
     * @param string $campo Campo de input
     * @param string $class Clase css
     * @param string $data_extra_html extra params en forma html
     * @param string $disabled_html Atributo disabled html puede venir vacio
     * @param string $valor Valor del checkbox
     * @return array|stdClass
     * @version  1.304.41
     */
    private function data_span_chk(string $campo, string $class, string $data_extra_html,
                                   string $disabled_html, string $valor): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error campo vacio', $campo);
        }
        $data_input = $this->data_input_chk(campo: $campo, class: $class,
            data_extra_html:  $data_extra_html, disabled_html: $disabled_html, valor:  $valor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data',data: $data_input);
        }

        $span_chk = (new etiquetas())->span_chk(data_input: $data_input);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar span',data: $span_chk);
        }

        $data = new stdClass();
        $data->data_input = $data_input;
        $data->span_chk = $span_chk;

        return $data;
    }

    /**
     * Genera el html de un div de checkbox
     * @param int $cols Columnas en css para maquetacion de divs
     * @param string $span_btn_chk html en forma de span para integrar a div
     * @return string|array
     * @version 1.309.41
     */
    private function div_chk(int $cols, string $span_btn_chk): string|array
    {
        $valida = $this->validacion->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error('Error al validar cols', $valida);
        }

        return "<div class='form-group col-md-".$cols."'>
                    <div class='input-group  col-md-12'>$span_btn_chk</div>
		        </div>";
    }

    /**
     * Genera una etiqueta para un checkbox
     * @param int $cols Columnas en css para maquetacion de divs
     * @param string $data_etiqueta Datos de la etiqueta del chk
     * @param string $span_chk html de span
     * @return array|string
     * @version 1.309.41
     */
    private function etiqueta_chk(int $cols, string $data_etiqueta, string $span_chk): array|string
    {
        $valida = $this->validacion->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error('Error al validar cols', $valida);
        }
        $span_btn_chk = (new etiquetas())->span_btn_chk(data_etiqueta: $data_etiqueta, span_chk: $span_chk);
        if(errores::$error){
            return $this->error->error('Error al generar span',$span_btn_chk);
        }

        $div_chk = $this->div_chk(cols: $cols, span_btn_chk: $span_btn_chk);
        if(errores::$error){
            return $this->error->error('Error al generar div',$div_chk);
        }
        return $div_chk;
    }

}
