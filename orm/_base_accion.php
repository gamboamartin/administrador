<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class _base_accion{
    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    private function adm_secciones_permitidas(array $adm_menu, PDO $link){

        $keys = array('adm_menu_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $adm_menu);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_menu',data:  $valida);
        }

        $adm_secciones = $this->menus_visibles_permitidos(link:$link, table: 'adm_seccion',
            id: $adm_menu['adm_menu_id'], table_filtro: 'adm_menu');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener menus',data:  $adm_secciones);
        }

        $adm_secciones = $this->asigna_acciones_a_seccion(adm_secciones: $adm_secciones,link:  $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar accion a seccion',data:  $adm_secciones);
        }
        return $adm_secciones;
    }

    /**
     * Asigna un registro de accion a una seccion
     * @param array $adm_seccion Registro de seccion precargada
     * @param array $adm_secciones Conjunto de secciones a inicializar
     * @param int $key_seccion Key de array de adm_secciones
     * @param PDO $link Conexion a la base de datos
     * @return array
     * @version 6.10.0
     */
    private function asigna_accion_a_seccion(array $adm_seccion, array $adm_secciones, int $key_seccion, PDO $link): array
    {
        $keys = array('adm_seccion_id');
        $valida = (new validacion())->valida_ids(keys: $keys,registro:  $adm_seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_seccion',data:  $valida);
        }

        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no existe $_SECCION activa',data: array());
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error no existe session usuario id',data: $_SESSION);
        }

        if((int)$_SESSION['usuario_id'] < 0){
            return  $this->error->error(mensaje: 'Error el usuario_id debe ser mayor a 0 ',
                data: $_SESSION['usuario_id']);
        }

        $adm_acciones = $this->menus_visibles_permitidos(link:$link, table: 'adm_accion',
            id: $adm_seccion['adm_seccion_id'], table_filtro: 'adm_seccion');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener menus',data:  $adm_acciones);
        }

        $adm_secciones[$key_seccion]['adm_acciones'] = $adm_acciones;
        return $adm_secciones;
    }

    private function asigna_acciones_a_seccion(array $adm_secciones, PDO $link){
        foreach ($adm_secciones as $key_seccion=>$adm_seccion){
            $adm_secciones = $this->asigna_accion_a_seccion(adm_seccion: $adm_seccion,
                adm_secciones:  $adm_secciones, key_seccion: $key_seccion, link: $link);

            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar accion a seccion',data:  $adm_secciones);
            }
        }
        return $adm_secciones;
    }

    private function asigna_seccion_a_menu(array $adm_menu, array $adm_menus, int $key_menu, PDO $link){
        $keys = array('adm_menu_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $adm_menu);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_menu',data:  $valida);
        }

        $adm_secciones = $this->adm_secciones_permitidas(adm_menu: $adm_menu,link:  $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar accion a seccion',data:  $adm_secciones);
        }

        $adm_menus[$key_menu]['adm_secciones'] = $adm_secciones;
        return $adm_menus;
    }

    public function asigna_secciones_a_menu(array $adm_menus, PDO $link){
        foreach ($adm_menus as $key_menu=>$adm_menu){

            $keys = array('adm_menu_id');
            $valida = $this->validacion->valida_ids(keys: $keys,registro:  $adm_menu);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar adm_menu',data:  $valida);
            }

            $adm_menus = $this->asigna_seccion_a_menu(adm_menu: $adm_menu,adm_menus:  $adm_menus,
                key_menu:  $key_menu, link: $link);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener menus',data:  $adm_menus);
            }
        }
        return $adm_menus;

    }

    private function css_registro(int $id, modelo $modelo, array $registro): array
    {
        $registro_previo = $modelo->registro(registro_id: $id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener accion',data: $registro_previo);
        }

        $registro = $this->init_css(registro: $registro, registro_previo: $registro_previo, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css',data: $registro);
        }
        return $registro;
    }

    /**
     * Genera un filtro para obtener los menus con permisos del usuario en ejecucion
     * el puede ser utilizado para integrar funciones de get data
     * @param int $adm_grupo_id El grupo del usuario ej ejecucion
     * @param int $id Id del parent o modelo parent ejemplo para seccion debe ser el id del menu
     * @param string $table_filtro Tabla del parent o modelo parent ejemplo para adm_seccion debe ser el adm_menu
     * @return array
     * @version 6.5.0
     */
    private function filtro_menu_visible(int $adm_grupo_id, int $id, string $table_filtro): array
    {
        if($adm_grupo_id <= 0){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0',data:  $adm_grupo_id);
        }
        $filtro['adm_grupo.id'] = $adm_grupo_id;
        $filtro['adm_accion.es_lista'] = 'inactivo';
        $filtro['adm_accion.es_status'] = 'inactivo';
        $filtro['adm_accion.visible'] = 'activo';
        if($id > 0){
            $table_filtro = trim($table_filtro);
            if($table_filtro === ''){
                return $this->error->error(
                    mensaje: 'Error si id es mayor a 0 entonces table_filtro no puede venir vacio',data:  $table_filtro);
            }
            $filtro[$table_filtro.'.id'] = $id;
        }
        return $filtro;
    }

    /**
     * Genera un menu permitido
     * @param PDO $link Conexion a la base de datos
     * @param int $id Identificador del menu o seccion
     * @param string $table_filtro Tabla para filtro
     * @return array
     * @version 6.7.0
     */
    private function filtro_menu_visible_permitido(PDO $link, int $id = -1, string $table_filtro = ''): array
    {
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no existe $_SECCION activa',data: array());
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error no existe session usuario id',data: $_SESSION);
        }

        if((int)$_SESSION['usuario_id'] < 0){
            return  $this->error->error(mensaje: 'Error el usuario_id debe ser mayor a 0 ',
                data: $_SESSION['usuario_id']);
        }

        $usuario = (new adm_usuario(link: $link))->usuario_activo();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al obtener usuario activo',data:  $usuario);
        }

        $filtro = $this->filtro_menu_visible(adm_grupo_id: $usuario['adm_grupo_id'], id: $id,
            table_filtro: $table_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro',data:  $filtro);
        }
        return $filtro;
    }



    /**
     * Inicializa el campo para css de accion
     * @param array $registro Registro en proceso
     * @param stdClass $registro_previo Registro previo a la actualizacion
     * @param string $tabla Tabla del modelo en ejecucion
     * @return array
     * @version 2.93.9
     */
    private function init_css(array $registro, stdClass $registro_previo, string $tabla): array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data: $tabla);
        }
        $key = $tabla.'_css';
        $keys = array($key);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro previo',data: $valida);
        }
        if(!isset($registro['css'])){


            $registro['css'] = $registro_previo->$key;
        }
        return $registro;
    }

    /**
     * Obtiene los menus permitidos del usuario en session
     * @param PDO $link Conexion a la base de datos
     * @param string $table Tabla o modelo de ejecucion
     * @param int $id Identificador parent del menu o seccion
     * @param string $table_filtro Tabla parent del modelo
     * @return array
     * @version 6.9.0
     */
    public function menus_visibles_permitidos(PDO $link, string $table, int $id = -1, string $table_filtro = ''): array
    {
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no existe $_SECCION activa',data: array());
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error no existe session usuario id',data: $_SESSION);
        }

        if((int)$_SESSION['usuario_id'] < 0){
            return  $this->error->error(mensaje: 'Error el usuario_id debe ser mayor a 0 ',
                data: $_SESSION['usuario_id']);
        }
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacio',data: $table);
        }

        $filtro = $this->filtro_menu_visible_permitido(link: $link,id: $id, table_filtro : $table_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro',data:  $filtro);
        }

        $group_by = array($table.'.id');

        $columnas_by_table = array($table);
        $resultado = (new adm_accion_grupo($link))->filtro_and(
            columnas_by_table: $columnas_by_table, filtro: $filtro, group_by: $group_by);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar menus visibles permitidos',data:  $resultado);
        }

        return $resultado->registros;

    }

    public function registro_validado_css(int $id, modelo $modelo, array $registro): array
    {
        $registro = $this->css_registro(id: $id, modelo: $modelo,registro: $registro );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css',data: $registro);
        }

        $keys = array('css');
        $valida = $this->validacion->valida_estilos_css(keys: $keys,row: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        return $registro;
    }

    public function valida_alta(array $registro){
        $keys = array('css');
        $valida = $this->validacion->valida_estilos_css(keys: $keys,row: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }
        $valida = $this->valida_icono(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }
        return true;
    }

    private function valida_icono(array $registro){
        if(isset($registro['muestra_icono_btn'])){
            if($registro['muestra_icono_btn'] === 'activo'){
                $keys_val = array('icono');
                $valida = $this->validacion->valida_existencia_keys(keys: $keys_val, registro: $registro);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
                }
            }
        }
        return true;
    }

    public function valida_icono_upd(int $id, modelo $modelo){
        $registro_actualizado = $modelo->registro(registro_id: $id, columnas_en_bruto: true, retorno_obj: true);
        if(errores::$error){
            return $this->error->error('Error al obtener registro', $registro_actualizado);
        }

        if($registro_actualizado->muestra_icono_btn === 'activo'){
            if(trim($registro_actualizado->icono) === ''){
                return $this->error->error(
                    mensaje: 'Error si muestra_icono_btn es activo entonces icono no puede venir vacio',
                    data: $registro_actualizado);
            }
        }
        return true;
    }
}
