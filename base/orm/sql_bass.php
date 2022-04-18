<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;
use JsonException;


class sql_bass{

    public array $estructura_bd = array();
    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }



    /**
     * P INT P ORDER
     * Funcion asignar true o false a un conjunto de campos para su utilizacion en vistas
     *
     * @param array $campo  campo a utilizar
     * @param array $bools  valores de campos a inicializar
     * @param array $bools_asignar  valores de campos para aplicar asignacion de bool
     * @example
     *      $required = false;
    if(in_array($campo['elemento_lista_campo'],$campos_obligatorios)){
    $required = true;
    }
    $bools = array('required'=>$required);
    $bools_asignar = array('ln','con_label','select_vacio_alta');
    $bools = $this->asigna_booleanos($bools_asignar,$campo,$bools);
     *
     * @return array conjunto de elementos con valores para su inicializacion
     * @throws errores definidos en internals
     * @uses consultas_base->genera_bools
     * @internal $this->asigna_data_bool($bool,$campo, $bools);
     */
    private function asigna_booleanos(array $bools, array $bools_asignar, array $campo):array{
        foreach($bools_asignar as $bool){
            if($bool === ''){
                return $this->error->error('Error $bool no puede venir vacia',$bool);
            }
            if(!isset($campo['elemento_lista_'.$bool])){
                return $this->error->error('Error $campo[\'elemento_lista_'.$bool.'] debe existir',$campo);
            }
            $data = $this->asigna_data_bool(bool: $bool, bools:  $bools,campo: $campo);
            if(errores::$error){
                return $this->error->error('Error al generar bool',$data);
            }
            $bools = $data;
        }

        return $bools;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion asignar true o false a un campo para su utilizacion en vistas
     *
     * @param string $bool key de campo
     * @param array $campo  campo a utilizar
     * @param array $bools  valores de campos a inicializar
     * @example
     *      $data = $this->asigna_data_bool($bool,$campo, $bools);
     *
     * @return array conjunto de elementos con valores para su inicializacion
     * @throws errores $bool vacio
     * @uses consultas_base->asigna_booleanos
     * @internal $this->true_false($bool,$campo);
     */
    private function asigna_data_bool(string $bool, array $bools, array $campo):array{
        if($bool === ''){
            return $this->error->error('Error $bool no puede venir vacia',$bool);
        }
        $key = 'elemento_lista_'.$bool;
        if(!isset($campo[$key])){
            return $this->error->error('Error $campo[elemento_lista_'.$bool.'] debe existir',$campo);
        }
        $data = $this->true_false(campo: $campo, key:$bool);
        if(errores::$error){
            return $this->error->error('Error al generar bool',$data);
        }
        $bools[$bool] = $data;

        return $bools;
    }



    /**
     * P INT P ORDER
     * Funcion para la generacion de booleanos para su utilizacion en vistas
     *
     * @param array $campo campo de la vista
     * @param array $campos_obligatorios  campos para la asignacion de booleanos
     * @example
     *      $bools = $this->genera_bools($campo,$campos_obligatorios);
     *
     * @return array conjunto de elementos para la asignacion de booleanos
     * @throws errores definidos en internals
     * @uses consultas_base->genera_estructura_init
     * @internal $this->asigna_booleanos($bools_asignar,$campo,$bools);
     */
    private function genera_bools(array $campo, array $campos_obligatorios):array{
        if(!isset($campo['elemento_lista_campo'])){
            return $this->error->error('Error no existe $campo[elemento_lista_campo]',$campo);
        }
        $required = false;
        if(in_array($campo['elemento_lista_campo'],$campos_obligatorios)){
            $required = true;
        }
        $bools = array('required'=>$required);
        $bools_asignar = array('ln','con_label','select_vacio_alta');
        $bools = $this->asigna_booleanos(bools: $bools, bools_asignar: $bools_asignar,campo: $campo);
        if(errores::$error){
            return $this->error->error('Error al generar bools',$bools);
        }

        return $bools;
    }

    /**
     * P INT P ORDER
     * Funcion para la generacion de la estructura para ser utilizada en views
     *
     * @param array $campo campo de la vista
     * @param string $tabla  tabla del modelo o estructura
     * @param array $campo_envio  informacion inicializada para su utilizacion n las vistas
     * @example
     *      $campo_envio = $this->maqueta_campo_envio($campo,$vista, $valor_extra,$representacion, $bools);
    $estructura = $this->genera_estructura($tabla,$campo,$campo_envio);
     *
     * @return array conjunto de elementos para ser utilizados en views
     * @throws errores definidos en internals
     * @uses consultas_base->inicializa_estructura
     */
    private function genera_estructura( array $campo, array $campo_envio, array $estructura_bd, string $tabla):array{

        $valida = $this->valida_estructura(campo: $campo);
        if(errores::$error){
            return $this->error->error('Error al validar estructura',$valida);
        }
        $estructura_bd[$tabla]['campos'][$campo['elemento_lista_campo']] = $campo_envio;
        $estructura_bd[$tabla]['campos_completos'][$campo['elemento_lista_tabla_externa'].'_'.$campo['elemento_lista_campo']] = $campo_envio;

        return $estructura_bd;
    }

    /**
     * P INT P ORDER
     * @param array $campo
     * @return bool|array
     */
    private function valida_estructura(array $campo): bool|array
    {
        if(!isset($campo['elemento_lista_campo'])){
            return $this->error->error('Error no existe $campo[elemento_lista_campo]',$campo);
        }
        if(!isset($campo['elemento_lista_tabla_externa'])){
            return $this->error->error('Error no existe $campo[elemento_lista_tabla_externa]',$campo);
        }
        return true;
    }


    /**
     * P INT P ORDER
     * Funcion para la generacion de la estructura para ser utilizada en views
     *
     * @param array $campo campo de la vista
     * @param string $tabla tabla del modelo o estructura
     * @param array $campos_obligatorios informacion inicializada para su utilizacion n las vistas
     * @param string $vista vista en la que se aplicaran los ajustes
     * @return array conjunto de elementos para ser utilizados en views
     * @throws errores definidos en internals*@throws JsonException
     * @throws JsonException
     * @example
     *      foreach ($estructura_init as $campo){
     * $estructura = $this->genera_estructura_init($campo,$campos_obligatorios,$vista,$tabla);
     * }
     *
     * @uses consultas_base->maqueta_estructuras
     * @internal  $this->genera_bools($campo,$campos_obligatorios);
     * @internal  $this->inicializa_estructura($campo,$vista, $valor_extra,$representacion,$tabla, $bools);
     */

    private function genera_estructura_init(array $campo, array $campos_obligatorios,  array $estructura_bd,
                                            string $tabla, string $vista):array{
        if(!isset($campo['elemento_lista_campo'])){
            return $this->error->error('Error no existe $campo[elemento_lista_campo]',$campo);
        }
        $bools = $this->genera_bools(campo: $campo,campos_obligatorios: $campos_obligatorios);
        if(errores::$error){
            return $this->error->error('Error al generar bools',$bools);
        }
        if(!isset($campo['elemento_lista_css_id'])){
            $campo['elemento_lista_css_id'] = '';
        }

        $valor_extra = $this->valor_extra(campo: $campo);
        if(errores::$error){
            return $this->error->error('Error al obtener valor_extra',$valor_extra);
        }

        if(isset($campo['elemento_lista_representacion']) && (string)$campo['elemento_lista_representacion']!==''){
            $elemento_lista_representacion =(string)$campo['elemento_lista_representacion'];
        }
        else{
            $elemento_lista_representacion = '';
        }
        $representacion = $elemento_lista_representacion;

        if(!isset($campo['elemento_lista_pattern'])){
            $campo['elemento_lista_pattern'] = '';
        }

        $estructura_bd = $this->inicializa_estructura(bools:  $bools, campo: $campo, estructura_bd:  $estructura_bd,
            representacion: $representacion, tabla: $tabla,valor_extra: $valor_extra, vista: $vista);
        if(errores::$error){
            return $this->error->error('Error al generar estructura',$estructura_bd);
        }


        return $estructura_bd;
    }


    /**
     * P INT P ORDER
     * @param array $campo
     * @return mixed
     * @throws JsonException
     */
    private function valor_extra(array $campo):mixed{
        $valor_extra = array();
        if(isset($campo['elemento_lista_valor_extra']) && (string)$campo['elemento_lista_valor_extra']!==''){
            $valor_extra = json_decode($campo['elemento_lista_valor_extra'], true, 512, JSON_THROW_ON_ERROR);
        }
        return $valor_extra;
    }



    /**
     * P INT P ORDER
     * Funcion para inicializar estructura para vistas
     *
     * @param string $tabla  tabla para la inicializacion de la estructura
     * @param array $campo  datos del campo a inicializar
     * @param string $vista  vista donde de aplica la estructura
     * @param array $valor_extra  valor extra que se anade a los inputs
     * @param string $representacion  forma en la que se muestran los datos
     * @param array $bools  configuracion de campos booleanos
     * @example
     *      $bools = $this->genera_bools($campo,$campos_obligatorios);
    if(!isset($campo['elemento_lista_css_id'])){
    $campo['elemento_lista_css_id'] = '';
    }
    $valor_extra = array();
    if((string)$campo['elemento_lista_valor_extra']!==''){
    $valor_extra =json_decode($campo['elemento_lista_valor_extra'], true);
    }
    $representacion = (string)$campo['elemento_lista_representacion'];

    $estructura = $this->inicializa_estructura($campo,$vista, $valor_extra,$representacion,$tabla, $bools);
     * @return array estructura con configuracion de campos
     * @throws errores definidos en internals
     * @uses consultas_base->genera_estructura_init
     */
    private function inicializa_estructura(array $bools, array $campo, array $estructura_bd, string $representacion,
                                           string $tabla, array $valor_extra, string $vista):array{

        $keys = array('elemento_lista_campo','elemento_lista_cols','elemento_lista_tipo',
            'elemento_lista_tabla_externa',
            'elemento_lista_etiqueta','elemento_lista_descripcion','elemento_lista_id');

        $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error("Error al validar campo", $valida);
        }

        $campo_envio = (new inicializacion())->maqueta_campo_envio(bools:  $bools, campo: $campo,
            representacion: $representacion, valor_extra: $valor_extra, vista: $vista);
        if(errores::$error){
            return $this->error->error('Error al maquetar campo envio',$campo_envio);
        }

        $estructura_bd = $this->genera_estructura(campo: $campo,campo_envio: $campo_envio,
            estructura_bd:  $estructura_bd, tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al generar estructura',$estructura_bd);
        }

        return $estructura_bd;
    }


    /**
     * P INT P ORDER
     * Funcion para maquetar un array para ser mostrado en las vistas base
     *
     * @param string $vista vista para su aplicacion en views
     * @param array $estructura_init array con datos de la estructura
     * @param array $campos_obligatorios campos para poner required true
     * @param string $tabla tabla de la estructura
     * @example
     *      $estructura_init = $resultado['registros'];
    $this->estructura_bd[$tabla]['campos'] = array();
    $this->estructura_bd[$tabla]['campos_completos']= array();
    $estructura = $this->maqueta_estructuras($estructura_init,$campos_obligatorios,$vista,$tabla);
     *
     * @return array con datos para su utilizacion en views
     * @throws errores definidos en internals
     * @uses consultas_base->genera_estructura_tabla
     * @internal  $this->genera_estructura_init($campo,$campos_obligatorios,$vista,$tabla);
     */

    public function maqueta_estructuras(array $campos_obligatorios, array $estructura_bd, array $estructura_init,
                                        string $tabla, string $vista):array{

        foreach ($estructura_init as $campo){
            if(!is_array($campo)){
                return $this->error->error('Error al campo debe ser un array',$estructura_init);
            }
            $estructura_bd = $this->genera_estructura_init(campo: $campo,campos_obligatorios: $campos_obligatorios,
                estructura_bd:  $estructura_bd, tabla: $tabla, vista: $vista);
            if(errores::$error){
                return $this->error->error('Error al generar estructura',$estructura_bd);
            }

        }

        return $estructura_bd;
    }


    /**
     * P INT P ORDER PROBADO
     * Funcion para determinar TRUE O FALSE a campo para elemento lista
     *
     * @param string $key  key de elemento lista a validar
     * @param array $campo  campo a validar
     * @example
     *      $data = $this->true_false($bool,$campo);
     *
     * @return array|bool conjunto de joins en forma de SQL
     * @throws errores $key vacio
     * @throws errores $campo[elemento_lista.$key] debe existir
     * @uses consultas_base->asigna_data_bool
     */
    private function true_false(array $campo, string $key):array|bool{
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error key no puede venir vacio',$key);
        }
        $key_row = 'elemento_lista_'.$key;
        if(!isset($campo[$key_row])){
            return $this->error->error('Error $campo[elemento_lista_'.$key.'] debe existir',$campo);
        }
        $data = false;
        if($campo[$key_row] === 'activo'){
            $data = true;
        }

        return $data;
    }

}