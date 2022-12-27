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

    private function filtro_menu_visible(int $adm_grupo_id): array
    {
        $filtro['adm_grupo.id'] = $adm_grupo_id;
        $filtro['adm_accion.es_lista'] = 'inactivo';
        $filtro['adm_accion.es_status'] = 'inactivo';
        $filtro['adm_accion.visible'] = 'activo';
        return $filtro;
    }

    private function filtro_menu_visible_permitido(PDO $link){
        $usuario = (new adm_usuario(link: $link))->usuario_activo();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al obtener usuario activo',data:  $usuario);
        }

        $filtro = $this->filtro_menu_visible(adm_grupo_id: $usuario['adm_grupo_id']);
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

    public function menus_visibles_permitidos(PDO $link, string $table){

        $filtro = $this->filtro_menu_visible_permitido(link: $link);
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
