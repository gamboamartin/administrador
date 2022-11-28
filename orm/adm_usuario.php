<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class adm_usuario extends modelo{ //PRUEBAS en proceso
    /**
     * DEBUG INI
     * usuario constructor.
     * @param PDO $link Conexion a la BD
     */
    public function __construct(PDO $link, array $childrens = array()){
        
        $tabla = 'adm_usuario';
        $columnas = array($tabla=>false,'adm_grupo'=>$tabla);

        $campos_obligatorios = array('user','password','email','adm_grupo_id','telefono');


        $childrens['adm_bitacora'] = "gamboamartin\\administrador\\models";
        $childrens['adm_session'] = "gamboamartin\\administrador\\models";
        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;
    }


    /**
     * Valida que el grupo que va a filtrar exista en la base de datos. En caso de que
     * halla un error en la búsqueda, que no exista o sea inconsistente la informacion. Mandará un error.
     *
     * @param array $filtro Verifica y valida los datos que se le ingresen
     * @return array
     *
     * @function $grupo_modelo = new adm_grupo($adm_usuario->link); Obtiene los datos de
     * un grupo por medio del enlace a una base de datos
     */
    public function data_grupo(array $filtro): array
    {
        $grupo_modelo = new adm_grupo($this->link);
        $r_grupo = $grupo_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener grupo',$r_grupo);
        }
        if((int)$r_grupo['n_registros'] === 0){
            return $this->error->error('Error al obtener grupo no existe',$r_grupo);
        }
        if((int)$r_grupo->n_registros > 1){
            return $this->error->error('Error al obtener grupo inconsistencia existe mas de uno',$r_grupo);
        }
        return $r_grupo->registros[0];
    }

    /**
     * Genera un filtro en forma de array para integrarlo a la seguridad de datos. En caso de error al
     * validar la SESSION o al obtener al usuario activo lanzará un error.
     *
     * @return array
     *
     * @function $valida = $adm_usuario->validacion->valida_ids(keys: $keys, registro: $_SESSION);
     * Recibe los resultados de la validacion del usuario en base a la session y la llave.
     * @version 1.141.31
     */
    public function filtro_seguridad():array{
        $keys = array('usuario_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar SESSION',data: $valida);
        }

        $usuario = self::usuario(usuario_id: $_SESSION['usuario_id'], link: $this->link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuario activo',data: $usuario);
        }
        $filtro = array();
        $aplica_seg = true;
        if($usuario['adm_grupo_root']==='activo') {
            $aplica_seg = false;
        }


        if($aplica_seg){
            $filtro['usuario_permitido_id']['campo'] = 'usuario_permitido_id';
            $filtro['usuario_permitido_id']['value'] = $_SESSION['usuario_id'];
            $filtro['usuario_permitido_id']['es_sq'] = true;
            $filtro['usuario_permitido_id']['operador'] = 'AND';
        }


        return $filtro;
    }

    public function tengo_permiso(string $adm_accion, string $adm_seccion): array|bool
    {
        $adm_usuario_id = -1;
        if(isset($_SESSION['usuario_id'])) {
            $adm_usuario_id = $_SESSION['usuario_id'];
        }
        $existe = false;
        if((int)$adm_usuario_id > 0) {

            $adm_grupo_id = -1;
            if(isset($_SESSION['grupo_id'])){
                $adm_grupo_id = (int)$_SESSION['grupo_id'];
            }

            if($adm_grupo_id > 0) {
                if (isset($_SESSION['permite'][$adm_grupo_id][$adm_seccion][$adm_accion])) {
                    if ((int)$_SESSION['permite'][$adm_grupo_id][$adm_seccion][$adm_accion] === 1) {
                        $existe = true;
                    }
                } else {
                    $filtro['adm_grupo.id'] = $adm_grupo_id;
                    $filtro['adm_accion.descripcion'] = $adm_accion;
                    $filtro['adm_grupo.status'] = 'activo';
                    $filtro['adm_accion.status'] = 'activo';
                    $filtro['adm_seccion.descripcion'] = $adm_seccion;
                    $filtro['adm_seccion.status'] = 'activo';

                    $existe = (new adm_accion_grupo(link: $this->link))->existe(filtro: $filtro);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al validar si existe', data: $existe);
                    }
                    $val_session = 0;
                    if ($existe) {
                        $val_session = 1;
                    }
                    $_SESSION['permite'][$adm_grupo_id][$adm_seccion][$adm_accion] = $val_session;
                }
            }
        }
        return $existe;

    }

    /**
     * Obtiene un usuario por id
     * @version 1.138.31
     * @param int $usuario_id Usuario a obtener
     * @param PDO $link Conexion a base de datos
     * @return array
     */
    public static function usuario(int $usuario_id, PDO $link):array{
       if($usuario_id <=0){
           return (new errores())->error('Error usuario_id debe ser mayor a 0',$usuario_id);
       }
        $usuario_modelo = new adm_usuario($link);
        $usuario_modelo->registro_id = $usuario_id;
        $usuario = $usuario_modelo->obten_data();
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener usuario',data: $usuario);
        }

        return $usuario;
    }

    /**
     * Obtiene un usuario activo basado en la session
     * @version 1.209.37
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-27
     * @return array
     */
    public function usuario_activo():array{
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error no existe session usuario id',data: $_SESSION);
        }

        if((int)$_SESSION['usuario_id'] < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0 en el modelo '.$this->tabla,
                data: $_SESSION['usuario_id']);
        }

        $this->registro_id = $_SESSION['usuario_id'];
        $usuario = $this->obten_data();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuario activo',data: $usuario);
        }
        return $usuario;
    }

    public function usuarios_por_grupo(int $adm_grupo_id): array
    {
        if($adm_grupo_id <=0 ){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0',data: $adm_grupo_id);
        }
        $filtro['adm_grupo.id'] = $adm_grupo_id;
        $r_usuario = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuarios',data: $r_usuario);
        }
        return $r_usuario->registros;
    }

    /**
     * Valida que un usuario y un password exista
     *
     * @param string $password Contraseña a verificar
     * @param string $usuario Usuario a verificar
     * @param string $accion_header elemento para regresar a accion especifica en el controlador
     * @param string $seccion_header elemento para regresar a seccion especifica en el controlador
     * @return array
     *
     * @function $r_usuario = $adm_usuario->filtro_and(filtro: $filtro); maqueta los datos obtenidos de un
     * usuario, antes siendo revisados por un filtro.
     * @version 2.25.3
     */
    public function valida_usuario_password(string $password, string $usuario, string $accion_header = '',
                                            string $seccion_header = ''): array
    {
        if($usuario === ''){
            return $this->error->error(mensaje: 'El usuario no puede ir vacio',data: $usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }
        if($password === ''){
            return $this->error->error(mensaje: 'El $password no puede ir vacio',data: $password,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }

        $filtro['adm_usuario.user'] = $usuario;
        $filtro['adm_usuario.password'] = $password;
        $filtro['adm_usuario.status'] = 'activo';
        $r_usuario = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuario',data: $r_usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }

        if((int)$r_usuario->n_registros === 0){
            return $this->error->error(mensaje: 'Error al validar usuario y pass ',data: $usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }
        if((int)$r_usuario->n_registros > 1){
            return $this->error->error(mensaje: 'Error al validar usuario y pass ',data: $usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }
        return $r_usuario->registros[0];
	}
}