<?php
namespace gamboamartin\administrador\models;

use base\orm\estructuras;
use base\orm\modelo_base;
use base\orm\sql;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class _instalacion
{
    private errores $error;
    private modelo_base $modelo;
    private PDO $link;

    public function __construct(PDO $link)
    {
        $this->link = $link;
        $this->error = new errores();
        $this->modelo = new modelo_base(link: $this->link);


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
        if ($tipo_dato === 'VARCHAR') {
            $longitud = '255';
        }

        $valida = (new sql())->valida_column(campo: $campo, table: $table, tipo_dato: $tipo_dato, longitud: $longitud);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $sql = (new sql())->add_column(campo: $campo, table: $table, tipo_dato: $tipo_dato,
            default: $default, longitud: $longitud);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    final public function add_columns(stdClass $campos, string $table)
    {

        $datos = $this->describe_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $datos);
        }
        $campos_origen = $datos->registros;

        $adds = array();
        foreach ($campos as $campo=>$atributos){

            $existe_campo = $this->existe_campo_origen(campo_integrar: $campo,campos_origen:  $campos_origen);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar si existe campo', data: $existe_campo);
            }

            if(!$existe_campo){
                $tipo_dato = 'VARCHAR';
                if(isset($atributos->tipo_dato)){
                    $tipo_dato = $atributos->tipo_dato;
                }
                $default = '';
                if(isset($atributos->default)){
                    $default = $atributos->default;
                }
                $longitud = '255';
                if(isset($atributos->longitud)){
                    $longitud = $atributos->longitud;
                }
                $add = $this->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
                    longitud: $longitud);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al agregar columna sql', data: $add);
                }
                $adds[] = $add;
            }

        }

        return $adds;
    }

    final public function create_table(stdClass $campos, string $table): array|stdClass
    {
        $sql = (new sql())->create_table(campos: $campos, table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }


        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    /**
     * Realiza una consulta para describir la estructura de una tabla.
     *
     * @param string $table El nombre de la tabla a describir.
     * @return stdClass|array Retorna el resultado de la consulta de descripción de la tabla o, en caso de error, devuelve un mensaje de error.
     */
    final public function describe_table(string $table): array|stdClass
    {
        $sql = (new sql())->describe_table(tabla: $table);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al generar sql", data: $sql);
        }
        $exe = $this->modelo->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;
    }

    final public function drop_table(string $table): array|stdClass
    {
        $sql = (new sql())->drop_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    /**
     * Verifica si un campo específico existe en un conjunto de campos dado.
     *
     * @param string $campo_integrar El nombre del campo a buscar.
     * @param array $campos_origen Un array de campos en los que buscar el campo.
     * @return bool Retorna true si el campo existe en el conjunto, false en caso contrario.
     */
    private function existe_campo_origen(string $campo_integrar, array $campos_origen): bool
    {
        $existe_campo = false;
        foreach ($campos_origen as $datos_campos){
            $campo_original = trim($datos_campos['Field']);
            if($campo_original === $campo_integrar){
                $existe_campo = true;
                break;
            }
        }
        return $existe_campo;
    }

    final public function foraneas(array $foraneas, string $table)
    {
        $results = array();
        foreach ($foraneas as $campo){
            $result = $this->foreign_key_seguro(campo: $campo,table: $table);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar foranea', data:  $result);
            }
            $results[] = $result;
        }
        return $results;

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
    final public function foreign_key_completo(string $campo, string $table): array|stdClass
    {

        $exe = $this->add_colum(campo: $campo, table: $table, tipo_dato: 'bigint', longitud: 100);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar add_column', data: $exe);
        }

        $fk = $this->foreign_por_campo(campo: $campo, table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $fk);
        }

        return $fk;

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
    final public function foreign_key_existente(string $relacion_table, string $table): array|stdClass
    {
        $table = trim($table);
        if ($table === '') {
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
        $relacion_table = trim($relacion_table);
        if ($relacion_table === '') {
            return $this->error->error(mensaje: 'Error relacion_table esta vacia', data: $relacion_table);
        }

        $sql = (new sql())->foreign_key(table: $table, relacion_table: $relacion_table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }


    final public function foreign_key_seguro(string $campo, string $table)
    {
        $existe_table = (new estructuras(link: $this->link))->existe_entidad(entidad: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
        }
        if(!$existe_table){
            return $this->error->error(mensaje: 'Error no existe la entidad',data:  $table);
        }

        $datos = $this->describe_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $datos);
        }
        $campos_origen = $datos->registros;

        $existe_campo = $this->existe_campo_origen(campo_integrar: $campo,campos_origen:  $campos_origen);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe campo', data: $existe_campo);
        }

        if(!$existe_campo){
            $fk = $this->foreign_key_completo(campo: $campo,table:  $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
            }
        }
        else{
            $fk = $this->foreign_no_conf_integra(campo: $campo, campos_origen: $campos_origen, table: $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign no conf', data: $fk);
            }
        }


        return $fk;

    }

    private function foreign_no_conf(string $campo, array $campo_origen, string $table)
    {
        $fk = 'Campo asignado '.$campo;
        if($campo_origen['Key'] !== 'MUL'){
            $fk = $this->foreign_por_campo(campo: $campo,table:  $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
            }
        }
        return $fk;

    }

    private function foreign_no_conf_integra(string $campo, array $campos_origen, string $table)
    {
        $fk = 'No existe campo '.$campo;
        foreach ($campos_origen as $campo_origen){

            $campo_origen_name = $campo_origen['Field'];

            if($campo_origen_name === $campo) {
                $fk = $this->foreign_no_conf(campo: $campo, campo_origen: $campo_origen, table: $table);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
                }
                break;
            }
        }
        return $fk;

    }

    private function foreign_por_campo(string $campo, string $table)
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        $valida = (new validacion())->key_id(txt: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campo', data: $valida);
        }

        $explode_campo = explode('_id', $campo);
        $relacion_table = $explode_campo[0];

        $fk = $this->foreign_key_existente(relacion_table: $relacion_table, table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $fk);
        }

        return $fk;

    }



    /**
     * Integra una clave foránea en una tabla si el campo correspondiente no existe ya en la tabla.
     *
     * @param string $campo_integrar El nombre del campo a integrar como clave foránea.
     * @param array $campos_origen Un array con los campos originales de la tabla.
     * @param array $integraciones Un array con las integraciones ya realizadas.
     * @param string $table El nombre de la tabla donde se integrará la clave foránea.
     * @return array Retorna un array con las integraciones actualizadas después de la integración del nuevo campo.
     */
    private function integra_fk(string $campo_integrar, array $campos_origen, array $integraciones, string $table): array
    {
        $existe_campo = $this->existe_campo_origen(campo_integrar: $campo_integrar,campos_origen:  $campos_origen);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe campo', data: $existe_campo);
        }

        if(!$existe_campo){
            $integra_fk = $this->foreign_key_completo(campo: $campo_integrar,table:  $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ejecutar sql', data: $integra_fk);
            }
            $integraciones[] = $integra_fk;
        }

        return $integraciones;
    }

    private function integra_fks(stdClass $campos, array $campos_origen, string $table)
    {
        $integraciones = array();
        foreach ($campos as $campo_integrar=>$estructura){

            if(isset($estructura->foreign_key) && $estructura->foreign_key){
                $integraciones = $this->integra_fk(campo_integrar: $campo_integrar,campos_origen:  $campos_origen,
                    integraciones:  $integraciones,table:  $table);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar campo', data: $integraciones);
                }
            }

        }
        return $integraciones;
    }

    final public function integra_foraneas(stdClass $campos, string $table)
    {
        $datos = $this->describe_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $datos);
        }
        $campos_origen = $datos->registros;

        $integraciones = $this->integra_fks(campos: $campos,campos_origen:  $campos_origen,table:  $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar campo', data: $integraciones);
        }

        return $integraciones;

    }

}
