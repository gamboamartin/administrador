<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use models\adm_accion;
use PDO;
use stdClass;

class listas{
    private errores $error;
    private validaciones_directivas $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();

    }






    /**
     *
     * @param string $seccion
     * @return array|string
     */
    private function filtros_para_lista(string $seccion): array|string
    {
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia',data: $seccion);
        }
        if(!class_exists($clase)){
            return $this->error->error(mensaje: 'Error no existe la clase '.$clase,data: $clase);
        }
        $filtro = $this->obten_filtros_session(seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros de session',$filtro);
        }
        if(!is_array($filtro)){
            return $this->error->error('Error filtro debe ser un array',$filtro);
        }


        return '<div class="col-md-12"><hr></div><div class="row col-md-12">'.'</div><div class="row col-md-12 form-row filtro-base">'.'</div>';
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

        $registro = $this->registro_status(registro: $registro, seccion: $seccion );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar status en registro',data: $registro);
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
     * P INT
     * @param string $seccion
     * @return array|string
     */

    public function genera_filtros_lista( string $seccion ): array|string
    {
        $html = '';


        $inputs_filtro_html = $this->filtros_para_lista(seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar filtros de lista',$inputs_filtro_html);
        }

        $html.=$inputs_filtro_html;



        return $html;
    }


    /**
     * P ORDER P INT
     * @param string $campo_id
     * @param array $registros
     * @param string $seccion
     * @return array|string
     */
    public function lista(string $campo_id, array $registros, string $seccion): array|string
    {

        $html = '';
        foreach ($registros as $key => $registro) {
            $key_id = $seccion . '_id';
            if(!isset($registro[$key_id])){
                return $this->error->error(mensaje: 'Error no existe campo $registro['.$seccion . '_id]',data: $registro);
            }

            $panel_html = $this->panel_completo( id:  $registro[$campo_id], registro: $registro,
                seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar panel html',data: $panel_html);
            }
            $html .= $panel_html;
        }



        return $html;
    }



    /**
     * P ORDER P INT ERROREV
     * @param string $seccion
     * @return mixed
     */
    private function obten_filtros_session(string $seccion): mixed
    {
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia',data: $seccion, params: get_defined_vars());
        }
        if(!class_exists($clase)){
            return $this->error->error(mensaje: 'Error no existe la clase '.$seccion,data: $seccion,
                params: get_defined_vars());
        }

        return $_SESSION['filtros'][$seccion]??array();
    }

    /**
     * P INT
     * @param string $filtros_lista
     * @param string $filtro_boton_seleccionado_html
     * @param string $seccion
     * @param string $session_id
     * @return array|string
     */
    public function obten_html_filtros(string $filtros_lista, string $filtro_boton_seleccionado_html,
                                       string $seccion, string $session_id): array|string
    {
        if($filtros_lista === ''){
            return $this->error->error('Error $filtros_lista no puede venir vacio',$filtros_lista);
        }

        $html  =  '<form method="POST" action="./index.php?seccion='.$seccion.'&accion=aplica_filtro&session_id='.$session_id.'" class="no-print">';
        $html .=    "<div class='col-md-12 no-print'><hr></div>";
        $html .=        $filtros_lista;
        $html.="        <div class='row col-md-12 no-print'>";



        $html.="<div class='col-md-4'>";

        $html.="</div>";


        $html.="<div class='col-md-4'>";

        $html.="</div>";

        $seccion_xls = $seccion;
        $accion_xls = 'xls_lista';



        $html.="<div class='col-md-4'>";

        $html.="</div>";

        $html.=         "</div>";
        $html .=  "<div class='col-md-12'><hr></div>";

        $html.='</form>';


        return $html;
    }

    /**
     * Ajusta el panel si es inactivo como rojo
     * @version 1.37.14
     * @param string $status Status del registro
     * @return array|string
     */
    private function obten_panel(string $status): array|string
    {
        $status = trim($status);
        if($status === ''){
            return $this->error->error(mensaje: 'Error status debe tener datos',data: $status);
        }
        if($status === 'activo'){
            $panel_class = '';
        }
        else{
            $panel_class = 'bg-danger';
        }

        return $panel_class;
    }

    /**
     * P ORDER P INT
     * @param array $registro
     * @param int $id
     * @param string $seccion
     * @param array $campos
     * @return array|string
     */
    private function panel_completo( int $id, array $registro, string $seccion): array|string
    {

        $html = '';
        $status = $registro[$seccion . '_status'];
        if((string)$status === ''){
            $status = 'inactivo';
        }
        $panel_class = $this->obten_panel(status: $status);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar panel',data: $panel_class);
        }


        return $html;
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
     * P ORDER P INT PROBADO
     * @param string $seccion
     * @param array $registro
     * @return array
     */
    private function registro_status(array $registro, string $seccion): array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia',data: $seccion);
        }

        $key_status = $seccion.'_status';
        if(!isset($registro[$key_status])){
            $registro[$key_status] = 'inactivo';
        }
        if((string)$registro[$key_status] === ''){
            $registro[$key_status] = 'inactivo';
        }
        return $registro;

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
