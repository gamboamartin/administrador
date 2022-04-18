<?php
namespace models;

use config\generales;
use gamboamartin\calculo\calculo;
use gamboamartin\errores\errores;


use gamboamartin\orm\modelo;
use JetBrains\PhpStorm\Pure;
use PDO;
use stdClass;
use Throwable;

class session extends modelo{//PRUEBAS FINALIZADAS
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'usuario'=>$tabla,'grupo'=>'usuario');
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
    }

    /**
     *
     * @return array
     */
    public function asigna_acciones_iniciales():array{
        $accion_modelo = new accion($this->link);
        $resultado = $accion_modelo->obten_acciones_iniciales();
        if(errores::$error){
            return $this->error->error('Error al obtener acciones iniciales',$resultado);
        }
        return $resultado['registros'];
    }

    /**
     * P ORDER P INT
     * @param stdClass $r_session
     * @return array
     */
    public function asigna_data_session(stdClass $r_session): array
    {

        $session_activa = $this->session_activa();
        if(errores::$error){
            return $this->error->error('Error al validar session', $session_activa);
        }

        $carga = $this->init_data_session(r_session: $r_session,session_activa:  $session_activa);
        if(errores::$error){
            return $this->error->error('Error al $asigna session', $carga);
        }

        return $_SESSION;
    }

    /**
     * P ORDER P INT
     * @param stdClass $r_session
     * @return array
     */
    private function asigna_datos_session(stdClass $r_session): array
    {
        $_SESSION['numero_empresa'] = 1;
        $_SESSION['activa'] = 1;
        $_SESSION['grupo_id'] = $r_session->registros[0]['grupo_id'];
        $_SESSION['usuario_id'] = $r_session->registros[0]['usuario_id'];
        return $_SESSION;
    }

    /**
     * P ORDER P INT
     * @param stdClass $r_session
     * @return array
     */
    private function carga_session(stdClass $r_session): array
    {
        $init = $this->init_session(session_id:(new generales())->session_id);
        if(errores::$error){
            return $this->error->error('Error al iniciar session', $init);
        }

        $asigna = $this->asigna_datos_session(r_session: $r_session);
        if(errores::$error){
            return $this->error->error('Error al $asigna session', $asigna);
        }
        return $asigna;
    }

    /**
     * P ORDER P INT
     * @param stdClass $r_session
     * @param bool $session_activa
     * @return bool|array
     */
    private function init_data_session(stdClass $r_session, bool $session_activa): bool|array
    {
        if($session_activa) {
            $carga = $this->carga_session(r_session: $r_session);
            if(errores::$error){
                return $this->error->error('Error al $asigna session', $carga);
            }
        }
        else{
            session_destroy();
        }
        return $session_activa;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $session_id
     * @return string|array
     */
    private function init_session(string $session_id): string|array
    {
        $session_id = trim($session_id);
        if($session_id === ''){
            return $this->error->error('Error session_id esta vacia', $session_id);
        }

        try{
            session_id($session_id);
            session_start();
        }
        catch (Throwable $e){
            return $this->error->error('Error al iniciar session', $e);
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
     * P INT P ORDER
     * @return array
     */
    public function carga_data_session(): array
    {
        $session_id = $_GET['session_id'] ?? '';
        $filtro['session.name'] = $session_id;
        $r_session = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener session',$r_session);
        }
        $session = array();
        if((int)$r_session->n_registros === 1){
            $session = $this->asigna_data_session(r_session: $r_session);
            if(errores::$error){
                return $this->error->error('Error al asignar session',$session);
            }
        }
        return $session;
    }

    public function carga_sessiones_fijas(): array|stdClass
    {
        $result = new stdClass();
        $r_usuarios = (new usuario($this->link))->obten_registros_activos();
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
     * P ORDER P INT PROBADO
     * @param string $seccion
     * @return array
     */
    public function obten_filtro_session(string $seccion): array{
        $seccion = str_replace('models\\','',$seccion);
        $class = 'models\\'.$seccion;
        if($seccion===''){
            return $this->error->error("Error la seccion esta vacia",$seccion);
        }
        if(!class_exists($class)){
            return $this->error->error("Error la clase es invalida",$class);
        }
        $filtro = array();
        if(isset($_SESSION['filtros'][$seccion])){
            $filtro = $_SESSION['filtros'][$seccion];
            if(!is_array($filtro)){
                return $this->error->error('Error filtro invalido',$filtro);
            }
        }

        return $filtro;
    }

    public function session(string $session): array
    {
        $filtro['session.session_id'] = $session;
        $r_session = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error("Error al filtrar", $r_session);
        }
        return $r_session;
    }

    /**
     * P ORDER P INT PROBADO
     * @return bool
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