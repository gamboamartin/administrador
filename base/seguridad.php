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

    /**
     * REG
     * Elimina una sesión activa en la base de datos y destruye la sesión en PHP.
     *
     * Esta función valida la existencia del filtro proporcionado, elimina la sesión en la base de datos
     * utilizando `adm_session::elimina_con_filtro_and()`, y si la sesión está activa en PHP, la destruye.
     *
     * @param array $filtro Filtro para seleccionar la sesión a eliminar. Debe contener al menos un elemento.
     *                      - Ejemplo de entrada:
     *                      ```php
     *                      $filtro = ['adm_session.name' => 'my_session_id'];
     *                      ```
     * @param adm_session $session_modelo Instancia del modelo `adm_session`, utilizado para eliminar registros en la BD.
     *
     * @return bool|array Retorna `true` si la eliminación y destrucción de sesión fueron exitosas.
     *                    Retorna un array con detalles de error si ocurre una falla.
     *
     * @example Ejemplo de uso:
     * ```php
     * $filtro = ['adm_session.name' => session_id()];
     * $session_modelo = new adm_session($link);
     * $resultado = $this->elimina_session_activa(filtro: $filtro, session_modelo: $session_modelo);
     *
     * if ($resultado !== true) {
     *     echo "Error: " . print_r($resultado, true);
     * } else {
     *     echo "Sesión eliminada con éxito.";
     * }
     * ```
     *
     * @throws errores Si el filtro está vacío o si hay un error al eliminar la sesión en la BD.
     */
    private function elimina_session_activa(array $filtro, adm_session $session_modelo): bool|array
    {

        if (count($filtro) === 0) {
            return $this->error->error('Error no existe filtro', $filtro, es_final: true);
        }

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
     * REG
     * Verifica si se debe eliminar una sesión de la base de datos.
     *
     * Esta función analiza la sesión proporcionada y determina si debe ser eliminada.
     * Si la sesión es permanente (`adm_session_permanente` = "activo"), la eliminación no se permite.
     *
     * @param stdClass $r_session Objeto que representa el resultado de la consulta de sesión en la base de datos.
     *                            Debe contener los atributos:
     *                            - `n_registros` (int): Número de registros encontrados.
     *                            - `registros` (array): Lista de registros de sesión.
     *                              - Cada registro debe incluir:
     *                                - `adm_session_permanente` (string): Indica si la sesión es permanente ("activo" o "inactivo").
     *
     * @return bool Retorna `true` si la sesión puede eliminarse, `false` si es una sesión permanente y no debe eliminarse.
     *
     * @example Ejemplo de entrada:
     * ```php
     * $r_session = new stdClass();
     * $r_session->n_registros = 1;
     * $r_session->registros = [
     *     ['adm_session_permanente' => 'inactivo']
     * ];
     * $puede_eliminar = $this->elimina_session_verifica($r_session);
     * echo $puede_eliminar ? "Sesión eliminable" : "Sesión permanente, no eliminable";
     * ```
     *
     * @example Ejemplo de salida:
     * ```php
     * true  // Si la sesión puede eliminarse
     * false // Si la sesión es permanente y no debe eliminarse
     * ```
     *
     * @throws errores No lanza errores, pero ajusta los valores por defecto si las claves no están definidas en `$r_session`.
     */
    private function elimina_session_verifica(stdClass $r_session): bool
    {
        $elimina = true;
        if(!isset($r_session->n_registros)){
            $r_session->n_registros = -1;
        }
        if((int)$r_session->n_registros === 1){
            if(!isset($r_session->registros[0])){
                $r_session->registros[0] = array();
            }
            $session = $r_session->registros[0];

            if(!isset($session['adm_session_permanente'])){
                $session['adm_session_permanente'] = 'inactivo';
            }

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
