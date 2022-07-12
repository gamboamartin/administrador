<?php
namespace base\controller;

use base\frontend\directivas;
use base\orm\modelo;
use config\generales;
use gamboamartin\errores\errores;

use models\adm_accion;
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
    public array $registro = array();
    public string $tabla = '';
    public string $accion = '';
    public array|stdClass $inputs = array();
    public directivas $directiva;
    public string $breadcrumbs = '';
    public array $registros = array();
    public array $orders = array();
    public array $filtro_boton_lista = array();
    public array $valores_filtrados  = array();
    public array $valores = array();
    public array $filtro = array();


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



    public function __construct(){
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

        $this->accion_titulo = ucwords($this->accion);
        $this->seccion_titulo = str_replace('_', ' ', $this->seccion);
        $this->seccion_titulo = ucwords($this->seccion_titulo);

    }

    /**
     * Obtiene los datos de un breadcrumb
     * @param bool $aplica_seguridad si aplica seguridad validara los elementos necesarios de seguridad
     * y permisos de acceso
     * @return array|string
     */
    protected function data_bread(bool $aplica_seguridad):array|string{
        if($aplica_seguridad && !isset($_SESSION['grupo_id']) && $_GET['seccion'] !== 'adm_session' && $_GET['accion'] !== 'login') {
            header('Location: index.php?seccion=adm_session&accion=login');
            exit;
        }

        $es_vista = false;
        $file_view = $this->path_base.'views/'.$this->seccion.'/'.$this->accion.'.php';
        if(file_exists($file_view)){
            $es_vista = true;
        }
        $file_view_base = $this->path_base.'views/vista_base/'.$this->accion.'.php';
        if(file_exists($file_view_base)){
            $es_vista = true;
        }
        if($this->seccion === 'adm_session' && $this->accion === 'login'){
            $es_vista = false;
        }
        $breadcrumbs = '';
        if($es_vista && $aplica_seguridad) {

            $accion_modelo = new adm_accion($this->link);

            $accion_registro = $accion_modelo->accion_registro(accion:  $this->accion, seccion: $this->seccion);
            if(errores::$error){
                return  $this->errores->error(mensaje: 'Error al obtener acciones',data: $accion_registro);
            }
            $acciones =  $accion_modelo->acciones_permitidas(seccion: $this->seccion,accion: $this->accion,modelo: $this->modelo);
            if(errores::$error){
                return  $this->errores->error(mensaje: 'Error al obtener acciones',data: $acciones);
            }

            $breadcrumbs = $this->directiva->genera_breadcrumbs( $this->seccion, $this->accion, $acciones, $this->link,
                $accion_registro,$this->session_id);
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al generar nav breads',data:  $breadcrumbs);
            }
        }
        return $breadcrumbs;
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
    protected function asigna_filtro_get(array $keys): array
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
     * P INT P ORDER ERROREV
     * @param int $limit
     * @param int $offset
     * @param array $filtro
     * @param array $orders
     * @param array $filtro_especial
     * @param array $columnas
     * @return array|stdClass
     */
    private function asigna_registros(array $columnas, array $filtro, array $filtro_especial, int $limit, int $offset,
                                      array $orders): array|stdClass{
        if($limit < 0){
            return $this->errores->error(
                mensaje: 'Error limit debe ser mayor o igual a 0  con 0 no aplica limit',data: $limit);
        }

        $resultado = $this->modelo->filtro_and(columnas: $columnas, filtro: $filtro, filtro_especial: $filtro_especial,
            group_by: array(), limit: $limit, offset: $offset, order: $orders, tipo_filtro: 'textos');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al filtrar',data: $resultado);
        }

        return $resultado;
    }

    /**
     * P ORDER P INT ERROREV
     * @param array $data_para_boton
     * @param string $filtro_boton_lista
     * @return array
     */
    private function genera_data_btn(array $data_para_boton, string $filtro_boton_lista):array{
        if($filtro_boton_lista === ''){
            return $this->errores->error(mensaje: 'Error $filtro_boton_lista no puede venir vacio',
                data: $this->seccion, params: get_defined_vars());
        }

        $key_id = $filtro_boton_lista.'_id';
        $key_descripcion = $filtro_boton_lista.'_descripcion';
        if(!isset($data_para_boton[$key_id])){
            return $this->errores->error(mensaje: 'Error $data_para_boton['.$key_id.'] no existe',
                data: $data_para_boton, params: get_defined_vars());
        }
        if(!isset($data_para_boton[$key_descripcion])){
            return $this->errores->error(mensaje: 'Error $data_para_boton['.$key_descripcion.'] no existe',
                data: $data_para_boton, params: get_defined_vars());
        }
        $data_btn = array();
        $data_btn['id'] = $data_para_boton[$key_id];
        $data_btn['filtro'] = array($filtro_boton_lista.'.id'=>$data_para_boton[$key_id]);
        $data_btn['etiqueta'] = $data_para_boton[$key_descripcion];
        $class = 'outline-primary';
        if(isset($_GET['filtro_btn'][$filtro_boton_lista.'.id'])){
            if((int)$_GET['filtro_btn'][$filtro_boton_lista.'.id'] === (int)$data_btn['id']) {
                $class = 'warning';
            }
        }
        $data_btn['class'] = $class;


        return $data_btn;
    }

    /**
     * P INT P ORDER ERROREV
     * @param int $limit
     * @param int $pag_seleccionada
     * @param array $filtro
     * @param array $orders
     * @param array $filtro_especial
     * @param array $columnas
     * @return array
     */
    private function genera_resultado_filtrado( array $columnas, array $filtro, array $filtro_especial, int $limit,
                                                array $orders, int $pag_seleccionada):array{

        if($limit < 0){
            return $this->errores->error(mensaje: 'Error limit debe ser mayor o igual a 0  con 0 no aplica limit',
                data: $limit, params: get_defined_vars());
        }
        if($pag_seleccionada < 0){
            return $this->errores->error(
                mensaje: 'Error $pag_seleccionada debe ser mayor o igual a 0 ',data: $pag_seleccionada,
                params: get_defined_vars());
        }
        $offset = ($pag_seleccionada - 1) * $limit;
        $resultado = $this->asigna_registros(columnas: $columnas, filtro: $filtro, filtro_especial:  $filtro_especial,
            limit: $limit, offset: $offset, orders: $orders);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al asignar registros',data: $resultado,
                params: get_defined_vars());
        }

        return $resultado->registros;
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


    protected function header_out(mixed $result, bool $header, bool $ws): void
    {
        if($header){
            $retorno = $_SERVER['HTTP_REFERER'];
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

    /**
     * P INT P ORDER ERROREV
     * @return array
     */
    protected function obten_botones_para_filtro():array{
        $botones_filtro = array();
        foreach($this->filtro_boton_lista as $filtro_boton_lista){
            $registros_botones_filtro = $this->obten_registros_para_boton_filtro(
                filtro_boton_lista: $filtro_boton_lista['tabla']);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al obtener registros de filtro',
                    data: $registros_botones_filtro, params: get_defined_vars());
            }
            $data_para_botones = $registros_botones_filtro['registros'];
            foreach ($data_para_botones as $data_para_boton){
                $data_btn = $this->genera_data_btn(data_para_boton: $data_para_boton,
                    filtro_boton_lista: $filtro_boton_lista['tabla']);
                if(errores::$error){
                    return  $this->errores->error(mensaje: 'Error al generar datos para el boton',data: $data_btn);
                }
                $botones_filtro[$filtro_boton_lista['tabla']][] = $data_btn;
            }
        }

        return $botones_filtro;
    }


    /**
     * PHPUNIT
     * @param array $campos
     * @return array
     */
    protected function obten_encabezados_xls(array $campos):array{
        $valida_seccion = $this->validacion->valida_seccion_base($this->seccion);
        if(errores::$error){
            return $this->errores->error('Error al validar datos de la seccion',$valida_seccion);
        }

        $campos = $this->obten_estructura($campos);
        if(errores::$error){
            return $this->errores->error('Error al obtener campos',$campos);
        }
        $keys = (new normalizacion())->genera_campos_lista($campos);
        if(errores::$error){
            return $this->errores->error('Error al genera keys',$keys);
        }


        return $keys;
    }

    /**
     * PHPUNIT
     * @param array $campos
     * @return array
     */
    protected function obten_estructura(array $campos): array
    {
        $valida_seccion = $this->validacion->valida_seccion_base($this->seccion);
        if(errores::$error){
            return $this->errores->error('Error al validar datos de la seccion',$valida_seccion);
        }

        return $campos['campos_completos'];

    }

    /**
     *
     * @param string $mensaje Mensaje a mostrar
     * @param errores|array|string|stdClass $data Complemento y/o detalle de error
     * @param bool $header si header retorna error en navegador y corta la operacion
     * @param bool $ws si ws retorna error en navegador via json
     * @param array $params
     * @return array
     */
    protected function retorno_error(string $mensaje, mixed $data, bool $header, bool $ws, array $params = array()): array
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
     * P INT P ORDER ERROREV
     * Obtiene todos los registros de un modelo para la muestra de los botones de filtros rapidos
     * @param string $filtro_boton_lista nombre del modelo para traerse todos
     * @example
     *       $registros_botones_filtro = $this->obten_registros_para_boton_filtro($filtro_boton_lista['tabla']);
     *
     * @return array conjunto de registros obtenidos
     * @throws errores $filtro_boton_lista===''
     * @uses  controler
     */
    private function obten_registros_para_boton_filtro(string $filtro_boton_lista):array{
        $filtro_boton_lista = str_replace('models\\','', $filtro_boton_lista);
        $class = 'models\\'.$filtro_boton_lista;
        if($filtro_boton_lista===''){
            return $this->errores->error(mensaje: 'Error $filtro_boton_lista no puede venir vacio',
                data: $filtro_boton_lista);

        }
        if(!class_exists($class)){
            return  $this->errores->error(mensaje: 'Error modelo no existe '.$filtro_boton_lista,
                data: $filtro_boton_lista);
        }
        $modelo_filtro_btns = $this->modelo->genera_modelo(modelo:$filtro_boton_lista);
        if(errores::$error){
            return  $this->errores->error(mensaje: 'Error al generar modelo', data: $modelo_filtro_btns);
        }
        $registros_botones_filtro = $modelo_filtro_btns->obten_registros();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener registros de filtro',
                data:  $registros_botones_filtro);
        }
        return $registros_botones_filtro;
    }

    /**
     * P INT ERORREV P ORDER
     * @param int $limit
     * @param int $pag_seleccionada
     * @param array $filtro
     * @param array $filtro_btn
     * @param array $columnas
     * @return array
     */
    protected function obten_registros_para_lista(array $filtro, int $limit, int $pag_seleccionada,
                                                  array $columnas = array(), array $filtro_btn = array()): array{
        $this->seccion = str_replace('models\\','',$this->seccion);
        $class = 'models\\'.$this->seccion;
        if($this->seccion === ''){
            return $this->errores->error(mensaje: "Error la seccion esta vacia",data: $this->seccion,
                params: get_defined_vars());
        }
        if(!class_exists($class)){
            return $this->errores->error(mensaje: "Error la clase es invalida",data: $class, params: get_defined_vars());
        }
        if($limit < 0){
            return $this->errores->error(mensaje: 'Error limit debe ser mayor o igual a 0  con 0 no aplica limit',
                data: $limit, params: get_defined_vars());
        }
        if($pag_seleccionada < 0){
            return $this->errores->error(mensaje: 'Error $pag_seleccionada debe ser mayor o igual a 0 ',
                data: $pag_seleccionada, params: get_defined_vars());
        }



        $filtro_modelado = (new normalizacion())->genera_filtro_modelado(controler:  $this, filtro: $filtro);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar filtro modelado',data: $filtro_modelado,
                params: get_defined_vars());

        }
        $filtro_especial = array();
        $contador = 0;
        foreach($filtro_btn as $campo => $valor){
            $filtro_especial[$contador][$campo]['operador'] = '=';
            $filtro_especial[$contador][$campo]['valor'] = $valor;
            $contador++;
        }
        $registros = $this->genera_resultado_filtrado(columnas: $columnas, filtro: $filtro_modelado,
            filtro_especial: $filtro_especial, limit: $limit, orders: $this->orders,
            pag_seleccionada: $pag_seleccionada);
        if(errores::$error){
            return  $this->errores->error(mensaje: 'Error al generar resultado filtrado',data: $registros,
                params: get_defined_vars());
        }
        return $registros;

    }

    /**
     * DEBUG INI ERROR DEF
     * @param array $filtro
     * @param array $filtro_btn
     * @return array|int
     */
    public function obten_total_registros_filtrados(array $filtro, array $filtro_btn = array()): array|int
    {
        
        $registros = $this->obten_registros_para_lista(0,1,$filtro,$filtro_btn,array($this->tabla.'_id'));
        if(errores::$error){
            return  $this->errores->error('Error al generar resultado filtrado',$registros);
        }


        return count($registros);
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