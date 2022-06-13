<?php
namespace base;

use config\generales;
use gamboamartin\errores\errores;
use models\adm_session;
use PDO;
use stdClass;


class seguridad{
    public string|bool $seccion = false;
    public string|bool $accion = false ;
    public string|bool $menu = false;
    public string|bool $webservice = false;
    private errores $error;

    public function __construct(bool $aplica_seguridad = true){

        $this->error = new errores();
        if(isset($_GET['seccion'])){
            $this->seccion = $_GET['seccion'];
        }
        if(isset($_GET['accion'])){
            $this->accion = $_GET['accion'];
        }
        if(isset($_GET['webservice'])) {
            $this->webservice = $_GET['webservice'];
        }

        if(!$this->seccion){
            $this->seccion = 'adm_session';
            $this->accion = "inicio";
            if(!isset($_SESSION['activa']) && $aplica_seguridad){
                $this->accion = "login";
            }
        }


        if(($this->seccion === 'adm_session') && $this->accion === 'login' && isset($_SESSION['activa']) && $aplica_seguridad) {
            $this->seccion = 'adm_session';
            $this->accion = 'inicio';
        }

        if(isset($_SESSION['activa']) && (int)$_SESSION['activa'] === 1) {
            $this->menu = true;
        }

        if(!isset($_SESSION['activa']) && ($this->seccion !== 'adm_session') && $this->accion !== 'loguea' && $aplica_seguridad) {

            $data = $this->init_menu_login();
            if(errores::$error){
                $error = $this->error->error(mensaje: 'Error al inicializar login',data:  $data,
                    params: get_defined_vars());
                print_r($error);
                die('Error');
            }

        }

        if($this->seccion === 'adm_session' && $this->accion === 'inicio' && $aplica_seguridad){

            $accion = $this->init_accion();
            if(errores::$error){
                $error = $this->error->error(mensaje: 'Error al inicializar accion',data:  $accion,
                    params: get_defined_vars());
                print_r($error);
                die('Error');
            }
        }

    }

    /**
     * AMBITO
     * @param PDO $link
     * @return array|bool
     */
    public function elimina_session(PDO $link): bool|array
    {
        $filtro = array('adm_session.name'=>(new generales())->session_id);
        $session_modelo = new adm_session($link);

        $r_session = $session_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error("Error al obtener registro", $r_session);
        }
        $elimina = true;
        if((int)$r_session->n_registros === 1){
            $session = $r_session->registros[0];
            if($session['adm_session_permanente'] === 'activo'){
                $elimina = false;
            }
        }
        if($elimina) {
            $session_modelo->filtro = $filtro;
            $result = $session_modelo->elimina_con_filtro_and();
            if (errores::$error) {
                return $this->error->error("Error al eliminar registro", $result);
            }
            unset ($_SESSION['username']);
            session_destroy();
        }
        return $elimina;
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
