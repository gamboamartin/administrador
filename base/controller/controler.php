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

        $init = (new init())->init_data_controler(controler: $this);
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
     * POR DOCUMENTAR EN WIKI
     * This function is used to handle and output errors in a web
     * service context. It accepts a parameter which can be an instance
     * of stdClass or an array representing the error details.
     * @param stdClass|array $error Error de resultado
     * @return void
     * @version 16.138.0
     */
    private function out_ws_error(stdClass|array $error): void
    {
        ob_clean();
        header('Content-Type: application/json');
        try {
            echo json_encode($error, JSON_THROW_ON_ERROR);
        }
        catch (Throwable $e){
            $error = $this->errores->error('Error al maquetar json', $e);
            if($header){
                print_r($error);
                exit;
            }
            return;
        }
        exit;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Retorna un error.
     *
     * Esta función se encarga de gestionar errores dentro de la aplicación. Recibe un mensaje de error,
     * datos adicionales, un booleano para determinar si enviar la cabecera HTTP, un booleano para determinar
     * si se trata de un error de Web Service y un array de parámetros opcionales.
     *
     * @param string $mensaje Mensaje de error.
     * @param mixed $data Datos adicionales del error.
     * @param bool $header Flag para determinar si enviar la cabecera HTTP.
     * @param bool $ws Flag para determinar si se trata de un error de Web Service.
     * @param array $params Parámetros adicionales (opcional).
     *
     * @return array Array con informacion del error.
     * @version 16.138.0
     *
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