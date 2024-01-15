<?php
namespace base\orm;
use gamboamartin\errores\errores;
use stdClass;

class _create
{
    private errores  $error;

    public function _construct(): void
    {
        $this->error = new errores();
    }

    /**
     * Define los atributos de una nueva instancia basándose en los atributos proporcionados.
     * Si ocurre un error al obtener los atributos iniciales o al establecer los atributos base,
     * el método generará un error y retornará un mensaje de error.
     *
     * @param stdClass $atributos El objeto que contiene los atributos a ser establecidos.
     * @return stdClass|array Retorna una nueva instancia con los atributos establecidos o,
     *                       si ocurre un error, no retorna nada y genera un mensaje de error.
     */
    private function atributos(stdClass $atributos): array|stdClass
    {
        $atributos_base = $this->atributos_iniciales();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos iniciales',data: $atributos_base);
        }

        $atributos_base = $this->atributos_base(atributos: $atributos,atributos_base:  $atributos_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos_base',data: $atributos_base);
        }

        return $atributos_base;

    }

    /**
     * Establece los atributos base del objeto proporcionado, si están presentes en otro objeto de entrada.
     * Este método toma dos objetos stdClass como parámetros: $atributos, que puede contener los valores a establecer,
     * y $atributos_base, que es el objeto en el que se configuran los atributos.
     * Si los atributos tipo_dato, longitud y not_null están presentes en el objeto $atributos,
     * entonces se configuran sus valores correspondientes en el objeto $atributos_base y se retorna este último.
     *
     * @param stdClass $atributos El objeto que puede contener los atributos a establecer.
     * @param stdClass $atributos_base El objeto cuyos atributos se van a configurar.
     * @return stdClass Retorna el objeto con los atributos base configurados si están presentes en el objeto de entrada.
     */
    private function atributos_base(stdClass $atributos, stdClass $atributos_base): stdClass
    {
        if(isset($atributos->tipo_dato)){
            $atributos_base->tipo_dato = trim($atributos->tipo_dato);
        }
        if(isset($atributos->longitud)){
            $atributos_base->longitud = trim($atributos->longitud);
        }
        if(isset($atributos->not_null)){
            $atributos_base->not_null = trim($atributos->not_null);
        }

        return $atributos_base;

    }

    /**
     * POR DOCUMENTAR WIKI
     * Define los atributos iniciales para el nuevo objeto que será creado.
     * Esta función crea un objeto estándar en PHP y le asigna tres atributos: tipo_dato, longitud y not_null,
     * los cuales son configurados con las cadenas "VARCHAR", "255" y "NOT NULL" respectivamente.
     * Finalmente, la función retorna este objeto.
     * @return stdClass Retorna una nueva instancia de stdClass con los atributos iniciales configurados.
     * @version 13.21.0
     */
    private function atributos_iniciales(): stdClass
    {
        $data = new stdClass();
        $data->tipo_dato = "VARCHAR";
        $data->longitud = "255";
        $data->not_null = "NOT NULL";

        return $data;
    }


    /**
     * Genera los atributos SQL para el objeto a partir de los atributos base,
     * y asigna la longitud SQL, si está presente.
     * Si ocurre un error al obtener los atributos base o al obtener la longitud SQL,
     * el método generará un error y retornará un mensaje de error.
     *
     * @param stdClass $atributos El objeto que contiene los atributos base.
     * @return stdClass|array Retorna un objeto que contiene los atributos SQL y longitud SQL,
     *                       o, si ocurre un error, no retorna nada y genera un mensaje de error.
     */
    private function atributos_sql(stdClass $atributos): array|stdClass
    {
        $atributos_base = $this->atributos(atributos: $atributos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos_base',data: $atributos_base);
        }

        $longitud_sql = $this->longitud_sql(atributos_base: $atributos_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener longitud_sql',data: $longitud_sql);
        }

        $atributos_base->longitud_sql = $longitud_sql;

        return $atributos_base;

    }

    /**
     * Genera una sentencia SQL para un campo a partir de los atributos base proporcionados.
     * Si no se pueden obtener los atributos base, este método retorna un mensaje de error.
     *
     * @param stdClass $atributos El objeto que contiene los atributos base.
     * @param string $campo El nombre del campo para el cual se generará la sentencia SQL.
     * @return string|array Retorna una sentencia SQL para el campo especificado
     *                      o, si ocurre un error, retorna un array con el mensaje y los datos del error.
     */
    private function campo_sql(stdClass $atributos, string $campo):string|array
    {
        $atributos_base = $this->atributos_sql(atributos: $atributos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos_base',data: $atributos_base);
        }
        return "$campo $atributos_base->tipo_dato $atributos_base->longitud_sql $atributos_base->not_null, ";

    }

    /**
     * Genera las sentencias SQL para todos los campos proporcionados.
     * Si no se puede generar la sentencia SQL para un campo, este método retorna un mensaje de error.
     *
     * @param stdClass $campos El objeto que contiene los campos y sus atributos.
     * @return string|array Retorna una cadena conteniendo las sentencias SQL para todos los campos,
     *                      o, si ocurre un error, retorna un array con el mensaje y los datos del error.
     */
    private function crea_campos_sql(stdClass $campos):string|array
    {
        $campos_sql = '';
        foreach ($campos as $campo=>$atributos){

            $campo_sql = $this->campo_sql(atributos: $atributos, campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener campo_sql',data: $campo_sql);
            }
            $campos_sql.=$campo_sql;
        }

        return $campos_sql;

    }
    /**
     * Genera las sentencias SQL de clave foránea para todos los campos proporcionados.
     * Si no se puede generar la sentencia de clave foránea para un campo, este método retorna un mensaje de error.
     *
     * @param stdClass $campos El objeto que contiene los campos y sus atributos.
     * @return string|array Retorna una cadena conteniendo las sentencias SQL de clave foránea para todos los campos,
     *                      o, si ocurre un error, retorna un array con el mensaje y los datos del error.
     */
    private function crea_foreign_keys(stdClass $campos):string|array
    {
        $foreign_keys = '';
        foreach ($campos as $campo=>$atributos){

            $foreign_key = $this->genera_foreign_key(atributos: $atributos,campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener foreign_key',data: $foreign_key);
            }
            $foreign_keys.=$foreign_key;
        }
        return $foreign_keys;
    }

    /**
     * Genera las sentencias SQL de los campos y las claves foráneas para una tabla a partir de los atributos proporcionados.
     * Si no se pueden generar estas sentencias, este método retorna un mensaje de error.
     *
     * @param stdClass $campos El objeto que contiene los campos y sus atributos.
     * @return stdClass|array Retorna un objeto que contiene las sentencias SQL para los campos y las claves foráneas,
     *                        o, si ocurre un error, retorna un array con el mensaje y los datos del error.
     */
    final public function datos_tabla(stdClass $campos):array|stdClass
    {
        $campos_sql = $this->crea_campos_sql(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos_sql',data: $campos_sql);
        }

        $foreign_keys = $this->crea_foreign_keys(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener foreign_keys',data: $foreign_keys);
        }

        $data = new stdClass();
        $data->campos = $campos_sql;
        $data->foreigns = $foreign_keys;

        return $data;
    }



    /**
     * Crea una sentencia de clave foránea SQL.
     *
     * @param string $campo El campo que se utilizará como clave foránea.
     * @param string $references La tabla de referencia para la clave foránea.
     * @return string Retorna una sentencia de clave foránea en formato SQL.
     */
    private function foreign_key(string $campo, string $references): string
    {
        return  $foreign_key = "FOREIGN KEY ($campo) REFERENCES $references(id) ON UPDATE RESTRICT ON DELETE RESTRICT";
    }

    /**
     * Genera una sentencia clave foránea SQL para el campo especificado.
     * Esta sentencia incluirá una referencia a la tabla adecuada, basada en el nombre del campo.
     *
     * @param string $campo El campo que se utilizará como clave foránea.
     * @return string|array Retorna una sentencia de clave foránea en formato SQL,
     *                     o, si ocurre un error, no retorna nada y genera un mensaje de error.
     */
    private function foreign_key_sql(string $campo): array|string
    {
        $references = $this->references(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener references',data: $references);
        }

        $foreign_key = $this->foreign_key(campo: $campo,references:  $references);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener foreign_key',data: $foreign_key);
        }
        return $foreign_key;
    }

    /**
     * Genera una sentencia de clave foránea SQL, si la clave foránea está configurada en los atributos proporcionados.
     * Si no se puede generar la clave foránea, este método retorna un mensaje de error.
     *
     * @param stdClass $atributos El objeto que contiene los atributos en los que puede estar configurada la clave foránea.
     * @param string $campo El campo que se utilizará como clave foránea.
     * @return string|array Retorna una sentencia de clave foránea en formato SQL
     *                      o, si ocurre un error, retorna un array con el mensaje y los datos del error.
     */
    private function genera_foreign_key(stdClass $atributos, string $campo):string|array
    {
        $foreign_key = '';
        if(isset($atributos->foreign_key) && $atributos->foreign_key){

            $foreign_key = $this->foreign_key_sql(campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener foreign_key',data: $foreign_key);
            }
        }
        return $foreign_key;
    }

    /**
     * Genera una cadena representando la longitud SQL del atributo, si está presente.
     *
     * @param stdClass $atributos_base El objeto que contiene los atributos base.
     * @return string Retorna una cadena representando la longitud SQL,
     *                o una cadena vacía si la longitud no está presente en los atributos base.
     */
    private function longitud_sql(stdClass $atributos_base): string
    {
        $longitud_sql = '';
        if($atributos_base->longitud !== ''){
            $longitud_sql = "($atributos_base->longitud)";
        }
        return $longitud_sql;
    }

    /**
     * Procesa un campo que tiene la estructura de nombre seguido por "_id"
     * y devuelve el nombre del campo sin el sufijo "_id". Esto es útil para obtener
     * en qué tabla debe buscar una clave foránea basada en una convención de nombres.
     *
     * @param string $campo El campo de la base de datos que termina en "_id".
     * @return string El nombre del campo sin el sufijo "_id".
     */
    private function references(string $campo): string
    {
        $explode_ref = explode('_id', $campo);
        return reset($explode_ref);
    }

    /**
     * Genera una sentencia SQL para crear una tabla con los campos y claves foráneas especificados.
     *
     * @param stdClass $datos_tabla Objeto que contiene los datos de los campos y las claves foráneas.
     * @param string $table Nombre de la tabla que se creará.
     * @return string Sentencia de creación de tabla en SQL.
     */
    final public function table(stdClass $datos_tabla, string $table): string
    {
        $coma_key = '';
        if($datos_tabla->foreigns!==''){
            $coma_key = ', ';
        }

        return "CREATE TABLE $table (
                    id bigint NOT NULL AUTO_INCREMENT,
                    $datos_tabla->campos
                    PRIMARY KEY (id) $coma_key
                   $datos_tabla->foreigns
                    );";


    }


}
