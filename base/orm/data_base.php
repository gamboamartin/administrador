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
     * Asigna un valor para registro precargado
     * @param array $data Registro en proceso
     * @param string $key campo a integrar
     * @param array $registro_previo Registro precargado
     * @return array
     * @version 2.53.6
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
     * @version 2.65.6
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
     * @version 2.27.6
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
     * @version 2.56.6
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

    public function init_data_base(array $data, int $id, modelo $modelo): array
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
     * Valida que los elementos para incializar un campo sean validos
     * @param mixed $key key a validar
     * @param array $registro_previo Registro con datos para asignacion
     * @return bool|array
     * @version 2.52.5
     */
    private function valida_init_data(mixed $key, array $registro_previo): bool|array
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