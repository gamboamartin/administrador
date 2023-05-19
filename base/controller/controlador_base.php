<?php //DEBUG FIN
namespace base\controller;

use base\orm\modelo;
use config\generales;
use config\views;
use gamboamartin\administrador\ctl\activacion;
use gamboamartin\administrador\ctl\altas;
use gamboamartin\administrador\ctl\normalizacion_ctl;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;
use Throwable;
use validacion\confs\configuraciones;


class controlador_base extends controler{ //PRUEBAS FINALIZADAS DEBUG
    public array $acciones_no_visibles;
    public array $directivas_extra = array();
	public int $error;

    public array $filtros_lista = array();

    public string $mensaje = '';

    public int $reg_x_pagina;

    public array $valores_asignados_default = array();

    public array $selects_registros_completos = array();

    public bool $registros_alta = false;

    public array $campos_disabled = array();

    public array $campos_invisibles  = array();

    public string $alta_html  = '';
    public string $lista_html = '';
    public string $modifica_html = '';
    public string $btn = '';
    public array $menu_permitido = array();

    public array $registro_en_proceso = array();
    public int $adm_menu_id = -1;
    public string $menu_header = '';

    public string $titulo_pagina = "";

    /**
     * @param PDO $link Conexion a la base de datos
     * @param modelo $modelo Modelo de datos a ejecutar
     * @param array $filtro_boton_lista Filtros para botones de lista
     * @param string $campo_busca Campos para integracion de filtros
     * @param string $valor_busca_fault  Valor default view
     * @param stdClass $paths_conf Rutas de configuracion
     */
    public function __construct(PDO $link, modelo $modelo, array $filtro_boton_lista = array(),
                                string   $campo_busca = 'registro_id', string $valor_busca_fault = '',
                                stdClass $paths_conf = new stdClass()){

        $this->paths_conf = $paths_conf;
        $this->campo_busca = $campo_busca;
        $this->errores = new errores();
        $this->filtros_lista = array();

        $valida = (new configuraciones())->valida_confs(paths_conf:$paths_conf);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al validar configuraciones',data: $valida);
            print_r($error);
            die('Error');
        }

        $conf_views = new views();
        $this->reg_x_pagina = $conf_views->reg_x_pagina;
        $this->acciones_no_visibles = array();
        $this->link = $link;
        $this->tabla = $modelo->tabla;
        $this->modelo = $modelo;


        $init = (new normalizacion_ctl())->init_controler(controler: $this);
        if(errores::$error){
            $error = $this->errores->error(mensaje:'Error al incializar entradas',data: $init);
            print_r($error);
            die('Error');
        }

        $this->valor_busca_fault = $this->registro_id;
        if($valor_busca_fault !== ''){
            $this->valor_busca_fault = $valor_busca_fault;
        }


        $this->selects_registros_completos = array();

        if(!empty($_POST) && $this->accion==='alta_bd'){
            $_SESSION['registro_en_proceso'][$this->seccion] = $_POST;
        }

        if(isset($_GET['tipo_mensaje'], $_GET['adm_accion']) && $_GET['adm_accion'] === 'alta' && $_GET['tipo_mensaje'] === 'error'){
            $this->registros_alta = true;
        }
        $this->directivas_extra = array();
        $this->filtro_boton_lista = $filtro_boton_lista;


        parent::__construct(link: $link);

        $aplica_seguridad = (new generales())->aplica_seguridad;
        if(!isset($_SESSION['grupo_id']) && $aplica_seguridad){
            if(!isset($_GET['seccion'])){
                $_GET['seccion'] = 'adm_session';
            }
            if(!isset($_GET['accion'])){
                $_GET['accion'] = 'login';
            }
            if($_GET['seccion'] !== 'adm_session' &&  $_GET['accion'] !== 'login'){
                header('Location: index.php?seccion=adm_session&accion=login');
                exit;
            }

        }


        /**
         * @author kevin.acuna
         * Obtiene el usuario activo y asigna a atributo
         */
        if(isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id']>0) {
            $datos_session_usuario = (new adm_usuario(link: $this->link))->usuario_activo();
            if (errores::$error) {
                $error = $this->errores->error(mensaje: 'Error al verificar usuario activo', data: $datos_session_usuario);
                print_r($error);
                die('Error');
            }
            $this->datos_session_usuario = $datos_session_usuario;
        }

        if(isset($_GET['adm_menu_id'])){
            $this->adm_menu_id = $_GET['adm_menu_id'];
        }
        $secciones_permitidas = (new adm_seccion($this->link))->secciones_permitidas(adm_menu_id: $this->adm_menu_id);
        if(errores::$error){
            $error =  $this->errores->error(mensaje: 'Error al obtener secciones permitidas',data: $secciones_permitidas);
            print_r($error);
            exit;
        }
        $this->secciones_permitidas = $secciones_permitidas;

        $menu = $this->genera_menu();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener menu', data: $menu);
            print_r($error);
            die('Error');
        }

    }

    /**
     * Genera un link de menu
     * @param array $menu Menu a integrar
     * @return array|string
     * @version 6.15.0
     */
    private function a_menu(array $menu): array|string
    {

        $valida = $this->valida_menu(menu: $menu);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al validar menu', data: $valida);
        }

        $href = $this->href_menu(menu: $menu);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener href menu', data: $href);
        }
        return "<a href='$href'>$menu[adm_menu_titulo]</a>";

    }


    /**
     * Función que activa un registro de la base de datos. Retorna un arreglo con el registro
     * correspondiente al id del registro en cuestión.
     * @param bool $header si header retorna error en navegador y corta la operacion
     * @return array almacena en un arreglo todos los datos del registro
     * @final revisada
     */
    public function activa_bd(bool $header ): array{
        if($this->registro_id === -1){
            return $this->errores->error('No existe id para activar',$_GET);
        }

        $registro = $this->modelo->registro(registro_id: $this->registro_id);
        if(errores::$error){
            $error = $this->errores->error('Error al obtener registro',$registro);
            if($header){
                print_r($error);
                die('Error');
            }
            return $error;
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $this->modelo->aplica_transaccion_inactivo, registro: $registro,
            registro_id: $this->registro_id, tabla: $this->modelo->tabla);

        if(errores::$error){
            $error = $this->errores->error('Error al validar transaccion activa',$valida);
            if($header){
                print_r($error);
                die('Error');
            }
            return $error;
        }

        $resultado = (new activacion())->activa_bd_base(
            modelo:  $this->modelo, registro_id: $this->registro_id,seccion:  $this->seccion);
        if(errores::$error){
            $error = $this->errores->error('Error al activar registro', $resultado);

            if($header){
                print_r($error);
                die('Error');
            }
            return $error;
        }
        $data_pagina_seleccionada = '';
        if(isset($_GET['p_seleccionada'])){
            $data_pagina_seleccionada = "&p_seleccionada=$_GET[p_seleccionada]";
        }
        if($header) {
            header("Location: ./index.php?seccion=$this->tabla&accion=lista&mensaje=" .
                'Registro activado con éxito&tipo_mensaje=exito&session_id=' . $this->session_id . $data_pagina_seleccionada);
            exit;
        }
        return $resultado;
    }

    /**
     * Vista para un alta
     * @param bool $header si header retorna error en navegador y corta la operacion
     * @param bool $ws si ws retorna json
     * @return array|string
     * @esfinal revisada
     */
    public function alta(bool $header, bool $ws = false):array|string{

        $this->valores['status'] = 'activo';
        $registro_en_proceso = $_SESSION['registro_en_proceso'][$this->seccion] ?? array();

        if(count($registro_en_proceso)>0){
            $this->valores = $registro_en_proceso;
        }
        $this->registro_en_proceso = $registro_en_proceso;
        $this->alta_html = '';

        return $this->alta_html;
    }

    /**
     *
     * Función que al validar los datos de una clase inserta los registros en la base de datos.
     * Si los registros no son válidos, éstos se limpian para ser capturados de nuevo.
     * @param bool $header Si header muestra resultado en front
     * @param bool $ws si ws retorna json
     * @return array|stdClass con datos del registro insertado
     * @version 2.36.3
     * @final revisada
     *
     */
    public function alta_bd(bool $header, bool $ws): array|stdClass{

        $transaccion_previa = $this->transaccion_previa();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al validar transaccion_previa', data: $transaccion_previa,
                header: $header, ws: $ws);
        }

        if(!$transaccion_previa) {
            $this->link->beginTransaction();
        }

        $valida = $this->validacion->valida_alta_bd(controler: $this);
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al validar datos', data: $valida,header:  $header,ws:  $ws);
        }


        $resultado = (new altas())->alta_base(registro: $_POST, controler: $this);

        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al insertar', data: $resultado, header: $header,ws:  $ws);

        }

        $this->registro_id = $resultado->registro_id;

        $_SESSION['registro_alta_id'] = $this->registro_id;
        if(!$transaccion_previa) {
            $this->link->commit();
        }

        $limpia = (new normalizacion_ctl())->limpia_registro_en_proceso();
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al limpiar SESSION', data: $limpia, header: $header,ws:  $ws);
        }

        if($header){
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            try {
                echo json_encode($resultado, JSON_THROW_ON_ERROR);
                exit;
            }
            catch (Throwable $e){
                return $this->retorno_error(mensaje: 'Error de salida', data: $e, header: true,ws:  false);
            }
        }
        return $resultado;
    }

    /**
     * P INT
     * Función que aplica filtro sobre los registro de una tabla después de
     * válidar los parámetros de la solicitud y la existencia de botón filtrar o botón limpiar.
     * @param bool $header si header retorna error en navegador y corta la operacion
     * @return array
     */
    public function aplica_filtro(bool $header): array{

        if(!isset($_POST)){
            $error =  $this->errores->error('Error POST debe existir',array());

            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        if(!isset($_POST['filtro'])){
            $error =  $this->errores->error('Error POST[filtro] debe existir',$_POST);

            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        if($this->seccion === ''){
            $error =  $this->errores->error('Error $this->seccion debe existir',$this->seccion);

            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        if(!isset($_POST['btn_limpiar']) && !isset($_POST['btn_filtrar'])){
            $error =  $this->errores->error('Error algun boton debe existir btn_filtrar o btn_limpiar debe existir',$_POST);

            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        if(isset($_POST['btn_limpiar'],$_POST['btn_filtrar'])){
            $error =  $this->errores->error('Error solo un boton debe existir o limpia o filtra',$_POST);

            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }


        $filtros = $_POST['filtro'];
        if(isset($_POST['btn_limpiar']) && $_POST['btn_limpiar'] ==='activo'){
            unset($_SESSION['filtros'][$this->seccion]);
        }

        $ejecuta = true;
        if(is_string($filtros)){
            $ejecuta = false;
        }

        $filtros_env = array();
        if(isset($_POST['btn_filtrar']) && $_POST['btn_filtrar'] ==='activo' && $ejecuta){
            foreach($filtros as $tabla_externa=>$data){
                if(!is_array($data)){
                    $error = $this->errores->error('Error data debe ser un array', $data);
                    if (!$header) {
                        return $error;
                    }
                    print_r($error);
                    die('Error');
                }
                foreach($data as $campo=>$value) {
                    $elm = (new adm_elemento_lista($this->link));
                    if(errores::$error){
                        $error =  $this->errores->error('Error al generar modelo',$elm);
                        print_r($error);
                        die('Error');

                    }
                    $elemento_lista = $elm->elemento_para_lista(campo: $campo, seccion:  $this->seccion, tabla_externa: $tabla_externa);
                    if (errores::$error) {
                        $error = $this->errores->error('Error al obtener elemento', $elemento_lista);
                        if (!$header) {
                            return $error;
                        }
                        print_r($error);
                        die('Error');
                    }
                    $filtros_env[$tabla_externa][$campo]['es_sq'] = $elemento_lista['elemento_lista_es_sq'];
                    $filtros_env[$tabla_externa][$campo]['value'] = $value;

                }
            }
            $_SESSION['filtros'][$this->seccion] = $filtros_env;
        }

        if($header) {
            $retorno = $_SERVER['HTTP_REFERER'];
            if(isset($_POST['btn_limpiar'])){
                $retorno = preg_replace('/&filtro_btn\[([a-z]*_?[a-z]*)*.([a-z]*_?[a-z]*)*]=[0-9]+/','',$retorno);
            }

            $retorno = preg_replace('/pag_seleccionada=[0-9]+/','pag_seleccionada=1',$retorno);
            header('Location:' . $retorno);
            exit;
        }
        return $_SESSION;
    }


    /**
     *
     * @param bool $header
     * @param bool $ws
     * @return array
     * @throws JsonException
     * @final rev
     */
    public function desactiva_bd(bool $header, bool $ws): array{//FINPROTEOCOMPLETA
        if($this->registro_id<=0){
            $error =  $this->errores->error('Error id debe ser mayor a 0',$_GET);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $valida = $this->validacion->valida_transaccion_status(controler: $this);
        if(errores::$error){
            $error =  $this->errores->error('Error al validar transaccion activa',$valida);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->modelo->registro_id = $this->registro_id;
        $resultado = $this->modelo->desactiva_bd();
        if(errores::$error){
            $error =  $this->errores->error('Error al desactivar',$resultado);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error);
                exit;
            }
            print_r($error);
            die('Error');
        }
        if($ws){
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
        }
        if($header) {
            header("Location: ./index.php?seccion=$this->seccion&accion=lista&mensaje="
                . 'Registro desactivado con éxito&tipo_mensaje=exito&session_id=' . $this->session_id);
        }

        return $resultado;
    }

    /**
     * Elimina un registro
     * @param bool $header Si header muestra resultado en nav
     * @param bool $ws Si ws muestra resultado en json
     * @return array|stdClass
     * @version 2.44.4
     */
    public function elimina_bd(bool $header, bool $ws): array|stdClass{
        $transacion_previa = false;
        if($this->link->inTransaction()){
            $transacion_previa = true;
        }
        if(!$transacion_previa) {
            $this->link->beginTransaction();
        }
        if($this->registro_id < 0){
            if(!$transacion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error('El id no puede ser menor a 0', $this->registro_id, $header, $ws);
        }
        $registro = $this->modelo->registro(registro_id: $this->registro_id);
        if(errores::$error){
            if(!$transacion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al obtener registro', data: $registro,header:  $header,ws:  $ws);
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $this->modelo->aplica_transaccion_inactivo,registro: $registro,
            registro_id: $this->registro_id, tabla: $this->modelo->tabla);
        if(errores::$error){
            if(!$transacion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error('Error al validar transaccion activa', $valida, $header, $ws);
        }
        $registro = $this->modelo->elimina_bd(id:$this->registro_id);
        if(errores::$error){
            if(!$transacion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error('Error al eliminar', $registro, $header, $ws);
        }

        $_SESSION['exito'][]['mensaje'] = 'Se elimino registro de '.$this->tabla.' de manera exitosa id: '.$this->registro_id;

        if(!$transacion_previa) {
            $this->link->commit();
        }

        if($header) {
            header('Location: '.$_SERVER['HTTP_REFERER']);
        }
        return $registro;
    }

    /**
     * PHPUNIT
     * @param bool $header
     * @param bool $ws
     * @return array
     */
    public function filtro_and(bool $header, bool $ws): array{
        $valida = $this->validacion->valida_filtros();
        if(errores::$error){
            return $this->retorno_error("Error al validar filtros", $valida, $header, $ws);
        }
        $r_modelo = $this->resultado_filtrado();
        if(errores::$error){
            return $this->retorno_error('Error al obtener datos', $r_modelo, $header, $ws);
        }
        if((int)$r_modelo['n_registros']===0){
            return $this->retorno_error('Error no hay datos', $r_modelo, $header, $ws);
        }
        if($ws) {
            ob_clean();
            header('Content-Type: application/json');
            $registros = $r_modelo['registros'];
            echo json_encode($registros);
            exit;
        }
        if(!$header){
            return $r_modelo['registros'];
        }
        return $r_modelo['registros'];
    }

    private function genera_menu(): array|stdClass
    {
        $menu = new stdClass();
        if(isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0) {
            $menu = $this->menu();
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al obtener menu', data: $menu);
            }
        }
        return $menu;
    }

    public function get(bool $header, bool $ws): array
    {
        $valida = $this->validacion->valida_filtros();
        if(errores::$error){
            return $this->retorno_error("Error al validar filtros", $valida, $header, $ws);
        }
        $r_modelo = $this->resultado_filtrado();
        if(errores::$error){
            return $this->retorno_error('Error al obtener datos', $r_modelo, $header, $ws);
        }
        if($ws) {
            ob_clean();
            header('Content-Type: application/json');
            $registros = $r_modelo;
            echo json_encode($registros);
            exit;
        }
        if(!$header){
            return $r_modelo;
        }
        return $r_modelo;
    }

    /**
     * Genera un a liga de menu base
     * @param array $menu Datos del menu
     * @return string|array
     * @version 6.13.0
     */
    private function href_menu(array $menu): string|array
    {
        $keys = array('adm_menu_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $menu);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al validar menu', data: $valida);
        }

        return "index.php?seccion=adm_session&accion=inicio&session_id=$this->session_id&adm_menu_id=$menu[adm_menu_id]";
    }

    private function li_menu(array $menu): array|string
    {
        $valida = $this->valida_menu(menu: $menu);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al validar menu', data: $valida);
        }
        $a_menu = $this->a_menu(menu: $menu);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener a_menu', data: $a_menu);
        }

        return "<li>$a_menu</li>";
    }


    /**
     * Genera la view de lista
     * @param bool $header Si header se mostrara la info en el navegador de manera directa
     * @param bool $ws Se ejecutara via web service con salida json
     * @return array
     */
    public function lista(bool $header, bool $ws = false): array{

        $this->registros = array();
        return $this->registros;

    }

    private function menu(): array|stdClass
    {
        $menu_permitido = (new adm_menu(link: $this->link))->menus_visibles_permitidos_full();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener menu permitido', data: $menu_permitido);
        }
        $this->menu_permitido = $menu_permitido;

        $menu_header = $this->menu_header();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener menu_header', data: $menu_header);
        }
        $this->menu_header = $menu_header;

        $data = new stdClass();
        $data->menu_permitido = $menu_permitido;
        $data->menu_header = $menu_header;
        return $data;
    }

    private function menu_header(): array|string
    {
        $menu_header = '';
        foreach ($this->menu_permitido as $menu){

            $valida = $this->valida_menu(menu: $menu);
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al validar menu', data: $valida);
            }

            $li_menu = $this->li_menu(menu: $menu);
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al obtener li_menu', data: $li_menu);
            }

            $menu_header .= $li_menu;
        }
        return $menu_header;
    }


    /**
     * Inicializa la vista de modifica
     * @param bool $header Si header retorna en html
     * @param bool $ws retorna json
     * @return array|stdClass
     * @version 2.17.2.1
     * @finalrev
     */
    public function modifica(bool $header, bool $ws = false):array|stdClass{

        $namespace = 'models\\';
        $this->seccion = str_replace($namespace,'',$this->seccion);

        if($this->seccion === ''){
            return $this->retorno_error(
                mensaje: 'Error seccion no puede venir vacio', data: $this->seccion,header: $header, ws: $ws);
        }
        if($this->registro_id<=0){
            return  $this->retorno_error(
                mensaje:'Error registro_id debe sr mayor a 0', data:$this->registro_id,header: $header, ws: $ws);
        }

        $resultado = (new upd())->asigna_datos_modifica(controler: $this);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al asignar datos',data:  $resultado,header: $header,ws: $ws);
        }
        $this->registro = $resultado;

        $registro_puro = $this->modelo->registro(registro_id: $this->registro_id, columnas_en_bruto: true,
            retorno_obj: true);
        if(errores::$error){
           return $this->retorno_error(mensaje: 'Error al obtener registro', data: $registro_puro,
               header: $header,ws:  $ws);
        }

        $this->row_upd =  $registro_puro;

        $data = new stdClass();
        $data->registro = $this->registro;
        $data->row_upd = $registro_puro;

        return $data;
    }

    /**
     *
     * @param bool $header Si header muestra resultado en html
     * @param bool $ws Si ws retorna un objeto en forma JSON PARA servicios REST
     * @return array|stdClass
     * @throws JsonException
     * @final rev
     */
    public function modifica_bd(bool $header, bool $ws): array|stdClass{
        $namespace = $this->modelo->NAMESPACE;

        if ($namespace === ''){
            $error = $this->errores->error(mensaje:'Error: NAMESPACE no esta inicializado',data: $_GET);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        $clase = $this->modelo->NAMESPACE.'\\'.$this->seccion;

        if($this->seccion === ''){
            $error = $this->errores->error(mensaje:'Error seccion no puede venir vacia',data: $_GET);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if(!class_exists($clase)){
            $error = $this->errores->error(mensaje:'Error no existe la clase',data: $clase);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        if($this->registro_id <=0){
            $error =  $this->errores->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $_GET);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        $this->modelo->registro_id = $this->registro_id;

        $registro = $this->modelo->registro(registro_id: $this->registro_id);
        if(errores::$error){
            $error = $this->errores->error('Error al obtener registro',$registro);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $this->modelo->aplica_transaccion_inactivo, registro: $registro,
            registro_id: $this->modelo->registro_id,tabla:  $this->modelo->tabla);
        if(errores::$error){
            $error = $this->errores->error('Error al validar transaccion activa',$valida);
            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        if(!isset($_POST)){
            $error = $this->errores->error('POST Debe existir',$_GET);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if(!is_array($_POST)){
            $error = $this->errores->error('POST Debe ser un array',$_POST);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        if(isset($_POST['btn_modifica'])){
            unset($_POST['btn_modifica']);
        }

        if(count($_POST)===0){
            $error = $this->errores->error('POST Debe tener info',$_POST);
            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        $r_modifica = (new upd())->modifica_bd_base(controler: $this,registro_upd:  $_POST);

        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al modificar registro',data: $r_modifica);

            if(!$header){
                return $error;
            }
            if($ws){
                header('Content-Type: application/json');
                echo json_encode($error, JSON_THROW_ON_ERROR);
                exit;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if($header){
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            echo json_encode($r_modifica, JSON_THROW_ON_ERROR);
            exit;
        }
        return $r_modifica;
    }


    public function status(bool $header, bool $ws): array|stdClass
    {
        $upd = $this->modelo->status(campo: 'status',registro_id:  $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al cambiar status',data:  $upd,header:  $header,ws:  $ws);
        }
        $_SESSION['exito'][]['mensaje'] = 'Se ajusto el estatus de manera el registro con el id '.$this->registro_id;

        $this->header_out(result: $upd, header: $header,ws:  $ws);


        return $upd;
    }

    /**
     * Verifica si esta en transaccion previa
     * @return bool
     * @version 2.6.2
     */
    final protected function transaccion_previa(): bool
    {
        $transaccion_previa = false;
        if($this->link->inTransaction()){
            $transaccion_previa = true;
        }
        return $transaccion_previa;
    }

    private function valida_menu(array $menu): bool|array
    {
        $keys = array('adm_menu_titulo','adm_menu_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $menu);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al al validar menu', data: $valida);
        }
        $keys = array('adm_menu_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $menu);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al validar menu', data: $valida);
        }
        return true;
    }





}
