<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use PDO;

class dependencias{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * REG
     * Ajusta el nombre del modelo eliminando prefijos innecesarios y asegurando que tenga el formato correcto.
     *
     * Esta función limpia y ajusta el nombre de un modelo, eliminando cualquier prefijo `models\` innecesario
     * y volviendo a agregarlo si es necesario. Si el nombre del modelo está vacío o el resultado es inválido,
     * la función devuelve un error.
     *
     * ---
     *
     * ### **Parámetros:**
     * @param string $name_modelo Nombre del modelo a ajustar.
     *                            - **Ejemplo válido:** `"models\\Cliente"`
     *                            - **Ejemplo con error:** `""` (cadena vacía)
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se elimina el espacio en blanco al inicio y final del nombre del modelo.
     * 2. Se verifica si `$name_modelo` está vacío después del `trim()`. Si está vacío, se devuelve un error.
     * 3. Se elimina el prefijo `models\` si está presente.
     * 4. Se vuelve a agregar `models\` para garantizar el formato adecuado.
     * 5. Se verifica nuevamente si el resultado es solo `models\`, lo que indica un error.
     * 6. Se devuelve el nombre del modelo ajustado.
     *
     * ---
     *
     * @return string|array Devuelve el nombre del modelo ajustado en formato `models\NombreModelo`.
     *                      Si ocurre un error, devuelve un array con el mensaje de error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $modelo = "models\\Factura";
     * $resultado = $this->ajusta_modelo_comp($modelo);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * "models\\Factura"
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo sin prefijo:**
     * ```php
     * $modelo = "Factura";
     * $resultado = $this->ajusta_modelo_comp($modelo);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * "models\\Factura"
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo vacío (Error):**
     * ```php
     * $modelo = "";
     * $resultado = $this->ajusta_modelo_comp($modelo);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error name_modelo no puede venir vacio",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con solo el prefijo (Error):**
     * ```php
     * $modelo = "models\\";
     * $resultado = $this->ajusta_modelo_comp($modelo);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error name_modelo no puede venir vacio",
     *     "data" => "models\\"
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con mensaje de error si el `$name_modelo` está vacío o si el resultado es inválido.
     */
    private function ajusta_modelo_comp(string $name_modelo): string|array
    {
        $name_modelo = trim($name_modelo);
        if($name_modelo === ''){
            return $this->error->error(mensaje:'Error name_modelo no puede venir vacio',data:  $name_modelo,
                es_final: true);
        }
        $name_modelo = str_replace('models\\','',$name_modelo);
        $name_modelo = 'models\\'.$name_modelo;

        if($name_modelo === 'models\\'){
            return $this->error->error(mensaje: 'Error name_modelo no puede venir vacio', data: $name_modelo,
                es_final: true);
        }
        return trim($name_modelo);
    }

    /**
     * Elimina los elementos de dependencias
     * @param bool $desactiva_dependientes Si desactiva busca dependientes
     * @param array $models_dependientes Conjunto de modelos hijos
     * @param PDO $link Conexion a la base de datos
     * @param int $registro_id Registro en ejecucion
     * @param string $tabla Tabla origen
     * @return array
     * @version 1.434.48
     */
    final public function aplica_eliminacion_dependencias(bool $desactiva_dependientes, PDO $link,array $models_dependientes,
                                                    int $registro_id, string $tabla): array
    {
        $data = array();
        if($desactiva_dependientes) {
            $elimina = $this->elimina_data_modelos_dependientes(
                models_dependientes:$models_dependientes,link: $link,registro_id: $registro_id,
                tabla:$tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar dependiente', data: $elimina);
            }
            $data = $elimina;
        }
        return $data;
    }

    /**
     * REG
     * Obtiene los registros dependientes de una tabla en función de un identificador padre.
     *
     * Este método busca registros en la tabla hija (`$tabla_children`) que estén relacionados con un registro en la tabla
     * principal (`$tabla`) a través del identificador `$parent_id`. Se usa un filtro para obtener solo los registros
     * asociados a la tabla padre.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param PDO $link Conexión activa a la base de datos mediante PDO.
     *                  - **Ejemplo:** `$pdo = new PDO($dsn, $user, $password);`
     *
     * @param string $namespace_model Espacio de nombres del modelo que representa la tabla hija.
     *                                - **Ejemplo:** `"gamboamartin\\facturacion\\models"`
     *
     * @param int $parent_id Identificador del registro en la tabla padre (`$tabla`) que se usará para filtrar registros.
     *                       - **Debe ser un número entero mayor a 0.**
     *                       - **Ejemplo:** `123`
     *
     * @param string $tabla Nombre de la tabla padre que contiene el registro a relacionar.
     *                      - **Ejemplo:** `"clientes"`
     *
     * @param string $tabla_children Nombre de la tabla hija donde se buscarán los registros dependientes.
     *                                - **Ejemplo:** `"facturas"`
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se valida que `$parent_id` sea un número mayor a 0.
     * 2. Se valida que `$tabla_children` sea un nombre de modelo válido.
     * 3. Se genera un modelo de la tabla hija utilizando `genera_modelo()`.
     * 4. Se construye un filtro para obtener registros de `$tabla_children` que estén asociados a `$parent_id` en `$tabla`.
     * 5. Se ejecuta la consulta con `filtro_and()` para obtener los registros dependientes.
     *
     * ---
     *
     * @return array Retorna un arreglo con los registros encontrados en la tabla hija (`$tabla_children`).
     *               Si ocurre un error, devuelve un array con información del error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $pdo = new PDO($dsn, $user, $password);
     * $namespace_model = "gamboamartin\\facturacion\\models";
     * $parent_id = 123;
     * $tabla = "clientes";
     * $tabla_children = "facturas";
     *
     * $dependencias = $this->data_dependientes(
     *     link: $pdo,
     *     namespace_model: $namespace_model,
     *     parent_id: $parent_id,
     *     tabla: $tabla,
     *     tabla_children: $tabla_children
     * );
     *
     * print_r($dependencias);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     [
     *         "id" => 1,
     *         "clientes_id" => 123,
     *         "total" => 500.00,
     *         "status" => "pagado"
     *     ],
     *     [
     *         "id" => 2,
     *         "clientes_id" => 123,
     *         "total" => 250.00,
     *         "status" => "pendiente"
     *     ]
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo de Error (parent_id inválido):**
     * ```php
     * $parent_id = -1; // ID no válido
     * $dependencias = $this->data_dependientes(
     *     link: $pdo,
     *     namespace_model: $namespace_model,
     *     parent_id: $parent_id,
     *     tabla: $tabla,
     *     tabla_children: $tabla_children
     * );
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error $parent_id debe ser mayor a 0",
     *     "data" => -1
     * ]
     * ```
     *
     * ---
     *
     * @throws array Si `$parent_id` es menor o igual a 0, o si `$tabla_children` no es válido.
     */
    private function data_dependientes(
        PDO $link, string $namespace_model, int $parent_id, string $tabla, string $tabla_children): array
    {

        if($parent_id<=0){
            return $this->error->error(mensaje: 'Error $parent_id debe ser mayor a 0',data: $parent_id, es_final: true);
        }
        $tabla_children = trim($tabla_children);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $tabla_children);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_children',data: $valida);
        }

        $modelo_children = (new modelo_base(link: $link))->genera_modelo(modelo: $tabla_children,
            namespace_model: $namespace_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data: $modelo_children);
        }

        $key_id = $tabla.'.id';
        $filtro[$key_id] = $parent_id;

        $result = $modelo_children->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener dependientes',data: $result);
        }
        return $result->registros;
    }

    /**
     * REG
     * Desactiva los registros dependientes de un modelo en la base de datos.
     *
     * Este método busca y desactiva los registros dependientes en un modelo relacionado (`$modelo_dependiente`)
     * utilizando la estructura de relaciones definidas en `$modelo->registro_id`.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo_base $modelo Instancia del modelo base desde el cual se validan y desactivan los registros dependientes.
     *                            - **Ejemplo:** `$modelo = new modelo_base($pdo); $modelo->tabla = 'clientes';`
     *
     * @param string $modelo_dependiente Nombre del modelo dependiente que contiene los registros a desactivar.
     *                                    - **Ejemplo:** `"facturas"`
     *
     * @param string $namespace_model Espacio de nombres del modelo dependiente.
     *                                - **Ejemplo:** `"gamboamartin\\facturacion\\models"`
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se valida que `$modelo_dependiente` no esté vacío.
     * 2. Se valida que `$modelo->registro_id` sea un número positivo mayor a 0.
     * 3. Se ajusta el nombre del modelo dependiente mediante `modelo_dependiente_val()`.
     * 4. Se genera el modelo dependiente con `model_dependiente()`.
     * 5. Se ejecuta la desactivación de los registros dependientes mediante `desactiva_dependientes()`.
     *
     * ---
     *
     * @return array Retorna un array con los registros desactivados.
     *               En caso de error, devuelve un array con información del error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $pdo = new PDO($dsn, $user, $password);
     * $modelo = new modelo_base($pdo);
     * $modelo->tabla = "clientes";
     * $modelo->registro_id = 123;
     *
     * $namespace_model = "gamboamartin\\facturacion\\models";
     * $modelo_dependiente = "facturas";
     *
     * $resultado = $this->desactiva_data_modelo($modelo, $modelo_dependiente, $namespace_model);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada (lista de registros desactivados):**
     * ```php
     * [
     *     ["id" => 1, "facturas_id" => 123, "status" => "inactivo"],
     *     ["id" => 2, "facturas_id" => 123, "status" => "inactivo"]
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo dependiente inválido (Error):**
     * ```php
     * $modelo_dependiente = ""; // Nombre de modelo inválido
     * $resultado = $this->desactiva_data_modelo($modelo, $modelo_dependiente, $namespace_model);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error modelo_dependiente no puede venir vacio",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con registro_id inválido (Error):**
     * ```php
     * $modelo->registro_id = 0; // ID inválido
     * $resultado = $this->desactiva_data_modelo($modelo, $modelo_dependiente, $namespace_model);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error $this->registro_id debe ser mayor a 0",
     *     "data" => 0
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si `$modelo_dependiente` es inválido o `$modelo->registro_id` es menor o igual a 0.
     */
    private function desactiva_data_modelo(
        modelo_base $modelo, string $modelo_dependiente, string $namespace_model): array
    {

        $modelo_dependiente = trim($modelo_dependiente);
        if($modelo_dependiente === ''){
            return $this->error->error(mensaje:'Error modelo_dependiente no puede venir vacio',
                data:  $modelo_dependiente, es_final: true);
        }
        if($modelo->registro_id <= 0){
            return $this->error->error(mensaje: 'Error $this->registro_id debe ser mayor a 0',
                data: $modelo->registro_id, es_final: true);
        }

        $modelo_dependiente_ajustado = $this->modelo_dependiente_val(
            modelo: $modelo, modelo_dependiente: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al ajustar modelo',data: $modelo_dependiente_ajustado);
        }

        $modelo_ = $this->model_dependiente(modelo: $modelo, modelo_dependiente: $modelo_dependiente_ajustado,
            namespace_model: $namespace_model);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar modelo',data:  $modelo_);
        }

        $desactiva = $this->desactiva_dependientes(modelo: $modelo_, namespace_model: $modelo_->NAMESPACE,
            parent_id: $modelo->registro_id, tabla_dep: $modelo_->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al desactivar dependiente',data:  $desactiva);
        }
        return $desactiva;
    }

    /**
     * REG
     * Desactiva los registros dependientes de un modelo en la base de datos.
     *
     * Este método recorre los modelos dependientes definidos en `$modelo->models_dependientes`
     * y desactiva cada uno de ellos utilizando la función `desactiva_data_modelo()`.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo_base $modelo Instancia del modelo base que contiene la lista de modelos dependientes.
     *                            - **Ejemplo:** `$modelo = new modelo_base($pdo); $modelo->tabla = 'clientes';`
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se inicializa un array `$data` para almacenar los resultados de la desactivación.
     * 2. Se recorre `$modelo->models_dependientes`, extrayendo el nombre del modelo dependiente (`dependiente`).
     * 3. Se llama a `desactiva_data_modelo()` para desactivar cada modelo dependiente.
     * 4. Se almacenan los resultados en `$data`.
     * 5. Si ocurre un error en cualquier paso, se retorna un mensaje de error con la información correspondiente.
     *
     * ---
     *
     * @return array Retorna un array con los registros desactivados.
     *               En caso de error, devuelve un array con información del error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $pdo = new PDO($dsn, $user, $password);
     * $modelo = new modelo_base($pdo);
     * $modelo->tabla = "clientes";
     * $modelo->registro_id = 123;
     * $modelo->models_dependientes = [
     *     ["dependiente" => "facturas"],
     *     ["dependiente" => "pagos"]
     * ];
     *
     * $resultado = $this->desactiva_data_modelos_dependientes($modelo);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada (lista de registros desactivados en los modelos dependientes):**
     * ```php
     * [
     *     [
     *         ["id" => 1, "facturas_id" => 123, "status" => "inactivo"],
     *         ["id" => 2, "facturas_id" => 123, "status" => "inactivo"]
     *     ],
     *     [
     *         ["id" => 3, "pagos_id" => 123, "status" => "inactivo"]
     *     ]
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo dependiente inválido (Error):**
     * ```php
     * $modelo->models_dependientes = [
     *     ["dependiente" => ""], // Nombre de modelo inválido
     * ];
     * $resultado = $this->desactiva_data_modelos_dependientes($modelo);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error modelo_dependiente no puede venir vacio",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo sin dependientes:**
     * ```php
     * $modelo->models_dependientes = []; // No hay modelos dependientes
     * $resultado = $this->desactiva_data_modelos_dependientes($modelo);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * []
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si `$modelo->models_dependientes` no contiene modelos válidos.
     */
    final public function desactiva_data_modelos_dependientes(modelo_base $modelo): array
    {
        $data = array();
        foreach ($modelo->models_dependientes as $data_dep) {
            $dependiente = $data_dep['dependiente'];
            $desactiva = $this->desactiva_data_modelo(modelo: $modelo,modelo_dependiente:  $dependiente,
                namespace_model: $modelo->NAMESPACE);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al desactivar dependiente', data: $desactiva);
            }
            $data[] = $desactiva;
        }
        return $data;
    }

    /**
     * REG
     * Desactiva registros dependientes de una tabla en la base de datos.
     *
     * Este método busca y desactiva todos los registros dependientes en la tabla especificada (`$tabla_dep`)
     * que estén relacionados con un registro padre (`$parent_id`) en la tabla principal (`$modelo->tabla`).
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo_base $modelo Instancia del modelo base desde el cual se validan y desactivan los dependientes.
     *                            - **Ejemplo:** `$modelo = new modelo_base($pdo); $modelo->tabla = 'clientes';`
     *
     * @param string $namespace_model Espacio de nombres del modelo dependiente.
     *                                - **Ejemplo:** `"gamboamartin\\facturacion\\models"`
     *
     * @param int $parent_id Identificador del registro en la tabla padre (`$modelo->tabla`).
     *                       - **Debe ser un número entero mayor a 0.**
     *                       - **Ejemplo:** `123`
     *
     * @param string $tabla_dep Nombre de la tabla dependiente donde se buscarán los registros a desactivar.
     *                          - **Ejemplo:** `"facturas"`
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se valida que `$modelo->tabla` sea un nombre de clase válido.
     * 2. Se valida que `$parent_id` sea un número positivo mayor a 0.
     * 3. Se valida que `$tabla_dep` sea un nombre de modelo válido.
     * 4. Se obtienen los registros dependientes en `$tabla_dep` relacionados con `$parent_id` en `$modelo->tabla`.
     * 5. Se genera un modelo de la tabla dependiente (`$tabla_dep`).
     * 6. Se desactivan los registros dependientes encontrados.
     *
     * ---
     *
     * @return array Retorna un array con los registros desactivados.
     *               En caso de error, devuelve un array con información del error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $pdo = new PDO($dsn, $user, $password);
     * $modelo = new modelo_base($pdo);
     * $modelo->tabla = "clientes";
     *
     * $namespace_model = "gamboamartin\\facturacion\\models";
     * $parent_id = 123;
     * $tabla_dep = "facturas";
     *
     * $resultado = $this->desactiva_dependientes($modelo, $namespace_model, $parent_id, $tabla_dep);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada (lista de registros desactivados):**
     * ```php
     * [
     *     ["id" => 1, "facturas_id" => 123, "status" => "inactivo"],
     *     ["id" => 2, "facturas_id" => 123, "status" => "inactivo"]
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con parent_id inválido (Error):**
     * ```php
     * $parent_id = -1; // ID no válido
     * $resultado = $this->desactiva_dependientes($modelo, $namespace_model, $parent_id, $tabla_dep);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error $parent_id debe ser mayor a 0",
     *     "data" => -1
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con tabla dependiente inválida (Error):**
     * ```php
     * $tabla_dep = ""; // Tabla vacía
     * $resultado = $this->desactiva_dependientes($modelo, $namespace_model, $parent_id, $tabla_dep);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error al validar $tabla_dep",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si `$parent_id` es inválido o `$tabla_dep` no es válida.
     */
    private function desactiva_dependientes(
        modelo_base $modelo, string $namespace_model, int $parent_id, string $tabla_dep): array
    {
        $valida = $this->validacion->valida_name_clase($modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla',data: $valida, es_final: true);
        }
        if($parent_id <= 0){
            return $this->error->error(mensaje: 'Error $parent_id debe ser mayor a 0',data: $parent_id,
                es_final: true);
        }

        $tabla_dep = trim($tabla_dep);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $tabla_dep);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_dep',data: $valida);
        }

        $dependientes = $this->data_dependientes(link: $modelo->link, namespace_model: $namespace_model,
            parent_id: $parent_id, tabla: $modelo->tabla, tabla_children: $tabla_dep);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener dependientes',data: $dependientes);
        }

        $key_dependiente_id = $tabla_dep.'_id';

        $modelo_dep = $modelo->genera_modelo(modelo: $tabla_dep,namespace_model: $namespace_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data: $modelo_dep);
        }


        $result = array();
        foreach($dependientes as $dependiente){

            $modelo_dep->registro_id = $dependiente[$key_dependiente_id];

            $desactiva_bd = $modelo_dep->desactiva_bd();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al desactivar dependiente',data: $desactiva_bd);
            }
            $result[] = $desactiva_bd;
        }
        return $result;

    }

    /**
     * Elimina los registros dependientes de un modelo
     * @param string $modelo_dependiente Modelo Hijo
     * @param string $namespace_model
     * @param PDO $link Conexion a la bd
     * @param int $registro_id Registro en proceso
     * @param string $tabla Tabla origen
     * @return array
     * @version 1.410.47
     */
    private function elimina_data_modelo(string $modelo_dependiente, string $namespace_model,PDO $link,
                                         int $registro_id, string $tabla): array
    {
        $modelo_dependiente = trim($modelo_dependiente);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo dependiente $modelo_dependiente",
                data: $valida);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje:'Error $this->registro_id debe ser mayor a 0',data:$registro_id);
        }


        $modelo = (new modelo_base($link))->genera_modelo(
            modelo: $modelo_dependiente, namespace_model: $namespace_model);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar modelo', data:$modelo);
        }
        $desactiva = $this->elimina_dependientes(model:  $modelo, parent_id: $registro_id,
            tabla: $tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al desactivar dependiente',data: $desactiva);
        }
        return $desactiva;
    }

    /**
     * Elimina los datos de un modelo dependiente
     * @param array $models_dependientes Modelos dependendientes
     * @param PDO $link Conexion a la base de datos
     * @param int $registro_id Registro en ejecucion
     * @param string $tabla Tabla origen
     * @return array
     * @version 1.433.48
     *
     */
    private function elimina_data_modelos_dependientes(array $models_dependientes, PDO $link, int $registro_id,
                                                       string $tabla): array
    {
        $data = array();
        foreach ($models_dependientes as $data_dep) {

            $keys = array('namespace_model','dependiente');
            $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $data_dep);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al validar data_dep",data: $valida);
            }

            $dependiente = trim($data_dep['dependiente']);
            $valida = $this->validacion->valida_data_modelo(name_modelo: $dependiente);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
            }
            if($registro_id<=0){
                return $this->error->error(mensaje:'Error $this->registro_id debe ser mayor a 0',
                    data:$registro_id);
            }

            $desactiva = $this->elimina_data_modelo(modelo_dependiente: $dependiente,
                namespace_model: $data_dep['namespace_model'], link: $link, registro_id: $registro_id, tabla: $tabla);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al desactivar dependiente', data:$desactiva);
            }
            $data[] = $desactiva;
        }
        return $data;
    }

    /**
     * Elimina los registros dependientes de un modelo
     * @param modelo $model Modelo en ejecucion
     * @param int $parent_id Id origen
     * @param string $tabla Tabla origen
     * @return array
     * @version 1.401.45
     */
    private function elimina_dependientes(modelo $model, int $parent_id, string $tabla): array
    {

        if($parent_id<=0){
            return $this->error->error(mensaje:'Error $parent_id debe ser mayor a 0',data: $parent_id);
        }

        $dependientes = $this->data_dependientes(link: $model->link, namespace_model: $model->NAMESPACE,
            parent_id: $parent_id, tabla: $tabla, tabla_children: $model->tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener dependientes',data:$dependientes);
        }

        $key_dependiente_id = $model->tabla.'_id';

        $result = array();
        foreach($dependientes as $dependiente){
            $elimina_bd = $model->elimina_bd(id: $dependiente[$key_dependiente_id]);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al desactivar dependiente',data:$elimina_bd);
            }
            $result[] = $elimina_bd;
        }
        return $result;

    }

    /**
     * REG
     * Genera y valida un modelo dependiente a partir de un modelo base.
     *
     * Este método ajusta el nombre del modelo dependiente, lo valida y genera una instancia del mismo
     * usando el `namespace_model` especificado.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo_base $modelo Instancia del modelo base desde el cual se está verificando la dependencia.
     *                            - **Ejemplo:** `$modelo = new modelo_base($pdo); $modelo->tabla = 'clientes'; $modelo->registro_id = 123;`
     *
     * @param string $modelo_dependiente Nombre del modelo dependiente que se desea generar.
     *                                   - **Ejemplo válido:** `"models\\Facturas"`
     *                                   - **Ejemplo con error:** `""` (cadena vacía)
     *
     * @param string $namespace_model Espacio de nombres del modelo dependiente.
     *                                - **Ejemplo:** `"gamboamartin\\facturacion\\models"`
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se valida que `$modelo_dependiente` no sea una cadena vacía.
     * 2. Se valida que `$modelo->registro_id` sea un número positivo mayor a 0.
     * 3. Se ajusta el nombre del modelo dependiente con `modelo_dependiente_val()`.
     * 4. Se genera una instancia del modelo dependiente con `genera_modelo()`.
     * 5. Si todas las validaciones son exitosas, se retorna la instancia del modelo dependiente.
     *
     * ---
     *
     * @return modelo_base|array Retorna una instancia del modelo dependiente (`modelo_base`).
     *                           En caso de error, retorna un `array` con un mensaje de error y los datos problemáticos.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $pdo = new PDO($dsn, $user, $password);
     * $modelo = new modelo_base($pdo);
     * $modelo->tabla = "clientes";
     * $modelo->registro_id = 123;
     *
     * $modelo_dependiente = "models\\Facturas";
     * $namespace_model = "gamboamartin\\facturacion\\models";
     *
     * $resultado = $this->model_dependiente($modelo, $modelo_dependiente, $namespace_model);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada (instancia de modelo dependiente):**
     * ```php
     * models\Facturas Object (
     *     [tabla] => "facturas"
     *     [registro_id] => 123
     *     [link] => PDO Object (...)
     * )
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo dependiente inválido (Error):**
     * ```php
     * $modelo_dependiente = "";
     * $resultado = $this->model_dependiente($modelo, $modelo_dependiente, $namespace_model);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error modelo_dependiente no puede venir vacio",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con registro_id inválido (Error):**
     * ```php
     * $modelo->registro_id = -1; // ID no válido
     * $resultado = $this->model_dependiente($modelo, $modelo_dependiente, $namespace_model);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error $this->registro_id debe ser mayor a 0",
     *     "data" => -1
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si `$modelo_dependiente` no es válido o si `$modelo->registro_id <= 0`.
     */
    private function model_dependiente(
        modelo_base $modelo, string $modelo_dependiente, string $namespace_model): modelo_base|array
    {

        $modelo_dependiente = trim($modelo_dependiente);
        if($modelo_dependiente === ''){
            return $this->error->error(mensaje:'Error modelo_dependiente no puede venir vacio',
                data:  $modelo_dependiente, es_final: true);
        }
        if($modelo->registro_id <= 0){
            return $this->error->error(mensaje: 'Error $this->registro_id debe ser mayor a 0',
                data: $modelo->registro_id, es_final: true);
        }

        $modelo_dependiente_ajustado = $this->modelo_dependiente_val(modelo: $modelo,
            modelo_dependiente: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error('Error al ajustar modelo',$modelo_dependiente_ajustado);
        }
        $modelo_ = $modelo->genera_modelo(modelo: $modelo_dependiente_ajustado,namespace_model: $namespace_model);
        if (errores::$error) {
            return $this->error->error('Error al generar modelo', $modelo_);
        }
        return $modelo_;
    }


    /**
     * REG
     * Valida y ajusta el nombre del modelo dependiente para su uso en la desactivación de registros.
     *
     * Este método verifica que el nombre del modelo dependiente sea válido y lo ajusta al formato correcto.
     * También valida que el modelo principal tenga un `registro_id` válido antes de proceder.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo_base $modelo Instancia del modelo principal desde el cual se quiere validar el dependiente.
     *                            - **Ejemplo:** `$modelo = new modelo_base($pdo); $modelo->tabla = 'clientes'; $modelo->registro_id = 123;`
     *
     * @param string $modelo_dependiente Nombre del modelo dependiente a validar.
     *                                   - **Ejemplo válido:** `"models\\Facturas"`
     *                                   - **Ejemplo con error:** `""` (cadena vacía)
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se valida que `$modelo_dependiente` no sea una cadena vacía.
     * 2. Se valida que `$modelo->registro_id` sea un número positivo mayor a 0.
     * 3. Se ajusta el nombre del modelo dependiente con `ajusta_modelo_comp()`.
     * 4. Se valida que tanto el modelo principal como el dependiente sean correctos con `valida_data_desactiva()`.
     * 5. Si todas las validaciones son exitosas, se retorna el modelo dependiente ajustado.
     *
     * ---
     *
     * @return array|string Retorna el nombre del modelo dependiente ajustado (`string`).
     *                      En caso de error, retorna un `array` con un mensaje de error y los datos problemáticos.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $pdo = new PDO($dsn, $user, $password);
     * $modelo = new modelo_base($pdo);
     * $modelo->tabla = "clientes";
     * $modelo->registro_id = 123;
     *
     * $modelo_dependiente = "models\\Facturas";
     *
     * $resultado = $this->modelo_dependiente_val($modelo, $modelo_dependiente);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * "models\\Facturas"
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo dependiente inválido (Error):**
     * ```php
     * $modelo_dependiente = "";
     * $resultado = $this->modelo_dependiente_val($modelo, $modelo_dependiente);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error modelo_dependiente no puede venir vacio",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con registro_id inválido (Error):**
     * ```php
     * $modelo->registro_id = -1; // ID no válido
     * $resultado = $this->modelo_dependiente_val($modelo, $modelo_dependiente);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error $this->registro_id debe ser mayor a 0",
     *     "data" => -1
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si `$modelo_dependiente` no es válido o si `$modelo->registro_id <= 0`.
     */
    private function modelo_dependiente_val(modelo_base $modelo, string $modelo_dependiente): array|string
    {

        $modelo_dependiente = trim($modelo_dependiente);
        if($modelo_dependiente === ''){
            return $this->error->error(mensaje:'Error modelo_dependiente no puede venir vacio',
                data:  $modelo_dependiente, es_final: true);
        }
        if($modelo->registro_id <= 0){
            return $this->error->error(mensaje: 'Error $this->registro_id debe ser mayor a 0',
                data: $modelo->registro_id, es_final: true);
        }

        $modelo_dependiente_ajustado = $this->ajusta_modelo_comp(name_modelo: $modelo_dependiente);
        if(errores::$error ){
            return  $this->error->error(mensaje: 'Error al ajustar modelo',data: $modelo_dependiente);
        }

        $valida = $this->valida_data_desactiva(modelo: $modelo, modelo_dependiente: $modelo_dependiente_ajustado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar modelos',data: $valida);
        }

        return $modelo_dependiente_ajustado;
    }

    /**
     * REG
     * Valida los datos requeridos para la desactivación de un modelo dependiente.
     *
     * Esta función verifica si los datos del modelo principal y del modelo dependiente son válidos
     * antes de proceder con la desactivación de registros en la base de datos.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo_base $modelo Instancia del modelo principal desde el cual se quiere desactivar un dependiente.
     *                            - **Ejemplo:** `$modelo = new modelo_base($pdo); $modelo->tabla = 'clientes';`
     *
     * @param string $modelo_dependiente Nombre del modelo dependiente a validar.
     *                                   - **Ejemplo válido:** `"models\\Facturas"`
     *                                   - **Ejemplo con error:** `""` (cadena vacía)
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se valida `$modelo_dependiente` y `$modelo->tabla` con `valida_names_model()`.
     * 2. Se verifica que `$modelo->registro_id` sea un número positivo mayor a 0.
     * 3. Si todas las validaciones son correctas, se retorna `true`.
     *
     * ---
     *
     * @return bool|array Retorna `true` si los datos son válidos.
     *                    En caso de error, retorna un array con un mensaje de error y los datos del problema.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $pdo = new PDO($dsn, $user, $password);
     * $modelo = new modelo_base($pdo);
     * $modelo->tabla = "clientes";
     * $modelo->registro_id = 123;
     *
     * $modelo_dependiente = "models\\Facturas";
     *
     * $resultado = $this->valida_data_desactiva($modelo, $modelo_dependiente);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * true
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo dependiente inválido (Error):**
     * ```php
     * $modelo_dependiente = "";
     * $resultado = $this->valida_data_desactiva($modelo, $modelo_dependiente);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error al validar modelos",
     *     "data" => "models\\Facturas"
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con registro_id inválido (Error):**
     * ```php
     * $modelo->registro_id = -1; // ID no válido
     * $resultado = $this->valida_data_desactiva($modelo, $modelo_dependiente);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error $this->registro_id debe ser mayor a 0",
     *     "data" => -1
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si `$modelo_dependiente` no es válido o si `$modelo->registro_id <= 0`.
     */
    private function valida_data_desactiva(modelo_base $modelo, string $modelo_dependiente): bool|array
    {
        $valida = $this->valida_names_model(modelo_dependiente: $modelo_dependiente,
            tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar modelos',data: $valida);
        }

        if($modelo->registro_id<=0){
            return $this->error->error(mensaje: 'Error $this->registro_id debe ser mayor a 0',
                data: $modelo->registro_id, es_final: true);
        }
        return true;
    }

    /**
     * REG
     * Valida el nombre de un modelo dependiente y el nombre de una tabla.
     *
     * Esta función verifica que el nombre del modelo dependiente y el nombre de la tabla sean válidos.
     * Se utilizan dos validaciones:
     * 1. `valida_data_modelo(name_modelo: $modelo_dependiente)`: Verifica si el nombre del modelo dependiente
     *    es correcto.
     * 2. `valida_name_clase(tabla: $tabla)`: Verifica si el nombre de la tabla es válido.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param string $modelo_dependiente Nombre del modelo dependiente que se desea validar.
     *                                   - **Ejemplo válido:** `"models\\Factura"`
     *                                   - **Ejemplo con error:** `""` (cadena vacía)
     *
     * @param string $tabla Nombre de la tabla que se desea validar.
     *                      - **Ejemplo válido:** `"facturas"`
     *                      - **Ejemplo con error:** `""` (cadena vacía)
     *
     * ---
     *
     * ### **Proceso Interno:**
     * 1. Se valida `$modelo_dependiente` utilizando `valida_data_modelo()`. Si es incorrecto, se genera un error.
     * 2. Se valida `$tabla` utilizando `valida_name_clase()`. Si es incorrecto, se genera un error.
     * 3. Si ambas validaciones son correctas, la función devuelve `true`.
     *
     * ---
     *
     * @return bool|array Retorna `true` si ambos nombres son válidos.
     *                    En caso de error, retorna un array con un mensaje de error y los datos del problema.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $modelo_dependiente = "models\\Factura";
     * $tabla = "facturas";
     * $resultado = $this->valida_names_model($modelo_dependiente, $tabla);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * true
     * ```
     *
     * ---
     *
     * ### **Ejemplo con modelo dependiente inválido (Error):**
     * ```php
     * $modelo_dependiente = "";
     * $tabla = "facturas";
     * $resultado = $this->valida_names_model($modelo_dependiente, $tabla);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error al validar modelo_dependiente",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con tabla inválida (Error):**
     * ```php
     * $modelo_dependiente = "models\\Factura";
     * $tabla = "";
     * $resultado = $this->valida_names_model($modelo_dependiente, $tabla);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error al validar tabla",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con ambos valores inválidos (Error):**
     * ```php
     * $modelo_dependiente = "";
     * $tabla = "";
     * $resultado = $this->valida_names_model($modelo_dependiente, $tabla);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     "error" => true,
     *     "mensaje" => "Error al validar modelo_dependiente",
     *     "data" => ""
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si `$modelo_dependiente` o `$tabla` no son válidos.
     */
    private function valida_names_model(string $modelo_dependiente, string $tabla): bool|array
    {
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo_dependiente",data: $valida);
        }

        $valida = $this->validacion->valida_name_clase(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla',data: $valida);
        }

        return true;
    }

}
