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
     * REG
     * Verifica si un campo específico dentro de un registro está vacío y lo ajusta si es necesario.
     *
     * Esta función realiza las siguientes validaciones:
     * 1. Verifica que el nombre del campo proporcionado (`$campo`) sea un texto válido mediante `txt_valido()`.
     * 2. Comprueba si el campo existe en `$registro`, utilizando `existe()`.
     * 3. Si el campo no existe en `$registro`, lo asigna con un valor vacío (`''`).
     * 4. Retorna el valor del campo después de aplicar `trim()`.
     *
     * @param string $campo Nombre del campo a validar dentro del registro.
     * @param array $keys_obligatorios Lista de claves que deben existir en `$registro`.
     * @param array $registro Registro en el que se busca el campo.
     *
     * @return array|string Retorna:
     *  - Un `string` con el valor del campo después de limpiar los espacios en blanco.
     *  - Un `array` con detalles del error si el campo es inválido o no existe en el registro.
     *
     * @throws errores Si `$campo` es inválido (vacío o numérico), si `$registro` no contiene los `keys_obligatorios`
     * o si hay problemas al validar la existencia del campo.
     *
     * @example
     *  Ejemplo 1: Campo existente y con contenido válido
     *  -------------------------------------------------
     *  $campo = 'nombre';
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => ' Juan ', 'apellido' => 'Pérez'];
     *  $resultado = $this->data_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado => 'Juan' (elimina espacios en blanco)
     *
     * @example
     *  Ejemplo 2: Campo existente pero vacío
     *  -------------------------------------
     *  $campo = 'apellido';
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Carlos', 'apellido' => '  '];
     *  $resultado = $this->data_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado => '' (cadena vacía después de trim())
     *
     * @example
     *  Ejemplo 3: Campo no existente en el registro
     *  -------------------------------------------
     *  $campo = 'edad';
     *  $keys_obligatorios = ['nombre', 'edad'];
     *  $registro = ['nombre' => 'Luis'];
     *  $resultado = $this->data_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado => '' (se asigna vacío porque no existía en el registro)
     *
     * @example
     *  Ejemplo 4: Campo no válido (vacío o numérico)
     *  ---------------------------------------------
     *  $campo = '';
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Ana', 'apellido' => 'López'];
     *  $resultado = $this->data_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al reasignar campo valor',
     *  //     'data' => ''
     *  // ]
     *
     * @example
     *  Ejemplo 5: Falta una clave obligatoria en el registro
     *  -----------------------------------------------------
     *  $campo = 'telefono';
     *  $keys_obligatorios = ['nombre', 'telefono'];
     *  $registro = ['nombre' => 'Mariana'];
     *  $resultado = $this->data_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar si existe',
     *  //     'data' => ...
     *  // ]
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


    /**
     * REG
     * Verifica si múltiples campos dentro de un registro contienen un código de 3 letras en mayúsculas.
     *
     * Esta función:
     * 1. Recorre cada campo dentro de `$keys_cod_3_mayus`.
     * 2. Llama a `verifica_cod_3_mayusc()` para validar que cada campo cumpla con el patrón `cod_3_letras_mayusc`.
     * 3. Si alguna validación falla, devuelve un error con detalles.
     * 4. Si todas las validaciones son exitosas, retorna `true`.
     *
     * @param array $keys_cod_3_mayus Lista de nombres de los campos que deben contener códigos de 3 letras mayúsculas.
     * @param array $registro Registro que contiene los datos a validar.
     *
     * @return bool|array Retorna:
     *  - `true` si todos los campos cumplen con el patrón `cod_3_letras_mayusc`.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @throws errores Si algún campo no existe en `$registro` o no cumple con el formato esperado.
     *
     * @example
     *  Ejemplo 1: Todos los campos cumplen con el patrón
     *  --------------------------------------------------
     *  ```php
     *  $this->patterns['cod_3_letras_mayusc'] = '/^[A-Z]{3}$/';
     *  $registro = ['codigo1' => 'ABC', 'codigo2' => 'XYZ'];
     *  $resultado = $this->cod_3_mayusc(['codigo1', 'codigo2'], $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Un campo no existe en el registro
     *  --------------------------------------------
     *  ```php
     *  $registro = ['codigo1' => 'ABC'];
     *  $resultado = $this->cod_3_mayusc(['codigo1', 'codigo2'], $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar campo ids',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error: El campo "codigo2" no existe en el registro',
     *  //         'data' => ['codigo1' => 'ABC']
     *  //     ]
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Un campo contiene un valor inválido
     *  ----------------------------------------------
     *  ```php
     *  $this->patterns['cod_3_letras_mayusc'] = '/^[A-Z]{3}$/';
     *  $registro = ['codigo1' => 'ABC', 'codigo2' => 'a1Z'];
     *  $resultado = $this->cod_3_mayusc(['codigo1', 'codigo2'], $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar campo ids',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error: El valor "a1Z" del campo "codigo2" no cumple con el patrón "cod_3_letras_mayusc"',
     *  //         'data' => [
     *  //             'valor' => 'a1Z',
     *  //             'patrón' => '/^[A-Z]{3}$/'
     *  //         ]
     *  //     ]
     *  // ]
     *  ```
     */
    private function cod_3_mayusc(array $keys_cod_3_mayus, array $registro ): bool|array
    {
        foreach($keys_cod_3_mayus as $campo){
            $verifica = $this->verifica_cod_3_mayusc(campo: $campo,keys_cod_3_mayus: $keys_cod_3_mayus,
                registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar campo ids',data:  $verifica);
            }

        }
        return true;
    }

    /**
     * REG
     * Verifica si un registro con un campo específico ya existe en la base de datos.
     *
     * Esta función valida si el valor de `$campo` en `$registro` ya está presente en la tabla `$tabla`
     * utilizando el modelo proporcionado. Si el campo ya existe, se devuelve un error.
     *
     * ### Pasos del proceso:
     * 1. **Valida que `$campo` no esté vacío** después de eliminar espacios en blanco.
     * 2. **Valida que `$tabla` no esté vacía** después de eliminar espacios en blanco.
     * 3. **Genera un filtro de búsqueda basado en el campo y la tabla** mediante `filtro_no_duplicado()`.
     * 4. **Consulta la base de datos** utilizando `$modelo->existe($filtro)`.
     * 5. **Si el registro ya existe, devuelve un error** para evitar duplicidad.
     *
     * @param string $campo Nombre del campo sobre el cual se verificará la duplicidad.
     * @param modelo $modelo Instancia del modelo que permite verificar la existencia del registro.
     * @param array $registro Registro que contiene los datos a validar.
     * @param string $tabla Nombre de la tabla en la que se aplicará la verificación.
     *
     * @return bool|array Retorna:
     *  - `false` si no existe un duplicado.
     *  - `array` con detalles del error si el campo es inválido, la tabla está vacía o el registro ya existe.
     *
     * @throws errores Si `$campo` o `$tabla` están vacíos, o si ocurre un problema al generar el filtro o verificar la existencia.
     *
     * @example
     *  Ejemplo 1: No hay duplicado, el campo es válido
     *  ----------------------------------------------
     *  ```php
     *  $campo = 'email';
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = 'usuarios';
     *  $modelo = new modelo();
     *  $resultado = $this->existe_duplicado($campo, $modelo, $registro, $tabla);
     *  // $resultado => false (El email no está duplicado)
     *  ```
     *
     * @example
     *  Ejemplo 2: Error por campo vacío
     *  ---------------------------------
     *  ```php
     *  $campo = '';
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = 'usuarios';
     *  $modelo = new modelo();
     *  $resultado = $this->existe_duplicado($campo, $modelo, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error campo esta vacio',
     *  //     'data' => '',
     *  //     'es_final' => true
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Error por tabla vacía
     *  ---------------------------------
     *  ```php
     *  $campo = 'email';
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = '';
     *  $modelo = new modelo();
     *  $resultado = $this->existe_duplicado($campo, $modelo, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error tabla esta vacio',
     *  //     'data' => '',
     *  //     'es_final' => true
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 4: Error por registro duplicado
     *  --------------------------------------
     *  ```php
     *  $campo = 'email';
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = 'usuarios';
     *  $modelo = new modelo();
     *
     *  // Supongamos que en la base de datos ya existe un usuario con este email
     *  $resultado = $this->existe_duplicado($campo, $modelo, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error ya existe un registro con el campo email',
     *  //     'data' => true
     *  // ]
     *  ```
     */
    private function existe_duplicado(string $campo, modelo $modelo, array $registro, string $tabla): bool|array
    {

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacio', data: $tabla, es_final: true);
        }

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

    /**
     * REG
     * Genera un filtro para evitar registros duplicados en la base de datos.
     *
     * Esta función crea un filtro basado en un campo específico dentro de una tabla,
     * asegurando que el valor correspondiente en `$registro` no se repita en la base de datos.
     *
     * ### Pasos del proceso:
     * 1. **Valida que `$campo` no esté vacío** después de eliminar espacios en blanco.
     * 2. **Valida que `$tabla` no esté vacía** después de eliminar espacios en blanco.
     * 3. **Verifica que `$campo` exista dentro de `$registro`**, usando `valida_existencia_keys()`.
     * 4. **Construye el filtro en formato `[tabla.campo => valor]`** y lo retorna.
     *
     * @param string $campo Nombre del campo sobre el cual se verificará la duplicidad.
     * @param array $registro Registro que contiene los datos a validar.
     * @param string $tabla Nombre de la tabla en la que se aplicará el filtro.
     *
     * @return array Retorna un array con la estructura del filtro en formato `[tabla.campo => valor]`.
     *  - Si ocurre un error, retorna un array con la estructura del error.
     *
     * @throws errores Si `$campo` o `$tabla` están vacíos, o si `$campo` no existe en `$registro`.
     *
     * @example
     *  Ejemplo 1: Generación exitosa del filtro
     *  ----------------------------------------
     *  ```php
     *  $campo = 'email';
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = 'usuarios';
     *  $resultado = $this->filtro_no_duplicado($campo, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'usuarios.email' => 'usuario@example.com'
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 2: Error por campo vacío
     *  ---------------------------------
     *  ```php
     *  $campo = '';
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = 'usuarios';
     *  $resultado = $this->filtro_no_duplicado($campo, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error campo esta vacio',
     *  //     'data' => ''
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Error por tabla vacía
     *  ---------------------------------
     *  ```php
     *  $campo = 'email';
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = '';
     *  $resultado = $this->filtro_no_duplicado($campo, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error tabla esta vacio',
     *  //     'data' => ''
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 4: Error por campo no existente en el registro
     *  ------------------------------------------------------
     *  ```php
     *  $campo = 'email';
     *  $registro = ['nombre' => 'Usuario'];
     *  $tabla = 'usuarios';
     *  $resultado = $this->filtro_no_duplicado($campo, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al validar registro',
     *  //     'data' => ...
     *  // ]
     *  ```
     */
    private function filtro_no_duplicado(string $campo, array $registro, string $tabla): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacio', data: $tabla, es_final: true);
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
     * REG
     * Valida que los campos obligatorios existan y no estén vacíos dentro de un registro.
     *
     * Esta función realiza dos validaciones principales:
     * 1. **Verifica la existencia de los campos** en el array `$registro` llamando a `existe()`.
     * 2. **Verifica que los campos no estén vacíos** llamando a `vacio()`.
     *
     * Si alguna de estas validaciones falla, la función devuelve un error detallado.
     *
     * @param array $keys_obligatorios Lista de claves que deben existir en `$registro` y no estar vacías.
     * @param array $registro Registro que contiene los valores a validar.
     *
     * @return bool|array Retorna:
     *  - `true` si todos los campos en `$keys_obligatorios` existen y no están vacíos en `$registro`.
     *  - Un `array` con detalles del error si algún campo falta o está vacío.
     *
     * @throws errores Si un campo en `$keys_obligatorios` no existe en `$registro` o si está vacío.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  ```php
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Juan', 'apellido' => 'Pérez'];
     *  $resultado = $this->obligatorios($keys_obligatorios, $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Falta un campo en el registro
     *  ----------------------------------------
     *  ```php
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Carlos'];
     *  $resultado = $this->obligatorios($keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al validar campos no existe',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error al verificar si existe campo',
     *  //         'data' => ['nombre' => 'Carlos']
     *  //     ]
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Campo vacío en el registro
     *  -------------------------------------
     *  ```php
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Carlos', 'apellido' => ''];
     *  $resultado = $this->obligatorios($keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al validar campo vacio',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error $registro[apellido] debe tener datos',
     *  //         'data' => ['nombre' => 'Carlos', 'apellido' => '']
     *  //     ]
     *  // ]
     *  ```
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
     * REG
     * Valida que múltiples campos en un registro cumplan con sus tipos de datos especificados.
     *
     * Esta función recorre cada campo en `$tipo_campos` y verifica que el valor en `$registro`
     * cumpla con el formato correspondiente mediante `verifica_tipo_dato`.
     *
     * ### Funcionamiento:
     * 1. **Recorre `$tipo_campos`, donde la clave es el nombre del campo y el valor es el tipo de dato esperado.**
     * 2. **Llama a `verifica_tipo_dato` para validar cada campo.**
     * 3. **Si una validación falla, devuelve un array de error detallado.**
     * 4. **Si todas las validaciones pasan, retorna `true`.**
     *
     * @param array $registro Datos a validar, donde las claves corresponden a los campos esperados.
     * @param array $tipo_campos Lista de tipos de datos esperados, donde la clave es el nombre del campo y el valor es el tipo de dato.
     *
     * @return bool|array `true` si todos los campos son válidos o un **array de error** si alguna validación falla.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $validacion = new validacion();
     * $validacion->patterns['entero'] = "/^[0-9]+$/";
     * $validacion->patterns['alfabetico'] = "/^[a-zA-Z]+$/";
     *
     * $registro = [
     *     'edad' => '25',
     *     'nombre' => 'Juan'
     * ];
     * $tipo_campos = [
     *     'edad' => 'entero',
     *     'nombre' => 'alfabetico'
     * ];
     *
     * $resultado = $validacion->tipo_campos(
     *     registro: $registro,
     *     tipo_campos: $tipo_campos
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (todos los campos cumplen con su tipo de dato)**
     * ```php
     * true
     * ```
     *
     * **Caso 2: Error (un campo no cumple con el tipo de dato esperado)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar campos edad entero"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 3: Error (falta un campo en `$registro`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar campos nombre alfabetico"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si un campo en `$tipo_campos` no existe en `$registro`,
     * si no cumple con el tipo de dato especificado o si alguna validación falla.
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
     * REG
     * Verifica que un conjunto de campos obligatorios en un registro no estén vacíos.
     *
     * Esta función realiza las siguientes validaciones:
     * 1. **Recorre cada campo en `$keys_obligatorios`** y verifica si está vacío en `$registro`.
     * 2. **Llama a `verifica_vacio()`** para validar cada campo de manera individual.
     * 3. **Si `verifica_vacio()` devuelve un error, lo propaga.**
     * 4. **Si todos los campos tienen datos válidos, retorna `true`.**
     *
     * @param array $keys_obligatorios Lista de nombres de los campos que deben existir en `$registro` y no estar vacíos.
     * @param array $registro Registro en el que se buscarán y validarán los campos.
     *
     * @return bool|array Retorna:
     *  - `true` si todos los campos tienen datos válidos.
     *  - Un `array` con detalles del error si alguno de los campos está vacío o no existe.
     *
     * @throws errores Si algún campo no existe en `$registro` o si su valor es vacío (`''` después de `trim()`).
     *
     * @example
     *  Ejemplo 1: Todos los campos son válidos
     *  --------------------------------------
     *  ```php
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Juan', 'apellido' => 'Pérez'];
     *  $resultado = $this->vacio($keys_obligatorios, $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Uno de los campos está vacío
     *  --------------------------------------
     *  ```php
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Carlos', 'apellido' => ''];
     *  $resultado = $this->vacio($keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar vacio',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error $registro[apellido] debe tener datos',
     *  //         'data' => ['nombre' => 'Carlos', 'apellido' => '']
     *  //     ]
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Falta un campo en el registro
     *  ---------------------------------------
     *  ```php
     *  $keys_obligatorios = ['nombre', 'edad'];
     *  $registro = ['nombre' => 'Luis'];
     *  $resultado = $this->vacio($keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar vacio',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error al verificar si existe',
     *  //         'data' => ...
     *  //     ]
     *  // ]
     *  ```
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
     * REG
     * Valida la estructura y consistencia de un registro antes de insertarlo en la base de datos.
     *
     * Esta función realiza una serie de validaciones en un registro antes de ser insertado en la base de datos.
     * Verifica la estructura, los campos obligatorios, la relación con padres, y evita registros duplicados.
     *
     * ### **Validaciones realizadas:**
     * 1. **Validación de alta:** Se verifica que el registro sea apto para insertarse con `valida_alta_bd()`.
     * 2. **Estructura del registro:** Se valida que los campos requeridos estén correctamente definidos en `verifica_estructura()`.
     * 3. **Verificación de relaciones padre-hijo:** Se asegura que los identificadores de los padres existan con `verifica_parents()`.
     * 4. **Verificación de duplicados:** Se revisa que no existan registros duplicados con `verifica_no_duplicado()`.
     *
     * Si alguna validación falla, se devuelve un array con detalles del error.
     *
     * @param array $campos_obligatorios Lista de campos que deben estar presentes en el registro.
     * @param modelo $modelo Instancia del modelo ORM que se usará para verificar los datos.
     * @param array $no_duplicados Lista de campos que no deben duplicarse en la base de datos.
     * @param array $registro Datos del registro que se desea insertar.
     * @param string $tabla Nombre de la tabla en la que se insertará el registro.
     * @param array $tipo_campos Definición de tipos de datos esperados para cada campo.
     * @param array $parents Lista de claves que representan relaciones con otras tablas.
     *
     * @return bool|array Retorna:
     *  - `true` si todas las validaciones son exitosas.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @throws errores Si alguno de los pasos de validación detecta inconsistencias.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $registro = [
     *      'nombre' => 'Juan',
     *      'edad' => 30,
     *      'usuario_id' => 5
     *  ];
     *  $campos_obligatorios = ['nombre', 'edad', 'usuario_id'];
     *  $no_duplicados = ['nombre'];
     *  $tipo_campos = ['nombre' => 'string', 'edad' => 'entero'];
     *  $tabla = 'usuarios';
     *  $parents = ['usuario'];
     *
     *  $resultado = $this->valida_base_alta(
     *      campos_obligatorios: $campos_obligatorios,
     *      modelo: $modelo,
     *      no_duplicados: $no_duplicados,
     *      registro: $registro,
     *      tabla: $tabla,
     *      tipo_campos: $tipo_campos,
     *      parents: $parents
     *  );
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Error por campo faltante
     *  -----------------------------------
     *  ```php
     *  $registro = [
     *      'nombre' => 'Carlos',
     *      'usuario_id' => 3
     *  ];
     *  $resultado = $this->valida_base_alta(
     *      campos_obligatorios: ['nombre', 'edad', 'usuario_id'],
     *      modelo: $modelo,
     *      no_duplicados: ['nombre'],
     *      registro: $registro,
     *      tabla: 'usuarios',
     *      tipo_campos: ['nombre' => 'string', 'edad' => 'entero'],
     *      parents: ['usuario']
     *  );
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error el campo al validar estructura',
     *  //     'data' => [...]
     *  // ]
     *  ```
     */
    final public function valida_base_alta(
        array $campos_obligatorios, modelo $modelo, array $no_duplicados, array $registro, string $tabla,
        array $tipo_campos, array $parents): bool|array
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
     * REG
     * Valida la estructura de un registro asegurando que cumpla con diferentes reglas de validación.
     *
     * Esta función:
     * 1. **Verifica los tipos de datos** en `$registro` usando `$tipo_campos`.
     * 2. **Valida que los campos obligatorios** en `$keys_obligatorios` existan y no estén vacíos.
     * 3. **Comprueba los campos de tipo ID** en `$keys_ids` para asegurar que sean números enteros válidos.
     * 4. **Revisa los campos booleanos (`checked`)** en `$keys_checked`, asegurando que solo contengan 'activo' o 'inactivo'.
     * 5. **Valida los códigos de 3 letras mayúsculas** en `$keys_cod_3_mayus` según un patrón predefinido.
     *
     * Si alguna validación falla, se devuelve un error con los detalles específicos. Si todas las validaciones pasan, retorna `true`.
     *
     * @param array $registro Registro de datos que será validado.
     * @param array $tipo_campos Lista de tipos de datos esperados por campo.
     * @param array $keys_checked Lista de campos que deben tener valores `'activo'` o `'inactivo'`.
     * @param array $keys_cod_3_mayus Lista de campos que deben contener códigos de 3 letras en mayúsculas.
     * @param array $keys_ids Lista de campos que deben ser identificadores numéricos válidos.
     * @param array $keys_obligatorios Lista de campos obligatorios que deben existir y no estar vacíos en `$registro`.
     *
     * @return bool|array Retorna:
     *  - `true` si todas las validaciones son exitosas.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @throws errores Si algún campo no cumple con las validaciones establecidas.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  ```php
     *  $registro = [
     *      'nombre' => 'Juan',
     *      'edad' => 30,
     *      'estado' => 'activo',
     *      'codigo' => 'ABC',
     *      'id_usuario' => 5
     *  ];
     *  $tipo_campos = ['nombre' => 'string', 'edad' => 'entero'];
     *  $keys_checked = ['estado'];
     *  $keys_cod_3_mayus = ['codigo'];
     *  $keys_ids = ['id_usuario'];
     *  $keys_obligatorios = ['nombre', 'edad', 'estado', 'codigo', 'id_usuario'];
     *  $resultado = $this->valida_estructura_campos($registro, $tipo_campos, $keys_checked, $keys_cod_3_mayus, $keys_ids, $keys_obligatorios);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Error por campo obligatorio faltante
     *  -----------------------------------------------
     *  ```php
     *  $registro = [
     *      'nombre' => 'Carlos',
     *      'edad' => 25,
     *      'codigo' => 'XYZ'
     *  ];
     *  $keys_obligatorios = ['nombre', 'edad', 'estado'];
     *  $resultado = $this->valida_estructura_campos($registro, [], [], [], [], $keys_obligatorios);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al validar tipo de campo',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error: El campo "estado" es obligatorio y falta en el registro',
     *  //         'data' => ['nombre' => 'Carlos', 'edad' => 25, 'codigo' => 'XYZ']
     *  //     ]
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Error por código de 3 letras inválido
     *  -----------------------------------------------
     *  ```php
     *  $registro = [
     *      'codigo' => 'a1Z'
     *  ];
     *  $keys_cod_3_mayus = ['codigo'];
     *  $resultado = $this->valida_estructura_campos($registro, [], [], $keys_cod_3_mayus, [], []);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al validar cod_3_mayusc',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error: El valor "a1Z" del campo "codigo" no cumple con el patrón "cod_3_letras_mayusc"',
     *  //         'data' => [
     *  //             'valor' => 'a1Z',
     *  //             'patrón' => '/^[A-Z]{3}$/'
     *  //         ]
     *  //     ]
     *  // ]
     *  ```
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
     * REG
     * Verifica si un campo dentro de un registro cumple con un patrón de validación.
     *
     * Esta función realiza las siguientes validaciones:
     * 1. Verifica que `$pattern_rev` no esté vacío.
     * 2. Verifica si `$campo` existe en el registro mediante `campo_existe()`.
     * 3. Verifica si `$campo` está presente en `$registro`.
     * 4. Valida que `$pattern_rev` esté definido en `$this->patterns`.
     * 5. Aplica la expresión regular almacenada en `$this->patterns[$pattern_rev]`
     *    para verificar si el valor del campo cumple con el formato esperado.
     *
     * @param string $campo Nombre del campo a validar dentro del registro.
     * @param array $keys Lista de claves esperadas en el registro.
     * @param string $pattern_rev Clave del patrón de validación dentro de `$this->patterns`.
     * @param array $registro Registro que contiene los datos a validar.
     *
     * @return bool|array Retorna:
     *  - `true` si el campo existe y su valor cumple con el patrón de validación.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @throws errores Si `$campo` no existe en `$registro`, si `$pattern_rev` está vacío o no definido en `$this->patterns`,
     * o si el valor del campo no coincide con el patrón esperado.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  ------------------------------
     *  ```php
     *  $this->patterns['entero'] = '/^[0-9]+$/';
     *  $registro = ['edad' => '25'];
     *  $resultado = $this->verifica_base('edad', ['edad'], 'entero', $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Campo inexistente en el registro
     *  -------------------------------------------
     *  ```php
     *  $registro = ['nombre' => 'Juan'];
     *  $resultado = $this->verifica_base('edad', ['edad'], 'entero', $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => "Error: El campo 'edad' no existe en el registro",
     *  //     'data' => ['nombre' => 'Juan']
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Valor inválido según el patrón
     *  -----------------------------------------
     *  ```php
     *  $this->patterns['entero'] = '/^[0-9]+$/';
     *  $registro = ['edad' => 'abc'];
     *  $resultado = $this->verifica_base('edad', ['edad'], 'entero', $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => "Error: El valor 'abc' del campo 'edad' no cumple con el patrón 'entero'",
     *  //     'data' => [
     *  //         'valor' => 'abc',
     *  //         'patrón' => '/^[0-9]+$/'
     *  //     ]
     *  // ]
     *  ```
     */
    private function verifica_base(string $campo, array $keys, string $pattern_rev, array $registro ): bool|array
    {
        $pattern_rev = trim($pattern_rev);
        if($pattern_rev === ''){
            return $this->error->error(mensaje: 'Error $pattern_rev esta vacio', data: $pattern_rev, es_final: true);
        }
        $campo_r = $this->campo_existe(campo: $campo,keys_ids: $keys,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar campo ids', data: $campo_r);
        }
        if(!isset($registro[$campo_r])){
            return $this->error->error(mensaje: 'Error no existe '.$campo_r.' en registro', data: $registro);
        }

        if(!preg_match($this->patterns[$pattern_rev], $registro[$campo_r])){
            return $this->error->error(mensaje: 'Error $registro['.$campo_r.'] es invalido',
                data: array($registro[$campo_r],$this->patterns[$pattern_rev]));
        }
        return true;
    }


    /**
     * REG
     * Verifica si un campo dentro de un registro contiene un código de 3 letras en mayúsculas.
     *
     * Esta función:
     * 1. Llama a `verifica_base()` para validar que el campo cumpla con el patrón `cod_3_letras_mayusc`.
     * 2. Si la validación falla, devuelve un error con un mensaje descriptivo.
     * 3. Si la validación es exitosa, retorna `true`.
     *
     * @param string $campo Nombre del campo a validar dentro del registro.
     * @param array $keys_cod_3_mayus Lista de claves esperadas en el registro.
     * @param array $registro Registro que contiene los datos a validar.
     *
     * @return bool|array Retorna:
     *  - `true` si el campo existe y su valor cumple con el patrón `cod_3_letras_mayusc`.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @throws errores Si `$campo` no existe en `$registro`, si el patrón no está definido en `$this->patterns`,
     * o si el valor del campo no coincide con el formato esperado.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  ------------------------------
     *  ```php
     *  $this->patterns['cod_3_letras_mayusc'] = '/^[A-Z]{3}$/';
     *  $registro = ['codigo' => 'ABC'];
     *  $resultado = $this->verifica_cod_3_mayusc('codigo', ['codigo'], $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Campo inexistente en el registro
     *  -------------------------------------------
     *  ```php
     *  $registro = ['nombre' => 'Juan'];
     *  $resultado = $this->verifica_cod_3_mayusc('codigo', ['codigo'], $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => "Error: El campo 'codigo' no existe en el registro",
     *  //     'data' => ['nombre' => 'Juan']
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Código con caracteres inválidos
     *  -----------------------------------------
     *  ```php
     *  $this->patterns['cod_3_letras_mayusc'] = '/^[A-Z]{3}$/';
     *  $registro = ['codigo' => 'Ab1'];
     *  $resultado = $this->verifica_cod_3_mayusc('codigo', ['codigo'], $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => "Error: El valor 'Ab1' del campo 'codigo' no cumple con el patrón 'cod_3_letras_mayusc'",
     *  //     'data' => [
     *  //         'valor' => 'Ab1',
     *  //         'patrón' => '/^[A-Z]{3}$/'
     *  //     ]
     *  // ]
     *  ```
     */
    private function verifica_cod_3_mayusc(string $campo, array $keys_cod_3_mayus, array $registro): bool|array
    {

        $verifica = $this->verifica_base(campo: $campo,keys:  $keys_cod_3_mayus,pattern_rev: 'cod_3_letras_mayusc',
            registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar campo', data: $verifica);
        }
        return $verifica;


    }


    /**
     * REG
     * Verifica la estructura de un registro validando los campos obligatorios y la estructura general.
     *
     * Esta función realiza dos validaciones principales:
     * 1. **Valida la existencia de los campos obligatorios** en `$registro` asegurando que estén presentes.
     * 2. **Verifica que la estructura general del registro sea válida** llamando a `valida_estructura_campos()`.
     *
     * Si alguna de estas validaciones falla, se devuelve un error con los detalles específicos. Si todas las validaciones pasan, retorna `true`.
     *
     * @param array $campos_obligatorios Lista de campos obligatorios que deben existir en `$registro`.
     * @param array $registro Registro de datos que será validado.
     * @param string $tabla Nombre de la tabla a la que pertenece el registro.
     * @param array $tipo_campos Lista de tipos de datos esperados por campo.
     *
     * @return bool|array Retorna:
     *  - `true` si todas las validaciones son exitosas.
     *  - Un `array` con detalles del error si alguna validación falla.
     *
     * @throws errores Si algún campo no cumple con las validaciones establecidas.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  ```php
     *  $registro = [
     *      'nombre' => 'Juan',
     *      'edad' => 30,
     *      'estado' => 'activo'
     *  ];
     *  $campos_obligatorios = ['nombre', 'edad', 'estado'];
     *  $tipo_campos = ['nombre' => 'string', 'edad' => 'entero'];
     *  $tabla = 'usuarios';
     *  $resultado = $this->verifica_estructura($campos_obligatorios, $registro, $tabla, $tipo_campos);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Error por campo obligatorio faltante
     *  -----------------------------------------------
     *  ```php
     *  $registro = [
     *      'nombre' => 'Carlos',
     *      'edad' => 25
     *  ];
     *  $campos_obligatorios = ['nombre', 'edad', 'estado'];
     *  $tipo_campos = ['nombre' => 'string', 'edad' => 'entero'];
     *  $tabla = 'usuarios';
     *  $resultado = $this->verifica_estructura($campos_obligatorios, $registro, $tabla, $tipo_campos);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error el campo al validar campos obligatorios de registro usuarios',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error: El campo "estado" es obligatorio y falta en el registro',
     *  //         'data' => ['nombre' => 'Carlos', 'edad' => 25]
     *  //     ]
     *  // ]
     *  ```
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
        $verifica = $this->verifica_base(campo: $campo,keys:  $keys_ids,pattern_rev: 'id', registro: $registro);
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


    /**
     * REG
     * Verifica que un conjunto de campos no tengan valores duplicados en la base de datos.
     *
     * Esta función revisa cada campo en `$no_duplicados` y valida que su valor en `$registro`
     * no esté repetido en la tabla `$tabla` usando el modelo proporcionado.
     *
     * ### Pasos del proceso:
     * 1. **Recorre cada campo en `$no_duplicados` y lo valida:**
     *    - Verifica que `$campo` no esté vacío.
     *    - Verifica que `$tabla` no esté vacía.
     * 2. **Para cada campo, genera un filtro de búsqueda y consulta la base de datos** utilizando `existe_duplicado()`.
     * 3. **Si encuentra duplicados, devuelve un error** con detalles del campo duplicado.
     * 4. **Si no hay duplicados en ninguno de los campos, devuelve `true`.**
     *
     * @param modelo $modelo Instancia del modelo que permite verificar la existencia del registro.
     * @param array $no_duplicados Lista de nombres de campos que deben ser únicos.
     * @param array $registro Registro que contiene los valores a validar.
     * @param string $tabla Nombre de la tabla en la que se aplicará la verificación.
     *
     * @return bool|array Retorna:
     *  - `true` si todos los campos en `$no_duplicados` son únicos en la base de datos.
     *  - `array` con detalles del error si un campo está vacío, la tabla está vacía o si algún valor ya existe.
     *
     * @throws errores Si `$campo` o `$tabla` están vacíos, o si ocurre un problema al verificar duplicados.
     *
     * @example
     *  Ejemplo 1: No hay duplicados, los campos son válidos
     *  ----------------------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $no_duplicados = ['email', 'username'];
     *  $registro = ['email' => 'usuario@example.com', 'username' => 'usuario123'];
     *  $tabla = 'usuarios';
     *
     *  $resultado = $this->verifica_no_duplicado($modelo, $no_duplicados, $registro, $tabla);
     *  // $resultado => true (No hay valores duplicados)
     *  ```
     *
     * @example
     *  Ejemplo 2: Error por campo vacío
     *  ---------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $no_duplicados = [''];
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = 'usuarios';
     *
     *  $resultado = $this->verifica_no_duplicado($modelo, $no_duplicados, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error campo esta vacio',
     *  //     'data' => '',
     *  //     'es_final' => true
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Error por tabla vacía
     *  ---------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $no_duplicados = ['email'];
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = '';
     *
     *  $resultado = $this->verifica_no_duplicado($modelo, $no_duplicados, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error tabla esta vacio',
     *  //     'data' => '',
     *  //     'es_final' => true
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 4: Error por campo duplicado
     *  ------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $no_duplicados = ['email'];
     *  $registro = ['email' => 'usuario@example.com'];
     *  $tabla = 'usuarios';
     *
     *  // Supongamos que en la base de datos ya existe un usuario con este email
     *  $resultado = $this->verifica_no_duplicado($modelo, $no_duplicados, $registro, $tabla);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar duplicado',
     *  //     'data' => [
     *  //         'error' => 1,
     *  //         'mensaje' => 'Error ya existe un registro con el campo email',
     *  //         'data' => true
     *  //     ]
     *  // ]
     *  ```
     */
    private function verifica_no_duplicado(
        modelo $modelo, array $no_duplicados, array $registro, string $tabla): bool|array
    {
        foreach($no_duplicados as $campo){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
            }
            $tabla = trim($tabla);
            if($tabla === ''){
                return $this->error->error(mensaje: 'Error tabla esta vacio', data: $tabla, es_final: true);
            }

            $existe = $this->existe_duplicado(campo: $campo, modelo: $modelo,registro:  $registro,tabla:  $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar duplicado',data:  $existe);
            }

        }
        return true;
    }

    /**
     * REG
     * Verifica si un registro tiene un parent válido en la base de datos.
     *
     * Esta función realiza los siguientes pasos:
     * 1. Genera un modelo basado en el nombre del parent proporcionado.
     * 2. Verifica que el modelo se haya generado correctamente.
     * 3. Comprueba si el identificador del parent existe en `$registro`.
     * 4. Valida que el ID del parent exista en la base de datos llamando a `existe_by_id()`.
     *
     * @param modelo $modelo Instancia del modelo que se está validando.
     * @param string $parent Nombre del modelo del parent que se debe verificar.
     * @param array $registro Registro que contiene los datos a validar.
     *
     * @return bool|array Retorna:
     *  - `true` si el parent es válido y existe en la base de datos.
     *  - Un `array` de error si hay algún problema en la validación.
     *
     * @throws errores Si ocurre algún error en la generación del modelo, la validación del ID o la existencia del registro.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  -----------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $parent = 'usuario';
     *  $registro = ['usuario_id' => 5];
     *  $resultado = $this->verifica_parent($modelo, $parent, $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Error al generar el modelo del parent
     *  ------------------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $parent = 'cliente';
     *  $registro = ['cliente_id' => 3];
     *  $resultado = $this->verifica_parent($modelo, $parent, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al generar modelo',
     *  //     'data' => null
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Error porque el campo ID del parent no existe en el registro
     *  ----------------------------------------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $parent = 'producto';
     *  $registro = ['nombre' => 'Laptop'];
     *  $resultado = $this->verifica_parent($modelo, $parent, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error registro[producto_id] no existe',
     *  //     'data' => ...
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 4: Error porque el ID del parent no existe en la base de datos
     *  ----------------------------------------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $parent = 'categoria';
     *  $registro = ['categoria_id' => 999]; // ID inexistente en BD
     *  $resultado = $this->verifica_parent($modelo, $parent, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar parent no existe',
     *  //     'data' => false
     *  // ]
     *  ```
     */
    private function verifica_parent(modelo $modelo, string $parent, array $registro): bool|array
    {
        $model_parent = $modelo->genera_modelo(modelo: $parent);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data:  $model_parent);
        }

        if(!isset($registro[$model_parent->key_id])){
            return $this->error->error(mensaje: 'Error registro[$model_parent->key_id] no existe',
                data:  $model_parent);
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


    /**
     * REG
     * Verifica que todos los parents especificados en `$parents` existan en la base de datos.
     *
     * Esta función recorre la lista de parents y valida que cada uno de ellos exista en la base de datos,
     * utilizando la función `verifica_parent()`. Si algún parent no es válido, devuelve un error detallado.
     *
     * ### Pasos del proceso:
     * 1. **Itera sobre cada parent en `$parents`**.
     * 2. **Llama a `verifica_parent()`** para verificar que el parent existe en la base de datos.
     * 3. **Si `verifica_parent()` devuelve un error, lo propaga inmediatamente.**
     * 4. **Si todos los parents son válidos, retorna `true`.**
     *
     * @param modelo $modelo Instancia del modelo en el que se realizará la validación.
     * @param array $parents Lista de nombres de modelos de parents a verificar.
     * @param array $registro Registro que contiene los datos a validar.
     *
     * @return bool|array Retorna:
     *  - `true` si todos los parents existen en la base de datos.
     *  - Un `array` de error si alguno de los parents no existe o si hay problemas en la validación.
     *
     * @throws errores Si ocurre algún error en la validación de los parents o si algún parent no existe.
     *
     * @example
     *  Ejemplo 1: Validación exitosa con múltiples parents
     *  --------------------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $parents = ['usuario', 'cliente'];
     *  $registro = ['usuario_id' => 5, 'cliente_id' => 10];
     *  $resultado = $this->verifica_parents($modelo, $parents, $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Error porque un parent no existe en el registro
     *  ----------------------------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $parents = ['usuario', 'producto'];
     *  $registro = ['usuario_id' => 3]; // Falta 'producto_id'
     *  $resultado = $this->verifica_parents($modelo, $parents, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error registro[producto_id] no existe',
     *  //     'data' => ...
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Error porque un parent no existe en la base de datos
     *  ----------------------------------------------------------------
     *  ```php
     *  $modelo = new modelo();
     *  $parents = ['usuario', 'categoria'];
     *  $registro = ['usuario_id' => 5, 'categoria_id' => 999]; // ID inexistente en BD
     *  $resultado = $this->verifica_parents($modelo, $parents, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar parent no existe',
     *  //     'data' => false
     *  // ]
     *  ```
     */
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
     * REG
     * Verifica si un campo en un registro cumple con un tipo de dato específico.
     *
     * Esta función valida que un campo dentro de `$registro` siga un formato adecuado según el tipo de dato `$tipo_campo`.
     *
     * ### Funcionamiento:
     * 1. **Limpia y ajusta el dato según el tipo especificado utilizando `limpia_data_tipo_campo`.**
     * 2. **Verifica que el campo siga el patrón adecuado llamando a `valida_pattern_campo`.**
     * 3. **Si todas las validaciones pasan, retorna `true`.**
     * 4. **Si alguna validación falla, devuelve un array de error detallado.**
     *
     * @param string $key Nombre del campo dentro de `$registro` que debe validarse.
     * @param array $registro Datos a validar, donde `$key` debe estar presente.
     * @param string $tipo_campo Clave del patrón en `$this->patterns` con el cual se debe validar el valor.
     *
     * @return bool|array `true` si el campo es válido o un **array de error** si alguna validación falla.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $validacion = new validacion();
     * $validacion->patterns['entero'] = "/^[0-9]+$/"; // Definiendo un patrón para números enteros
     *
     * $registro = ['edad' => '25'];
     * $resultado = $validacion->verifica_tipo_dato(
     *     key: 'edad',
     *     registro: $registro,
     *     tipo_campo: 'entero'
     * );
     * print_r($resultado);
     * ```
     *
     * ### **Posibles salidas:**
     * **Caso 1: Éxito (el campo cumple con el tipo de dato esperado)**
     * ```php
     * true
     * ```
     *
     * **Caso 2: Error (problema al limpiar el dato)**
     * ```php
     * Array
     * (
     *     [error] => "Error al limpiar dato edad entero"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 3: Error (el campo no cumple con el patrón especificado en `$tipo_campo`)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar campos"
     *     [data] => false
     * )
     * ```
     *
     * @throws errores Si `$key` no existe en `$registro`, si `$tipo_campo` no tiene un patrón registrado
     * o si el valor del campo no cumple con el formato esperado.
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
     * REG
     * Verifica si un campo en un registro está vacío y genera un error si no contiene datos válidos.
     *
     * Esta función realiza las siguientes validaciones:
     * 1. **Llama a `data_vacio()`** para verificar si el campo existe y obtener su valor.
     * 2. **Si `data_vacio()` devuelve un error, lo propaga.**
     * 3. **Si el campo está vacío (`''` después de `trim()`), devuelve un error.**
     * 4. **Si todo está correcto, retorna `true`.**
     *
     * @param string $campo Nombre del campo a validar dentro del registro.
     * @param array $keys_obligatorios Lista de claves que deben existir en `$registro`.
     * @param array $registro Registro en el que se busca el campo.
     *
     * @return bool|array Retorna:
     *  - `true` si el campo tiene datos válidos.
     *  - Un `array` con detalles del error si el campo no existe o está vacío.
     *
     * @throws errores Si `$campo` es inválido, si `$registro` no contiene los `keys_obligatorios`
     * o si el valor del campo está vacío.
     *
     * @example
     *  Ejemplo 1: Campo válido con datos
     *  ---------------------------------
     *  ```php
     *  $campo = 'nombre';
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => ' Juan ', 'apellido' => 'Pérez'];
     *  $resultado = $this->verifica_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado => true
     *  ```
     *
     * @example
     *  Ejemplo 2: Campo vacío después de `trim()`
     *  -----------------------------------------
     *  ```php
     *  $campo = 'apellido';
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Carlos', 'apellido' => '  '];
     *  $resultado = $this->verifica_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error $registro[apellido] debe tener datos',
     *  //     'data' => ['nombre' => 'Carlos', 'apellido' => '  ']
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 3: Campo no existente en el registro
     *  -------------------------------------------
     *  ```php
     *  $campo = 'edad';
     *  $keys_obligatorios = ['nombre', 'edad'];
     *  $registro = ['nombre' => 'Luis'];
     *  $resultado = $this->verifica_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar si existe',
     *  //     'data' => ...
     *  // ]
     *  ```
     *
     * @example
     *  Ejemplo 4: Campo inválido (vacío o numérico)
     *  ---------------------------------------------
     *  ```php
     *  $campo = '';
     *  $keys_obligatorios = ['nombre', 'apellido'];
     *  $registro = ['nombre' => 'Ana', 'apellido' => 'López'];
     *  $resultado = $this->verifica_vacio($campo, $keys_obligatorios, $registro);
     *  // $resultado =>
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error al verificar si existe',
     *  //     'data' => ...
     *  // ]
     *  ```
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