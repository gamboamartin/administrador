<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;



class data_format{

    public errores $error;
    public validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * REG
     * Ajusta los campos de tipo moneda dentro de un registro.
     *
     * Este método recorre un array de campos y sus respectivos tipos de datos y
     * aplica el formato de moneda a aquellos que correspondan. Se asegura de eliminar
     * caracteres especiales de moneda, como `$`, `€`, y `,` en los valores correspondientes.
     *
     * @param array $registro Registro que contiene los datos a procesar.
     *                        - Ejemplo:
     *                          ```php
     *                          [
     *                              'precio' => '$1,234.56',
     *                              'costo' => '€ 2.500,75',
     *                              'cantidad' => 5
     *                          ]
     *                          ```
     * @param array $tipo_campos Especifica el tipo de dato esperado para cada campo.
     *                           Se debe definir en la forma `['campo' => 'tipo_dato']`.
     *                           - Ejemplo:
     *                             ```php
     *                             [
     *                                 'precio' => 'moneda',
     *                                 'costo' => 'moneda',
     *                                 'cantidad' => 'int'
     *                             ]
     *                             ```
     *
     * @return array Retorna el registro con los campos de tipo `moneda` o `double`
     *               ajustados correctamente.
     *               - Si un campo es de tipo `moneda` o `double`, se le aplica el formato.
     *               - Si un campo no existe en `$registro`, se omite su procesamiento.
     *               - Si hay un error, se devuelve un array con los detalles del error.
     *
     * @throws array Devuelve un error en los siguientes casos:
     *               - Si `$campo` está vacío.
     *               - Si `$tipo_dato` no es un string válido.
     *               - Si `$tipo_dato` está vacío.
     *
     * @example
     * ```php
     * $registro = [
     *     'precio' => '$1,234.56',
     *     'costo' => '€ 2.500,75',
     *     'cantidad' => 5
     * ];
     *
     * $tipo_campos = [
     *     'precio' => 'moneda',
     *     'costo' => 'moneda',
     *     'cantidad' => 'int'
     * ];
     *
     * $dataFormat = new data_format();
     * $registro_limpio = $dataFormat->ajusta_campos_moneda($registro, $tipo_campos);
     *
     * print_r($registro_limpio);
     * // Salida esperada:
     * // [
     * //     'precio' => '1234.56',
     * //     'costo' => '2500.75',
     * //     'cantidad' => 5
     * // ]
     * ```
     */

    final public function ajusta_campos_moneda(array $registro, array $tipo_campos): array
    {
        foreach($tipo_campos as $campo =>$tipo_dato){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo,es_final: true);
            }
            if(!is_string($tipo_dato)){
                $fix = 'modelo->tipo_campos debe llevar esta forma $modelo->tipo_campos[campo] = regex 
                donde el regex debe existir en el paquete de validaciones en validacion->patterns';
                return $this->error->error(mensaje: 'Error el tipo_dato debe ser un string', data: $tipo_dato,
                    es_final: true, fix: $fix);
            }

            $tipo_dato = trim($tipo_dato);
            if($tipo_dato === ''){
                $fix = 'modelo->tipo_campos debe llevar esta forma $modelo->tipo_campos[campo] = regex 
                donde el regex debe existir en el paquete de validaciones en validacion->patterns';
                return $this->error->error(mensaje: 'Error el tipo_dato esta vacio', data: $tipo_dato,
                    es_final: true, fix: $fix);
            }

            $registro = $this->asignacion_campo_moneda(campo: $campo, registro: $registro,tipo_dato:  $tipo_dato);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro);
            }
        }
        return $registro;
    }

    /**
     * REG
     * Formatea un campo de tipo moneda en un registro eliminando caracteres no numéricos.
     *
     * Esta función toma un campo dentro de un array asociativo y elimina caracteres
     * especiales de moneda, como `$`, `€`, y separadores de miles `,`, dejando solo
     * el valor numérico listo para ser procesado como un número flotante.
     *
     * @param string $campo Nombre del campo que se va a formatear.
     *                      - Ejemplo: 'precio', 'costo_total', 'saldo'.
     * @param array $registro Array asociativo que contiene los datos del registro.
     *                        - Ejemplo:
     *                          ```php
     *                          [
     *                              'precio' => '$1,234.56',
     *                              'cantidad' => 10
     *                          ]
     *                          ```
     *
     * @return array Retorna el registro con el campo monetario formateado.
     *               - Si el campo existe y es válido, retorna el array con el valor limpio.
     *               - Si hay un error, retorna un array con la estructura del error.
     *
     * @throws array Devuelve un error en los siguientes casos:
     *               - Si el nombre del campo está vacío.
     *               - Si el campo no existe dentro del array `$registro`.
     *               - Si el valor del campo es `null` o una cadena vacía.
     *               - Si el valor no puede convertirse a un número válido.
     *
     * @example
     * ```php
     * $registro = [
     *     'precio' => '$1,234.56',
     *     'cantidad' => 5
     * ];
     *
     * $dataFormat = new data_format();
     * $registro_limpio = $dataFormat->asigna_campo_moneda('precio', $registro);
     *
     * print_r($registro_limpio);
     * // Salida esperada:
     * // [
     * //     'precio' => '1234.56',
     * //     'cantidad' => 5
     * // ]
     * ```
     *
     * @example
     * ```php
     * $registro = [
     *     'costo' => '€ 2.500,75'
     * ];
     * $registro_limpio = $dataFormat->asigna_campo_moneda('costo', $registro);
     * // Salida esperada: ['costo' => '2500.75']
     * ```
     *
     * @example
     * ```php
     * $registro = [
     *     'saldo' => null
     * ];
     * $registro_limpio = $dataFormat->asigna_campo_moneda('saldo', $registro);
     * // Error: 'Error: el valor del campo saldo es nulo o vacío'
     * ```
     */

    private function asigna_campo_moneda(string $campo, array $registro): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio', data: $campo, es_final: true);
        }
        if(!isset($registro[$campo])){
            return $this->error->error(mensaje: 'Error $registro['.$campo.'] no existe',data:  $registro,
                es_final: true);
        }
        $registro[$campo] = str_replace('€', '', $registro[$campo]);
        $registro[$campo] = str_replace('$', '', $registro[$campo]);
        $registro[$campo] = str_replace(',', '', $registro[$campo]);
        return $registro;
    }

    /**
     * REG
     * Asigna un formato adecuado a un campo de tipo moneda dentro de un registro.
     *
     * Esta función verifica si un campo en un registro necesita ser formateado como
     * una moneda o un número de tipo `double`. Si el campo existe y su tipo de dato es
     * 'moneda' o 'double', se aplicará el formato correspondiente eliminando caracteres
     * especiales como `$`, `€`, y separadores de miles `,`.
     *
     * @param string $campo Nombre del campo dentro del array `$registro` que se desea formatear.
     *                      - Ejemplo: 'precio', 'costo_total', 'saldo'.
     * @param array $registro Registro que contiene los datos a procesar.
     *                        - Ejemplo:
     *                          ```php
     *                          [
     *                              'precio' => '$1,234.56',
     *                              'cantidad' => 10
     *                          ]
     *                          ```
     * @param string $tipo_dato Tipo de dato que se validará antes de aplicar el formato.
     *                          Solo se procesarán valores de tipo `'double'` o `'moneda'`.
     *                          - Ejemplo: `'double'`, `'moneda'`
     *
     * @return array Retorna el registro con el campo formateado correctamente.
     *               - Si el campo existe y es de tipo `'double'` o `'moneda'`, se aplicará el formato.
     *               - Si el campo no está en `$registro`, se devuelve el registro sin cambios.
     *               - Si hay un error, se devuelve un array con detalles del error.
     *
     * @throws array Devuelve un error en los siguientes casos:
     *               - Si `$campo` está vacío.
     *               - Si `$tipo_dato` está vacío.
     *               - Si `$tipo_dato` no es un string válido.
     *               - Si `$registro[$campo]` no existe y es obligatorio.
     *
     * @example
     * ```php
     * $registro = [
     *     'precio' => '$1,234.56',
     *     'cantidad' => 5
     * ];
     *
     * $dataFormat = new data_format();
     * $registro_limpio = $dataFormat->asignacion_campo_moneda('precio', $registro, 'moneda');
     *
     * print_r($registro_limpio);
     * // Salida esperada:
     * // [
     * //     'precio' => '1234.56',
     * //     'cantidad' => 5
     * // ]
     * ```
     *
     * @example
     * ```php
     * $registro = [
     *     'costo' => '€ 2.500,75'
     * ];
     * $registro_limpio = $dataFormat->asignacion_campo_moneda('costo', $registro, 'moneda');
     * // Salida esperada: ['costo' => '2500.75']
     * ```
     *
     * @example
     * ```php
     * $registro = [
     *     'saldo' => '1,000.00'
     * ];
     * $registro_limpio = $dataFormat->asignacion_campo_moneda('saldo', $registro, 'double');
     * // Salida esperada: ['saldo' => '1000.00']
     * ```
     */

    private function asignacion_campo_moneda(string $campo, array $registro, string $tipo_dato): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo, es_final: true);
        }


        $tipo_dato = trim($tipo_dato);
        if($tipo_dato === ''){
            $fix = 'modelo->tipo_campos debe llevar esta forma $modelo->tipo_campos[campo] = regex 
                donde el regex debe existir en el paquete de validaciones en validacion->patterns';
            return $this->error->error(mensaje: 'Error el tipo_dato esta vacio', data: $tipo_dato,
                es_final: true, fix: $fix);
        }
        if(isset($registro[$campo]) && ($tipo_dato === 'double' || $tipo_dato === 'moneda')){
            $registro = $this->asigna_campo_moneda(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro);
            }
        }
        return $registro;
    }


}