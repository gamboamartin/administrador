<?php
namespace base;

use config\generales;
use gamboamartin\administrador\models\adm_session;
use gamboamartin\errores\errores;
use PDO;
use stdClass;


class seguridad{
    public string|bool $seccion = false;
    public string|bool $accion = false ;
    public string|bool $menu = false;
    public string|bool $webservice = false;
    public bool $acceso_denegado = false;
    private errores $error;

    public function __construct(bool $aplica_seguridad = true){

        $this->error = new errores();

        $init = $this->inicializa_data(aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            $error = $this->error->error(mensaje: 'Error al inicializar menu',data:  $init);
            print_r($error);
            die('Error');
        }

    }

    private function elimina_session_activa(array $filtro, adm_session $session_modelo){
        $result = $session_modelo->elimina_con_filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje:"Error al eliminar registro",data:  $result);
        }
        if(session_status() === PHP_SESSION_ACTIVE) {
            unset ($_SESSION['username']);
            session_destroy();
        }
        return true;
    }

    /**
     * Verifica si se permite o no la eliminacion de una session
     * @param stdClass $r_session Resultado previo de session en database
     * @return bool
     */
    private function elimina_session_verifica(stdClass $r_session): bool
    {
        $elimina = true;
        if((int)$r_session->n_registros === 1){
            $session = $r_session->registros[0];
            if($session['adm_session_permanente'] === 'activo'){
                $elimina = false;
            }
        }
        return $elimina;
    }

    /**
     * Elimina los datos de una session
     * @param PDO $link
     * @return array|bool
     */
    final public function elimina_session(PDO $link): bool|array
    {
        $filtro = array('adm_session.name'=>(new generales())->session_id);
        $session_modelo = new adm_session(link: $link);

        $r_session = $session_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al obtener registro",data:  $r_session);
        }

        $elimina = $this->elimina_session_completa(filtro: $filtro,r_session:  $r_session,
            session_modelo:  $session_modelo);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al obtener si permite del",data:  $elimina);
        }


        return $elimina;
    }

    private function elimina_session_completa(array $filtro, stdClass $r_session, adm_session $session_modelo){
        $elimina = $this->elimina_session_verifica(r_session: $r_session);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al obtener si permite del",data:  $elimina);
        }

        if($elimina) {
            $result = $this->elimina_session_activa(filtro: $filtro,session_modelo:  $session_modelo);
            if (errores::$error) {
                return $this->error->error(mensaje:"Error al eliminar registro y finalizar session",data:  $result);
            }
        }
        return $elimina;
    }

    private function inicializa_data(bool $aplica_seguridad): array|static
    {
        $init = $this->init_vars(aplica_seguridad:$aplica_seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data',data:  $init);

        }

        $init = $this->init_full_menu(aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar menu',data:  $init);
        }
        return $init;
    }

    /**
     * Inicializa los datos para implementar seguridad
     * @return $this
     * @version 2.5.2
     */
    private function init(): static
    {
        if(isset($_GET['seccion'])){
            $this->seccion = $_GET['seccion'];
        }
        if(isset($_GET['accion'])){
            $this->accion = $_GET['accion'];
        }
        if(isset($_GET['webservice'])) {
            $this->webservice = $_GET['webservice'];
        }
        return $this;
    }

    /**
     * TODO
     * Inicializa this->accion si session esta activa asigna a inicio
     * @return bool|string
     */
    private function init_accion(): bool|string
    {
        $this->accion = 'login';
        if(isset($_SESSION['activa'])){
            $this->accion = 'inicio';
        }
        return $this->accion;
    }

    private function init_full_menu(bool $aplica_seguridad): array|static
    {
        $init = $this->init_menu_inicial(aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar menu',data:  $init);
        }

        $init = $this->init_menu_accion(aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar menu',data:  $init);

        }
        return $init;
    }

    private function init_menu(): static
    {
        if(isset($_SESSION['activa']) && (int)$_SESSION['activa'] === 1) {
            $this->menu = true;
        }
        return $this;
    }

    private function init_menu_accion(bool $aplica_seguridad): array|static
    {
        if($this->seccion === 'adm_session' && $this->accion === 'inicio' && $aplica_seguridad){

            $accion = $this->init_accion();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar accion',data:  $accion);
            }
        }
        return $this;
    }

    private function init_menu_inicial(bool $aplica_seguridad): array|static
    {
        $init = $this->init_menu();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar menu',data:  $init);
        }

        if(!isset($_SESSION['activa']) && ($this->seccion !== 'adm_session') && $this->accion !== 'loguea' && $aplica_seguridad) {

            $data = $this->init_menu_login();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar login',data:  $data);

            }
        }
        return $this;
    }

    /**
     * TODO
     * Inicializa menu en false, seccion en session y accion en login
     * @return stdClass
     */
    private function init_menu_login(): stdClass
    {
        $this->menu = false;
        $this->seccion = "adm_session";
        $this->accion = "login";

        $data = new stdClass();
        $data->menu = $this->menu;
        $data->seccion = $this->seccion;
        $data->accion = $this->accion;
        return $data;
    }

    private function init_val_inicio(bool $aplica_seguridad): static
    {
        if(($this->seccion === 'adm_session') && $this->accion === 'login' && isset($_SESSION['activa']) && $aplica_seguridad) {
            $this->seccion = 'adm_session';
            $this->accion = 'inicio';
        }
        return $this;
    }

    private function init_val_login(bool $aplica_seguridad): static
    {
        if(!$this->seccion){
            $this->seccion = 'adm_session';
            $this->accion = "inicio";
            if(!isset($_SESSION['activa']) && $aplica_seguridad){
                $this->accion = "login";
            }
        }
        return $this;
    }

    private function init_vars(bool $aplica_seguridad): array|static
    {
        $init = $this->init();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data',data:  $init);
        }

        $init = $this->init_val_login(aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data',data:  $init);
        }
        $init = $this->init_val_inicio(aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data',data:  $init);
        }
        return $this;
    }

    /**
     * AMBITO
     * @param $link
     * @param $tiempo_activo
     * @return array|void
     */
    public function valida_tiempo_session($link, $tiempo_activo){
        $vida_session = time() - $tiempo_activo;
        if($vida_session > MAX_TIEMPO_INACTIVO)
        {
            $data = $this->elimina_session($link);
            if(errores::$error){
                return $this->error->error("Error al eliminar registro", $data);
            }
            header('Location: index.php?seccion=adm_session&accion=login');
        }
    }

}
