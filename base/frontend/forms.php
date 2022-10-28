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
     *
     * @return string
     */
    public function data_form_base(): string
    {
        return 'method="POST" enctype="multipart/form-data"';
    }


    /**
     * Obtiene el header de un formulario
     * @param string $seccion Seccion en ejecucion
     * @param string $accion Accion ene ejecucion
     * @param string $accion_request accion a ejecutar
     * @param string $session_id Session de seguridad
     * @return string|array
     * @version 1.232.39
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-08-01 13:39
     */
    public function header_form( string $accion, string $accion_request, string $seccion,
                                 string $session_id): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error $seccion no puede venir vacio', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje:'Error $accion no puede venir vacio',data: $accion);
        }
        $accion_request = trim($accion_request);
        if($accion_request === ''){
            return $this->error->error(mensaje:'Error $accion_request no puede venir vacio',data: $accion_request);
        }
        $session_id = trim($session_id);
        if($session_id === ''){
            return $this->error->error(mensaje:'Error $session_id no puede venir vacio',data: $session_id);
        }

        return "<form id='form-$seccion-$accion' name='form-$seccion-alta' method='POST' 
            action='./index.php?seccion=$seccion&session_id=$session_id&accion=$accion_request' 
            enctype='multipart/form-data'>";
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
