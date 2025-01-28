<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use models\atributo;
use models\bitacora;
use models\seccion;
use stdClass;

class sumas{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * REG
     * Genera una cadena SQL que contiene columnas con sumas agregadas basadas en un array de campos y alias.
     *
     * Esta función recorre un array de campos, valida los datos de entrada y genera una definición de columnas SQL
     * en formato de suma (`SUM`) con sus respectivos alias. Maneja automáticamente la separación de las columnas
     * con comas para concatenarlas correctamente.
     *
     * @param array $campos Un array asociativo donde las claves son los alias de las columnas y los valores
     *                      son los nombres de los campos a sumar.
     *                      Ejemplo:
     *                      ```php
     *                      [
     *                          'suma_montos' => 'monto',
     *                          'suma_cantidades' => 'cantidad'
     *                      ]
     *                      ```
     *
     * @return array|string Devuelve una cadena que representa las columnas de suma en SQL.
     *                      Ejemplo: `'IFNULL( SUM(monto) ,0) AS suma_montos , IFNULL( SUM(cantidad) ,0) AS suma_cantidades'`.
     *
     *                      En caso de error, devuelve un array con los detalles del error.
     *
     * @throws array Si alguno de los parámetros no cumple con los requisitos de validación.
     *
     * ### Ejemplo de uso exitoso:
     *
     * 1. **Generar columnas de suma a partir de campos**:
     *    ```php
     *    $campos = [
     *        'suma_montos' => 'monto',
     *        'suma_cantidades' => 'cantidad'
     *    ];
     *
     *    $resultado = $this->columnas_suma(campos: $campos);
     *
     *    // Resultado esperado:
     *    // 'IFNULL( SUM(monto) ,0) AS suma_montos , IFNULL( SUM(cantidad) ,0) AS suma_cantidades'
     *    ```
     *
     * 2. **Generar una columna de suma única**:
     *    ```php
     *    $campos = [
     *        'suma_precios' => 'precio'
     *    ];
     *
     *    $resultado = $this->columnas_suma(campos: $campos);
     *
     *    // Resultado esperado:
     *    // 'IFNULL( SUM(precio) ,0) AS suma_precios'
     *    ```
     *
     * ### Casos de validación:
     *
     * - Si `$campos` está vacío:
     *    ```php
     *    $campos = [];
     *    $resultado = $this->columnas_suma(campos: $campos);
     *    // Resultado esperado: Error indicando que `$campos` no puede estar vacío.
     *    ```
     *
     * - Si un alias es numérico:
     *    ```php
     *    $campos = [
     *        123 => 'monto' // Alias no válido
     *    ];
     *    $resultado = $this->columnas_suma(campos: $campos);
     *    // Resultado esperado: Error indicando que `$alias` debe ser texto.
     *    ```
     *
     * - Si un campo está vacío:
     *    ```php
     *    $campos = [
     *        'suma_montos' => '' // Campo vacío
     *    ];
     *    $resultado = $this->columnas_suma(campos: $campos);
     *    // Resultado esperado: Error indicando que `$campo` no puede estar vacío.
     *    ```
     *
     * ### Dependencias:
     * - `data_campo_suma`: Genera las definiciones individuales de las columnas con sus alias.
     * - `coma_sql`: Determina si se necesita una coma separadora al concatenar columnas.
     *
     * ### Resultado esperado:
     * - Una cadena SQL que representa las columnas de suma.
     * - Un array de error si alguna validación falla o si ocurre un problema durante el procesamiento.
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
     * Genera y estructura los datos necesarios para agregar una columna de suma con alias a una consulta SQL.
     *
     * Esta función valida y procesa un campo y su alias para integrarlo en una consulta SQL como una
     * columna de suma. También calcula la coma separadora necesaria para concatenar el campo con
     * otras columnas en la consulta.
     *
     * @param string $alias El alias que se asignará a la columna en la consulta SQL.
     *                      Ejemplo: `'suma_total'`.
     *
     * @param string $campo El nombre del campo que se utilizará para realizar la suma en la consulta SQL.
     *                      Ejemplo: `'monto'`.
     *
     * @param string $columnas Las columnas existentes en la consulta SQL, utilizadas para determinar si
     *                         se necesita una coma separadora. Ejemplo: `'columna1, columna2'`.
     *
     * @return array|stdClass Devuelve un objeto con las siguientes propiedades:
     *                        - **column**: La cadena SQL que representa la columna con la función de suma
     *                          y el alias asignado. Ejemplo: `'IFNULL( SUM(monto) ,0) AS suma_total'`.
     *                        - **coma**: La coma separadora necesaria, si corresponde. Ejemplo: `' , '`.
     *
     *                        En caso de error, devuelve un array con los detalles del error.
     *
     * @throws array Si alguno de los parámetros requeridos está vacío o ocurre un error en las
     *                   dependencias utilizadas.
     *
     * ### Ejemplo de uso exitoso:
     *
     * 1. **Agregar una nueva columna con suma**:
     *    ```php
     *    $alias = 'suma_total';
     *    $campo = 'monto';
     *    $columnas = 'columna1, columna2';
     *
     *    $resultado = $this->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
     *
     *    // Resultado esperado:
     *    // $resultado->column => 'IFNULL( SUM(monto) ,0) AS suma_total'
     *    // $resultado->coma => ' , '
     *    ```
     *
     * 2. **Agregar una columna como primera entrada**:
     *    ```php
     *    $alias = 'suma_total';
     *    $campo = 'monto';
     *    $columnas = ''; // No hay columnas previas.
     *
     *    $resultado = $this->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
     *
     *    // Resultado esperado:
     *    // $resultado->column => 'IFNULL( SUM(monto) ,0) AS suma_total'
     *    // $resultado->coma => '' (no se agrega coma ya que no hay columnas previas)
     *    ```
     *
     * ### Casos de validación:
     *
     * - Si `$campo` está vacío:
     *    ```php
     *    $alias = 'suma_total';
     *    $campo = ''; // Campo vacío.
     *    $columnas = 'columna1';
     *
     *    $resultado = $this->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
     *    // Resultado esperado: Error indicando que `$campo` no puede estar vacío.
     *    ```
     *
     * - Si `$alias` está vacío:
     *    ```php
     *    $alias = ''; // Alias vacío.
     *    $campo = 'monto';
     *    $columnas = 'columna1';
     *
     *    $resultado = $this->data_campo_suma(alias: $alias, campo: $campo, columnas: $columnas);
     *    // Resultado esperado: Error indicando que `$alias` no puede estar vacío.
     *    ```
     *
     * ### Dependencias:
     * - `columnas::add_column`: Genera la definición de la columna con función de suma.
     * - `sql_bass::coma_sql`: Calcula si se debe agregar una coma a la consulta SQL.
     *
     * ### Resultado esperado:
     * - Un objeto `stdClass` con las propiedades `column` y `coma` si no hay errores.
     * - Un array con detalles del error si alguna validación falla o las dependencias generan un error.
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
