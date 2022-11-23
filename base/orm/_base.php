<?php
namespace base\orm;
use gamboamartin\errores\errores;

class _base extends modelo{

    /**
     * Se sobreescribe en el modelo en ejecucion
     * @param array $registro Registro en proceso
     * @return array
     *
     */
    protected function asigna_full_status_alta(array $registro): array
    {
        /**
         * array $keys array campos de tipo status activo inactivo
         */
        $keys = array(); //SE

        $registro = $this->asigna_status_alta(keys:$keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar status',data: $registro);
        }
        return $registro;
    }

    private function asigna_status(string $key, array $registro): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data: $key);
        }
        $registro[$key] = 'activo';
        return $registro;
    }

    protected function asigna_status_alta(array $keys, array $registro): array
    {
        foreach ($keys as $key){
            $registro = $this->status_alta(key: $key,registro:  $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar status',data: $registro);
            }
        }
        return $registro;
    }

    private function status_alta(string $key, array $registro): array
    {
        if(!isset($registro[$key])){
            $registro = $this->asigna_status(key: $key, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar status',data: $registro);
            }
        }
        return $registro;
    }

    protected function valida_alta_bd(array $registro): bool|array
    {

        $keys = array('descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        return true;
    }

}
