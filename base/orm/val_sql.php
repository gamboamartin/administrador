<?php
namespace base\orm;

use gamboamartin\errores\errores;
use stdClass;

class val_sql extends validaciones {

    private function campo_existe(string $campo, array $keys_ids, array $registro): array|string
    {
        $campo_r = $this->txt_valido(txt: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error key invalido', data: $campo_r, params: get_defined_vars());
        }
        $existe = $this->existe(keys_obligatorios: $keys_ids, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al verificar si existe', $existe);
        }
        return $campo_r;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $keys_checked
     * @param array $registro
     * @return bool|array
     */
    private function checked(array $keys_checked, array $registro): bool|array
    {
        foreach($keys_checked as $campo){
            $verifica = $this->verifica_chk(campo: $campo,keys_checked: $keys_checked,registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al verificar campo',$verifica);
            }
        }
        return true;
    }

    private function data_vacio(string $campo, array $keys_obligatorios, array $registro): array|string
    {
        $campo_r = $this->txt_valido($campo);
        if(errores::$error){
            return $this->error->error('Error al reasignar campo valor',$campo_r);
        }
        $existe = $this->existe(keys_obligatorios: $keys_obligatorios,registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al verificar si existe',$existe);
        }
        return trim($registro[$campo_r]);
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $keys_obligatorios
     * @param array $registro
     * @return bool|array
     */
    private function existe(array $keys_obligatorios, array $registro): bool|array
    {
        foreach($keys_obligatorios as $campo){

            $verifica = $this->verifica_existe(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al verificar si existe campo', $verifica);
            }

        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $keys_obligatorios
     * @param array $registro
     * @return bool|array
     */
    private function obligatorios(array $keys_obligatorios, array $registro): bool|array
    {
        $existe = $this->existe(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al validar campos no existe', $existe);
        }
        $vacio = $this->vacio(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al validar campo vacio', $vacio);
        }


        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $keys_ids
     * @param array $registro
     * @return bool|array
     */
    private function ids(array $keys_ids, array $registro ): bool|array
    {
        foreach($keys_ids as $campo){
            $verifica = $this->verifica_id(campo: $campo,keys_ids: $keys_ids,registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar campo ids',data:  $verifica,
                    params: get_defined_vars());
            }

        }
        return true;
    }

    private function limpia_data_tipo_campo(string $key, string $tipo_campo): array|stdClass
    {
        $key_r = $this->txt_valido(txt:$key);
        if(errores::$error){
            return $this->error->error('Error en key de tipo campo', $key);
        }
        $tipo_campo_r = $this->txt_valido(txt:$tipo_campo);
        if(errores::$error){
            return $this->error->error('Error en $tipo_campo de tipo campo', $tipo_campo);
        }
        $data = new stdClass();
        $data->key = $key_r;
        $data->tipo_campo = $tipo_campo_r;
        return $data;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $registro
     * @param array $tipo_campos
     * @return bool|array
     */
    private function tipo_campos(array $registro, array $tipo_campos): bool|array
    {
        foreach($tipo_campos as $key =>$tipo_campo){
            $valida_campos = $this->verifica_tipo_dato(key: $key,registro: $registro,tipo_campo: $tipo_campo);
            if(errores::$error){
                return $this->error->error('Error al validar campos', $valida_campos);
            }
        }


        return true;
    }

    private function txt_valido(string $txt): array|string
    {
        $txt = trim($txt);

        if($txt === ''){
            return $this->error->error(mensaje: 'Error el $txt no puede venir vacio', data: $txt,
                params: get_defined_vars());
        }
        if(is_numeric($txt)){
            return $this->error->error('Error el $txt es numero debe se un string', $txt);
        }
        return $txt;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $keys_obligatorios
     * @param array $registro
     * @return bool|array
     */
    PUBLIC function vacio(array $keys_obligatorios, array $registro): bool|array
    {
        foreach($keys_obligatorios as $campo){
            $verifica = $this->verifica_vacio(campo: $campo,keys_obligatorios: $keys_obligatorios,registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al verificar vacio',$verifica);
            }
        }
        return true;
    }

    private function verifica_chk(string $campo, array $keys_checked, array $registro): bool|array
    {
        $campo_r = $this->txt_valido($campo);
        if(errores::$error){
            return $this->error->error('Error al reasignar campo valor',$campo_r);
        }
        $existe = $this->existe(keys_obligatorios: $keys_checked,registro:  $registro);
        if(errores::$error){
            return $this->error->error('Error al validar si existe',$existe);
        }
        if((string)$registro[$campo_r] !== 'activo' && (string)$registro[$campo_r]!=='inactivo' ){
            return $this->error->error('Error $registro['.$campo_r.'] debe ser activo o inactivo',$registro);
        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $campos_obligatorios
     * @param array $registro
     * @param string $tabla
     * @param array $tipo_campos
     * @return bool|array
     */
    private function verifica_estructura(array $campos_obligatorios, array $registro, string $tabla,
                                         array $tipo_campos): bool|array
    {
        $valida_campo_obligatorio = $this->valida_campo_obligatorio(campos_obligatorios: $campos_obligatorios,
            registro: $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar campos obligatorios de registro '.$tabla,
                data: $valida_campo_obligatorio, params: get_defined_vars());
        }

        $valida_estructura = (new val_sql())->valida_estructura_campos(registro: $registro, tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar estructura ', data: $valida_estructura,
                params: get_defined_vars());
        }
        return true;
    }

    private function verifica_id(string $campo, array $keys_ids, array $registro): bool|array
    {
        $campo_r = $this->campo_existe(campo: $campo,keys_ids: $keys_ids,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar campo ids', data: $campo_r,
                params: get_defined_vars());
        }

        if(!preg_match($this->patterns['id'], $registro[$campo_r])){
            return $this->error->error('Error $registro['.$campo_r.'] es invalido',
                array($registro[$campo_r],$this->patterns['id']));
        }
        return true;
    }

    /**
     * P INT P ORDER
     * @param array $campos_obligatorios
     * @param modelo $modelo
     * @param array $no_duplicados
     * @param array $registro
     * @param string $tabla
     * @param array $tipo_campos
     * @return bool|array
     */
    public function valida_base_alta(array $campos_obligatorios, modelo $modelo, array $no_duplicados, array $registro,
                                     string $tabla, array $tipo_campos): bool|array
    {
        $valida = (new validaciones())->valida_alta_bd(registro: $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar alta ',data:  $valida, params: get_defined_vars());
        }

        $valida_estructura = $this->verifica_estructura(campos_obligatorios: $campos_obligatorios,
            registro: $registro,tabla: $tabla,tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar estructura ', data: $valida_estructura,
                params: get_defined_vars());
        }

        foreach($no_duplicados as $campo){
            $filtro = array();
            $key = $tabla.'.'.$campo;
            $filtro[$key] = $registro[$campo];

            $existe = $modelo->existe(filtro:$filtro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error verificar si existe duplicado',data:  $existe, params: get_defined_vars());
            }
            if($existe){
                return $this->error->error(mensaje: 'Error ya existe un registro con el campo '.$campo, data: $existe,
                    params: get_defined_vars());
            }
        }

        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion que valida la estructura de los campos de un modelo
     *
     * @param array $registro
     * @param array $tipo_campos
     * @param array $keys_checked conjunto de campos en forma checked
     * @param array $keys_ids conjunto de campos en forma de id a validar
     * @param array $keys_obligatorios conjunto de campos obligatorios a validar
     * @return array|bool $this->registro
     * @example
     *     $valida_estructura = $this->valida_estructura_campos();
     *
     * @uses modelos->alta_bd
     * @uses producto->asigna_data_producto_factor
     * @internal  $this->valida_pattern_campo($key,$tipo_campo);
     */
    public function valida_estructura_campos(array $registro, array $tipo_campos, array $keys_checked = array(),
                                             array $keys_ids = array(), array $keys_obligatorios = array()): array|bool
    {


        $v_tipo_campos = $this->tipo_campos(registro:$registro, tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo de campo', data: $v_tipo_campos,
                params: get_defined_vars());
        }
        $v_obligatorios = $this->obligatorios(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al validar tipo de campo', $v_obligatorios);
        }
        $v_ids = $this->ids(keys_ids: $keys_ids,registro:  $registro);
        if(errores::$error){
            return $this->error->error('Error al validar id', $v_ids);
        }
        $v_checked = $this->checked(keys_checked: $keys_checked,registro:  $registro);
        if(errores::$error){
            return $this->error->error('Error al validar checked', $v_checked);
        }


        return true;
    }

    private function verifica_existe(string $campo, array $registro): bool|array
    {
        $campo_r = $this->txt_valido(txt:$campo);
        if(errores::$error){
            return $this->error->error('Error al limpiar campo', $campo);
        }
        if(!isset($registro[$campo_r])){
            return $this->error->error('Error $registro['.$campo_r.'] debe existir', $registro);
        }
        return true;
    }

    private function verifica_tipo_dato(string $key, array $registro, string $tipo_campo): bool|array
    {
        $data = $this->limpia_data_tipo_campo(key: $key, tipo_campo: $tipo_campo);
        if(errores::$error){
            return $this->error->error('Error al limpiar dato', $data);
        }

        $valida_campos = $this->valida_pattern_campo(key: $key, registro:  $registro, tipo_campo: $tipo_campo);
        if(errores::$error){
            return $this->error->error('Error al validar campos', $valida_campos);
        }
        return true;
    }

    private function verifica_vacio(string $campo,array $keys_obligatorios, array $registro): bool|array
    {
        $value = $this->data_vacio(campo: $campo,keys_obligatorios: $keys_obligatorios,registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al verificar si existe',$value);
        }
        if($value === ''){
            return $this->error->error('Error $registro['.$campo.'] debe tener datos',$registro);
        }
        return true;
    }



}