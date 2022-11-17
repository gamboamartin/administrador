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

    private function asigna_data_row_previo(array $data, int $id, modelo $modelo): array
    {
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

    private function asigna_datas_base(array $data, array $registro_previo): array
    {
        $keys = array('descripcion','codigo');

        $data = $this->asigna_datas_no_existe(data: $data,keys:  $keys,registro_previo:  $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asigna data',data: $data);
        }
        return $data;
    }

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