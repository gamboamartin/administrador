<?php
namespace base\orm;

use gamboamartin\errores\errores;
use stdClass;

class val_sql extends validaciones {

    /**
     * Verifica si un campo existe en una transaccion
     * @param string $campo Campo a validar si existe
     * @param array $keys_ids Keys a validar
     * @param array $registro Registro a verificar
     * @return array|string
     * @version 1.321.41
     */
    private function campo_existe(string $campo, array $keys_ids, array $registro): array|string
    {
        $campo_r = $this->txt_valido(txt: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error key invalido', data: $campo_r);
        }
        $existe = $this->existe(keys_obligatorios: $keys_ids, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe', data: $existe);
        }
        return $campo_r;
    }

    /**
     * P INT P ORDER PROBADO ERRROEV
     * @param array $keys_checked Keys a validar checked
     * @param array $registro
     * @return bool|array
     */
    private function checked(array $keys_checked, array $registro): bool|array
    {
        foreach($keys_checked as $campo){
            $verifica = $this->verifica_chk(campo: $campo,keys_checked: $keys_checked,registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar campo',data: $verifica);
            }
        }
        return true;
    }

    /**
     * Limpia y valida unj elemento vacio
     * @param string $campo campo a verificar
     * @param array $keys_obligatorios Conjunto de campos obligatorios definidos en el modelo
     * @param array $registro Registro a validar
     * @return array|string
     * @version 1.351.41
     */
    private function data_vacio(string $campo, array $keys_obligatorios, array $registro): array|string
    {
        $campo_r = $this->txt_valido($campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al reasignar campo valor',data: $campo_r);
        }
        $existe = $this->existe(keys_obligatorios: $keys_obligatorios,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe',data: $existe);
        }
        if(!isset($registro[$campo_r])){
            $registro[$campo_r] = '';
        }
        return trim($registro[$campo_r]);
    }

    /**
     * Verifica si existen o no los campos obligatorios de un modelo en transacciones
     * @param array $keys_obligatorios Conjunto de campos obligatorios integrados en el modelo
     * @param array $registro Registro a verificar
     * @return bool|array
     * @version 1.320.41
     */
    private function existe(array $keys_obligatorios, array $registro): bool|array
    {
        foreach($keys_obligatorios as $campo){

            $verifica = $this->verifica_existe(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar si existe campo', data: $verifica);
            }

        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO ERROREV
     * @param array $keys_obligatorios Conjunto de keys a verificar nativos de modelo
     * @param array $registro Registro a validar
     * @return bool|array
     */
    private function obligatorios(array $keys_obligatorios, array $registro): bool|array
    {
        $existe = $this->existe(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campos no existe', data: $existe);
        }
        $vacio = $this->vacio(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campo vacio', data: $vacio);
        }


        return true;
    }

    /**
     * Valida que un conjunto de ids existan y sean validos para una transaccion de un modelo
     * @param array $keys_ids Campos de tipo ids a validar
     * @param array $registro Registro a verificar en conjunto de los keys id definidos
     * @return bool|array
     */
    private function ids(array $keys_ids, array $registro ): bool|array
    {
        foreach($keys_ids as $campo){
            $verifica = $this->verifica_id(campo: $campo,keys_ids: $keys_ids,registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar campo ids',data:  $verifica);
            }

        }
        return true;
    }

    /**
     * Limpia los elementos de un alt a de un registro
     * @param string $key Key a limpiar
     * @param string $tipo_campo Tipo de dato para aplicar limpieza
     * @return array|stdClass
     * @version 1.285.41
     *
     */
    private function limpia_data_tipo_campo(string $key, string $tipo_campo): array|stdClass
    {
        $key_r = $this->txt_valido(txt:$key);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error en key de tipo campo', data: $key_r);
        }
        $tipo_campo_r = $this->txt_valido(txt:$tipo_campo);
        if(errores::$error){
            return $this->error->error(mensaje:'Error en $tipo_campo de tipo campo',data: $tipo_campo_r);
        }
        $data = new stdClass();
        $data->key = $key_r;
        $data->tipo_campo = $tipo_campo_r;
        return $data;
    }

    public function tabla(string $tabla): bool|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        return true;
    }

    /**
     * Valida que los campos esten bien aplicados conforme el tipo de dato asignado
     * @param array $registro Registro a verificar
     * @param array $tipo_campos Conjunto de tipos de datos a verificar
     * @return bool|array
     * @version 1.292.41
     */
    private function tipo_campos(array $registro, array $tipo_campos): bool|array
    {
        foreach($tipo_campos as $key =>$tipo_campo){
            $valida_campos = $this->verifica_tipo_dato(key: $key,registro: $registro,tipo_campo: $tipo_campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar campos', data: $valida_campos);
            }
        }


        return true;
    }

    /**
     * Valida si un texto es valido
     * @param string $txt Texto a validar
     * @return array|string
     * @version 1.266.40
     * @verfuncion 1.1.0
     * @fecha 2022-08-03 11:47
     * @author mgamboa
     *
     */
    private function txt_valido(string $txt): array|string
    {
        $txt = trim($txt);

        if($txt === ''){
            return $this->error->error(mensaje: 'Error el $txt no puede venir vacio', data: $txt);
        }
        if(is_numeric($txt)){
            return $this->error->error(mensaje: 'Error el $txt es numero debe se un string', data: $txt);
        }
        return $txt;
    }

    /**
     * Verifica si en elemento es vacio
     * @param array $keys_obligatorios Conjunto de keys a verificar nativos de modelo
     * @param array $registro Registro a validar
     * @return bool|array
     */
    private function vacio(array $keys_obligatorios, array $registro): bool|array
    {
        foreach($keys_obligatorios as $campo){
            $verifica = $this->verifica_vacio(campo: $campo,keys_obligatorios: $keys_obligatorios,registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar vacio',data: $verifica);
            }
        }
        return true;
    }

    /**
     * ERRORREV
     * @param string $campo Nombre del campo
     * @param array $keys_checked
     * @param array $registro
     * @return bool|array
     */
    private function verifica_chk(string $campo, array $keys_checked, array $registro): bool|array
    {
        $campo_r = $this->txt_valido($campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al reasignar campo valor',data: $campo_r);
        }
        $existe = $this->existe(keys_obligatorios: $keys_checked,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe',data: $existe);
        }
        if((string)$registro[$campo_r] !== 'activo' && (string)$registro[$campo_r]!=='inactivo' ){
            return $this->error->error(mensaje: 'Error $registro['.$campo_r.'] debe ser activo o inactivo',
                data: $registro);
        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO ERROREV
     * @param array $campos_obligatorios Conjunto de campos obligatorios de un modelo
     * @param array $registro Registro a verificar en conjunto de los keys id definidos
     * @param string $tabla Tabla a verificar
     * @param array $tipo_campos Campos con tipificacion desde modelo
     * @return bool|array
     */
    private function verifica_estructura(array $campos_obligatorios, array $registro, string $tabla,
                                         array $tipo_campos): bool|array
    {
        $valida_campo_obligatorio = $this->valida_campo_obligatorio(campos_obligatorios: $campos_obligatorios,
            registro: $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar campos obligatorios de registro '.$tabla,
                data: $valida_campo_obligatorio);
        }

        $valida_estructura = (new val_sql())->valida_estructura_campos(registro: $registro, tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar estructura ', data: $valida_estructura);
        }
        return true;
    }

    /**
     * Verifica que existe un campo de tipo id en una transaccion
     * @param string $campo Campo a validar
     * @param array $keys_ids Keys a validar
     * @param array $registro Registro a verificar
     * @return bool|array
     */
    private function verifica_id(string $campo, array $keys_ids, array $registro): bool|array
    {
        $campo_r = $this->campo_existe(campo: $campo,keys_ids: $keys_ids,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar campo ids', data: $campo_r);
        }

        if(!preg_match($this->patterns['id'], $registro[$campo_r])){
            return $this->error->error(mensaje: 'Error $registro['.$campo_r.'] es invalido',
                data: array($registro[$campo_r],$this->patterns['id']));
        }
        return true;
    }

    /**
     * P INT P ORDER ERROR
     * @param array $campos_obligatorios Campos definidos en el modelo como obligatorios
     * @param modelo $modelo Modelo a validar el alta
     * @param array $no_duplicados Conjunto de campos del modelo asignados como no duplicados
     * @param array $registro Registro a validar
     * @param string $tabla
     * @param array $tipo_campos Campos con tipificacion desde modelo
     * @return bool|array
     */
    public function valida_base_alta(array $campos_obligatorios, modelo $modelo, array $no_duplicados, array $registro,
                                     string $tabla, array $tipo_campos): bool|array
    {
        $valida = (new validaciones())->valida_alta_bd(registro: $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar alta ',data:  $valida);
        }

        $valida_estructura = $this->verifica_estructura(campos_obligatorios: $campos_obligatorios,
            registro: $registro,tabla: $tabla,tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar estructura ', data: $valida_estructura);
        }

        foreach($no_duplicados as $campo){
            $filtro = array();
            $key = $tabla.'.'.$campo;
            $filtro[$key] = $registro[$campo];

            $existe = $modelo->existe(filtro:$filtro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error verificar si existe duplicado',data:  $existe);
            }
            if($existe){
                return $this->error->error(mensaje: 'Error ya existe un registro con el campo '.$campo, data: $existe);
            }
        }

        return true;
    }

    /**
     *
     * Funcion que valida la estructura de los campos de un modelo
     *
     * @param array $registro Registro a verificar en conjunto de los keys id definidos
     * @param array $tipo_campos Campos con tipificacion desde modelo
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
            return $this->error->error(mensaje: 'Error al validar tipo de campo', data: $v_tipo_campos);
        }
        $v_obligatorios = $this->obligatorios(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo de campo', data: $v_obligatorios);
        }
        $v_ids = $this->ids(keys_ids: $keys_ids,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar id', data: $v_ids);
        }
        $v_checked = $this->checked(keys_checked: $keys_checked,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar checked', data: $v_checked);
        }


        return true;
    }

    /**
     * Verifica si existe o no un campo
     * @param string $campo campo a validar
     * @param array $registro Registro a validar
     * @return bool|array
     * v1.294.41
     *
     */
    private function verifica_existe(string $campo, array $registro): bool|array
    {
        $campo_r = $this->txt_valido(txt:$campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar campo invalido', data: $campo);
        }
        if(!isset($registro[$campo_r])){
            return $this->error->error(mensaje: 'Error $registro['.$campo_r.'] debe existir', data: $registro);
        }
        return true;
    }

    /**
     * Verifica el tipo de dato de un campo este bien aplicado
     * @param string $key Key del campo a verificar
     * @param array $registro Registro a verificar
     * @param string $tipo_campo Tipo de campo a validar
     * @return bool|array
     * @version 1.291.41
     */
    private function verifica_tipo_dato(string $key, array $registro, string $tipo_campo): bool|array
    {
        $data = $this->limpia_data_tipo_campo(key: $key, tipo_campo: $tipo_campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar dato',data:  $data);
        }

        $valida_campos = $this->valida_pattern_campo(key: $key, registro:  $registro, tipo_campo: $tipo_campo);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar campos', data:$valida_campos);
        }
        return true;
    }

    /**
     * Verifica si un elemento esta vacio
     * @param string $campo campo a verificar
     * @param array $keys_obligatorios Conjunto de campos obligatorios definidos en el modelo
     * @param array $registro Registro a validar
     * @return bool|array
     */
    private function verifica_vacio(string $campo,array $keys_obligatorios, array $registro): bool|array
    {
        $value = $this->data_vacio(campo: $campo,keys_obligatorios: $keys_obligatorios,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al verificar si existe',data:$value);
        }
        if($value === ''){
            return $this->error->error(mensaje:'Error $registro['.$campo.'] debe tener datos',data:$registro);
        }
        return true;
    }



}