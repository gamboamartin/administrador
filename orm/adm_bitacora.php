<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;

use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_bitacora extends modelo{
    /**
     * DEBUG INI
     * bitacora constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        
        $tabla = 'adm_bitacora';
        $columnas = array($tabla=>false,'adm_seccion'=>$tabla,'adm_usuario'=>$tabla);
        $campos_obligatorios = array('adm_seccion_id','registro','adm_usuario_id','transaccion','sql_data','valor_id');
        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }

    /**
     * REG
     * Registra un nuevo evento en la bitácora en la base de datos.
     *
     * Este método inserta un nuevo registro en la tabla de bitácoras (`adm_bitacora`).
     * Si el campo `codigo` no está presente en el registro, se genera un código aleatorio de 20 caracteres.
     * Si el campo `descripcion` no está presente, se genera una descripción con la fecha y un número aleatorio.
     * Luego, se llama al método `parent::alta_bd()` para insertar el registro en la base de datos.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con la información del registro insertado o un array de error si ocurre una falla.
     *
     * @throws errores Si ocurre un error al generar el código o la descripción, o si la inserción falla.
     *
     * @example
     * ```php
     * $bitacora = new adm_bitacora($pdo);
     * $bitacora->registro = [
     *     'adm_seccion_id' => 1,
     *     'registro' => json_encode(['campo' => 'valor']),
     *     'adm_usuario_id' => 10,
     *     'transaccion' => 'INSERT',
     *     'sql_data' => 'INSERT INTO tabla (campo) VALUES ("valor")',
     *     'valor_id' => 25
     * ];
     * $resultado = $bitacora->alta_bd();
     * print_r($resultado);
     * ```
     * **Salida esperada en caso de éxito:**
     * ```php
     * stdClass Object
     * (
     *     [id] => 1
     *     [codigo] => ABCD1234XYZ56789MNOQ
     *     [descripcion] => 2025-03-15 14:35 12345
     *     [adm_seccion_id] => 1
     *     [registro] => '{"campo":"valor"}'
     *     [adm_usuario_id] => 10
     *     [transaccion] => 'INSERT'
     *     [sql_data] => 'INSERT INTO tabla (campo) VALUES ("valor")'
     *     [valor_id] => 25
     * )
     * ```
     * **Salida esperada en caso de error:**
     * ```php
     * Array
     * (
     *     [error] => true
     *     [mensaje] => 'Error al generar código'
     *     [data] => null
     * )
     * ```
     */
    final public function alta_bd(): array|stdClass
    {
        if(!isset($this->registro['codigo'])){
            $codigo = $this->get_codigo_aleatorio(longitud: 20);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo',data:  $codigo);
            }
            $this->registro['codigo'] = $codigo;
        }
        if(!isset($this->registro['descripcion'])){
            $descripcion = date('Y-m-d i:s').mt_rand(10000,99999);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar descripcion',data:  $descripcion,
                    es_final: true);
            }
            $this->registro['descripcion'] = $descripcion;
        }
        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar bitacora',data:  $r_alta_bd);
        }
        return $r_alta_bd;

    }
}