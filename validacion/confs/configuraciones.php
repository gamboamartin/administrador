<?php
namespace validacion\confs;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;
use Throwable;

class configuraciones extends validacion {


    /**
     * REG
     * Valida una configuración específica de la aplicación, verificando que:
     * - El archivo de configuración exista.
     * - El namespace esté correctamente registrado en `composer.json`.
     *
     * Este método combina dos validaciones críticas:
     * 1. Verifica si el archivo de configuración (como `config/database.php`) existe.
     * 2. Verifica si el namespace correspondiente (`config\`) está registrado en el autoload de Composer.
     *
     * En caso de error en cualquiera de las validaciones, se devuelve un array detallado con el mensaje
     * del error, datos asociados y una posible sugerencia de solución.
     *
     * @param stdClass $paths_conf Objeto con las rutas de los archivos de configuración a validar.
     *                              Cada propiedad debe corresponder al tipo de configuración.
     *                              Ejemplo: `$paths_conf->database = "config/database.php";`
     *
     * @param string $tipo_conf Nombre del tipo de configuración a validar.
     *                          Debe coincidir con una propiedad de `$paths_conf`, como `generales`, `database`, etc.
     *
     * @return bool|array Devuelve `true` si ambas validaciones son exitosas.
     *                    Devuelve un array con mensaje y datos si ocurre algún error.
     *
     * @throws errores Si ocurre un error en alguna de las validaciones internas (`valida_conf_file`, `valida_conf_composer`).
     *
     * @version 16.24.0
     * @author Martin Gamboa
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.validacion.confs.configuraciones.valida_conf.21.28.0
     *
     * @example Validación exitosa:
     * ```php
     * $paths_conf = new stdClass();
     * $paths_conf->database = 'config/database.php';
     * $validador = new \validacion\confs\configuraciones();
     * $resultado = $validador->valida_conf($paths_conf, 'database');
     * var_dump($resultado); // true
     * ```
     *
     * @example Validación con error por archivo inexistente:
     * ```php
     * $paths_conf = new stdClass();
     * $paths_conf->database = 'config/no_existe.php';
     * $validador = new \validacion\confs\configuraciones();
     * $resultado = $validador->valida_conf($paths_conf, 'database');
     * print_r($resultado);
     * // Resultado esperado: array con mensaje indicando que no existe el archivo
     * ```
     *
     * @example Validación con error por tipo de configuración vacío:
     * ```php
     * $paths_conf = new stdClass();
     * $paths_conf->database = 'config/database.php';
     * $validador = new \validacion\confs\configuraciones();
     * $resultado = $validador->valida_conf($paths_conf, '');
     * print_r($resultado);
     * // Resultado esperado: ['mensaje' => 'Error $tipo_conf esta vacio', ...]
     * ```
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
     * REG
     * Valida múltiples configuraciones críticas del sistema (`generales`, `database`, `views`).
     *
     * Este método itera sobre los tipos de configuración requeridos y valida para cada uno:
     * - Que el archivo de configuración exista.
     * - Que el namespace esté registrado correctamente en Composer (`autoload.psr-4`).
     *
     * Si alguna configuración falla su validación, se devuelve un array con el mensaje de error y
     * los datos detallados del fallo.
     *
     * @param stdClass $paths_conf Objeto que contiene las rutas a los archivos de configuración.
     *                              Debe incluir las propiedades: `generales`, `database`, `views`.
     *                              Ejemplo:
     * ```php
     * $paths_conf = new stdClass();
     * $paths_conf->generales = 'config/generales.php';
     * $paths_conf->database = 'config/database.php';
     * $paths_conf->views = 'config/views.php';
     * ```
     *
     * @return bool|array Devuelve `true` si todas las configuraciones son válidas.
     *                    Devuelve un `array` de error si alguna validación falla, con mensaje y datos asociados.
     *
     * @throws errores Lanza una excepción o devuelve array con mensaje si alguna validación de archivo o namespace falla.
     *
     * @version 16.79.0
     * @author Martin Gamboa
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.validacion.confs.configuraciones.valida_confs.21.28.0
     *
     * @example Validación correcta de todas las configuraciones:
     * ```php
     * use validacion\confs\configuraciones;
     *
     * $validador = new configuraciones();
     * $paths_conf = new stdClass();
     * $paths_conf->generales = 'config/generales.php';
     * $paths_conf->database = 'config/database.php';
     * $paths_conf->views = 'config/views.php';
     *
     * $resultado = $validador->valida_confs($paths_conf);
     * if ($resultado === true) {
     *     echo "Todas las configuraciones están correctamente validadas.";
     * }
     * ```
     *
     * @example Error al validar una configuración inexistente:
     * ```php
     * $paths_conf = new stdClass();
     * $paths_conf->generales = 'config/generales.php';
     * $paths_conf->database = 'config/no_existe.php';
     * $paths_conf->views = 'config/views.php';
     *
     * $resultado = $validador->valida_confs($paths_conf);
     * if (is_array($resultado)) {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
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
     * REG
     * Valida que la configuración esté registrada correctamente en `composer.json`.
     *
     * Esta función verifica si la clase de configuración especificada existe en el autoload de Composer
     * bajo el namespace `config\`. Si no está registrada, se genera un mensaje de error indicando cómo
     * registrar el namespace en el `composer.json`, utilizando PSR-4.
     *
     * También construye un ejemplo de la clave que debe agregarse al `composer.json` en caso de estar ausente,
     * y sugiere ejecutar `composer update` después de la modificación.
     *
     * @param string $tipo_conf El nombre del archivo de configuración (sin extensión) que se desea validar.
     *                          Por ejemplo: `generales`, `database`, `views`.
     *
     * @return true|array Devuelve `true` si la clase está registrada correctamente.
     *                    Devuelve un array con el mensaje de error si no se encuentra la clase.
     *
     * @throws errores Si ocurre un error al codificar el arreglo JSON con `json_encode`.
     *
     * @version 16.0.0
     * @author Martin Gamboa
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.validacion.confs.configuraciones.valida_conf_composer.21.28.0
     *
     * @example Validación exitosa:
     * ```php
     * $validador = new \validacion\confs\configuraciones();
     * $resultado = $validador->valida_conf_composer('generales');
     * var_dump($resultado); // true
     * ```
     *
     * @example Validación fallida (namespace no registrado en composer.json):
     * ```php
     * // Si no está registrado "config\\" => "config/" en composer.json:
     * $resultado = $validador->valida_conf_composer('generales');
     * print_r($resultado);
     * // Resultado:
     * // [
     * //   'mensaje' => 'Agrega el registro {"autoload":{"psr-4":{"config\\":"config/"}}} en composer.json despues ejecuta composer update',
     * //   'data' => '',
     * //   'es_final' => true,
     * // ]
     * ```
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
