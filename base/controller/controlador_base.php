<?php //DEBUG FIN
namespace base\controller;

use base\frontend\directivas;
use base\frontend\templates;
use base\frontend\values;
use base\orm\modelo;
use config\generales;
use config\views;
use gamboamartin\errores\errores;

use gamboamartin\plugins\exportador;
use JsonException;

use models\adm_accion;
use models\adm_elemento_lista;
use models\adm_session;
use PDO;
use stdClass;
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


    public array $registro_en_proceso = array();


    /**
     * @param PDO $link Conexion a la base de datos
     * @param modelo $modelo
     * @param array $filtro_boton_lista
     * @param string $campo_busca
     * @param string $valor_busca_fault
     * @param stdClass $paths_conf
     */
    public function __construct(PDO $link, modelo $modelo, array $filtro_boton_lista = array(),
                                string   $campo_busca = 'registro_id', string $valor_busca_fault = '',
                                stdClass $paths_conf = new stdClass()){


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

        $this->directiva = new directivas();

        $init = (new normalizacion())->init_controler(controler: $this);
        if(errores::$error){
            $error = $this->errores->error(mensaje:'Error al incializar entradas',data: $init,
                params: get_defined_vars());
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


        $inputs_busca = $this->directiva->panel_busca(campo_busca: $this->campo_busca,
            valor_busca_fault: $this->valor_busca_fault);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar datos de busqueda',data: $inputs_busca);
            print_r($error);
            die('Error');
        }

        $this->campo_busca = $inputs_busca['campo_busca'];
        $this->btn_busca = $inputs_busca['btn_busca'];

        parent::__construct();

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

        $breadcrumbs = $this->data_bread(aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar nav breads',data: $breadcrumbs);
            print_r($error);
            die('Error');
        }
        $this->breadcrumbs = $breadcrumbs;


    }

    /**
     * 
     * @param bool $header
     * @return array
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
            aplica_transaccion_inactivo: $this->modelo->aplica_transaccion_inactivo, registro_id: $this->registro_id,
            tabla: $this->modelo->tabla,registro: $registro);

        if(errores::$error){
            $error = $this->errores->error('Error al validar transaccion activa',$valida);
            if($header){
                print_r($error);
                die('Error');
            }
            return $error;
        }

        $resultado = (new activacion())->activa_bd_base(modelo:  $this->modelo, registro_id: $this->registro_id,seccion:  $this->seccion);
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
     * P INT
     * @param bool $header si header retorna error en navegador y corta la operacion
     * @param bool $ws
     * @return array|string
     */
    public function alta(bool $header, bool $ws = false):array|string{

        /**
         * REFACTOR
         */
        $this->seccion = str_replace('models\\','',$this->seccion);

        if($this->seccion === ''){
            return $this->retorno_error(mensaje: "Error la seccion esta vacia",data:  $this->seccion, header: $header,
                ws: false);
        }
        if($this->accion === ''){
            return $this->retorno_error(mensaje: "Error la accion esta vacia",data:  $this->accion,header:  $header,
                ws:false);

        }

        $this->valores['status'] = 'activo';


        $registro_en_proceso = $_SESSION['registro_en_proceso'][$this->seccion] ?? array();

        if(count($registro_en_proceso)>0){
            $this->valores = $registro_en_proceso;
        }


        $elm = new adm_elemento_lista($this->link);


        $template = new templates($this->link);
        $template->valores =$this->valores;
        $template->campos_invisibles =$this->campos_invisibles;
        $this->registro_en_proceso = $registro_en_proceso;

        $campos = $elm->obten_campos(estructura_bd:  array(), modelo: $this->modelo,vista: 'alta');
        if(errores::$error){
            return  $this->retorno_error(mensaje: 'Error al obtener campos',data: $campos,header: $header,ws: false);
        }

        $alta_html = $template->alta(aplica_form: true, directivas_extra: $this->directivas_extra,
            muestra_btn_guardar:  true,valores_filtrados: $this->valores_filtrados,campos: $campos ,seccion:  $this->seccion,
            session_id: $this->session_id,path_base: $this->path_base,
            campos_disabled: $this->campos_disabled,valores_default: $this->valores_asignados_default,
            campos_invisibles: $this->campos_invisibles);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template alta', data: $alta_html, header: $header,
                ws: false);
        }
        $this->alta_html = $alta_html;
        $directiva = new directivas();
        $btn = $directiva->btn_enviar(label: 'Agrega',name: 'btn_agrega',value: 'btn_agrega',stilo: 'success');

        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar btn', data: $btn , header: $header, ws: false);

        }
        $this->btn = $btn;
        return $this->alta_html;
    }

    /**
     * ERROREV
     * @param bool $header Si header muestra resultado en front
     * @param bool $ws
     * @return array|stdClass
     * @throws JsonException
     */
    public function alta_bd(bool $header, bool $ws): array|stdClass{
        /**
         * REFACTORIZA
         */


        $transaccion_previa = false;
        if($this->link->inTransaction()){
            $transaccion_previa = true;
        }
        if(!$transaccion_previa) {
            $this->link->beginTransaction();
        }

        $valida = $this->validacion->valida_clase(controler: $this);
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al validar clase', data: $valida, header: $header, ws: $ws);
        }

        if($this->tabla===''){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error seccion por get debe existir',data:  $_GET, header: $header,
                ws:  $ws);
        }

        $limpia = (new normalizacion())->limpia_post_alta();
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al limpiar POST', data: $limpia,header:  $header,ws:  $ws);
        }

        $valida = $this->validacion->valida_post_alta();
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al validar POST', data: $valida,header:  $header,ws:  $ws);
        }


        if($this->seccion === ''){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al seccion no puede venir vacia',data:  $this->seccion,
                header: $header, ws: $ws);
        }


        $resultado = (new altas())->alta_base(controler:  $this, registro: $_POST);

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

        $limpia = (new normalizacion())->limpia_registro_en_proceso();
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
            echo json_encode($resultado, JSON_THROW_ON_ERROR);
            exit;
        }
        return $resultado;
    }

    /**
     * P INT
     * @param bool $header
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
     *
     * @param bool $header
     * @param bool $ws
     * @return array
     * @throws JsonException
     */
    public function elimina_bd(bool $header, bool $ws): array{
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
     * P INT
     * @param bool $header Si header se mostrara la info en el navegador de manera directa
     * @param bool $ws Se ejecutara via web service con salida json
     * @return array
     */
    public function lista(bool $header, bool $ws = false): array{

        $valida = $this->validacion->valida_datos_lista_entrada(accion: $this->accion, seccion: $this->seccion);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al validar', data: $valida,header:  $header, ws: $ws);
        }
        $modelo = new adm_accion($this->link);

        $acciones = $modelo->acciones_permitidas(accion:$this->accion, modelo:$this->modelo, seccion:$this->seccion);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener accion', data: $acciones, header: $header, ws: $ws);
        }

        $pag_seleccionada = 1;
        if(isset($_GET['pag_seleccionada'])){
            $pag_seleccionada = (int)$_GET['pag_seleccionada'];
        }
        $filtro_btn = (new normalizacion())->filtro_btn(controler: $this);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener filtro de boton', data: $filtro_btn,
                header: $header, ws: $ws);
        }

        $elm = new adm_elemento_lista($this->link);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar modelo',data: $elm);
            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }


        $filtro['adm_seccion.descripcion'] = trim($this->seccion);
        $filtro['adm_elemento_lista.lista'] = 'activo';
        $filtro['adm_elemento_lista.status'] = 'activo';

        $r_elementos = $elm->filtro_and(filtro:$filtro);

        if(errores::$error){
            $error = $this->errores->error(mensaje: "Error al filtrar",data: $r_elementos);
            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }


        $elementos = $r_elementos->registros;
        $columnas_mostrables = array();
        $status_encontrado = false;
        $id_encontrado = false;
        foreach ($elementos as $elemento){
            $columnas_mostrables[] = $elemento['adm_elemento_lista_descripcion'];
            if(trim($elemento['adm_elemento_lista_descripcion']) === $this->seccion.'_status'){
                $status_encontrado = true;
            }
            if(trim($elemento['adm_elemento_lista_descripcion']) === $this->seccion.'_id'){
                $id_encontrado = true;
            }
        }
        if(!$status_encontrado){
            $columnas_mostrables[] = $this->seccion.'_status';
        }
        if(!$id_encontrado){
            $columnas_mostrables[] = $this->seccion.'_id';
        }

        $session_modelo = new adm_session($this->link);


        $filtro = $session_modelo->obten_filtro_session(seccion:$this->seccion);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener filtro',data: $filtro,header: $header, ws: $ws);
        }


        $registros = $this->obten_registros_para_lista(filtro:  $filtro, limit: 15,
            pag_seleccionada:  $pag_seleccionada,columnas: $columnas_mostrables, filtro_btn: $filtro_btn);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al obtener registros',data: $registros);
            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }


        $filtro = $session_modelo->obten_filtro_session(seccion:$this->seccion);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener filtro',data: $filtro);
        }

        $filtros =    (new normalizacion())->genera_filtro_modelado(controler:  $this, filtro: $filtro);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al $filtros',data: $filtros);
            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }



        $filtro_especial = array();
        $contador = 0;
        foreach($filtro_btn as $campo => $valor){
            $filtro_especial[$contador][$campo]['operador'] = '=';
            $filtro_especial[$contador][$campo]['valor'] = $valor;
            $contador++;
        }
        $n_registros = $this->modelo->cuenta(filtro: $filtros,tipo_filtro: 'textos',filtro_especial:  $filtro_especial);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al contar registros',data: $n_registros);
            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        $filtro_html = $this->directiva->format_filtro_base_html(filtro: $filtros);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al $filtro_html',data: $filtro_html);
            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        $this->registros['n_registros'] = $n_registros;
        $this->registros['filtros'] = $filtro_html;
        $this->registros['ip'] = $this->get_real_ip();
        $this->registros['registros'] = $registros;
        $this->registros['data'] = $registros;



        $botones_filtro = $this->obten_botones_para_filtro();
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar datos para el boton',data: $botones_filtro);

            if(!$header){
                return $error;
            }
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }

        $n_paginas = ceil((int)$n_registros / 15);
        $elm = new adm_elemento_lista($this->link);


        $filtro = array();
        $filtro['adm_seccion.descripcion'] = $this->seccion;
        $filtro['adm_elemento_lista.status'] = 'activo';
        $filtro['adm_elemento_lista.lista'] = 'activo';

        $resultado = $elm->obten_registros_filtro_and_ordenado(campo: 'adm_elemento_lista.orden',
            columnas_en_bruto: false, filtros: $filtro,orden: 'ASC');
        if(errores::$error){
            $error =  $this->errores->error(mensaje: 'Error al obtener obten_registros_filtro_and_ordenado',
                data: $resultado);
            print_r($error);
            die('Error');

        }
        $elementos_lista = $resultado->registros;



        $filtro = array();
        $filtro['adm_seccion.descripcion'] = $this->seccion;
        $filtro['adm_elemento_lista.filtro'] = 'activo';
        $filtro['adm_elemento_lista.status'] = 'activo';

        $resultado = $elm->obten_registros_filtro_and_ordenado(campo: 'adm_elemento_lista.orden',
            columnas_en_bruto: false, filtros: $filtro,orden: 'ASC');

        if(errores::$error){
            return $this->errores->error('Error al obtener registros',$resultado);
        }

        $elementos_lista_filtro = $resultado->registros;

        $template = new templates($this->link);

        $campos = $template->campos_lista(elementos_lista: $elementos_lista);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener campos', data: $campos,header:  $header,ws:  $ws);
        }

        $campos_filtro = $template->obten_campos_filtro(elementos_lista: $elementos_lista_filtro);
        if(errores::$error){
            return $this->retorno_error('Error al obtener campos de filtro', $campos_filtro, $header, $ws);
        }

        $accion_modelo = new adm_accion($this->link);


        $filtro = array();
        $filtro['adm_accion.status'] = 'activo';
        $filtro['adm_seccion.descripcion'] = $this->seccion;
        $filtro['adm_accion.lista'] = 'activo';
        $resultado = $accion_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->retorno_error('Error al obtener acciones', $resultado, $header, $ws);
        }
        $acciones_asignadas = $resultado->registros;

        $lista_html = $template->lista_completa(campo_id:  $this->tabla.'_id', registros: $registros,
            n_paginas:  $n_paginas, pagina_seleccionada: $pag_seleccionada,seccion:  $this->seccion,
            acciones_asignadas:  $acciones_asignadas,seccion_link: $this->seccion,accion_link: $this->accion,
            session_id: $this->session_id,campos:  $campos->campos,etiqueta_campos:  $campos->etiqueta_campos,
            botones_filtros: $botones_filtro, filtro_boton_seleccionado: $filtro_btn);


        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data: $lista_html,header: $header,ws: $ws);
        }

        $this->lista_html = $lista_html;

        $this->registros['html'] = $this->lista_html;

        return $this->registros;

    }


    /**
     *
     * @param bool $header
     * @param bool $ws
     * @param string $breadcrumbs
     * @param bool $aplica_form
     * @param bool $muestra_btn
     * @return array|string
     */
    public function modifica(bool $header, bool $ws = false, string $breadcrumbs='',
                             bool $aplica_form = true, bool $muestra_btn = true):array|string{

        $namespace = 'models\\';
        $this->seccion = str_replace($namespace,'',$this->seccion);
        $clase = $namespace.$this->seccion;

        if((string)$this->seccion === ''){
            $error = $this->errores->error('Error no existe seccion', $_GET);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        if(!class_exists($clase)){
            return $this->errores->error('Error no existe la clase',$clase);
        }

        $resultado = (new upd())->asigna_datos_modifica(controler: $this);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al asignar datos',data:  $resultado,header: $header,ws: $ws);
        }
        $this->registro = $resultado;

        $elm = new adm_elemento_lista($this->link);

        $template = new templates($this->link);

        $campos = $elm->obten_campos(modelo: $this->modelo,vista: 'modifica',estructura_bd:  array());
        if(errores::$error){
            return $this->errores->error('Error al obtener campos',$campos);
        }

        $campos_alta = $campos['campos'];


        $modifica_html =  $template->modifica(registro: $this->registro, seccion: $this->seccion,
            breadcrumbs:  $breadcrumbs, valores_filtrados: $this->valores_filtrados, campos_alta: $campos_alta,
            session_id:  $this->session_id,path_base:  $this->path_base,aplica_form:  $aplica_form,muestra_btn:  $muestra_btn);


        if(errores::$error){
            $error = $this->errores->error('Error al generar template', $modifica_html);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $this->modifica_html = $modifica_html;

        $registro_puro = $this->modelo->registro(registro_id: $this->registro_id, columnas_en_bruto: true,
            retorno_obj: true);
        if(errores::$error){
           return $this->retorno_error(mensaje: 'Error al obtener registro', data: $registro_puro,
               header: $header,ws:  $ws);
        }

        $this->row_upd =  $registro_puro;

        return $this->modifica_html;
    }

    /**
     *
     * @param bool $header
     * @param bool $ws
     * @return array|stdClass
     * @throws JsonException
     */
    public function modifica_bd(bool $header, bool $ws): array|stdClass{
        $namespace = 'models\\';
        $this->seccion = str_replace($namespace,'',$this->seccion);
        $clase = $namespace.$this->seccion;
        if($this->seccion === ''){
            $error = $this->errores->error('Error seccion no puede venir vacia',$_GET);
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
            $error = $this->errores->error('Error no existe la clase',$clase);
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
            $error =  $this->errores->error('Error registro_id debe ser mayor a 0',$_GET);
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

    /**
     * @throws JsonException
     */
    public function status(bool $header, bool $ws): array|stdClass
    {
        $upd = $this->modelo->status('status', $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al cambiar status',data:  $upd,header:  $header,ws:  $ws);
        }
        $_SESSION['exito'][]['mensaje'] = 'Se ajusto el estatus de manera el registro con el id '.$this->registro_id;

        $this->header_out(result: $upd, header: $header,ws:  $ws);


        return $upd;
    }

    /**
     *
     * @param bool $header
     * @param bool $ws
     * @return array|string
     * @throws JsonException
     */
    public function xls_lista(bool $header = true, bool $ws = false): array|string
    {
        $filtro_btn = $_GET['filtro_btn'] ?? array();

        $session_modelo = new adm_session($this->link);


        $filtro = $session_modelo->obten_filtro_session(seccion: $this->seccion);
        if(errores::$error){
            return $this->errores->error('Error al obtener filtro',$filtro);
        }

        $registros = $this->obten_registros_para_lista(filtro: $filtro, limit: 0,pag_seleccionada: 1,filtro_btn: $filtro_btn);
        if(errores::$error){
            $error =  $this->errores->error('Error al generar resultado filtrado',$registros);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $elm = new adm_elemento_lista($this->link);


        $exportador = new exportador();

        $campos = $elm->obten_campos(estructura_bd: array(), modelo: $this->modelo,vista: 'lista');
        if(errores::$error){
            return   $this->errores->error('Error al obtener campos',$campos);
        }
        $keys = $this->obten_encabezados_xls($campos);
        if(isset($keys['error'])){
            $error = $this->errores->error('Error al obtener encabezados',$keys);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $campos = $elm->obten_campos(estructura_bd:  array(), modelo: $this->modelo,vista: 'lista');
        if(errores::$error){
            return   $this->errores->error('Error al obtener campos',$campos);
        }

        $campos = $this->obten_estructura($campos);

        if(errores::$error){
            $error =  $this->errores->error('Error al obtener estructura',$campos);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $registros_xls = (new values())->ajusta_formato_salida_registros(campos: $campos, registros: $registros);
        if(errores::$error){
            return $this->retorno_error('Error al ajusta formato de salida', $registros_xls, $header, false);

        }

        $resultado = $exportador->listado_base_xls(header: $header, name: $this->seccion, keys:  $keys,
            path_base: $this->path_base,registros:  $registros_xls,totales:  array());
        if(errores::$error){
            $error =  $this->errores->error('Error al generar xls',$resultado);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        if(!$header){
            return $resultado;
        }
        exit;

    }

}
