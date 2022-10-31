<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;

class forms{
    private errores $error;

    #[Pure] public function __construct(){
        $this->error = new errores();

    }


    /**
     * Genera un header form div css
     * @param int $cols N columnas css
     * @return string|array
     * @version 1.455.49
     */
    public function header_form_group(int $cols): string|array
    {
        $valida = (new validaciones_directivas())->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar cols",data:  $valida);
        }

        return '<div class="form-group col-md-'.$cols.'">';
    }


}
