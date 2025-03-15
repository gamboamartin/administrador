<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Throwable;

class bitacoras{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * REG
     * Aplica un registro en la bitácora generando un modelo, consultando un registro específico y
     * registrando la transacción correspondiente.
     *
     * Esta función se encarga de generar una bitácora tomando como referencia un modelo específico,
     * obteniendo el registro afectado y almacenando la transacción en la base de datos.
     *
     * ### 🔹 **Flujo de ejecución:**
     * 1. **Verifica que `$registro_id` sea mayor a 0.**
     * 2. **Genera un modelo basado en la tabla proporcionada.**
     * 3. **Obtiene el registro de la base de datos usando `$registro_id`.**
     * 4. **Registra la transacción en la bitácora utilizando `bitacora()`.**
     * 5. **Retorna los datos de la bitácora generada o un error si ocurre una falla.**
     *
     * @param string $consulta Consulta SQL que se registrará en la bitácora.
     * @param string $funcion Nombre de la función o acción que se está ejecutando.
     * @param modelo $modelo Instancia del modelo en uso, que contiene los datos de conexión.
     * @param int $registro_id Identificador del registro afectado.
     * @param string $tabla Nombre de la tabla con la que se va a interactuar.
     *
     * @return array Un array con los datos de la bitácora generada o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $consulta = "UPDATE usuarios SET estado = 'activo' WHERE id = 10";
     * $funcion = "actualizar_estado";
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $modelo->NAMESPACE = "gamboamartin\\usuarios\\models";
     * $registro_id = 10;
     * $tabla = "usuarios";
     *
     * $resultado = $bitacoras->aplica_bitacora(
     *     consulta: $consulta,
     *     funcion: $funcion,
     *     modelo: $modelo,
     *     registro_id: $registro_id,
     *     tabla: $tabla
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Ejemplo de salida exitosa**
     * ```php
     * Array
     * (
     *     [id] => 312
     *     [adm_seccion_id] => 7
     *     [status] => "activo"
     *     [registro] => '{"id":10,"nombre":"Pedro López","estado":"activo"}'
     *     [adm_usuario_id] => 5
     *     [transaccion] => "actualizar_estado"
     *     [sql_data] => "UPDATE usuarios SET estado = 'activo' WHERE id = 10"
     *     [valor_id] => 10
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`registro_id` no válido)**
     * ```php
     * Array
     * (
     *     [error] => "Error al obtener registro $registro_id debe ser mayor a 0"
     *     [data] => -1
     *     [es_final] => true
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`error al generar modelo`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar modelo usuarios"
     *     [data] => false
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`error al obtener el registro`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al obtener registro de usuarios"
     *     [data] => false
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`fallo al insertar la bitácora`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al insertar bitácora de usuarios"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si `$registro_id` es menor o igual a 0, si falla la generación del modelo,
     * si no se obtiene el registro correctamente o si la inserción en la bitácora falla.
     */
    private function aplica_bitacora(
        string $consulta, string $funcion, modelo $modelo, int $registro_id, string $tabla): array
    {

        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $registro_id debe ser mayor a 0',
                data: $registro_id, es_final: true);
        }
        $model = $modelo->genera_modelo(modelo: $tabla, namespace_model: $modelo->NAMESPACE);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo'.$tabla,data: $model);
        }

        $registro_bitacora = $model->registro(registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener registro de '.$tabla,data:$registro_bitacora);
        }

        $bitacora = $this->bitacora(consulta: $consulta, funcion: $funcion, modelo: $modelo,
            registro: $registro_bitacora);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar bitacora de '.$tabla,data:$bitacora);
        }
        return $bitacora;
    }

    /**
     * REG
     * Asigna y estructura los datos necesarios para registrar una transacción en la bitácora.
     *
     * Esta función valida la existencia de la sección en la bitácora, verifica que el usuario tenga
     * una sesión activa, y estructura los datos requeridos para el registro en la bitácora.
     *
     * ### Funcionamiento:
     * 1. **Valida que `adm_seccion_id` esté presente en `$seccion`.**
     * 2. **Verifica que `usuario_id` esté presente en `$_SESSION`.**
     * 3. **Asegura que `funcion` y `consulta` no sean cadenas vacías.**
     * 4. **Valida que `registro_id` del modelo sea un número mayor a 0.**
     * 5. **Estructura un array con la información relevante para la bitácora.**
     *
     * @param string $consulta Consulta SQL que se va a registrar en la bitácora.
     * @param string $funcion Nombre de la función que se está ejecutando en el proceso.
     * @param modelo $modelo Modelo en ejecución, que contiene el `registro_id` a validar.
     * @param array $registro Datos del registro afectado en la operación.
     * @param array $seccion Información de la sección de la bitácora.
     *
     * @return array Un array con los datos estructurados para la bitácora o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $_SESSION['usuario_id'] = 1;
     * $consulta = "UPDATE usuarios SET status = 'activo' WHERE id = 10";
     * $funcion = "actualizar_usuario";
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $modelo->registro_id = 10;
     * $registro = ["id" => 10, "nombre" => "Juan Pérez"];
     * $seccion = ["adm_seccion_id" => 3];
     *
     * $resultado = $bitacoras->asigna_registro_para_bitacora(
     *     consulta: $consulta,
     *     funcion: $funcion,
     *     modelo: $modelo,
     *     registro: $registro,
     *     seccion: $seccion
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (datos estructurados para la bitácora)**
     * ```php
     * Array
     * (
     *     [adm_seccion_id] => 3
     *     [status] => "activo"
     *     [registro] => '{"id":10,"nombre":"Juan Pérez"}'
     *     [adm_usuario_id] => 1
     *     [transaccion] => "actualizar_usuario"
     *     [sql_data] => "UPDATE usuarios SET status = 'activo' WHERE id = 10"
     *     [valor_id] => 10
     * )
     * ```
     *
     * **Caso 2: Error (`adm_seccion_id` no está presente en `$seccion`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar sección"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 3: Error (`usuario_id` no está en la sesión)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar SESSION"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 4: Error (`funcion` vacía)**
     * ```php
     * Array
     * (
     *     [error] => "Error $funcion no puede venir vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 5: Error (`consulta` vacía)**
     * ```php
     * Array
     * (
     *     [error] => "Error $consulta no puede venir vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 6: Error (`registro_id` no válido)**
     * ```php
     * Array
     * (
     *     [error] => "Error el id de $this->registro_id no puede ser menor a 0"
     *     [data] => 0
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 7: Error (`json_encode` falla al procesar el `registro`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar json de bitácora"
     *     [data] => "Exception: Malformed UTF-8 characters"
     * )
     * ```
     *
     * @throws errores Si `adm_seccion_id` o `usuario_id` no están definidos, si `funcion` o `consulta` están vacíos,
     * o si `registro_id` no es válido.
     */
    private function asigna_registro_para_bitacora(string $consulta,string $funcion, modelo $modelo,
                                                   array $registro, array $seccion): array
    {

        $keys = array('adm_seccion_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar seccion ',data:$valida);
        }

        $keys = array('usuario_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar SESSION ',data:$valida);
        }

        if($funcion === ''){
            return $this->error->error(mensaje: 'Error $funcion no puede venir vacia',data:$funcion, es_final: true);
        }
        if($consulta === ''){
            return $this->error->error(mensaje: 'Error $consulta no puede venir vacia',data:$consulta, es_final: true);
        }
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje: 'Error el id de $this->registro_id no puede ser menor a 0',
                data:$modelo->registro_id, es_final: true);
        }
        $registro_data['adm_seccion_id'] = $seccion['adm_seccion_id'];
        $registro_data['status'] = 'activo';
        try {
            $registro_data['registro'] = json_encode($registro, JSON_THROW_ON_ERROR);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al generar json de bitacora', data:$e);
        }
        $registro_data['adm_usuario_id'] = $_SESSION['usuario_id'];
        $registro_data['transaccion'] = $funcion;
        $registro_data['sql_data'] = $consulta;
        $registro_data['valor_id'] = $modelo->registro_id;

        return $registro_data;
    }

    /**
     * REG
     * Registra una transacción en la bitácora si el modelo tiene activada la opción `aplica_bitacora`.
     *
     * Este método se encarga de generar una bitácora validando la consulta SQL, el modelo y los datos relacionados.
     * Primero, obtiene el namespace del modelo y valida los datos necesarios. Luego, genera un registro de bitácora
     * y lo inserta en la base de datos. Si el modelo no tiene habilitada la opción `aplica_bitacora`, retorna un array vacío.
     *
     * ### 🔹 **Flujo de ejecución:**
     * 1. **Verifica** si `$modelo->aplica_bitacora` está activado.
     * 2. **Obtiene** el namespace del modelo con `clase_namespace()`.
     * 3. **Valida** los datos de la bitácora con `valida_data_bitacora()`.
     * 4. **Genera** la bitácora con `genera_bitacora()`.
     * 5. **Retorna** la bitácora insertada o un error si ocurre una falla.
     *
     * @param string $consulta Consulta SQL que se registrará en la bitácora.
     * @param string $funcion Nombre de la función o acción que se ejecuta.
     * @param modelo $modelo Instancia del modelo en uso, con los datos de conexión y `registro_id`.
     * @param array $registro Datos del registro afectado por la transacción.
     *
     * @return array Un array con la bitácora generada o vacío si `aplica_bitacora` está desactivado.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $consulta = "INSERT INTO usuarios (nombre, email) VALUES ('Juan Pérez', 'juan@example.com')";
     * $funcion = "crear_usuario";
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $modelo->registro_id = 15;
     * $modelo->aplica_bitacora = true;
     * $registro = ["id" => 15, "nombre" => "Juan Pérez", "email" => "juan@example.com"];
     *
     * $resultado = $bitacoras->bitacora(
     *     consulta: $consulta,
     *     funcion: $funcion,
     *     modelo: $modelo,
     *     registro: $registro
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Ejemplo de salida exitosa**
     * ```php
     * Array
     * (
     *     [id] => 205
     *     [adm_seccion_id] => 4
     *     [status] => "activo"
     *     [registro] => '{"id":15,"nombre":"Juan Pérez","email":"juan@example.com"}'
     *     [adm_usuario_id] => 2
     *     [transaccion] => "crear_usuario"
     *     [sql_data] => "INSERT INTO usuarios (nombre, email) VALUES ('Juan Pérez', 'juan@example.com')"
     *     [valor_id] => 15
     * )
     * ```
     *
     * ### **Ejemplo de salida cuando `aplica_bitacora` es `false`**
     * ```php
     * Array()
     * ```
     *
     * ### **Ejemplo de salida con error (`namespace del modelo no válido`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar namespace modelo"
     *     [data] => false
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`validación fallida`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar datos"
     *     [data] => false
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`fallo al generar la bitácora`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar bitácora en usuarios"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si falla la obtención del namespace, la validación de datos o la generación de la bitácora.
     */
    final public function bitacora(string $consulta, string $funcion, modelo $modelo, array $registro): array
    {
        $bitacora = array();
        if($modelo->aplica_bitacora){

            $data_ns = $this->clase_namespace(tabla: $modelo->tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
            }

            $valida = $this->valida_data_bitacora(
                consulta: $consulta, data_ns: $data_ns, funcion: $funcion,modelo:  $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
            }

            $r_bitacora = $this->genera_bitacora(consulta:  $consulta, funcion: $funcion, modelo: $modelo,
                registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al generar bitacora en '.$data_ns->tabla,data:$r_bitacora);
            }
            $bitacora = $r_bitacora;
        }

        return $bitacora;
    }

    /**
     * REG
     * Genera un objeto con el nombre de la tabla y su namespace correspondiente.
     *
     * Esta función toma el nombre de una tabla, lo limpia de espacios en blanco
     * y le asigna un namespace predeterminado (`models\`). Si la tabla está vacía
     * o mal escrita, devuelve un error.
     *
     * ### Funcionamiento:
     * 1. **Elimina espacios en blanco en el nombre de la tabla.**
     * 2. **Verifica que `$tabla` no esté vacía.**
     * 3. **Elimina el prefijo `models\` si ya está presente.**
     * 4. **Retorna un objeto `stdClass` con el nombre limpio de la tabla y su clase con namespace.**
     *
     * @param string $tabla Nombre de la tabla a procesar.
     *
     * @return stdClass|array Un objeto con el nombre de la tabla y su namespace,
     * o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $resultado = $bitacoras->clase_namespace(tabla: 'models\\usuarios');
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (tabla normalizada correctamente)**
     * ```php
     * stdClass Object
     * (
     *     [tabla] => "usuarios"
     *     [clase] => "models\usuarios"
     * )
     * ```
     *
     * **Caso 2: Error (nombre de la tabla vacío)**
     * ```php
     * Array
     * (
     *     [error] => "Error tabla vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 3: Error (nombre mal escrito o eliminado después de limpieza)**
     * ```php
     * Array
     * (
     *     [error] => "Error tabla vacía o mal escrita"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * @throws errores Si `$tabla` está vacía o si, después de limpiar el namespace, el nombre sigue siendo inválido.
     */
    private function clase_namespace(string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia',data:  $tabla, es_final: true);
        }
        $namespace = 'models\\';
        $tabla = str_replace($namespace,'',$tabla);

        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia o mal escrita',data:  $tabla, es_final: true);
        }

        $data = new stdClass();
        $data->tabla = $tabla;
        $data->clase = $namespace.$tabla;

        return$data;
    }

    /**
     * REG
     * Valida y genera un objeto con información del namespace del modelo basado en el nombre de la tabla.
     *
     * Esta función se encarga de validar que el nombre de la tabla no esté vacío,
     * generar el namespace correspondiente y verificar que la tabla generada sea válida.
     *
     * ### Funcionamiento:
     * 1. **Limpia espacios en blanco en el nombre de la tabla.**
     * 2. **Verifica que la tabla no esté vacía.**
     * 3. **Genera el namespace y la estructura del modelo llamando a `clase_namespace`.**
     * 4. **Verifica que la tabla generada no esté vacía.**
     * 5. **Devuelve un `stdClass` con el nombre de la tabla y su namespace, o un error si hay fallos.**
     *
     * @param string $tabla Nombre de la tabla que se validará y normalizará.
     *
     * @return array|stdClass Un objeto con el nombre de la tabla y su namespace,
     * o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $resultado = $bitacoras->data_ns_val(tabla: 'usuarios');
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (namespace generado correctamente)**
     * ```php
     * stdClass Object
     * (
     *     [tabla] => "usuarios"
     *     [clase] => "models\usuarios"
     * )
     * ```
     *
     * **Caso 2: Error (nombre de la tabla vacío)**
     * ```php
     * Array
     * (
     *     [error] => "Error tabla vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 3: Error (fallo al generar el namespace)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar namespace modelo"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 4: Error (nombre de la tabla inválido después de la normalización)**
     * ```php
     * Array
     * (
     *     [error] => "Error this->tabla no puede venir vacío"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * @throws errores Si `$tabla` está vacía, si la normalización del modelo falla
     * o si el namespace generado no es válido.
     */
    private function data_ns_val(string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia',data:  $tabla, es_final: true);
        }
        $data_ns = $this->clase_namespace(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
        }

        if($data_ns->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio',data: $data_ns->tabla,
                es_final: true);
        }

        return $data_ns;
    }

    /**
     * REG
     * Ejecuta una transacción en la base de datos y registra la operación en la bitácora.
     *
     * Esta función toma como entrada una consulta SQL, la ejecuta en la base de datos,
     * y posteriormente registra la transacción en la bitácora con los datos asociados.
     *
     * ### 🔹 **Flujo de ejecución:**
     * 1. **Verifica si la consulta SQL está vacía; en ese caso, usa la consulta del modelo.**
     * 2. **Ejecuta la consulta SQL en la base de datos.**
     * 3. **Si la consulta es exitosa, llama a `aplica_bitacora` para registrar la operación.**
     * 4. **Retorna los datos de la bitácora o un error si ocurre un fallo.**
     *
     * @param string $tabla Nombre de la tabla afectada por la transacción.
     * @param string $funcion Nombre de la función o acción que se está ejecutando.
     * @param modelo $modelo Instancia del modelo que ejecuta la consulta.
     * @param int $registro_id Identificador del registro afectado en la base de datos.
     * @param string $sql (Opcional) Consulta SQL que se ejecutará. Si está vacía, se toma del modelo.
     *
     * @return array Un array con los datos de la bitácora generada o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $modelo->consulta = "UPDATE usuarios SET estado = 'activo' WHERE id = 10";
     * $funcion = "actualizar_estado";
     * $registro_id = 10;
     * $tabla = "usuarios";
     *
     * $resultado = $bitacoras->ejecuta_transaccion(
     *     tabla: $tabla,
     *     funcion: $funcion,
     *     modelo: $modelo,
     *     registro_id: $registro_id
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Ejemplo de salida exitosa**
     * ```php
     * Array
     * (
     *     [id] => 312
     *     [adm_seccion_id] => 7
     *     [status] => "activo"
     *     [registro] => '{"id":10,"nombre":"Pedro López","estado":"activo"}'
     *     [adm_usuario_id] => 5
     *     [transaccion] => "actualizar_estado"
     *     [sql_data] => "UPDATE usuarios SET estado = 'activo' WHERE id = 10"
     *     [valor_id] => 10
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`consulta` vacía en el modelo)**
     * ```php
     * Array
     * (
     *     [error] => "La consulta no puede venir vacía del modelo usuarios"
     *     [data] => ''
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`fallo al ejecutar SQL`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al ejecutar sql en usuarios"
     *     [data] => "Error de sintaxis en la consulta"
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`fallo al insertar en bitácora`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al insertar bitácora en usuarios"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si la consulta está vacía, si la ejecución SQL falla o si la inserción en la bitácora falla.
     */
    final public function ejecuta_transaccion(
        string $tabla, string $funcion,  modelo $modelo, int $registro_id , string $sql = ''):array{
        $consulta =trim($sql);
        if($sql === '') {
            $consulta = $modelo->consulta;
        }
        if($modelo->consulta === ''){
            return $this->error->error(mensaje: 'La consulta no puede venir vacia del modelo '.$modelo->tabla,
                data: $modelo->consulta);
        }
        $resultado = $modelo->ejecuta_sql(consulta: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql en '.$tabla,data:$resultado);
        }
        $bitacora = $this->aplica_bitacora(consulta: $consulta, funcion: $funcion,modelo: $modelo,
            registro_id:  $registro_id, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar bitacora en '.$tabla,data:$bitacora);
        }

        return $bitacora;
    }

    /**
     * REG
     * Genera un registro de bitácora en la base de datos.
     *
     * Este método valida los datos de entrada, instancia el modelo `adm_bitacora`,
     * genera los datos estructurados para la bitácora y luego inserta el registro
     * en la base de datos. En caso de errores en cualquiera de estos pasos,
     * retorna un array con la información del error.
     *
     * ### 🔹 **Flujo de ejecución:**
     * 1. **Valida** los datos de la bitácora (`consulta`, `funcion`, `modelo`).
     * 2. **Instancia** el modelo `adm_bitacora`.
     * 3. **Genera** los datos para la bitácora con `maqueta_data_bitacora()`.
     * 4. **Inserta** el registro en la base de datos con `alta_bd()`.
     * 5. **Retorna** el resultado de la inserción o un error en caso de fallos.
     *
     * @param string $consulta Consulta SQL que se registrará en la bitácora.
     * @param string $funcion Nombre de la función o acción que se está ejecutando.
     * @param modelo $modelo Instancia del modelo que contiene la conexión y `registro_id`.
     * @param array $registro Datos del registro afectado por la transacción.
     *
     * @return array|stdClass Un objeto con el registro insertado o un array de error si ocurre un fallo.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $consulta = "UPDATE usuarios SET status = 'activo' WHERE id = 10";
     * $funcion = "actualizar_usuario";
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $modelo->registro_id = 10;
     * $registro = ["id" => 10, "nombre" => "Juan Pérez"];
     *
     * $resultado = $bitacoras->genera_bitacora(
     *     consulta: $consulta,
     *     funcion: $funcion,
     *     modelo: $modelo,
     *     registro: $registro
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Ejemplo de salida exitosa**
     * ```php
     * stdClass Object
     * (
     *     [id] => 105
     *     [adm_seccion_id] => 3
     *     [status] => "activo"
     *     [registro] => '{"id":10,"nombre":"Juan Pérez"}'
     *     [adm_usuario_id] => 1
     *     [transaccion] => "actualizar_usuario"
     *     [sql_data] => "UPDATE usuarios SET status = 'activo' WHERE id = 10"
     *     [valor_id] => 10
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`validación fallida`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar valores"
     *     [data] => false
     * )
     * ```
     *
     * ### **Ejemplo de salida con error (`fallo en la inserción`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al insertar bitácora"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si los datos de entrada no son válidos o si la inserción falla.
     */
    private function genera_bitacora(
        string $consulta, string $funcion, modelo $modelo, array $registro): array|stdClass{

        $val = $this->valida_bitacora(consulta:$consulta,funcion:  $funcion, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar valores', data: $val);
        }

        $bitacora_modelo = (new adm_bitacora($modelo ->link));
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener bitacora',data:$bitacora_modelo);
        }

        $bitacora_modelo->registro = $this->maqueta_data_bitacora(consulta:  $consulta, funcion: $funcion,
            modelo: $modelo, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener MAQUETAR REGISTRO PARA BITACORA',
                data:$bitacora_modelo->registro);
        }
        $r_bitacora = $bitacora_modelo->alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar bitacora',data:$r_bitacora);
        }
        return $r_bitacora;
    }

    /**
     * REG
     * Genera un array de datos estructurados para registrar en la bitácora.
     *
     * Esta función valida los datos de entrada, obtiene la sección de la bitácora
     * correspondiente y estructura los datos necesarios para el registro de la transacción.
     *
     * ### Funcionamiento:
     * 1. **Valida los datos de la bitácora (`consulta`, `funcion`, `modelo`).**
     * 2. **Obtiene la sección del menú correspondiente a la tabla del modelo.**
     * 3. **Estructura los datos de bitácora llamando a `asigna_registro_para_bitacora`.**
     * 4. **Devuelve el array con los datos estructurados o un error en caso de fallos.**
     *
     * @param string $consulta Consulta SQL que se va a registrar en la bitácora.
     * @param string $funcion Nombre de la función que se está ejecutando en el proceso.
     * @param modelo $modelo Modelo en ejecución, que contiene el `registro_id` a validar.
     * @param array $registro Datos del registro afectado en la operación.
     *
     * @return array Un array con los datos estructurados para la bitácora o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $_SESSION['usuario_id'] = 1;
     * $consulta = "UPDATE usuarios SET status = 'activo' WHERE id = 10";
     * $funcion = "actualizar_usuario";
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $modelo->registro_id = 10;
     * $registro = ["id" => 10, "nombre" => "Juan Pérez"];
     *
     * $resultado = $bitacoras->maqueta_data_bitacora(
     *     consulta: $consulta,
     *     funcion: $funcion,
     *     modelo: $modelo,
     *     registro: $registro
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (datos estructurados para la bitácora)**
     * ```php
     * Array
     * (
     *     [adm_seccion_id] => 3
     *     [status] => "activo"
     *     [registro] => '{"id":10,"nombre":"Juan Pérez"}'
     *     [adm_usuario_id] => 1
     *     [transaccion] => "actualizar_usuario"
     *     [sql_data] => "UPDATE usuarios SET status = 'activo' WHERE id = 10"
     *     [valor_id] => 10
     * )
     * ```
     *
     * **Caso 2: Error (fallo en la validación de los valores de la bitácora)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar valores"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 3: Error (fallo al obtener la sección del menú)**
     * ```php
     * Array
     * (
     *     [error] => "Error al obtener sección"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 4: Error (fallo al estructurar el registro para la bitácora)**
     * ```php
     * Array
     * (
     *     [error] => "Error al obtener MAQUETAR REGISTRO PARA BITACORA"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si la validación de la bitácora falla, si no se encuentra la sección correspondiente
     * o si no se puede estructurar correctamente el registro de la bitácora.
     */
    private function maqueta_data_bitacora(string $consulta, string $funcion, modelo $modelo, array $registro):array{


        $val = $this->valida_bitacora(consulta: $consulta,funcion:  $funcion, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar valores', data: $val);
        }

        $seccion_menu = $this->obten_seccion_bitacora(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener seccion', data:$seccion_menu);
        }

        $registro = $this->asigna_registro_para_bitacora(consulta: $consulta, funcion: $funcion,
            modelo: $modelo, registro: $registro, seccion: $seccion_menu);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener MAQUETAR REGISTRO PARA BITACORA', data:$registro);
        }


        return $registro;
    }

    /**
     * REG
     * Obtiene la sección de bitácora correspondiente a la tabla del modelo.
     *
     * Esta función genera el namespace del modelo, crea una instancia de `adm_seccion`,
     * y consulta la base de datos para obtener la sección correspondiente a la tabla del modelo.
     *
     * ### Funcionamiento:
     * 1. **Valida y genera el namespace del modelo utilizando `data_ns_val`.**
     * 2. **Crea una instancia de `adm_seccion` con la conexión del modelo.**
     * 3. **Genera un filtro basado en la descripción de la sección (`tabla`).**
     * 4. **Consulta la base de datos con `filtro_and` para obtener la sección.**
     * 5. **Valida si existen registros y devuelve la primera coincidencia.**
     *
     * @param modelo $modelo Instancia del modelo que contiene la tabla a consultar.
     *
     * @return array Datos de la sección de bitácora, o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $resultado = $bitacoras->obten_seccion_bitacora(modelo: $modelo);
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (sección encontrada)**
     * ```php
     * Array
     * (
     *     [id] => 3
     *     [descripcion] => "usuarios"
     *     [status] => "activo"
     * )
     * ```
     *
     * **Caso 2: Error (fallo al generar el namespace del modelo)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar namespace modelo"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 3: Error (fallo al crear la instancia de `adm_seccion`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar modelo"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 4: Error (no se encontró la sección de la tabla)**
     * ```php
     * Array
     * (
     *     [error] => "Error no existe la sección menú"
     *     [data] => []
     * )
     * ```
     *
     * @throws errores Si la tabla no es válida, si no se puede generar el modelo,
     * o si no existen registros de la sección en la base de datos.
     */
    private function obten_seccion_bitacora(modelo $modelo): array
    {

        $data_ns = $this->data_ns_val(tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
        }

        $seccion_menu_modelo = (new adm_seccion(link: $modelo->link));
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar modelo',data:$seccion_menu_modelo);
        }

        $filtro['adm_seccion.descripcion'] = $data_ns->tabla;
        $r_seccion_menu = $seccion_menu_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener seccion menu',data:$r_seccion_menu);
        }
        if((int)$r_seccion_menu->n_registros === 0){
            return $this->error->error(mensaje:'Error no existe la seccion menu',data:$r_seccion_menu);
        }
        return $r_seccion_menu->registros[0];
    }

    /**
     * REG
     * Valida los datos esenciales antes de registrar una transacción en la bitácora.
     *
     * Esta función se encarga de verificar que la consulta SQL, la función y el identificador del
     * registro del modelo sean correctos antes de proceder con la bitácora.
     *
     * ### Funcionamiento:
     * 1. **Verifica que `funcion` no sea una cadena vacía.**
     * 2. **Verifica que `consulta` no sea una cadena vacía.**
     * 3. **Valida que `registro_id` en el modelo sea un número mayor a 0.**
     * 4. **Devuelve `true` si todas las validaciones pasan, o un error en caso contrario.**
     *
     * @param string $consulta Consulta SQL que se va a registrar en la bitácora.
     * @param string $funcion Nombre de la función que se está ejecutando en el proceso.
     * @param modelo $modelo Modelo en ejecución, que contiene el `registro_id` a validar.
     *
     * @return bool|array `true` si los datos son válidos, o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $consulta = "UPDATE usuarios SET status = 'activo' WHERE id = 10";
     * $funcion = "actualizar_usuario";
     * $modelo = new modelo();
     * $modelo->registro_id = 10;
     *
     * $resultado = $bitacoras->val_bitacora(consulta: $consulta, funcion: $funcion, modelo: $modelo);
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (datos válidos para la bitácora)**
     * ```php
     * true
     * ```
     *
     * **Caso 2: Error (`funcion` vacía)**
     * ```php
     * Array
     * (
     *     [error] => "Error $funcion no puede venir vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 3: Error (`consulta` vacía)**
     * ```php
     * Array
     * (
     *     [error] => "Error $consulta no puede venir vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 4: Error (`registro_id` no válido)**
     * ```php
     * Array
     * (
     *     [error] => "Error el id de $this->registro_id no puede ser menor a 0"
     *     [data] => 0
     *     [es_final] => true
     * )
     * ```
     *
     * @throws errores Si `$funcion` o `$consulta` están vacíos, o si `$modelo->registro_id` es menor o igual a 0.
     */
    private function val_bitacora(string $consulta, string $funcion, modelo $modelo): bool|array
    {
        if($funcion === ''){
            return $this->error->error(mensaje:'Error $funcion no puede venir vacia',data:$funcion, es_final: true);
        }
        if($consulta === ''){
            return $this->error->error(mensaje:'Error $consulta no puede venir vacia',data:$consulta, es_final: true);
        }
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje:'Error el id de $this->registro_id no puede ser menor a 0',
                data:$modelo->registro_id, es_final: true);
        }
        return true;
    }

    /**
     * REG
     * Valida los datos esenciales antes de registrar una transacción en la bitácora.
     *
     * Esta función verifica que la sesión del usuario sea válida, que el nombre de la tabla sea correcto
     * y que los datos de la bitácora (consulta SQL, función y registro del modelo) sean adecuados antes de proceder.
     *
     * ### Funcionamiento:
     * 1. **Verifica que `usuario_id` esté presente en la sesión (`$_SESSION`).**
     * 2. **Obtiene y valida el namespace del modelo llamando a `data_ns_val`.**
     * 3. **Llama a `val_bitacora` para validar la consulta, función y `registro_id` del modelo.**
     * 4. **Devuelve `true` si todas las validaciones pasan, o un error en caso contrario.**
     *
     * @param string $consulta Consulta SQL que se va a registrar en la bitácora.
     * @param string $funcion Nombre de la función que se está ejecutando en el proceso.
     * @param modelo $modelo Modelo en ejecución, que contiene el `registro_id` a validar.
     *
     * @return bool|array `true` si los datos son válidos, o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $_SESSION['usuario_id'] = 1;
     * $consulta = "UPDATE usuarios SET status = 'activo' WHERE id = 10";
     * $funcion = "actualizar_usuario";
     * $modelo = new modelo();
     * $modelo->tabla = "usuarios";
     * $modelo->registro_id = 10;
     *
     * $resultado = $bitacoras->valida_bitacora(consulta: $consulta, funcion: $funcion, modelo: $modelo);
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (datos válidos para la bitácora)**
     * ```php
     * true
     * ```
     *
     * **Caso 2: Error (`usuario_id` no está presente en la sesión)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar SESSION"
     *     [data] => false
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 3: Error (fallo al generar el namespace del modelo)**
     * ```php
     * Array
     * (
     *     [error] => "Error al generar namespace modelo"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 4: Error (fallo en la validación de la bitácora)**
     * *(Por ejemplo, si la consulta está vacía, la función está vacía o `registro_id` es inválido)*
     * ```php
     * Array
     * (
     *     [error] => "Error al validar valores"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si `usuario_id` no está en la sesión, si el namespace del modelo no es válido,
     * o si los datos de la bitácora (consulta, función y `registro_id`) no son correctos.
     */
    private function valida_bitacora(string $consulta, string $funcion, modelo $modelo): bool|array
    {
        $keys = array('usuario_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar SESSION ',data:$valida, es_final: true);
        }

        $data_ns = $this->data_ns_val(tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
        }
        $val = $this->val_bitacora(consulta: $consulta,funcion: $funcion,modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar valores', data: $val);
        }

        return true;
    }

    /**
     * REG
     * Valida los datos necesarios antes de registrar una transacción en la bitácora.
     *
     * Esta función se encarga de validar que la tabla, función, consulta SQL y el identificador del registro
     * sean correctos antes de proceder con la inserción en la bitácora.
     *
     * ### Funcionamiento:
     * 1. **Verifica que `tabla` exista en el objeto `data_ns`.**
     * 2. **Asegura que `tabla` no esté vacía después de la validación.**
     * 3. **Verifica que `funcion` y `consulta` no sean cadenas vacías.**
     * 4. **Valida que `registro_id` en el modelo sea un valor válido (> 0).**
     * 5. **Devuelve `true` si todas las validaciones pasan, o un error en caso contrario.**
     *
     * @param string $consulta Consulta SQL que se va a registrar en la bitácora.
     * @param stdClass $data_ns Objeto con información de la tabla y el modelo asociado.
     * @param string $funcion Nombre de la función que se está ejecutando en el proceso.
     * @param modelo $modelo Modelo en ejecución, que contiene el `registro_id` a validar.
     *
     * @return bool|array `true` si los datos son válidos, o un **array de error** si hay problemas.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $bitacoras = new bitacoras();
     * $data_ns = new stdClass();
     * $data_ns->tabla = "usuarios";
     * $consulta = "UPDATE usuarios SET status = 'activo' WHERE id = 10";
     * $funcion = "actualizar_usuario";
     * $modelo = new modelo();
     * $modelo->registro_id = 10;
     *
     * $resultado = $bitacoras->valida_data_bitacora(consulta: $consulta, data_ns: $data_ns, funcion: $funcion, modelo: $modelo);
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (datos válidos para la bitácora)**
     * ```php
     * true
     * ```
     *
     * **Caso 2: Error (falta la clave `tabla` en `$data_ns`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar data_ns"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 3: Error (`tabla` está vacía)**
     * ```php
     * Array
     * (
     *     [error] => "Error this->tabla no puede venir vacío"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 4: Error (`funcion` vacía)**
     * ```php
     * Array
     * (
     *     [error] => "Error $funcion no puede venir vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 5: Error (`consulta` vacía)**
     * ```php
     * Array
     * (
     *     [error] => "Error $consulta no puede venir vacía"
     *     [data] => ''
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 6: Error (`registro_id` no válido)**
     * ```php
     * Array
     * (
     *     [error] => "Error el id de $this->registro_id no puede ser menor a 0"
     *     [data] => 0
     *     [es_final] => true
     * )
     * ```
     *
     * @throws errores Si `$data_ns->tabla`, `$funcion`, `$consulta` están vacíos, o si `$modelo->registro_id` no es válido.
     */
    private function valida_data_bitacora(
        string $consulta, stdClass $data_ns, string $funcion, modelo $modelo): bool|array
    {
        $keys = array('tabla');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $data_ns);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al al validar data_ns',data:$valida);
        }
        if($data_ns->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio',data: $data_ns->tabla,
                es_final: true);
        }
        if($funcion === ''){
            return $this->error->error(mensaje:'Error $funcion no puede venir vacia',data:$funcion, es_final: true);
        }
        if($consulta === ''){
            return $this->error->error(mensaje:'Error $consulta no puede venir vacia',data:$consulta, es_final: true);
        }
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje:'Error el id de $this->registro_id no puede ser menor a 0',
                data: $modelo->registro_id, es_final: true);
        }
        return true;
    }



}
