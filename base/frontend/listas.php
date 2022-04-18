<?php
namespace base\frontend;
use base\orm\modelo_base;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
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
     *  P ORDER P INT PROBADO
     * @param string $accion
     * @param string $status
     * @return bool|array|stdClass
     */
    private function accion_activa_desactiva(string $accion, string $status): bool|array|stdClass
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error('Error accion esta vacia',$accion);
        }
        if ($accion === 'desactiva_bd' || $accion === 'activa_bd') {

            $act_dec = $this->activa_desactiva(status: $status);
            if(errores::$error){
                return $this->error->error('Error al obtener accion',$act_dec);
            }
            $accion = $act_dec;
        }

        $data = new stdClass();
        $data->accion = $accion;
        return $data;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $status
     * @return string
     */
    private function activa_desactiva(string $status): string
    {
        if ($status === 'activo') {
            $accion = 'desactiva_bd';
        }
        else {
            $accion = 'activa_bd';
        }
        return $accion;
    }

    private function asigna_accion_to_session(PDO $link, string $accion, string $seccion){
        $datos_accion_bd = $this->datos_accion_bd(link: $link,accion: $accion,seccion: $seccion);
        if (errores::$error) {
            return $this->error->error('Error al obtener acciones', $datos_accion_bd);
        }
        $_SESSION['datos_accion'][$seccion][$accion] = $datos_accion_bd;

        return $datos_accion_bd;
    }

    /**
     *
     * @param string $id
     * @param string $seccion
     * @param string $accion
     * @param $session_id
     * @param string $class_link
     * @param PDO $link
     * @return array|string
     */
    private function asigna_datos_accion_link(string $id, string $seccion, string $accion, $session_id,
                                              string $class_link, PDO $link): array|string
    {
        if ($accion === '') {
            return $this->error->error('Error la accion esta vacia', $accion);
        }
        $directiva = new directivas();
        $datos_accion = $this->datos_accion($seccion,$accion, $link);
        if(errores::$error){
            return $this->error->error('Error al obtener acciones',$datos_accion);
        }

        $link_accion = $directiva->genera_link_accion($datos_accion, $id, $session_id, $class_link);
        if(errores::$error){
            return $this->error->error('Error al generar link',$link_accion);
        }

        return $link_accion;
    }

    private function btn_acciones_row(): string
    {
        return '<button class="btn btn-outline-info  btn-sm"><i class="bi bi-chevron-down"></i> Acciones </button>';
    }

    /**
     * P ORDER P INT
     * @param string $campo
     * @param int $valor
     * @param string $etiqueta
     * @param string $seccion
     * @param string $session_id
     * @param string $class
     * @return array|string
     */
    private function btn_filtro_rapido(string $campo, string $etiqueta, string $seccion,  string $session_id,
                                       int $valor, string $class='primary'):array|string{
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($campo === ''){
            return  $this->error->error("Error campo vacio",$campo);
        }
        if($valor <=0 ){
            return  $this->error->error("Error el valor er menor a 0",$valor);
        }
        if($etiqueta === ''){
            return $this->error->error("Etiqueta vacia",$etiqueta);
        }
        if($seccion === ''){
            return $this->error->error("Error la seccion esta vacia",$seccion);
        }
        if(!class_exists($clase)){
            return $this->error->error("Error la clase es invalida",$clase);
        }
        if(is_numeric($campo)){
            return $this->error->error("Error el campo es un numero",$campo);
        }

        $boton = "<a class='btn btn-$class btn-sm' href='index.php?seccion=$seccion&accion=lista&session_id=";
        $boton.=$session_id."&filtro_btn[$campo]=$valor' role='button'>$etiqueta</a>";
        return $boton;
    }

    /**
     * PROBADO P ORDER P INT
     * @param array $etiqueta_campos
     * @param string $seccion
     * @return array|string
     */
    private function campos_lista_html(array $etiqueta_campos, string $seccion): array|string
    {
        if(count($etiqueta_campos) === 0){
            return $this->error->error('Error $etiqueta_campos esta vacio',$etiqueta_campos);
        }
        $html = '';
        foreach($etiqueta_campos as $campo){
            $campo = trim($campo);
            if($campo === '' ){
                return $this->error->error('Error campo no puede venir vacio',$campo);
            }
            $campo_ajustado = $this->parsea_ths_html(campo:  $campo, seccion: $seccion);
            if(errores::$error){
                return $this->error->error('Error al ajustar campos',$campo_ajustado);
            }
            $html .=$campo_ajustado;
        }

        return $html;
    }

    /**
     *
     * @param string $seccion
     * @param array $botones_filtro
     * @param string $session_id
     * @return array|string
     */
    private function carga_botones_filtro_rapido(string $seccion, array $botones_filtro, string $session_id): array|string
    {
        $filtro_btn_html = '';
        foreach($botones_filtro as $tabla=>$boton){
            $filtro_btn_html = $this->genera_conjunto_filtro_rapido($tabla,$filtro_btn_html, $boton, $seccion, $session_id);
            if(errores::$error){
                return $this->error->error('Error al generar conjunto de botones',$filtro_btn_html);
            }
        }

        return $filtro_btn_html;

    }

    /**
     * P ORDER P INT PROBADO
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
            return $this->error->error('Error al asignar valor campo',$valida);
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
            return $this->error->error('Error al asignar valor campo',$valida);
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
            return $this->error->error('Error al asignar valor campo',$valida);
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
            return $this->error->error('Error al asignar valor campo',$valida);
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
            return $this->error->error('Error al asignar valor campo',$valida);
        }
        if($campo['representacion'] === 'telefono'){
            $dato = "<a href='tel:$dato'>$dato</a>";
        }
        return $dato;
    }

    /**
     *
     * @param string $seccion
     * @param string $accion
     * @param PDO $link
     * @return array
     */
    private function datos_accion(string $seccion, string $accion, PDO $link): array
    {

        $valida = $this->validacion->valida_datos_accion(accion: $accion, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error('Error al validar datos', $valida);
        }

        if(!isset($_SESSION['datos_accion'][$seccion][$accion])) {
            $datos_accion_bd = $this->asigna_accion_to_session(link: $link,accion: $accion,seccion: $seccion);
            if (errores::$error) {
                return $this->error->error('Error al asignar acciones', $datos_accion_bd);
            }
        }

        return $_SESSION['datos_accion'][$seccion][$accion];
    }

    private function datos_accion_bd(PDO $link, string $accion, string $seccion){
        $accion_modelo = (new modelo_base($link))->genera_modelo(modelo: 'accion');
        if (errores::$error) {
            return $this->error->error('Error al generar modelo', $accion_modelo);
        }

        $filtro['accion.descripcion'] = $accion;
        $filtro['seccion.descripcion'] =$seccion;

        $resultado = $accion_modelo->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error('Error al obtener acciones', $resultado);
        }
        if ((int)($resultado->n_registros) === 0) {
            return $this->error->error( 'Error no existen acciones',  $resultado);
        }
        return $resultado->registros[0];
    }

    /**
     *
     * @param string $seccion
     * @param array $campos_filtro
     * @return array|string
     */
    private function filtros_para_lista(string $seccion, array $campos_filtro): array|string
    {
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($seccion === ''){
            return $this->error->error('Error seccion esta vacia',$seccion);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        $filtro = $this->obten_filtros_session(seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros de session',$filtro);
        }
        if(!is_array($filtro)){
            return $this->error->error('Error filtro debe ser un array',$filtro);
        }

        $inputs_filtro_html = $this->genera_filtros_html_lista(filtro: $filtro,campos_filtro:  $campos_filtro);
        if(errores::$error){
            return $this->error->error('Error al generar filtros de lista',$inputs_filtro_html);
        }

        $btn = (new directivas())->btn_enviar(label:'Filtro',type: 'button',class_css: array('btn-filtro'));
        if(errores::$error){
            return $this->error->error('Error al generar boton',$btn);
        }

        return '<div class="col-md-12"><hr></div><div class="row col-md-12">'.$btn.'</div><div class="row col-md-12 form-row filtro-base">'.$inputs_filtro_html.'</div>';;
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
            return $this->error->error('Error al validar datos',$valida);
        }

        $registro = $this->registro_status(registro: $registro, seccion: $seccion );
        if(errores::$error){
            return $this->error->error('Error al asignar status en registro',$registro);
        }

        $td_acciones = $this->td_acciones();
        if(errores::$error){
            return $this->error->error('Error al generar td de acciones',$td_acciones);
        }

        $data = new stdClass();
        $data->td_acciones = $td_acciones;
        $data->registro = $registro;

        return $data;
    }

    /**
     * P ORDER P INT
     * @param array $boton
     * @param string $filtro_btn_html
     * @param string $seccion
     * @param string $session_id
     * @return array|string
     */
    private function genera_botones_filtro_rapido_seccion(array $boton, string $filtro_btn_html, string $seccion,
                                                          string $session_id): array|string
    {

        foreach($boton as $data_boton){
            if(!is_array($data_boton)){
                return $this->error->error('Error $data_boton debe ser un array',$data_boton);
            }

            $valida = $this->validacion->valida_data_btn_template(data_boton: $data_boton);
            if(errores::$error){
                return $this->error->error('Error al validar boton',$valida);
            }

            $boton_html = $this->genera_btn_filtro_rapido_completo(data_boton: $data_boton,seccion:  $seccion,
                session_id: $session_id);
            if(errores::$error){
                return $this->error->error('Error al generar boton',$boton_html);
            }
            $filtro_btn_html.=$boton_html;
        }


        return $filtro_btn_html;
    }

    /**
     * P ORDER P INT
     * @param array $data_boton
     * @param string $seccion
     * @param string $session_id
     * @return array|string
     */
    private function genera_btn_filtro_rapido(array $data_boton, string $seccion, string $session_id): array|string
    { //FIN
        $valida = $this->validacion->btn_base(data_boton: $data_boton);
        if(errores::$error){
            return $this->error->error('Error al validar data_boton',$valida);
        }

        if(is_array($data_boton['etiqueta'])){
            return $this->error->error('Error $data_boton[etiqueta] debe ser un string',$data_boton);
        }
        if(is_array($data_boton['id'])){
            return $this->error->error('Error $data_boton[id] debe ser un int',$data_boton);
        }
        if($data_boton['id'] === ''){
            return $this->error->error('Error id no puede venir vacio',$data_boton['id']);
        }
        $valida = $this->validacion->btn_second(data_boton: $data_boton);
        if(errores::$error){
            return $this->error->error('Error al validar data_boton',$valida);
        }
        if(!is_numeric($data_boton['id'])){
            return $this->error->error('Error $data_boton[id] debe ser numero',$data_boton);
        }
        $campo = key($data_boton['filtro']);
        if(is_numeric($campo)){
            return $this->error->error('Error campo debe ser un string ',$campo);
        }
        if($campo === NULL){
            return $this->error->error('Error campo debe ser un string con datos',$campo);
        }
        $valor = (int)$data_boton['id'];
        $boton = $this->btn_filtro_rapido(campo: $campo, etiqueta: $data_boton['etiqueta'], seccion:  $seccion,
            session_id: $session_id, valor: $valor, class: $data_boton['class']);
        if(errores::$error){
            return $this->error->error('Error al generar boton',$boton);
        }

        return $boton;
    }

    /**
     * P ORDER P INT
     * @param array $data_boton
     * @param string $seccion
     * @param string $session_id
     * @return array|string
     */
    private function genera_btn_filtro_rapido_completo(array $data_boton, string $seccion, string $session_id): array|string
    {

        $valida = $this->validacion->valida_data_btn_template(data_boton: $data_boton);
        if(errores::$error){
            return $this->error->error('Error al validar boton',$valida);
        }
        $boton = $this->genera_btn_filtro_rapido(data_boton: $data_boton, seccion: $seccion, session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al generar boton',$boton);
        }

        $boton.='';
        return $boton;

    }



    /**
     * P ORDER P INT
     * @param string $seccion
     * @param array $etiqueta_campos
     * @return array|string
     */
    private function genera_campos_elementos_lista(array $etiqueta_campos, string $seccion):array|string{

        if(count($etiqueta_campos) === 0){
            return $this->error->error('Error $etiqueta_campos esta vacio',$etiqueta_campos);
        }

        $td_acciones_html = $this->td_acciones_html();
        if(errores::$error){
            return $this->error->error('Error al generar td',$td_acciones_html);
        }

        $campos_lista_html = $this->campos_lista_html(etiqueta_campos: $etiqueta_campos,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error('Error al ajustar campos',$campos_lista_html);
        }

        return $td_acciones_html.$campos_lista_html;
    }

    /**
     *
     * @param string $tabla
     * @param string $filtro_btn_html
     * @param array $boton
     * @param string $seccion
     * @param string $session_id
     * @return array|string
     */
    private function genera_conjunto_filtro_rapido(string $tabla,string $filtro_btn_html, array $boton,
                                                   string $seccion, string $session_id): array|string
    {
        $filtro_btn_html = $this->titulo_conjunto_filtro_rapido(filtro_btn_html: $filtro_btn_html, tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al generar titulos',$filtro_btn_html);
        }
        $filtro_btn_html = $this->genera_botones_filtro_rapido_seccion(boton: $boton,filtro_btn_html: $filtro_btn_html,
            seccion:  $seccion,session_id:  $session_id);
        if(errores::$error){
            return $this->error->error('Error al generar botones',$filtro_btn_html);
        }

        return $filtro_btn_html;
    }

    /**
     *
     * @param array $filtro
     * @param array $campos_filtro
     * @return array|string
     */
    private function genera_filtros_html_lista(array $filtro, array $campos_filtro): array|string
    {
        $inputs_filtro_html = '';
        foreach($campos_filtro as $campo) {
            if(!is_array($campo)){
                return $this->error->error('Error campo debe ser un array',$campo);
            }
            $input_text = $this->genera_input_text_filtro(campo: $campo,filtro: $filtro);
            if(errores::$error){
                return $this->error->error('Error al generar input',$input_text);
            }
            $inputs_filtro_html .= $input_text;
        }

        return $inputs_filtro_html;
    }

    /**
     *
     * @param string $seccion
     * @param array $botones_filtros
     * @param array $campos_filtro
     * @return array|string
     */

    public function genera_filtros_lista(string $seccion, array $botones_filtros, array $campos_filtro, string $session_id ): array|string
    {
        $html = '';

        $filtro_btn_html = $this->carga_botones_filtro_rapido(seccion: $seccion,botones_filtro:  $botones_filtros, session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al generar filtros rapidos',$filtro_btn_html);
        }
        $html.=$filtro_btn_html;

        $inputs_filtro_html = $this->filtros_para_lista(seccion: $seccion, campos_filtro: $campos_filtro);
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
     *
     * @param array $campo
     * @param array $filtro
     * @return string|array
     */
    private function genera_input_text_filtro(array $campo, array $filtro): string|array
    {
        $keys = array('etiqueta','tabla_externa','campo');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $campo);
        if(errores::$error){
            return $this->error->error('Error al validar campo',$valida);
        }
        $etiqueta = $campo['etiqueta'];
        $value = $filtro[$campo['tabla_externa']][$campo['campo']]['value']??false;

        $input_text = $this->genera_text_filtro_lista(campo: $campo,etiqueta: $etiqueta,value: $value);
        if(errores::$error){
            return $this->error->error('Error al generar input',$input_text);
        }

        return $input_text;
    }

    /**
     *
     * @param array $campo
     * @param string $etiqueta
     * @param string $value
     * @return array|string
     */
    private function genera_text_filtro_lista(array $campo, string $etiqueta, string $value):array|string{
        $directiva = new directivas();
        $clases_css = array('filtro');
        $ids_css = array('filtro');
        $input_text = $directiva->genera_input_text(campo: "filtro[$campo[tabla_externa]][$campo[campo]]",
            cols: 2,value: $value,required: false,etiqueta: $etiqueta, data_extra: array(), clases_css: $clases_css,
            ids_css: $ids_css,size: 'sm');
        if(errores::$error){
            return $this->error->error('Error al generar input',$input_text);
        }
        return $input_text;
    }

    /**
     * P ORDER P INT
     * @param string $seccion
     * @param array $etiqueta_campos
     * @return array|string
     */
    public function genera_th(array $etiqueta_campos, string $seccion):array|string{
        if(count($etiqueta_campos) === 0){
            return $this->error->error('Error $etiqueta_campos esta vacio',$etiqueta_campos);
        }
        $html = $this->genera_campos_elementos_lista(etiqueta_campos: $etiqueta_campos, seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar campos',$html);
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
    public function lista(string $campo_id, array $campos, array $registros, string $seccion): array|string
    {
        if(count($campos) === 0){
            return $this->error->error('Error los campos de lista no pueden venir vacios',$campos);
        }
        $html = '';
        foreach ($registros as $key => $registro) {
            $key_id = $seccion . '_id';
            if(!isset($registro[$key_id])){
                return $this->error->error('Error no existe campo $registro['.$seccion . '_id]',$registro);
            }

            $panel_html = $this->panel_completo(campos: $campos, id:  $registro[$campo_id], registro: $registro,
                seccion: $seccion);
            if(errores::$error){
                return $this->error->error('Error al generar panel html',$panel_html);
            }
            $html .= $panel_html;
        }



        return $html;
    }

    /**
     *
     * @param string $id
     * @param string $status
     * @param array $acciones
     * @param string $seccion
     * @param string $class_link
     * @param PDO $link
     * @param string $session_id
     * @return array|string
     */
    public function obten_acciones( string $id, string $status, array $acciones, string $seccion,
                                    string $class_link, PDO $link, string $session_id): array|string
    {
        $html = '';

        $modelo_accion = (new modelo_base($link))->genera_modelo('accion');
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo_accion);
        }


        foreach ($acciones as $accion){


            /*$data_accion = $this->accion_activa_desactiva(accion: $accion, status: $status);
            if (errores::$error) {
                return $this->error->error('Error al obtener accion', $data_accion);
            }

            $accion = $data_accion->accion;*/

            $acciones_permitidas = $modelo_accion->obten_accion_permitida_session(seccion:$seccion, accion:$accion);

            if(errores::$error){
                return $this->error->error('Error al obtener acciones permitidas',$acciones_permitidas);
            }

            if ($acciones_permitidas) {
                $link_accion = $this->asigna_datos_accion_link($id, $seccion, $accion, $session_id, $class_link, $link);
                if(errores::$error){
                    return $this->error->error('Error al generar link',$link_accion);
                }
                $html.=$link_accion;
            }
        }

        return $html;
    }

    /**
     *
     * @param string $seccion
     * @return mixed
     */
    private function obten_filtros_session(string $seccion): mixed
    {
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($seccion === ''){
            return $this->error->error('Error seccion esta vacia',$seccion);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$seccion,$seccion);
        }

        return $_SESSION['filtros'][$seccion]??array();
    }

    /**
     *
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
        $directiva = new directivas();

        $html  =  '<form method="POST" action="./index.php?seccion='.$seccion.'&accion=aplica_filtro&session_id='.$session_id.'" class="no-print">';
        $html .=    "<div class='col-md-12 no-print'><hr></div>";
        $html .=        $filtros_lista;
        $html.="        <div class='row col-md-12 no-print'>";


        $btn = $directiva->btn_enviar(12,'Filtrar','btn_filtrar','activo','submit','success btn-sm',array('filtro-base'));
        if(errores::$error){
            return $this->error->error('Error al generar btn',$btn);
        }
        $html.="<div class='col-md-4'>";
        $html .=  $btn;
        $html.="</div>";

        $btn = $directiva->btn_enviar(12,'Limpiar','btn_limpiar','activo','submit','warning btn-sm',array('filtro-base'));
        if(errores::$error){
            return $this->error->error('Error al generar btn',$btn);
        }
        $html.="<div class='col-md-4'>";
        $html .=  $btn;
        $html.="</div>";

        $seccion_xls = $seccion;
        $accion_xls = 'xls_lista';

        $btn =  "
                <a href='./index.php?seccion=" . $seccion_xls . '&accion=' . $accion_xls . '&session_id=' . $session_id . $filtro_boton_seleccionado_html."'  
                title='Exporta Excel' alt='Exporta Excel' class='no-print'>
            " . $directiva->btn_enviar(12, 'Exporta Excel','btn_xls','activo','button','info btn-sm') . '</a>';

        if(errores::$error){
            return $this->error->error('Error al generar btn',$btn);
        }

        $html.="<div class='col-md-4'>";
        $html .=  $btn;
        $html.="</div>";

        $html.=         "</div>";
        $html .=  "<div class='col-md-12'><hr></div>";

        $html.='</form>';


        return $html;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $status
     * @return array|string
     */
    private function obten_panel(string $status): array|string
    {
        $status = trim($status);
        if($status === ''){
            return $this->error->error('Error status debe tener datos',$status);
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
    private function panel_completo(array $campos,  int $id, array $registro, string $seccion): array|string
    {
        if(count($campos) === 0){
            return $this->error->error('Error los campos de lista no pueden venir vacios',$campos);
        }
        $html = '';
        $status = $registro[$seccion . '_status'];
        if((string)$status === ''){
            $status = 'inactivo';
        }
        $panel_class = $this->obten_panel(status: $status);
        if(errores::$error){
            return $this->error->error('Error al generar panel',$panel_class);
        }
        $registro_html = $this->registro( campos: $campos, id:  $id, panel_class: $panel_class,registro:  $registro,
            seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar registro',$registro_html);
        }
        $html .=$registro_html;


        return $html;
    }

    /**
     * PROBADO P ORDER P INT
     * @param string $seccion
     * @param string $campo
     * @return array|string
     */
    private function parsea_ths_html(string $campo, string $seccion):array|string{
        $campo = trim($campo);
        if($campo === '' ){
            return $this->error->error('Error campo no puede venir vacio',$campo);
        }
        $html = '';
        $campo_sin_tabla = str_replace($seccion,' ', $campo);
        $campo_sin_guion = str_replace('_',' ',$campo_sin_tabla);
        $campo_mayuscula = ucwords($campo_sin_guion);
        $html .= "<th class='text-uppercase text-truncate td-90'>$campo_mayuscula</th>";
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
        if(count($campos) === 0){
            return $this->error->error('Error los campos de lista no pueden venir vacios',$campos);
        }

        $tr_data = $this->tr_data(campos: $campos,registro: $registro, seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar tr',$tr_data);
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
            return $this->error->error('Error seccion esta vacia',$seccion);
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
        $boton_acciones = (new botones())->boton_acciones_list();
        if(errores::$error){
            return $this->error->error('Error al generar boton',$boton_acciones);
        }

        $td = $this->td_acciones_base($boton_acciones);
        if(errores::$error){
            return $this->error->error('Error al generar td',$td);
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
     * PROBADO P ORDER P INT
     * @return string
     */
    private function td_acciones_html(): string
    {
        return'<td class="no-print">ACCIONES</td>';
    }

    private function td_acciones_row(): array|string
    {
        $boton_acciones = $this->btn_acciones_row();
        if(errores::$error){
            return $this->error->error('Error al generar boton',$boton_acciones);
        }

        $html = '<td data-toggle="modal" data-target=".menu_acciones_lista" class="no-print">';
        $html .= $boton_acciones;
        $html .= '</td>';

        return $html;
    }

    /**
     * P ORDER P INT
     * @param string $tabla
     * @param string $filtro_btn_html
     * @return string
     */
    private function titulo_conjunto_filtro_rapido(string $filtro_btn_html, string $tabla):string{
        $titulo =str_replace('_',' ',$tabla);
        $titulo =ucwords($titulo);

        $filtro_btn_html.='<div class="col-12 filtro_rapido">';
        $filtro_btn_html.='<span class="letra-grande"><b>'.$titulo.'</b></span>';
        $filtro_btn_html.='</div>';


        return $filtro_btn_html;
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
            return $this->error->error('Error al validar datos',$valida);
        }

        $row_html = $this->row_html(campos: $campos, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al generar row',$row_html);
        }

        $footer = $this->footer_registro(registro: $registro, seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar footer',$footer);
        }

        return $footer->td_acciones.$row_html.$footer->td_acciones;
    }

}
