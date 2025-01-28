<?php
namespace base\orm;
use gamboamartin\errores\errores;
use stdClass;

/**
 * REG
 * Clase `sumas`
 *
 * Esta clase proporciona métodos para generar cadenas SQL dinámicas con columnas sumatorias y alias,
 * facilitando la construcción de consultas SQL complejas de manera estructurada y flexible.
 *
 * @package base\orm
 * @property errores $error Instancia de la clase `errores` para manejar errores en tiempo de ejecución.
 */
class sumas{
    /**
     * @var errores Instancia para el manejo de errores.
     */
    private errores $error;

    /**
     * Constructor de la clase `sumas`.
     *
     * Inicializa la propiedad `$error` con una nueva instancia de la clase `errores`.
     */
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * REG
     * Genera una cadena SQL con columnas sumatorias, asignando alias a cada una.
     *
     * Esta función recibe un array de campos donde la clave es el alias y el valor es el nombre del campo.
     * Genera una cadena que incluye sumas con alias, en el formato `IFNULL(SUM(campo),0) AS alias`.
     *
     * @param array $campos Array asociativo donde:
     *                      - La clave (alias) es el nombre del alias para el campo sumatorio.
     *                      - El valor es el nombre del campo a sumar.
     *                      - Ejemplo: ['suma_monto' => 'monto', 'suma_cantidad' => 'cantidad'].
     *
     * @return array|string Devuelve una cadena con las columnas generadas o un array con detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campos = [
     *     'suma_monto' => 'monto',
     *     'suma_cantidad' => 'cantidad'
     * ];
     *
     * $resultado = $this->columnas_suma(campos: $campos);
     *
     * // Resultado esperado:
     * // "IFNULL(SUM(monto),0) AS suma_monto , IFNULL(SUM(cantidad),0) AS suma_cantidad"
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $campos vacío
     * $campos = [];
     *
     * $resultado = $this->columnas_suma(campos: $campos);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error campos no puede venir vacio',
     * //   'data' => []
     * // ]
     *
     * // Caso 2: Alias numérico
     * $campos = [
     *     0 => 'monto'
     * ];
     *
     * $resultado = $this->columnas_suma(campos: $campos);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $alias no es txt $campos[alias]=campo',
     * //   'data' => [...]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación inicial:**
     *    - `$campos` no debe estar vacío.
     * 2. **Recorrido y validaciones de `$campos`:**
     *    - La clave (`alias`) no puede ser numérica ni estar vacía.
     *    - El valor (`campo`) no puede estar vacío.
     * 3. **Generación de columnas:**
     *    - Para cada campo, se genera la columna SQL utilizando `data_campo_suma`.
     *    - Las columnas se concatenan con comas.
     * 4. **Retorno del resultado:**
     *    - Si no hay errores, devuelve la cadena con las columnas generadas.
     *    - Si hay errores, devuelve un array con los detalles del error.
     *
     * ### Casos de uso:
     * - **Contexto:** Construcción dinámica de columnas en consultas SQL que incluyen sumas con alias.
     * - **Ejemplo real:** Generar columnas como:
     *   ```sql
     *   "IFNULL(SUM(monto),0) AS suma_monto , IFNULL(SUM(cantidad),0) AS suma_cantidad"
     *   ```
     *
     * ### Consideraciones:
     * - Asegúrate de que las claves del array (`alias`) sean cadenas válidas y los valores (`campo`) no estén vacíos.
     * - La función maneja errores mediante la clase `errores`, proporcionando retroalimentación detallada.
     */

    final public function columnas_suma(array $campos): array|string
    {
        if(count($campos)===0){
            return $this->error->error(mensaje:'Error campos no puede venir vacio',data: $campos, es_final: true);
        }
        $columnas = '';
        foreach($campos as $alias =>$campo){
            if(is_numeric($alias)){
                return $this->error->error(mensaje: 'Error $alias no es txt $campos[alias]=campo',data: $campos,
                    es_final: true);
            }
            if($campo === ''){
                return $this->error->error(mensaje: 'Error $campo esta vacio $campos[alias]=campo',data: $campos,
                    es_final: true);
            }
            $alias = trim($alias);
            if($alias === ''){
                return $this->error->error(mensaje: 'Error $alias esta vacio',data: $alias, es_final: true);
            }

            $data = $this->data_campo_suma(alias: $alias, campo:$campo, columnas:  $columnas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al agregar columna',data: $data);
            }
            $columnas .= "$data->coma $data->column";

        }
        return $columnas;
    }

    /**
     * REG
     * Genera los datos necesarios para agregar un campo sumatorio con alias en una consulta SQL.
     *
     * Esta función permite generar una columna SQL en formato `IFNULL(SUM(campo),0) AS alias` y la coma separadora
     * para concatenar a las columnas existentes de una consulta SQL.
     *
     * @param string $alias Nombre del alias para el campo sumatorio.
     *                      - No puede estar vacío.
     * @param string $campo Nombre del campo a sumar.
     *                      - No puede estar vacío.
     * @param string $columnas Cadena que contiene las columnas ya definidas en la consulta SQL.
     *                         - Se utiliza para determinar si se debe agregar una coma separadora.
     *
     * @return array|stdClass Devuelve un objeto con dos propiedades:
     *                        - `column` (string): La columna generada en formato `IFNULL(SUM(campo),0) AS alias`.
     *                        - `coma` (string): La coma separadora si `$columnas` no está vacía.
     *                        Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $alias = 'suma_total';
     * $campo = 'monto';
     * $columnas = 'nombre, edad';
     *
     * $resultado = $this->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
     *
     * // Resultado esperado:
     * // $resultado->column => "IFNULL(SUM(monto),0) AS suma_total"
     * // $resultado->coma => " , "
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $campo vacío
     * $alias = 'suma_total';
     * $campo = '';
     * $columnas = 'nombre, edad';
     *
     * $resultado = $this->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $campo no puede venir vacio',
     * //   'data' => ''
     * // ]
     *
     * // Caso 2: $alias vacío
     * $alias = '';
     * $campo = 'monto';
     * $columnas = 'nombre, edad';
     *
     * $resultado = $this->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $alias no puede venir vacio',
     * //   'data' => ''
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de parámetros:**
     *    - `$campo` y `$alias` no deben estar vacíos.
     * 2. **Generación de la columna SQL:**
     *    - Usa la función `add_column` para crear la columna sumatoria con alias.
     * 3. **Determinación de la coma separadora:**
     *    - Usa la función `coma_sql` para agregar la coma si es necesario.
     * 4. **Retorno del resultado:**
     *    - Devuelve un objeto `stdClass` con las propiedades `column` y `coma`.
     *
     * ### Casos de uso:
     * - Útil para construir consultas SQL dinámicas que involucren sumas con alias.
     * - Facilita la integración de múltiples columnas con separación adecuada en consultas complejas.
     *
     * ### Consideraciones:
     * - Asegúrate de proporcionar valores válidos para `$alias` y `$campo`, ya que son obligatorios.
     * - La función maneja errores mediante la clase `errores`, asegurando retroalimentación clara.
     */

    private function data_campo_suma(string $alias, string $campo, string $columnas): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje:'Error $campo no puede venir vacio',data:  $campo, es_final: true);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error(mensaje: 'Error $alias no puede venir vacio', data: $alias, es_final: true);
        }

        $column = (new columnas())->add_column(alias: $alias, campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al agregar columna',data: $column);
        }

        $coma = (new sql_bass())->coma_sql(columnas: $columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al agregar coma',data: $coma);
        }

        $data = new stdClass();
        $data->column = $column;
        $data->coma = $coma;

        return $data;
    }


}
