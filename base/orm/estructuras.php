<?php
namespace base\orm;
use config\database;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class estructuras{
    private errores  $error;
    public stdClass $estructura_bd;
    private PDO $link;

    private validacion $validacion;
    public function __construct(PDO $link){
        $this->error = new errores();
        $this->estructura_bd = new stdClass();
        $this->link = $link;
        $this->validacion = new validacion();
    }

    /**
     * @param array $campo
     * @param array $keys_no_foraneas
     * @param string $name_modelo
     * @return array|stdClass
     */
    private function asigna_dato_estructura(array $campo, array $keys_no_foraneas, string $name_modelo): array|stdClass
    {
        $init = $this->init_estructura_campo(campo: $campo,name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $init);
        }

        $campo_init = $this->inicializa_campo(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo', data: $campo_init);
        }

        $estructura_bd = $this->maqueta_estructura(campo: $campo,campo_init: $campo_init,name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
        }
        return $estructura_bd;
    }


    /**
     * @param string $name_db Nombre de la base de datos
     * @return array|stdClass
     */
    final public function asigna_datos_estructura(string $name_db): array|stdClass
    {
        $modelos = $this->modelos(name_db: $name_db);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener modelos', data: $modelos);
        }
        $keys_no_foraneas = array('usuario_alta','usuario_update');
        $estructura_bd = $this->genera_estructura(keys_no_foraneas: $keys_no_foraneas, modelos:$modelos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
        }

        $estructura_bd = $this->asigna_foraneas(estructura_bd: $estructura_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
        }


        $this->estructura_bd = $estructura_bd;

        return $estructura_bd;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Función que asigna un modelo de datos a partir de un array dado.
     *
     * @param array $modelos Array de modelos existentes.
     * @param string $name_db Nombre de la base de datos.
     * @param array $row Array de datos a asignar.
     *
     * @return array Devuelve un array de modelos añadiendo el nuevo modelo de datos.
     *
     * @throws errores Si el nombre de base de datos está vacío, si ocurre un error al generar la clave, si la clave
     * no existe en $row, o si $row[$key] está vacío.
     */
    private function asigna_data_modelo(array $modelos, string $name_db, array $row): array
    {
        $name_db = trim($name_db);
        if($name_db === ''){
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $key = $this->key_table(name_db: $name_db);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar key', data: $key);
        }

        if(!isset($row[$key])){
            return $this->error->error(mensaje: 'Error no existe $row[$key] ', data: $key);
        }
        if(trim($row[$key]) === ''){
            return $this->error->error(mensaje: 'Error esta vacio $row[$key] ', data: $key);
        }

        $data = $row[$key];
        $modelos[] = $data;
        return $modelos;
    }

    /**
     * @param stdClass $data
     * @param stdClass $estructura_bd
     * @param stdClass $foraneas
     * @param string $modelo
     * @return stdClass
     */
    private function asigna_dato_foranea(stdClass $data, stdClass $estructura_bd, stdClass $foraneas,
                                         string $modelo): stdClass
    {
        $tabla_foranea = $data->tabla_foranea;
        $foraneas->$tabla_foranea = new stdClass();
        $estructura_bd->$modelo->tiene_foraneas = true;
        return $estructura_bd;
    }

    /**
     * @param array $data_table
     * @param array $keys_no_foraneas
     * @param string $name_modelo
     * @return array|stdClass
     */
    private function asigna_datos_modelo(array $data_table, array $keys_no_foraneas, string $name_modelo): array|stdClass
    {
        $estructura_bd = array();
        foreach ($data_table as $campo){

            $estructura_bd = $this->asigna_dato_estructura(campo: $campo, keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
            }

        }
        return $estructura_bd;
    }

    /**
     * @param stdClass $estructura_bd
     * @return array|stdClass
     */
    private function asigna_foraneas(stdClass $estructura_bd): array|stdClass
    {
        $estructura_bd_r = $estructura_bd;
        foreach ($estructura_bd as $modelo=>$estructura){
            $estructura_bd_r = $this->calcula_foranea(estructura: $estructura,estructura_bd: $estructura_bd_r,modelo: $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
            }
        }
        return $estructura_bd_r;
    }

    /**
     * @param stdClass $estructura
     * @param stdClass $estructura_bd
     * @param string $modelo
     * @return array|stdClass
     */
    private function calcula_foranea(stdClass $estructura, stdClass $estructura_bd, string $modelo): array|stdClass
    {
        $estructura_bd_r = $estructura_bd;

        $estructura_bd_r->$modelo->tiene_foraneas = false;
        $data_campos = $estructura->data_campos;
        $foraneas = new stdClass();

        $estructura_bd_r = $this->genera_foranea(data_campos: $data_campos,estructura_bd: $estructura_bd_r,
            foraneas: $foraneas,modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd_r);
        }

        $estructura_bd_r->$modelo->foraneas = $foraneas;
        return $estructura_bd_r;
    }

    /**
     * @param string $name_db
     * @return array
     */
    final public function entidades(string $name_db): array
    {

        if(!isset($_SESSION['entidades_bd'])){
            $data = $this->asigna_datos_estructura(name_db: $name_db);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener estructura', data: $data);
            }
            $entidades = array();
            foreach ($data as $entidad=>$data_ent){
                $entidades[] = $entidad;
            }
            $_SESSION['entidades_bd'] = $entidades;
        }

        return $_SESSION['entidades_bd'];
    }


    /**
     * Integra si el campo es autoincrement o no
     * @param array $campo Campo a validar
     * @return bool|array
     * @version 11.1.0
     */
    private function es_auto_increment(array $campo): bool|array
    {
        if(!isset($campo['Extra'])){
            $campo['Extra'] = '';
        }
        $es_auto_increment = false;
        if($campo['Extra'] === 'auto_increment'){
            $es_auto_increment = true;
        }
        return $es_auto_increment;
    }

    /**
     * Verifica si el campo es una llave foranea o no
     * @param array $campo Campo a validar
     * @param array $keys_no_foraneas Keys previos con foraneas
     * @return bool|array
     * @version 11.23.0
     */
    private function es_foranea(array $campo, array $keys_no_foraneas): bool|array
    {
        if(!isset($campo['Field'])){
            return $this->error->error(mensaje: 'Error al campo[Field] no existe', data: $campo);
        }
        $es_foranea = false;
        $explode_campo = explode('_id', $campo['Field']);

        if((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if(!$es_no_foranea){
                $es_foranea = true;
            }

        }
        return $es_foranea;
    }

    /**
     * Asigna verdadero si el campo es una llave primaria
     * @param array $campo Campo a validar
     * @return bool|array
     * @version 10.97.4
     */
    private function es_primaria(array $campo): bool|array
    {
        if(!isset($campo['Key'])){
            return $this->error->error(mensaje: 'Error campo[Key] debe existir', data: $campo);
        }
        $es_primaria = false;
        if($campo['Key'] === 'PRI'){
            $es_primaria = true;
        }
        return $es_primaria;
    }

    /**
     * TOTAL
     * Comprueba si existe una entidad en la base de datos
     *
     * Este método realiza una consulta SQL para verificar si existe una entidad con el nombre proporcionado
     * en la base de datos. Si la entidad existe, el método devuelve `true`, de lo contrario, devuelve `false`.
     *
     * @param string $entidad El nombre de la entidad que se va a comprobar.
     * @return bool|array Devuelve `true` si la entidad existe, `false` si no existe.
     *
     * @example
     * $estructuras = new estructuras();
     * $existe = $estructuras->existe_entidad('nombre_entidad');
     * if ($existe) {
     *     echo 'La entidad existe.';
     * } else {
     *     echo 'La entidad no existe.';
     * }
     *
     * @version 15.4.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.estructuras.existe_entidad
     *
     */
    final public function existe_entidad(string $entidad): bool|array
    {
        $entidad = trim($entidad);
        if($entidad === ''){
            return $this->error->error(mensaje: 'Error entidad vacia', data: $entidad, es_final: true);
        }
        $sql = (new sql())->show_tables(entidad: $entidad);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }

        $result = (new modelo_base($this->link))->ejecuta_consulta(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }
        $existe_entidad = false;
        if($result->n_registros > 0){
            $existe_entidad = true;
        }

        return $existe_entidad;
    }

    /**
     * @param array $keys_no_foraneas
     * @param array $modelos
     * @return array|stdClass
     */
    private function genera_estructura(array $keys_no_foraneas, array $modelos): array|stdClass
    {
        $estructura_bd = array();
        $modelo_base = new modelo_base($this->link);
        foreach ($modelos as $name_modelo){

            $data_table = $this->init_dato_estructura(modelo_base: $modelo_base,name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $data_table);
            }
            $estructura_bd = $this->asigna_datos_modelo(data_table: $data_table, keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
            }
        }
        return $estructura_bd;
    }

    /**
     * @param stdClass $data_campos
     * @param stdClass $estructura_bd
     * @param stdClass $foraneas
     * @param string $modelo
     * @return array|stdClass
     */
    private function genera_foranea(stdClass $data_campos, stdClass $estructura_bd, stdClass $foraneas,
                                    string $modelo): array|stdClass
    {
        foreach ($data_campos as $data){
            if($data->es_foranea){
                $estructura_bd = $this->asigna_dato_foranea(data: $data,estructura_bd: $estructura_bd,
                    foraneas: $foraneas,modelo: $modelo);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
                }
            }
        }
        return $estructura_bd;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Método privado get_tables_sql.
     *
     * Este método se encarga de obtener una lista de todas las tablas en la base de datos.
     * Utiliza la clase "sql" para generar la consulta SQL "SHOW TABLES" y la clase "modelo_base"
     * para ejecutar la consulta.
     *
     * En caso de error, ya sea en la generación de la consulta SQL o en la ejecución de la misma,
     * el método devuelve un objeto de error con los detalles. Además, si no se obtienen tablas,
     * también retorna un objeto de error.
     *
     * @return array Devuelve un array con el resultado de la consulta.
     *               Cada elemento del array corresponde a una fila del resultado,
     *               con los nombres de las columnas como claves y los datos correspondientes como valores.
     *               Si se produce un error, se devuelve un objeto de error en lugar de un array con los resultados.
     * @version 15.25.0
     */
    private function get_tables_sql(): array
    {
        $sql = (new sql())->show_tables();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }

        $result = (new modelo_base($this->link))->ejecuta_consulta(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }
        if($result->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existen entidades en la bd '.(new database())->db_name,
                data: $sql, es_final: true);
        }

        return $result->registros;
    }

    /**
     * Inicializa un campo con loa datos de estructura de bd
     * @param array $campo campo a inicializar
     * @param array $keys_no_foraneas Keys integrados como no foraneas
     * @return array|stdClass
     */
    private function inicializa_campo(array $campo, array $keys_no_foraneas): array|stdClass
    {
        $permite_null = $this->permite_null(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar permite null', data: $permite_null);
        }
        $es_primaria = $this->es_primaria(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_primaria', data: $es_primaria);
        }
        $es_auto_increment = $this->es_auto_increment(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_auto_increment', data: $es_auto_increment);
        }
        $es_foranea = $this->es_foranea(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_foranea', data: $es_foranea);
        }
        $tabla_foranea = $this->tabla_foranea(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $tabla_foranea', data: $tabla_foranea);
        }

        $data = new stdClass();
        $data->permite_null = $permite_null;
        $data->es_primaria = $es_primaria;
        $data->es_auto_increment = $es_auto_increment;
        $data->es_foranea = $es_foranea;
        $data->tabla_foranea = $tabla_foranea;

        return $data;
    }

    /**
     * Inicializa los datos de una estructura de un modelo
     * @param modelo_base $modelo_base Modelo en ejecucion
     * @param string $name_modelo Nombre del modelo a obtener info
     * @return array
     */
    private function init_dato_estructura(modelo_base $modelo_base, string $name_modelo): array
    {
        $name_modelo = trim($name_modelo);
        if($name_modelo === ''){
            return $this->error->error(mensaje: 'Error name_modelo esta vacio',data:  $name_modelo);
        }
        if(is_numeric($name_modelo)){
            return $this->error->error(mensaje: 'Error name_modelo no puede ser un numero',data:  $name_modelo);
        }

        $data_table = (new columnas())->columnas_bd_native(modelo: $modelo_base, tabla_bd: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos', data: $data_table);
        }

        $init = $this->init_estructura_modelo(name_modelo:$name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $init);
        }

        return $data_table;
    }


    /**
     * Inicializa una estructura
     * @param array $campo Campo a inicializar
     * @param string $name_modelo Nombre del modelo entidad
     * @return stdClass|array
     * @version 9.127.5
     */
    private function init_estructura_campo(array $campo, string $name_modelo): stdClass|array
    {
        $name_modelo = trim($name_modelo);
        if($name_modelo === ''){
            return $this->error->error(mensaje: 'Error $name_modelo esta vacio', data: $name_modelo);
        }
        $keys = array('Field');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar valida', data: $valida);
        }
        if(!isset($this->estructura_bd->$name_modelo)){
            $this->estructura_bd->$name_modelo = new stdClass();
        }
        if(!isset($this->estructura_bd->$name_modelo->data_campos)){
            $this->estructura_bd->$name_modelo->data_campos = new stdClass();
        }

        $campo_name = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name = new stdClass();
        return $this->estructura_bd;
    }

    /**
     * Inicializa la estructura de un modelo
     * @param string $name_modelo Nombre de la entidad
     * @return stdClass|array
     * @version 9.98.4
     */
    private function init_estructura_modelo(string $name_modelo): stdClass|array
    {
        $name_modelo = trim($name_modelo);
        if($name_modelo === ''){
            return $this->error->error(mensaje: 'Error $name_modelo esta vacio', data: $name_modelo);
        }
        $this->estructura_bd->$name_modelo = new stdClass();
        $this->estructura_bd->$name_modelo->campos = array();
        $this->estructura_bd->$name_modelo->data_campos = new stdClass();
        return $this->estructura_bd;
    }

    /**
     * @param array $campo
     * @param stdClass $campo_init
     * @param string $name_modelo
     * @return stdClass
     */
    private function maqueta_estructura(array $campo, stdClass $campo_init, string $name_modelo): stdClass
    {
        $campo_name = $campo['Field'];

        $this->estructura_bd->$name_modelo->campos[] = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tabla_foranea =  $campo_init->tabla_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_foranea = $campo_init->es_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->permite_null = $campo_init->permite_null;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->campo_name = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tipo_dato = $campo['Type'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_primaria = $campo_init->es_primaria;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->valor_default = $campo['Default'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->extra = $campo['Extra'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_auto_increment = $campo_init->es_auto_increment;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tipo_llave = $campo['Key'];
        return $this->estructura_bd;
    }

    /**
     * Funcion que obtiene todas las tablas de una base de datos del sistema en ejecucion
     * @author mgamboa
     * @fecha 2022-07-25 17:26
     * @param string $name_db Nombre de la base de datos
     * @return array|stdClass
     */
    final public function modelos(string $name_db): array|stdClass
    {
        $name_db = trim($name_db);
        if($name_db === ''){
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $rows = $this->get_tables_sql();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $rows);
        }

        $modelos = $this->maqueta_modelos(name_db: $name_db, rows: $rows);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar modelos', data: $modelos);
        }

        return $modelos;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función genera la clave de la tabla a partir del nombre de la base de datos.
     *
     * @param string $name_db Nombre de la base de datos.
     * @return string|array Retorna la clave de la tabla.
     * @version 15.49.1
     */
    private function key_table(string $name_db): string|array
    {
        $name_db = trim($name_db);
        if($name_db === ''){
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $pref = 'Tables_in_';
        return $pref.$name_db;
    }

    /**
     * Funcion que retorna todas las tablas de una base de datos
     * @param string $name_db Nombre de la base de datos
     * @param array $rows Conjunto de tablas de show tables
     * @author mgamboa
     * @fecha 2022-07-25 17:25
     * @return array
     */
    private function maqueta_modelos(string $name_db, array $rows): array
    {
        $name_db = trim($name_db);
        if($name_db === ''){
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $modelos = array();
        foreach ($rows as $row){
            $modelos = $this->asigna_data_modelo(modelos:$modelos, name_db: $name_db,row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar modelo', data: $modelos);
            }
        }
        return $modelos;
    }

    /**
     * Integra permite null
     * @param array $campo Datos del campo
     * @return bool|array
     * @version 10.78.3
     */
    private function permite_null(array $campo): bool|array
    {
        if(!isset($campo['Null'])){
            return $this->error->error(mensaje: 'Error campo[Null] debe existir', data: $campo);
        }

        $permite_null = true;
        if($campo['Null'] === 'NO'){
            $permite_null = false;
        }
        return $permite_null;
    }

    /**
     * Integra el nombre de la tabla foranea ligada a la entidad
     * @param array $campo Datos del campo
     * @param array $keys_no_foraneas Key de foraneas
     * @return string|array
     * @version 11.25.0
     */
    private function tabla_foranea(array $campo, array $keys_no_foraneas): string|array
    {
        if(!isset($campo['Field'])){
            return $this->error->error(mensaje: 'Error al campo[Field] no existe', data: $campo);
        }

        $tabla_foranea = '';
        $explode_campo = explode('_id', $campo['Field']);
        if((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if(!$es_no_foranea){
                $tabla_foranea = $explode_campo[0];
            }

        }
        return $tabla_foranea;
    }

}
