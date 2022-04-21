<?php
namespace base;
use config\database;
use gamboamartin\errores\errores;
use PDO;
class conexion{
	public static PDO $link;


    /**
     * P ORDER P INT
     */
    public function __construct(){
        $error = new errores();
        if(!class_exists(database::class)){
            $error = $error->error(mensaje: 'Error no existe clase config\\database',data: '',
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