<?php
namespace base\orm;
use gamboamartin\administrador\modelado\params_sql;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class filtros{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * REG
     * Genera y prepara el complemento de filtros para la consulta SQL.
     *
     * Este método toma un objeto de filtros ($complemento) y, utilizando la información del modelo
     * ($modelo), realiza una serie de procesos para limpiar y configurar los parámetros necesarios
     * para la generación de la sentencia SQL completa. El proceso se realiza en tres pasos:
     *
     * 1. **Limpieza de Filtros**
     *    Se invoca el método {@see where::limpia_filtros()} pasando el objeto de filtros y las
     *    columnas extra definidas en el modelo (almacenadas en `$modelo->columnas_extra`). Esto asegura
     *    que todas las claves requeridas para los filtros estén definidas, asignando un valor vacío (`""`)
     *    a aquellas que no existan.
     *
     * 2. **Inicialización de Parámetros SQL**
     *    Se llama al método {@see where::init_params_sql()} con el objeto de filtros ya limpiado y las keys
     *    de filtro definidas en `$modelo->keys_data_filter`. Esto inicializa los parámetros SQL (como offset,
     *    group_by, order, limit, etc.) en el complemento, preparándolo para la integración en la consulta.
     *
     * 3. **Inicialización del Complemento**
     *    Finalmente, se utiliza el método {@see inicializacion::inicializa_complemento()} para garantizar que el
     *    objeto de filtros cuente con todas las propiedades necesarias (por ejemplo, 'filtro_especial',
     *    'filtro_extra', 'filtro_fecha', 'filtro_rango', 'in', 'not_in', 'sentencia' y 'sql_extra').
     *
     * Si ocurre un error en cualquiera de estos pasos, el método retorna un array con la información
     * detallada del error utilizando el manejador de errores.
     *
     * @param stdClass $complemento Objeto que contiene los filtros previos a aplicar. Este objeto puede tener propiedades
     *                              parciales como 'filtro_especial', 'filtro_extra', etc., las cuales serán completadas.
     * @param modelo   $modelo       Instancia del modelo en ejecución que provee las configuraciones necesarias,
     *                              entre ellas:
     *                              - **columnas_extra**: Un array de columnas (o alias) extra utilizados en los filtros.
     *                              - **keys_data_filter**: Un array de keys que se usarán para identificar los filtros.
     *
     * @return array|stdClass Devuelve el objeto $complemento con los filtros limpios e inicializados, listo para integrarse
     *                        en una sentencia SQL. Si ocurre algún error, se retorna un array con la información del error.
     *
     * @example Ejemplo 1: Complemento con filtros parcialmente definidos
     * <pre>
     * // Supongamos que tenemos un objeto complemento con solo el filtro especial definido:
     * $complemento = new stdClass();
     * $complemento->filtro_especial = "productos.precio > '100'";
     *
     * // Y un modelo que define:
     * $modelo->columnas_extra = ['productos.precio', 'productos.nombre'];
     * $modelo->keys_data_filter = [
     *     'filtro_especial', 'filtro_extra', 'filtro_fecha',
     *     'filtro_rango', 'in', 'not_in', 'sentencia', 'sql_extra'
     * ];
     *
     * // Al llamar al método:
     * $complemento_inicializado = $this->complemento($complemento, $modelo);
     *
     * // Se espera que $complemento_inicializado sea un objeto stdClass con las siguientes propiedades:
     * // - filtro_especial: "productos.precio > '100'" (valor original)
     * // - filtro_extra: "" (inicializado a cadena vacía)
     * // - filtro_fecha: "" (inicializado a cadena vacía)
     * // - filtro_rango: "" (inicializado a cadena vacía)
     * // - in: "" (inicializado a cadena vacía)
     * // - not_in: "" (inicializado a cadena vacía)
     * // - sentencia: "" (inicializado a cadena vacía)
     * // - sql_extra: "" (inicializado a cadena vacía)
     * </pre>
     *
     * @example Ejemplo 2: Error al limpiar filtros
     * <pre>
     * // Si el objeto $complemento no contiene la estructura esperada, por ejemplo:
     * $complemento = new stdClass();
     * // No se definen propiedades de filtros
     *
     * $resultado = $this->complemento($complemento, $modelo);
     *
     * // Resultado esperado: Un array de error con un mensaje similar a:
     * // [
     * //   'error'   => 1,
     * //   'mensaje' => 'Error al limpiar filtros',
     * //   'data'    => (detalles del objeto complemento),
     * //   'es_final'=> true
     * // ]
     * </pre>
     */
    private function complemento(stdClass $complemento, modelo $modelo): array|stdClass
    {
        $complemento_ = (new where())->limpia_filtros(filtros: $complemento,
            keys_data_filter: $modelo->columnas_extra);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al limpiar filtros',
                data: $complemento_
            );
        }

        $complemento_r = (new where())->init_params_sql(complemento: $complemento_,
            keys_data_filter: $modelo->keys_data_filter);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al inicializar params',
                data: $complemento_r
            );
        }

        $complemento_r = $this->inicializa_complemento(complemento: $complemento_r);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al inicializar complemento',
                data: $complemento_r
            );
        }
        return $complemento_r;
    }


    /**
     * REG
     * Genera el complemento SQL completo para la ejecución de una consulta SELECT.
     *
     * Este método arma y retorna un objeto de filtros que incluye todos los fragmentos necesarios para
     * construir la cláusula WHERE y otros componentes de la consulta SQL, a partir de los parámetros que se le
     * suministran. La función realiza los siguientes pasos:
     *
     * 1. **Armado del objeto de parámetros**: Crea un objeto stdClass llamado `$params_fn` que encapsula
     *    todos los parámetros de la consulta, tales como los filtros básicos, especiales, de rango, de fecha,
     *    las cláusulas IN/NOT IN, GROUP BY, LIMIT, OFFSET, ORDER y cualquier SQL extra.
     *
     * 2. **Validación de límites**: Verifica que el valor de `$limit` y `$offset` sean mayores o iguales a 0.
     *
     * 3. **Validación del tipo de filtro**: Se utiliza el método `verifica_tipo_filtro()` del objeto `where`
     *    para confirmar que el tipo de filtro especificado en `$tipo_filtro` es válido.
     *
     * 4. **Generación de parámetros SQL**: Se invoca el método `params_sql()` del objeto `params_sql` para
     *    generar un objeto de parámetros SQL, utilizando los valores de `$group_by`, `$limit`, `$offset`, `$order`
     *    y la cláusula SQL previa (`$sql_extra`). Este objeto se almacena en la variable `$params`.
     *
     * 5. **Generación de filtros**: Se llama al método `data_filtros_full()` del objeto `where` para generar un
     *    objeto que contenga todos los fragmentos de filtros SQL (por ejemplo, filtros especiales, de rango, de fecha,
     *    etc.), utilizando además los arrays `$columnas_extra`, `$diferente_de`, `$filtro`, `$filtro_especial`,
     *    `$filtro_extra`, `$filtro_fecha`, `$filtro_rango`, `$in`, `$keys_data_filter` (definido en el modelo), `$not_in`,
     *    `$sql_extra` y `$tipo_filtro`.
     *
     * 6. **Normalización de la cláusula IN**: Se revisa que la propiedad `in` del objeto de filtros esté definida;
     *    en caso contrario, se inicializa a una cadena vacía. Además, se normaliza la cadena eliminando espacios
     *    redundantes y corrigiendo la estructura de los paréntesis.
     *
     * 7. **Integración de parámetros**: Se asigna el objeto de parámetros SQL generado (almacenado en `$params`)
     *    a la propiedad `params` del objeto de filtros.
     *
     * Finalmente, se retorna el objeto de filtros completo, que incluye la cláusula WHERE y todos los demás fragmentos
     * necesarios para ensamblar la consulta SQL final.
     *
     * @param bool   $aplica_seguridad Indica si se debe aplicar la seguridad en la consulta.
     * @param array  $diferente_de      Arreglo de condiciones para generar cláusulas "diferente de" en la consulta.
     * @param array  $filtro            Filtro básico que se aplicará en la consulta.
     * @param array  $filtro_especial   Filtros especiales, definidos como condiciones específicas (por ejemplo, operadores,
     *                                  valores y comparaciones lógicas).
     * @param array  $filtro_extra      Filtros extra que se concatenarán a la consulta.
     * @param array  $filtro_rango      Filtros que definen un rango de valores (p. ej., mediante BETWEEN). Puede tener la forma:
     *                                  - Opción 1: ['valor1' => 'valor', 'valor2' => 'valor']
     *                                  - Opción 2: ['valor1' => 'valor', 'valor2' => 'valor', 'valor_campo' => true]
     * @param array  $group_by          Arreglo con los campos para la cláusula GROUP BY.
     * @param array  $in                Arreglo que contiene los elementos para construir una cláusula IN. Debe tener la estructura:
     *                                  ['llave' => 'nombre_columna', 'values' => array(…)]
     * @param int    $limit             Número máximo de registros a mostrar.
     * @param modelo $modelo            Instancia del modelo en ejecución, la cual proporciona propiedades como
     *                                  `columnas_extra` y `keys_data_filter`.
     * @param array  $not_in            Arreglo que contiene los elementos para construir una cláusula NOT IN. Debe tener la
     *                                  estructura similar a `$in`.
     * @param int    $offset            Número de registro de inicio para la consulta.
     * @param array  $order             Arreglo asociativo que define el orden de los registros (p. ej., ['campo' => 'ASC']).
     * @param string $sql_extra         Cadena SQL extra que se integrará en la consulta, usualmente para condiciones
     *                                  adicionales.
     * @param string $tipo_filtro       Tipo de filtro a aplicar. Por ejemplo, 'numeros' para filtros exactos o 'textos' para
     *                                  filtros con comodines (%%).
     * @param array  $filtro_fecha      Filtros de fecha para la consulta. Se espera un arreglo con claves que definan
     *                                  las columnas de fecha y sus condiciones.
     *
     * @return array|stdClass Devuelve un objeto de filtros (stdClass) que contiene todas las propiedades necesarias
     *                        para ensamblar la consulta SQL final, o un array de error en caso de fallo.
     *
     * @example Ejemplo de uso:
     * <pre>
     * // Supongamos que se tiene un modelo $modelo con:
     * // $modelo->columnas_extra = ['tabla.precio', 'tabla.stock'];
     * // $modelo->keys_data_filter = ['filtro_especial', 'filtro_extra', 'filtro_fecha', 'filtro_rango', 'in', 'not_in', 'sentencia', 'sql_extra'];
     *
     * $diferente_de   = ['tabla.estado' => 'inactivo'];
     * $filtro         = ['tabla.categoria' => 'Electrónica'];
     * $filtro_especial = [
     *     'tabla.precio' => [
     *         'operador'    => '>',
     *         'valor'       => '100',
     *         'comparacion' => 'AND'
     *     ]
     * ];
     * $filtro_extra   = []; // Sin filtros extra
     * $filtro_rango   = ['tabla.stock' => ['valor1' => '10', 'valor2' => '50']];
     * $in             = ['llave' => 'tabla.id', 'values' => [1, 2, 3]];
     * $group_by       = ['tabla.categoria'];
     * $not_in         = []; // Sin NOT IN
     * $limit          = 20;
     * $offset         = 0;
     * $order          = ['tabla.precio' => 'ASC'];
     * $sql_extra      = ''; // Sin SQL extra
     * $tipo_filtro    = 'numeros';
     * $filtro_fecha   = []; // Sin filtros de fecha
     *
     * $complemento_sql = $this->complemento_sql(
     *     $aplica_seguridad = true,
     *     $diferente_de,
     *     $filtro,
     *     $filtro_especial,
     *     $filtro_extra,
     *     $filtro_rango,
     *     $group_by,
     *     $in,
     *     $limit,
     *     $modelo,
     *     $not_in,
     *     $offset,
     *     $order,
     *     $sql_extra,
     *     $tipo_filtro,
     *     $filtro_fecha
     * );
     *
     * // $complemento_sql contendrá un objeto con propiedades como:
     * // - where, sentencia, filtro_especial, filtro_rango, filtro_fecha, filtro_extra, in, not_in, diferente_de, sql_extra
     * // - params (que incluye group_by, order, limit y offset)
     * </pre>
     */
    final public function complemento_sql(
        bool $aplica_seguridad,
        array $diferente_de,
        array $filtro,
        array $filtro_especial,
        array $filtro_extra,
        array $filtro_rango,
        array $group_by,
        array $in,
        int $limit,
        modelo $modelo,
        array $not_in,
        int $offset,
        array $order,
        string $sql_extra,
        string $tipo_filtro,
        array $filtro_fecha = array()
    ): array|stdClass {
        $params_fn = new stdClass();
        $params_fn->aplica_seguridad = $aplica_seguridad;
        $params_fn->diferente_de = $diferente_de;
        $params_fn->filtro = $filtro;
        $params_fn->filtro_especial = $filtro_especial;
        $params_fn->filtro_extra = $filtro_extra;
        $params_fn->filtro_fecha = $filtro_fecha;
        $params_fn->filtro_rango = $filtro_rango;
        $params_fn->group_by = $group_by;
        $params_fn->in = $in;
        $params_fn->limit = $limit;
        $params_fn->modelo = $modelo;
        $params_fn->not_in = $not_in;
        $params_fn->offset = $offset;
        $params_fn->order = $order;
        $params_fn->sql_extra = $sql_extra;
        $params_fn->tipo_filtro = $tipo_filtro;

        if ($limit < 0) {
            return $this->error->error(
                mensaje: 'Error limit debe ser mayor o igual a 0',
                data: $params_fn,
                es_final: true
            );
        }
        if ($offset < 0) {
            return $this->error->error(
                mensaje: 'Error $offset debe ser mayor o igual a 0',
                data: $params_fn,
                es_final: true
            );
        }
        $verifica_tf = (new \gamboamartin\where\where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar tipo_filtro',
                data: $verifica_tf
            );
        }

        $params = (new params_sql())->params_sql(
            aplica_seguridad: $aplica_seguridad,
            group_by: $group_by,
            limit: $limit,
            modelo_columnas_extra: $modelo->columnas_extra,
            offset: $offset,
            order: $order,
            sql_where_previo: $sql_extra
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar parametros sql',
                data: $params
            );
        }

        $filtros = (new where())->data_filtros_full(
            columnas_extra: $modelo->columnas_extra,
            diferente_de: $diferente_de,
            filtro: $filtro,
            filtro_especial: $filtro_especial,
            filtro_extra: $filtro_extra,
            filtro_fecha: $filtro_fecha,
            filtro_rango: $filtro_rango,
            in: $in,
            keys_data_filter: $modelo->keys_data_filter,
            not_in: $not_in,
            sql_extra: $sql_extra,
            tipo_filtro: $tipo_filtro
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtros',
                data: $filtros
            );
        }

        if (!isset($filtros->in)) {
            $filtros->in = '';
        }

        // Normaliza la cadena del filtro IN, eliminando espacios redundantes y corrigiendo paréntesis.
        $filtros->in = str_replace('( (', '((', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('( (', '((', $filtros->in);
        $filtros->in = trim($filtros->in);

        $filtros->params = $params;
        return $filtros;
    }


    /**
     * REG
     * Genera la consulta SQL completa para una sentencia SELECT integrando todos los filtros.
     *
     * Este método construye la sentencia SQL final para una consulta SELECT combinando una consulta previa
     * con un complemento de filtros. El complemento, que se obtiene mediante el método {@see complemento()},
     * contiene distintos fragmentos SQL generados a partir de filtros básicos, especiales, de rango, de fecha,
     * cláusulas IN/NOT IN y otros parámetros adicionales (como GROUP BY, ORDER, LIMIT y OFFSET).
     *
     * El proceso se realiza en los siguientes pasos:
     * <ol>
     *     <li>
     *         Se recibe la consulta previa en la variable <code>$consulta</code> y se valida que no esté vacía.
     *         Si <code>$consulta</code> es una cadena vacía, se retorna un error.
     *     </li>
     *     <li>
     *         Se genera el objeto complemento de filtros completo llamando al método {@see complemento()} con
     *         el objeto <code>$complemento</code> y el modelo <code>$modelo</code>. Este complemento agrupa
     *         todos los filtros y parámetros necesarios para la consulta.
     *     </li>
     *     <li>
     *         Con el complemento completo, se construye la sentencia SQL final llamando al método privado
     *         {@see sql()}, el cual concatena la consulta previa y todos los fragmentos del complemento,
     *         normalizando la cadena final.
     *     </li>
     *     <li>
     *         La sentencia resultante se asigna a la propiedad <code>$modelo->consulta</code> y se retorna.
     *     </li>
     * </ol>
     *
     * @param stdClass $complemento Objeto que contiene los filtros y parámetros para la consulta SQL.
     *                              Este objeto se espera que contenga información generada previamente a través de
     *                              métodos internos de filtrado y complementación.
     * @param string   $consulta    Cadena SQL previa que se utilizará como base para la consulta. Por ejemplo,
     *                              podría ser una cláusula SELECT o cualquier otro fragmento de consulta.
     * @param modelo   $modelo      Instancia del modelo en ejecución, que contiene información como las columnas,
     *                              claves y otros parámetros necesarios para la generación de la consulta.
     *
     * @return string|array Devuelve la consulta SQL completa y normalizada en forma de cadena si el proceso es exitoso;
     *                      en caso de error, retorna un array con los detalles del error generado.
     *
     * @example Ejemplo 1: Consulta completa con filtros definidos
     * <pre>
     * // Suponiendo que se tiene un objeto $complemento con los siguientes valores:
     * $complemento = new stdClass();
     * $complemento->where = " WHERE estado = 'activo' ";
     * $complemento->sentencia = "SELECT * FROM usuarios";
     * $complemento->filtro_especial = " AND edad > 18";
     * $complemento->filtro_rango = "";
     * $complemento->filtro_fecha = "";
     * $complemento->filtro_extra = "";
     * $complemento->in = "";
     * $complemento->not_in = "";
     * $complemento->diferente_de = "";
     * $complemento->sql_extra = "";
     *
     * // Y que $complemento->params ya está inicializado con:
     * $complemento->params = new stdClass();
     * $complemento->params->group_by = "";
     * $complemento->params->order = " ORDER BY nombre ASC ";
     * $complemento->params->limit = " LIMIT 10 ";
     * $complemento->params->offset = " OFFSET 0 ";
     *
     * // Además, se tiene la consulta previa:
     * $consulta = "/* Consulta base *\/ ";
     *
     * // Y un modelo $modelo que contiene las configuraciones necesarias.
     *
     * // Al ejecutar:
     * $sql = $this->consulta_full_and($complemento, $consulta, $modelo);
     *
     * // Se espera que la salida sea similar a:
     * // "/* Consulta base *\/ WHERE estado = 'activo' SELECT * FROM usuarios AND edad > 18 ORDER BY nombre ASC LIMIT 10 OFFSET 0"
     * </pre>
     *
     * @example Ejemplo 2: Error por consulta previa vacía
     * <pre>
     * $complemento = new stdClass();
     * // Se omite la definición de filtros y parámetros en $complemento.
     *
     * $consulta = "   "; // Cadena vacía después de trim().
     *
     * // Al ejecutar:
     * $sql = $this->consulta_full_and($complemento, $consulta, $modelo);
     *
     * // Se retorna un error:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => "Error $consulta no puede venir vacia",
     * //   'data' => "",
     * //   'es_final' => true
     * // ]
     * </pre>
     */
    final public function consulta_full_and(stdClass $complemento, string $consulta, modelo $modelo): string|array
    {
        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(
                mensaje: 'Error $consulta no puede venir vacia',
                data: $consulta,
                es_final: true
            );
        }

        $complemento_r = $this->complemento(complemento: $complemento, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(
                mensaje:'Error al inicializar complemento',
                data:$complemento_r
            );
        }

        $sql = $this->sql(complemento: $complemento_r, consulta_previa: $consulta);
        if(errores::$error){
            return $this->error->error(
                mensaje:'Error al generar sql',
                data:$sql
            );
        }
        $sql = trim($sql);
        $modelo->consulta = $sql;
        return $modelo->consulta;
    }



    final public function filtro_children(string $tabla, int $id): array
    {
        $filtro_children = array();
        $filtro_children[$tabla.'.id'] = $id;
        return $filtro_children;
    }

    /**
     *
     * @param string $fecha
     * @param modelo_base $modelo
     * @return array
     */
    public function filtro_fecha_final(string $fecha, modelo_base $modelo): array
    {
        $name_modelo = $this->init_name_model(fecha: $fecha,modelo:  $modelo);
        if(errores::$error){
            return $this->error->error("Error al inicializa name model", $name_modelo);
        }


        $filtro[$fecha]['valor'] = $name_modelo.'.fecha_final';
        $filtro[$fecha]['operador'] = '<=';
        $filtro[$fecha]['comparacion'] = 'AND';
        $filtro[$fecha]['valor_es_campo'] = true;

        return $filtro;
    }

    /**
     *
     * @param string $fecha
     * @param modelo_base $modelo
     * @return array
     */
    public function filtro_fecha_inicial(string $fecha, modelo_base $modelo): array
    {

        $name_modelo = $this->init_name_model(fecha: $fecha,modelo:  $modelo);
        if(errores::$error){
            return $this->error->error("Error al inicializa name model", $name_modelo);
        }

        $filtro[$fecha]['valor'] = $name_modelo.'.fecha_inicial';
        $filtro[$fecha]['operador'] = '>=';
        $filtro[$fecha]['valor_es_campo'] = true;

        return $filtro;

    }



    /**
     * PRUEBAS FINALIZADAS
     * @param string $monto
     * @param string $campo
     * @param modelo_base $modelo
     * @return array
     */
    public function filtro_monto_ini(string $monto, string $campo, modelo_base $modelo): array
    {

        $data_filtro = $this->init_filtro_monto(campo: $campo,modelo:  $modelo,monto:  $monto);
        if(errores::$error){
            return $this->error->error("Error inicializa filtros", $data_filtro);
        }

        $filtro["$monto"]['valor'] = $data_filtro->tabla.'.'.$data_filtro->campo;
        $filtro["$monto"]['operador'] = '>=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }

    public function filtro_monto_fin(string $monto, string $campo, modelo_base $modelo): array
    {
        $data_filtro = $this->init_filtro_monto(campo: $campo,modelo:  $modelo,monto:  $monto);
        if(errores::$error){
            return $this->error->error("Error inicializa filtros", $data_filtro);
        }

        $filtro["$monto"]['valor'] = $data_filtro->tabla.'.'.$data_filtro->campo;
        $filtro["$monto"]['operador'] = '<=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }


    /**
     * REG
     * Inicializa el complemento de filtros asegurándose de que contenga todas las claves necesarias.
     *
     * Este método privado se encarga de obtener el conjunto de keys que se requieren para un complemento
     * (usando el método {@see keys_complemento()}) y de inicializar dichas keys en el objeto `$complemento`
     * mediante el método {@see init_complemento()}. De esta forma, se garantiza que el objeto de complemento
     * cuente con todas las propiedades necesarias (como 'filtro_especial', 'filtro_extra', 'filtro_fecha',
     * 'filtro_rango', 'in', 'not_in', 'sentencia' y 'sql_extra') para la posterior generación de sentencias SQL.
     *
     * @param stdClass $complemento Objeto que representa el complemento previo de filtros. Este objeto puede
     *                              provenir de la combinación de filtros generados en diferentes partes del sistema.
     *
     * @return stdClass|array Devuelve el objeto `$complemento` con las claves necesarias inicializadas. Si ocurre
     *                        algún error durante el proceso (por ejemplo, si la obtención de keys o la inicialización
     *                        falla), se retorna un arreglo con la información del error.
     *
     * @example Ejemplo 1: Complemento con algunos campos definidos
     * <pre>
     * // Se parte de un objeto complemento que solo tiene definido 'filtro_especial'
     * $complemento = new stdClass();
     * $complemento->filtro_especial = "productos.precio > '100'";
     *
     * // Supongamos que keys_complemento() retorna el siguiente arreglo:
     * // ['filtro_especial', 'filtro_extra', 'filtro_fecha', 'filtro_rango', 'in', 'not_in', 'sentencia', 'sql_extra']
     *
     * // Al llamar al método:
     * $complemento_inicializado = $this->inicializa_complemento($complemento);
     *
     * // Se espera que $complemento_inicializado sea un objeto stdClass con:
     * // - filtro_especial: "productos.precio > '100'" (valor original)
     * // - filtro_extra: "" (inicializado a cadena vacía)
     * // - filtro_fecha: "" (inicializado a cadena vacía)
     * // - filtro_rango: "" (inicializado a cadena vacía)
     * // - in: "" (inicializado a cadena vacía)
     * // - not_in: "" (inicializado a cadena vacía)
     * // - sentencia: "" (inicializado a cadena vacía)
     * // - sql_extra: "" (inicializado a cadena vacía)
     * </pre>
     *
     * @example Ejemplo 2: Error al obtener keys
     * <pre>
     * // Si por alguna razón ocurre un error en el método keys_complemento(), se genera un error.
     * // Por ejemplo, keys_complemento() podría retornar un error interno (esto dependerá de la implementación).
     * // En tal caso, el método devolverá un arreglo con la información del error, similar a:
     * // [
     * //     'error'   => 1,
     * //     'mensaje' => 'Error al obtener keys',
     * //     'data'    => (detalles de keys),
     * //     'es_final'=> true
     * // ]
     * </pre>
     *
     * @example Ejemplo 3: Error al inicializar complemento
     * <pre>
     * // Si el objeto $complemento recibido no permite la inicialización de alguna key (por ejemplo, alguna key es vacía),
     * // el método init_complemento() generará un error y se retornará un arreglo de error con un mensaje descriptivo.
     * $complemento = new stdClass();
     * // Supongamos que $complemento carece de una estructura válida.
     * $resultado = $this->inicializa_complemento($complemento);
     *
     * // Resultado esperado:
     * // [
     * //    'error'   => 1,
     * //    'mensaje' => 'Error al inicializar complemento',
     * //    'data'    => (información sobre el complemento),
     * //    'es_final'=> true
     * // ]
     * </pre>
     */
    private function inicializa_complemento(stdClass $complemento): stdClass|array
    {
        $keys = $this->keys_complemento();
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener keys',
                data: $keys
            );
        }

        $complemento = $this->init_complemento(complemento: $complemento, keys: $keys);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al inicializar complemento',
                data: $complemento
            );
        }
        return $complemento;
    }


    /**
     * REG
     * Inicializa los keys de un complemento para filtro.
     *
     * Este método privado se encarga de recorrer un arreglo de claves proporcionado y, para cada clave:
     * - Elimina espacios en blanco con `trim()`.
     * - Verifica que la clave no sea una cadena vacía; si lo es, retorna un error.
     * - Si la propiedad correspondiente no existe en el objeto `$complemento`, se inicializa con una cadena vacía.
     *
     * De esta manera, se garantiza que el objeto `$complemento` contenga todas las claves necesarias (incluso si
     * no se han definido previamente), facilitando la posterior construcción o integración de filtros SQL.
     *
     * @param stdClass $complemento Objeto que representa el complemento de filtros, el cual se modificará
     *                              para asegurar que contenga todas las propiedades indicadas en `$keys`.
     * @param array    $keys         Arreglo de cadenas que representan las claves que se deben inicializar en el complemento.
     *
     * @return stdClass|array Devuelve el objeto `$complemento` con las claves inicializadas. Si el arreglo `$keys`
     *                        está vacío o alguna de las claves es una cadena vacía, retorna un arreglo de error.
     *
     * @example Ejemplo 1: Inicialización exitosa de un complemento
     * <pre>
     * // Supongamos que tenemos un objeto complemento sin algunas propiedades definidas:
     * $complemento = new stdClass();
     * $complemento->filtro_especial = "productos.precio > '100'";
     * // El arreglo de keys requeridos:
     * $keys = ['filtro_especial', 'filtro_extra', 'filtro_fecha', 'filtro_rango', 'in', 'not_in', 'sentencia', 'sql_extra'];
     *
     * // Llamada al método:
     * $complemento_inicializado = $this->init_complemento($complemento, $keys);
     *
     * // Resultado esperado:
     * // $complemento_inicializado es un objeto stdClass con las siguientes propiedades:
     * // - filtro_especial: "productos.precio > '100'" (valor original)
     * // - filtro_extra: ""           (inicializado a cadena vacía)
     * // - filtro_fecha: ""           (inicializado a cadena vacía)
     * // - filtro_rango: ""           (inicializado a cadena vacía)
     * // - in: ""                     (inicializado a cadena vacía)
     * // - not_in: ""                 (inicializado a cadena vacía)
     * // - sentencia: ""              (inicializado a cadena vacía)
     * // - sql_extra: ""              (inicializado a cadena vacía)
     * </pre>
     *
     * @example Ejemplo 2: Error por keys vacíos
     * <pre>
     * // Si se pasa un arreglo vacío para keys:
     * $keys = [];
     * $resultado = $this->init_complemento($complemento, $keys);
     *
     * // Resultado esperado: Un arreglo de error similar a:
     * // [
     * //   'error'   => 1,
     * //   'mensaje' => "Error los keys de un complemento esta vacio",
     * //   'data'    => [],
     * //   'es_final'=> true
     * // ]
     * </pre>
     *
     * @example Ejemplo 3: Error por clave vacía en el arreglo keys
     * <pre>
     * // Si el arreglo keys contiene una cadena vacía:
     * $keys = ['filtro_especial', '', 'sentencia'];
     * $resultado = $this->init_complemento($complemento, $keys);
     *
     * // Resultado esperado: Un arreglo de error similar a:
     * // [
     * //   'error'   => 1,
     * //   'mensaje' => "Error el key esta vacio",
     * //   'data'    => "",
     * //   'es_final'=> true
     * // ]
     * </pre>
     */
    private function init_complemento(stdClass $complemento, array $keys): stdClass|array
    {
        if (count($keys) === 0) {
            return $this->error->error(
                mensaje: 'Error los keys de un complemento esta vacio',
                data: $keys,
                es_final: true
            );
        }
        foreach ($keys as $key) {
            $key = trim($key);
            if ($key === '') {
                return $this->error->error(
                    mensaje: 'Error el key esta vacio',
                    data: $key,
                    es_final: true
                );
            }
            if (!isset($complemento->$key)) {
                $complemento->$key = '';
            }
        }
        return $complemento;
    }


    private function init_filtro_monto(string $campo, modelo_base $modelo, float $monto): array|stdClass
    {
        if($monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }
        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);

        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }

        $data = new stdClass();
        $data->campo = $campo;
        $data->tabla = $modelo->tabla;
        return $data;

    }

    private function init_name_model(string $fecha, modelo_base $modelo): array|string
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error fecha", $valida);
        }
        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);

        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        return $modelo->tabla;
    }

    /**
     * REG
     * Obtiene las claves utilizadas para definir un complemento de filtros en una consulta SQL.
     *
     * Este método privado retorna un arreglo que contiene los nombres de las propiedades que se utilizan
     * para almacenar y organizar distintos componentes de filtros en el objeto complemento que se emplea
     * en la construcción de cláusulas WHERE para consultas SQL.
     *
     * Las claves devueltas corresponden a:
     * - **filtro_especial**: Condiciones especiales definidas para filtros (por ejemplo, condiciones complejas o subconsultas).
     * - **filtro_extra**: Filtros adicionales que se concatenan a la consulta.
     * - **filtro_fecha**: Filtros relacionados con fechas.
     * - **filtro_rango**: Filtros que definen un rango de valores (por ejemplo, mediante BETWEEN).
     * - **in**: Cláusula IN, que agrupa un conjunto de valores para filtrado.
     * - **not_in**: Cláusula NOT IN, para excluir un conjunto de valores.
     * - **sentencia**: Sentencia SQL principal o fragmento de la consulta.
     * - **sql_extra**: SQL adicional que se desea integrar en la consulta.
     *
     * @return array Retorna un arreglo con las claves definidas para el complemento de filtros.
     *
     * @example
     * <pre>
     * // Ejemplo de uso:
     * $keys = $this->keys_complemento();
     *
     * // Resultado esperado:
     * // [
     * //     'filtro_especial',
     * //     'filtro_extra',
     * //     'filtro_fecha',
     * //     'filtro_rango',
     * //     'in',
     * //     'not_in',
     * //     'sentencia',
     * //     'sql_extra'
     * // ]
     * </pre>
     */
    private function keys_complemento(): array
    {
        return array(
            'filtro_especial',
            'filtro_extra',
            'filtro_fecha',
            'filtro_rango',
            'in',
            'not_in',
            'sentencia',
            'sql_extra'
        );
    }


    /**
     * REG
     * Genera la sentencia SQL completa a partir del objeto complemento de filtros y una consulta previa.
     *
     * Este método construye la consulta SQL final concatenando la consulta previa y los distintos filtros
     * contenidos en el objeto `$complemento`. Para ello, se asegura de que las siguientes propiedades estén definidas en
     * `$complemento` y se inicializan a una cadena vacía en caso contrario:
     *
     * - `filtro_especial`
     * - `filtro_extra`
     * - `filtro_fecha`
     * - `filtro_rango`
     * - `in`
     * - `not_in`
     * - `diferente_de`
     * - `sentencia`
     * - `sql_extra`
     * - `where`
     *
     * Además, se verifica que el objeto `$complemento->params` esté definido y contenga las siguientes propiedades:
     *
     * - `group_by`
     * - `limit`
     * - `offset`
     * - `order`
     *
     * Posteriormente, el método concatena en el siguiente orden:
     *
     * 1. `$consulta_previa`
     * 2. `$complemento->where`
     * 3. `$complemento->sentencia`
     * 4. `$complemento->filtro_especial`
     * 5. `$complemento->filtro_rango`
     * 6. `$complemento->filtro_fecha`
     * 7. `$complemento->filtro_extra`
     * 8. `$complemento->in`
     * 9. `$complemento->not_in`
     * 10. `$complemento->diferente_de`
     * 11. `$complemento->sql_extra`
     * 12. Los parámetros definidos en `$complemento->params` (en el orden: `group_by`, `order`, `limit`, `offset`)
     *
     * Finalmente, se realizan varias sustituciones para limpiar espacios redundantes, como la conversión
     * de secuencias de espacios dobles a simples y la corrección de combinaciones de paréntesis.
     *
     * @param stdClass $complemento     Objeto que contiene los filtros y parámetros para la consulta SQL.
     *                                 Debe incluir (o se inicializa con) las siguientes propiedades:
     *                                 - `filtro_especial`
     *                                 - `filtro_extra`
     *                                 - `filtro_fecha`
     *                                 - `filtro_rango`
     *                                 - `in`
     *                                 - `not_in`
     *                                 - `diferente_de`
     *                                 - `sentencia`
     *                                 - `sql_extra`
     *                                 - `where`
     *                                 Además, debe tener un objeto `params` con:
     *                                 - `group_by`
     *                                 - `limit`
     *                                 - `offset`
     *                                 - `order`
     *
     * @param string   $consulta_previa Cadena SQL previa que se concatenará al inicio de la sentencia final.
     *
     * @return string Devuelve la sentencia SQL final generada, normalizada y lista para ser ejecutada.
     *
     * @example Ejemplo 1: Complemento completamente definido
     * <pre>
     * // Se define un objeto $complemento con varias propiedades:
     * $complemento = new stdClass();
     * $complemento->where           = " WHERE estado = 'activo' ";
     * $complemento->sentencia       = "SELECT * FROM usuarios";
     * $complemento->filtro_especial = " AND edad > 18";
     * $complemento->filtro_rango    = "";
     * $complemento->filtro_fecha    = "";
     * $complemento->filtro_extra    = "";
     * $complemento->in              = "";
     * $complemento->not_in          = "";
     * $complemento->diferente_de    = "";
     * $complemento->sql_extra       = "";
     *
     * // Se define también el objeto de parámetros:
     * $complemento->params = new stdClass();
     * $complemento->params->group_by = "";
     * $complemento->params->order    = " ORDER BY nombre ASC ";
     * $complemento->params->limit    = " LIMIT 10 ";
     * $complemento->params->offset   = " OFFSET 0 ";
     *
     * $consulta_previa = "/* Consulta base *\/ ";
     *
     * // Al llamar al método:
     * $sql = $this->sql($complemento, $consulta_previa);
     *
     * // Salida esperada (ajustada y sin espacios redundantes):
     * // "/* Consulta base *\/ WHERE estado = 'activo' SELECT * FROM usuarios AND edad > 18 ORDER BY nombre ASC LIMIT 10 OFFSET 0"
     * </pre>
     *
     * @example Ejemplo 2: Complemento incompleto (propiedades no definidas)
     * <pre>
     * // Si $complemento es un objeto vacío:
     * $complemento = new stdClass();
     *
     * $consulta_previa = "SELECT * FROM productos";
     *
     * $sql = $this->sql($complemento, $consulta_previa);
     *
     * // Salida esperada: La consulta se genera a partir de $consulta_previa sin agregar filtros adicionales,
     * // ya que las propiedades faltantes se inicializan como cadenas vacías:
     * // "SELECT * FROM productos"
     * </pre>
     */
    private function sql(stdClass $complemento, string $consulta_previa): string
    {
        $keys = array('filtro_especial','filtro_extra','filtro_fecha','filtro_rango','in','not_in','diferente_de',
            'sentencia', 'sql_extra','where');

        // Asegura que todas las propiedades clave estén definidas en $complemento.
        foreach ($keys as $key){
            if(!isset($complemento->$key)){
                $complemento->$key = '';
            }
        }

        // Verifica la existencia del objeto 'params' y sus propiedades.
        if(!isset($complemento->params)){
            $complemento->params = new stdClass();
        }

        $keys = array('group_by','limit','offset','order');
        foreach ($keys as $key){
            if(!isset($complemento->params->$key)){
                $complemento->params->$key = '';
            }
        }

        // Construye la sentencia SQL concatenando la consulta previa y los filtros.
        $sql = $consulta_previa
            . $complemento->where
            . $complemento->sentencia . ' '
            . $complemento->filtro_especial . ' '
            . $complemento->filtro_rango . ' '
            . $complemento->filtro_fecha . ' '
            . $complemento->filtro_extra . ' '
            . $complemento->in . ' '
            . $complemento->not_in . ' '
            . $complemento->diferente_de . ' '
            . $complemento->sql_extra . ' '
            . $complemento->params->group_by . ' '
            . $complemento->params->order . ' '
            . $complemento->params->limit . ' '
            . $complemento->params->offset;

        // Limpia la cadena SQL reemplazando secuencias de espacios redundantes y corrige paréntesis.
        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('( (', '((', $sql);

        return str_replace('  ', ' ', $sql);
    }


}
