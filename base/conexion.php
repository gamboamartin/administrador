<?php
namespace base;
use chillerlan\QRCodeExamples\QRImageWithLogo;
use config\database;
use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JsonException;
use PDO;
use stdClass;
use Throwable;

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

        $valida = $this->valida_confs(paths_conf: $paths_conf);
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
            return (new errores())->error(mensaje: "Error al asignar codificacion en bd",data:$link,
                params: get_defined_vars());
        }

        $link = $this->asigna_sql_mode(link: $link, sql_mode: $sql_mode);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al asignar sql mode en bd",data:$link,
                params: get_defined_vars());
        }

        $link = $this->asigna_timeout(link:$link, time_out: $time_out);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al asignar sql mode en bd",data:$link,
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
            return (new errores())->error(mensaje:  'Error al validar conf_database',data: $valida,
                params: get_defined_vars());
        }
        try{
            $link = new PDO("mysql:host=$conf_database->db_host;dbname=$conf_database->db_name",
                $conf_database->db_user, $conf_database->db_password);
        }
        catch (Throwable $e) {
            return (new errores())->error(mensaje:  'Error al conectar',data: $e,params: get_defined_vars());
        }
        return $link;
    }

    private function genera_link(): PDO|array
    {
        $conf_database = new database();

        $link = $this->conecta(conf_database: $conf_database);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al conectar",data:$link, params: get_defined_vars());
        }

        $link = $this->asigna_parametros_query(link: $link, set_name: 'utf8', sql_mode: '',time_out: 10);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al asignar parametros", data:$link,
                params: get_defined_vars());
        }

        $link = $this->usa_base_datos(link: $link, db_name: $conf_database->db_name);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error usar base de datos", data:$link,
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

    /**
     * P ORDER P INT PROBADO
     * @throws JsonException
     */
    private function valida_conf(stdClass $paths_conf,string $tipo_conf): bool|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return (new errores())->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf,
                params: get_defined_vars());
        }

        $valida = $this->valida_conf_file(paths_conf:$paths_conf, tipo_conf:$tipo_conf);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                params: get_defined_vars());
        }
        $valida = $this->valida_conf_composer(tipo_conf: $tipo_conf);
        if(errores::$error){
            return (new errores())->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                params: get_defined_vars());
        }
        return true;
    }

    /**
     * P ORDER P INT PROBADO
     * @throws JsonException
     */
    private function valida_conf_composer(string $tipo_conf): bool|array
    {
        $tipo_conf = trim($tipo_conf);
        if($tipo_conf === ''){
            return (new errores())->error(mensaje: 'Error $tipo_conf esta vacio',data: $tipo_conf,
                params: get_defined_vars());
        }

        if(!class_exists("config\\$tipo_conf")){

            $data_composer['autoload']['psr-4']['config\\'] = "config/";
            $llave_composer = json_encode($data_composer, JSON_THROW_ON_ERROR);

            $mensaje = "Agrega el registro $llave_composer en composer.json despues ejecuta composer update";
            return (new errores())->error(mensaje: $mensaje,data: '',
                params: get_defined_vars());
        }
        return true;
    }



    /**
     * P ORDER P INT PROBADO
     * @throws JsonException
     */
    private function valida_confs(stdClass $paths_conf): bool|array
    {
        $tipo_confs[] = 'generales';
        $tipo_confs[] = 'database';

        foreach ($tipo_confs as $tipo_conf){
            $valida = $this->valida_conf(paths_conf: $paths_conf, tipo_conf: $tipo_conf);
            if(errores::$error){
                return (new errores())->error(mensaje: "Error al validar $tipo_conf.php",data:$valida,
                    params: get_defined_vars());
            }
        }
        return true;
    }

    /**
     * P ORDER P INT PROBADO
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