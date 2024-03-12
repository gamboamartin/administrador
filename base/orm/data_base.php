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
     * @version 16.224.0
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
     * POR DOCUMENTAR EN WIKI
     * Asigna datos a una fila previa basándose en un id y un modelo proporcionados.
     *
     * @param array $data El arreglo de datos base que se va a asignar a la fila.
     * @param int $id El id de la fila a la que se le asignan los datos.
     * @param modelo $modelo Modelo que contiene el registro.
     *
     * @return array Devuelve el arreglo de datos asignado en caso de éxito, en caso contrario devuelve error.
     *
     * @throws errores Si el id es menor o igual a cero se lanza un error.
     * @throws errores Si hay un error al obtener el registro previo basándose en el id proporcionado se lanza un error.
     * @throws errores Si hay un error al asignar los datos al registro se lanza un error.
     *
     * @version 28.7.0
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
     * POR DOCUMENTAR EN WIKI
     * Asigna valores a un array $data basado en un array $registro_previo.
     *
     * Esta función realiza lo siguiente:
     * 1. Verifica la existencia de las keys 'descripcion' y 'codigo' en el array $registro_previo.
     * 2. Si las keys existen, procede a asignar valores del array $registro_previo a las mismas keys en el array $data.
     * 3. En caso de error durante la validación o la asignación, llama a la función error de la clase errores y retorna el resultado.
     *
     * @param array $data  array al que se le asignarán datos.
     * @param array $registro_previo El array desde donde se obtendrán los datos.
     *
     * @return array Retorna el array $data con los nuevos valores asignados.
     * En caso de error durante el proceso, retorna el resultado de la función error de la clase errores.
     * @version 16.278.1
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
     * POR DOCUMENTAR EN WIKI
     * Ésta función asigna valores a un arreglo $data basado en una lista de llaves ($keys) y un arreglo de registros previos ($registro_previo).
     * Verifica si cada llave en $keys existe en $registro_previo. Si la llave no existe, añade el valor de la llave al arreglo $data.
     * La función regresa $data una vez se ha terminado de iterar sobre todas las $keys.
     *
     * @param array $data El arreglo al que se añaden los valores
     * @param array $keys Las llaves que se buscan en $registro_previo
     * @param array $registro_previo Las llaves y valores existentes que se van a comparar con $keys
     *
     * @return array Regresa $data con los valores agregados
     * @throws errores Si hay algún error al validar el registro previo o al asignar el dato
     * @version 16.225.0
     *
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
     * POR DOCUMENTAR EN WIKI
     * Inicializa la base de datos con campos predeterminados.
     *
     * Este método es responsable de iniciar la base de datos con los datos proporcionados.
     * Si se proporciona un id y los datos no contienen los campos 'descripcion' y/o 'codigo',
     * se asignan los valores del registro con el id proporcionado.
     *
     * @param array $data Los datos para inicializar la base de datos.
     * @param int $id El ID del registro previo.
     * @param modelo $modelo El modelo a utilizar.
     *
     * @return array Los datos de la base de datos después de la inicialización.
     *
     * @throws errores Si ocurre un error al obtener el registro previo.
     * @version 16.295.1
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