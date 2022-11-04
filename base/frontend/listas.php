<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class listas{
    private errores $error;
    private validaciones_directivas $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();

    }

    /**
     * P INT P ORDER PROBADO
     * @param string $seccion
     * @param array $registro
     * @return array|string
     */
    private function footer_registro(array $registro, string $seccion): array|stdClass{

        $valida = $this->validacion->valida_footer_row(registro: $registro,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data: $valida, params: get_defined_vars());
        }


        $td_acciones = $this->td_acciones();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td de acciones',data: $td_acciones);
        }

        $data = new stdClass();
        $data->td_acciones = $td_acciones;
        $data->registro = $registro;

        return $data;
    }




    /**
     * P ORDER P INT
     * @param int $id
     * @param string $panel_class
     * @param array $registro
     * @param string $seccion
     * @return array|string
     */
    private function registro( int $id, string $panel_class, array $registro, string $seccion): array|string
    {

        $tr_data = $this->tr_data(registro: $registro, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar tr',data: $tr_data);
        }
        $key_status = $seccion.'_status';
        $status = $registro[$key_status];
        return "<tr class='$panel_class registro_lista' data-registro_id='$id' data-status_row = '$status' role='button'>$tr_data</tr>";

    }



    /**
     * PROBADO-PARAMS ORDER
     * @return string
     */
    private function td_acciones(): string
    {

        $td = $this->td_acciones_base('');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td',data: $td);
        }


        return $td;
    }

    private function td_acciones_base(string $boton_acciones): string
    {

        $html = '<td data-toggle="modal" data-target=".menu_acciones_lista" class="no-print">';
        $html .= $boton_acciones;
        $html .= '</td>';
        return $html;
    }



    /**
     * P ORDER P INT PROBADO
     * @param array $registro
     * @param string $seccion
     * @return array|string
     */
    private function tr_data( array $registro, string $seccion): array|string
    {
        $valida = $this->validacion->valida_footer_row(registro: $registro,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data: $valida, params: get_defined_vars());
        }


        $footer = $this->footer_registro(registro: $registro, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar footer',data: $footer);
        }

        return $footer->td_acciones.$footer->td_acciones;
    }

}
