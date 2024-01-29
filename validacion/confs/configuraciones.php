<?php
namespace validacion\confs;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;
use Throwable;

class configuraciones extends validacion {


    /**
     * Valida configuraciones
     * @param stdClass $paths_conf Paths de configuracion
     * @param string $tipo_conf Tipos de configuracion
     * @return bool|array
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
     * Valida las configuraciones para ejecutar el sistema
     * @param stdClass $paths_conf Archivos de configuracion
     * @return bool|array
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
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf);
        }

        if(!class_exists("config\\$tipo_conf")){

            $data_composer['autoload']['psr-4']['config\\'] = "config/";
            try {
                $llave_composer = json_encode($data_composer, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: $mensaje,data: $e);
            }

            $mensaje = "Agrega el registro $llave_composer en composer.json despues ejecuta composer update";
            return $this->error->error(mensaje: $mensaje,data: '');
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf);
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
            la ruta $path basado en la estructura del ejemplo $path_e",data: $data);
        }
        return true;
    }

}
