<?php
namespace validacion\confs;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;
use Throwable;

class configuraciones extends validacion {


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función para validar una configuración especificada.
     *
     * @param stdClass $paths_conf - Ruta de la configuración a validar.
     * @param string $tipo_conf - El tipo de configuración a validar.
     * @return bool|array Devuelve true si la validación fue exitosa, o un error en caso contrario.
     * @version 16.24.0
     */
    private function valida_conf(stdClass $paths_conf,string $tipo_conf): bool|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf);
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
     * POR DOCUMENTAR WN WIKI FINAL REV
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
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Verifica la existencia y validez de un archivo de configuración específico.
     *
     * @param stdClass $paths_conf Contiene los paths de todos los archivos de configuración.
     * @param string   $tipo_conf  Representa el tipo de archivo de configuración que se va a verificar.
     *
     * @return bool|array Retorna `true` si el archivo de configuración es válido.
     *                    En caso contrario, retorna un array con la información del error.
     *
     * @throws errores Lanza una excepción si `$tipo_conf` es una cadena vacía
     *                   o si el archivo de configuración no existe.
     *
     * @example
     *   $configPath = new stdClass();
     *   $configPath->myConfig = "myConfigPath/myConfig.php";
     *
     *   $isValid = $this->valida_conf_file($configPath, "myConfig");
     *
     *   if ($isValid) {
     *       echo "El archivo de configuración es válido.";
     *   } else {
     *       echo "El archivo de configuración no es válido.";
     *   }
     * @version 15.51.1
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
