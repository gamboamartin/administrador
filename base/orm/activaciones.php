<?php
/**
 * @author Martin Gamboa Vazquez
 * Clase definida para activar elementos en la base de datos
 * @version 1.110.27
 */
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class activaciones{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }

    /**
     * REG
     * Inicializa el proceso de activación de un registro en la base de datos.
     *
     * Esta función valida que el `registro_id` sea válido (>0), normaliza el nombre del modelo,
     * genera los datos de activación y asigna la consulta y el tipo de transacción al modelo.
     *
     * ### Funcionamiento:
     * 1. **Verifica que `registro_id` sea mayor a 0.**
     * 2. **Llama a `normaliza_name_model` para obtener el nombre limpio del modelo.**
     * 3. **Genera los datos de activación llamando a `maqueta_activacion`.**
     * 4. **Asigna la consulta SQL y el tipo de transacción al modelo.**
     * 5. **Devuelve un `stdClass` con los datos de activación o un error si algo falla.**
     *
     * @param modelo $modelo Instancia del modelo con el registro a activar.
     * @param bool $reactiva Indica si se permite la reactivación (`true`) o si se debe validar (`false`).
     *
     * @return array|stdClass Un objeto con la consulta SQL, el tipo de transacción y el nombre del modelo,
     * o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $activaciones = new activaciones();
     * $modelo = new adm_accion();
     * $modelo->registro_id = 5;
     * $reactiva = false;
     * $resultado = $activaciones->init_activa(modelo: $modelo, reactiva: $reactiva);
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (activación generada correctamente)**
     * ```php
     * stdClass Object
     * (
     *     [consulta] => "UPDATE adm_accion SET status = 'activo' WHERE id = 5"
     *     [transaccion] => "ACTIVA"
     *     [name_model] => "adm_accion"
     * )
     * ```
     *
     * **Caso 2: Error (ID no válido)**
     * ```php
     * Array
     * (
     *     [error] => "Error $modelo->registro_id debe ser mayor a 0"
     *     [data] => 0
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 3: Error (fallo al normalizar el modelo)**
     * ```php
     * Array
     * (
     *     [error] => "Error al normalizar modelo adm_accion"
     *     [data] => "Error: tabla vacía"
     * )
     * ```
     *
     * **Caso 4: Error (fallo al generar los datos de activación)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar datos de activación adm_accion"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si `$registro_id` es menor o igual a 0, si la normalización del modelo falla
     * o si la generación de la activación no es exitosa.
     */
    final public function init_activa(modelo $modelo, bool $reactiva): array|stdClass
    {
        if($modelo->registro_id <=0){
            return  $this->error->error(mensaje: 'Error  $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id,es_final: true);
        }

        $name_model = $this->normaliza_name_model(modelo:$modelo);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al normalizar modelo '.$modelo->tabla,data:$name_model);
        }

        $data_activacion = $this->maqueta_activacion(modelo:$modelo, reactiva: $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar datos de activacion '.$modelo->tabla,
                data:$data_activacion);
        }
        $modelo->consulta = $data_activacion->consulta;
        $modelo->transaccion = $data_activacion->transaccion;

        $data_activacion->name_model = $name_model;
        return $data_activacion;
    }

    /**
     * REG
     * Genera la estructura de activación para un registro en la base de datos.
     *
     * Esta función valida que el modelo sea elegible para activación, verifica si la reactivación está permitida
     * y genera la consulta SQL correspondiente para activar el registro.
     *
     * ### Funcionamiento:
     * 1. **Verifica que `registro_id` sea mayor a 0.**
     * 2. **Llama a `verifica_reactivacion` para validar si el registro puede activarse.**
     * 3. **Genera la consulta SQL llamando a `sql_activa`.**
     * 4. **Retorna un objeto `stdClass` con la consulta SQL y el tipo de transacción.**
     *
     * @param modelo $modelo Instancia del modelo que contiene la información del registro a activar.
     * @param bool $reactiva Indica si se permite la reactivación (`true`) o si se debe validar (`false`).
     *
     * @return array|stdClass Un objeto con la consulta SQL y el tipo de transacción, o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $activaciones = new activaciones();
     * $modelo = new adm_accion();
     * $modelo->registro_id = 15;
     * $reactiva = false;
     * $resultado = $activaciones->maqueta_activacion(modelo: $modelo, reactiva: $reactiva);
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (estructura generada correctamente)**
     * ```php
     * stdClass Object
     * (
     *     [consulta] => "UPDATE adm_accion SET status = 'activo' WHERE id = 15"
     *     [transaccion] => "ACTIVA"
     * )
     * ```
     *
     * **Caso 2: Error (ID no válido)**
     * ```php
     * Array
     * (
     *     [error] => "Error $modelo->registro_id debe ser mayor a 0"
     *     [data] => 0
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 3: Error (no se puede reactivar el registro)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar transacción activa en adm_accion"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si `$registro_id` es menor o igual a 0 o si la consulta SQL no puede generarse correctamente.
     */
    private function maqueta_activacion(modelo $modelo, bool $reactiva): array|stdClass
    {
        if($modelo->registro_id <=0){
            return  $this->error->error(mensaje: 'Error  $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id, es_final: true);
        }

        $valida = $this->verifica_reactivacion(modelo:$modelo,reactiva:  $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al validar transaccion activa en '.$modelo->tabla,data:$valida);
        }

        $sql = $this->sql_activa(registro_id:$modelo->registro_id,tabla:  $modelo->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar sql '.$modelo->tabla,data:$valida);
        }

        $data = new stdClass();
        $data->consulta = $sql;
        $data->transaccion = 'ACTIVA';

        return $data;
    }

    /**
     * REG
     * Normaliza el nombre del modelo eliminando espacios en blanco y el namespace `models\`.
     *
     * Esta función procesa el atributo `tabla` de un modelo, eliminando espacios en blanco
     * y removiendo el prefijo del namespace si está presente. Si el atributo `tabla` está vacío,
     * devuelve un error.
     *
     * ### Funcionamiento:
     * 1. **Elimina espacios en blanco en el nombre de la tabla.**
     * 2. **Verifica que `$modelo->tabla` no esté vacío.**
     * 3. **Remueve el prefijo `models\` en caso de estar presente.**
     * 4. **Retorna el nombre normalizado del modelo.**
     *
     * @param modelo $modelo Instancia del modelo que contiene el nombre de la tabla.
     *
     * @return array|string El nombre normalizado de la tabla o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $activaciones = new activaciones();
     * $modelo = new adm_accion();
     * $modelo->tabla = 'models\\usuarios';
     * $nombre_normalizado = $activaciones->normaliza_name_model(modelo: $modelo);
     * print_r($nombre_normalizado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (tabla normalizada correctamente)**
     * ```php
     * "usuarios"
     * ```
     *
     * **Caso 2: Error (nombre de la tabla vacío)**
     * ```php
     * Array
     * (
     *     [error] => "Error el atributo tabla del modelo  Esta vacio"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * @throws errores Si `$modelo->tabla` está vacío, devuelve un error.
     */
    private function normaliza_name_model(modelo $modelo): array|string
    {
        $modelo->tabla = trim($modelo->tabla);
        if($modelo->tabla === ''){
            return $this->error->error(mensaje:'Error el atributo tabla del modelo '.$modelo->tabla.' Esta vacio',
                data:$modelo->tabla, es_final: true);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        return $modelo->tabla;
    }

    /**
     * REG
     * Genera una consulta SQL para activar un registro en la base de datos.
     *
     * Esta función construye una consulta `UPDATE` para cambiar el estado (`status`)
     * de un registro específico a "activo". Antes de generar la consulta,
     * valida que la tabla no esté vacía y que el ID del registro sea mayor a 0.
     *
     * ### Funcionamiento:
     * 1. **Elimina espacios en blanco en el nombre de la tabla.**
     * 2. **Verifica que `$tabla` no esté vacía.**
     * 3. **Verifica que `$registro_id` sea un número mayor a 0.**
     * 4. **Retorna una consulta SQL en formato `UPDATE` si todo es válido.**
     *
     * @param int $registro_id Identificador del registro a activar (debe ser > 0).
     * @param string $tabla Nombre de la tabla donde se aplicará la actualización.
     *
     * @return string|array La consulta SQL en formato `UPDATE`, o un array de error si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $activaciones = new activaciones();
     * $sql = $activaciones->sql_activa(registro_id: 10, tabla: 'usuarios');
     * print_r($sql);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (consulta generada correctamente)**
     * ```sql
     * UPDATE usuarios SET status = 'activo' WHERE id = 10
     * ```
     *
     * **Caso 2: Error (nombre de la tabla vacío)**
     * ```php
     * Array
     * (
     *     [error] => Error la tabla está vacía
     *     [data] => ''
     * )
     * ```
     *
     * **Caso 3: Error (ID no válido)**
     * ```php
     * Array
     * (
     *     [error] => Error $registro_id debe ser mayor a 0
     *     [data] => 0
     * )
     * ```
     *
     * @throws errores Si `$tabla` está vacía o `$registro_id` es menor o igual a 0, devuelve un error.
     */
    private function sql_activa(int $registro_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return  $this->error->error(mensaje: 'Error  la tabla esta vacia', data: $tabla);
        }
        if($registro_id<=0){
            return  $this->error->error(mensaje: 'Error $registro_id debe ser mayor a 0', data: $registro_id);
        }
        return "UPDATE " . $tabla . " SET status = 'activo' WHERE id = " . $registro_id;
    }

    /**
     * REG
     * Valida si un registro en la base de datos puede ser activado según las reglas definidas en el modelo.
     *
     * Esta función verifica que el identificador del registro (`registro_id`) sea válido (>0),
     * obtiene el registro correspondiente desde la base de datos y valida si la transacción
     * de activación está permitida según la configuración del modelo.
     *
     * ### Funcionamiento:
     * 1. **Verifica que `registro_id` sea mayor a 0.**
     * 2. **Obtiene el registro desde el modelo.**
     * 3. **Valida si la activación está permitida según la configuración del modelo.**
     * 4. **Devuelve `true` si la activación es válida, o un error si no lo es.**
     *
     * @param modelo $modelo Instancia del modelo con el registro a verificar.
     *
     * **Ejemplo de objeto `$modelo` de entrada:**
     * ```php
     * $modelo = new adm_accion();
     * $modelo->registro_id = 5;
     * $modelo->aplica_transaccion_inactivo = true;
     * ```
     *
     * @return bool|array `true` si la activación es válida, o un array con detalles de error en caso de falla.
     *
     * **Ejemplo de salida exitosa:**
     * ```php
     * true
     * ```
     *
     * **Ejemplo de salida en caso de error (`registro_id` inválido):**
     * ```php
     * Array
     * (
     *     [error] => 1
     *     [mensaje] => Error $modelo->registro_id debe ser mayor a 0
     *     [data] => 0
     *     [es_final] => true
     * )
     * ```
     *
     * **Ejemplo de salida en caso de error al obtener el registro:**
     * ```php
     * Array
     * (
     *     [error] => 1
     *     [mensaje] => Error al obtener registro adm_accion
     *     [data] => null
     * )
     * ```
     *
     * **Ejemplo de salida en caso de error en validación de activación:**
     * ```php
     * Array
     * (
     *     [error] => 1
     *     [mensaje] => Error al validar transacción activa en adm_accion
     *     [data] => false
     *     [es_final] => true
     * )
     * ```
     *
     * @throws errores En caso de que el modelo no tenga un `registro_id` válido o falle la validación.
     */
    final public function valida_activacion(modelo $modelo): bool|array
    {
        if ($modelo->registro_id <= 0) {
            return $this->error->error(
                mensaje: 'Error $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id,
                es_final: true
            );
        }

        $registro = $modelo->registro(registro_id: $modelo->registro_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener registro ' . $modelo->tabla,
                data: $registro
            );
        }

        $valida = $modelo->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $modelo->aplica_transaccion_inactivo,
            registro: $registro,
            registro_id: $modelo->registro_id,
            tabla: $modelo->tabla
        );

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar transacción activa en ' . $modelo->tabla,
                data: $valida,
                es_final: true
            );
        }

        return $valida;
    }


    /**
     * REG
     * Verifica si un registro puede ser reactivado en la base de datos.
     *
     * Esta función evalúa si un registro ya activado puede ser reactivado
     * según las reglas definidas en el modelo. Si la reactivación no está permitida,
     * se valida la activación usando `valida_activacion`.
     *
     * ### Funcionamiento:
     * 1. **Verifica que `registro_id` sea mayor a 0.**
     * 2. **Si `$reactiva` es `false`, llama a `valida_activacion` para verificar si la transacción es válida.**
     * 3. **Devuelve `true` si la reactivación es permitida, o un error en caso contrario.**
     *
     * @param modelo $modelo Objeto del modelo con el registro a verificar.
     * @param bool $reactiva Indica si se permite la reactivación (`true`) o si debe validar (`false`).
     *
     * @return bool|array `true` si la reactivación es válida, o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $activaciones = new activaciones();
     * $modelo = new adm_accion();
     * $modelo->registro_id = 5;
     * $reactiva = false;
     * $resultado = $activaciones->verifica_reactivacion(modelo: $modelo, reactiva: $reactiva);
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (se permite la reactivación)**
     * ```php
     * true
     * ```
     *
     * **Caso 2: Error (el registro no es válido para reactivación)**
     * ```php
     * Array
     * (
     *     [error] => Error al validar transaccion activa en adm_accion
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si `registro_id` es menor o igual a 0, devuelve un error.
     */
    private function verifica_reactivacion(modelo $modelo, bool $reactiva): bool|array
    {
        if ($modelo->registro_id <= 0) {
            return $this->error->error(
                mensaje: 'Error: $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id,
                es_final: true
            );
        }

        $valida = true;
        if (!$reactiva) {
            $valida = $this->valida_activacion(modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al validar transaccion activa en ' . $modelo->tabla,
                    data: $valida
                );
            }
        }

        return $valida;
    }




}
