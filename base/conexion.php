<?php
namespace base;
use config\database;
use config\generales;
use gamboamartin\errores\errores;
use PDO;
class conexion{
	public static PDO $link;


    /**
     * P ORDER P INT
     * @throws \JsonException
     */
    public function __construct(){
        $error = new errores();

        $path_gc = 'config/generales.php';
        if(!file_exists($path_gc)){

            $path_gce = "vendor/gamboa.martin/configuraciones/$path_gc.example";
            $data = htmlentities(file_get_contents("././$path_gce"));

            $data.="<br><br>$data><br><br>";

            $error = $error->error(mensaje: "Error no existe el archivo $path_gc favor de generar 
            la ruta $path_gc basado en la estructura del ejemplo $path_gce",data: $data,
                params: get_defined_vars());
            print_r($error);
            exit;
        }

        if(!class_exists(generales::class)){

            $data_composer['autoload']['psr-4']['config\\'] = "config/";
            $llave_composer = json_encode($data_composer, JSON_THROW_ON_ERROR);

            $mensaje = "Agrega el registro $llave_composer en composer.json";
            $error_ = $error->error(mensaje: $mensaje,data: '',
                params: get_defined_vars());
            print_r($error_);
            exit;
        }

        if(!class_exists(database::class)){
            $data = file_get_contents((new generales())->path_base.'config/database.php.example');
            $error = $error->error(mensaje: 'Error no existe clase config\\database',data: $data,
                params: get_defined_vars());
            print_r($error);
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
}