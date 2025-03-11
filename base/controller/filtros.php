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
     * Filtra los registros de un modelo utilizando los criterios proporcionados.
     *
     * Este método ejecuta la función `filtro_and` en el modelo del controlador, aplicando los filtros
     * especificados en `$filtros`. Si ocurre un error durante la consulta, se captura y se devuelve
     * un objeto de error. En caso de éxito, retorna el conjunto de registros filtrados.
     *
     * ### Funcionamiento:
     * 1. Se invoca el método `filtro_and` del modelo asociado al controlador, pasando los filtros.
     * 2. Si hay un error en la ejecución de la consulta, se devuelve un error detallado.
     * 3. Si la consulta es exitosa, se retorna el conjunto de registros filtrados.
     *
     * ### Parámetros:
     * @param controler $controler Instancia del controlador que contiene el modelo a consultar.
     * @param array $filtros Filtros a aplicar en la consulta. Debe contener pares clave-valor donde la clave es
     *                       el nombre del campo y el valor es el criterio de filtrado.
     *
     * ### Retorno:
     * @return array|stdClass Devuelve un array con los registros filtrados o un objeto `stdClass` en caso de éxito.
     *                        Si ocurre un error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso:
     * ```php
     * // Suponiendo que tenemos un controlador con un modelo de usuarios:
     * $controlador = new UsuarioControlador();
     *
     * // Filtros a aplicar: buscamos usuarios con rol "admin"
     * $filtros = [
     *     'usuarios.rol' => 'admin'
     * ];
     *
     * // Llamada al método filtra
     * $resultado = $controlador->filtros->filtra($controlador, $filtros);
     *
     * // Ejemplo de salida esperada (si hay éxito):
     * // [
     * //     ["id" => 1, "nombre" => "Juan Pérez", "rol" => "admin"],
     * //     ["id" => 2, "nombre" => "Ana López", "rol" => "admin"]
     * // ]
     * ```
     *
     * ### Ejemplo de salida en caso de error:
     * ```php
     * // Si hay un error en la consulta:
     * [
     *     'error' => 1,
     *     'mensaje' => 'Error al obtener datos',
     *     'data' => 'Detalles del error en la consulta'
     * ]
     * ```
     */
    final public function filtra(controler $controler, array $filtros): array|stdClass
    {
        // Ejecutar filtro en el modelo asociado al controlador
        $r_modelo = $controler->modelo->filtro_and(filtro: $filtros);

        // Manejo de errores
        if(errores::$error){
            return $controler->errores->error(
                mensaje: 'Error al obtener datos',
                data: $r_modelo
            );
        }

        // Retornar resultado filtrado
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
