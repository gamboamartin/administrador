<?php

namespace base\orm;

use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use PDOStatement;
use stdClass;

class _result
{
    private errores $error;
    private validaciones $validacion;

    public function __construct()
    {
        $this->error = new errores();
        $this->validacion = new validaciones();

    }

    /**
     * REG
     * Ajusta una fila (`$row`) para incluir valores desencriptados y registros hijos relacionados.
     *
     * Este método:
     * 1. Desencripta los valores de las columnas especificadas en `$campos_encriptados` dentro de la fila `$row`.
     * 2. Genera registros hijos relacionados con base en los modelos configurados en `$modelos_hijos`.
     *
     * @param array $campos_encriptados Lista de nombres de columnas en la fila `$row` cuyos valores deben desencriptarse.
     * @param modelo_base $modelo_base Instancia del modelo base utilizado para generar los modelos hijos.
     * @param array $modelos_hijos Array asociativo de modelos hijos configurados, donde:
     *                              - La clave es el nombre del modelo hijo.
     *                              - El valor es un array con configuraciones como `nombre_estructura`, `namespace_model`, etc.
     * @param array $row La fila que se ajustará para incluir valores desencriptados y registros hijos.
     *
     * @return array
     *   - Retorna la fila ajustada, incluyendo los valores desencriptados y registros hijos.
     *   - Retorna un arreglo de error si ocurre algún problema durante la ejecución.
     *
     * @example
     *  Ejemplo 1: Ajustar una fila con valores desencriptados y registros hijos
     *  -------------------------------------------------------------------------
     *  $campos_encriptados = ['nombre', 'email'];
     *  $modelo_base = new modelo_base($link);
     *  $modelos_hijos = [
     *      'detalle_factura' => [
     *          'nombre_estructura' => 'detalles',
     *          'namespace_model' => 'gamboamartin\\facturacion\\models',
     *          'filtros' => ['factura_id' => 'id'],
     *          'filtros_con_valor' => ['activo' => 'SI']
     *      ]
     *  ];
     *  $row = [
     *      'id' => 1,
     *      'nombre' => 'ValorEncriptado123',
     *      'email' => 'ValorEncriptado456'
     *  ];
     *
     *  $resultado = $this->ajusta_row_select(
     *      campos_encriptados: $campos_encriptados,
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      row: $row
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     'id' => 1,
     *  //     'nombre' => 'NombreDesencriptado',
     *  //     'email' => 'EmailDesencriptado',
     *  //     'detalles' => [
     *  //         ['id' => 101, 'factura_id' => 1, 'activo' => 'SI'],
     *  //         ['id' => 102, 'factura_id' => 1, 'activo' => 'SI']
     *  //     ]
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error al desencriptar un valor
     *  -----------------------------------------
     *  $campos_encriptados = ['nombre'];
     *  $modelo_base = new modelo_base($link);
     *  $modelos_hijos = [];
     *  $row = [
     *      'id' => 1,
     *      'nombre' => 'ValorEncriptadoErroneo'
     *  ];
     *
     *  $resultado = $this->ajusta_row_select(
     *      campos_encriptados: $campos_encriptados,
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      row: $row
     *  );
     *
     *  // Retorna un arreglo de error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al desencriptar',
     *  //     'data' => [...],
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Ajustar una fila sin modelos hijos
     *  ---------------------------------------------
     *  $campos_encriptados = ['nombre'];
     *  $modelo_base = new modelo_base($link);
     *  $modelos_hijos = [];
     *  $row = [
     *      'id' => 1,
     *      'nombre' => 'ValorEncriptado'
     *  ];
     *
     *  $resultado = $this->ajusta_row_select(
     *      campos_encriptados: $campos_encriptados,
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      row: $row
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     'id' => 1,
     *  //     'nombre' => 'NombreDesencriptado'
     *  // ]
     */
    private function ajusta_row_select(
        array $campos_encriptados, modelo_base $modelo_base, array $modelos_hijos, array $row): array
    {
        // Desencripta los valores en los campos especificados
        $row = (new inicializacion())->asigna_valor_desencriptado(
            campos_encriptados: $campos_encriptados,
            row: $row
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al desencriptar', data: $row);
        }

        // Si hay modelos hijos configurados, genera los registros correspondientes
        if (count($modelos_hijos) > 0) {
            $row = $this->genera_registros_hijos(
                modelo_base: $modelo_base,
                modelos_hijos: $modelos_hijos,
                row: $row
            );
            if (errores::$error) {
                return $this->error->error(mensaje: "Error en registro", data: $row);
            }
        }

        return $row;
    }


    /**
     * REG
     * Asigna registros hijos a una fila específica utilizando un modelo y un filtro.
     *
     * Este método:
     * 1. Valida la estructura y existencia de datos clave como el modelo, namespace y estructura.
     * 2. Genera un modelo basado en el nombre y namespace proporcionados.
     * 3. Filtra los registros del modelo utilizando el filtro proporcionado.
     * 4. Asigna los registros filtrados a la fila bajo una clave específica (`$nombre_estructura`).
     *
     * @param array $filtro Filtro para aplicar en el modelo para obtener los registros hijos.
     * @param modelo_base $modelo_base Instancia de un modelo base utilizado para generar el modelo objetivo.
     * @param string $name_modelo Nombre del modelo que se utilizará para obtener los registros.
     * @param string $namespace_model Namespace del modelo para su correcta instanciación.
     * @param string $nombre_estructura Nombre de la clave donde se asignarán los registros hijos en la fila.
     * @param array $row Fila en la que se agregarán los registros hijos.
     *
     * @return array
     *   - Retorna la fila `$row` con los registros hijos asignados en la clave `$nombre_estructura`.
     *   - Retorna un arreglo de error si ocurre algún problema durante la ejecución.
     *
     * @example
     *  Ejemplo 1: Asignación exitosa de registros hijos
     *  -------------------------------------------------
     *  $filtro = ['parent_id' => 1];
     *  $modelo_base = new modelo_base($link);
     *  $name_modelo = 'hijo_modelo';
     *  $namespace_model = 'gamboamartin\\facturacion\\models';
     *  $nombre_estructura = 'registros_hijos';
     *  $row = ['id' => 1, 'nombre' => 'Registro Padre'];
     *
     *  $resultado = $this->asigna_registros_hijo(
     *      filtro: $filtro,
     *      modelo_base: $modelo_base,
     *      name_modelo: $name_modelo,
     *      namespace_model: $namespace_model,
     *      nombre_estructura: $nombre_estructura,
     *      row: $row
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     'id' => 1,
     *  //     'nombre' => 'Registro Padre',
     *  //     'registros_hijos' => [
     *  //         ['id' => 101, 'parent_id' => 1, 'nombre' => 'Hijo 1'],
     *  //         ['id' => 102, 'parent_id' => 1, 'nombre' => 'Hijo 2']
     *  //     ]
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error por modelo inválido
     *  -------------------------------------
     *  $filtro = ['parent_id' => 1];
     *  $modelo_base = new modelo_base($link);
     *  $name_modelo = '';
     *  $namespace_model = 'gamboamartin\\facturacion\\models';
     *  $nombre_estructura = 'registros_hijos';
     *  $row = ['id' => 1, 'nombre' => 'Registro Padre'];
     *
     *  $resultado = $this->asigna_registros_hijo(
     *      filtro: $filtro,
     *      modelo_base: $modelo_base,
     *      name_modelo: $name_modelo,
     *      namespace_model: $namespace_model,
     *      nombre_estructura: $nombre_estructura,
     *      row: $row
     *  );
     *
     *  // Retorna un arreglo de error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al validar entrada para modelo',
     *  //     'data' => ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por nombre de estructura vacío
     *  ------------------------------------------------
     *  $filtro = ['parent_id' => 1];
     *  $modelo_base = new modelo_base($link);
     *  $name_modelo = 'hijo_modelo';
     *  $namespace_model = 'gamboamartin\\facturacion\\models';
     *  $nombre_estructura = '';
     *  $row = ['id' => 1, 'nombre' => 'Registro Padre'];
     *
     *  $resultado = $this->asigna_registros_hijo(
     *      filtro: $filtro,
     *      modelo_base: $modelo_base,
     *      name_modelo: $name_modelo,
     *      namespace_model: $namespace_model,
     *      nombre_estructura: $nombre_estructura,
     *      row: $row
     *  );
     *
     *  // Retorna un arreglo de error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error nombre estructura no puede venir vacia',
     *  //     'data' => ''
     *  // ]
     */
    private function asigna_registros_hijo(array $filtro, modelo_base $modelo_base, string $name_modelo,
                                           string $namespace_model, string $nombre_estructura, array $row): array
    {
        // Validar que el nombre del modelo sea válido
        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar entrada para modelo', data: $valida);
        }

        // Validar que el nombre de la estructura no esté vacío
        if ($nombre_estructura === '') {
            return $this->error->error(
                mensaje: 'Error nombre estructura no puede venir vacia',
                data: $nombre_estructura,
                es_final: true
            );
        }

        // Generar modelo basado en el nombre y namespace
        $modelo = $modelo_base->genera_modelo(modelo: $name_modelo, namespace_model: $namespace_model);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar modelo', data: $modelo);
        }

        // Obtener registros filtrados del modelo
        $data = $modelo->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registro hijos', data: $data);
        }

        // Asignar registros hijos al row
        $row[$nombre_estructura] = $data->registros;

        return $row;
    }


    /**
     * REG
     * Ejecuta una consulta SQL, procesa los resultados y los retorna en un formato estructurado.
     *
     * Este método:
     * 1. Valida que la consulta SQL no esté vacía.
     * 2. Ejecuta la consulta SQL y procesa los resultados utilizando `result_sql`.
     * 3. Maqueta el resultado final con información adicional como totales, número de registros, y registros en formato
     *    objeto utilizando `maqueta_result`.
     *
     * @param array $campos_encriptados Campos que requieren desencriptarse en los registros resultantes.
     * @param array $columnas_totales Columnas para las cuales se deben calcular los totales acumulados.
     * @param string $consulta La consulta SQL que se ejecutará.
     * @param modelo_base $modelo Instancia del modelo base que ejecutará la consulta.
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con los siguientes datos si el proceso es exitoso:
     *     - `registros`: Array con los registros procesados.
     *     - `n_registros`: Número total de registros procesados.
     *     - `sql`: La consulta SQL ejecutada.
     *     - `campos_entidad`: Los campos de la entidad del modelo.
     *     - `totales`: Objeto con los totales acumulados.
     *     - `registros_obj`: Array con los registros procesados como objetos.
     *   - Retorna un arreglo con el error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Ejecución exitosa con totales
     *  ----------------------------------------
     *  $campos_encriptados = ['campo_secreto'];
     *  $columnas_totales = ['precio'];
     *  $consulta = "SELECT id, nombre, precio FROM productos";
     *  $modelo = new modelo_base($link);
     *  $modelo->tabla = 'productos';
     *
     *  $resultado = $this->data_result($campos_encriptados, $columnas_totales, $consulta, $modelo);
     *  // $resultado contendrá un objeto con los datos procesados:
     *  // {
     *  //     "registros": [...],
     *  //     "n_registros": 5,
     *  //     "sql": "SELECT id, nombre, precio FROM productos",
     *  //     "campos_entidad": ["id", "nombre", "precio"],
     *  //     "totales": {"precio": 500},
     *  //     "registros_obj": [
     *  //         {"id": 1, "nombre": "Producto A", "precio": 100},
     *  //         ...
     *  //     ]
     *  // }
     *
     * @example
     *  Ejemplo 2: Error en la consulta vacía
     *  --------------------------------------
     *  $consulta = "";
     *  $resultado = $this->data_result($campos_encriptados, $columnas_totales, $consulta, $modelo);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => "Error consulta vacia",
     *  //   'data' => " tabla: productos",
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error al procesar totales
     *  -------------------------------------
     *  $columnas_totales = ['columna_inexistente'];
     *  $resultado = $this->data_result($campos_encriptados, $columnas_totales, $consulta, $modelo);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => "Error al parsear totales_rs",
     *  //   'data' => ...
     *  // ]
     */
    final public function data_result(
        array $campos_encriptados, array $columnas_totales, string $consulta, modelo_base $modelo): array|stdClass
    {
        // Validación inicial de la consulta SQL
        $consulta = trim($consulta);
        if ($consulta === '') {
            return $this->error->error(
                mensaje: "Error consulta vacia",
                data: $consulta . ' tabla: ' . $modelo->tabla,
                es_final: true
            );
        }

        // Ejecución y procesamiento inicial de la consulta
        $result_sql = $this->result_sql(
            campos_encriptados: $campos_encriptados,
            columnas_totales: $columnas_totales,
            consulta: $consulta,
            modelo: $modelo
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al ejecutar sql",
                data: $result_sql
            );
        }

        // Estructuración final del resultado
        $data = $this->maqueta_result(
            consulta: $consulta,
            modelo: $modelo,
            n_registros: $result_sql->n_registros,
            new_array: $result_sql->new_array,
            totales_rs: $result_sql->totales
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al parsear registros",
                data: $data
            );
        }

        return $data;
    }


    /**
     * REG
     * Genera una estructura con información de los modelos hijos de un modelo base.
     *
     * Este método recorre los modelos hijos definidos en la propiedad `hijo` del modelo base, validando
     * que cada modelo cumpla con los requisitos necesarios (como tener filtros, filtros con valor, y
     * un nombre de estructura definido). Si la validación falla, retorna un arreglo con el error.
     *
     * @param modelo_base $modelo Instancia del modelo base que contiene los modelos hijos.
     *                            El modelo base debe tener:
     *                            - `hijo`: Un array asociativo donde cada clave es el nombre del modelo hijo,
     *                              y su valor es un array con las propiedades:
     *                              - `filtros`: Array con los filtros aplicables al modelo hijo.
     *                              - `filtros_con_valor`: Array con los filtros que tienen valores asignados.
     *                              - `nombre_estructura`: Nombre de la estructura del modelo hijo.
     *                              - `namespace_model`: Namespace del modelo hijo.
     *
     * @return array Retorna un array con la estructura de los modelos hijos. Cada modelo hijo incluye:
     *               - `filtros`
     *               - `filtros_con_valor`
     *               - `nombre_estructura`
     *               - `namespace_model`
     *
     * @throws array Si alguno de los modelos hijos no cumple con las validaciones requeridas:
     *               - La clave del modelo hijo no puede ser numérica.
     *               - Deben existir las propiedades `filtros`, `filtros_con_valor`, y `nombre_estructura`.
     *               - Las propiedades `filtros` y `filtros_con_valor` deben ser arrays.
     *
     * @example
     *  Ejemplo 1: Generar modelos hijos válidos
     *  ----------------------------------------
     *  $modelo = new modelo_base();
     *  $modelo->hijo = [
     *      'usuario_direccion' => [
     *          'filtros' => ['activo' => '1'],
     *          'filtros_con_valor' => ['pais_id' => '1'],
     *          'nombre_estructura' => 'UsuarioDireccion',
     *          'namespace_model' => 'app\\modelos\\usuario_direccion'
     *      ]
     *  ];
     *
     *  $resultado = $this->genera_modelos_hijos($modelo);
     *  // $resultado será:
     *  // [
     *  //     'usuario_direccion' => [
     *  //         'filtros' => ['activo' => '1'],
     *  //         'filtros_con_valor' => ['pais_id' => '1'],
     *  //         'nombre_estructura' => 'UsuarioDireccion',
     *  //         'namespace_model' => 'app\\modelos\\usuario_direccion'
     *  //     ]
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error en un modelo hijo
     *  -----------------------------------
     *  $modelo = new modelo_base();
     *  $modelo->hijo = [
     *      0 => [
     *          'filtros' => ['activo' => '1'],
     *          'filtros_con_valor' => ['pais_id' => '1'],
     *          'nombre_estructura' => 'UsuarioDireccion',
     *          'namespace_model' => 'app\\modelos\\usuario_direccion'
     *      ]
     *  ];
     *
     *  $resultado = $this->genera_modelos_hijos($modelo);
     *  // Retorna un error, ya que la clave del modelo hijo es numérica:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error en key',
     *  //     'data' => [...]
     *  // ]
     */
    private function genera_modelos_hijos(modelo_base $modelo): array
    {
        // Inicializa la estructura de modelos hijos
        $modelos_hijos = array();

        // Recorre los modelos hijos definidos en el modelo base
        foreach ($modelo->hijo as $key => $modelo) {
            // Validación de la clave del modelo hijo
            if (is_numeric($key)) {
                return $this->error->error(
                    mensaje: "Error en key",
                    data: $modelo->hijo,
                    es_final: true
                );
            }

            // Validación de las propiedades requeridas
            if (!isset($modelo['filtros'])) {
                return $this->error->error(
                    mensaje: "Error filtro",
                    data: $modelo->hijo,
                    es_final: true
                );
            }
            if (!isset($modelo['filtros_con_valor'])) {
                return $this->error->error(
                    mensaje: "Error filtro",
                    data: $modelo->hijo,
                    es_final: true
                );
            }
            if (!is_array($modelo['filtros'])) {
                return $this->error->error(
                    mensaje: "Error filtro",
                    data: $modelo->hijo,
                    es_final: true
                );
            }
            if (!is_array($modelo['filtros_con_valor'])) {
                return $this->error->error(
                    mensaje: "Error filtro",
                    data: $modelo->hijo,
                    es_final: true
                );
            }
            if (!isset($modelo['nombre_estructura'])) {
                return $this->error->error(
                    mensaje: "Error en estructura",
                    data: $modelo->hijo,
                    es_final: true
                );
            }

            // Asigna las propiedades del modelo hijo
            $modelos_hijos[$key]['filtros'] = $modelo['filtros'];
            $modelos_hijos[$key]['filtros_con_valor'] = $modelo['filtros_con_valor'];
            $modelos_hijos[$key]['nombre_estructura'] = $modelo['nombre_estructura'];
            $modelos_hijos[$key]['namespace_model'] = $modelo['namespace_model'];
        }

        return $modelos_hijos;
    }


    /**
     * REG
     * Genera un registro hijo asociado a una fila específica utilizando un modelo y datos configurados.
     *
     * Este método:
     * 1. Valida que los datos del modelo contengan las claves necesarias (`nombre_estructura`, `namespace_model`).
     * 2. Obtiene el filtro para el registro hijo utilizando la información del modelo y la fila proporcionada.
     * 3. Asigna los registros hijos a la fila bajo la clave especificada en `$data_modelo['nombre_estructura']`.
     *
     * @param array $data_modelo Datos del modelo que contienen la configuración para generar los registros hijos:
     *                           - `nombre_estructura`: Clave donde se asignarán los registros hijos en la fila.
     *                           - `namespace_model`: Namespace del modelo a utilizar.
     * @param modelo_base $modelo_base Instancia de un modelo base para generar el modelo objetivo.
     * @param string $name_modelo Nombre del modelo que se utilizará para obtener los registros hijos.
     * @param array $row Fila en la que se agregarán los registros hijos.
     *
     * @return array
     *   - Retorna la fila `$row` con los registros hijos asignados bajo la clave `$data_modelo['nombre_estructura']`.
     *   - Retorna un arreglo de error si ocurre algún problema durante la ejecución.
     *
     * @example
     *  Ejemplo 1: Generación exitosa de registros hijos
     *  ------------------------------------------------
     *  $data_modelo = [
     *      'nombre_estructura' => 'detalles',
     *      'namespace_model' => 'gamboamartin\\facturacion\\models',
     *      'filtros' => ['parent_id' => 'id'],
     *      'filtros_con_valor' => ['activo' => 'SI']
     *  ];
     *  $modelo_base = new modelo_base($link);
     *  $name_modelo = 'factura_detalle';
     *  $row = ['id' => 1, 'nombre' => 'Factura 1'];
     *
     *  $resultado = $this->genera_registro_hijo(
     *      data_modelo: $data_modelo,
     *      modelo_base: $modelo_base,
     *      name_modelo: $name_modelo,
     *      row: $row
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     'id' => 1,
     *  //     'nombre' => 'Factura 1',
     *  //     'detalles' => [
     *  //         ['id' => 101, 'parent_id' => 1, 'activo' => 'SI'],
     *  //         ['id' => 102, 'parent_id' => 1, 'activo' => 'SI']
     *  //     ]
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error por datos incompletos en `$data_modelo`
     *  ---------------------------------------------------------
     *  $data_modelo = [
     *      'namespace_model' => 'gamboamartin\\facturacion\\models'
     *  ];
     *  $modelo_base = new modelo_base($link);
     *  $name_modelo = 'factura_detalle';
     *  $row = ['id' => 1, 'nombre' => 'Factura 1'];
     *
     *  $resultado = $this->genera_registro_hijo(
     *      data_modelo: $data_modelo,
     *      modelo_base: $modelo_base,
     *      name_modelo: $name_modelo,
     *      row: $row
     *  );
     *
     *  // Retorna un arreglo de error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error debe existir $data_modelo[\'nombre_estructura\']',
     *  //     'data' => $data_modelo
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error al asignar registros hijos
     *  --------------------------------------------
     *  $data_modelo = [
     *      'nombre_estructura' => 'detalles',
     *      'namespace_model' => 'gamboamartin\\facturacion\\models',
     *      'filtros' => ['parent_id' => 'id'],
     *      'filtros_con_valor' => []
     *  ];
     *  $modelo_base = new modelo_base($link);
     *  $name_modelo = 'factura_detalle';
     *  $row = ['id' => 1, 'nombre' => 'Factura 1'];
     *
     *  $resultado = $this->genera_registro_hijo(
     *      data_modelo: $data_modelo,
     *      modelo_base: $modelo_base,
     *      name_modelo: $name_modelo,
     *      row: $row
     *  );
     *
     *  // Si el modelo no genera registros correctamente, se devuelve un error.
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al asignar registros de hijo',
     *  //     'data' => ...
     *  // ]
     */
    private function genera_registro_hijo(array $data_modelo, modelo_base $modelo_base, string $name_modelo, array $row): array
    {
        // Validar la existencia de claves necesarias en el modelo
        $keys = array('nombre_estructura', 'namespace_model');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $data_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al validar data_modelo", data: $valida);
        }

        // Validar que la clave `nombre_estructura` esté definida
        if (!isset($data_modelo['nombre_estructura'])) {
            return $this->error->error(
                mensaje: 'Error debe existir $data_modelo[\'nombre_estructura\']',
                data: $data_modelo,
                es_final: true
            );
        }

        // Generar el filtro para obtener los registros hijos
        $filtro = (new rows())->obten_filtro_para_hijo(data_modelo: $data_modelo, row: $row);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error filtro", data: $filtro);
        }

        // Asignar los registros hijos a la fila
        $row = $this->asigna_registros_hijo(
            filtro: $filtro,
            modelo_base: $modelo_base,
            name_modelo: $name_modelo,
            namespace_model: $data_modelo['namespace_model'],
            nombre_estructura: $data_modelo['nombre_estructura'],
            row: $row
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar registros de hijo', data: $row);
        }

        return $row;
    }


    /**
     * REG
     * Genera registros hijos para una fila específica utilizando modelos configurados.
     *
     * Este método:
     * 1. Itera sobre los modelos hijos proporcionados en `$modelos_hijos` y valida su estructura.
     * 2. Valida que cada modelo hijo tenga las claves necesarias (`nombre_estructura`, `namespace_model`).
     * 3. Llama al método `genera_registro_hijo` para generar y asignar los registros hijos a la fila.
     *
     * @param modelo_base $modelo_base Instancia del modelo base utilizado para generar los modelos hijos.
     * @param array $modelos_hijos Array asociativo de modelos hijos configurados, donde cada clave es el nombre del modelo
     *                             y el valor es un array con las configuraciones:
     *                             - `nombre_estructura`: Clave donde se almacenarán los registros hijos.
     *                             - `namespace_model`: Namespace del modelo hijo.
     *                             - `filtros`: Array de configuración de filtros.
     *                             - `filtros_con_valor`: Array con filtros adicionales con valores específicos.
     * @param array $row Fila a la que se agregarán los registros hijos.
     *
     * @return array
     *   - Retorna la fila `$row` con los registros hijos asignados en las claves especificadas.
     *   - Retorna un arreglo de error si ocurre algún problema durante la ejecución.
     *
     * @example
     *  Ejemplo 1: Generar registros hijos exitosamente
     *  -----------------------------------------------
     *  $modelos_hijos = [
     *      'factura_detalle' => [
     *          'nombre_estructura' => 'detalles',
     *          'namespace_model' => 'gamboamartin\\facturacion\\models',
     *          'filtros' => ['parent_id' => 'id'],
     *          'filtros_con_valor' => ['activo' => 'SI']
     *      ]
     *  ];
     *  $modelo_base = new modelo_base($link);
     *  $row = ['id' => 1, 'nombre' => 'Factura 1'];
     *
     *  $resultado = $this->genera_registros_hijos(
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      row: $row
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     'id' => 1,
     *  //     'nombre' => 'Factura 1',
     *  //     'detalles' => [
     *  //         ['id' => 101, 'parent_id' => 1, 'activo' => 'SI'],
     *  //         ['id' => 102, 'parent_id' => 1, 'activo' => 'SI']
     *  //     ]
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error en datos de `$modelos_hijos`
     *  ---------------------------------------------
     *  $modelos_hijos = [
     *      'factura_detalle' => [
     *          'namespace_model' => 'gamboamartin\\facturacion\\models',
     *          'filtros' => ['parent_id' => 'id'],
     *          'filtros_con_valor' => ['activo' => 'SI']
     *      ]
     *  ];
     *  $modelo_base = new modelo_base($link);
     *  $row = ['id' => 1, 'nombre' => 'Factura 1'];
     *
     *  $resultado = $this->genera_registros_hijos(
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      row: $row
     *  );
     *
     *  // Retorna un arreglo de error indicando que falta la clave `nombre_estructura`:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error debe existir $data_modelo[\'nombre_estructura\']',
     *  //     'data' => $modelos_hijos['factura_detalle'],
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por `$name_modelo` no válido
     *  ---------------------------------------------
     *  $modelos_hijos = [
     *      123 => [
     *          'nombre_estructura' => 'detalles',
     *          'namespace_model' => 'gamboamartin\\facturacion\\models',
     *          'filtros' => ['parent_id' => 'id'],
     *          'filtros_con_valor' => ['activo' => 'SI']
     *      ]
     *  ];
     *  $modelo_base = new modelo_base($link);
     *  $row = ['id' => 1, 'nombre' => 'Factura 1'];
     *
     *  $resultado = $this->genera_registros_hijos(
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      row: $row
     *  );
     *
     *  // Retorna un arreglo de error indicando que `$name_modelo` debe ser un string:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error $name_modelo debe ser un string',
     *  //     ...
     *  // ]
     */
    private function genera_registros_hijos(modelo_base $modelo_base, array $modelos_hijos, array $row): array
    {
        foreach ($modelos_hijos as $name_modelo => $data_modelo) {
            // Validar que los datos del modelo hijo sean un array
            if (!is_array($data_modelo)) {
                $fix = '$modelos_hijos debe ser un array asociativo de la siguiente forma';
                $fix .= ' $modelos_hijos[name_modelo][nombre_estructura] = nombre de la tabla dependiente';
                $fix .= ' $modelos_hijos[name_modelo][filtros] = array() con configuración de filtros';
                $fix .= ' $modelos_hijos[name_modelo][filtros_con_valor] = array() con configuración de filtros';
                return $this->error->error(mensaje: "Error en datos", data: $modelos_hijos, es_final: true, fix: $fix);
            }

            // Validar existencia de claves necesarias en el modelo hijo
            $keys = ['nombre_estructura', 'namespace_model'];
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $data_modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: "Error al validar data_modelo", data: $valida);
            }

            // Validar que el nombre del modelo sea un string
            if (!is_string($name_modelo)) {
                $fix = '$modelos_hijos debe ser un array asociativo de la siguiente forma';
                $fix .= ' $modelos_hijos[name_modelo][nombre_estructura] = nombre de la tabla dependiente';
                $fix .= ' $modelos_hijos[name_modelo][filtros] = array() con configuración de filtros';
                $fix .= ' $modelos_hijos[name_modelo][filtros_con_valor] = array() con configuración de filtros';
                return $this->error->error(mensaje: 'Error $name_modelo debe ser un string ', data: $data_modelo, es_final: true, fix: $fix);
            }

            // Generar el registro hijo para la fila
            $row = $this->genera_registro_hijo(
                data_modelo: $data_modelo,
                modelo_base: $modelo_base,
                name_modelo: $name_modelo,
                row: $row
            );
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar registros de hijo', data: $row);
            }
        }

        return $row;
    }


    /**
     * REG
     * Inicializa el resultado base para un modelo y prepara un objeto con los datos procesados.
     *
     * Este método:
     * 1. Actualiza las propiedades del modelo con los registros, número de registros y la consulta SQL.
     * 2. Crea un objeto `stdClass` que contiene los mismos datos que se asignan al modelo, además de los totales acumulados.
     *
     * @param string $consulta La consulta SQL ejecutada.
     * @param modelo_base $modelo El modelo al que se asignarán los registros procesados y los datos resultantes.
     * @param int $n_registros Número total de registros procesados.
     * @param array $new_array Array con los registros procesados.
     * @param stdClass $totales_rs Objeto con los totales acumulados para las columnas especificadas.
     *
     * @return stdClass
     *   - Retorna un objeto con los siguientes campos:
     *     - `registros`: Los registros procesados.
     *     - `n_registros`: Número de registros procesados.
     *     - `sql`: La consulta SQL ejecutada.
     *     - `totales`: Los totales acumulados para las columnas especificadas.
     *
     * @example
     *  Ejemplo 1: Inicialización exitosa de resultados
     *  -----------------------------------------------
     *  $consulta = "SELECT * FROM facturas";
     *  $modelo = new modelo_base($link);
     *  $n_registros = 10;
     *  $new_array = [
     *      ['id' => 1, 'total' => 100],
     *      ['id' => 2, 'total' => 200],
     *  ];
     *  $totales_rs = new stdClass();
     *  $totales_rs->total = 300;
     *
     *  $resultado = $this->init_result_base($consulta, $modelo, $n_registros, $new_array, $totales_rs);
     *  // $resultado contendrá un objeto con los datos:
     *  // {
     *  //     "registros": [...],
     *  //     "n_registros": 10,
     *  //     "sql": "SELECT * FROM facturas",
     *  //     "totales": {"total": 300}
     *  // }
     *
     *  // Además, el modelo tendrá las siguientes propiedades actualizadas:
     *  // $modelo->registros = [...];
     *  // $modelo->n_registros = 10;
     *  // $modelo->sql = "SELECT * FROM facturas";
     *
     * @throws void Este método no genera excepciones ni errores directos.
     */
    private function init_result_base(
        string $consulta,
        modelo_base $modelo,
        int $n_registros,
        array $new_array,
        stdClass $totales_rs
    ): stdClass {
        // Actualiza las propiedades del modelo
        $modelo->registros = $new_array;
        $modelo->n_registros = $n_registros;
        $modelo->sql = $consulta;

        // Crea el objeto con los datos resultantes
        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = $n_registros;
        $data->sql = $consulta;
        $data->totales = $totales_rs;

        return $data;
    }


    /**
     * REG
     * Maqueta un arreglo de registros procesando cada fila obtenida de una consulta SQL.
     *
     * Este método:
     * 1. Convierte cada fila del resultado de la consulta (`PDOStatement`) en un array.
     * 2. Ajusta cada fila para incluir valores desencriptados y registros hijos, si están configurados.
     * 3. Retorna un arreglo con las filas procesadas.
     *
     * @param array $campos_encriptados Lista de columnas que contienen valores encriptados y deben ser desencriptados.
     * @param modelo_base $modelo_base Instancia del modelo base que se utiliza para procesar los registros hijos.
     * @param array $modelos_hijos Configuración de modelos hijos que se utilizarán para agregar datos relacionados.
     *                             - La clave es el nombre del modelo hijo.
     *                             - El valor es un array con configuraciones como `nombre_estructura`, `namespace_model`, etc.
     * @param PDOStatement $r_sql Resultado de la consulta SQL ejecutada, obtenido como un objeto `PDOStatement`.
     *
     * @return array
     *   - Retorna un arreglo de registros procesados, con valores desencriptados y registros hijos.
     *   - Retorna un arreglo de error si ocurre algún problema durante la ejecución.
     *
     * @example
     *  Ejemplo 1: Procesar registros de una consulta con desencriptación y modelos hijos
     *  -------------------------------------------------------------------------------
     *  $campos_encriptados = ['nombre', 'email'];
     *  $modelo_base = new modelo_base($link);
     *  $modelos_hijos = [
     *      'detalle_factura' => [
     *          'nombre_estructura' => 'detalles',
     *          'namespace_model' => 'gamboamartin\\facturacion\\models',
     *          'filtros' => ['factura_id' => 'id'],
     *          'filtros_con_valor' => ['activo' => 'SI']
     *      ]
     *  ];
     *  $r_sql = $pdo->query("SELECT * FROM factura");
     *
     *  $resultado = $this->maqueta_arreglo_registros(
     *      campos_encriptados: $campos_encriptados,
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      r_sql: $r_sql
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     [
     *  //         'id' => 1,
     *  //         'nombre' => 'NombreDesencriptado',
     *  //         'email' => 'EmailDesencriptado',
     *  //         'detalles' => [
     *  //             ['id' => 101, 'factura_id' => 1, 'activo' => 'SI'],
     *  //             ['id' => 102, 'factura_id' => 1, 'activo' => 'SI']
     *  //         ]
     *  //     ],
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 2: Procesar registros sin modelos hijos
     *  -------------------------------------------------
     *  $campos_encriptados = ['nombre'];
     *  $modelo_base = new modelo_base($link);
     *  $modelos_hijos = [];
     *  $r_sql = $pdo->query("SELECT * FROM usuarios");
     *
     *  $resultado = $this->maqueta_arreglo_registros(
     *      campos_encriptados: $campos_encriptados,
     *      modelo_base: $modelo_base,
     *      modelos_hijos: $modelos_hijos,
     *      r_sql: $r_sql
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     [
     *  //         'id' => 1,
     *  //         'nombre' => 'NombreDesencriptado',
     *  //         ...
     *  //     ],
     *  //     ...
     *  // ]
     */
    private function maqueta_arreglo_registros(
        array $campos_encriptados, modelo_base $modelo_base, array $modelos_hijos, PDOStatement $r_sql): array
    {
        $new_array = array();

        while ($row = $r_sql->fetchObject()) {
            $row = (array) $row;

            // Ajusta la fila actual (desencriptar valores y agregar registros hijos)
            $row_new = $this->ajusta_row_select(
                campos_encriptados: $campos_encriptados,
                modelo_base: $modelo_base,
                modelos_hijos: $modelos_hijos,
                row: $row
            );
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ajustar rows', data: $row_new);
            }

            // Agrega la fila procesada al nuevo arreglo
            $new_array[] = $row_new;
        }

        return $new_array;
    }


    /**
     * REG
     * Prepara y estructura el resultado de una consulta en un formato estándar.
     *
     * Este método:
     * 1. Inicializa los datos base del resultado utilizando `init_result_base`.
     * 2. Construye un objeto completo de resultado utilizando `result`.
     *
     * @param string $consulta La consulta SQL ejecutada.
     * @param modelo_base $modelo El modelo base que contiene la información de los campos y datos relacionados.
     * @param int $n_registros Número total de registros procesados.
     * @param array $new_array Array con los registros procesados.
     * @param stdClass $totales_rs Objeto con los totales acumulados para las columnas especificadas.
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con los siguientes campos si el proceso es exitoso:
     *     - `registros`: Array con los registros procesados.
     *     - `n_registros`: Número total de registros procesados.
     *     - `sql`: La consulta SQL ejecutada.
     *     - `campos_entidad`: Los campos de la entidad del modelo.
     *     - `totales`: Objeto con los totales acumulados.
     *     - `registros_obj`: Array con los registros procesados como objetos.
     *   - Retorna un arreglo de error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Generación exitosa de resultado
     *  ------------------------------------------
     *  $consulta = "SELECT * FROM productos";
     *  $modelo = new modelo_base($link);
     *  $modelo->campos_entidad = ['id', 'nombre', 'precio'];
     *  $n_registros = 10;
     *  $new_array = [
     *      ['id' => 1, 'nombre' => 'Producto A', 'precio' => 100],
     *      ['id' => 2, 'nombre' => 'Producto B', 'precio' => 200],
     *  ];
     *  $totales_rs = new stdClass();
     *  $totales_rs->precio = 300;
     *
     *  $resultado = $this->maqueta_result($consulta, $modelo, $n_registros, $new_array, $totales_rs);
     *  // $resultado contendrá un objeto con los datos del resultado:
     *  // {
     *  //     "registros": [...],
     *  //     "n_registros": 10,
     *  //     "sql": "SELECT * FROM productos",
     *  //     "campos_entidad": ["id", "nombre", "precio"],
     *  //     "totales": {"precio": 300},
     *  //     "registros_obj": [
     *  //         {"id": 1, "nombre": "Producto A", "precio": 100},
     *  //         {"id": 2, "nombre": "Producto B", "precio": 200}
     *  //     ]
     *  // }
     *
     * @example
     *  Ejemplo 2: Error durante la inicialización de resultados
     *  --------------------------------------------------------
     *  $consulta = "";
     *  $resultado = $this->maqueta_result($consulta, $modelo, $n_registros, $new_array, $totales_rs);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => "Error al parsear resultado",
     *  //   'data' => ...
     *  // ]
     */
    private function maqueta_result(
        string $consulta,
        modelo_base $modelo,
        int $n_registros,
        array $new_array,
        stdClass $totales_rs
    ): array|stdClass {
        // Inicializa los datos base del resultado
        $init = $this->init_result_base(
            consulta: $consulta,
            modelo: $modelo,
            n_registros: $n_registros,
            new_array: $new_array,
            totales_rs: $totales_rs
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al parsear resultado",
                data: $init
            );
        }

        // Construye el objeto completo del resultado
        $data = $this->result(
            consulta: $consulta,
            modelo: $modelo,
            n_registros: $n_registros,
            new_array: $new_array,
            totales_rs: $totales_rs
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al parsear registros",
                data: $new_array
            );
        }

        return $data;
    }


    /**
     * REG
     * Procesa registros obtenidos de una consulta SQL para su envío, desencriptando campos
     * y agregando registros hijos relacionados.
     *
     * Este método:
     * 1. Genera la configuración de los modelos hijos asociados al modelo base.
     * 2. Maqueta los registros obtenidos de la consulta, desencriptando campos y agregando
     *    registros hijos según sea necesario.
     * 3. Retorna un arreglo de registros procesados y listos para envío.
     *
     * @param array $campos_encriptados Lista de campos que contienen valores encriptados que deben ser desencriptados.
     * @param modelo_base $modelo_base Instancia del modelo base que contiene la configuración de los modelos hijos.
     * @param PDOStatement $r_sql Resultado de la consulta SQL, como un objeto `PDOStatement`.
     *
     * @return array
     *   - Retorna un arreglo con los registros procesados, incluyendo valores desencriptados y datos de modelos hijos.
     *   - Si ocurre un error durante el proceso, retorna un arreglo de error con detalles del problema.
     *
     * @example
     *  Ejemplo 1: Procesar registros con campos encriptados y modelos hijos
     *  --------------------------------------------------------------------
     *  $campos_encriptados = ['nombre', 'email'];
     *  $modelo_base = new modelo_base($link);
     *  $r_sql = $pdo->query("SELECT * FROM factura");
     *
     *  $resultado = $this->parsea_registros_envio(
     *      campos_encriptados: $campos_encriptados,
     *      modelo_base: $modelo_base,
     *      r_sql: $r_sql
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     [
     *  //         'id' => 1,
     *  //         'nombre' => 'NombreDesencriptado',
     *  //         'email' => 'EmailDesencriptado',
     *  //         'detalles' => [
     *  //             ['id' => 101, 'factura_id' => 1, 'activo' => 'SI'],
     *  //             ...
     *  //         ]
     *  //     ],
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 2: Procesar registros sin modelos hijos
     *  ------------------------------------------------
     *  $campos_encriptados = ['nombre'];
     *  $modelo_base = new modelo_base($link);
     *  $r_sql = $pdo->query("SELECT * FROM usuarios");
     *
     *  $resultado = $this->parsea_registros_envio(
     *      campos_encriptados: $campos_encriptados,
     *      modelo_base: $modelo_base,
     *      r_sql: $r_sql
     *  );
     *
     *  // Resultado:
     *  // [
     *  //     [
     *  //         'id' => 1,
     *  //         'nombre' => 'NombreDesencriptado',
     *  //         ...
     *  //     ],
     *  //     ...
     *  // ]
     *
     * @throws array Si ocurre un error al generar modelos hijos o procesar registros, se retorna un arreglo con detalles del error.
     */
    private function parsea_registros_envio(
        array $campos_encriptados, modelo_base $modelo_base, PDOStatement $r_sql): array
    {
        // Genera la configuración de los modelos hijos asociados al modelo base
        $modelos_hijos = $this->genera_modelos_hijos(modelo: $modelo_base);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al generar modelo", data: $modelos_hijos);
        }

        // Procesa los registros obtenidos, desencriptando campos y agregando registros hijos
        $new_array = $this->maqueta_arreglo_registros(
            campos_encriptados: $campos_encriptados,
            modelo_base: $modelo_base,
            modelos_hijos: $modelos_hijos,
            r_sql: $r_sql
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar arreglo con registros', data: $new_array);
        }

        return $new_array;
    }


    /**
     * REG
     * Genera un objeto de resultado con la información procesada de una consulta.
     *
     * Este método:
     * 1. Crea un objeto `stdClass` que incluye los registros, el número de registros, la consulta SQL,
     *    los campos de la entidad y los totales acumulados.
     * 2. Convierte los registros procesados en objetos y los asigna a una propiedad adicional.
     *
     * @param string $consulta La consulta SQL ejecutada.
     * @param modelo_base $modelo El modelo que contiene los campos de la entidad y la estructura de datos.
     * @param int $n_registros Número total de registros procesados.
     * @param array $new_array Array con los registros procesados.
     * @param stdClass $totales_rs Objeto con los totales acumulados para las columnas especificadas.
     *
     * @return stdClass
     *   - Retorna un objeto con los siguientes campos:
     *     - `registros`: Array con los registros procesados.
     *     - `n_registros`: Número total de registros procesados.
     *     - `sql`: La consulta SQL ejecutada.
     *     - `campos_entidad`: Los campos de la entidad del modelo.
     *     - `totales`: Objeto con los totales acumulados.
     *     - `registros_obj`: Array con los registros procesados como objetos.
     *
     * @example
     *  Ejemplo 1: Generación de resultados exitosos
     *  --------------------------------------------
     *  $consulta = "SELECT * FROM usuarios";
     *  $modelo = new modelo_base($link);
     *  $modelo->campos_entidad = ['id', 'nombre', 'email'];
     *  $n_registros = 5;
     *  $new_array = [
     *      ['id' => 1, 'nombre' => 'Juan', 'email' => 'juan@example.com'],
     *      ['id' => 2, 'nombre' => 'Ana', 'email' => 'ana@example.com'],
     *  ];
     *  $totales_rs = new stdClass();
     *  $totales_rs->email = 2;
     *
     *  $resultado = $this->result($consulta, $modelo, $n_registros, $new_array, $totales_rs);
     *  // $resultado contendrá un objeto con los datos:
     *  // {
     *  //     "registros": [...],
     *  //     "n_registros": 5,
     *  //     "sql": "SELECT * FROM usuarios",
     *  //     "campos_entidad": ["id", "nombre", "email"],
     *  //     "totales": {"email": 2},
     *  //     "registros_obj": [
     *  //         {"id": 1, "nombre": "Juan", "email": "juan@example.com"},
     *  //         {"id": 2, "nombre": "Ana", "email": "ana@example.com"}
     *  //     ]
     *  // }
     *
     * @throws void Este método no genera excepciones directas, pero puede devolver errores estructurados.
     */
    private function result(
        string $consulta,
        modelo_base $modelo,
        int $n_registros,
        array $new_array,
        stdClass $totales_rs
    ): stdClass {
        // Obtiene los campos de la entidad del modelo
        $campos_entidad = $modelo->campos_entidad;

        // Crea el objeto base para los datos
        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = (int)$n_registros;
        $data->sql = $consulta;
        $data->campos_entidad = $campos_entidad;
        $data->totales = $totales_rs;

        // Convierte los registros a objetos y los asigna
        $data->registros_obj = array();
        foreach ($data->registros as $row) {
            $row_obj = (object)$row;
            $data->registros_obj[] = $row_obj;
        }

        return $data;
    }



    /**
     * REG
     * Ejecuta una consulta SQL, procesa los registros y calcula los totales de columnas específicas.
     *
     * Este método:
     * 1. Valida que la consulta SQL no esté vacía.
     * 2. Ejecuta la consulta SQL utilizando el modelo proporcionado.
     * 3. Procesa los registros obtenidos para desencriptar valores y generar datos de registros hijos.
     * 4. Calcula los totales acumulados para las columnas especificadas.
     * 5. Retorna un objeto con los resultados, los registros procesados, el número de registros y los totales calculados.
     *
     * @param array $campos_encriptados Array de nombres de campos que deben desencriptarse en los registros.
     * @param array $columnas_totales Array de nombres de columnas para las cuales se calcularán los totales.
     *                                Cada elemento debe ser un string no vacío que represente una columna.
     * @param string $consulta Consulta SQL a ejecutar. No debe estar vacía.
     * @param modelo_base $modelo Instancia del modelo que ejecutará la consulta.
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con los siguientes campos:
     *     - `result`: Resultado completo de la ejecución de la consulta.
     *     - `r_sql`: Objeto PDOStatement con los resultados SQL originales.
     *     - `new_array`: Array con los registros procesados.
     *     - `n_registros`: Número de registros procesados.
     *     - `totales`: Objeto con los totales acumulados para las columnas especificadas.
     *   - Retorna un arreglo de error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Ejecución exitosa con columnas para totales
     *  -------------------------------------------------------
     *  $campos_encriptados = ['nombre', 'apellido'];
     *  $columnas_totales = ['subtotal', 'total'];
     *  $consulta = 'SELECT * FROM facturas';
     *  $modelo = new modelo_base($link);
     *
     *  $resultado = $this->result_sql($campos_encriptados, $columnas_totales, $consulta, $modelo);
     *  // $resultado será un objeto `stdClass` con los resultados procesados y los totales acumulados.
     *
     * @example
     *  Ejemplo 2: Error por consulta vacía
     *  -----------------------------------
     *  $campos_encriptados = ['nombre', 'apellido'];
     *  $columnas_totales = ['subtotal', 'total'];
     *  $consulta = '';
     *  $modelo = new modelo_base($link);
     *
     *  $resultado = $this->result_sql($campos_encriptados, $columnas_totales, $consulta, $modelo);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error consulta vacia',
     *  //   'data' => ' tabla: nombre_de_la_tabla',
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error en la ejecución de la consulta
     *  -----------------------------------------------
     *  $campos_encriptados = ['nombre', 'apellido'];
     *  $columnas_totales = ['subtotal', 'total'];
     *  $consulta = 'SELECT * FROM tabla_inexistente';
     *  $modelo = new modelo_base($link);
     *
     *  $resultado = $this->result_sql($campos_encriptados, $columnas_totales, $consulta, $modelo);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al ejecutar sql',
     *  //   'data' => [...],
     *  //   ...
     *  // ]
     *
     * @throws array Si ocurre un error durante la validación, ejecución o procesamiento de los registros.
     */
    private function result_sql(
        array $campos_encriptados, array $columnas_totales, string $consulta, modelo_base $modelo): array|stdClass
    {
        // Validación de la consulta
        $consulta = trim($consulta);
        if ($consulta === '') {
            return $this->error->error(
                mensaje: "Error consulta vacia",
                data: $consulta . ' tabla: ' . $modelo->tabla,
                es_final: true
            );
        }

        // Ejecución de la consulta
        $result = $modelo->ejecuta_sql(consulta: $consulta);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }

        // Procesamiento de los registros
        $r_sql = $result->result;
        $new_array = $this->parsea_registros_envio(
            campos_encriptados: $campos_encriptados,
            modelo_base: $modelo,
            r_sql: $r_sql
        );
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $new_array);
        }

        // Cálculo de totales
        $totales_rs = $this->totales_rs(
            columnas_totales: $columnas_totales,
            new_array: $new_array
        );
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear totales_rs", data: $totales_rs);
        }

        // Preparación del resultado
        $n_registros = $r_sql->rowCount();
        $r_sql->closeCursor();

        $data = new stdClass();
        $data->result = $result;
        $data->r_sql = $r_sql;
        $data->new_array = $new_array;
        $data->n_registros = $n_registros;
        $data->totales = $totales_rs;

        return $data;
    }


    /**
     * REG
     * Acumula el total de un campo específico de un registro en un objeto de totales.
     *
     * Este método:
     * 1. Valida que el campo existe, no está vacío y contiene un número válido en el registro.
     * 2. Verifica si el campo ya existe en el objeto de totales `$totales_rs`.
     * 3. Si no existe, inicializa el valor del campo en el objeto de totales a 0.
     * 4. Suma el valor del campo en el registro al campo correspondiente en el objeto de totales.
     *
     * @param string $campo Nombre del campo que se debe acumular.
     * @param array $row Registro que contiene el campo y su valor.
     * @param stdClass $totales_rs Objeto que almacena los totales acumulados.
     *
     * @return stdClass|array
     *   - Retorna el objeto `$totales_rs` actualizado si todo es válido.
     *   - Retorna un arreglo de error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Acumular un valor en un objeto de totales
     *  ----------------------------------------------------
     *  $campo = 'total';
     *  $row = ['total' => 150.50, 'nombre' => 'Factura 1'];
     *  $totales_rs = new stdClass();
     *  $totales_rs->total = 200.00;
     *
     *  $resultado = $this->total_rs_acumula($campo, $row, $totales_rs);
     *  // $resultado será:
     *  // {
     *  //     "total": 350.50
     *  // }
     *
     * @example
     *  Ejemplo 2: Inicializar un campo no existente en totales
     *  --------------------------------------------------------
     *  $campo = 'subtotal';
     *  $row = ['subtotal' => 100.00];
     *  $totales_rs = new stdClass();
     *
     *  $resultado = $this->total_rs_acumula($campo, $row, $totales_rs);
     *  // $resultado será:
     *  // {
     *  //     "subtotal": 100.00
     *  // }
     *
     * @example
     *  Ejemplo 3: Error en la validación del campo
     *  -------------------------------------------
     *  $campo = 'total';
     *  $row = ['total' => 'NoUnNumero'];
     *  $totales_rs = new stdClass();
     *  $totales_rs->total = 0;
     *
     *  $resultado = $this->total_rs_acumula($campo, $row, $totales_rs);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error row[total] no es un numero valido',
     *  //   'data' => ['total' => 'NoUnNumero'],
     *  //   ...
     *  // ]
     *
     * @throws array Si el campo no es válido, no existe en el registro, o no contiene un número válido.
     */
    private function total_rs_acumula(string $campo, array $row, stdClass $totales_rs): stdClass|array
    {
        // Valida que el campo existe y es numérico en el registro
        $valida = $this->valida_totales(campo: $campo, row: $row);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar row', data: $valida);
        }

        // Inicializa el campo en el objeto de totales si no existe
        if (!isset($totales_rs->$campo)) {
            $totales_rs->$campo = 0;
        }

        // Suma el valor del campo al total acumulado
        $totales_rs->$campo += $row[$campo];

        return $totales_rs;
    }


    /**
     * REG
     * Calcula y acumula el total de un campo específico en un conjunto de registros.
     *
     * Este método:
     * 1. Inicializa el valor del campo en `$totales_rs` con 0.
     * 2. Llama al método `totales_rs_acumula` para iterar sobre los registros y sumar los valores del campo.
     * 3. Devuelve el objeto `$totales_rs` actualizado con el total acumulado del campo.
     *
     * @param string $campo Nombre del campo que se desea calcular y acumular.
     * @param array $new_array Conjunto de registros que contienen el campo a acumular.
     *                         Cada registro debe ser un array.
     * @param stdClass $totales_rs Objeto que almacena los totales acumulados.
     *
     * @return array|stdClass
     *   - Retorna el objeto `$totales_rs` actualizado con el total acumulado del campo.
     *   - Retorna un arreglo de error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Calcular el total de un campo en múltiples registros
     *  --------------------------------------------------------------
     *  $campo = 'monto';
     *  $new_array = [
     *      ['monto' => 100.50, 'nombre' => 'Registro 1'],
     *      ['monto' => 200.75, 'nombre' => 'Registro 2']
     *  ];
     *  $totales_rs = new stdClass();
     *
     *  $resultado = $this->total_rs_campo($campo, $new_array, $totales_rs);
     *  // $resultado será:
     *  // {
     *  //     "monto": 301.25
     *  // }
     *
     * @example
     *  Ejemplo 2: Campo inicializado en un objeto vacío
     *  ------------------------------------------------
     *  $campo = 'subtotal';
     *  $new_array = [
     *      ['subtotal' => 50.00],
     *      ['subtotal' => 150.00]
     *  ];
     *  $totales_rs = new stdClass();
     *
     *  $resultado = $this->total_rs_campo($campo, $new_array, $totales_rs);
     *  // $resultado será:
     *  // {
     *  //     "subtotal": 200.00
     *  // }
     *
     * @example
     *  Ejemplo 3: Error al calcular el total por datos no válidos
     *  ----------------------------------------------------------
     *  $campo = 'total';
     *  $new_array = [
     *      ['total' => 100.50],
     *      ['total' => 'NoUnNumero']
     *  ];
     *  $totales_rs = new stdClass();
     *
     *  $resultado = $this->total_rs_campo($campo, $new_array, $totales_rs);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error row[total] no es un numero valido',
     *  //   'data' => ['total' => 'NoUnNumero'],
     *  //   ...
     *  // ]
     *
     * @throws array Si el campo no es válido o si algún registro tiene datos no numéricos en el campo.
     */
    private function total_rs_campo(string $campo, array $new_array, stdClass $totales_rs): array|stdClass
    {
        // Valida que el nombre del campo no esté vacío
        $campo = trim($campo);
        if ($campo === '') {
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
        }

        // Inicializa el campo en el objeto de totales
        $totales_rs->$campo = 0;

        // Acumula los valores del campo en el objeto de totales
        $totales_rs = $this->totales_rs_acumula(campo: $campo, new_array: $new_array, totales_rs: $totales_rs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error acumular total', data: $totales_rs);
        }

        return $totales_rs;
    }


    /**
     * REG
     * Calcula los totales acumulados para un conjunto de columnas en un arreglo de registros.
     *
     * Este método:
     * 1. Inicializa un objeto `stdClass` para almacenar los totales de cada columna.
     * 2. Itera sobre el array de columnas proporcionado.
     * 3. Para cada columna, llama al método `total_rs_campo` para calcular el total acumulado en los registros.
     * 4. Retorna un objeto `stdClass` con los totales acumulados para cada columna.
     *
     * @param array $columnas_totales Array de nombres de columnas para las cuales se calcularán los totales.
     *                                Cada elemento debe ser un string no vacío que represente una columna.
     * @param array $new_array Conjunto de registros que contienen las columnas a acumular.
     *                         Cada registro debe ser un array con valores numéricos en las columnas especificadas.
     *
     * @return stdClass|array
     *   - Retorna un objeto `stdClass` con los totales acumulados para cada columna.
     *   - Retorna un arreglo de error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Calcular totales para múltiples columnas
     *  ---------------------------------------------------
     *  $columnas_totales = ['subtotal', 'impuestos', 'total'];
     *  $new_array = [
     *      ['subtotal' => 100.00, 'impuestos' => 16.00, 'total' => 116.00],
     *      ['subtotal' => 200.00, 'impuestos' => 32.00, 'total' => 232.00],
     *  ];
     *
     *  $resultado = $this->totales_rs($columnas_totales, $new_array);
     *  // $resultado será:
     *  // {
     *  //     "subtotal": 300.00,
     *  //     "impuestos": 48.00,
     *  //     "total": 348.00
     *  // }
     *
     * @example
     *  Ejemplo 2: Error por columna vacía en `$columnas_totales`
     *  ----------------------------------------------------------
     *  $columnas_totales = ['subtotal', '', 'total'];
     *  $new_array = [
     *      ['subtotal' => 100.00, 'total' => 116.00],
     *      ['subtotal' => 200.00, 'total' => 232.00],
     *  ];
     *
     *  $resultado = $this->totales_rs($columnas_totales, $new_array);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error campo esta vacio',
     *  //     'data' => '',
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por valor no numérico en los registros
     *  -------------------------------------------------------
     *  $columnas_totales = ['subtotal', 'total'];
     *  $new_array = [
     *      ['subtotal' => 100.00, 'total' => 'NoUnNumero'],
     *      ['subtotal' => 200.00, 'total' => 232.00],
     *  ];
     *
     *  $resultado = $this->totales_rs($columnas_totales, $new_array);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error row[total] no es un numero valido',
     *  //     'data' => ['total' => 'NoUnNumero'],
     *  //     ...
     *  // ]
     *
     * @throws array Si alguna columna es inválida o si algún registro tiene datos no numéricos en las columnas especificadas.
     */
    private function totales_rs(array $columnas_totales, array $new_array): stdClass|array
    {
        // Inicializa el objeto para almacenar los totales
        $totales_rs = new stdClass();

        // Itera sobre las columnas para calcular los totales
        foreach ($columnas_totales as $campo) {
            $campo = trim($campo);

            // Valida que el nombre de la columna no esté vacío
            if ($campo === '') {
                return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
            }

            // Calcula y acumula el total para la columna
            $totales_rs = $this->total_rs_campo(campo: $campo, new_array: $new_array, totales_rs: $totales_rs);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error acumular total', data: $totales_rs);
            }
        }

        return $totales_rs;
    }


    /**
     * REG
     * Acumula los valores de un campo específico para un conjunto de registros.
     *
     * Este método:
     * 1. Valida que el nombre del campo no esté vacío.
     * 2. Itera sobre un array de registros (`$new_array`) y valida que cada registro sea un array.
     * 3. Para cada registro, valida la existencia y validez del campo a acumular.
     * 4. Suma el valor del campo de cada registro al total acumulado en `$totales_rs`.
     *
     * @param string $campo Nombre del campo que se desea acumular.
     * @param array $new_array Conjunto de registros que contienen el campo a acumular.
     *                         Cada registro debe ser un array.
     * @param stdClass $totales_rs Objeto que almacena los totales acumulados.
     *
     * @return array|stdClass
     *   - Retorna el objeto `$totales_rs` actualizado con el total acumulado del campo.
     *   - Retorna un arreglo de error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Acumular valores de un campo en múltiples registros
     *  --------------------------------------------------------------
     *  $campo = 'monto';
     *  $new_array = [
     *      ['monto' => 100.50, 'nombre' => 'Registro 1'],
     *      ['monto' => 200.75, 'nombre' => 'Registro 2']
     *  ];
     *  $totales_rs = new stdClass();
     *
     *  $resultado = $this->totales_rs_acumula($campo, $new_array, $totales_rs);
     *  // $resultado será:
     *  // {
     *  //     "monto": 301.25
     *  // }
     *
     * @example
     *  Ejemplo 2: Inicializar y acumular valores en un objeto vacío
     *  ------------------------------------------------------------
     *  $campo = 'subtotal';
     *  $new_array = [
     *      ['subtotal' => 50.00],
     *      ['subtotal' => 150.00]
     *  ];
     *  $totales_rs = new stdClass();
     *
     *  $resultado = $this->totales_rs_acumula($campo, $new_array, $totales_rs);
     *  // $resultado será:
     *  // {
     *  //     "subtotal": 200.00
     *  // }
     *
     * @example
     *  Ejemplo 3: Error en un registro no válido
     *  -----------------------------------------
     *  $campo = 'total';
     *  $new_array = [
     *      ['total' => 100.50],
     *      ['total' => 'NoUnNumero']
     *  ];
     *  $totales_rs = new stdClass();
     *
     *  $resultado = $this->totales_rs_acumula($campo, $new_array, $totales_rs);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error row[total] no es un numero valido',
     *  //   'data' => ['total' => 'NoUnNumero'],
     *  //   ...
     *  // ]
     *
     * @throws array Si el campo no es válido, algún registro no es un array, o un valor no es numérico.
     */
    private function totales_rs_acumula(string $campo, array $new_array, stdClass $totales_rs): array|stdClass
    {
        // Valida que el nombre del campo no esté vacío
        $campo = trim($campo);
        if ($campo === '') {
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
        }

        // Itera sobre los registros para acumular los valores del campo
        foreach ($new_array as $row) {
            if (!is_array($row)) {
                return $this->error->error(mensaje: 'Error row debe ser un array', data: $row, es_final: true);
            }

            // Valida el campo en cada registro
            $valida = $this->valida_totales(campo: $campo, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar row', data: $valida);
            }

            // Acumula el valor del campo en el objeto de totales
            $totales_rs = $this->total_rs_acumula(campo: $campo, row: $row, totales_rs: $totales_rs);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error acumular total', data: $totales_rs);
            }
        }

        return $totales_rs;
    }



    /**
     * REG
     * Valida los totales en un campo específico dentro de un registro.
     *
     * Este método:
     * 1. Verifica que el nombre del campo no esté vacío.
     * 2. Confirma que el campo existe en el arreglo `$row`.
     * 3. Asegura que el valor del campo sea un número válido.
     *
     * @param string $campo Nombre del campo que debe ser validado.
     * @param array $row Registro que contiene el campo a validar.
     *
     * @return true|array
     *   - Retorna `true` si el campo es válido.
     *   - Retorna un arreglo de error si ocurre algún problema durante la validación.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  $campo = 'total';
     *  $row = ['total' => 100.50, 'nombre' => 'Factura'];
     *
     *  $resultado = $this->valida_totales($campo, $row);
     *  // $resultado será `true` ya que el campo `total` existe y contiene un número válido.
     *
     * @example
     *  Ejemplo 2: Campo vacío
     *  -----------------------
     *  $campo = '';
     *  $row = ['total' => 100.50];
     *
     *  $resultado = $this->valida_totales($campo, $row);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error campo esta vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Campo no existe en el registro
     *  ------------------------------------------
     *  $campo = 'subtotal';
     *  $row = ['total' => 100.50];
     *
     *  $resultado = $this->valida_totales($campo, $row);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error row[subtotal] NO EXISTE',
     *  //   'data' => ['total' => 100.50],
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 4: Valor del campo no es un número válido
     *  -------------------------------------------------
     *  $campo = 'total';
     *  $row = ['total' => 'NoUnNumero'];
     *
     *  $resultado = $this->valida_totales($campo, $row);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error row[total] no es un numero valido',
     *  //   'data' => ['total' => 'NoUnNumero'],
     *  //   ...
     *  // ]
     *
     * @throws array Si el campo está vacío, no existe en el registro, o no es un número válido.
     */
    private function valida_totales(string $campo, array $row): true|array
    {
        $campo = trim($campo);
        if ($campo === '') {
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
        }
        if (!isset($row[$campo])) {
            return $this->error->error(mensaje: 'Error row[' . $campo . '] NO EXISTE', data: $row, es_final: true);
        }
        if (!is_numeric($row[$campo])) {
            return $this->error->error(mensaje: 'Error row[' . $campo . '] no es un numero valido', data: $row, es_final: true);
        }
        return true;
    }


}
