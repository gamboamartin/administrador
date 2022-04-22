<?php
namespace validacion\confs;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JsonException;
use stdClass;

class configuraciones extends validacion {

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @throws JsonException
     */
    private function valida_conf(stdClass $paths_conf,string $tipo_conf): bool|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf,
                params: get_defined_vars());
        }

        $valida = $this->valida_conf_file(paths_conf:$paths_conf, tipo_conf:$tipo_conf);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                params: get_defined_vars());
        }
        $valida = $this->valida_conf_composer(tipo_conf: $tipo_conf);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                params: get_defined_vars());
        }
        return true;
    }
    /**
     * P ORDER P INT PROBADO ERRORREV
     * @throws JsonException
     */
    public function valida_confs(stdClass $paths_conf): bool|array
    {
        $tipo_confs[] = 'generales';
        $tipo_confs[] = 'database';
        $tipo_confs[] = 'views';

        foreach ($tipo_confs as $tipo_conf){
            $valida = $this->valida_conf(paths_conf: $paths_conf, tipo_conf: $tipo_conf);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                    params: get_defined_vars());
            }
        }
        return true;
    }


    /**
     * P ORDER P INT PROBADO ERROREV
     * @throws JsonException
     */
    private function valida_conf_composer(string $tipo_conf): bool|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf,
                params: get_defined_vars());
        }

        if(!class_exists("config\\$tipo_conf")){

            $data_composer['autoload']['psr-4']['config\\'] = "config/";
            $llave_composer = json_encode($data_composer, JSON_THROW_ON_ERROR);

            $mensaje = "Agrega el registro $llave_composer en composer.json despues ejecuta composer update";
            return $this->error->error(mensaje: $mensaje,data: '',
                params: get_defined_vars());
        }
        return true;
    }

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @param stdClass $paths_conf
     * @param string $tipo_conf
     * @return bool|array
     */
    private function valida_conf_file(stdClass $paths_conf, string $tipo_conf): bool|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return $this->error->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf,
                params: get_defined_vars());
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
            la ruta $path basado en la estructura del ejemplo $path_e",data: $data,
                params: get_defined_vars());
        }
        return true;
    }

}
