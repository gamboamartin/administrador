<?php
namespace base;
use config\database;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JsonException;
use PDO;
use stdClass;
use Throwable;
use validacion\confs\configuraciones;

class conexion{
	public static PDO $link;
    private errores $error;


    /**
     * P ORDER P INT
     * @throws JsonException
     */
    public function __construct(stdClass $paths_conf = new stdClass()){
        $error = new errores();
        $this->error = new errores();

        $valida = (new configuraciones())->valida_confs(paths_conf: $paths_conf);
        if(errores::$error){
            $error_ = $error->error(mensaje: "Error al validar configuraciones",data:$valida, params: get_defined_vars());
            print_r($error_);
            exit;
        }

        $link = $this->genera_link();
        if(errores::$error){
            $error_ = $error->error(mensaje: "Error al generar link",data: $link, params: get_defined_vars());
            print_r($error_);
            exit;
        }

        self::$link = $link;

	}

    private function asigna_set_names(PDO $link, string $set_name): PDO
    {
        $link->query("SET NAMES '$set_name'");
        return $link;
    }

    private function asigna_sql_mode(PDO $link, string $sql_mode): PDO
    {
        $sql = "SET sql_mode = '$sql_mode';";
        $link->query($sql);
        return $link;
    }

    private function asigna_timeout(PDO $link, int $time_out): PDO
    {
        $sql = "SET innodb_lock_wait_timeout=$time_out;";
        $link->query($sql);
        return $link;
    }

    private function asigna_parametros_query(PDO $link, string $set_name, string $sql_mode, int $time_out): PDO|array
    {
        $link = $this->asigna_set_names(link: $link, set_name: $set_name);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar codificacion en bd",data:$link,
                params: get_defined_vars());
        }

        $link = $this->asigna_sql_mode(link: $link, sql_mode: $sql_mode);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar sql mode en bd",data:$link,
                params: get_defined_vars());
        }

        $link = $this->asigna_timeout(link:$link, time_out: $time_out);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar sql mode en bd",data:$link,
                params: get_defined_vars());
        }

        return $link;
    }

    /**
     * P INT P ORDER PROBADO
     * @param database $conf_database
     * @return PDO|array
     */
    private function conecta(database $conf_database): PDO|array
    {
        $keys = array('db_host','db_name','db_user','db_password');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje:  'Error al validar conf_database',data: $valida,
                params: get_defined_vars());
        }
        try{
            $link = new PDO("mysql:host=$conf_database->db_host;dbname=$conf_database->db_name",
                $conf_database->db_user, $conf_database->db_password);
        }
        catch (Throwable $e) {
            return $this->error->error(mensaje:  'Error al conectar',data: $e,params: get_defined_vars());
        }
        return $link;
    }

    private function genera_link(): PDO|array
    {
        $conf_database = new database();

        $link = $this->conecta(conf_database: $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al conectar",data:$link, params: get_defined_vars());
        }

        $link = $this->asigna_parametros_query(link: $link, set_name: 'utf8', sql_mode: '',time_out: 10);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar parametros", data:$link,
                params: get_defined_vars());
        }

        $link = $this->usa_base_datos(link: $link, db_name: $conf_database->db_name);
        if(errores::$error){
            return $this->error->error(mensaje: "Error usar base de datos", data:$link,
                params: get_defined_vars());
        }

        return $link;
    }

    private function usa_base_datos(PDO $link, string $db_name): PDO
    {
        $consulta = "USE ".$db_name;
        $link->query($consulta);

        return $link;
    }


}