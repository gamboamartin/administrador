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
     * P ORDER P INT PROBADO ERROREV
     * @return stdClass
     */
    #[Pure] public function data_accion_limpia(): stdClass
    {
        $href = '#';
        $modal = "data-toggle='modal' data-target='#modalAccion'";
        $btn_modal = 'btn_modal';
        $data = new stdClass();
        $data->href = $href;
        $data->modal = $modal;
        $data->btn_modal = $btn_modal;
        return $data;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $campo
     * @param array $registro
     * @return array|string
     */
    PUBLIC function data_html(array $campo, array $registro): array|string
    {
        $keys = array('representacion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valor campo',data: $valida);
        }

        $dato = $this->init_dato_campo(campo: $campo,registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al obtener dato',$dato);
        }

        $dato = $this->dato_campo(campo: $campo,dato:  $dato);
        if(errores::$error){
            return $this->error->error('Error al asignar valor',$dato);
        }

        $data_html = $this->genera_html_dato(dato: $dato);
        if(errores::$error){
            return $this->error->error('Error al generar html',$data_html);
        }
        return $data_html;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $campo
     * @param array $registro
     * @return array|string
     */
    PUBLIC function data_row_html(array $campo, array $registro): array|string
    {
        $keys = array('representacion','nombre_campo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valor campo',data: $valida);
        }

        if(!isset($registro[$campo['nombre_campo']])){
            $registro[$campo['nombre_campo']] = '';
        }

        $data_html = $this->data_html(campo: $campo, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al generar html',$data_html);
        }

        return $data_html;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $campo
     * @param float|string|int $dato
     * @return float|int|array|string
     */
    private function dato_campo(array $campo, float|string|int $dato): float|int|array|string
    {
        $keys = array('representacion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valor campo',data: $valida,
                params: get_defined_vars());
        }
        $dato_env = $this->dato_moneda(campo: $campo,dato:  $dato);
        if(errores::$error){
            return $this->error->error('Error al asignar valor moneda',$dato_env);
        }

        $dato_env = $this->dato_telefono(campo: $campo,dato:  $dato_env);
        if(errores::$error){
            return $this->error->error('Error al asignar valor telefono',$dato_env);
        }

        return $dato_env;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $campo
     * @param float|int|string $dato
     * @return float|int|array|string
     */
    private function dato_moneda(array $campo, float|int|string $dato): float|int|array|string
    {
        $keys = array('representacion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valor campo',data: $valida,
                params: get_defined_vars());
        }
        $dato_env = $dato;
        if($campo['representacion'] === 'moneda'){
            $dato_env = (new values())->valor_moneda(valor: $dato);
            if(errores::$error){
                return $this->error->error('Error al asignar valor moneda',$dato_env);
            }
        }
        return $dato_env;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $campo
     * @param float|int|string $dato
     * @return float|int|string|array
     */
    private function dato_telefono(array $campo, float|int|string $dato): float|int|string|array
    {
        $keys = array('representacion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valor campo',data: $valida,
                params: get_defined_vars());
        }
        if($campo['representacion'] === 'telefono'){
            $dato = "<a href='tel:$dato'>$dato</a>";
        }
        return $dato;
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
     * Funcion que genera los elementos para un listado
     * @version 1.36.14
     * @param string $seccion Seccion en ejecucion
     * @param array $etiqueta_campos campos para ajustar la lista
     * @return array|string
     */
    private function genera_campos_elementos_lista():array|string{


        $td_acciones_html = $this->td_acciones_html();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td',data: $td_acciones_html);
        }


        return $td_acciones_html;
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
     * PROBADO P ORDER P INT
     * @param string $dato
     * @return string
     */
    private function genera_html_dato(string $dato): string{
        $class = 'text-uppercase text-truncate td-90';
        $html = "<td class='$class' data-toggle='tooltip' data-placement='top' title='$dato'>";
        $html .= $dato;
        $html .= '</td>';

        return $html;
    }



    /**
     * P ORDER P INT
     * @param string $seccion
     * @param array $etiqueta_campos
     * @return array|string
     */
    public function genera_th():array|string{

        $html = $this->genera_campos_elementos_lista();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar campos',data: $html);
        }
        $html .= '<th class="no-print">ACCIONES</th>';
        return $html;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $campo
     * @param array $registro
     * @return string|array
     */
    private function init_dato_campo(array $campo, array $registro): string|array
    {
        $keys = array('nombre_campo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error('Error al validar campo', $valida);
        }

        if(!isset($registro[$campo['nombre_campo']])){
            $registro[$campo['nombre_campo']] = '';
        }
        return (string)$registro[$campo['nombre_campo']];
    }

    /**
     * P ORDER P INT
     * @param array $registros
     * @param string $campo_id
     * @param string $seccion
     * @param array $campos
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
     * @param string $panel_class
     * @param array $registro
     * @param int $id
     * @param string $seccion
     * @param array $campos
     * @return array|string
     */
    private function registro(array $campos, int $id, string $panel_class, array $registro, string $seccion): array|string
    {

        $tr_data = $this->tr_data(campos: $campos,registro: $registro, seccion: $seccion);
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
     * P ORDER P INT PROBADO
     * @param array $campos
     * @param array $registro
     * @return array|string
     */
    private function row_html(array $campos, array $registro): array|string
    {
        $html = '';
        foreach ($campos as $campo) {
            if(!is_array($campo)){
                return $this->error->error('Error el campo debe ser un array',$campos);
            }
            if(!isset($campo['representacion']) || $campo['representacion'] === ''){
                $campo['representacion'] = 'NO APLICA';
            }
            $data_html = $this->data_row_html(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al generar html',$data_html);
            }

            $html .= $data_html;
        }
        return $html;
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
     * Genera un td con acciones
     * @version 1.33.14
     * @return string
     */
    private function td_acciones_html(): string
    {
        return'<td class="no-print">ACCIONES</td>';
    }


    /**
     * P ORDER P INT PROBADO
     * @param array $campos
     * @param array $registro
     * @param string $seccion
     * @return array|string
     */
    private function tr_data(array $campos, array $registro, string $seccion): array|string
    {
        $valida = $this->validacion->valida_footer_row(registro: $registro,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data: $valida, params: get_defined_vars());
        }

        $row_html = $this->row_html(campos: $campos, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al generar row',$row_html);
        }

        $footer = $this->footer_registro(registro: $registro, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar footer',data: $footer);
        }

        return $footer->td_acciones.$row_html.$footer->td_acciones;
    }

}
