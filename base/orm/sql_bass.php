<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;
use JsonException;
use Throwable;


class sql_bass{

    public array $estructura_bd = array();
    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }



    /**
     *
     * Funcion asignar true o false a un conjunto de campos para su utilizacion en vistas
     * @version 1.64.17
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
                return $this->error->error(mensaje: 'Error $bool no puede venir vacia',data: $bools_asignar);
            }
            $key = 'adm_elemento_lista_'.$bool;
            if(!isset($campo[$key])){
                return $this->error->error(mensaje: 'Error $campo['.$key.'] debe existir',data: $campo);
            }
            $data = $this->asigna_data_bool(bool: $bool, bools:  $bools,campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar bool',data: $data);
            }
            $bools = $data;
        }

        return $bools;
    }

    /**
     *
     * Funcion asignar true o false a un campo para su utilizacion en vistas
     * @version 1.64.17
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
            return $this->error->error(mensaje: 'Error $bool no puede venir vacia',data: $bool);
        }
        $key = 'adm_elemento_lista_'.$bool;
        if(!isset($campo[$key])){
            return $this->error->error(mensaje: 'Error $campo[adm_elemento_lista_'.$bool.'] debe existir',data: $campo);
        }
        $data = $this->true_false(campo: $campo, key:$bool);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar bool',data:$data);
        }
        $bools[$bool] = $data;

        return $bools;
    }

    /**
     * REG
     * Genera una coma separadora para agregar columnas en una consulta SQL.
     *
     * @param string $columnas Cadena que contiene las columnas actuales de una consulta SQL.
     *                         - Si está vacía, no se agrega la coma separadora.
     *                         - Si no está vacía, devuelve una coma separadora con espacios alrededor.
     *
     * @return string Devuelve una cadena con la coma separadora (`', '`) si `$columnas` no está vacía.
     *                Devuelve una cadena vacía si `$columnas` está vacía.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $columnas = 'nombre, edad';
     *
     * $coma = $this->coma_sql($columnas);
     * echo $coma;
     * // Resultado esperado:
     * // ' , '
     *
     * $columnas = '';
     * $coma = $this->coma_sql($columnas);
     * echo $coma;
     * // Resultado esperado:
     * // ''
     * ```
     *
     * ### Proceso de la función:
     * 1. **Trimming de `$columnas`:**
     *    - Elimina espacios en blanco al inicio y al final de la cadena.
     * 2. **Evaluación de `$columnas`:**
     *    - Si `$columnas` no está vacía, asigna la coma separadora (`', '`).
     *    - Si `$columnas` está vacía, retorna una cadena vacía.
     * 3. **Retorno del resultado:**
     *    - Devuelve la coma separadora o una cadena vacía, según la evaluación.
     *
     * ### Ejemplo de errores:
     * Esta función no lanza errores ya que no tiene validaciones específicas. Sin embargo, es importante que se utilice con una cadena válida como parámetro.
     *
     * ### Casos de uso:
     * - Usada en la generación dinámica de consultas SQL, para agregar columnas adicionales de forma limpia.
     * - Facilita la concatenación de columnas en instrucciones SQL complejas.
     *
     * ### Consideraciones:
     * - La función está diseñada para manejar columnas en formato de cadena y determinar si se debe agregar una coma separadora.
     * - Es útil cuando se construyen consultas SQL de manera dinámica.
     */

    final public function coma_sql(string $columnas): string
    {
        $columnas = trim($columnas);
        $coma = '';
        if($columnas !== ''){
            $coma = ' , ';
        }
        return $coma;
    }



    /**
     *
     * Funcion para la generacion de booleanos para su utilizacion en vistas
     * @version 1.73.17
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
        if(!isset($campo['adm_elemento_lista_campo'])){
            return $this->error->error(mensaje: 'Error no existe $campo[adm_elemento_lista_campo]',data: $campo);
        }
        $required = false;
        if(in_array($campo['adm_elemento_lista_campo'], $campos_obligatorios, true)){
            $required = true;
        }
        $bools = array('required'=>$required);
        $bools_asignar = array('ln','con_label','select_vacio_alta');
        $bools = $this->asigna_booleanos(bools: $bools, bools_asignar: $bools_asignar,campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar bools',data: $bools);
        }

        return $bools;
    }

    /**
     *
     * Funcion para la generacion de la estructura para ser utilizada en views
     * @version 1.184.34
     * @param array $campo campo de la vista
     * @param string $tabla  tabla del modelo o estructura
     * @param array $campo_envio  informacion inicializada para su utilizacion n las vistas
     * @param array $estructura_bd Estructura del modelo de la base de datos
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
        $estructura_bd[$tabla]['campos'][$campo['adm_elemento_lista_campo']] = $campo_envio;
        $estructura_bd[$tabla]['campos_completos'][$campo['adm_elemento_lista_tabla_externa'].'_'.$campo['adm_elemento_lista_campo']] = $campo_envio;

        return $estructura_bd;
    }

    /**
     * Valida la estructura para upd
     * @version 1.183.34
     * @param array $campo campo de tipo elemento lista
     * @return bool|array
     */
    private function valida_estructura(array $campo): bool|array
    {
        if(!isset($campo['adm_elemento_lista_campo'])){
            return $this->error->error('Error no existe $campo[adm_elemento_lista_campo]',$campo);
        }
        if(!isset($campo['adm_elemento_lista_tabla_externa'])){
            return $this->error->error('Error no existe $campo[adm_elemento_lista_tabla_externa]',$campo);
        }
        return true;
    }


    /**
     *
     * Funcion para la generacion de la estructura para ser utilizada en views
     * @version 1.189.34
     * @param array $campo campo de la vista
     * @param string $tabla tabla del modelo o estructura
     * @param array $campos_obligatorios informacion inicializada para su utilizacion n las vistas
     * @param string $vista vista en la que se aplicaran los ajustes
     * @param array $estructura_bd Estructura del modelo de la base de datos
     * @return array conjunto de elementos para ser utilizados en views
     * @throws errores definidos en internals*@throws JsonException
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
        if(!isset($campo['adm_elemento_lista_campo'])){
            return $this->error->error(mensaje: 'Error no existe $campo[adm_elemento_lista_campo]',data: $campo);
        }

        $bools = $this->genera_bools(campo: $campo,campos_obligatorios: $campos_obligatorios);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar bools',data: $bools);
        }
        if(!isset($campo['adm_elemento_lista_css_id'])){
            $campo['adm_elemento_lista_css_id'] = '';
        }

        $valor_extra = $this->valor_extra(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener valor_extra',data: $valor_extra);
        }

        if(isset($campo['adm_elemento_lista_representacion']) && (string)$campo['adm_elemento_lista_representacion']!==''){
            $elemento_lista_representacion =(string)$campo['adm_elemento_lista_representacion'];
        }
        else{
            $elemento_lista_representacion = '';
        }
        $representacion = $elemento_lista_representacion;

        if(!isset($campo['adm_elemento_lista_pattern'])){
            $campo['elemento_lista_pattern'] = '';
        }

        $estructura_bd = $this->inicializa_estructura(bools:  $bools, campo: $campo, estructura_bd:  $estructura_bd,
            representacion: $representacion, tabla: $tabla,valor_extra: $valor_extra, vista: $vista);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar estructura',data: $estructura_bd);
        }


        return $estructura_bd;
    }


    /**
     *
     * @param array $campo
     * @version 1.74.17
     * @return mixed
     */
    private function valor_extra(array $campo):mixed{
        $valor_extra = array();
        if(isset($campo['adm_elemento_lista_valor_extra']) && (string)$campo['adm_elemento_lista_valor_extra']!==''){
            try {
                $valor_extra = json_decode($campo['adm_elemento_lista_valor_extra'], true, 512,
                    JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                return $this->error->error('Error al generar json',$e);
            }
        }
        return $valor_extra;
    }



    /**
     *
     * Funcion para inicializar estructura para vistas
     * @version 1.187.34
     * @param string $tabla  tabla para la inicializacion de la estructura
     * @param array $campo  datos del campo a inicializar
     * @param string $vista  vista donde de aplica la estructura
     * @param array $valor_extra  valor extra que se anade a los inputs
     * @param string $representacion  forma en la que se muestran los datos
     * @param array $bools  configuracion de campos booleanos
     * @param array $estructura_bd Estructura del modelo de la base de datos
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

        $keys = array('adm_elemento_lista_campo','adm_elemento_lista_cols','adm_elemento_lista_tipo',
            'adm_elemento_lista_tabla_externa', 'adm_elemento_lista_etiqueta','adm_elemento_lista_descripcion',
            'adm_elemento_lista_id');

        $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar campo", data: $valida);
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
     *
     * Funcion para maquetar un array para ser mostrado en las vistas base
     *
     * @param string $vista vista para su aplicacion en views
     * @param array $estructura_init array con datos de la estructura
     * @param array $campos_obligatorios campos para poner required true
     * @param string $tabla tabla de la estructura
     * @param array $estructura_bd Estructura del modelo de la base de datos
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
     * @version 1.204.34
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-26 10:10
     */

    public function maqueta_estructuras(array $campos_obligatorios, array $estructura_bd, array $estructura_init,
                                        string $tabla, string $vista):array{

        foreach ($estructura_init as $campo){
            if(!is_array($campo)){
                return $this->error->error(mensaje: 'Error al campo debe ser un array',data: $estructura_init);
            }
            $estructura_bd = $this->genera_estructura_init(campo: $campo,campos_obligatorios: $campos_obligatorios,
                estructura_bd:  $estructura_bd, tabla: $tabla, vista: $vista);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar estructura',data: $estructura_bd);
            }

        }

        return $estructura_bd;
    }


    /**
     *
     * Funcion para determinar TRUE O FALSE a campo para elemento lista
     * @version 1.64.17
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
            return $this->error->error(mensaje: 'Error key no puede venir vacio',data: $key);
        }
        $key_row = 'adm_elemento_lista_'.$key;
        if(!isset($campo[$key_row])){
            return $this->error->error(mensaje:'Error $campo[adm_elemento_lista_'.$key.'] debe existir',data:$campo);
        }
        $data = false;
        if($campo[$key_row] === 'activo'){
            $data = true;
        }

        return $data;
    }

}