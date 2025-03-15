<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\administrador\models\adm_atributo;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use PDO;

class atributos{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * REG
     * Obtiene los atributos de una tabla específica desde la base de datos.
     *
     * Esta función consulta los atributos asociados a una tabla en la base de datos mediante el modelo `adm_atributo`.
     * Si el nombre de la tabla está vacío o hay un error en la consulta, devuelve un array con información del error.
     *
     * @param PDO $link Conexión a la base de datos utilizada para realizar la consulta.
     * @param string $tabla Nombre de la tabla de la cual se desean obtener los atributos.
     *                      No debe estar vacío.
     *
     * @return array Retorna un array con los atributos de la tabla si la consulta es exitosa.
     *               En caso de error, devuelve un array de error con el mensaje correspondiente.
     *
     * @throws errores Si la tabla está vacía, devuelve un error con el mensaje `Error this->tabla esta vacia`.
     *                 Si la consulta falla, devuelve un error con el mensaje `Error al obtener atributos`.
     *
     * @example
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=testdb', 'user', 'password');
     *
     * $resultado = atributos($pdo, 'usuario');
     * // Salida esperada:
     * // [
     * //   ['id' => 1, 'descripcion' => 'Nombre'],
     * //   ['id' => 2, 'descripcion' => 'Correo'],
     * //   ['id' => 3, 'descripcion' => 'Teléfono']
     * // ]
     *
     * $resultado = atributos($pdo, '');
     * // Salida esperada:
     * // ['error' => true, 'mensaje' => 'Error this->tabla esta vacia', 'data' => '']
     *
     * $resultado = atributos($pdo, 'tabla_inexistente');
     * // Salida esperada (si la tabla no tiene atributos o hay error en la consulta):
     * // ['error' => true, 'mensaje' => 'Error al obtener atributos', 'data' => [...]]
     * ```
     */
    private function atributos(PDO $link, string $tabla): array
    {
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla esta vacia',data:  $tabla, es_final: true);
        }
        $modelo_atributo = new adm_atributo($link);
        $filtro['adm_seccion.descripcion'] = $tabla;
        $r_atributo = $modelo_atributo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos', data: $r_atributo);
        }
        return $r_atributo->registros;
    }

    /**
     * REG
     * Genera el nombre de la clase de atributos basado en el nombre de la tabla proporcionada.
     *
     * Esta función toma un nombre de tabla como entrada, lo limpia y le agrega un prefijo
     * para generar el nombre de una clase de atributos. Si el nombre de la tabla está vacío,
     * devuelve un error estructurado.
     *
     * @param string $tabla Nombre de la tabla de la cual se generará la clase de atributos.
     *                      Debe ser una cadena válida y no vacía.
     *
     * @return string|array Retorna el nombre de la clase de atributos con prefijo `models\attr_`
     *                      o un array de error si la tabla está vacía.
     *
     * @throws errores Si el nombre de la tabla es una cadena vacía, devuelve un array de error con el mensaje.
     *
     * @example
     * ```php
     * $resultado = class_attr('usuario');
     * // Salida: 'models\attr_usuario'
     *
     * $resultado = class_attr('producto');
     * // Salida: 'models\attr_producto'
     *
     * $resultado = class_attr('models\cliente');
     * // Salida: 'models\attr_cliente' (Elimina el prefijo 'models\')
     *
     * $resultado = class_attr('');
     * // Salida: ['error' => true, 'mensaje' => 'Error tabla vacía', 'data' => '']
     * ```
     */
    private function class_attr(string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia',data: $tabla, es_final: true);
        }
        $namespace = 'models\\';
        $clase_attr = str_replace($namespace,'',$tabla);
        return 'models\\attr_'.$clase_attr;
    }

    /**
     * REG
     * Genera los datos de inserción para un atributo en la base de datos.
     *
     * Esta función valida el atributo recibido y estructura los datos necesarios para su inserción en la base de datos.
     * Se asegura de que los valores requeridos estén presentes y de que el identificador del registro sea válido.
     *
     * @param array $atributo Arreglo asociativo con los datos del atributo a insertar.
     *                        Debe contener las claves `adm_atributo_descripcion` y `adm_atributo_id`.
     * @param modelo $modelo Instancia del modelo que representa la tabla en la que se insertará el atributo.
     * @param int $registro_id Identificador del registro en la tabla correspondiente. Debe ser un entero positivo mayor a 0.
     *
     * @return array Devuelve un arreglo con los datos estructurados para la inserción del atributo.
     *               En caso de error, devuelve un arreglo con la información del error.
     *
     * @throws errores Si los datos del atributo no son válidos o si el modelo no tiene una tabla definida.
     *
     * @example
     * ```php
     * $atributo = [
     *     'adm_atributo_descripcion' => 'Color',
     *     'adm_atributo_id' => 5
     * ];
     * $modelo = new modelo();
     * $modelo->tabla = 'productos';
     * $registro_id = 10;
     *
     * $resultado = data_inst_attr($atributo, $modelo, $registro_id);
     * // Salida esperada:
     * // [
     * //     'descripcion' => 'Color',
     * //     'status' => 'activo',
     * //     'adm_atributo_id' => 5,
     * //     'productos_id' => 10,
     * //     'valor' => ''
     * // ]
     *
     * $atributo_invalido = [
     *     'adm_atributo_descripcion' => 'Tamaño'
     * ];
     * $resultado = data_inst_attr($atributo_invalido, $modelo, $registro_id);
     * // Salida esperada:
     * // ['error' => true, 'mensaje' => 'Error al validar $atributo', 'data' => [...]]
     *
     * $modelo->tabla = '';
     * $resultado = data_inst_attr($atributo, $modelo, $registro_id);
     * // Salida esperada:
     * // ['error' => true, 'mensaje' => 'Error $this->tabla esta vacia', 'data' => '']
     * ```
     */
    private function data_inst_attr(array $atributo, modelo $modelo, int $registro_id): array
    {
        $keys = array('adm_atributo_descripcion','adm_atributo_id');
        $valida = $this->valida_attr(atributo: $atributo,keys:  $keys, registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }
        $modelo->tabla = trim($modelo->tabla);
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error $this->tabla esta vacia',data: $modelo->tabla, es_final: true);
        }

        $data_ins['descripcion'] = $atributo['adm_atributo_descripcion'];
        $data_ins['status'] = 'activo';
        $data_ins['adm_atributo_id'] = $atributo['adm_atributo_id'];
        $data_ins[$modelo->tabla.'_id'] = $registro_id;
        $data_ins['valor'] = '';
        return $data_ins;
    }

    /**
     * Ejecuta la aplicacion de atributos
     * @param modelo $modelo Modelo en ejecucion
     * @param int $registro_id Identificador de la tabla u objeto de tipo modelo un entero positivo mayor a 0
     * @return array|string
     */
    final public function ejecuta_insersion_attr(modelo $modelo, int $registro_id): array|string
    {
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id,
                es_final: true);
        }

        $clase_attr = $this->class_attr(tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener class', data: $clase_attr);
        }
        if(class_exists($clase_attr)){

            $r_ins = $this->inserta_data_attr(clase_attr: $clase_attr, modelo: $modelo, registro_id: $registro_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al insertar atributos',data:  $r_ins);
            }
        }
        return $clase_attr;
    }

    /**
     * Inserta un atributo
     * @param array $atributo Registro de tipo modelo atributo
     * @param modelo $modelo_base modelo a integrar
     * @param int $registro_id Identificador
     * @param string $tabla Tabla modelo
     * @return array
     */
    private function inserta_atributo(array $atributo, modelo $modelo_base, int $registro_id, string $tabla): array
    {
        $keys = array('adm_atributo_descripcion','adm_atributo_descripcion');
        $valida = $this->valida_attr(atributo: $atributo,keys:  $keys, registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }

        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error la tabla esta vacia',data: $tabla, es_final: true);
        }


        $data_ins = $this->data_inst_attr(atributo: $atributo, modelo: $modelo_base,registro_id:  $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar atributos', data: $data_ins);
        }

        $modelo = $modelo_base->genera_modelo(modelo: $tabla, namespace_model: $modelo_base->NAMESPACE);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data:  $modelo);
        }

        $r_ins = $modelo->alta_registro(registro: $data_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar atributos',data:  $r_ins);
        }
        return $r_ins;
    }

    /**
     * Inserta atributos
     * @param modelo $modelo Modelo en ejecucion
     * @param int $registro_id Identificador de la tabla u objeto de tipo modelo un entero positivo mayor a 0
     * @param string $tabla_attr Tabla de atributo
     * @return array
     */
    private function inserta_atributos(modelo $modelo, int $registro_id, string $tabla_attr): array
    {
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla esta vacia',data:  $modelo->tabla, es_final: true);
        }

        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $registro_id,
                es_final: true);
        }
        if($tabla_attr === ''){
            return $this->error->error(mensaje: 'Error tabla_attr esta vacia',data:  $tabla_attr, es_final: true);
        }


        $atributos = $this->atributos(link:$modelo->link, tabla: $tabla_attr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos', data: $atributos);
        }

        foreach($atributos as $atributo){
            $r_ins = $this->inserta_atributo(atributo: $atributo, modelo_base: $modelo,
                registro_id:  $registro_id,tabla:  $tabla_attr);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al insertar atributos', data: $r_ins);
            }
        }
        return $atributos;
    }


    /**
     * Inserta aun atributo
     * @param string $clase_attr Clase de atributo
     * @param modelo $modelo Modelo en ejecucion
     * @param int $registro_id Identificador de la tabla u objeto de tipo modelo un entero positivo mayor a 0
     * @return array
     */
    private function inserta_data_attr(
        string $clase_attr,modelo $modelo, int $registro_id): array
    {
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id,
                es_final: true);
        }
        $clase_attr = trim($clase_attr);
        if($clase_attr === ''){
            return $this->error->error(mensaje: 'Error clase_attr esta vacia', data: $clase_attr, es_final: true);
        }

        $model_attr = $modelo->genera_modelo(modelo: $clase_attr, namespace_model: $modelo->NAMESPACE);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data:  $model_attr);
        }

        $r_ins = $this->inserta_atributos(modelo:$modelo, registro_id:  $registro_id, tabla_attr:  $model_attr->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar atributos', data: $r_ins);
        }
        return $r_ins;
    }

    /**
     * REG
     * Valida que un atributo cumpla con los requisitos básicos antes de ser procesado.
     *
     * Esta función verifica que el atributo tenga las claves necesarias (`$keys`),
     * que el identificador (`adm_atributo_id`) sea válido y que el `registro_id` sea un entero positivo mayor a 0.
     *
     * @param array $atributo Arreglo asociativo que representa un atributo a validar.
     *                        Debe contener las claves definidas en `$keys` y un `adm_atributo_id` válido.
     * @param array $keys Lista de claves obligatorias que deben existir en `$atributo`.
     * @param int $registro_id Identificador del registro al que se asocia el atributo.
     *                         Debe ser un entero mayor a 0.
     *
     * @return bool|array Devuelve `true` si la validación es exitosa.
     *                    Si ocurre un error, devuelve un array con la información del error.
     *
     * @throws errores Si falta alguna clave en `$atributo`, el `adm_atributo_id` no es válido o el `registro_id` es menor o igual a 0.
     *
     * @example
     * ```php
     * $atributo = [
     *     'adm_atributo_descripcion' => 'Color',
     *     'adm_atributo_id' => 5
     * ];
     * $keys = ['adm_atributo_descripcion', 'adm_atributo_id'];
     * $registro_id = 10;
     *
     * $resultado = valida_attr($atributo, $keys, $registro_id);
     * // Salida esperada: true
     *
     * $atributo_invalido = [
     *     'adm_atributo_descripcion' => 'Tamaño'
     * ];
     * $resultado = valida_attr($atributo_invalido, $keys, $registro_id);
     * // Salida esperada:
     * // ['error' => true, 'mensaje' => 'Error al validar $atributo', 'data' => [...]]
     *
     * $resultado = valida_attr($atributo, $keys, 0);
     * // Salida esperada:
     * // ['error' => true, 'mensaje' => 'Error registro_id debe ser mayor a 0', 'data' => 0]
     * ```
     */
    private function valida_attr(array $atributo, array $keys, int $registro_id): bool|array
    {
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }
        $keys = array('adm_atributo_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $registro_id,
                es_final: true);
        }

        return true;
    }


}
