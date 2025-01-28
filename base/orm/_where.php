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
     * REG
     * Genera un `WHERE` SQL que incluye condiciones de seguridad específicas según las reglas definidas
     * en el modelo proporcionado.
     *
     * Esta función aplica reglas de seguridad basadas en la configuración del modelo, combinando cualquier
     * condición de seguridad generada con un `WHERE` previo proporcionado.
     *
     * @param modelo $modelo Instancia del modelo que define las reglas de seguridad a aplicar. Propiedades relevantes:
     *                       - `aplica_seguridad` (bool): Determina si deben aplicarse condiciones de seguridad.
     *                       - `columnas_extra` (array): Contiene la configuración específica para las condiciones
     *                         de seguridad, como campos requeridos y subconsultas relacionadas.
     * @param string $where Condición SQL `WHERE` inicial que puede estar vacía o contener condiciones previas.
     *
     * @return array|string Retorna la condición `WHERE` resultante en formato SQL si la operación es exitosa.
     *                      En caso de error, devuelve un arreglo con los detalles del error.
     *
     * @example Uso exitoso con seguridad aplicada:
     * ```php
     * $modelo->aplica_seguridad = true;
     * $modelo->columnas_extra = [
     *     'usuario_permitido_id' => "(tabla.usuario_id)"
     * ];
     * $where = "estado = 'activo'";
     * $resultado = $this->genera_where_seguridad($modelo, $where);
     * // Resultado:
     * // "estado = 'activo' AND (tabla.usuario_id) = $_SESSION[usuario_id]"
     * ```
     *
     * @example Uso exitoso sin seguridad aplicada:
     * ```php
     * $modelo->aplica_seguridad = false;
     * $modelo->columnas_extra = [];
     * $where = "estado = 'activo'";
     * $resultado = $this->genera_where_seguridad($modelo, $where);
     * // Resultado:
     * // "estado = 'activo'"
     * ```
     *
     * @example Uso con un `where` vacío:
     * ```php
     * $modelo->aplica_seguridad = true;
     * $modelo->columnas_extra = [
     *     'usuario_permitido_id' => "(tabla.usuario_id)"
     * ];
     * $where = '';
     * $resultado = $this->genera_where_seguridad($modelo, $where);
     * // Resultado:
     * // "WHERE (tabla.usuario_id) = $_SESSION[usuario_id]"
     * ```
     *
     * @throws errores Retorna un error si:
     * - La generación de condiciones de seguridad (`seguridad`) falla.
     * - La integración del `WHERE` falla.
     */
    private function genera_where_seguridad(modelo $modelo, string $where): array|string
    {
        $seguridad = (new params_sql())->seguridad(
            aplica_seguridad: $modelo->aplica_seguridad,
            modelo_columnas_extra: $modelo->columnas_extra,
            sql_where_previo: $where
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql de seguridad', data: $seguridad);
        }

        $where = $this->where_seguridad(modelo: $modelo, seguridad: $seguridad, where: $where);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        return $where;
    }



    /**
     * REG
     * Integra condiciones de seguridad en una consulta SQL agregando un `WHERE` con restricciones específicas
     * basadas en las reglas definidas en el modelo proporcionado.
     *
     * Esta función toma una consulta base, aplica condiciones de seguridad según el modelo, y retorna la consulta
     * completa con las condiciones de seguridad integradas.
     *
     * @param string $consulta Consulta SQL base que puede o no contener un `WHERE` previo.
     * @param modelo $modelo Instancia del modelo que define las reglas de seguridad a aplicar. Propiedades relevantes:
     *                       - `aplica_seguridad` (bool): Indica si deben aplicarse restricciones de seguridad.
     *                       - `columnas_extra` (array): Define las configuraciones y campos relacionados con la seguridad.
     * @param string $where Condición SQL `WHERE` inicial que puede estar vacía o contener restricciones previas.
     *
     * @return string|array Retorna la consulta SQL completa con el `WHERE` de seguridad integrado si la operación es exitosa.
     *                      En caso de error, devuelve un arreglo con los detalles del error.
     *
     * @example Uso exitoso con seguridad aplicada:
     * ```php
     * $consulta = "SELECT * FROM usuarios";
     * $modelo->aplica_seguridad = true;
     * $modelo->columnas_extra = [
     *     'usuario_permitido_id' => "(tabla.usuario_id)"
     * ];
     * $where = "estado = 'activo'";
     * $resultado = $this->integra_where_seguridad($consulta, $modelo, $where);
     * // Resultado:
     * // "SELECT * FROM usuarios WHERE estado = 'activo' AND (tabla.usuario_id) = $_SESSION[usuario_id]"
     * ```
     *
     * @example Uso exitoso sin seguridad aplicada:
     * ```php
     * $consulta = "SELECT * FROM usuarios";
     * $modelo->aplica_seguridad = false;
     * $modelo->columnas_extra = [];
     * $where = "estado = 'activo'";
     * $resultado = $this->integra_where_seguridad($consulta, $modelo, $where);
     * // Resultado:
     * // "SELECT * FROM usuarios WHERE estado = 'activo'"
     * ```
     *
     * @example Uso con consulta y `where` vacíos:
     * ```php
     * $consulta = "SELECT * FROM usuarios";
     * $modelo->aplica_seguridad = true;
     * $modelo->columnas_extra = [
     *     'usuario_permitido_id' => "(tabla.usuario_id)"
     * ];
     * $where = '';
     * $resultado = $this->integra_where_seguridad($consulta, $modelo, $where);
     * // Resultado:
     * // "SELECT * FROM usuarios WHERE (tabla.usuario_id) = $_SESSION[usuario_id]"
     * ```
     *
     * @throws errores Retorna un error si:
     * - La generación del `WHERE` de seguridad falla.
     * - La integración del `WHERE` en la consulta falla.
     */
    private function integra_where_seguridad(string $consulta, modelo $modelo, string $where): string|array
    {
        $where = $this->genera_where_seguridad(modelo: $modelo, where: $where);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        $consulta .= $where;

        return $consulta;
    }


    /**
     * REG
     * Genera una consulta SQL con una cláusula `WHERE` basada en los datos del modelo.
     *
     * Esta función toma una consulta SQL base y agrega una cláusula `WHERE` utilizando el `registro_id` y
     * opcionalmente el `campo_llave` definidos en el modelo. Además, integra seguridad adicional si está habilitada
     * en el modelo.
     *
     * @param string $consulta La consulta SQL base sobre la cual se construirá la cláusula `WHERE`.
     * @param modelo $modelo Una instancia del modelo que contiene los datos necesarios para generar la cláusula
     *                       `WHERE`, como `registro_id`, `campo_llave` y `tabla`.
     *
     * @return array|string Retorna la consulta SQL completa con la cláusula `WHERE` integrada. En caso de error,
     *                      retorna un arreglo con los detalles del error.
     *
     * @example Uso exitoso:
     * ```php
     * $consulta = 'SELECT * FROM usuarios';
     * $modelo = new modelo();
     * $modelo->registro_id = 123;
     * $modelo->campo_llave = 'id_usuario';
     * $modelo->tabla = 'usuarios';
     * $consulta_completa = $this->sql_where(consulta: $consulta, modelo: $modelo);
     * // Resultado:
     * // "SELECT * FROM usuarios WHERE usuarios.id_usuario = 123"
     * ```
     *
     * @example Error por `registro_id` inválido:
     * ```php
     * $consulta = 'SELECT * FROM usuarios';
     * $modelo = new modelo();
     * $modelo->registro_id = -1; // ID inválido
     * $modelo->campo_llave = 'id_usuario';
     * $modelo->tabla = 'usuarios';
     * $resultado = $this->sql_where(consulta: $consulta, modelo: $modelo);
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error registro_id debe ser mayor a 0',
     * //     'data' => -1
     * // ]
     * ```
     *
     * @example Error en la generación de la cláusula `WHERE`:
     * ```php
     * $consulta = 'SELECT * FROM usuarios';
     * $modelo = new modelo();
     * $modelo->registro_id = 123;
     * $modelo->campo_llave = '';
     * $modelo->tabla = ''; // Tabla vacía
     * $resultado = $this->sql_where(consulta: $consulta, modelo: $modelo);
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error tabla esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * @throws errores Retorna un error si:
     * - `registro_id` del modelo es menor o igual a 0.
     * - La función `where_inicial` falla en la construcción de la cláusula `WHERE`.
     * - La función `integra_where_seguridad` falla al integrar seguridad adicional en la consulta.
     *
     * @note Esta función utiliza las funciones privadas `where_inicial` y `integra_where_seguridad` para construir
     *       la cláusula `WHERE` y aplicar reglas de seguridad según el modelo.
     */
    final public function sql_where(string $consulta, modelo $modelo): array|string
    {
        if ($modelo->registro_id <= 0) {
            return $this->error->error(
                mensaje: 'Error registro_id debe ser mayor a 0',
                data: $modelo->registro_id,
                es_final: true
            );
        }

        $where = $this->where_inicial(
            campo_llave: $modelo->campo_llave,
            registro_id: $modelo->registro_id,
            tabla: $modelo->tabla
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        $consulta = $this->integra_where_seguridad(
            consulta: $consulta,
            modelo: $modelo,
            where: $where
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar where', data: $consulta);
        }

        return $consulta;
    }


    /**
     * REG
     * Genera una cláusula `WHERE` para filtrar registros en una tabla utilizando un campo llave y su valor.
     *
     * Esta función construye una cláusula SQL `WHERE` basada en un campo llave específico, su valor y el nombre de la
     * tabla. Se valida que el nombre de la tabla y el campo llave no estén vacíos antes de construir la cláusula.
     *
     * @param string $campo_llave El nombre del campo llave que se utilizará para filtrar los registros. Debe ser una
     *                            cadena no vacía.
     * @param int $registro_id El ID del registro que se desea filtrar. Debe ser un número entero válido.
     * @param string $tabla El nombre de la tabla donde se realizará el filtro. Debe ser una cadena no vacía.
     *
     * @return string|array Retorna la cláusula `WHERE` en formato de cadena si la operación es exitosa. Por ejemplo:
     *                      `" WHERE usuarios.id_usuario = 123 "`.
     *                      En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example Uso exitoso:
     * ```php
     * $campo_llave = 'id_usuario';
     * $registro_id = 123;
     * $tabla = 'usuarios';
     * $where = $this->where_campo_llave($campo_llave, $registro_id, $tabla);
     * // Resultado:
     * // " WHERE usuarios.id_usuario = 123 "
     * ```
     *
     * @example Error por tabla vacía:
     * ```php
     * $campo_llave = 'id_usuario';
     * $registro_id = 123;
     * $tabla = '';
     * $where = $this->where_campo_llave($campo_llave, $registro_id, $tabla);
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error tabla esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * @example Error por campo llave vacío:
     * ```php
     * $campo_llave = '';
     * $registro_id = 123;
     * $tabla = 'usuarios';
     * $where = $this->where_campo_llave($campo_llave, $registro_id, $tabla);
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error campo_llave esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * @throws errores Retorna un error si:
     * - El nombre de la tabla está vacío.
     * - El nombre del campo llave está vacío.
     *
     * @note Esta función no valida si el `$registro_id` o `$campo_llave` son válidos en términos de negocio, solo se
     *       asegura de que estén presentes y sean utilizables en la consulta.
     */
    private function where_campo_llave(string $campo_llave, int $registro_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla, es_final: true);
        }
        $campo_llave = trim($campo_llave);
        if ($campo_llave === '') {
            return $this->error->error(mensaje: 'Error campo_llave esta vacia', data: $campo_llave, es_final: true);
        }
        return " WHERE $tabla" . ".$campo_llave = $registro_id ";
    }


    /**
     * REG
     * Genera una cláusula `WHERE` básica para buscar un registro específico en una tabla por su ID.
     *
     * La función construye una condición SQL `WHERE` para filtrar registros basándose en el ID proporcionado y el nombre
     * de la tabla. Se valida que el nombre de la tabla no esté vacío antes de construir la cláusula.
     *
     * @param int $registro_id El ID del registro que se desea filtrar en la tabla. Debe ser un número entero válido.
     * @param string $tabla El nombre de la tabla donde se realizará el filtro. Debe ser una cadena no vacía.
     *
     * @return string|array Retorna la cláusula `WHERE` en formato de cadena si la operación es exitosa. Por ejemplo:
     *                      `" WHERE tabla.id = 123 "`.
     *                      En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example Uso exitoso:
     * ```php
     * $registro_id = 123;
     * $tabla = 'usuarios';
     * $where = $this->where_id_base($registro_id, $tabla);
     * // Resultado:
     * // " WHERE usuarios.id = 123 "
     * ```
     *
     * @example Error por tabla vacía:
     * ```php
     * $registro_id = 123;
     * $tabla = '';
     * $where = $this->where_id_base($registro_id, $tabla);
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error tabla esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * @throws errores Retorna un error si:
     * - El nombre de la tabla está vacío.
     *
     * @note Esta función no valida si el `$registro_id` es válido en términos de negocio, solo asume que es un entero.
     *       Se recomienda validar el `$registro_id` antes de llamar a esta función si es necesario.
     */
    private function where_id_base(int $registro_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla, es_final: true);
        }
        return " WHERE $tabla" . ".id = $registro_id ";
    }


    /**
     * REG
     * Genera una cláusula `WHERE` inicial basada en un registro ID y opcionalmente un campo llave.
     *
     * Esta función construye una cláusula SQL `WHERE` para filtrar registros en una tabla. Si se proporciona un campo
     * llave, se utiliza para construir el filtro; de lo contrario, se filtra únicamente por el ID del registro.
     *
     * @param string $campo_llave El nombre del campo llave que se utilizará para filtrar los registros. Puede ser una
     *                            cadena vacía si no se utiliza un campo llave adicional.
     * @param int $registro_id El ID del registro que se desea filtrar. Debe ser un número entero válido.
     * @param string $tabla El nombre de la tabla donde se realizará el filtro. Debe ser una cadena no vacía.
     *
     * @return string|array Retorna la cláusula `WHERE` en formato de cadena si la operación es exitosa. Por ejemplo:
     *                      `" WHERE tabla.id = 123 "` o `" WHERE tabla.campo_llave = 123 "`.
     *                      En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example Uso exitoso sin campo llave:
     * ```php
     * $campo_llave = '';
     * $registro_id = 123;
     * $tabla = 'usuarios';
     * $where = $this->where_inicial($campo_llave, $registro_id, $tabla);
     * // Resultado:
     * // " WHERE usuarios.id = 123 "
     * ```
     *
     * @example Uso exitoso con campo llave:
     * ```php
     * $campo_llave = 'id_usuario';
     * $registro_id = 123;
     * $tabla = 'usuarios';
     * $where = $this->where_inicial($campo_llave, $registro_id, $tabla);
     * // Resultado:
     * // " WHERE usuarios.id_usuario = 123 "
     * ```
     *
     * @example Error por tabla vacía:
     * ```php
     * $campo_llave = '';
     * $registro_id = 123;
     * $tabla = '';
     * $where = $this->where_inicial($campo_llave, $registro_id, $tabla);
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error tabla esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * @example Error por fallo en la generación de `where_id_base`:
     * ```php
     * $campo_llave = '';
     * $registro_id = -1; // Valor inválido que causa un error en `where_id_base`.
     * $tabla = 'usuarios';
     * $where = $this->where_inicial($campo_llave, $registro_id, $tabla);
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error al generar where_id_base',
     * //     'data' => ... // Detalles del error.
     * // ]
     * ```
     *
     * @throws errores Retorna un error si:
     * - El nombre de la tabla está vacío.
     * - La función `where_id_base` o `where_campo_llave` falla.
     *
     * @note Esta función utiliza las funciones privadas `where_id_base` y `where_campo_llave` para construir la cláusula
     *       `WHERE` según los parámetros proporcionados.
     */
    private function where_inicial(string $campo_llave, int $registro_id, string $tabla): array|string
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla, es_final: true);
        }

        $where_id_base = $this->where_id_base(registro_id: $registro_id, tabla: $tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar where_id_base', data: $where_id_base);
        }

        if ($campo_llave === "") {
            $where = $where_id_base;
        } else {
            $where_campo_llave = $this->where_campo_llave(campo_llave: $campo_llave, registro_id: $registro_id, tabla: $tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar where_id_base', data: $where_campo_llave);
            }
            $where = $where_campo_llave;
        }

        return $where;
    }


    /**
     * REG
     * Genera o ajusta una cláusula SQL `WHERE` incorporando condiciones de seguridad según el modelo.
     *
     * Esta función asegura que, si el modelo requiere aplicar seguridad, se integre una cláusula de seguridad
     * en el `WHERE` existente o se cree una nueva cláusula `WHERE` si no existe previamente.
     *
     * @param modelo $modelo Instancia del modelo que contiene la propiedad `aplica_seguridad`, indicando si
     *                       debe aplicarse seguridad.
     *                       - `true`: Se aplica seguridad.
     *                       - `false`: No se realiza ningún cambio en el `WHERE`.
     * @param string $seguridad Condición de seguridad en formato SQL que debe ser integrada al `WHERE`. Ejemplo:
     *                          ```sql
     *                          "(tabla.usuario_id) = $_SESSION[usuario_id]"
     *                          ```
     *                          Esta condición asegura que los datos sean filtrados según el usuario actual.
     * @param string $where Condición `WHERE` existente que puede ser extendida con la condición de seguridad.
     *                      Si está vacía, se genera una nueva cláusula `WHERE`.
     *
     * @return string|array Devuelve el `WHERE` resultante en formato SQL si la operación es exitosa.
     *                      En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example Uso exitoso con `aplica_seguridad = true` y `where` existente:
     * ```php
     * $modelo->aplica_seguridad = true;
     * $seguridad = "(tabla.usuario_id) = $_SESSION[usuario_id]";
     * $where = "estado = 'activo'";
     * $resultado = $this->where_seguridad($modelo, $seguridad, $where);
     * // Resultado:
     * // "estado = 'activo' AND (tabla.usuario_id) = $_SESSION[usuario_id]"
     * ```
     *
     * @example Uso exitoso con `aplica_seguridad = true` y `where` vacío:
     * ```php
     * $modelo->aplica_seguridad = true;
     * $seguridad = "(tabla.usuario_id) = $_SESSION[usuario_id]";
     * $where = '';
     * $resultado = $this->where_seguridad($modelo, $seguridad, $where);
     * // Resultado:
     * // "WHERE (tabla.usuario_id) = $_SESSION[usuario_id]"
     * ```
     *
     * @example Uso sin aplicar seguridad:
     * ```php
     * $modelo->aplica_seguridad = false;
     * $seguridad = "(tabla.usuario_id) = $_SESSION[usuario_id]";
     * $where = "estado = 'activo'";
     * $resultado = $this->where_seguridad($modelo, $seguridad, $where);
     * // Resultado:
     * // "estado = 'activo'"
     * ```
     *
     * @throws errores Retorna un error si:
     * - `seguridad` está vacío cuando `aplica_seguridad` es `true`.
     * - Ocurre cualquier fallo en la lógica.
     */
    private function where_seguridad(modelo $modelo, string $seguridad, string $where): string|array
    {
        if ($modelo->aplica_seguridad) {
            $seguridad = trim($seguridad);
            if ($seguridad === '') {
                return $this->error->error(mensaje: 'Error seguridad esta vacia', data: $seguridad, es_final: true);
            }
            $where = trim($where);
            if ($where === '') {
                $where .= " WHERE $seguridad ";
            } else {
                $where .= " AND $seguridad ";
            }
            $where = " $where ";
        }
        return $where;
    }


}
