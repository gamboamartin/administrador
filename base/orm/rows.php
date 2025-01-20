<?php
/**
 * @author Martin Gamboa Vazquez
 * Clase definida para activar elementos en la base de datos
 * @version 1.110.27
 */
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;


class rows{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }


    /**
     * REG
     * Asigna un valor de un array `$row` a un array de filtro `$filtro` basado en los campos proporcionados.
     *
     * Este método:
     * 1. Valida que los nombres de los campos `$campo_filtro` y `$campo_row` no estén vacíos.
     * 2. Si `$campo_row` no está definido en `$row`, lo inicializa con una cadena vacía.
     * 3. Asigna el valor de `$row[$campo_row]` convertido a cadena al filtro `$filtro[$campo_filtro]`.
     * 4. Retorna el array `$filtro` actualizado.
     *
     * @param string $campo_filtro Nombre del campo en el array de filtro donde se asignará el valor.
     * @param string $campo_row    Nombre del campo en el array `$row` desde donde se obtendrá el valor.
     * @param array  $filtro       Array de filtro que se actualizará con el valor asignado.
     * @param array  $row          Array asociativo que contiene el valor del campo `$campo_row`.
     *
     * @return array
     *   - Retorna el array `$filtro` con el nuevo valor asignado.
     *   - Si ocurre un error en las validaciones, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Asignar un valor existente en `$row` al filtro
     *  ---------------------------------------------------------
     *  $campo_filtro = 'id_filtro';
     *  $campo_row = 'id';
     *  $filtro = [];
     *  $row = ['id' => 123];
     *
     *  $resultado = $this->filtro_hijo($campo_filtro, $campo_row, $filtro, $row);
     *  // $resultado será:
     *  // [
     *  //     'id_filtro' => '123'
     *  // ]
     *
     * @example
     *  Ejemplo 2: Campo no existente en `$row`
     *  ---------------------------------------
     *  $campo_filtro = 'id_filtro';
     *  $campo_row = 'id';
     *  $filtro = [];
     *  $row = []; // El campo 'id' no está definido
     *
     *  $resultado = $this->filtro_hijo($campo_filtro, $campo_row, $filtro, $row);
     *  // $resultado será:
     *  // [
     *  //     'id_filtro' => ''
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por campo vacío
     *  --------------------------------
     *  $campo_filtro = '';
     *  $campo_row = 'id';
     *  $filtro = [];
     *  $row = ['id' => 123];
     *
     *  $resultado = $this->filtro_hijo($campo_filtro, $campo_row, $filtro, $row);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error filtro',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @throws array Si `$campo_row` o `$campo_filtro` están vacíos o si ocurre un error en la asignación, retorna un arreglo de error.
     */
    private function filtro_hijo(string $campo_filtro, string $campo_row, array $filtro, array $row): array
    {
        // Valida que el nombre del campo `$campo_row` no esté vacío
        if ($campo_row === '') {
            return $this->error->error(
                mensaje: "Error campo vacio",
                data: $campo_row,
                es_final: true
            );
        }

        // Valida que el nombre del campo `$campo_filtro` no esté vacío
        if ($campo_filtro === '') {
            return $this->error->error(
                mensaje: "Error filtro",
                data: $campo_filtro,
                es_final: true
            );
        }

        // Si `$campo_row` no existe en `$row`, inicializa su valor como cadena vacía
        if (!isset($row[$campo_row])) {
            $row[$campo_row] = '';
        }

        // Asigna el valor de `$row[$campo_row]` al filtro, convertido a string
        $filtro[$campo_filtro] = (string)$row[$campo_row];

        return $filtro;
    }


    /**
     * REG
     * Genera un filtro asociativo basado en un conjunto de mapeos de campos (`$filtros`) y datos de una fila (`$row`).
     *
     * Este método:
     * 1. Itera sobre un array de mapeos (`$filtros`), donde cada clave representa el nombre de un campo de filtro,
     *    y el valor representa el campo correspondiente en `$row`.
     * 2. Valida que los valores en `$filtros` no estén vacíos.
     * 3. Usa el método `filtro_hijo` para asignar el valor de `$row` correspondiente al filtro.
     * 4. Retorna el filtro generado o un arreglo de error si ocurre algún problema.
     *
     * @param array $filtros Array asociativo donde:
     *                       - Las claves son los nombres de los campos en el filtro final.
     *                       - Los valores son los nombres de los campos en `$row` desde donde se obtendrán los valores.
     * @param array $row     Array asociativo que contiene los datos de la fila base para generar el filtro.
     *
     * @return array
     *   - Retorna un array con los filtros generados.
     *   - Si ocurre un error en las validaciones, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Generar filtros a partir de un mapeo válido
     *  ------------------------------------------------------
     *  $filtros = [
     *      'id_hijo' => 'id_padre',
     *      'nombre_hijo' => 'nombre_padre'
     *  ];
     *  $row = [
     *      'id_padre' => 123,
     *      'nombre_padre' => 'Juan'
     *  ];
     *
     *  $resultado = $this->filtro_para_hijo($filtros, $row);
     *  // $resultado será:
     *  // [
     *  //     'id_hijo' => '123',
     *  //     'nombre_hijo' => 'Juan'
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error por campo vacío en `$filtros`
     *  ----------------------------------------------
     *  $filtros = [
     *      'id_hijo' => '',
     *      'nombre_hijo' => 'nombre_padre'
     *  ];
     *  $row = [
     *      'id_padre' => 123,
     *      'nombre_padre' => 'Juan'
     *  ];
     *
     *  $resultado = $this->filtro_para_hijo($filtros, $row);
     *  // Retorna un arreglo de error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error campo vacio',
     *  //     'data' => 'id_hijo',
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Campo no definido en `$row`
     *  --------------------------------------
     *  $filtros = [
     *      'id_hijo' => 'id_padre',
     *      'nombre_hijo' => 'nombre_padre'
     *  ];
     *  $row = [
     *      'id_padre' => 123
     *      // Falta 'nombre_padre'
     *  ];
     *
     *  $resultado = $this->filtro_para_hijo($filtros, $row);
     *  // $resultado será:
     *  // [
     *  //     'id_hijo' => '123',
     *  //     'nombre_hijo' => ''
     *  // ]
     *
     * @throws array Si algún valor en `$filtros` está vacío o si ocurre un error durante la asignación, retorna un arreglo con el error.
     */
    private function filtro_para_hijo(array $filtros, array $row): array
    {
        // Inicializa el array de filtro final
        $filtro = array();

        // Itera sobre los mapeos de campos en `$filtros`
        foreach ($filtros as $campo_filtro => $campo_row) {
            // Valida que el campo de `$filtros` no esté vacío
            if ($campo_row === '') {
                return $this->error->error(
                    mensaje: "Error campo vacio",
                    data: $campo_filtro,
                    es_final: true
                );
            }

            // Asigna el valor correspondiente desde `$row` al filtro
            $filtro = $this->filtro_hijo(
                campo_filtro: $campo_filtro,
                campo_row: $campo_row,
                filtro: $filtro,
                row: $row
            );

            // Maneja errores en la asignación del filtro
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al generar filtro',
                    data: $filtro
                );
            }
        }

        // Retorna el filtro generado
        return $filtro;
    }


    /**
     * REG
     * Genera un filtro asociativo para un modelo hijo basado en datos de entrada.
     *
     * Este método:
     * 1. Valida que el array `$data_modelo` contenga las claves necesarias (`filtros` y `filtros_con_valor`).
     * 2. Valida que las claves `filtros` y `filtros_con_valor` sean arrays.
     * 3. Genera un filtro basado en los mapeos proporcionados en `$data_modelo['filtros']` y los valores asociados en `$row`.
     * 4. Agrega valores adicionales desde `$data_modelo['filtros_con_valor']` al filtro generado.
     * 5. Retorna el filtro final o un arreglo de error si alguna validación falla.
     *
     * @param array $data_modelo Array asociativo que debe contener:
     *                           - `filtros`: Array asociativo donde las claves son los nombres de los campos en el filtro
     *                             final y los valores son los nombres de los campos en `$row`.
     *                           - `filtros_con_valor`: Array asociativo con valores adicionales a incluir en el filtro.
     * @param array $row         Array asociativo que contiene los datos base para generar el filtro.
     *
     * @return array
     *   - Retorna un array con el filtro generado, combinando los valores mapeados desde `$row` y los valores adicionales.
     *   - Si ocurre un error, retorna un arreglo con los detalles del error y un mensaje de corrección.
     *
     * @example
     *  Ejemplo 1: Generar un filtro válido
     *  -----------------------------------
     *  $data_modelo = [
     *      'filtros' => [
     *          'id_hijo' => 'id_padre',
     *          'nombre_hijo' => 'nombre_padre'
     *      ],
     *      'filtros_con_valor' => [
     *          'estatus' => 'activo'
     *      ]
     *  ];
     *  $row = [
     *      'id_padre' => 123,
     *      'nombre_padre' => 'Juan'
     *  ];
     *
     *  $resultado = $this->obten_filtro_para_hijo($data_modelo, $row);
     *  // $resultado será:
     *  // [
     *  //     'id_hijo' => '123',
     *  //     'nombre_hijo' => 'Juan',
     *  //     'estatus' => 'activo'
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error por falta de `filtros` en `$data_modelo`
     *  ---------------------------------------------------------
     *  $data_modelo = [
     *      // Falta la clave 'filtros'
     *      'filtros_con_valor' => [
     *          'estatus' => 'activo'
     *      ]
     *  ];
     *  $row = [
     *      'id_padre' => 123,
     *      'nombre_padre' => 'Juan'
     *  ];
     *
     *  $resultado = $this->obten_filtro_para_hijo($data_modelo, $row);
     *  // Retorna un arreglo de error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error filtro',
     *  //     'data' => [...],
     *  //     'fix' => 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()',
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por `filtros` no ser un array
     *  ----------------------------------------------
     *  $data_modelo = [
     *      'filtros' => 'no_es_array',
     *      'filtros_con_valor' => [
     *          'estatus' => 'activo'
     *      ]
     *  ];
     *  $row = [
     *      'id_padre' => 123,
     *      'nombre_padre' => 'Juan'
     *  ];
     *
     *  $resultado = $this->obten_filtro_para_hijo($data_modelo, $row);
     *  // Retorna un arreglo de error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error filtro',
     *  //     'data' => [...],
     *  //     'fix' => 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()',
     *  //     ...
     *  // ]
     *
     * @throws array Si alguna validación falla, retorna un arreglo de error con los detalles y un mensaje de corrección.
     */
    final public function obten_filtro_para_hijo(array $data_modelo, array $row): array
    {
        // Validar existencia de la clave 'filtros'
        if (!isset($data_modelo['filtros'])) {
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()';
            return $this->error->error(
                mensaje: "Error filtro",
                data: $data_modelo,
                es_final: true,
                fix: $fix
            );
        }

        // Validar existencia de la clave 'filtros_con_valor'
        if (!isset($data_modelo['filtros_con_valor'])) {
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros_con_valor] = array()';
            return $this->error->error(
                mensaje: "Error filtro",
                data: $data_modelo,
                es_final: true,
                fix: $fix
            );
        }

        // Validar que 'filtros' sea un array
        if (!is_array($data_modelo['filtros'])) {
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()';
            return $this->error->error(
                mensaje: "Error filtro",
                data: $data_modelo,
                es_final: true,
                fix: $fix
            );
        }

        // Validar que 'filtros_con_valor' sea un array
        if (!is_array($data_modelo['filtros_con_valor'])) {
            $fix = 'En data_modelo debe existir un key filtros_con_valor como array data_modelo[filtros_con_valor] = array()';
            return $this->error->error(
                mensaje: "Error filtro",
                data: $data_modelo,
                es_final: true,
                fix: $fix
            );
        }

        // Generar filtro base a partir de 'filtros'
        $filtros = $data_modelo['filtros'];
        $filtros_con_valor = $data_modelo['filtros_con_valor'];

        $filtro = $this->filtro_para_hijo(filtros: $filtros, row: $row);
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error filtro",
                data: $filtro
            );
        }

        // Agregar valores adicionales de 'filtros_con_valor'
        foreach ($filtros_con_valor as $campo_filtro => $value) {
            $filtro[$campo_filtro] = $value;
        }

        return $filtro;
    }



}
