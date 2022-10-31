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
     * Integra los extra params a un option
     * @param string $tabla tabla en ejecucion
     * @param int $valor_envio Valor de option
     * @param array $value Valor  integrar
     * @return array|stdClass
     * @version 1.509.51
     */
    public function data_content_option(string $tabla, int $valor_envio, array $value): array|stdClass
    {
        $selected = $this->validacion->valida_selected(id: $valor_envio, tabla: $tabla, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar selected', data: $selected);
        }



        $value_html = (new values())->content_option_value(tabla: $tabla, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar value',data:  $value_html);
        }

        $datas = new stdClass();
        $datas->selected = $selected;

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
     * Genera required en forma html para ser integrado en un input
     * @version 1.87.19
     * @stable true
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
