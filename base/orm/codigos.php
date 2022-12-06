<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Throwable;


class codigos{

    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     *
     * Devuelve una cadena de ceros con una longitud definida entre la cantidad de digits ingresados y el limite de
     * digitos que requiere en el codigo
     *
     * @param int $longitud es un digito que indica el inicio a partir del cual se concatenaran los ceros faltantes hasta
     * el limite
     * @param int $total_cadena es un digito que indica la cantidad total de caracteres
     * @example
     *      $resultado = asigna_cero_codigo(1,10);
     *      //return $ceros = '000000000';
     *
     * @return array|string
     * @throws errores Si $longitud es menor a 0
     * @example
     *      $resultado = asigna_cero_codigo(-1,10);
     *      //return array errores
     * @throws errores Si $total_cadena es menor a 0
     * @example
     *      $resultado = asigna_cero_codigo(10,-1);
     *      //return array errores
     */
    private function asigna_cero_codigo(int $longitud, int $total_cadena): array|string
    {//FIN Y DOC
        if($longitud<0){
            return $this->error->error('Error $longitud debe ser mayor a 0',$longitud);
        }
        if($total_cadena<0){
            return $this->error->error('Error $total_cadena debe ser mayor a 0',$total_cadena);
        }
        $ceros = '';
        for($i = $longitud; $i<$total_cadena; $i++){
            $ceros.='0';
        }
        return $ceros;
    }

    /**
     * Genera un codigo automatico
     * @param array $keys_registro Key para asignacion de datos base registro
     * @param array $keys_row Keys para asignacion de datos en base row
     * @param stdClass $row Registro previo
     * @param array $registro Registro de alta
     * @return array|string
     * @version 1.392.45
     */
    private function codigo_alta(array $keys_registro, array $keys_row, stdClass $row, array $registro): array|string
    {

        $codigo = $this->codigo_aut_init(keys_registro: $keys_registro,keys_row:  $keys_row,
            registro: $registro, row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar codigo', data: $codigo);
        }

        $codigo_random = $this->codigo_random();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener codigo random', data: $codigo_random);
        }

        $codigo.=$codigo_random;

        return $codigo;
    }

    /**
     * @param array $keys_registro Key para asignacion de datos base registro
     * @param array $keys_row Keys para asignacion de datos en base row
     * @param array $registro Registro para maquetacion de codigo basado en los keys_registro
     * @param stdClass $row
     * @return array|string
     */
    private function codigo_aut_init(array $keys_registro, array $keys_row, array $registro, stdClass $row): array|string
    {
        $codigo = '';

        $codigo = $this->codigo_base_aut(codigo:$codigo,keys:  $keys_registro, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al concatenar codigo', data: $codigo);
        }

        $codigo = $this->codigo_base_aut(codigo:$codigo,keys:  $keys_row, registro: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al concatenar codigo', data: $codigo);
        }
        return $codigo;
    }



    /**
     * @param string $codigo Codigo precargado
     * @param array $keys Key de integracion de registro
     * @param array|stdClass $registro Registro a utilizar para generacion de codigo
     * @return array|string
     */
    private function codigo_base_aut(string $codigo, array $keys, array|stdClass $registro): array|string
    {
        $codigo_ = $codigo;
        foreach ($keys as $key){

            $codigo_ = $this->codigo_concat_aut(codigo:$codigo_,key:  $key,keys:  $keys, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al concatenar codigo', data: $codigo_);
            }

        }
        return $codigo_;
    }

    /**
     * @param string $codigo Codigo precargado
     * @param mixed $key Key para validacion
     * @param array $keys Conjunto de keys para integrar codigo
     * @param array|stdClass $registro
     * @return array|string
     */
    private function codigo_concat_aut(string $codigo, mixed $key, array $keys, array|stdClass $registro): array|string
    {
        $valida = $this->valida_codigo_aut(key: $key,keys_registro:  $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $codigo = $this->concat_codigo_aut(codigo:$codigo, key: $key,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al concatenar codigo', data: $codigo);
        }
        return $codigo;
    }


    /**
     * Genera codigo random
     * @return array|string
     * @version 1.391.45
     */
    private function codigo_random(): array|string
    {
        try {
            $codigo = random_int(10, 99) . random_int(10, 99) . random_int(10, 99) . random_int(10, 99);
            $codigo .= random_int(10, 99) . random_int(10, 99) . random_int(10, 99) . random_int(10, 99);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al generar codigo random', data: $e);
        }
        return $codigo;
    }

    /**
     * Concatena un codigo previo con datos de registro
     * @param string $codigo Codigo precargaddo para concatenar
     * @param string $key
     * @param array|stdClass $registro
     * @return string
     */
    private function concat_codigo_aut(string $codigo, string $key, array|stdClass $registro): string
    {
        if(is_object($registro)){
            $registro = (array)$registro;
        }
        $codigo .= $registro[$key];
        $codigo .= '-';
        return $codigo;
    }

    /**
     * Genera un codigo de forma automatica
     * @param array $keys_registro Key para asignacion de datos base registro
     * @param array $keys_row Keys para asignacion de datos en base row
     * @param modelo $modelo Modelo para obtencion de datos precargados
     * @param int $registro_id Identificador
     * @param array $registro Registro para integracion de codigo
     * @return array|string
     * @version 1.394.45
     */
    public function genera_codigo(array $keys_registro, array $keys_row, modelo $modelo, int $registro_id,
                                   array $registro): array|string
    {
        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error $registro_id debe ser mayor a 0', data: $registro_id);
        }

        $row = $modelo->registro(registro_id: $registro_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $row);
        }

        $codigo = $this->codigo_alta(keys_registro: $keys_registro,keys_row:  $keys_row,row:  $row,
            registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener codigo', data: $codigo);
        }
        return $codigo;
    }



    /**
     *
     * @param array $registros
     * @param string $key
     * @param int $longitud_maxima
     * @return array|string
     */
    private function genera_ultimo_codigo_base_numero(array $registros, string $key,int $longitud_maxima):array|string{

        $valida_base = $this->valida_base_ultimo_codigo($registros,$key);
        if(errores::$error){
            return $this->error->error('Error al validar',$valida_base);
        }
        if($longitud_maxima < 0){
            return $this->error->error('Error $longitud_maxima debe ser mayor a 0',$longitud_maxima);
        }

        $ultimo_codigo_upd = $this->obten_ultimo_codigo_insert($registros,$key);
        if(errores::$error){
            return $this->error->error('Error al generar ultimo codigo',$ultimo_codigo_upd);
        }

        $longitud_codigo = strlen($ultimo_codigo_upd);

        $ceros = $this->asigna_cero_codigo($longitud_codigo,$longitud_maxima);
        if(errores::$error){
            return $this->error->error('Error al asignar ceros',$ceros);
        }

        return $ceros.$ultimo_codigo_upd;
    }

    /**
     *
     * @param int $ultimo_codigo
     * @return int|array
     */
    private function genera_ultimo_codigo_int(int $ultimo_codigo): int|array
    {
        if($ultimo_codigo<0){
            return $this->error->error('Error $ultimo_codigo debe ser mayor a 0',$ultimo_codigo);
        }

        $ultimo_codigo_int = $ultimo_codigo;
        return $ultimo_codigo_int+1;
    }

    /**
     *
     * @param array $registros
     * @param string $key
     * @return array|int
     */
    private function obten_ultimo_codigo_insert(array $registros, string $key): array|int
    {

        $valida_base = $this->valida_base_ultimo_codigo($registros,$key);
        if(errores::$error){
            return $this->error->error('Error al validar',$valida_base);
        }

        $registro  = $registros['registros'][0];

        if(!isset($registro[$key])){
            return $this->error->error('Error no existe $registro['.$key.']',$registro);
        }


        $ultimo_codigo = (int)$registro[$key];


        $ultimo_codigo_upd = $this->genera_ultimo_codigo_int($ultimo_codigo);
        if(errores::$error){
            return $this->error->error('Error al generar ultimo codigo',$ultimo_codigo_upd);
        }

        return $ultimo_codigo_upd;
    }

    /**
     *
     * Devuelve el registro ya validado en la posicion de codigo
     *
     * @param array $registros registro a revisar
     * @param string $key cadena de texto que indica la posicion del registro
     * @example
     *      $valida_base = $this->valida_base_ultimo_codigo($registros,$key);
     *
     * @return array
     * @throws errores $registros['registros'] debe existir
     * @throws errores $registros['registros'][0] debe existir
     * @throws errores $key no puede venir vacio
     *
     * @uses modelo_basico->genera_ultimo_codigo_base_numero()
     * @uses modelo_basico->obten_ultimo_codigo_insert()
     *
     */
    private function valida_base_ultimo_codigo(array $registros, string $key):array{
        if(!isset($registros['registros'])){
            return $this->error->error('Error no existe registros en registro',$registros);
        }
        if(!isset($registros['registros'][0])){
            return $this->error->error('Error no existe registros[registro][0]',$registros);
        }
        if($key === ''){
            return $this->error->error('Error no existe key no puede venir vacio',$key);
        }
        return $registros;
    }

    /**
     * Valida que los datos de un codigo automatico sean validos
     * @param mixed $key Key para validacion
     * @param array $keys_registro conjunto de key para integrar en base registro
     * @param array|stdClass $registro Registro de alta
     * @return bool|array
     * @version 1.395.45
     */
    private function valida_codigo_aut(mixed $key, array $keys_registro, array|stdClass $registro): bool|array
    {
        $valida = $this->valida_key_vacio(key: $key);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar key', data: $valida);
        }

        $valida = $this->validacion->valida_existencia_keys(keys: $keys_registro, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        return true;
    }

    /**
     * Valida si en txt esta vacio
     * @param mixed $key Texto a validar
     * @return bool|array
     * @version 1.393.45
     */
    private function valida_key_vacio(mixed $key): bool|array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
        }
        return true;
    }


}