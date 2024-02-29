<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;



class data_base{

    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método verifica si un elemento específico está presente en un arreglo
     * proporcionado por el usuario ($data). Si el elemento no existe,
     * se asignará el valor correspondiente a partir de otro arreglo ($registro_previo).
     *
     * @param array $data – Datos proporcionados por el usuario
     * @param string $key – Llave para verificar en $data
     * @param array $registro_previo – Arreglo con datos originales, para copiar en caso de que $key no existe en $data
     *
     * @return array Retorna un arreglo modificado con elementos añadidos, si necesario
     * @version
     */
    private function asigna_data_no_existe(array $data, string $key, array $registro_previo): array
    {
        $valida = $this->valida_init_data(key: $key,registro_previo:  $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro previo',data: $valida);
        }

        if(!isset($data[$key])){
            $data[$key] = $registro_previo[$key];
        }
        return $data;
    }

    /**
     * Asigna los datos de un registro previo
     * @param array $data Datos de registro en proceso
     * @param int $id Identificador en proceso
     * @param modelo $modelo Modelo en ejecucion
     * @return array
     */
    private function asigna_data_row_previo(array $data, int $id, modelo $modelo): array
    {
        if($id<=0){
            return $this->error->error(mensaje: 'Error el id debe ser mayor a 0',data: $id);
        }
        $registro_previo = $modelo->registro(registro_id: $id, columnas_en_bruto: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro previo',data: $registro_previo);
        }
        $data = $this->asigna_datas_base(data: $data,registro_previo:  $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asigna data',data: $data);
        }

        return $data;
    }

    /**
     * Asigna datos para una base inicial
     * @param array $data Registro en proceso
     * @param array $registro_previo Registro cargado anteriormente
     * @return array
     */
    private function asigna_datas_base(array $data, array $registro_previo): array
    {
        $keys = array('descripcion','codigo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro previo',data: $valida);
        }

        $data = $this->asigna_datas_no_existe(data: $data,keys:  $keys,registro_previo:  $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asigna data',data: $data);
        }
        return $data;
    }

    /**
     * Asigna datos faltantes default
     * @param array $data Registro en proceso
     * @param array $keys Keys de asignacion
     * @param array $registro_previo Registro previamente cargado
     * @return array
     */
    private function asigna_datas_no_existe(array $data, array $keys, array $registro_previo): array
    {
        foreach ($keys as $key){

            $valida = $this->valida_init_data(key: $key,registro_previo:  $registro_previo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar registro previo',data: $valida);
            }

            $data = $this->asigna_data_no_existe(data: $data,key:  $key,registro_previo:  $registro_previo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asigna data',data: $data);
            }
        }
        return $data;
    }

    /**
     * Inicializa campos
     * @param array $data registro en proceso
     * @param int $id Identificador
     * @param modelo $modelo Modelo en ejecucion
     * @return array
     */
    final public function init_data_base(array $data, int $id, modelo $modelo): array
    {

        if((!isset($data['descripcion']) || !isset($data['codigo'])) && $id > 0){

            $data = $this->asigna_data_row_previo(data:$data,id :$id, modelo: $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener registro previo',data: $data);
            }
        }
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Función valida_init_data
     *
     * Esta función examina una clave y un registro previo para determinar si pueden ser utilizados para secuencias
     * de operaciones en la base de datos.
     *
     * @param  mixed $key - Una clave que se quiere examinar. Debe ser una cadena de caracteres.
     * @param  array $registro_previo - Un arreglo que representa el registro anterior, tal como sería almacenado en la base de datos.
     *
     * @return true|array devuelve true si las validaciones son correctas, en caso contrario retorna un arreglo representando un mensaje de error.
     *
     * @throws errores si la clave no es una cadena de texto.
     * @throws errores si la clave está vacía.
     * @throws errores si hay un error al validar el registro previo.
     * @version 16.221.0
     */
    private function valida_init_data(mixed $key, array $registro_previo): true|array
    {
        if(!is_string($key)){
            return $this->error->error(mensaje: 'Error key debe ser un string',data: $key);
        }
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data: $key);
        }

        $keys = array($key);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro previo',data: $valida);
        }
        return true;
    }


}