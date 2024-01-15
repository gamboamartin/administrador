<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo_base;
use base\orm\sql;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class _instalacion
{
    private errores $error;
    private modelo_base $modelo;

    public function __construct(PDO $link)
    {
        $this->error = new errores();
        $this->modelo = new modelo_base(link: $link);

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Agrega una columna a una tabla dada.
     *
     * @param string $campo El nombre de la columna a agregar.
     * @param string $table El nombre de la tabla a la que se agregará la columna.
     * @param string $tipo_dato El tipo de dato de la nueva columna.
     * @param string $default Valor default en caso de vacio no lo integra
     * @param string $longitud Opcional. La longitud del nuevo campo. Por defecto es una cadena vacía.
     * @return stdClass|array Retorna la ejecución de la sentencia SQL para agregar la columna, o en caso de error,
     * devuelve el mensaje de error.
     * @version 13.28.0
     */
    final public function add_colum(string $campo, string $table, string $tipo_dato,
                                    string $default = '', string $longitud = ''): stdClass|array
    {
        $campo = trim($campo);
        $table = trim($table);
        $tipo_dato = trim($tipo_dato);
        $tipo_dato = strtoupper($tipo_dato);

        $longitud = trim($longitud);
        if($tipo_dato === 'VARCHAR'){
            $longitud = '255';
        }

        $valida = (new sql())->valida_column(campo:$campo,table:  $table, tipo_dato: $tipo_dato, longitud: $longitud);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data: $valida);
        }

        $sql = (new sql())->add_column(campo: $campo, table: $table, tipo_dato: $tipo_dato,
            default: $default, longitud: $longitud);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Agrega una columna a una tabla y luego establece una clave foránea en la misma.
     *
     * @param string $campo El nombre de la columna a agregar, que luego se convertirá en la clave foránea.
     * @param string $table El nombre de la tabla a la que se agregará la columna y se establecerá la clave foránea.
     * @return array|stdClass Devuelve el resultado del proceso de generar la clave foránea o, en caso de error, devuelve un mensaje de error.
     * @version 13.30.0
     */
    final public function foreign_key_completo(string $campo, string $table):array|stdClass
    {

        $exe = $this->add_colum(campo: $campo, table: $table,tipo_dato:  'bigint',longitud: 100);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar add_column', data: $exe);
        }

        $explode_campo = explode('_id', $campo);
        $relacion_table = $explode_campo[0];

        $exe = $this->foreign_key_existente(relacion_table: $relacion_table,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $exe);
        }

        return $exe;

    }
    /**
     * POR DOCUMENTAR EN WIKI
     * Genera una sentencia SQL para crear una clave foránea y luego la ejecuta.
     *
     * @param string $relacion_table El nombre de la tabla que la clave foránea está referenciando.
     * @param string $table El nombre de la tabla donde se creará la clave foránea.
     * @return array|stdClass Devuelve el resultado de la ejecución de la consulta SQL, o un error si ocurre uno.
     * @version 13.29.0
     */
    final public function foreign_key_existente(string $relacion_table, string $table):array|stdClass
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
        $relacion_table = trim($relacion_table);
        if($relacion_table === ''){
            return $this->error->error(mensaje: 'Error relacion_table esta vacia', data: $relacion_table);
        }

        $sql = (new sql())->foreign_key(table: $table,relacion_table:  $relacion_table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }



    final public function create_table(stdClass $campos, string $table):array|stdClass
    {
        $sql = (new sql())->create_table(campos: $campos,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }


        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    final public function drop_table(string $table):array|stdClass
    {
        $sql = (new sql())->drop_table(table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

}
