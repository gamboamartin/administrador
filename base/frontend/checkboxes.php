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
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param string $salto
     * @param string $div_chk
     * @param string $etiqueta
     * @param string $data_input
     * @return string Html formado
     */
    public function checkbox(string $data_input, string $div_chk, string $etiqueta, string $salto): string
    {
        $html = "$salto $div_chk";

        if($etiqueta === ''){
            $html = $data_input;
        }
        return $html;
    }

    /**
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param string $campo Campo de input
     * @param string $valor
     * @param string $class
     * @param string $id_html
     * @param string $data_extra_html
     * @param string $checked_html
     * @param string $data_etiqueta
     * @param int $cols Columnas para css
     * @param string $disabled_html
     * @return array|stdClass
     */
    public function data_chk(string $campo, string $checked_html, string $class,int $cols, string $data_etiqueta,
                             string $data_extra_html, string $disabled_html, string $id_html,string $valor): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data:  $campo);
        }
        $valida = $this->validacion->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $data_span = $this->data_span_chk(campo: $campo, checked_html:  $checked_html,class:  $class,
            data_extra_html: $data_extra_html, disabled_html:  $disabled_html, id_html: $id_html,valor: $valor);
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
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param string $campo Campo de input
     * @param string $valor Valor activo o inactivo
     * @param string $class class css
     * @param string $id_html id css
     * @param string $data_extra_html extra params
     * @param string $checked_html atributo checked
     * @param string $disabled_html atributo disabled
     * @return string|array
     */
    private function data_input_chk(string $campo, string $checked_html, string $class, string $data_extra_html,
                                    string $disabled_html, string $id_html, string $valor): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error campo vacio', $campo);
        }
        $valor = trim($valor);
        $class = trim($class);
        $id_html = trim($id_html);
        $data_extra_html = trim($data_extra_html);
        $checked_html = trim($checked_html);
        $disabled_html = trim($disabled_html);

        if($valor !== 'activo'){
            $valor = 'inactivo';
        }

        $html = "<input type='checkbox' $disabled_html ";
        $html .= "name='$campo' value='$valor' $class $id_html $data_extra_html $checked_html>";
        return $html;
    }

    /**
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param string $campo Campo de input
     * @param string $valor
     * @param string $class
     * @param string $id_html
     * @param string $data_extra_html
     * @param string $checked_html
     * @param string $disabled_html
     * @return array|stdClass
     */
    private function data_span_chk(string $campo, string $checked_html, string $class, string $data_extra_html,
                                   string $disabled_html, string $id_html, string $valor): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error campo vacio', $campo);
        }
        $data_input = $this->data_input_chk(campo: $campo, checked_html:  $checked_html, class: $class,
            data_extra_html:  $data_extra_html, disabled_html: $disabled_html,id_html:  $id_html, valor:  $valor);
        if(errores::$error){
            return $this->error->error('Error al generar data',$data_input);
        }

        $span_chk = (new etiquetas())->span_chk(data_input: $data_input);
        if(errores::$error){
            return $this->error->error('Error al generar span',$span_chk);
        }

        $data = new stdClass();
        $data->data_input = $data_input;
        $data->span_chk = $span_chk;

        return $data;
    }

    /**
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param int $cols
     * @param string $span_btn_chk
     * @return string|array
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
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param string $data_etiqueta
     * @param string $span_chk
     * @param int $cols
     * @return array|string
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
