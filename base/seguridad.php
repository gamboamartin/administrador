<?php
namespace base;
require "init.php";

use config\generales;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use models\session;
use PDO;


class seguridad{
    public string|bool $seccion = false;
    public string|bool $accion = false ;
    public string|bool $menu = false;
    public string|bool $webservice = false;
    private errores $error;

    #[Pure] public function __construct(){

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
            $this->seccion = 'session';
            $this->accion = "inicio";
            if(!isset($_SESSION['activa'])){
                $this->accion = "login";
            }
        }


        if(($this->seccion === 'session') && $this->accion === 'login' && isset($_SESSION['activa'])) {
            $this->seccion = 'session';
            $this->accion = 'inicio';
        }

        if(isset($_SESSION['activa']) && (int)$_SESSION['activa'] === 1) {
            $this->menu = true;
        }

        if(!isset($_SESSION['activa']) && ($this->seccion !== 'session') && $this->accion !== 'loguea') {
            $this->menu = false;
            $this->seccion = "session";
            $this->accion = "login";
        }

        if($this->seccion === 'session' && $this->accion === 'inicio'){

            $this->accion = 'login';
            if(isset($_SESSION['activa'])){
                $this->accion = 'inicio';
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
        $filtro = array('session_id'=>(new generales())->session_id);
        $session_modelo = new session($link);

        $r_session = $session_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error("Error al obtener registro", $r_session);
        }
        $elimina = true;
        if((int)$r_session['n_registros'] === 1){
            $session = $r_session['registros'][0];
            if($session['session_permanente'] === 'activo'){
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
            header('Location: index.php?seccion=session&accion=login');
        }
    }

}
