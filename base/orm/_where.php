<?php

namespace base\orm;

use gamboamartin\administrador\modelado\params_sql;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;

class _where
{
    private errores $error;
    private validaciones $validacion;

    public function __construct()
    {
        $this->error = new errores();
        $this->validacion = new validaciones();

    }

    /**
     * TOTAL
     * Metodo genera_where_seguridad
     *
     * Este método se encarga de generar la parte WHERE de una consulta SQL, dependiendo de ciertas condiciones de seguridad.
     *
     * @access private
     * @param string $where El WHERE inicial de la consulta
     * @return array|string El WHERE final de la consulta luego de aplicar las condiciones de seguridad
     *
     * @uses params_sql::seguridad() para generar las condiciones de seguridad
     * @uses modelo::where_seguridad() para formar la parte WHERE de la consulta con las condiciones de seguridad
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm._where.genera_where_seguridad
     */
    private function genera_where_seguridad(modelo $modelo, string $where): array|string
    {
        $seguridad = (new params_sql())->seguridad(aplica_seguridad:$modelo->aplica_seguridad,
            modelo_columnas_extra: $modelo->columnas_extra,
            sql_where_previo:  $where);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql de seguridad', data: $seguridad);
        }

        $where = $this->where_seguridad(modelo: $modelo, seguridad: $seguridad, where: $where);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        return $where;

    }


    /**
     * TOTAL
     * Esta función integra la cláusula WHERE segura a la consulta SQL proporcionada.
     *
     * @param string $consulta Consulta SQL a la que se le debe agregar la cláusula WHERE.
     * @param string $where Condición WHERE a ser agregadada a la consulta.
     * @return string|array Consulta SQL con la cláusula WHERE segura agregada.
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm._where.integra_where_seguridad
     *
     */
    private function integra_where_seguridad(string $consulta, modelo $modelo, string $where): string|array
    {
        $where = $this->genera_where_seguridad(modelo: $modelo, where: $where);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        $consulta .= $where;

        return $consulta;

    }

    /**
     * TOTAL
     * Genera y valida una cláusula WHERE SQL para una consulta, asegurando que el registro ID sea válido
     * y aplicando condiciones de seguridad adicionales.
     *
     * @param string $consulta La consulta SQL base a la cual se le añadirá la cláusula WHERE.
     * @param modelo $modelo Instancia del modelo que contiene la información necesaria para generar
     *                       la cláusula WHERE, incluyendo el ID del registro, la llave primaria y el nombre de la tabla.
     *
     * @return string|array Devuelve la consulta SQL con la cláusula WHERE integrada si no hay errores.
     *                      En caso de error, devuelve un array con detalles del error.
     *
     * @throws \errores::error() Si el ID del registro es menor o igual a 0, devuelve un error
     *                           indicando que el registro ID debe ser mayor a 0.
     * @throws \errores::error() Si ocurre un error al generar la cláusula WHERE inicial,
     *                           devuelve un error con detalles del problema.
     * @throws \errores::error() Si ocurre un error al integrar la cláusula WHERE en la consulta,
     *                           devuelve un error con detalles del problema.
     *
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm._where.sql_where
     */
    final public function sql_where(string $consulta, modelo $modelo):array|string
    {
        if($modelo->registro_id <= 0 ){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data:  $modelo->registro_id,
                es_final: true);
        }
        $where = $this->where_inicial(campo_llave: $modelo->campo_llave,registro_id:  $modelo->registro_id,
            tabla:  $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where',data:  $where);
        }

        $consulta = $this->integra_where_seguridad(consulta: $consulta, modelo: $modelo, where: $where);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where', data: $consulta);
        }

        return $consulta;

    }

    /**
     * TOTAL
     * The `where_campo_llave` function constructs an SQL WHERE clause based upon
     * the parameters given.
     *
     * @param string $campo_llave The name of the key field's column.
     * @param int $registro_id The id of the record.
     * @param string $tabla The name of the table.
     * @return string|array The SQL WHERE clause OR error array with message and data if either $campo_llave or $tabla are empty.
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm._where.where_campo_llave
     */
    private function where_campo_llave(string $campo_llave, int $registro_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje:'Error tabla esta vacia', data:$tabla, es_final: true);
        }
        $campo_llave = trim($campo_llave);
        if($campo_llave === ''){
            return $this->error->error(mensaje:'Error campo_llave esta vacia', data:$campo_llave, es_final: true);
        }
        return " WHERE $tabla".".$campo_llave = $registro_id ";

    }

    /**
     * TOTAL
     * Este método genera una condición WHERE para una consulta SQL utilizando
     * el ID de registro y el nombre de tabla proporcionados.
     *
     * @param int $registro_id Es el ID del registro en la base de datos
     * @param string $tabla Es el nombre de la tabla en la base de datos
     * @return string|array Retorna una cadena que representa una condición WHERE
     * en caso de éxito y un array con un mensaje de error si el nombre de la tabla
     * proporcionado está vacío
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm._where.where_id_base
     */
    private function where_id_base(int $registro_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla, es_final: true);
        }
        return " WHERE $tabla".".id = $registro_id ";
    }

    /**
     * TOTAL
     * Genera una cláusula WHERE SQL inicial basándose en el campo clave proporcionado y el registro_id.
     *
     * @param string $campo_llave El nombre del campo clave para el cual se generará la cláusula WHERE.
     * @param int $registro_id El identificador del registro para el que se generará la cláusula WHERE.
     * @param string $tabla El nombre de la tabla donde se realizará la consulta.
     *
     * @return array|string Si todo va bien, retorna la cláusula WHERE generada. En caso de error,
     * retorna un array con la descripción del error.
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm._where.where_inicial
     */
    private function where_inicial(string $campo_llave, int $registro_id, string $tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla, es_final: true);
        }
        $where_id_base = $this->where_id_base(registro_id: $registro_id,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where_id_base',data:  $where_id_base);
        }

        if($campo_llave === ""){
            $where = $where_id_base;
        }
        else{
            $where_campo_llave = $this->where_campo_llave(campo_llave: $campo_llave, registro_id: $registro_id,
                tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar where_id_base',data:  $where_campo_llave);
            }
            $where = $where_campo_llave;
        }
        return $where;

    }

    /**
     * TOTAL
     * Método que se encarga de aplicar una condición de seguridad a una declaración WHERE de SQL.
     *
     * @param string $seguridad La condición de seguridad a aplicar.
     * @param string $where La declaración WHERE actual, a la que se añadirá la condición de seguridad.
     *
     * @return string|array Si 'aplica_seguridad' es verdadero, devuelve una nueva declaración WHERE con la condición de seguridad aplicada.
     *                      Si 'aplica_seguridad' es falso, simplemente devuelve la declaración WHERE original.
     *
     *                      Si la condición de seguridad o la declaración WHERE están vacías, termina la ejecución del método y
     *                      devuelve un array con un mensaje de error y la condición de seguridad que causó el problema.
     *
     * @example Si 'aplica_seguridad' es verdadero, $seguridad es 'usuario_id = 1' y $where es 'producto_id = 5', el método devolverá
     *          'producto_id = 5 AND usuario_id = 1'.
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm._where.where_seguridad
     *
     */
    private function where_seguridad(modelo $modelo, string $seguridad, string $where): string|array
    {
        if($modelo->aplica_seguridad){
            $seguridad = trim($seguridad);
            if($seguridad === ''){
                return $this->error->error(mensaje: 'Error seguridad esta vacia',data:  $seguridad, es_final: true);
            }
            $where = trim($where);
            if($where === ''){
                $where .= " WHERE $seguridad ";
            }
            else{
                $where .= " AND $seguridad ";
            }
            $where = " $where ";
        }
        return $where;
    }

}
