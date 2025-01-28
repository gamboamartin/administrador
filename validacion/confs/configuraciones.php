<?php
namespace validacion\confs;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;
use Throwable;

class configuraciones extends validacion {


    /**
     * TOTAL
     * Función para validar una configuración especificada.
     *
     * @param stdClass $paths_conf - Ruta de la configuración a validar.
     * @param string $tipo_conf - El tipo de configuración a validar.
     * @return bool|array Devuelve true si la validación fue exitosa, o un error en caso contrario.
     * @version 16.24.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.validacion.confs.configuraciones.valida_conf.21.28.0
     */
    private function valida_conf(stdClass $paths_conf,string $tipo_conf): bool|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf, es_final: true);
        }

        $valida = $this->valida_conf_file(paths_conf:$paths_conf, tipo_conf:$tipo_conf);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar $tipo_conf.php",data:$valida);
        }
        $valida = $this->valida_conf_composer(tipo_conf: $tipo_conf);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar $tipo_conf.php",data:$valida);
        }
        return true;
    }

    /**
     * TOTAL
     * Valida las configuraciones de la aplicación.
     *
     * Esta función verifica las configuraciones de `generales`, `database` y `views`.
     * Retorna `true` si todas las configuraciones son válidas.
     * Si alguna configuración no es válida, se retorna un array con el mensaje de error y los datos de validación.
     *
     * @param stdClass $paths_conf  Las rutas de las configuraciones a validar.
     * @return bool|array           `true` si todas las configuraciones son válidas.
     *                              Si no, un array con el mensaje del error y los datos de validación.
     *
     * @throws errores            Lanza una excepción si ocurre un error durante la validación.
     *
     * @example
     * ```php
     * $configuraciones = new Configuraciones();
     * $paths_conf = new stdClass();
     * $paths_conf->generales = '/path/to/generales.php';
     * $paths_conf->database = '/path/to/database.php';
     * $paths_conf->views = '/path/to/views.php';
     * $result = $configuraciones->valida_confs($paths_conf);
     * if (is_array($result)) {
     *     echo "Se produjo un error al validar las configuraciones: " . $result['mensaje'];
     * } else {
     *     echo "Las configuraciones se validaron correctamente.";
     * }
     * ```
     * @version 16.79.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.validacion.confs.configuraciones.valida_confs.21.28.0
     */
    final public function valida_confs(stdClass $paths_conf): bool|array
    {
        $tipo_confs[] = 'generales';
        $tipo_confs[] = 'database';
        $tipo_confs[] = 'views';

        foreach ($tipo_confs as $tipo_conf){
            $valida = $this->valida_conf(paths_conf: $paths_conf, tipo_conf: $tipo_conf);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al validar $tipo_conf.php",data:$valida);
            }
        }
        return true;
    }


    /**
     * TOTAL
     * Esta función valida la configuración necesaria para un proyecto Composer.
     *
     * @param   string $tipo_conf El tipo de configuración que se desea validar.
     * @return  bool|array Si la validación es exitosa, devuelve true.
     *                     Si la validación falla, entonces se devolverá un array
     *                     con el error que ocurrió.
     * @throws  errores Excepción lanzada si ocurre un error al codificar el objeto a formato JSON.
     *
     * @author  Martin Gamboa
     * @version 16.0.0
     * @urhttps://github.com/gamboamartin/administrador/wiki/administrador.validacion.confs.configuraciones.valida_conf_composer.21.28.0
     *
     */
    private function valida_conf_composer(string $tipo_conf): true|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf, es_final: true);
        }

        if(!class_exists("config\\$tipo_conf")){

            $data_composer['autoload']['psr-4']['config\\'] = "config/";
            try {
                $llave_composer = json_encode($data_composer, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: $mensaje,data: $e, es_final: true);
            }

            $mensaje = "Agrega el registro $llave_composer en composer.json despues ejecuta composer update";
            return $this->error->error(mensaje: $mensaje,data: '', es_final: true);
        }
        return true;
    }

    /**
     * REG
     * Valida la existencia de un archivo de configuración según un tipo de configuración especificado.
     *
     * Esta función verifica si un archivo de configuración existe en la ruta especificada. Si el archivo no se encuentra,
     * intenta buscar un archivo de ejemplo en una ubicación predeterminada. En caso de no encontrarlo, genera un mensaje
     * de error detallado que incluye un ejemplo del contenido del archivo esperado.
     *
     * @param stdClass $paths_conf Objeto con las rutas configuradas para los archivos de configuración.
     *                             - Las propiedades del objeto corresponden a diferentes tipos de configuración.
     *                             - Ejemplo: `{"database": "config/database.php", "cache": "config/cache.php"}`
     * @param string $tipo_conf Nombre del tipo de configuración a validar.
     *                          - No puede estar vacío.
     *                          - Ejemplo: 'database'.
     *
     * @return true|array Devuelve `true` si el archivo de configuración existe. Si ocurre un error, devuelve un array con los
     *                    detalles del problema, incluyendo un ejemplo del archivo esperado si es posible.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $paths_conf = (object)[
     *     'database' => 'config/database.php',
     *     'cache' => 'config/cache.php'
     * ];
     * $tipo_conf = 'database';
     *
     * $resultado = $this->valida_conf_file(paths_conf: $paths_conf, tipo_conf: $tipo_conf);
     *
     * // Resultado esperado:
     * // true (Si el archivo 'config/database.php' existe)
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: Archivo no encontrado
     * $paths_conf = (object)[
     *     'database' => 'config/database.php'
     * ];
     * $tipo_conf = 'database';
     *
     * $resultado = $this->valida_conf_file(paths_conf: $paths_conf, tipo_conf: $tipo_conf);
     *
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error no existe el archivo config/database.php favor de generar la ruta
     * //                 config/database.php basado en la estructura del ejemplo vendor/gamboa.martin/configuraciones/config/database.php.example',
     * //   'data' => 'Contenido del archivo de ejemplo codificado en HTML'
     * // ]
     *
     * // Caso 2: Tipo de configuración vacío
     * $paths_conf = (object)[
     *     'database' => 'config/database.php'
     * ];
     * $tipo_conf = '';
     *
     * $resultado = $this->valida_conf_file(paths_conf: $paths_conf, tipo_conf: $tipo_conf);
     *
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $tipo_conf esta vacio',
     * //   'data' => ''
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de parámetros:**
     *    - Verifica que `$tipo_conf` no esté vacío.
     * 2. **Resolución de la ruta del archivo:**
     *    - Obtiene la ruta del archivo desde `$paths_conf->$tipo_conf` o usa una ruta predeterminada.
     * 3. **Verificación de la existencia del archivo:**
     *    - Comprueba si el archivo existe en la ruta especificada.
     * 4. **Búsqueda del archivo de ejemplo:**
     *    - Si el archivo no existe, intenta localizar un archivo de ejemplo en `vendor/gamboa.martin/configuraciones/`.
     * 5. **Generación del mensaje de error:**
     *    - Si el archivo y su ejemplo no existen, se genera un mensaje de error detallado con sugerencias.
     * 6. **Retorno del resultado:**
     *    - Devuelve `true` si el archivo existe, o un array con los detalles del error.
     *
     * ### Casos de uso:
     * - **Contexto:** Validar la existencia de archivos de configuración antes de inicializar una aplicación.
     * - **Ejemplo real:** Verificar la existencia de `config/database.php` antes de establecer la conexión a la base de datos.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$tipo_conf` contenga un valor válido que corresponda a una propiedad de `$paths_conf`.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes claros y detallados.
     */

    private function valida_conf_file(stdClass $paths_conf, string $tipo_conf): true|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf, es_final: true);
        }

        $path = $paths_conf->$tipo_conf ?? "config/$tipo_conf.php";
        if(!file_exists($path)){

            $path_e = "vendor/gamboa.martin/configuraciones/$path.example";
            $data = '';
            if(file_exists("././$path_e")) {
                $data = htmlentities(file_get_contents("././$path_e"));
            }

            $data.="<br><br>$data><br><br>";

            return $this->error->error(mensaje: "Error no existe el archivo $path favor de generar 
            la ruta $path basado en la estructura del ejemplo $path_e",data: $data,es_final: true);
        }
        return true;
    }

}
