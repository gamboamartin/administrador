<?php
namespace base\orm;

use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use stdClass;

class val_sql extends validaciones {

    /**
     * REG
     * Verifica si un campo específico existe dentro de un registro y valida su contenido.
     *
     * Esta función primero valida que el nombre del campo proporcionado (`$campo`) sea un texto válido
     * utilizando `txt_valido()`. Luego, verifica si dicho campo existe dentro del array `$registro`,
     * comparándolo con una lista de claves obligatorias (`$keys_ids`).
     *
     * @param string $campo Nombre del campo que se debe verificar.
     * @param array $keys_ids Lista de claves que deben existir en `$registro`.
     * @param array $registro Registro en el que se validará la existencia del campo.
     *
     * @return array|string Retorna:
     *  - Un `string` con el nombre del campo si la validación es exitosa.
     *  - Un `array` con detalles del error si el campo no es válido o no existe en el registro.
     *
     * @example
     *  Ejemplo 1: Campo válido y presente en el registro
     *  -------------------------------------------------
     *  $campo = 'id_usuario';
     *  $keys_ids = ['id_usuario', 'id_cliente'];
     *  $registro = ['id_usuario' => 10, 'id_cliente' => 20];
     *  $resultado = $this->campo_existe($campo, $keys_ids, $registro);
     *  // $resultado => 'id_usuario'
     *
     * @example
     *  Ejemplo 2: Campo inválido (vacío o numérico)
     *  --------------------------------------------
     *  $campo = '';
     *  $keys_ids = ['id_usuario', 'id_cliente'];
     *  $registro = ['id_usuario' => 10, 'id_cliente' => 20];
     *  $resultado = $this->campo_existe($campo, $keys_ids, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error key invalido',
     *  //     'data' => ''
     *  // ]
     *
     * @example
     *  Ejemplo 3: Campo no encontrado en el registro
     *  --------------------------------------------
     *  $campo = 'id_pedido';
     *  $keys_ids = ['id_usuario', 'id_cliente'];
     *  $registro = ['id_usuario' => 10, 'id_cliente' => 20];
     *  $resultado = $this->campo_existe($campo, $keys_ids, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar si existe',
     *  //     'data' => ...
     *  // ]
     */
    private function campo_existe(string $campo, array $keys_ids, array $registro): array|string
    {
        $campo_r = $this->txt_valido(txt: $campo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error key invalido', data: $campo_r);
        }

        $existe = $this->existe(keys_obligatorios: $keys_ids, registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al verificar si existe', data: $existe);
        }

        return $campo_r;
    }


    /**
     * REG
     * Valida que los campos especificados en `$keys_checked` contengan valores válidos en `$registro`.
     *
     * Esta función recorre cada campo en `$keys_checked` y verifica que:
     * 1. Exista en el array `$registro`.
     * 2. Contenga un valor válido (`'activo'` o `'inactivo'`).
     *
     * La validación se realiza utilizando la función `verifica_chk()`,
     * la cual se encarga de verificar la existencia del campo y que su valor sea válido.
     *
     * @param array $keys_checked Lista de claves que deben existir en `$registro` y tener valores `'activo'` o `'inactivo'`.
     * @param array $registro Registro que contiene los valores a validar.
     *
     * @return bool|array Retorna:
     *  - `true` si todos los campos en `$keys_checked` están presentes en `$registro` y contienen valores válidos.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  $keys_checked = ['estado', 'estatus'];
     *  $registro = ['estado' => 'activo', 'estatus' => 'inactivo'];
     *  $resultado = $this->checked($keys_checked, $registro);
     *  // $resultado => true
     *
     * @example
     *  Ejemplo 2: Falta un campo en el registro
     *  ----------------------------------------
     *  $keys_checked = ['estado', 'estatus'];
     *  $registro = ['estado' => 'activo'];
     *  $resultado = $this->checked($keys_checked, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error debe existir en registro estatus',
     *  //     'data' => ['estado' => 'activo']
     *  // ]
     *
     * @example
     *  Ejemplo 3: Campo con valor inválido
     *  ------------------------------------
     *  $keys_checked = ['estado'];
     *  $registro = ['estado' => 'pendiente'];
     *  $resultado = $this->checked($keys_checked, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error $registro[estado] debe ser activo o inactivo',
     *  //     'data' => ['estado' => 'pendiente']
     *  // ]
     */
    private function checked(array $keys_checked, array $registro): bool|array
    {
        foreach ($keys_checked as $campo) {
            $verifica = $this->verifica_chk(campo: $campo, keys_checked: $keys_checked, registro: $registro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al verificar campo', data: $verifica);
            }
        }
        return true;
    }


    /**
     * Valida que un conjunto de ids existan y sean validos para una transaccion de un modelo
     * @param array $keys_ids Campos de tipo ids a validar
     * @param array $registro Registro a verificar en conjunto de los keys id definidos
     * @return bool|array
     * @version 1.443.48
     */
    private function ids(array $keys_ids, array $registro ): bool|array
    {
        foreach($keys_ids as $campo){
            $verifica = $this->verifica_id(campo: $campo,keys_ids: $keys_ids,registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar campo ids',data:  $verifica);
            }

        }
        return true;
    }

    /**
     * Limpia y valida unj elemento vacio
     * @param string $campo campo a verificar
     * @param array $keys_obligatorios Conjunto de campos obligatorios definidos en el modelo
     * @param array $registro Registro a validar
     * @return array|string
     * @version 1.351.41
     */
    private function data_vacio(string $campo, array $keys_obligatorios, array $registro): array|string
    {
        $campo_r = $this->txt_valido($campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al reasignar campo valor',data: $campo_r);
        }
        $existe = $this->existe(keys_obligatorios: $keys_obligatorios,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe',data: $existe);
        }
        if(!isset($registro[$campo_r])){
            $registro[$campo_r] = '';
        }
        return trim($registro[$campo_r]);
    }

    /**
     * REG
     * Verifica si todos los campos obligatorios existen dentro de un registro.
     *
     * Esta función recorre un array de claves obligatorias (`$keys_obligatorios`)
     * y verifica si cada una de ellas está presente en el array `$registro`.
     *
     * Para ello, utiliza la función `verifica_existe()`, la cual valida la existencia
     * de cada campo y devuelve un error en caso de que falte alguno.
     *
     * @param array $keys_obligatorios Lista de nombres de los campos que deben existir en `$registro`.
     * @param array $registro Registro en el que se validará la existencia de los campos obligatorios.
     *
     * @return bool|array Retorna:
     *  - `true` si todos los campos obligatorios existen en el registro.
     *  - Un `array` con detalles del error si falta algún campo.
     *
     * @example
     *  Ejemplo 1: Todos los campos existen
     *  -----------------------------------
     *  $keys_obligatorios = ['nombre', 'edad', 'correo'];
     *  $registro = ['nombre' => 'Juan', 'edad' => 30, 'correo' => 'juan@example.com'];
     *  $resultado = $this->existe($keys_obligatorios, $registro);
     *  // $resultado => true
     *
     * @example
     *  Ejemplo 2: Falta un campo en el registro
     *  ----------------------------------------
     *  $keys_obligatorios = ['nombre', 'edad', 'correo'];
     *  $registro = ['nombre' => 'Juan', 'edad' => 30];
     *  $resultado = $this->existe($keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar si existe campo',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error $registro[correo] debe existir',
     *  //         'data' => ['nombre' => 'Juan', 'edad' => 30]
     *  //     ],
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Lista de campos vacía
     *  --------------------------------
     *  $keys_obligatorios = [];
     *  $registro = ['nombre' => 'Juan', 'edad' => 30, 'correo' => 'juan@example.com'];
     *  $resultado = $this->existe($keys_obligatorios, $registro);
     *  // $resultado => true (No hay campos obligatorios que validar)
     */
    private function existe(array $keys_obligatorios, array $registro): bool|array
    {
        foreach ($keys_obligatorios as $campo) {
            $verifica = $this->verifica_existe(campo: $campo, registro: $registro);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al verificar si existe campo',
                    data: $verifica
                );
            }
        }
        return true;
    }



    private function cod_3_mayusc(array $keys_cod_3_mayus, array $registro ): bool|array
    {
        foreach($keys_cod_3_mayus as $campo){
            $verifica = $this->verifica_cod_3_mayusc(campo: $campo,keys_cod_3_mayus: $keys_cod_3_mayus,registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar campo ids',data:  $verifica);
            }

        }
        return true;
    }

    private function existe_duplicado(string $campo, modelo $modelo, array $registro, string $tabla): bool|array
    {
        $filtro = $this->filtro_no_duplicado(campo: $campo,registro:  $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data:  $filtro);
        }
        $existe = $modelo->existe(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error verificar si existe duplicado',data:  $existe);
        }
        if($existe){
            return $this->error->error(mensaje: 'Error ya existe un registro con el campo '.$campo, data: $existe);
        }

        return $existe;
    }

    private function filtro_no_duplicado(string $campo, array $registro, string $tabla): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacio', data: $tabla);
        }

        $keys = array($campo);
        $valida = $this->valida_existencia_keys(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro ', data: $valida);
        }

        $filtro = array();

        $key = $tabla.'.'.$campo;
        $filtro[$key] = $registro[$campo];

        return $filtro;
    }

    /**
     * REG
     * Limpia y valida los valores de `$key` y `$tipo_campo`, asegurando que sean strings válidos.
     *
     * Esta función:
     * 1. Verifica que `$key` y `$tipo_campo` no estén vacíos y sean strings válidos mediante `txt_valido()`.
     * 2. Si alguna validación falla, devuelve un error.
     * 3. Retorna un objeto `stdClass` con los valores validados.
     *
     * @param string $key Nombre de la clave que será validada.
     * @param string $tipo_campo Tipo de dato que será validado.
     *
     * @return array|stdClass Retorna:
     *  - Un objeto `stdClass` con los valores limpios si la validación es exitosa.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  $key = "nombre";
     *  $tipo_campo = "string";
     *  $resultado = $this->limpia_data_tipo_campo($key, $tipo_campo);
     *  // $resultado =>
     *  // {
     *  //     "key": "nombre",
     *  //     "tipo_campo": "string"
     *  // }
     *
     * @example
     *  Ejemplo 2: Error por `$key` vacío
     *  ---------------------------------
     *  $key = "";
     *  $tipo_campo = "string";
     *  $resultado = $this->limpia_data_tipo_campo($key, $tipo_campo);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error en key de tipo campo  string',
     *  //     'data' => ''
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por `$tipo_campo` vacío
     *  ----------------------------------------
     *  $key = "nombre";
     *  $tipo_campo = "";
     *  $resultado = $this->limpia_data_tipo_campo($key, $tipo_campo);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error en $tipo_campo de tipo campo',
     *  //     'data' => ''
     *  // ]
     */
    private function limpia_data_tipo_campo(string $key, string $tipo_campo): array|stdClass
    {
        $key_r = $this->txt_valido(txt: $key);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error en key de tipo campo ' . $key . ' ' . $tipo_campo, data: $key_r);
        }

        $tipo_campo_r = $this->txt_valido(txt: $tipo_campo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error en $tipo_campo de tipo campo', data: $tipo_campo_r);
        }

        $data = new stdClass();
        $data->key = $key_r;
        $data->tipo_campo = $tipo_campo_r;

        return $data;
    }


    /**
     * Verifica los campos obligatorios esten bien ajustados
     * @param array $keys_obligatorios Conjunto de keys a verificar nativos de modelo
     * @param array $registro Registro a validar
     * @return bool|array
     * @version 1.439.48
     */
    private function obligatorios(array $keys_obligatorios, array $registro): bool|array
    {
        $existe = $this->existe(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campos no existe', data: $existe);
        }
        $vacio = $this->vacio(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campo vacio', data: $vacio);
        }


        return true;
    }

    /**
     * REG
     * Valida que el nombre de una tabla no esté vacío.
     *
     * Este método verifica que el parámetro `$tabla` contenga un valor válido (no vacío).
     * Si `$tabla` está vacío, se genera un error utilizando `$this->error->error()`.
     * En caso contrario, retorna `true`, indicando que la validación fue exitosa.
     *
     * @param string $tabla Nombre de la tabla a validar.
     *
     * @return true|array Retorna:
     *  - `true` si `$tabla` contiene un valor no vacío.
     *  - Un `array` con detalles del error si `$tabla` está vacío.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  $tabla = "usuarios";
     *  $resultado = $this->tabla($tabla);
     *  // $resultado => true
     *
     * @example
     *  Ejemplo 2: Error por tabla vacía
     *  ---------------------------------
     *  $tabla = "";
     *  $resultado = $this->tabla($tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error tabla esta vacia',
     *  //     'data' => '',
     *  //     ...
     *  // ]
     */
    final public function tabla(string $tabla): true|array
    {
        // Elimina espacios en blanco del inicio y fin de la cadena
        $tabla = trim($tabla);

        // Valida que la tabla no esté vacía
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error tabla esta vacia',
                data: $tabla,
                es_final: true
            );
        }

        return true;
    }


    /**
     * Valida que los campos esten bien aplicados conforme el tipo de dato asignado
     * @param array $registro Registro a verificar
     * @param array $tipo_campos Conjunto de tipos de datos a verificar
     * @return bool|array
     * @version 1.292.41
     */
    private function tipo_campos(array $registro, array $tipo_campos): bool|array
    {
        foreach($tipo_campos as $key =>$tipo_campo){
            $valida_campos = $this->verifica_tipo_dato(key: $key,registro: $registro,tipo_campo: $tipo_campo);
            if(errores::$error){
                return $this->error->error(
                    mensaje: 'Error al validar campos '.$key.' '.$tipo_campo, data: $valida_campos);
            }
        }


        return true;
    }

    /**
     * REG
     * Valida que un texto no esté vacío y no sea un número.
     *
     * Esta función realiza las siguientes validaciones:
     * - Elimina espacios en blanco al inicio y al final del texto.
     * - Verifica que el texto no esté vacío.
     * - Verifica que el texto no sea un número.
     *
     * Si alguna de estas validaciones falla, la función devuelve un error estructurado utilizando `$this->error->error()`.
     * Si todas las validaciones son exitosas, devuelve el texto validado.
     *
     * @param string $txt El texto a validar.
     *
     * @return array|string Retorna:
     *  - Un `array` con detalles del error si el texto es inválido.
     *  - Un `string` con el texto validado si pasa todas las validaciones.
     *
     * @example
     *  Ejemplo 1: Texto válido
     *  -----------------------
     *  $resultado = $this->txt_valido("Ejemplo de texto");
     *  // $resultado => "Ejemplo de texto"
     *
     * @example
     *  Ejemplo 2: Texto vacío
     *  ----------------------
     *  $resultado = $this->txt_valido("");
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error el $txt no puede venir vacio',
     *  //     'data' => '',
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Texto numérico
     *  -------------------------
     *  $resultado = $this->txt_valido("12345");
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error el $txt es numero debe se un string',
     *  //     'data' => '12345',
     *  //     ...
     *  // ]
     */
    private function txt_valido(string $txt): array|string
    {
        $txt = trim($txt);

        if ($txt === '') {
            return $this->error->error(
                mensaje: 'Error el $txt no puede venir vacio',
                data: $txt,
                es_final: true
            );
        }

        if (is_numeric($txt)) {
            return $this->error->error(
                mensaje: 'Error el $txt es numero debe se un string',
                data: $txt,
                es_final: true
            );
        }

        return $txt;
    }


    /**
     * Valida que un campo obligatorio no se encuentre vacio
     * @param array $keys_obligatorios Conjunto de keys a verificar nativos de modelo
     * @param array $registro Registro a validar
     * @return bool|array
     * @version 1.439.48
     *
     */
    private function vacio(array $keys_obligatorios, array $registro): bool|array
    {
        foreach($keys_obligatorios as $campo){
            $verifica = $this->verifica_vacio(campo: $campo,keys_obligatorios: $keys_obligatorios,registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar vacio',data: $verifica);
            }
        }
        return true;
    }

    /**
     * Valida un registro previo a la alta en bd
     * @param array $campos_obligatorios Campos definidos en el modelo como obligatorios
     * @param modelo $modelo Modelo a validar el alta
     * @param array $no_duplicados Conjunto de campos del modelo asignados como no duplicados
     * @param array $registro Registro a validar
     * @param string $tabla Tabla o modelo a validar
     * @param array $tipo_campos Campos con tipificacion desde modelo
     * @param array $parents Campos parents a verificar
     * @return bool|array
     */
    final public function valida_base_alta(array $campos_obligatorios, modelo $modelo, array $no_duplicados, array $registro,
                                     string $tabla, array $tipo_campos, array $parents): bool|array
    {

        $valida = (new validaciones())->valida_alta_bd(registro: $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar alta ',data:  $valida);
        }

        $valida_estructura = $this->verifica_estructura(campos_obligatorios: $campos_obligatorios,
            registro: $registro,tabla: $tabla,tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar estructura ', data: $valida_estructura);
        }


        $verifica_parent = $this->verifica_parents(modelo: $modelo,parents:  $parents,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar parent',data:  $verifica_parent);
        }

        $verifica_no_duplicado = $this->verifica_no_duplicado(
            modelo: $modelo,no_duplicados:  $no_duplicados,registro:  $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar duplicado',data:  $verifica_no_duplicado);
        }

        return true;
    }

    /**
     *
     * Funcion que valida la estructura de los campos de un modelo
     *
     * @param array $registro Registro a verificar en conjunto de los keys id definidos
     * @param array $tipo_campos Campos con tipificacion desde modelo
     * @param array $keys_checked conjunto de campos en forma checked
     * @param array $keys_ids conjunto de campos en forma de id a validar
     * @param array $keys_obligatorios conjunto de campos obligatorios a validar
     * @return array|bool $this->registro
     * @example
     *     $valida_estructura = $this->valida_estructura_campos();
     *
     * @uses modelos->alta_bd
     * @uses producto->asigna_data_producto_factor
     * @internal  $this->valida_pattern_campo($key,$tipo_campo);
     * @version 1.444.48
     */
    private function valida_estructura_campos(array $registro, array $tipo_campos, array $keys_checked = array(),
                                              array $keys_cod_3_mayus = array(), array $keys_ids = array(),
                                              array $keys_obligatorios = array()): array|bool
    {


        $v_tipo_campos = $this->tipo_campos(registro:$registro, tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo de campo', data: $v_tipo_campos);
        }
        $v_obligatorios = $this->obligatorios(keys_obligatorios: $keys_obligatorios, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo de campo', data: $v_obligatorios);
        }
        $v_ids = $this->ids(keys_ids: $keys_ids,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar id', data: $v_ids);
        }
        $v_checked = $this->checked(keys_checked: $keys_checked,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar checked', data: $v_checked);
        }

        $v_cod_3_mayusc= $this->cod_3_mayusc(keys_cod_3_mayus: $keys_cod_3_mayus,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cod_3_mayusc', data: $v_cod_3_mayusc);
        }


        return true;
    }



    /**
     * REG
     * Valida que un campo en el registro tenga un valor de tipo "activo" o "inactivo".
     *
     * Esta función realiza las siguientes validaciones:
     * 1. Verifica que el nombre del campo proporcionado (`$campo`) sea un texto válido mediante `txt_valido()`.
     * 2. Comprueba si el campo existe en el registro, utilizando `existe()`.
     * 3. Confirma que el campo está presente en `$registro`.
     * 4. Valida que el valor del campo solo sea `'activo'` o `'inactivo'`.
     *
     * @param string $campo Nombre del campo a validar.
     * @param array $keys_checked Lista de claves que deben existir en `$registro`.
     * @param array $registro Registro en el que se validará el campo.
     *
     * @return bool|array Retorna:
     *  - `true` si la validación es exitosa.
     *  - Un `array` con detalles del error si el campo es inválido o su valor no es `'activo'` o `'inactivo'`.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  $campo = 'estado';
     *  $keys_checked = ['estado'];
     *  $registro = ['estado' => 'activo'];
     *  $resultado = $this->verifica_chk($campo, $keys_checked, $registro);
     *  // $resultado => true
     *
     * @example
     *  Ejemplo 2: Campo no existe en el registro
     *  ----------------------------------------
     *  $campo = 'estatus';
     *  $keys_checked = ['estado'];
     *  $registro = ['estado' => 'activo'];
     *  $resultado = $this->verifica_chk($campo, $keys_checked, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error debe existir en registro estatus',
     *  //     'data' => ['estado' => 'activo']
     *  // ]
     *
     * @example
     *  Ejemplo 3: Valor inválido en el campo
     *  ------------------------------------
     *  $campo = 'estado';
     *  $keys_checked = ['estado'];
     *  $registro = ['estado' => 'pendiente'];
     *  $resultado = $this->verifica_chk($campo, $keys_checked, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error $registro[estado] debe ser activo o inactivo',
     *  //     'data' => ['estado' => 'pendiente']
     *  // ]
     */
    private function verifica_chk(string $campo, array $keys_checked, array $registro): bool|array
    {
        $campo_r = $this->txt_valido($campo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar campo valor', data: $campo_r);
        }

        $existe = $this->existe(keys_obligatorios: $keys_checked, registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe', data: $existe);
        }

        if (!isset($registro[$campo_r])) {
            return $this->error->error(mensaje: 'Error debe existir en registro ' . $campo_r, data: $registro);
        }

        if ((string)$registro[$campo_r] !== 'activo' && (string)$registro[$campo_r] !== 'inactivo') {
            return $this->error->error(mensaje: 'Error $registro[' . $campo_r . '] debe ser activo o inactivo',
                data: $registro);
        }
        return true;
    }


    /**
     * Verifica la existencia de un campo
     * @param string $campo
     * @param array $keys
     * @param array $registro
     * @return array|true
     */
    private function verifica_base(string $campo, array $keys, array $registro): bool|array
    {
        $campo_r = $this->campo_existe(campo: $campo,keys_ids: $keys,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar campo ids', data: $campo_r);
        }
        if(!isset($registro[$campo_r])){
            return $this->error->error(mensaje: 'Error no existe '.$campo_r.' en registro', data: $registro);
        }

        if(!preg_match($this->patterns['id'], $registro[$campo_r])){
            return $this->error->error(mensaje: 'Error $registro['.$campo_r.'] es invalido',
                data: array($registro[$campo_r],$this->patterns['id']));
        }
        return true;
    }

    private function verifica_cod_3_mayusc(string $campo, array $keys_cod_3_mayus, array $registro): bool|array
    {

        $verifica = $this->verifica_base(campo: $campo,keys:  $keys_cod_3_mayus, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar campo', data: $verifica);
        }
        return $verifica;


    }

    /**
     * Verifica la estructura de un registro conforme a la configuracion del modelo
     * @param array $campos_obligatorios Conjunto de campos obligatorios de un modelo
     * @param array $registro Registro a verificar en conjunto de los keys id definidos
     * @param string $tabla Tabla a verificar
     * @param array $tipo_campos Campos con tipificacion desde modelo
     * @return bool|array
     */
    private function verifica_estructura(array $campos_obligatorios, array $registro, string $tabla,
                                         array $tipo_campos): bool|array
    {
        $valida_campo_obligatorio = $this->valida_campo_obligatorio(campos_obligatorios: $campos_obligatorios,
            registro: $registro,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar campos obligatorios de registro '.$tabla,
                data: $valida_campo_obligatorio);
        }

        $valida_estructura = (new val_sql())->valida_estructura_campos(registro: $registro, tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error el campo al validar estructura ', data: $valida_estructura);
        }
        return true;
    }

    /**
     * Verifica que existe un campo de tipo id en una transaccion
     * @param string $campo Campo a validar
     * @param array $keys_ids Keys a validar
     * @param array $registro Registro a verificar
     * @return bool|array
     * @version 1.439.49
     */
    private function verifica_id(string $campo, array $keys_ids, array $registro): bool|array
    {
        $verifica = $this->verifica_base(campo: $campo,keys:  $keys_ids, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar campo', data: $verifica);
        }
        return $verifica;
    }

    /**
     * REG
     * Verifica si un campo específico existe dentro de un registro.
     *
     * Esta función realiza las siguientes validaciones:
     * - Valida que el nombre del campo no esté vacío ni sea un número.
     * - Verifica si el campo existe en el array `$registro`.
     *
     * Si el campo no es válido o no existe en el registro, devuelve un error estructurado utilizando `$this->error->error()`.
     * Si todas las validaciones son exitosas, devuelve `true`.
     *
     * @param string $campo Nombre del campo a verificar.
     * @param array $registro Registro en el que se busca el campo.
     *
     * @return bool|array Retorna:
     *  - `true` si el campo existe en el registro.
     *  - Un `array` con detalles del error si el campo no existe o es inválido.
     *
     * @example
     *  Ejemplo 1: Campo existente
     *  --------------------------
     *  $registro = ['nombre' => 'Juan', 'edad' => 30];
     *  $resultado = $this->verifica_existe('nombre', $registro);
     *  // $resultado => true
     *
     * @example
     *  Ejemplo 2: Campo no existente
     *  -----------------------------
     *  $registro = ['nombre' => 'Juan', 'edad' => 30];
     *  $resultado = $this->verifica_existe('direccion', $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error $registro[direccion] debe existir',
     *  //     'data' => ['nombre' => 'Juan', 'edad' => 30],
     *  //     ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Campo inválido (vacío)
     *  ---------------------------------
     *  $registro = ['nombre' => 'Juan', 'edad' => 30];
     *  $resultado = $this->verifica_existe('', $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al limpiar campo invalido',
     *  //     'data' => '',
     *  //     ...
     *  // ]
     */
    private function verifica_existe(string $campo, array $registro): bool|array
    {
        $campo_r = $this->txt_valido(txt: $campo);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al limpiar campo invalido',
                data: $campo
            );
        }

        if (!isset($registro[$campo_r])) {
            return $this->error->error(
                mensaje: 'Error $registro[' . $campo_r . '] debe existir',
                data: $registro,
                es_final: true
            );
        }

        return true;
    }



    private function verifica_no_duplicado(modelo $modelo, array $no_duplicados, array $registro, string $tabla): bool|array
    {
        foreach($no_duplicados as $campo){

            $existe = $this->existe_duplicado(campo: $campo, modelo: $modelo,registro:  $registro,tabla:  $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar duplicado',data:  $existe);
            }

        }
        return true;
    }

    private function verifica_parent(modelo $modelo, string $parent, array $registro): bool|array
    {
        $model_parent = $modelo->genera_modelo(modelo: $parent);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data:  $model_parent);
        }

        $model_parent_id = $registro[$model_parent->key_id];

        $existe = $model_parent->existe_by_id(registro_id: $model_parent_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe row',data:  $existe);
        }

        if(!$existe){
            return $this->error->error(mensaje: 'Error al verificar parent no existe',data:  $existe);
        }
        return true;
    }

    final public function verifica_parents(modelo $modelo, array $parents, array $registro): bool|array
    {
        foreach($parents as $parent){

            $verifica_parent = $this->verifica_parent(modelo: $modelo,parent:  $parent,registro:  $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar parent',data:  $verifica_parent);
            }

        }
        return true;
    }

    /**
     * Verifica el tipo de dato de un campo este bien aplicado
     * @param string $key Key del campo a verificar
     * @param array $registro Registro a verificar
     * @param string $tipo_campo Tipo de campo a validar
     * @return bool|array
     * @version 1.291.41
     */
    private function verifica_tipo_dato(string $key, array $registro, string $tipo_campo): bool|array
    {
        $data = $this->limpia_data_tipo_campo(key: $key, tipo_campo: $tipo_campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar dato '.$key.' '.$tipo_campo,data:  $data);
        }

        $valida_campos = $this->valida_pattern_campo(key: $key, registro:  $registro, tipo_campo: $tipo_campo);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar campos', data:$valida_campos);
        }
        return true;
    }

    /**
     * Verifica si un elemento esta vacio
     * @param string $campo campo a verificar
     * @param array $keys_obligatorios Conjunto de campos obligatorios definidos en el modelo
     * @param array $registro Registro a validar
     * @return bool|array
     * @version 1.361.41
     */
    private function verifica_vacio(string $campo,array $keys_obligatorios, array $registro): bool|array
    {
        $value = $this->data_vacio(campo: $campo,keys_obligatorios: $keys_obligatorios,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al verificar si existe',data:$value);
        }
        if($value === ''){
            return $this->error->error(mensaje:'Error $registro['.$campo.'] debe tener datos',data:$registro);
        }
        return true;
    }



}