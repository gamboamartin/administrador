<?php
namespace base\controller;

use base\orm\modelo;
use config\generales;
use config\views;
use gamboamartin\errores\errores;

use PDO;
use stdClass;
use Throwable;


class controler{
    public modelo $modelo;
    public int $registro_id = -1;
    public string $seccion = '';

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
    public string $link_modifica = '';
    public string $link_modifica_bd = '';
    public string $include_inputs_alta = '';
    public string $include_inputs_modifica = '';
    public string $include_lista_row = '';
    public string $include_lista_thead= '';

    public array $subtitulos_menu = array();

    public int $number_active = -1;

    public array $secciones_permitidas = array();

    public function __construct(PDO $link){
        $this->link = $link;

        $generals = (new generales());
        if(!isset($_SESSION['grupo_id']) && $generals->aplica_seguridad){
            if(isset($_GET['seccion'], $_GET['accion']) && $_GET['seccion'] !== 'adm_session' && $_GET['accion'] !== 'login') {
                $url = 'index.php?seccion=adm_session&accion=login';
                header('Location: '.$url);
            }
        }

        $init = (new init())->init_data_controler(controler: $this);
        if(errores::$error){
            $error =  $this->errores->error(mensaje: 'Error al inicializar',data: $init);
            print_r($error);
            exit;
        }

        $this->pestanas->includes = array();
        $this->pestanas->targets = array();

        if(!isset($generals->path_base)){
            $error =  $this->errores->error('path base en generales debe existir','');
            print_r($error);
            exit;
        }
        if(!isset($generals->session_id)){
            $error =  $this->errores->error('session_id en generales debe existir','');
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
        $this->accion_titulo = ucwords($this->accion_titulo);
        $this->seccion_titulo = str_replace('_', ' ', $this->seccion);
        $this->seccion_titulo = ucwords($this->seccion_titulo);


        $views = new views();
        if(!isset($views->subtitulos_menu)){
            $error = $this->errores->error(mensaje: 'Error no existe subtitulos_menu en views', data: $views);
            var_dump($error);
            die('Error');
        }

        $this->subtitulos_menu = $views->subtitulos_menu;


    }

    private function asigna_filtro(string $campo, array $filtro, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $key_get = $this->key_get(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar key',data: $key_get);
        }

        $filtro = $this->asigna_filtro_existe(campo: $campo,filtro: $filtro,key_get: $key_get,tabla: $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar filtro',data: $filtro);
        }
        return $filtro;
    }

    private function asigna_filtro_existe(string $campo, array $filtro, string $key_get, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        if(isset($_GET[$key_get])){
            $filtro = $this->asigna_key_filter(campo: $campo,filtro: $filtro,key_get: $key_get,tabla: $tabla);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al generar filtro',data: $filtro);
            }
        }
        return $filtro;
    }

    /**
     * @param array $keys Keys a verificar para asignacion de filtros via GET
     * @version 1.117.28
     * @example
     *      $keys['tabla'] = array('id','descripcion');
     *      $filtro = $ctl->asigna_filtro_get(keys:$keys);
     *      print_r($filtro);
     *      //filtro[tabla.id] = $_GET['tabla_id']
     * @return array
     */
    private function asigna_filtro_get(array $keys): array
    {

        $filtro = array();
        foreach ($keys as $tabla=>$campos){
            if(!is_array($campos)){
                return $this->errores->error(mensaje: 'Error los campos deben ser un array', data: $campos);
            }
            foreach ($campos as $campo) {

                $valida = $this->valida_data_filtro(campo: $campo, tabla: $tabla);
                if (errores::$error) {
                    return $this->errores->error(mensaje: 'Error al validar filtro', data: $valida);
                }
                $filtro = $this->asigna_filtro(campo: $campo, filtro: $filtro, tabla: $tabla);
                if (errores::$error) {
                    return $this->errores->error(mensaje: 'Error al generar filtro', data: $filtro);
                }
            }
        }
        return $filtro;
    }

    public function asigna_inputs(array|stdClass $inputs): array|stdClass
    {
        foreach ($this->modelo->campos_view as $key => $value){
            if(!is_array($value)){
                return $this->errores->error(mensaje: 'Error value debe ser un array',data: $value);
            }
            $inputs_controller = $this->inputs_view(inputs: $inputs,key:  $key,value:  $value);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al obtener inputs',data: $inputs_controller);
            }
        }

        return $this->inputs;
    }

    private function asigna_key_filter(string $campo, array $filtro, string $key_get, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $key_filter = $this->key_filter(campo:$campo,tabla:  $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar filtro',data: $key_filter);
        }
        $filtro[$key_filter] = $_GET[$key_get];
        return $filtro;
    }

    /**
     * PHPUNIT
     * @param array $filtros
     * @return array
     */
    private function filtra(array $filtros): array
    {
        $r_modelo = $this->modelo->filtro_and(filtro: $filtros,filtro_especial: array());
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener datos',data: $r_modelo);
        }
        return $r_modelo;
    }

    /**
     * Generacion de metodo para ser utilizado en cualquier llamada get con filtros
     * @param bool $header si header da info en http
     * @param array $keys conjunto de datos a integrar en filtros
     * @param bool $ws out web services JSON
     * @return array|stdClass
     * @version 1.504.50
     */
    protected function get_out(bool $header, array $keys, bool $ws): array|stdClass
    {
        $filtro = $this->asigna_filtro_get(keys: $keys);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar filtros',data:  $filtro,header: $header,ws: $ws);

        }

        /**
         * llave = string tabla.campo
         * values = array(n1,n2,n3,nn)
         * @example $_POST[llave] = 'adm_seccion.id'
         * @example $_POST[values] = array(1,2,3);
         */
        $not_in = $this->integra_not_in_post();
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
    public function get_real_ip():string{
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
    public function genera_controlador(string $name):controlador_base|array{
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

    protected function header_out(mixed $result, bool $header, bool $ws, string $retorno_sig = ''): void
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

    private function inputs_view(array $inputs, string $key, array $value): array|stdClass
    {
        $keys = array('type');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $value);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar value filtro',data: $valida);
        }
        $key = trim($key);
        if($key === ''){
            return $this->errores->error(mensaje: 'Error key esta vacio',data: $key);
        }
        $type = $this->type_validado(inputs: $inputs,value:  $value);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener type',data: $type);
        }

        if(!is_object($this->inputs)){
            return $this->errores->error(
                mensaje: 'Error controlador->inputs debe se run objeto',data: $this->inputs);
        }

        $this->inputs->$key = $inputs[$type]->$key;
        return $this->inputs;
    }

    private function integra_not_in_post(): array
    {
        $not_in = array();
        if(isset($_POST['not_in'])){
            /**
             * llave = string tabla.campo
             * values = array(n1,n2,n3,nn)
             * @example $_POST[llave] = 'adm_seccion.id'
             * @example $_POST[values] = array(1,2,3);
             */
            $not_in = $this->not_in_post();
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al integrar not in',data:  $not_in);
            }
        }
        return $not_in;
    }

    private function key_get(string $campo, string $tabla): string|array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        return $tabla.'_'.$campo;
    }

    private function key_filter(string $campo, string $tabla): string|array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        return $tabla.'.'.$campo;
    }

    /**
     * Maqueta un not in obtenido por POST
     * @return array
     * @version 1.600.54
    *
     * llave = string tabla.campo
     * values = array(n1,n2,n3,nn)
     * @example $_POST[llave] = 'adm_seccion.id'
     * @example $_POST[values] = array(1,2,3);
     */
    private function not_in_post(): array
    {

        $keys = array('not_in');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $_POST);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar not in',data:  $valida);
        }

        $keys = array('llave');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $_POST['not_in']);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar not in',data:  $valida);
        }

        if(!is_array($_POST['not_in']['values'])){
            return $this->errores->error(mensaje: 'Error POST[not_in][values] debe ser un array',data:  $_POST);
        }
        if(count($_POST['not_in']['values']) === 0){
            return $this->errores->error(mensaje: 'Error POST[not_in][values] esta vacio',data:  $_POST);
        }

        $not_in['llave'] = $_POST['not_in']['llave'];
        $not_in['values'] = $_POST['not_in']['values'];

        return $not_in;
    }

    /**
     * Genera salida para eventos controller
     * @param string $mensaje Mensaje a mostrar
     * @param errores|array|string|stdClass $data Complemento y/o detalle de error
     * @param bool $header si header retorna error en navegador y corta la operacion
     * @param bool $ws si ws retorna error en navegador via json
     * @param array $params
     * @return array
     */
    public function retorno_error(string $mensaje, mixed $data, bool $header, bool $ws, array $params = array()): array
    {
        $error = $this->errores->error(mensaje: $mensaje,data:  $data, params: $params);
        if($ws){
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
                return $error;
            }

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
            $liga = './index.php?seccion='.$seccion_header.'&accion='.$accion_header.'&registro_id='.$_GET['registro_id'].'&session_id='.$this->session_id;
            header("Location: $liga");
            exit;
        }
        print_r($error);
        die('Error');
    }

    /**
     * PHPUNIT
     * @return array
     */
    protected function resultado_filtrado(): array
    {
        if(!isset($_POST['filtros'])){
            return $this->errores->error('Error no existe filtros en POST',$_POST);
        }
        if(!is_array($_POST['filtros'])){
            return $this->errores->error('Error al generar filtros en POST debe ser un array',$_POST);
        }
        $filtros = (new normalizacion())->genera_filtros_envio($_POST['filtros']);
        if(errores::$error){
            return $this->errores->error('Error al generar filtros',$filtros);
        }

        $r_modelo = $this->filtra($filtros);
        if(errores::$error){
            return $this->errores->error('Error al obtener datos',$r_modelo);
        }
        return $r_modelo;
    }

    /**
     * Obtiene el tipo de input para templates de alta
     * @param array $value Value de modelo->campos_view
     * @return array|string
     * @version 2.14.2.1
     */
    private function type(array $value): array|string
    {
        $keys = array('type');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $value);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar value',data: $valida);
        }
        $type = $value['type'];

        $type = trim($type);
        if($type === ''){
            return $this->errores->error(mensaje: 'Error type esta vacio',data: $type);
        }
        return $type;
    }

    /**
     * Obtiene el type para templates alta validado
     * @param array|stdClass $inputs Inputs precargados
     * @param array $value Valor de modelo campos views
     * @return array|string
     * @version 2.14.2.2
     */
    private function type_validado(array|stdClass $inputs, array $value): array|string
    {
        $type = $this->type(value: $value);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener type',data: $type);
        }

        $keys = array($type);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $inputs);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar value',data: $valida);
        }
        return $type;
    }

    private function valida_data_filtro(string $campo, string $tabla): bool|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->errores->error(mensaje: 'Error $campo esta vacio',data: $campo);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->errores->error(mensaje: 'Error $tabla esta vacio',data: $tabla);
        }
        return true;
    }

}