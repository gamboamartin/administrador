<?php
namespace models;

use base\orm\modelo;
use gamboamartin\errores\errores;


use PDO;

class usuario extends modelo{ //PRUEBAS en proceso
    /**
     * DEBUG INI
     * usuario constructor.
     * @param PDO $link Conexion a la BD
     */
    public function __construct(PDO $link){
        
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'grupo'=>$tabla);
        parent::__construct(link: $link,tabla: $tabla,columnas: $columnas);
    }


    public function alta_usuario(array $grupo, string $es_prospectador, string $es_cerrador){
        $data_usuario = $this->asigna_data_usuario($grupo,$es_prospectador,$es_cerrador);
        if(errores::$error){
            return $this->error->error('Error al asignar datos de usuario',$data_usuario);
        }

        $r_usuario_alta_bd = $this->alta_bd();
        if(isset($r_usuario_alta_bd['error'])){
            return $this->error->error('Error al insertar usuario',$r_usuario_alta_bd);
        }
        return $r_usuario_alta_bd['registro_id'];
    }


    /**
     * 
     * @param array $grupo
     * @param string $es_prospectador
     * @param string $es_cerrador
     * @return array
     */
    public function asigna_data_usuario(array $grupo, string $es_prospectador, string $es_cerrador): array
    {
        $this->registro['user'] = $_POST['usuario'];
        $this->registro['password'] = $_POST['password'];
        $this->registro['email'] = $_POST['email'];
        $this->registro['grupo_id'] = $grupo['grupo_id'];
        $this->registro['status'] = 'activo';
        $this->registro['acceso_total_cliente'] = 'inactivo';
        $this->registro['es_prospectador'] = $es_prospectador;
        $this->registro['es_cerrador'] = $es_cerrador;
        $this->registro['es_responsable_compra'] = 'inactivo';
        $this->registro['acceso_total_ubicaciones'] = 'activo';

        return $this->registro;
    }

    /**
     * PHPUNIT
     * @return array
     */
    public function usuario_activo():array{ //FIN PROT
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error('Error no existe session usuario id',$_SESSION);
        }
        $this->registro_id = $_SESSION['usuario_id'];
        $usuario = $this->obten_data();
        if(errores::$error){
            return $this->error->error('Error al obtener usuario activo',$usuario);
        }
        return $usuario;
    }

    /**
     * 
     * @param string $tabla
     * @return array
     */
    public function filtro_seguridad(string $tabla):array{ //FIN
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
        if($usuario['grupo_root']==='activo') {
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

    /**
     * 
     * @param array $filtro
     * @return array
     */
    public function data_grupo(array $filtro): array
    {
        $grupo_modelo = new grupo($this->link);
        $r_grupo = $grupo_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener grupo',$r_grupo);
        }
        if((int)$r_grupo['n_registros'] === 0){
            return $this->error->error('Error al obtener grupo no existe',$r_grupo);
        }
        if((int)$r_grupo['n_registros'] > 1){
            return $this->error->error('Error al obtener grupo inconsistencia existe mas de uno',$r_grupo);
        }
        return $r_grupo['registros'][0];
    }



    /**
     * TEST
     * @param int $usuario_id
     * @param PDO $link
     * @return array
     */
    public static function usuario(int $usuario_id, PDO $link):array{
       if($usuario_id <=0){
           return (new errores())->error('Error usuario_id debe ser mayor a 0',$usuario_id);
       }
        $usuario_modelo = new usuario($link);
        $usuario_modelo->registro_id = $usuario_id;
        $usuario = $usuario_modelo->obten_data();
        if(errores::$error){
            return (new errores())->error('Error al obtener usuario',$usuario);
        }

        return $usuario;
    }



    public function valida_usuario_password(string $password, string $usuario, string $accion_header = '',
                                            string $seccion_header = ''){
        if($usuario === ''){
            return $this->error->error(mensaje: 'El usuario no puede ir vacio',data: $usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }
        if($password === ''){
            return $this->error->error(mensaje: 'El $password no puede ir vacio',data: $password,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }

        $filtro['usuario.user'] = $usuario;
        $filtro['usuario.password'] = $password;
        $filtro['usuario.status'] = 'activo';
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