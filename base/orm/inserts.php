<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use PDO;
use PDOStatement;
use stdClass;
use Throwable;

class inserts{
    private errores $error;

    #[Pure] public function __construct(){
        $this->error = new errores();

    }

    /**
     *
     * Devuelve un arreglo con las columnas y el valor del ID del usuario
     *
     * @return array
     *
     * @example
     *      $data_asignacion = $this->asigna_data_user_transaccion();
     *
     * @uses modelos->alta_bd();
     * @version 1.476.49
     */
    private function asigna_data_user_transaccion(): array
    {
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no hay session iniciada',data: array());
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error existe usuario',data: $_SESSION);
        }
        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id']);
        }

        $usuario_alta_id = $_SESSION['usuario_id'];
        $usuario_upd_id = $_SESSION['usuario_id'];
        $campos = ',usuario_alta_id,usuario_update_id';
        $valores = ','.$usuario_alta_id.','.$usuario_upd_id;

        return array('campos'=>$campos,'valores'=>$valores);
    }

    /**
     * Genera los campos en forma de SQL para insersion
     * @param string $campos Conjunto de campos previos
     * @param string $campo Campo a insertar
     * @return string|array
     * @version 1.468.49
     */
    private function campos_alta_sql(string $campo, string $campos): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        $campos .= $campos === '' ? $campo : ",$campo";
        return $campos;
    }

    /**
     * Integra los datos de log a alta SQÑ
     * @param bool|PDOStatement $alta_valido String sql o resultado PDO
     * @param bool|PDOStatement $update_valido String sql o resultado PDO
     * @param string $campos Conjunto de campos previos a alta
     * @param string $valores Conjunto de valores previos a alta
     * @return array|stdClass
     * @version 1.479.49
     */
    private function data_log(
        bool|PDOStatement $alta_valido, string $campos, bool|PDOStatement $update_valido,
        string $valores): array|stdClass
    {

        $campos = trim($campos);
        if($campos === ''){
            return $this->error->error(mensaje: 'Error campos esta vacio',data: $campos);
        }
        $valores = trim($valores);
        if($valores === ''){
            return $this->error->error(mensaje: 'Error valores esta vacio',data: $valores);
        }

        if($alta_valido &&  $update_valido ){
            if(!isset($_SESSION)){
                return $this->error->error(mensaje: 'Error no hay session iniciada',data: array());
            }
            if(!isset($_SESSION['usuario_id'])){
                return $this->error->error(mensaje: 'Error existe usuario',data: $_SESSION);
            }
            if($_SESSION['usuario_id'] <= 0){
                return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id']);
            }

            $data_asignacion = $this->asigna_data_user_transaccion();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar datos de transaccion', data: $data_asignacion);
            }
            $campos .= $data_asignacion['campos'];
            $valores .= $data_asignacion['valores'];
        }

        $data = new stdClass();
        $data->campos = $campos;
        $data->valores = $valores;
        return $data;
    }

    /**
     * Integra los datos default de alta
     * @param PDO $link Conexion  a la base de datos
     * @param string $tabla Tabla o modelo
     * @return stdClass|array
     * @version 1.475.49
     */
    private function data_para_log(PDO $link, string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje:'Error tabla esta vacia', data: $tabla);
        }

        $existe_alta_id = /** @lang MYSQL */"SELECT count(usuario_alta_id) FROM " . $tabla;
        $existe_update_id = /** @lang MYSQL */"SELECT count(usuario_alta_id) FROM $tabla";

        try {
            $alta_valido = $link->query($existe_alta_id);
        }
        catch (Throwable $e){
            $data_error = new stdClass();
            $data_error->e = $e;
            $data_error->sql = $existe_alta_id;
            return $this->error->error(mensaje:'Error al ejecutar sql', data: $data_error);
        }
        try {
            $update_valido = $link->query($existe_update_id);
        }
        catch (Throwable $e){
            $data_error = new stdClass();
            $data_error->e = $e;
            $data_error->sql = $existe_update_id;
            return $this->error->error(mensaje:'Error al ejecutar sql', data: $data_error);
        }

        $data = new stdClass();
        $data->alta_valido = $alta_valido;
        $data->update_valido = $update_valido;
        return $data;
    }

    /**
     * Genera los datos de un log para alta
     * @param PDO $link Conexion a la base de datos
     * @param array $registro Registro previo a la insersion
     * @param string $tabla Tabla para integracion de datos logo
     * @return array|stdClass
     * @version 1.487.49
     */
    private function genera_data_log(PDO $link, array $registro, string $tabla): array|stdClass
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro vacio',data:  $registro);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje:'Error tabla esta vacia', data: $tabla);
        }
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no hay session iniciada',data: array());
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error existe usuario',data: $_SESSION);
        }
        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id']);
        }

        $sql_data_alta = $this->sql_alta_full(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql ', data: $sql_data_alta);
        }

        $datas = $this->data_para_log(link:$link,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data log', data: $datas);
        }

        $data_log = $this->data_log(alta_valido: $datas->alta_valido, campos:  $sql_data_alta->campos,
            update_valido:  $datas->update_valido,valores:  $sql_data_alta->valores);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data log', data: $data_log);
        }

        return $data_log;
    }

    /**
     * P INT P ORDER ERROREV
     * @param int $registro_id
     * @param string $tabla
     * @return  array
     */
    private function data_session_alta(int $registro_id, string $tabla): array
    {
        if($tabla === ''){
            return  $this->error->error(mensaje: 'Error this->tabla esta vacia',data: $tabla);
        }
        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error $this->registro_id debe ser mayor a 0',data: $registro_id);
        }
        $_SESSION['exito'][]['mensaje'] = $tabla.' se agrego con el id '.$registro_id;
        return $_SESSION['exito'];
    }

    /**
     * P INT P ORDER ERROREV
     * @param stdClass $data_log Conjunto de datos log
     * @param modelo $modelo
     * @return array|stdClass
     */
    private function inserta_sql(stdClass $data_log, modelo $modelo): array|stdClass
    {
        $keys = array('campos','valores');
        foreach($keys as $key){
            if(!isset($data_log->$key)){
                return $this->error->error(mensaje: 'Error no existe data_log->'.$key, data: $data_log);
            }
        }
        foreach($keys as $key){
            if(trim($data_log->$key) === ''){
                return $this->error->error(mensaje:'Error esta vacio data_log->'.$key, data: $data_log);
            }
        }

        $modelo->transaccion = 'INSERT';

        $sql = $this->sql_alta(campos: $data_log->campos,tabla: $modelo->tabla, valores: $data_log->valores);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:  $sql);
        }

        $resultado = $modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql',data:  $resultado);
        }
        return $resultado;
    }

    /**
     * Ajusta los elementos a insertar con slaches
     * @param string $campo Nombre del campo a integrar al sql
     * @param mixed $value Valor a insertar
     * @return array|stdClass
     * @version 1.467.49
     */
    private function slaches_campo(string $campo, mixed $value): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo);
        }

        $campo = addslashes($campo);


        $data_value = $this->value(value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar value',data:  $data_value);
        }

        $data = new stdClass();
        $data->campo = $campo;
        $data->value = $data_value->value;
        $data->value_es_null = $data_value->value_es_null;
        return $data;
    }

    /**
     * P ORDER P INT ERROREV
     * @param string $campos Campos para insert
     * @param string $tabla
     * @param string $valores
     * @return string|array
     */
    private function sql_alta(string $campos,string $tabla, string $valores): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $this tabla no puede venir vacio',data:  $tabla);
        }
        if($campos === ''){
            return $this->error->error(mensaje:'Error campos esta vacio', data:$campos);
        }
        if($valores === ''){
            return $this->error->error(mensaje:'Error valores esta vacio',data: $valores);
        }
        return /** @lang mysql */ 'INSERT INTO '. $tabla.' ('.$campos.') VALUES ('.$valores.')';
    }

    /**
     * Genera el SQL para insersion
     * @param array $registro Registro previo a la insersion
     * @return array|stdClass
     * @version 1.474.49
     */
    private function sql_alta_full(array $registro): array|stdClass
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro vacio',data:  $registro);
        }
        $campos = '';
        $valores = '';
        foreach ($registro as $campo => $value) {
            if(is_numeric($campo)){
                return $this->error->error(mensaje: 'Error el campo no es valido',data:  $campo);
            }
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo);
            }

            $sql_base = $this->sql_base_alta(campo: $campo, campos:  $campos, valores:  $valores, value:  $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql ',data:  $sql_base);
            }
            $campos = $sql_base->campos;
            $valores = $sql_base->valores;
        }

        $datas = new stdClass();
        $datas->campos = $campos;
        $datas->valores = $valores;
        return $datas;
    }

    /**
     * Genera el SQL base de un alta
     * @param string $campo Nombre del campo a integrar al sql
     * @param mixed $value Valor a insertar
     * @param string $campos Campos a integrar
     * @param string $valores Valores a integrar
     * @return array|stdClass
     * @version 1.473.49
     */
    private function sql_base_alta(string $campo, string $campos, string $valores, mixed $value): array|stdClass
    {
        if(is_numeric($campo)){
            return $this->error->error(mensaje: 'Error el campo no es valido',data:  $campo);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo);
        }

        $slacheados = $this->slaches_campo(campo: $campo,value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ajustar campo ', data:$slacheados);
        }


        $campos_r = $this->campos_alta_sql(campo:  $slacheados->campo, campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar campo ', data:$campos_r);
        }

        $valores_r = $this->valores_sql_alta(valores: $valores,value:  $slacheados->value,
            value_es_null: $slacheados->value_es_null);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar valor ',data: $valores_r);
        }
        $data = new stdClass();
        $data->campos = $campos_r;
        $data->valores = $valores_r;
        return $data;
    }

    /**
     * Genera las transacciones en sql
     * @param modelo $modelo Modelo en ejecucion
     * @return array|stdClass
     */
    public function transacciones(modelo $modelo): array|stdClass
    {
        $data_log = $this->genera_data_log(link: $modelo->link,registro: $modelo->registro,tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data log', data: $data_log);
        }

        $resultado = $this->inserta_sql(data_log: $data_log, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $resultado);
        }

        $transacciones = $this->transacciones_default(consulta: $resultado->sql, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transacciones',data:  $transacciones);
        }

        $resultado->transacciones = $transacciones;

        return $resultado;
    }

    /**
     * P INT ERROREV
     * @param string $consulta texto en forma de SQL
     * @param modelo $modelo
     * @return array|stdClass
     */
    private function transacciones_default(string $consulta, modelo $modelo): array|stdClass
    {
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje: 'Error this->registro_id debe ser mayor a 0', data: $modelo->registro_id);
        }

        $bitacora = (new bitacoras())->bitacora(consulta: $consulta, funcion: __FUNCTION__, modelo: $modelo,
            registro: $modelo->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar bitacora',data:  $bitacora);
        }

        $r_ins = (new atributos())->ejecuta_insersion_attr(modelo: $modelo, registro_id: $modelo->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar atributos', data: $r_ins);
        }

        $data_session = $this->data_session_alta(registro_id:$modelo->registro_id,tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al asignar dato de SESSION', data: $data_session);
        }

        $datos = new stdClass();
        $datos->bitacora = $bitacora;
        $datos->attr = $r_ins;
        $datos->session = $data_session;
        return $datos;
    }

    /**
     * Integra los valores para una insersion
     * @param string $valores Valores previamente cargados
     * @param string $value Valor a cargar
     * @param bool $value_es_null Si null ajusta el campo en null sql
     * @return string|array
     * @version 1.468.49
     */
    private function valores_sql_alta(string $valores, string $value, bool $value_es_null): string|array
    {
        $value_aj = "'$value'";
        if($value_es_null){
            $value_aj = $value;
        }
        $value_aj = trim($value_aj);
        $valores .= $valores === '' ? $value_aj : ",$value_aj";
        return $valores;
    }

    /**
     * Integra un value para insersion
     * @param mixed $value Valor a insertar
     * @return array|stdClass
     * @version 1.465.49
     */
    private function value(mixed $value): array|stdClass
    {
        $value_es_null = false;
        try {
            if(is_null($value)){
                $value_es_null = true;
                $value = 'NULL';
            }
            else{
                $value = addslashes($value);
            }

        }
        catch (Throwable  $e){
            return $this->error->error(mensaje: 'Error al asignar value de campo '.$campo, data: $e);
        }
        $data = new stdClass();
        $data->value = $value;
        $data->value_es_null = $value_es_null;
        return $data;
    }



}
