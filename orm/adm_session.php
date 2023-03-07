<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo;
use config\generales;
use gamboamartin\calculo\calculo;
use gamboamartin\errores\errores;


use JetBrains\PhpStorm\Pure;
use PDO;
use stdClass;
use Throwable;

class adm_session extends modelo{//PRUEBAS FINALIZADAS
    public function __construct(PDO $link){
        $tabla = 'adm_session';
        $columnas = array($tabla=>false, 'adm_usuario'=>$tabla,'adm_grupo'=>'adm_usuario');

        $columnas_extra['adm_session_nombre_completo'] =
            "(CONCAT( ( IFNULL(adm_usuario.nombre,'') ),' ',( IFNULL(adm_usuario.ap,'') ),' ',( IFNULL(adm_usuario.am,'') )) )";

        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas, columnas_extra: $columnas_extra);
        $this->NAMESPACE = __NAMESPACE__;
    }

    /**
     * Obtiene del nombre del usuario en session
     * @param string $adm_session_name Session name
     * @return array|string
     * @version 9.125.5
     */
    final public function adm_session_nombre_completo(string $adm_session_name): array|string
    {

        $adm_session_name = trim($adm_session_name);
        if($adm_session_name === ''){
            return $this->error->error(mensaje: "Error adm_session_name esta vacia",data:  $adm_session_name);
        }

        $session_en_ejecucion = $this->session(session: $adm_session_name);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al cargar session',data: $session_en_ejecucion);
        }
        if((int)$session_en_ejecucion->n_registros > 1){
            return  $this->error->error(mensaje: 'Error existe mas de una session',data: $session_en_ejecucion);
        }

        $adm_session_nombre_completo = '';
        if((int)$session_en_ejecucion->n_registros === 1){
            $adm_session_nombre_completo = $session_en_ejecucion->registros[0]['adm_session_nombre_completo'];
        }
        return $adm_session_nombre_completo;
    }

    /**
     *
     * @return array
     */
    public function asigna_acciones_iniciales():array{
        $accion_modelo = new adm_accion($this->link);
        $resultado = $accion_modelo->obten_acciones_iniciales();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones iniciales',data: $resultado);
        }
        return $resultado['registros'];
    }

    /**
     * P ORDER P INT ERRORREV
     * @param stdClass $r_session
     * @return array
     */
    final public function asigna_data_session(stdClass $r_session): array
    {

        $session_activa = $this->session_activa();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar session', data: $session_activa);
        }

        $carga = $this->init_data_session(r_session: $r_session,session_activa:  $session_activa);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al $asigna session', data: $carga);
        }

        return $_SESSION;
    }

    /**
     *
     * Asigna los datos a mostrar al usuario en base a su id de grupo y usuario
     * @param stdClass $r_session Session a verificar
     * @return array
     * @version 1.518.51
     */
    private function asigna_datos_session(stdClass $r_session): array
    {
        if(!isset($r_session->registros)){
            return $this->error->error(mensaje: 'Error r_session no tiene key registros',data:  $r_session);
        }
        if(!is_array($r_session->registros)){
            return $this->error->error(mensaje: 'Error $r_session->registros debe ser un array',data:  $r_session);
        }
        if(count($r_session->registros) === 0){
            return $this->error->error(mensaje: 'Error $r_session->registros esta vacio',data:  $r_session);
        }
        if(count($r_session->registros) > 1){
            return $this->error->error(mensaje: 'Error $r_session->registros es incoherente',data:  $r_session);
        }
        if(!is_array($r_session->registros[0])){
            return $this->error->error(mensaje: 'Error $r_session->registros[0] debe ser un array',data:  $r_session);
        }

        $keys = array('adm_grupo_id','adm_usuario_id');
        $valida = $this->validacion->valida_ids(keys:$keys,registro:  $r_session->registros[0]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar r_session',data:  $valida);
        }
        $keys = array('adm_session_nombre_completo');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,
            registro:  $r_session->registros[0],valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar r_session',data:  $valida);
        }

        $_SESSION['numero_empresa'] = 1;
        $_SESSION['activa'] = 1;
        $_SESSION['grupo_id'] = $r_session->registros[0]['adm_grupo_id'];
        $_SESSION['usuario_id'] = $r_session->registros[0]['adm_usuario_id'];
        $_SESSION['nombre_usuario'] = $r_session->registros[0]['adm_session_nombre_completo'];
        return $_SESSION;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param stdClass $r_session
     * @return array
     */
    private function carga_session(stdClass $r_session): array
    {
        $init = $this->init_session(session_id:(new generales())->session_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al iniciar session',data:  $init);
        }

        $asigna = $this->asigna_datos_session(r_session: $r_session);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al $asigna session', data: $asigna);
        }
        return $asigna;
    }

    /**
     * P ORDER P INT ERRORREV
     *
     * Funcion que carga los datos de una sesion. En caso de haber una sesion activa, cargará los
     * datos de esa sesion. Caso contrario cerrará/destuirá las sesiones. Devuelve el estado de la sesion.
     *
     * @param stdClass $r_session Sesion a verificar
     * @param bool $session_activa Verífica la sesion está activa
     * @return bool|array
     *
     * @function $carga = $adm_session->carga_session(r_session: $r_session);
     * Maqueta los datos de la sesion en curso. En caso de error al asignar la sesion devolverá un error
     *
     */
    private function init_data_session(stdClass $r_session, bool $session_activa): bool|array
    {
        if($session_activa) {
            $carga = $this->carga_session(r_session: $r_session);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al $asigna session',data:  $carga);
            }
        }
        else{
            session_destroy();
        }
        return $session_activa;
    }

    /**
     *
     * Funcion para generar una session, recibe un id de session y verifica que sea válido,
     * en caso de error lanzará un mensaje.
     *
     * @param string $session_id Identificador de la session que se usará
     * @return string|array
     * @version 1.518.51
     *
     */
    private function init_session(string $session_id): string|array
    {
        $session_id = trim($session_id);
        if($session_id === ''){
            return $this->error->error(mensaje: 'Error session_id esta vacia',data:  $session_id);
        }

        try{
            session_id($session_id);
            session_start();
        }
        catch (Throwable $e){
            return $this->error->error(mensaje:'Error al iniciar session', data: $e);
        }

        return $session_id;
    }



    public function inserta_session(array $usuario): array
    {
        $data_session = $this->session_permanente($usuario);
        if(errores::$error){
            return $this->error->error(MENSAJES['session_maqueta'], $data_session);
        }
        $r_session = $this->alta_registro($data_session);
        if(errores::$error){
            return $this->error->error(MENSAJES['alta_error'], $r_session);
        }
        return $r_session;
    }

    /**
     * P INT P ORDER ERROREV
     *
     * Funcion para cargar los datos de una sesion iniciada en base a los filtros aplicados a $session_id.
     * En caso de error al obtener o a asignar una sesion, lanzará un mensaje.
     *
     * @return array
     *
     * $r_session = $adm_session->filtro_and(filtro: $filtro);
     *
     * $session = $adm_seccion->asigna_data_session(r_session: $r_session);
     */
    final public function carga_data_session(): array
    {
        $session_id = $_GET['session_id'] ?? '';
        $filtro['adm_session.name'] = $session_id;
        $r_session = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener session',data: $r_session);
        }
        $session = array();
        if((int)$r_session->n_registros === 1){
            $session = $this->asigna_data_session(r_session: $r_session);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al asignar session',data: $session);
            }
        }
        return $session;
    }

    public function carga_sessiones_fijas(): array|stdClass
    {
        $result = new stdClass();
        $r_usuarios = (new adm_usuario($this->link))->obten_registros_activos();
        $usuarios = $r_usuarios['registros'];
        foreach($usuarios as $usuario){
            $continua = $this->continua_carga($usuario);
            if(errores::$error){
                return $this->error->error(MENSAJES['continua_error'], $continua);
            }
            if(!$continua){
                continue;
            }
            $r_session = $this->inserta_session($usuario);
            if(errores::$error){
                return $this->error->error(MENSAJES['alta_error'], $r_session);
            }
            $result->data = $r_session;
        }
        return $result;

    }

    public function continua_carga(array $usuario): bool|array
    {
        $continua = true;
        if((int)$usuario['usuario_session']===-1){
            $continua = false;
        }
        $r_session = $this->session($usuario['usuario_session']);
        if(errores::$error){
            return $this->error->error("Error al filtrar", $r_session);
        }
        if((int)$r_session['n_registros'] === 1){
            $continua = false;
        }
        return $continua;
    }

    public function consulta_ultima_ejecucion(string $session_id){ //FIN PROT

        $filtro['session.session_id']['campo'] = 'session.session_id';
        $filtro['session.session_id']['value'] = $session_id;

        $registros = $this->filtro_and($filtro,'numeros', array(),array(),0, 0,array());

        if(isset($registros['error'])){
            return $this->error->error('Error al filtrar sessiones',$registros);
        }
        if((int)$registros['n_registros'] >0){
            return $registros['registros'][0]['session_fecha_ultima_ejecucion'];
        }
        return '1900-01-01';
    }

    public function limpia_sessiones(){
        $filtro['session.permanente'] = 'inactivo';
        $fecha = (new calculo())->obten_fecha_resta(0, date('Y-m-d'));
        if(errores::$error){
            return $this->error->error(MENSAJES['fecha_error'], $fecha);
        }
        $filtro_especial[0]['session.fecha_alta']['operador'] = '<=';
        $filtro_especial[0]['session.fecha_alta']['valor'] =$fecha;

        $r_session = $this->filtro_and(filtro:$filtro,filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error("Error al filtrar", $r_session);
        }
        $sessiones = $r_session['registros'];
        foreach($sessiones as $session){
            $r_elimina = $this->elimina_bd($session['session_id']);
            if(errores::$error){
                return $this->error->error(MENSAJES['elimina_error'], $r_elimina);
            }
        }
        return $sessiones;
    }

    /**
     *
     * @return array
     */
    public function modifica_session(): array
    {
        $filtro['session.session_id'] = SESSION_ID;
        $result = $this->modifica_con_filtro_and($filtro, array('fecha_ultima_ejecucion' => time()));
        if(errores::$error){
            return $this->error->error('Error al ajustar session',$result);

        }
        return $result;
    }

    /**
     * FULL
     * Funcion para obtener los resultados de los filtros en base a los parametros
     * dados por $seccion. En caso de que la seccion esté vacia, la clase sea invalida
     *
     * @param string $seccion Seccion a verificar
     * @return array
     */
    public function obten_filtro_session(string $seccion): array{
        $seccion = str_replace('models\\','',$seccion);
        $class = 'models\\'.$seccion;
        if($seccion===''){
            return $this->error->error(mensaje: "Error la seccion esta vacia",data: $seccion);
        }

        $filtro = array();
        if(isset($_SESSION['filtros'][$seccion])){
            $filtro = $_SESSION['filtros'][$seccion];
            if(!is_array($filtro)){
                return $this->error->error(mensaje: 'Error filtro invalido',data: $filtro);
            }
        }

        return $filtro;
    }

    /**
     * Obtiene los datos de una session
     * @param string $session session_id por GET
     * @return array|stdClass
     * @version 3.7.1
     */
    private function session(string $session): array|stdClass
    {
        $session = trim($session);

        $filtro['adm_session.name'] = $session;
        $r_session = $this->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al filtrar",data:  $r_session);
        }
        return $r_session;
    }

    /**
     * Dice si una session esta activa a no
     * @return bool
     * @version 1.518.51
     */
    #[Pure] private function session_activa(): bool
    {
        $session_id = (new generales())->session_id;
        $session_activa = false;
        if($session_id !== ''){
            $session_activa = true;
        }
        return $session_activa;
    }


    public function session_permanente(array $usuario): array
    {
        $data_session['session_id'] = $usuario['usuario_session'];
        $data_session['usuario_id'] = $usuario['usuario_id'];
        $data_session['numero_empresa'] = 1;
        $data_session['fecha'] = date('Y-m-d');
        $data_session['grupo_id'] = $usuario['grupo_id'];
        $data_session['fecha_ultima_ejecucion'] = time();
        $data_session['status'] = 'activo';
        $data_session['permanente'] = 'activo';
        return $data_session;
    }
}