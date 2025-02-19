<?php
namespace base\orm;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;

class _defaults{

    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }
    final public function ajusta_data_catalogo(array $catalogo, modelo $modelo){

        $campos = array('id','descripcion','codigo');
        foreach ($campos as $campo) {
            $catalogo = $this->ajusta_datas_catalogo(catalogo: $catalogo,campo:  $campo,modelo:  $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    private function ajusta_datas_catalogo(array $catalogo, string $campo, modelo $modelo){
        foreach ($catalogo as $indice => $row) {
            $catalogo = $this->ajusta_row(campo: $campo, catalogo: $catalogo, indice: $indice, modelo: $modelo, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    private function ajusta_row(string $campo, array $catalogo, int $indice, modelo $modelo, array $row){
        if(isset($row[$campo])) {
            $filtro = $this->filtro(campo: $campo, modelo: $modelo, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
            }

            $catalogo = $this->limpia_si_existe(catalogo: $catalogo, filtro: $filtro, indice: $indice, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    final public function alta_defaults(array $catalogo, modelo $entidad, array $filtro = array()){

        $catalogo = $this->ajusta_data_catalogo(catalogo: $catalogo,modelo:  $entidad);
        if (errores::$error) {
            $error = $this->error->error(mensaje: 'Error al ajustar catalogo', data: $catalogo);
            print_r($error);
            exit;
        }

        foreach ($catalogo as $row) {
            $r_alta_bd = $this->inserta_default(entidad: $entidad,row:  $row, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $catalogo;
    }

    /**
     * Verifica si existe un codigo
     * @param modelo $entidad Entidad en proceso
     * @param array $row Registro a validar
     * @param array $filtro Filtro a validar
     * @return array|bool
     *
     */
    private function existe_cod_default(modelo $entidad, array $row, array $filtro = array()): bool|array
    {
        $existe = false;
        if(isset($row[$entidad->tabla.'.codigo'])) {
            $filtro = $this->filtro_default(entidad: $entidad, row: $row, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
            }

            $existe = $entidad->existe(filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar si existe cat_sat_tipo_de_comprobante', data: $existe);
            }
        }
        return $existe;
    }

    /**
     * Genera un filtro para default
     * @param string $campo Campo a verificar
     * @param modelo $modelo Modelo a integrar
     * @param array $row Registro a integrar
     * @return array
     */
    private function filtro(string $campo, modelo $modelo, array $row): array
    {
        $filtro = array();
        $filtro[$modelo->tabla.'.'.$campo] = $row[$campo];
        return $filtro;
    }

    /**
     * REG
     * Genera un filtro para validar la existencia de un registro en la base de datos.
     *
     * Esta función crea un filtro basado en el valor del campo `codigo` dentro del registro `$row`.
     * Si no se proporciona un filtro preexistente (`$filtro` está vacío), se validará que el campo `codigo` esté presente en `$row`.
     * Luego, se construirá un filtro para verificar la existencia del registro en la tabla de la entidad proporcionada.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo $entidad Instancia del modelo que representa la entidad en la base de datos.
     *                        Se utilizará su propiedad `$tabla` para formar el filtro.
     *                        - **Ejemplo:** `new adm_usuario($pdo)` representa la tabla `adm_usuario`.
     *
     * @param array $row Registro que contiene los datos a validar, incluyendo el campo `codigo`.
     *                   - **Ejemplo:** `['codigo' => 'USR123', 'nombre' => 'Juan Pérez']`
     *
     * @param array $filtro (Opcional) Filtro preexistente que puede ser combinado con el generado.
     *                      Si está vacío, se generará un filtro con base en `codigo`.
     *                      - **Ejemplo:** `['adm_usuario.email' => 'usuario@example.com']`
     *
     * ---
     *
     * @return array Retorna un array con el filtro generado para validar la existencia del registro.
     *               Si `$filtro` ya contenía valores, se conservarán y se añadirá el nuevo filtro.
     *               En caso de error, la función devolverá un array con el mensaje de error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $modelo = new adm_usuario($pdo);
     * $row = ['codigo' => 'USR123', 'nombre' => 'Juan Pérez'];
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: $row);
     * print_r($filtro);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     'adm_usuario.codigo' => 'USR123'
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con Filtro Preexistente:**
     * ```php
     * $modelo = new adm_usuario($pdo);
     * $row = ['codigo' => 'USR123', 'nombre' => 'Juan Pérez'];
     * $filtro_existente = ['adm_usuario.email' => 'usuario@example.com'];
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: $row, filtro: $filtro_existente);
     * print_r($filtro);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     'adm_usuario.email' => 'usuario@example.com',
     *     'adm_usuario.codigo' => 'USR123'
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si el campo `codigo` no está presente en `$row`**
     * ```php
     * $row = ['nombre' => 'Juan Pérez'];
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: $row);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al validar row',
     *     'data' => ['nombre' => 'Juan Pérez']
     * ]
     * ```
     *
     * **Ejemplo 2: Si la tabla está vacía**
     * ```php
     * $modelo->tabla = ''; // Forzar un error
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: ['codigo' => 'USR123']);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error tabla esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si la tabla está vacía o si el campo `codigo` no está presente en `$row`.
     */
    private function filtro_default(modelo $entidad, array $row, array $filtro = array()): array
    {
        // Validar que la tabla de la entidad no esté vacía
        $tabla = trim($entidad->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        // Si no se ha pasado un filtro preexistente, validar la existencia del campo 'codigo' en el registro
        if(count($filtro) === 0) {
            $keys = array('codigo');
            $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar row', data: $valida);
            }

            // Generar filtro basado en el campo 'codigo'
            $filtro[$tabla . '.codigo'] = $row['codigo'];
        }

        return $filtro;
    }


    private function inserta_default(modelo $entidad, array $row, array $filtro = array()){
        $existe = $this->existe_cod_default(entidad: $entidad,row:  $row, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe entidad'.$entidad->tabla, data: $existe);
        }

        if (!$existe) {
            $r_alta_bd = $entidad->alta_registro(registro: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $row;
    }

    private function limpia_si_existe(array $catalogo, array $filtro, int $indice, modelo $modelo){
        $existe = $modelo->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al verificar si existe', data: $existe);
        }
        if($existe){
            unset($catalogo[$indice]);
        }
        return $catalogo;
    }
}
