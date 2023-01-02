<?php
namespace base\controller;
use base\conexion;
use base\orm\modelo_base;
use base\seguridad;
use config\generales;
use config\views;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_session;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;
use Throwable;

class init{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Verifica si es aplicable o no una view
     * @param PDO $link Conexion a la base de datos
     * @param seguridad $seguridad Datos de seguridad aplicable en este caso seccion y accion
     * @return bool|array
     *
     * @functions $accion = (new adm_accion($link))->accion_registro($seguridad->seccion,$seguridad->accion);.
     * Obtiene la accion ejecutada en base a seccion y accion. En caso de error lanzará un mensaje
     * @version 2.24.3
     */
    private function aplica_view(PDO $link, seguridad $seguridad): bool|array
    {

        $valida = (new validacion())->seccion_accion(accion: $seguridad->accion, seccion: $seguridad->seccion);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar seccion',data: $valida);
        }

        $accion = (new adm_accion($link))->accion_registro(accion: $seguridad->accion, seccion: $seguridad->seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener accion', data: $accion);
        }
        $aplica_view = false;
        if($accion['adm_accion_es_view'] === 'activo'){
            $aplica_view = true;
        }
        return $aplica_view;
    }

    /**
     * Genera un controlador basado en el nombre
     * @param PDO $link Conexion a base de datos
     * @param string $seccion Seccion en ejecucion
     * @param stdClass $paths_conf Configuraciones de conexion
     * @return controler|array
     * @version 1.253.39
     * @verfuncion 1.1.0
     * @fecha 2022-08-02 10:01
     * @author mgamboa
     */
    public function controller(PDO $link, string $seccion, stdClass $paths_conf = new stdClass()):controler|array{
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia ',data: $seccion);
        }
        $name_ctl = $this->name_controler(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre de controlador', data: $name_ctl);

        }

        /**
         * @var $name_ctl controlador_base
         */

        if($paths_conf === null){
            return new $name_ctl(link:$link);
        }

        return new $name_ctl(link:$link,paths_conf: $paths_conf);
    }

    /**
     *
     * Asigna una session aleatoria a get
     * @return array GET con session_id en un key
     * @version 2.25.3
     */
    public function asigna_session_get(): array
    {
        $session_id = $this->session_id();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar session_id', data: $session_id);
        }

        $_GET['session_id'] = $session_id;
        return $_GET;
    }

    /**
     * Verifica si existe un archivo para include view
     * @param string $include_action Ruta include
     * @return bool|array
     * @version 2.26.3
     */
    private function existe_include(string $include_action): bool|array
    {
        $include_action = trim($include_action);
        if($include_action === ''){
            return $this->error->error(mensaje: 'Error include_action esta vacio', data: $include_action);
        }
        $existe = false;
        if (file_exists($include_action)) {
            $existe = true;
        }
        return $existe;
    }

    /**
     * Obtiene los datos de un template de una accion
     * @param string $accion Accion a verificar
     * @param string $seccion Seccion a verificar
     * @return array|stdClass
     * @functions $data_include = $init->include_action_local_base_data. Verifica si existe una view en base a
     * "$accion" y "$seccion" obtenidas. En caso de error mostrará un mensaje
     *
     * @functions $data_include = $init->include_template. valida y obtiene la ruta de un template para posterior maquetarla.
     * En caso de ocurrir un error, mostrará un mensaje
     * @version 2.75.6
     */
    private function data_include_base(string $accion, string $seccion): array|stdClass
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }

        $data_include = $this->include_action_local_base_data(accion: $accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include local base', data: $data_include);
        }
        if(!$data_include->existe){
            $data_include = $this->include_template(accion: $accion,seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener include template', data: $data_include);
            }
        }
        return $data_include;
    }

    /**
     * Genera la salida para controller
     * @param string $include_action Accion include a integrar para frontend
     * @return array|stdClass
     * @version 2.29.3
     *
     */
    private function genera_salida(string $include_action): array|stdClass
    {
        $include_action = trim($include_action);
        if($include_action === ''){
            return $this->error->error(mensaje: 'Error include_action esta vacio', data: $include_action);
        }

        $existe = $this->existe_include(include_action:$include_action);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar include', data: $include_action);
        }

        $data = $this->output_include(existe: $existe,include_action: $include_action);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar salida', data: $data);
        }
        return $data;
    }

    /**
     * Aqui se determina que view se va a utilizar para el frontend
     * v1.18.9
     * @param bool $aplica_view Si view es activo se buscara un archivo valido
     * @param seguridad $seguridad se utiliza la seccion y accion para l asignacion de la vista
     * @return string|array retorna el path para include
     *
     * @functions $data_include = $this->include_view(accion: $seguridad->accion,seccion: $seguridad->seccion);.
     * Se utiliza para asignar la accion y maquetarla. Si ocurre un error, lanzará un mensaje.
     */
    private function include_action(bool $aplica_view, seguridad $seguridad): string|array
    {
        $data_include = new stdClass();
        $data_include->include_action = '';
        if($aplica_view) {
            $data_include = $this->include_view(accion: $seguridad->accion,seccion: $seguridad->seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener include local', data: $data_include);
            }
        }

        return $data_include->include_action;
    }

    /**
     * Genera la ruta de un include para acciones local
     * @param string $accion Accion a verificar
     * @param string $seccion Seccion a verificar
     * @return string|array
     * @version 1.105.25
     */
    private function include_action_local(string $accion, string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }
        return './views/' . $seccion . '/' . $accion . '.php';
    }

    /**
     * Genera un include para view
     * @param string $accion Accion a crear
     * @return string|array
     * @version 2.27.3
     */
    private function include_action_local_base(string $accion): string|array
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        return './views/vista_base/' . $accion . '.php';
    }

    /**
     * Integra un include de template
     * @param string $accion Accion ej ejecucion
     * @param string $seccion Seccion en ejecucion
     * @return string|array
     * @version 2.31.3
     */
    private function include_action_template(string $accion, string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        if(!isset((new views())->ruta_template_base)){
            return $this->error->error(mensaje: 'Error debe existir views->ruta_template_base', data: (new views()));
        }

        $include = (new views())->ruta_template_base.'views/'.$seccion.'/'. $accion . '.php';
        return str_replace('//', '/', $include);
    }

    /**
     * Obtiene el include de una view para un template
     * @version 1.105.26
     * @param string $accion Accion a verificar template
     * @return string|array
     */
    private function include_action_template_base(string $accion): string|array
    {

        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        if(!isset((new views())->ruta_template_base)){
            return $this->error->error(mensaje: 'Error debe existir views->ruta_template_base', data: (new views()));
        }

        $include = (new views())->ruta_template_base.'views/vista_base/' . $accion . '.php';
        return str_replace('//', '/', $include);
    }

    /**
     * Obtiene los elementos de un include
     * @param string $accion Accion en ejecucion
     * @param string $seccion Seccion en ejecucion
     * @return array|stdClass
     * @version 2.77.6
     */
    private function include_action_local_data(string $accion, string $seccion): array|stdClass
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $include_action = $this->include_action_local(accion: $accion,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include local', data: $include_action);
        }

        $data = $this->genera_salida(include_action:$include_action);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar salida', data: $data);
        }

        return $data;
    }

    /**
     * Data para include de fronted
     * @param string $accion Accion en ejecucion
     * @return stdClass|array
     * @version 2.30.3
     */
    private function include_action_local_base_data(string $accion): stdClass|array
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $include_action = $this->include_action_local_base(accion: $accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include local base', data: $include_action);
        }
        $data = $this->genera_salida(include_action:$include_action);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar salida', data: $data);
        }
        return $data;
    }

    /**
     * Integra en include de un template
     * @param string $accion Accion en ejecucion
     * @param string $seccion Seccion en ejecucion
     * @return array|stdClass
     * @version 2.32.3
     */
    private function include_action_template_data(string $accion, string $seccion): array|stdClass
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $include_action = $this->include_action_template(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include template', data: $include_action);
        }
        $data = $this->genera_salida(include_action:$include_action);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar salida', data: $data);
        }
        return $data;
    }

    /**
     * Obtiene el template de una vista.
     *
     * @param string $accion Accion a verificar
     *
     * @return array|stdClass
     *
     * @functions $include_action = $init->include_action_template_base. Genera una ruta para obtener un
     * template en base a "$accion". En caso de error, lanzará un mensaje
     *
     * @functions  $data = $init->genera_salida. Valida y maqueta el objeto almacenado si existe tanto
     * el objeto como la ruta del archivo. En caso de error lanzará un mensaje.
     * @version 2.33.3
     */
    private function include_action_template_base_data(string $accion): array|stdClass
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $include_action = $this->include_action_template_base(accion: $accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include template base', data: $include_action);
        }
        $data = $this->genera_salida(include_action:$include_action);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar salida', data: $data);
        }
        return $data;
    }

    /**
     * Obtiene la ruta de un template
     * @param string $accion Accion a verificar
     *
     * @param string $seccion Seccion a verificar
     *
     * @return array|stdClass
     *
     * @functions $data_include = $init->include_action_template_data. Genera una ruta contemplando "$accion" y "$seccion"
     * para obtener un template. En caso de error, lanzará un mensaje.
     *
     * @functions $data_include = $init->include_template_base. Valida y maqueta el objeto requerido en base
     *  "$accion" si éste existe. En caso de error, lanzará un mensaje.
     * @version 2.35.3
     */
    private function include_template(string $accion, string $seccion): array|stdClass
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $data_include = $this->include_action_template_data(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include template', data: $data_include);
        }
        if(!$data_include->existe){
            $data_include = $this->include_template_base(accion: $accion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener include template', data: $data_include);
            }
        }
        return $data_include;
    }

    /**
     * Obtiene la ruta de un template basado en una accion. Si no existe, lanzará un mensaje de error.
     *
     * @param string $accion Accion a verificar
     *
     * @return array|stdClass
     *
     * @functions $init->include_action_template_base_data. Genera una ruta contemplando "$accion"
     * para obtener un template. Si ocurre un error, lanzará un mensaje.
     * @version 2.34.3
     *
     */
    private function include_template_base(string $accion): array|stdClass
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $data_include = $this->include_action_template_base_data(accion: $accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include template', data: $data_include);
        }
        if(!$data_include->existe){
            return $this->error->error(mensaje: 'Error no existe la view', data: $data_include);
        }
        return $data_include;
    }

    /**
     * Obtiene los datos de un template
     * @param string $accion Accion a verificar
     *
     * @param string $seccion Seccion a verificar
     *
     * @return array|stdClass
     *
     * @functions $init->include_action_local_data. Genera una ruta contemplando "$accion" y "$sección"
     * para obtener los datos. Si ocurre un error, lanzará un mensaje.
     *
     * @functions $init->data_include_base. Valida y maqueta el objeto requerido en base
     * a "$accion" y "$seccion" si éste existe. En caso de error, lanzará un mensaje.
     */
    private function include_view(string $accion, string $seccion): array|stdClass
    {
        $data_include = $this->include_action_local_data(accion: $accion,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener include local', data: $data_include);
        }

        if (!$data_include->existe) {
            $data_include = $this->data_include_base(accion: $accion,seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener include local base', data: $data_include);
            }
        }
        return $data_include;
    }


    /**
     * @param bool $aplica_seguridad si aplica seguridad se implementa acl por accion
     * @return array|stdClass
     */
    public function index(bool $aplica_seguridad): array|stdClass
    {
        $con = new conexion();
        $link = conexion::$link;

        $session = (new adm_session($link))->carga_data_session();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar session',data: $session);

        }

        $conf_generales = new generales();
        $seguridad = new seguridad(aplica_seguridad: $aplica_seguridad);
        $_SESSION['tiempo'] = time();

        $seguridad = $this->permiso( link: $link,seguridad:   $seguridad);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al verificar seguridad',data: $seguridad);

        }

        $valida = (new validacion())->seccion_accion(accion: $seguridad->accion, seccion: $seguridad->seccion);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar seccion',data: $valida);
        }

        $aplica_view = $this->aplica_view( link:$link, seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si aplica view', data: $aplica_view);
        }

        $controlador = $this->controller(link:  $link,seccion:  $seguridad->seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar controlador', data: $controlador);

        }

        $include_action = $this->include_action(aplica_view:$aplica_view, seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar include',data: $include_action);

        }

        $out_ws = (new salida_data())->salida_ws(controlador:$controlador, include_action: $include_action,
            seguridad:  $seguridad);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar salida',data: $out_ws);

        }

        $mensajeria = (new mensajes())->data();
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar mensajes',data: $mensajeria);
        }

        $data_custom = (new custom())->data(seguridad: $seguridad);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar datos custom',data: $data_custom);
        }

        $data = new stdClass();
        $data->css_custom = $data_custom->css;
        $data->js_seccion = $data_custom->js_seccion;
        $data->js_accion = $data_custom->js_accion;
        $data->js_view = $data_custom->js_view;

        $data->menu = $seguridad->menu;
        $data->acceso_denegado = $seguridad->acceso_denegado;

        $data->link = $link;
        $data->path_base = $conf_generales->path_base;


        $data->error_msj = $mensajeria->error_msj;
        $data->exito_msj = $mensajeria->exito_msj;

        $data->breadcrumbs = $controlador->breadcrumbs;

        $data->include_action = $include_action;

        $data->controlador = $controlador;

        $data->conf_generales = $conf_generales;
        $data->muestra_index = $conf_generales->muestra_index;
        $data->aplica_view = $aplica_view;


        return $data;
    }

    /**
     *
     * Se inicializan datos base para controler
     * @version 1.41.14
     * @param controler $controler Controlador en ejecucion
     * @return controler
     */
    public function init_data_controler(controler $controler): controler
    {

        $controler->errores = new errores();
        $controler->validacion = new valida_controller();
        $controler->pestanas = new stdClass();
        $controler->pestanas->includes = array();
        $controler->pestanas->targets = array();
        return $controler;
    }

    private function init_for_view(): stdClass
    {
        $data = new stdClass();
        $data->header = false;
        $data->ws = false;
        $data->view = true;
        return $data;
    }

    /**
     * Inicializador de datos para la funcion "ws" en base a los resultados obtenidos  de
     * otras funciones
     * @return stdClass Devuelve las validaciones de las demas funciones para iniciar los procesos en "ws".
     * @example $data->header = false, $data->ws = true, $data->view = false
     *
     * @version 2.122.12
     */
    private function init_for_ws(): stdClass
    {
        $data = new stdClass();
        $data->header = false;
        $data->ws = true;
        $data->view = false;
        return $data;
    }

    /**
     * Integra los keys para parametros base de un input
     * @param int $cols Cols css
     * @param string $key Campo de input
     * @param array $keys_selects Conjunto de keys precargados
     * @param string $place_holder Tag de input
     * @param bool $required Atributo required
     * @return array
     * @version 2.115.12
     */
    public function key_select_txt(int $cols, string $key, array $keys_selects, string $place_holder,
                                   bool $required = true): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data:  $key);
        }

        if(!isset($keys_selects[$key])) {
            $keys_selects[$key] = new stdClass();
        }
        if(!isset($keys_selects[$key]->cols)) {
            $keys_selects[$key]->cols = $cols;
        }
        if(!isset($keys_selects[$key]->place_holder)) {
            $keys_selects[$key]->place_holder = $place_holder;
        }
        if(!isset($keys_selects[$key]->required)) {
            $keys_selects[$key]->required = $required;
        }
        return $keys_selects;
    }

    /**
     * Integra los inputs de un select para parametros
     * @param array $selects Selects
     * @param string $name_model Nombre del modelo
     * @param string $namespace_paquete Paquete
     * @return array
     * @version 2.101.9
     */
    private function maqueta_key_select_input(array  $selects, string $name_model, string $namespace_paquete): array
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error name_model esta vacio',data:  $name_model);
        }
        $namespace_paquete = trim($namespace_paquete);
        if($namespace_paquete === ''){
            return $this->error->error(mensaje: 'Error namespace_paquete esta vacio',data:  $namespace_paquete);
        }

        $name_model_id = $name_model.'_id';
        $selects[$name_model_id] = new stdClass();
        $selects[$name_model_id]->name_model = $name_model;
        $selects[$name_model_id]->namespace_model = "$namespace_paquete\\models";

        return $selects;
    }

    /**
     * Inicializa un campo de tipo model
     * @param array $campos_view Conjunto de campos provenientes de modelo
     * @param string $key Key a integrar
     * @param string $type Tipo de input
     * @return array
     * @version 2.37.3.1
     */
    private function model_init_campos(array $campos_view, string $key, string $type): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data:  $key);
        }

        $type = trim($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio',data:  $type);
        }

        $campos_view[$key]['type'] = $type;
        return $campos_view;

    }

    /**
     * Integra los elementos de una view para system
     * @param array $campos_view Campos de modelo
     * @param string $key Key a integrar
     * @param string $type Tipo de input
     * @return array
     * @version 2.81.6
     */
    private function model_init_campos_input(array $campos_view, string $key, string $type): array
    {

        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data:  $key);
        }
        $type = trim($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio',data:  $type);
        }

        $campos_view = $this->model_init_campos(campos_view: $campos_view,key:  $key,type:  $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }
        return $campos_view;
    }

    /**
     * Inicializa los campos para un template
     * @param array $campos_view Campos precargados
     * @param array $keys Keys a inicializar con nombre de los campos
     * @param string $type Typo de campo inputs o password
     * @return array
     * @version 2.100.9
     */
    private function model_init_campos_inputs(array $campos_view, array $keys, string $type): array
    {
        $type = trim($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio',data:  $type);
        }


        foreach ($keys as $key){

            if(!is_string($key)){
                return $this->error->error(mensaje: 'Error key debe ser string',data:  $key);
            }
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error key esta vacio',data:  $key);
            }

            $campos_view = $this->model_init_campos_input(campos_view: $campos_view, key: $key, type: $type);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
            }
        }

        return $campos_view;
    }

    /**
     * Inicializa los campos de tipo select
     * @param array $campos_view Campos de view template
     * @param array $keys Keys a integrar
     * @param PDO $link Conexion a la base de datos
     * @return array
     * @version 2.105.10
     */
    private function model_init_campos_selects(array $campos_view, array $keys, PDO $link): array
    {

        foreach ($keys as $campo =>$data){
            if(!is_object($data)){
                return $this->error->error(mensaje: 'Error al data de ser un obj',data:  $data);
            }
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error campo esta vacio',data:  $campo);
            }
            if(is_numeric($campo)){
                return $this->error->error(mensaje: 'Error campo es un numero debe ser un texto',data:  $campo);
            }
            $keys_val = array('name_model','namespace_model');
            $valida = (new validacion())->valida_existencia_keys(keys:$keys_val, registro: $data);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar data',data:  $valida);
            }

            $campos_view = $this->model_init_campos_select(campos_view: $campos_view, key: $campo, link: $link,
                name_model: $data->name_model, namespace_model: $data->namespace_model);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
            }

        }

        return $campos_view;
    }

    /**
     * Inicializa los elementos para un input de tipo select
     * @param array $campos_view Campos de template
     * @param string $key Campos a integrar
     * @param PDO $link Conexion a la base de datos
     * @param string $name_model Nombre del modelo entidad
     * @param string $namespace_model Paquete
     * @return array
     * @version 2.103.10
     */
    private function model_init_campos_select(
        array $campos_view, string $key, PDO $link, string $name_model, string $namespace_model): array
    {

        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data:  $key);
        }
        $namespace_model = trim($namespace_model);
        if($namespace_model === ''){
            return $this->error->error(mensaje: 'Error namespace_model esta vacio',data:  $namespace_model);
        }
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error name_model esta vacio',data:  $name_model);
        }

        $campos_view = $this->model_init_campos(campos_view: $campos_view,key:  $key,type:  'selects');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }

        $modelo = (new modelo_base($link))->genera_modelo(modelo: $name_model,namespace_model: $namespace_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera modelo',data:  $modelo);
        }
        $campos_view[$key]['model'] = $modelo;

        return $campos_view;
    }

    public function model_init_campos_template(array $campos_view, stdClass $keys, PDO $link): array
    {
        $campos_view = $this->modela_inputs_campos(campos_view: $campos_view,keys:  $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }

        if(!isset($keys->selects)){
            $keys->selects = array();
        }

        $campos_view = $this->model_init_campos_selects(
            campos_view: $campos_view, keys: $keys->selects, link: $link);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }
        return $campos_view;

    }

    /**
     * Modela los campos view para frontend
     * @param string $atributo Atributo a ajustar
     * @param array $campos_view Campos previos
     * @param stdClass $keys Conjunto de campos a integrar
     * @return array
     * @version 4.9.0
     *
     */
    private function modela_input(string $atributo, array $campos_view, stdClass $keys): array
    {
        $atributo = trim($atributo);
        if($atributo === ''){
            return $this->error->error(mensaje: 'Error atributo esta vacio',data:  $atributo);
        }

        if(!isset($keys->$atributo)){
            $keys->$atributo = array();
        }
        if(!is_array($keys->$atributo)){
            return $this->error->error(mensaje: 'Error $keys->'.$atributo.' debe ser un array',data:  $keys);
        }


        $campos_view = $this->model_init_campos_inputs(campos_view: $campos_view, keys: $keys->$atributo, type: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }
        return $campos_view;
    }

    /**
     * Modela los inputs por type para frontend
     * @param array $atributos Atributos a inicializar
     * @param array $campos_view Campos a integrar provenientes de modelo
     * @param stdClass $keys Parametros
     * @return array
     * @version 4.10.1
     */
    private function modela_inputs_attr(array $atributos, array $campos_view, stdClass $keys): array
    {
        foreach ($atributos as $atributo){
            $atributo = trim($atributo);
            if($atributo === ''){
                return $this->error->error(mensaje: 'Error atributo esta vacio',data:  $atributo);
            }

            $campos_view = $this->modela_input(atributo: $atributo,campos_view:  $campos_view,keys:  $keys);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
            }
        }
        return $campos_view;
    }

    /**
     * Modela los elementos de front base
     * @param array $campos_view Campos de modelo
     * @param stdClass $keys Keys con parametros especiales
     * @return array
     * @version 6.3.0
     */
    private function modela_inputs_campos(array $campos_view, stdClass $keys): array
    {
        $atributos = array('inputs','passwords','telefonos','emails','fechas');

        $campos_view = $this->modela_inputs_attr(atributos: $atributos,campos_view:  $campos_view,keys:  $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }
        return $campos_view;
    }

    /**
     *
     * Retorna del nombre de cun controlador para su creacion posterior
     * @version 1.176.33
     * @param string $seccion Seccion en ejecucion
     * @return string|array
     */
    private function name_controler(string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia ',data: $seccion);
        }
        $sistema = (new generales())->sistema;
        $namespace = '';
        if($sistema === 'administrador'){
            $namespace = 'gamboamartin\\';
        }

        /**
         * REFCATORIZAR SIMPLICAR REGISTRO DE PAQUETES
         */
        if($sistema === 'organigrama'){
            $namespace = 'gamboamartin\\organigrama\\';
        }
        if($sistema === 'cat_sat'){
            $namespace = 'gamboamartin\\cat_sat\\';
        }

        if($sistema === 'academico'){
            $namespace = 'gamboamartin\\academico\\';
        }
        if($sistema === 'cfd_sep'){
            $namespace = 'gamboamartin\\cfd_sep\\';
        }
        if($sistema === 'acl'){
            $namespace = 'gamboamartin\\acl\\';
        }
        if($sistema === 'documento'){
            $namespace = 'gamboamartin\\documento\\';
        }
        
        if($sistema === 'proceso'){
            $namespace = 'gamboamartin\\proceso\\';
        }
        if($sistema === 'nomina'){
            $namespace = 'gamboamartin\\nomina\\';
        }
        if($sistema === 'comercial'){
            $namespace = 'gamboamartin\\comercial\\';
        }
        if($sistema === 'tg_cliente'){
            $namespace = 'tglobally\\tg_cliente\\';
        }
        if($sistema === 'tg_empresa'){
            $namespace = 'tglobally\\tg_empresa\\';
        }
        if($sistema === 'tg_empleado'){
            $namespace = 'tglobally\\tg_empleado\\';
        }
        if($sistema === 'tg_acl'){
            $namespace = 'tglobally\\tg_acl\\';
        }
        if($sistema === 'tg_nomina'){
            $namespace = 'tglobally\\tg_nomina\\';
        }
        if($sistema === 'tg_imss'){
            $namespace = 'tglobally\\tg_imss\\';
        }
        if($sistema === 'tg_direccion_postal'){
            $namespace = 'tglobally\\tg_direccion_postal\\';
        }
        if($sistema === 'tg_cobranza'){
            $namespace = 'tglobally\\tg_cobranza\\';
        }
        if($sistema === 'empleado'){
            $namespace = 'gamboamartin\\empleado\\';
        }
        if($sistema === 'facturacion'){
            $namespace = 'gamboamartin\\facturacion\\';
        }
        if($sistema === 'im_registro_patronal'){
            $namespace = 'gamboamartin\\im_registro_patronal\\';
        }
        if($sistema === 'imss'){
            $namespace = 'gamboamartin\\im_registro_patronal\\';
        }
        if($sistema === 'banco'){
            $namespace = 'gamboamartin\\banco\\';
        }
        if($sistema === 'facturacion'){
            $namespace = 'gamboamartin\\facturacion\\';
        }
        if($sistema === 'gastos'){
            $namespace = 'gamboamartin\\gastos\\';
        }
        if($sistema === 'tg_facturacion'){
            $namespace = 'tglobally\\tg_facturacion\\';
        }
        if($sistema === 'tg_banco'){
            $namespace = 'tglobally\\tg_banco\\';
        }
        if($sistema === 'almacen'){
            $namespace = 'gamboamartin\\almacen\\';
        }
        if($sistema === 'tg_cat_gen'){
            $namespace = 'tglobally\\tg_cat_gen\\';
        }
        if($sistema === 'instalacion'){
            $namespace = 'gamboamartin\\instalacion\\';
        }
        if($sistema === 'boletaje'){
            $namespace = 'gamboamartin\\boletaje\\';
        }
        if($sistema === 'tg_cat_sat'){
            $namespace = 'tglobally\\tg_cat_sat\\';
        }
        if($sistema === 'academico'){
            $namespace = 'gamboamartin\\academico\\';
        }
        if($sistema === 'cobranza'){
            $namespace = 'gamboamartin\\cobranza\\';
        }


        $name_ctl = 'controlador_'.$seccion;
        $name_ctl = str_replace($namespace.'controllers\\','',$name_ctl);
        $name_ctl = $namespace.'controllers\\'.$name_ctl;

        if(!class_exists($name_ctl)){
            return $this->error->error(mensaje: 'Error no existe la clase '.$name_ctl,data: $name_ctl);
        }

        return $name_ctl;
    }

    /**
     * Da lña salida para web
     * @param bool $existe Verifica si existe view
     * @param string $include_action Accion include para front
     * @return stdClass
     * @version 2.28.3
     */
    private function output_include(bool $existe, string $include_action): stdClass
    {
        $data = new stdClass();
        $data->existe = $existe;
        $data->include_action = $include_action;
        return $data;
    }

    /**
     * P INT P ORDER
     * @return stdClass
     */
    public function params_controler(): stdClass
    {

        $data_i = $this->init_params();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar ws',data: $data_i);
        }

        $data_i = $this->init_con_get(data_i:$data_i);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar ws',data: $data_i);
        }


        return $data_i;
    }

    private function init_con_get(stdClass $data_i): array|stdClass
    {
        if(isset($_GET['ws'])){
            $data_i = $this->init_for_ws();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar ws',data: $data_i);
            }

        }
        if(isset($_GET['view'])) {

            $data_i = $this->init_for_view();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializar ws', data: $data_i);
            }
        }
        return $data_i;
    }

    /**
     * Inicializa los elementos para salida de un controller
     * @version 1.133.31
     * @return stdClass
     */
    private function init_params(): stdClass
    {

        $data = new stdClass();

        $data->ws = false;
        $data->header = true;
        $data->view = false;

        return $data;
    }

    /**
     * P INT
     * Funcion utilizada para verificar las solicitudes de un permiso.
     *
     * @param PDO $link Representa la conexion entre PHP y la base de datos
     *
     * @param seguridad $seguridad llamada a la clase "seguridad"
     *
     * @return array|seguridad
     *
     * @functions $modelo_accion = new adm_accion.  Genera un objeto de tipo adm_accion.
     *
     * @functions $permiso = $modelo_accion->permiso.  Valida que el grupo de usuarios cuente con los
     * permisos basado en accion y seccion
     *
     * @functions $n_acciones = $modelo_accion->cuenta_acciones. Cuenta la cantidad de funciones las cuales el grupo de
     * usuarios tiene permisos
     */
    public function permiso(PDO $link, seguridad $seguridad): array|seguridad
    {
        $modelo_accion = new adm_accion($link);
        if (isset($_SESSION['grupo_id'])) {
            $permiso = $modelo_accion->permiso(accion: $seguridad->accion, seccion: $seguridad->seccion);
            if(errores::$error){
                session_destroy();
                return $this->error->error('Error al validar permisos',$permiso);
            }

            if (!$permiso) {
                $seguridad->seccion = 'adm_session';
                $seguridad->accion = 'denegado';
                $seguridad->acceso_denegado = true;
            }

            $n_acciones = $modelo_accion->cuenta_acciones();
            if(errores::$error){
                session_destroy();
                return $modelo_accion->error->error(mensaje: 'Error al contar acciones permitidas',data: $n_acciones);
            }
            if ((int)$n_acciones === 0) {
                session_destroy();
            }
        }
        return $seguridad;
    }

    public function select_key_input(array $init_data, array $selects): array
    {

        foreach ($init_data as $name_model=>$namespace_paquete){
            $selects = $this->maqueta_key_select_input(selects: $selects,name_model: $name_model, namespace_paquete: $namespace_paquete);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar select',data:  $selects);
            }
        }
        return $selects;
    }

    /**
     * Obtiene la session en curso
     * Genera la session_id basada en un rand
     * @return array|string string es la session generada
     * @version 2.25.3
     */
    private function session_id(): array|string
    {
        if(isset($_GET['session_id'])){
            return $_GET['session_id'];
        }
        try{
            $session_id = random_int(10,99);
            $session_id .= random_int(10,99);
            $session_id .= random_int(10,99);
            $session_id .= random_int(10,99);
            $session_id .= random_int(10,99);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al generar session', data: $e);
        }
        return $session_id;
    }
}