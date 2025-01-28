<?php
namespace base\controller;

use base\orm\modelo;
use config\generales;
use config\views;
use gamboamartin\administrador\ctl\normalizacion_ctl;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_session;
use gamboamartin\errores\errores;
use PDO;
use stdClass;
use Throwable;


class controler{
    public modelo $modelo;
    public int $registro_id = -1;
    public string $seccion = '';
    public bool $acceso_denegado = false;

    public errores $errores;

    public valida_controller $validacion;

    public PDO $link ;
    public array|stdClass $registro = array();
    public string $tabla = '';
    public string $accion = '';
    public array|stdClass $inputs = array();
    public string $breadcrumbs = '';
    public array $registros = array();
    public array $orders = array();
    public array $filtro_boton_lista = array();
    public array $valores_filtrados  = array();
    public array $valores = array();
    public array $filtro = array();

    public array $datos_session_usuario = array();

    public string $campo_busca = 'registro_id';
    public string $valor_busca_fault = '';
    public string $btn_busca = '';
    public array $valor_filtro;
    public array $campo_filtro;
    public bool $selected = false;
    public array $campo;
    public bool $campo_resultado=false;
    public stdclass $pestanas ;
    public string $path_base;
    public string $session_id;
    public string $url_base;
    public string $titulo_lista = '';
    public int $n_registros = 0;
    public string $fecha_hoy;
    public stdClass $row_upd;
    public string $mensaje_exito = '';
    public string $mensaje_warning = '';
    public bool $msj_con_html = true;
    public string $accion_titulo = '';
    public string $seccion_titulo;
    public string $link_alta = '';
    public string $link_alta_bd = '';
    public string $link_elimina_bd = '';
    public string $link_lista = '';
    public string $link_descarga_excel= '';
    public string $link_modifica = '';
    public string $link_modifica_bd = '';
    public string $include_inputs_alta = '';
    public string $include_inputs_modifica = '';
    public string $include_lista_row = '';
    public string $include_lista_thead= '';
    public array $buttons = array(); //Usado para system e integrar botones permitidos

    public string $link_importa_previo = '';
    public string $link_importa_previo_muestra = '';
    public string $link_importa_previo_muestra_bd = '';

    public string $link_google_calendar_redirect = '';

    public array $subtitulos_menu = array();

    public int $number_active = -1;

    public array $secciones_permitidas = array();

    public string $nombre_usuario = '';

    public string $path_vendor_views = '';

    public generales $conf_generales;

    public bool $verifica_parents_alta = false;
    public array $parents_verifica = array();

    public array $childrens_data = array();
    public array $buttons_childrens_alta = array();
    public stdClass $buttons_parents_ir ;

    public stdClass $buttons_parents_alta;

    public array $params_upd_get = array();

    public array $url_servicios = array();

    public stdClass $paths_conf;

    public array $columnas_entidad = array();
    public array $columnas_calc = array();

    public array $ths = array();

    public string $params_importa = '';

    public string $input_params_importa = '';

    public function __construct(PDO $link){
        $this->link = $link;
        $this->buttons_parents_alta = new stdClass();
        $this->buttons_parents_ir = new stdClass();

        $generals = (new generales());
        $this->conf_generales = $generals;
        if(!isset($_SESSION['grupo_id']) && $generals->aplica_seguridad){
            if(isset($_GET['seccion'], $_GET['accion']) && $_GET['seccion'] !== 'adm_session' && $_GET['accion'] !== 'login') {
                $url = 'index.php?seccion=adm_session&accion=login';
                header('Location: '.$url);
            }
        }

        (new init())->init_data_controler(controler: $this);
        if(errores::$error){
            $out = array_reverse(errores::$out);
            foreach ($out as $msj){
                echo $msj;
                echo "<br>";
                echo "<hr>";
            }
            die('Error');
            exit;
        }

        $this->pestanas->includes = array();
        $this->pestanas->targets = array();

        if(!isset($generals->path_base)){
            $error =  $this->errores->error(mensaje: 'path base en generales debe existir',data: '',es_final: true);
            print_r($error);
            exit;
        }
        if(!isset($generals->session_id)){
            $error =  $this->errores->error(mensaje: 'session_id en generales debe existir',data: '',es_final: true);
            print_r($error);
            exit;
        }

        $this->path_base = $generals->path_base;
        $this->session_id = $generals->session_id;

        $this->fecha_hoy = date('Y-m-d H:i:s');

        $mensajes = (new mensajes())->data(con_html: $this->msj_con_html);
        if(errores::$error){
            $error =  $this->errores->error(mensaje: 'Error al cargar mensajes',data: $mensajes);
            print_r($error);
            exit;
        }

        $this->mensaje_exito = $mensajes->exito_msj;
        $this->mensaje_warning = $mensajes->warning_msj;

        $this->accion_titulo = str_replace('_',' ',$this->accion);
        $this->seccion_titulo = str_replace('_', ' ', $this->seccion);


        if($this->seccion !== '') {
            $adm_accion = (new adm_accion(link: $this->link))->accion_registro(accion: $this->accion,
                seccion: $this->seccion);
            if (errores::$error) {
                $error = $this->errores->error(mensaje: 'Error al obtener accion', data: $adm_accion);
                if(isset($_GET['ws'])){
                    if((int)$_GET['ws'] === 1){
                        ob_clean();
                        header('Content-Type: application/json');
                        try {
                            echo json_encode($error, JSON_THROW_ON_ERROR);
                        }
                        catch (Throwable $e){
                            print_r($e);
                            exit;
                        }
                        exit;
                    }
                }
                print_r($error);
                exit;
            }

            if (isset($adm_accion['adm_accion_titulo'])) {
                $adm_accion_titulo = trim($adm_accion['adm_accion_titulo']);
                if ($adm_accion_titulo !== '') {
                    if ($adm_accion_titulo !== 'ST') {
                        $this->accion_titulo = $adm_accion_titulo;
                    }
                }
            }

            if (isset($adm_accion['adm_seccion_etiqueta_label'])) {
                $adm_seccion_titulo = trim($adm_accion['adm_seccion_etiqueta_label']);
                if ($adm_seccion_titulo !== '') {
                    if ($adm_seccion_titulo !== 'ST') {
                        $this->seccion_titulo = $adm_seccion_titulo;
                    }
                }
            }
        }



        $this->accion_titulo = ucwords($this->accion_titulo);
        $this->seccion_titulo = ucwords($this->seccion_titulo);


        $views = new views();
        if(!isset($views->subtitulos_menu)){
            $error = $this->errores->error(mensaje: 'Error no existe subtitulos_menu en views', data: $views);
            var_dump($error);
            die('Error');
        }

        $this->subtitulos_menu = $views->subtitulos_menu;

        if(trim($this->session_id)!=='') {
            $adm_session_nombre_completo = $this->nombre_usuario();
            if (errores::$error) {
                $error = $this->errores->error(
                    mensaje: 'Error al cargar adm_session_nombre_completo', data: $adm_session_nombre_completo);
                print_r($error);
                exit;
            }
        }

        $this->path_vendor_views = '';

        $this->url_base = (new generales())->url_base;

        $this->link_google_calendar_redirect = "./index.php?seccion=adm_sistema&accion=google_calendar_redirect&session_id=$this->session_id";

    }


    /**
     * Asigna los inputs para views
     * @param array|stdClass $inputs Conjunto de inputs a asignar
     * @return array|stdClass
     * @version 9.119.4
     */
    final public function asigna_inputs(array|stdClass $inputs): array|stdClass
    {
        if(!is_object($this->inputs)){
            return $this->errores->error(
                mensaje: 'Error controlador->inputs debe se run objeto',data: $this->inputs);
        }
        foreach ($this->modelo->campos_view as $key => $value){
            if(!is_array($value)){
                return $this->errores->error(mensaje: 'Error value debe ser un array',data: $value);
            }
            $inputs_controller = (new inputs())->inputs_view(controler: $this, inputs: $inputs,key:  $key,
                value:  $value);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al obtener inputs',data: $inputs_controller);
            }
        }

        return $this->inputs;
    }


    /**
     * Generacion de metodo para ser utilizado en cualquier llamada get con filtros
     * @param bool $header si header da info en http
     * @param array $keys conjunto de datos a integrar en filtros
     * @param bool $ws out web services JSON
     * @return array|stdClass
     */
    final protected function get_out(bool $header, array $keys, bool $ws): array|stdClass
    {
        $filtro = (new filtros())->asigna_filtro_get(keys: $keys, seccion: $this->seccion);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar filtros',data:  $filtro,header: $header,ws: $ws);

        }

        /**
         * llave = string tabla.campo
         * values = array(n1,n2,n3,nn)
         * @example $_POST[llave] = 'adm_seccion.id'
         * @example $_POST[values] = array(1,2,3);
         */
        $not_in = (new not_in())->integra_not_in_post();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al integrar not in',data:  $not_in,header: $header,ws: $ws);
        }

        $salida = (new salida_data())->salida_get(controler: $this,filtro:  $filtro,header:  $header, not_in: $not_in,
            ws:  $ws);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar salida',data:  $salida,header: $header,ws: $ws);

        }
        return $salida;
    }

    /**
     * P ORDER P INT ERROREFV
     * @return string
     */
    final public function get_real_ip():string{
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        }
        else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }

    /**
     * PHPUNIT
     * @param string $name
     * @return controlador_base|array
     */
    final public function genera_controlador(string $name):controlador_base|array{
        $namespace = 'controllers\\';
        $name = str_replace($namespace,'',$name);
        $class = $namespace.$name;
        if($name === ''){
            return $this->errores->error('Error name controlador puede venir vacio',$name);
        }
        if(!class_exists($class)){
            return $this->errores->error('Error no existe la clase',$class);
        }
        return new $class($this->link);
    }

    final public function header_out(mixed $result, bool $header, bool $ws, string $retorno_sig = ''): void
    {
        if($header){
            $retorno_sig = trim($retorno_sig);
            $retorno = $_SERVER['HTTP_REFERER'];

            if($retorno_sig!==''){
                $retorno = $retorno_sig;
            }
            header('Location:'.$retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            try {
                echo json_encode($result, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                $error = $this->errores->error(mensaje: 'Error al dar salida JSON', data: $e);
                var_dump($error);
            }
            exit;
        }
    }

    private function nombre_usuario(){
        $adm_session_nombre_completo = '';
        if(!isset($_SESSION['nombre_usuario']) || $_SESSION['nombre_usuario'] === ''){
            $adm_session_nombre_completo = (new adm_session(link: $this->link))->adm_session_nombre_completo(
                adm_session_name: $this->session_id);
            if(errores::$error){
                return  $this->errores->error(
                    mensaje: 'Error al cargar adm_session_nombre_completo',data: $adm_session_nombre_completo);
            }
            $_SESSION['nombre_usuario'] = $adm_session_nombre_completo;
        }
        $this->nombre_usuario = $_SESSION['nombre_usuario'];
        return $adm_session_nombre_completo;
    }

    /**
     * REG
     * Envía un mensaje de error en formato JSON como respuesta HTTP.
     *
     * Esta función se utiliza para enviar mensajes de error estructurados en formato JSON como respuesta en un contexto
     * de servicio web (Web Service). Se asegura de limpiar cualquier salida previa, establece el encabezado de tipo de
     * contenido como `application/json`, y maneja errores al codificar el JSON.
     *
     * @param stdClass|array $error Estructura de datos que contiene los detalles del error a enviar. Debe ser un objeto
     *                              (`stdClass`) o un arreglo asociativo con la información del error.
     *
     * @return void Esta función no devuelve un valor, ya que finaliza la ejecución con `exit` tras enviar la respuesta.
     *
     * @throws errores Retorna un error si ocurre un problema al codificar el mensaje en formato JSON. Este error se envía
     *                 como salida con la información de excepción capturada.
     *
     * @example Uso exitoso:
     * ```php
     * $error = [
     *     'error' => true,
     *     'mensaje' => 'Recurso no encontrado',
     *     'codigo' => 404
     * ];
     *
     * $this->out_ws_error($error);
     *
     * // Salida (HTTP Response):
     * // HTTP/1.1 200 OK
     * // Content-Type: application/json
     * // {
     * //     "error": true,
     * //     "mensaje": "Recurso no encontrado",
     * //     "codigo": 404
     * // }
     * ```
     *
     * @example Error al codificar JSON:
     * ```php
     * $error = [
     *     'error' => true,
     *     'mensaje' => 'Error en la estructura de datos',
     *     'codigo' => 500,
     *     'detalles' => "\xB1\x31" // Carácter inválido para JSON
     * ];
     *
     * $this->out_ws_error($error);
     *
     * // Resultado:
     * // Error al maquetar json
     * // Array
     * // (
     * //     [error] => ...
     * //     [mensaje] => ...
     * // )
     * ```
     *
     * @note Esta función detiene la ejecución del script con `exit` después de enviar la respuesta, por lo que debe ser
     *       usada únicamente en contextos donde sea seguro detener el flujo de ejecución.
     *
     * @note Utiliza la opción `JSON_THROW_ON_ERROR` para manejar errores en la codificación JSON de manera controlada.
     */
    private function out_ws_error(stdClass|array $error): void
    {
        ob_clean(); // Limpia cualquier salida previa.
        header('Content-Type: application/json'); // Establece el encabezado HTTP para JSON.
        try {
            echo json_encode($error, JSON_THROW_ON_ERROR); // Codifica el error a JSON y lo envía como respuesta.
        }
        catch (Throwable $e) {
            // Si ocurre un error al codificar el JSON, genera un error detallado.
            $error = $this->errores->error('Error al maquetar json', $e);
            if ($header) { // Si el encabezado aún no se envió, imprime el error y detiene la ejecución.
                print_r($error);
                exit;
            }
            return; // Si ya se envió un encabezado, simplemente retorna.
        }
        exit; // Detiene la ejecución tras enviar la respuesta.
    }


    /**
     * REG
     * Maneja y retorna errores en diferentes formatos según el contexto.
     *
     * Esta función centraliza la lógica para gestionar errores en el sistema, permitiendo generar un array de error
     * estructurado y realizar diferentes acciones como enviar respuestas JSON para servicios web, redirigir a otra
     * ubicación en caso de errores de interfaz, o detener la ejecución con detalles del error.
     *
     * @param string $mensaje Mensaje que describe el error ocurrido.
     * @param mixed $data Información adicional relacionada con el error (puede ser cualquier tipo de dato).
     * @param bool $header Indica si debe ejecutarse una redirección HTTP tras el error (true para redirigir).
     * @param bool $ws Indica si el error debe enviarse como respuesta JSON para un servicio web (true para WS).
     * @param string $class (Opcional) Nombre de la clase donde ocurrió el error.
     * @param bool $es_final (Opcional) Indica si el error debe finalizar la ejecución del programa inmediatamente.
     * @param string $file (Opcional) Archivo donde ocurrió el error.
     * @param string $function (Opcional) Función donde ocurrió el error.
     * @param string $line (Opcional) Línea de código donde ocurrió el error.
     * @param array $params (Opcional) Parámetros adicionales para detallar el contexto del error.
     *
     * @return array Retorna un array con la estructura del error generado.
     *
     * @throws errores Detiene la ejecución con un mensaje estructurado si $ws es true.
     *
     * @example Uso en servicio web (respuesta JSON):
     * ```php
     * $error = $this->retorno_error(
     *     mensaje: 'Recurso no encontrado',
     *     data: ['id' => 123],
     *     header: false,
     *     ws: true
     * );
     * // Salida (JSON):
     * // {
     * //     "mensaje": "Recurso no encontrado",
     * //     "data": {
     * //         "id": 123
     * //     },
     * //     ...
     * // }
     * ```
     *
     * @example Uso en interfaz con redirección:
     * ```php
     * $_SESSION['seccion_header'] = 'producto';
     * $_SESSION['accion_header'] = 'modifica';
     * $_GET['registro_id'] = 42;
     *
     * $error = $this->retorno_error(
     *     mensaje: 'Error al procesar la solicitud',
     *     data: null,
     *     header: true,
     *     ws: false
     * );
     * // Redirige a:
     * // ./index.php?seccion=producto&accion=modifica&registro_id=42&session_id=abc123
     * ```
     *
     * @example Detener ejecución con mensaje:
     * ```php
     * $error = $this->retorno_error(
     *     mensaje: 'Acceso no permitido',
     *     data: null,
     *     header: false,
     *     ws: false
     * );
     * // Salida:
     * // Acceso no permitido
     * // <br>
     * // <hr>
     * // Error
     * ```
     *
     * @note
     * - Si `$ws` es true, la respuesta se envía como JSON y la ejecución se detiene.
     * - Si `$header` es true, se verifica la existencia de `seccion_header` y `accion_header` en la sesión y se realiza
     *   una redirección a la sección/acción correspondiente.
     * - Si no aplica ni `$ws` ni `$header`, se imprimen los mensajes de error acumulados en `errores::$out`.
     */
    final public function retorno_error(string $mensaje, mixed $data, bool $header, bool $ws, string $class = '',
                                        bool$es_final = false, string $file  = '', string$function = '',
                                        string $line = '', array $params = array()): array
    {
        $error = $this->errores->error(mensaje: $mensaje, data: $data, class: $class, es_final: $es_final,
            file: $file, funcion: $function, line: $line, params: $params);
        if($ws){
            $this->out_ws_error(error: $error);
        }
        if(!$header){
            return $error;
        }
        $aplica_header = false;
        $seccion_header = '';
        $accion_header = '';

        if(isset($_SESSION['seccion_header'], $_SESSION['accion_header'])) {
            if (trim($_SESSION['seccion_header']) !== '' && trim($_SESSION['accion_header']) !== '') {
                $seccion_header = trim($_SESSION['seccion_header']);
                $accion_header = trim($_SESSION['accion_header']);
                unset($_SESSION['seccion_header'],$_SESSION['accion_header']);
                $aplica_header = true;
            }
        }

        if($aplica_header){
            $liga = './index.php?seccion='.$seccion_header.'&accion='.$accion_header.'&registro_id='
                .$_GET['registro_id'].'&session_id='.$this->session_id;
            header("Location: $liga");
            exit;
        }
        $out = array_reverse(errores::$out);
        foreach ($out as $msj){
            echo $msj;
            echo "<br>";
            echo "<hr>";
        }
        die('Error');
    }

    /**
     * @return array|stdClass
     */
    final protected function resultado_filtrado(): array|stdClass
    {
        if(!isset($_POST['filtros'])){
            return $this->errores->error('Error no existe filtros en POST',$_POST);
        }
        if(!is_array($_POST['filtros'])){
            return $this->errores->error('Error al generar filtros en POST debe ser un array',$_POST);
        }
        $filtros = (new normalizacion_ctl())->genera_filtros_envio($_POST['filtros']);
        if(errores::$error){
            return $this->errores->error('Error al generar filtros',$filtros);
        }

        $r_modelo = (new filtros())->filtra(controler: $this, filtros: $filtros);
        if(errores::$error){
            return $this->errores->error('Error al obtener datos',$r_modelo);
        }
        return $r_modelo;
    }


}