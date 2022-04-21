<?php
namespace base;
use config\database;
use config\generales;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
class conexion{
	public static PDO $link;


    /**
     * P ORDER P INT
     * @throws \JsonException
     */
    public function __construct(){
        $error = new errores();

        $valida = $this->valida_confs();
        if(errores::$error){
            $error_ = $error->error(mensaje: "Error al validar configuraciones",data:$valida, params: get_defined_vars());
            print_r($error_);
            exit;
        }

       
        $conf_database = new database();

        $link = new PDO("mysql:host=$conf_database->db_host;dbname=$conf_database->db_name",
            $conf_database->db_user, $conf_database->db_password);

        $link->query("SET NAMES 'utf8'");
        $sql = "SET sql_mode = '';";
        $link->query($sql);

        $sql = "SET innodb_lock_wait_timeout=100;";
        $link->query($sql);

        $consulta = "USE ".$conf_database->db_name;
        $link->query($consulta);

        self::$link = $link;

	}

    /**
     * @throws JsonException
     */
    private function valida_conf(string $tipo_conf): bool|array
    {
        $valida = $this->valida_conf_file($tipo_conf);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                params: get_defined_vars());
        }
        $valida = $this->valida_conf_composer($tipo_conf);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                params: get_defined_vars());
        }
        return true;
    }

    /**
     * @throws JsonException
     */
    private function valida_conf_composer(string $tipo_conf): bool|array
    {
        if(!class_exists("$tipo_conf\\class")){

            $data_composer['autoload']['psr-4']['config\\'] = "config/";
            $llave_composer = json_encode($data_composer, JSON_THROW_ON_ERROR);

            $mensaje = "Agrega el registro $llave_composer en composer.json despues ejecuta composer update";
            return (new errores())->error(mensaje: $mensaje,data: '',
                params: get_defined_vars());
        }
        return true;
    }

    private function valida_conf_file(string $tipo_conf): bool|array
    {
        $path = "config/$tipo_conf.php";
        if(!file_exists($path)){

            $path_e = "vendor/gamboa.martin/configuraciones/$path.example";
            $data = htmlentities(file_get_contents("././$path_e"));

            $data.="<br><br>$data><br><br>";

            return (new errores())->error(mensaje: "Error no existe el archivo $path favor de generar 
            la ruta $path basado en la estructura del ejemplo $path_e",data: $data,
                params: get_defined_vars());
        }
        return true;
    }

    /**
     * @throws JsonException
     */
    private function valida_confs(): bool|array
    {
        $tipo_confs[] = 'generales';
        $tipo_confs[] = 'database';

        foreach ($tipo_confs as $tipo_conf){
            $valida = $this->valida_conf($tipo_conf);
            if(errores::$error){
                return (new errores())->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                    params: get_defined_vars());
            }
        }
        return true;
    }








}