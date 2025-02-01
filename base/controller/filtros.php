<?php
namespace base\controller;

use gamboamartin\errores\errores;

use JetBrains\PhpStorm\Pure;
use stdClass;


class filtros{
    private errores $error;

    #[Pure] public function __construct(){
        $this->error = new errores();

    }

    private function asigna_filtro(string $campo, array $filtro, string $seccion, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo, seccion: $seccion,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $key_get = $this->key_get(campo: $campo,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar key',data: $key_get);
        }

        $filtro = $this->asigna_filtro_existe(campo: $campo,filtro: $filtro,key_get: $key_get,
            seccion: $seccion,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro);
        }

        return $filtro;
    }

    /**
     * @param string $campo
     * @param array $filtro
     * @param string $key_get
     * @param string $seccion Seccion de ejecucion origen
     * @param string $tabla Tabla origen GET
     * @return array
     */
    private function asigna_filtro_existe(string $campo, array $filtro, string $key_get, string $seccion, string $tabla): array
    {
        $valida = $this->valida_data_filtro(campo: $campo, seccion: $seccion, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        if(isset($_GET[$key_get])){
            $filtro = $this->asigna_key_filter(campo: $campo,filtro: $filtro,key_get: $key_get,tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro);
            }
        }
        $filtro[$seccion.'.status'] = 'activo';
        if(isset($_GET['no_valida_status'])){
            unset($filtro[$seccion.'.status']);
        }
        if(isset($_GET['todos'])){
            $filtro = array();
        }

        return $filtro;
    }

    /**
     * @param array $keys Keys a verificar para asignacion de filtros via GET
     * @param string $seccion
     * @return array
     * @example
     *      $keys['tabla'] = array('id','descripcion');
     *      $filtro = $ctl->asigna_filtro_get(keys:$keys);
     *      print_r($filtro);
     *      //filtro[tabla.id] = $_GET['tabla_id']
     */
    final public function asigna_filtro_get(array $keys, string $seccion): array
    {

        $filtro = array();
        foreach ($keys as $tabla=>$campos){
            if(!is_array($campos)){
                return $this->error->error(mensaje: 'Error los campos deben ser un array', data: $campos);
            }
            foreach ($campos as $campo) {

                $valida = $this->valida_data_filtro(campo: $campo, seccion: $seccion, tabla: $tabla);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
                }
                $filtro = $this->asigna_filtro(campo: $campo, filtro: $filtro, seccion: $seccion, tabla: $tabla);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
                }
            }
        }
        return $filtro;
    }

    private function asigna_key_filter(string $campo, array $filtro, string $key_get, string $tabla): array
    {

        $valida = $this->valida_data_filtro_base(campo: $campo,tabla:  $tabla);
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
     * REG
     * Ejecuta el filtrado de registros utilizando los filtros especificados.
     *
     * Este método invoca el método `filtro_and()` del modelo asociado al controlador para aplicar los filtros
     * proporcionados y obtener los registros que cumplan con dichos criterios. En caso de producirse un error durante
     * el filtrado, se retorna la estructura de error utilizando el manejador de errores del controlador.
     *
     * @param controler $controler Instancia del controlador desde el cual se llama a esta función. Este parámetro se utiliza para acceder
     *                             al modelo asociado (a través de `$controler->modelo`) y al manejador de errores (a través de `$controler->errores`).
     *                             <br><b>Ejemplo:</b> `new controler($link)`
     * @param array $filtros Array asociativo que contiene los filtros a aplicar en la consulta. Cada elemento del array debe estar
     *                       estructurado según lo esperado por el método `filtro_and()` del modelo.
     *                       <br><b>Ejemplo:</b>
     *                       <pre>
     *                       [
     *                           'usuarios.status' => 'activo',
     *                           'usuarios.edad'   => 30
     *                       ]
     *                       </pre>
     *
     * @return array|stdClass Devuelve un objeto o un array (dependiendo de la implementación interna del modelo) que contiene
     *                        los registros filtrados. En caso de error, retorna un array con la información del error generado.
     *
     * @throws errores Lanza un error si falla la ejecución del filtrado.
     *
     * @example Ejemplo 1: Filtrado básico de registros
     * <pre>
     * // Supongamos que se tiene un controlador $ctl cuyo modelo tiene registros de usuarios.
     * // Se desea obtener únicamente los usuarios activos.
     * $filtros = [
     *     'usuarios.status' => 'activo'
     * ];
     *
     * // Se llama al método filtra pasando el controlador y los filtros:
     * $resultado = $ctl->filtra($ctl, $filtros);
     *
     * // Si la operación es exitosa, $resultado contendrá los registros de usuarios activos.
     * // Por ejemplo:
     * // stdClass {
     * //     "n_registros": 5,
     * //     "registros": [
     * //         { "usuarios.id": 1, "usuarios.nombre": "Juan", ... },
     * //         { "usuarios.id": 2, "usuarios.nombre": "María", ... },
     * //         ...
     * //     ]
     * // }
     * </pre>
     *
     * @example Ejemplo 2: Filtrado con múltiples criterios
     * <pre>
     * // Se desea filtrar los usuarios que estén activos y tengan al menos 25 años.
     * $filtros = [
     *     'usuarios.status' => 'activo',
     *     'usuarios.edad'   => 25
     * ];
     *
     * $resultado = $ctl->filtra($ctl, $filtros);
     *
     * // El método aplicará ambos filtros y retornará los usuarios que cumplan ambas condiciones.
     * </pre>
     *
     * @example Ejemplo 3: Manejo de error en el filtrado
     * <pre>
     * // Si se pasa un filtro con un formato incorrecto, por ejemplo, un valor nulo o mal estructurado:
     * $filtros = [
     *     'usuarios.status' => null
     * ];
     *
     * $resultado = $ctl->filtra($ctl, $filtros);
     *
     * // En caso de error, se retornará un array con la siguiente estructura:
     * // [
     * //     'error'     => 1,
     * //     'mensaje'   => 'Error al obtener datos',
     * //     'data'      => (detalles del error),
     * //     'es_final'  => true
     * // ]
     * </pre>
     */
    final public function filtra(controler $controler, array $filtros): array|stdClass
    {
        $r_modelo = $controler->modelo->filtro_and(filtro: $filtros);
        if(errores::$error){
            return $controler->errores->error(
                mensaje: 'Error al obtener datos',
                data: $r_modelo
            );
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
        $valida = $this->valida_data_filtro_base(campo: $campo,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        $tabla = trim($tabla);
        $campo = trim($campo);
        return $tabla.'.'.$campo;
    }

    private function key_get(string $campo, string $tabla): string|array
    {
        $valida = $this->valida_data_filtro_base(campo: $campo,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        return $tabla.'_'.$campo;
    }

    /**
     * Valida los elementos de un filtro
     * @param string $campo Campo de filtro
     * @param string $seccion Seccion en ejecucion
     * @param string $tabla Tabla de filtro proveniente de GET
     * @return bool|array
     */
    private function valida_data_filtro(string $campo, string $seccion, string $tabla): bool|array
    {

        $valida = $this->valida_data_filtro_base(campo: $campo,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacio',data: $seccion);
        }
        if(is_numeric($seccion)){
            return $this->error->error(mensaje: 'Error seccion debe ser un texto no un numero',data: $seccion);
        }

        return true;
    }

    private function valida_data_filtro_base(string $campo, string $tabla): bool|array
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
