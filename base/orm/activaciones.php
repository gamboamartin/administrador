<?php
/**
 * @author Martin Gamboa Vazquez
 * Clase definida para activar elementos en la base de datos
 * @version 1.110.27
 */
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class activaciones{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }

    /**
     * Inicializa la transaccion de activacion, y valida que el modelo sea aplicable a dicha transaccion
     * @version 1.110.27
     * @param modelo $modelo Modelo a normalizar, debe ser una estructura de la base de datos
     * @param bool $reactiva Si no es aplicable la reactivacion retornara error
     * @return array|stdClass obj->consulta, obj->transaccion y obj->name_model
     * @example
     *  $activaciones = new activaciones();
     *  $modelo = new adm_accion();
     *  $modelo->registro_id = 1;
     *  $reactiva = true;
     *  $init_activa = $activaciones->init_activa(modelo:$modelo, reactiva:$reactiva);
     *  print_r($init_activa);
     *      stdClass Object
     *      (
     *          [consulta] => UPDATE adm_accion_grupo SET status = 'activo' WHERE id = 1
     *          [transaccion] => ACTIVA
     *          [name_model] => adm_accion_grupo
     *      )'
     *
     * @example Verifica que exista el registro y que el modelo permita la actualizacion de activacion
     *  $activaciones = new activaciones();
     *  $modelo = new adm_accion();
     *  $modelo->registro_id = 1;
     *  $reactiva = false;
     *  $init_activa = $activaciones->init_activa(modelo:$modelo, reactiva:$reactiva);
     *  print_r($init_activa);
     *      stdClass Object
     *      (
     *          [consulta] => UPDATE adm_accion_grupo SET status = 'activo' WHERE id = 1
     *          [transaccion] => ACTIVA
     *          [name_model] => adm_accion_grupo
     */
    final public function init_activa(modelo $modelo, bool $reactiva): array|stdClass
    {
        if($modelo->registro_id <=0){
            return  $this->error->error(mensaje: 'Error  $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id);
        }

        $name_model = $this->normaliza_name_model(modelo:$modelo);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al normalizar modelo '.$modelo->tabla,data:$name_model);
        }

        $data_activacion = $this->maqueta_activacion(modelo:$modelo, reactiva: $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar datos de activacion '.$modelo->tabla,
                data:$data_activacion);
        }
        $modelo->consulta = $data_activacion->consulta;
        $modelo->transaccion = $data_activacion->transaccion;

        $data_activacion->name_model = $name_model;
        return $data_activacion;
    }

    /**
     * Genera el SQL para la activacion de un registro validado ue el modelo permita que se puedan
     * ejecutar cambios de activacion
     * @version 1.17.9
     * @param modelo $modelo Modelo a verificar validacion
     * @param bool $reactiva Si no es aplicable la reactivacion retornara error
     * @return array|stdClass Obj->consulta, Obj->transaccion
     */
    private function maqueta_activacion(modelo $modelo, bool $reactiva): array|stdClass
    {
        if($modelo->registro_id <=0){
            return  $this->error->error(mensaje: 'Error  $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id);
        }

        $valida = $this->verifica_reactivacion(modelo:$modelo,reactiva:  $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al validar transaccion activa en '.$modelo->tabla,data:$valida);
        }

        $sql = $this->sql_activa(registro_id:$modelo->registro_id,tabla:  $modelo->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar sql '.$modelo->tabla,data:$valida);
        }

        $data = new stdClass();
        $data->consulta = $sql;
        $data->transaccion = 'ACTIVA';

        return $data;
    }

    /**
     * Funcion que normaliza el nombre de un modelo de la forma namespace
     * @version 1.16.9
     * @param modelo $modelo Modelo a normalizar, debe ser una estructura de la base de datos
     * @return array|string string con nombre de la tabla normalizada como la clase en forma de namespace
     */
    private function normaliza_name_model(modelo $modelo): array|string
    {
        $modelo->tabla = trim($modelo->tabla);
        if($modelo->tabla === ''){
            return $this->error->error(mensaje:'Error el atributo tabla del modelo '.$modelo->tabla.' Esta vacio',
                data:$modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        return $modelo->tabla;
    }

    /**
     * Genera el SQL de status 0 'activo
     * Esta funcion pasarla a la clase sql
     * @param int $registro_id Identificador del registro a aplicar el update
     * @param string $tabla Tabla donde se aplicara el update
     * @return string|array SQL en forma de UPDATE
     * @version 1.17.9
     */
    private function sql_activa(int $registro_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return  $this->error->error(mensaje: 'Error  la tabla esta vacia', data: $tabla);
        }
        if($registro_id<=0){
            return  $this->error->error(mensaje: 'Error $registro_id debe ser mayor a 0', data: $registro_id);
        }
        return "UPDATE " . $tabla . " SET status = 'activo' WHERE id = " . $registro_id;
    }

    /**
     * Valida que la transaccion cuando en el modelo valida transaccion activa sea true bloquee la transaccion
     * @version 1.17.9
     * @param modelo $modelo Modelo a verificar validacion
     * @return bool|array Verdadero sila configuracion es valida
     */
    final public function valida_activacion(modelo $modelo): bool|array
    {
        if($modelo->registro_id <=0){
            return  $this->error->error(mensaje: 'Error  $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id);
        }

        $registro = $modelo->registro(registro_id: $modelo->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al obtener registro '.$modelo->tabla,data:$registro);
        }

        $valida = $modelo->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $modelo->aplica_transaccion_inactivo, registro: $registro,
            registro_id: $modelo->registro_id,tabla:  $modelo->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al validar transaccion activa en '.$modelo->tabla,data:$valida);
        }
        return $valida;
    }

    /**
     * Valida que la transaccion cuando en el modelo valida transaccion activa sea true bloquee la transaccion
     * @version 1.17.9
     * @param modelo $modelo Modelo a verificar validacion
     * @param bool $reactiva Si no es aplicable la reactivacion retornara error
     * @return bool|array Verdadero si es correcta la validacion
     */
    private function verifica_reactivacion(modelo $modelo,bool $reactiva): bool|array
    {
        if($modelo->registro_id <=0){
            return  $this->error->error(mensaje: 'Error  $modelo->registro_id debe ser mayor a 0',
                data: $modelo->registro_id);
        }

        $valida = true;
        if(!$reactiva) {
            $valida = $this->valida_activacion(modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al validar transaccion activa en '.$modelo->tabla,
                    data:$valida);
            }
        }
        return $valida;
    }



}
