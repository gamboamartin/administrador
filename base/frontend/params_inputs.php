<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;


class params_inputs{




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
