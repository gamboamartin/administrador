<?php
namespace base\controller;

use gamboamartin\errores\errores;

use JetBrains\PhpStorm\Pure;


class filtros{
    private errores $error;

    #[Pure] public function __construct(){
        $this->error = new errores();

    }

    private function asigna_filtro(string $campo, array $filtro, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $key_get = $this->key_get(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar key',data: $key_get);
        }

        $filtro = $this->asigna_filtro_existe(campo: $campo,filtro: $filtro,key_get: $key_get,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro);
        }

        return $filtro;
    }

    private function asigna_filtro_existe(string $campo, array $filtro, string $key_get, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        if(isset($_GET[$key_get])){
            $filtro = $this->asigna_key_filter(campo: $campo,filtro: $filtro,key_get: $key_get,tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro);
            }
        }
        $filtro[$tabla.'.status'] = 'activo';
        if(isset($_GET['no_valida_status'])){
            unset($filtro[$tabla.'.status']);
        }
        if(isset($_GET['todos'])){
            $filtro = array();
        }

        return $filtro;
    }

    /**
     * @param array $keys Keys a verificar para asignacion de filtros via GET
     * @return array
     * @version 1.117.28
     * @example
     *      $keys['tabla'] = array('id','descripcion');
     *      $filtro = $ctl->asigna_filtro_get(keys:$keys);
     *      print_r($filtro);
     *      //filtro[tabla.id] = $_GET['tabla_id']
     */
    public function asigna_filtro_get(array $keys): array
    {

        $filtro = array();
        foreach ($keys as $tabla=>$campos){
            if(!is_array($campos)){
                return $this->error->error(mensaje: 'Error los campos deben ser un array', data: $campos);
            }
            foreach ($campos as $campo) {

                $valida = $this->valida_data_filtro(campo: $campo, tabla: $tabla);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
                }
                $filtro = $this->asigna_filtro(campo: $campo, filtro: $filtro, tabla: $tabla);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
                }
            }
        }
        return $filtro;
    }

    private function asigna_key_filter(string $campo, array $filtro, string $key_get, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $key_filter = $this->key_filter(campo:$campo,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data: $key_filter);
        }
        $filtro[$key_filter] = $_GET[$key_get];
        return $filtro;
    }

    /**
     *
     * @param controler $controler
     * @param array $filtros
     * @return array
     */
    public function filtra(controler $controler, array $filtros): array
    {
        $r_modelo = $controler->modelo->filtro_and(filtro: $filtros,filtro_especial: array());
        if(errores::$error){
            return $controler->errores->error(mensaje: 'Error al obtener datos',data: $r_modelo);
        }
        return $r_modelo;
    }

    /**
     * Integra un filtro de get para get_out
     * @param string $campo Campo a integrar filtro
     * @param string $tabla Entidad en ejecucion
     * @return string|array
     * @version 2.79.6
     */
    private function key_filter(string $campo, string $tabla): string|array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $tabla = trim($tabla);
        $campo = trim($campo);
        return $tabla.'.'.$campo;
    }

    private function key_get(string $campo, string $tabla): string|array
    {
        $valida = $this->valida_data_filtro(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        return $tabla.'_'.$campo;
    }

    /**
     * Valida los elementos de un filtro
     * @param string $campo Campo de filtro
     * @param string $tabla Tabla de filtro
     * @return bool|array
     * @version 2.41.4
     */
    private function valida_data_filtro(string $campo, string $tabla): bool|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo esta vacio',data: $campo);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $tabla esta vacio',data: $tabla);
        }
        if(is_numeric($tabla)){
            return $this->error->error(mensaje: 'Error $tabla debe ser un texto no un numero',data: $tabla);
        }
        return true;
    }

}
