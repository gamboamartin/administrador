<?php
namespace base\orm;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\errores\errores;
use stdClass;

class upd{
    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    /**
     *
     * Devuelve una cadena que comprueba la existencia del usuario que realiza la modificacion asignando su id a la
     * columna usuario_update_id
     *
     * @param modelo_base $modelo
     * @param bool $valida_user si false no se valida la existencia del user en database
     * @return array|string
     * @example
     *      $this->campos_sql = $campos_sql;
     *      $campos_sql = $this->agrega_usuario_session();
     *
     * @uses modelos->modifica_bd();
     * @uses modelos->modifica_por_id();
     * @internal $this->usuario_existente();
     * @version 1.287.41
     */
    private function agrega_usuario_session(modelo_base $modelo, bool $valida_user): array|string
    {
        if($modelo->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $modelo->usuario_id);
        }

        if($modelo->campos_sql === ''){
            return $this->error->error(mensaje: 'campos no puede venir vacio',data: $modelo->campos_sql);
        }

        $usuario_id = 2;
        if($valida_user) {

            $existe_user = $this->usuario_existente(modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error validar existencia de usuario', data: $existe_user);
            }
            if (!$existe_user) {
                return $this->error->error(mensaje: 'Error no existe usuario',
                    data: array($existe_user, $modelo->campos_sql, $modelo->usuario_id));
            }
            $usuario_id = $modelo->usuario_id;
        }

        return 'usuario_update_id=' . $usuario_id;
    }


    /**
     * @param stdClass $ejecuta_upd Ejecuta la actualizacion de un row
     * @param int $id Identificador en proceso
     * @param modelo $modelo Modelo en ejecucion
     * @param bool $reactiva Si reactiva  valida si un upd es valido en el modelo
     * @param array $registro Registro en proceso
     * @param bool $valida_user si false no se valida la existencia del user en database
     * @return array|stdClass
     * @version 4.2.0
     */
    final public function aplica_ejecucion(
        stdClass $ejecuta_upd, int $id, modelo $modelo, bool $reactiva, array $registro, bool $valida_user): array|stdClass
    {
        if($modelo->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $modelo->usuario_id);
        }

        if(!isset($ejecuta_upd->resultado)){
            return $this->error->error(mensaje: 'Error ejecuta_upd->resultado debe existir',data: $ejecuta_upd);
        }
        if(!isset($ejecuta_upd->ejecuta_upd)){
            return $this->error->error(mensaje: 'Error ejecuta_upd->ejecuta_upd debe existir',data: $ejecuta_upd);
        }
        if(!is_object($ejecuta_upd->resultado)){
            return $this->error->error(mensaje: 'Error ejecuta_upd->resultado debe ser un objeto',data: $ejecuta_upd);
        }

        $resultado = $ejecuta_upd->resultado;

        if($ejecuta_upd->ejecuta_upd) {

            $valida = $this->valida_upd(modelo: $modelo,id:  $id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar modelo', data: $valida);
            }

            $resultado = $this->ejecuta_upd_modelo(id:$id, modelo: $modelo,reactiva:  $reactiva,
                registro:  $registro, valida_user: $valida_user);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ejecutar sql',
                    data:  array($resultado, 'sql' => $modelo->consulta));
            }
        }
        return $resultado;
    }

    /**
     * Genera los campos para un update
     * @param modelo_base $modelo Modelo en ejecucion
     * @return array|string
     * @version 1.432.48
     */
    private function campos(modelo_base $modelo): array|string
    {
        $campos = '';
        foreach ($modelo->registro_upd as $campo => $value) {

            if(is_numeric($campo)){
                return $this->error->error(mensaje: 'Error ingrese un campo valido es un numero',data: $campo);
            }
            if($campo === ''){
                return $this->error->error(mensaje: 'Error ingrese un campo valido esta vacio',data: $campo);
            }

            $campos = $this->maqueta_rows_upd(campo: $campo, campos:  $campos, modelo: $modelo,value:  $value);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar campos', data: $campos);
            }
        }
        return $campos;
    }

    /**
     * Obtiene los campos para un upd del modelo
     * @param modelo $modelo Modelo en ejecucion
     * @param bool $valida_user si false no se valida la existencia del user en database
     * @return array|string
     * @version 1.565.51
     */
    private function campos_sql(modelo $modelo, bool $valida_user): array|string
    {
        if($modelo->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $modelo->usuario_id);
        }
        if(count($modelo->registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro_upd de modelo no puede venir vacio',
                data: $modelo->registro_upd);
        }

        $campos_sql_model = $this->genera_campos_update(modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campos',data:  $campos_sql_model);
        }


        $modelo->campos_sql = $campos_sql_model;
        $campos_sql_user = $this->agrega_usuario_session(modelo: $modelo, valida_user: $valida_user);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al AGREGAR USER', data: $campos_sql_user);
        }


        $modelo->campos_sql =  $campos_sql_model.','.$campos_sql_user;
        return $modelo->campos_sql;
    }


    /**
     * Ejecuta un update en el motor d ebase de datos
     * @param int $id Identificador
     * @param modelo $modelo Modelo en ejecucion
     * @param bool $reactiva Si reactiva el elemento sera validado
     * @param array $registro Registro en ejecucion
     * @param bool $valida_user si false no se valida la existencia del user en database
     * @return array|stdClass
     * @version 1.567.51
     */
    private function ejecuta_upd_modelo(int $id, modelo $modelo, bool $reactiva, array $registro,
                                        bool $valida_user): array|stdClass
    {

        $valida = $this->valida_upd(modelo: $modelo,id:  $id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar modelo', data: $valida);
        }

        $sql = $this->sql_update(id:$id,modelo:  $modelo,reactiva:  $reactiva,registro:  $registro,
            valida_user: $valida_user);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }

        $consulta = $sql;
        $modelo->consulta = $consulta;

        $modelo->transaccion = 'UPDATE';
        $modelo->registro_id = $id;

        $resultado = $modelo->ejecuta_sql(consulta: $modelo->consulta);

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql',
                data:  array($resultado, 'sql' => $modelo->consulta));
        }

        $bitacora = (new bitacoras())->bitacora(consulta: $consulta, funcion: __FUNCTION__, modelo: $modelo,
            registro: $modelo->registro_upd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar bitacora',data:  $bitacora);
        }

        return $resultado;
    }

    /**
     * Verifica si aplica ejecucion de modificacion de datos
     * @version 1.80.17
     * @param int $id Identificador del modelo
     * @param modelo $modelo Modelo en ejecucion
     * @return array|stdClass
     */
    final public function ejecuta_upd(int $id, modelo $modelo): array|stdClass
    {
        $resultado = new stdClass();
        $ejecuta_upd = true;
        if(count($modelo->registro_upd) === 0){
            $ejecuta_upd = false;

            $resultado = (new inicializacion())->result_warning_upd(id:$id,
                registro_upd: $modelo->registro_upd,resultado:  $resultado);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al inicializar elemento',data:$resultado);
            }
        }
        $data = new stdClass();
        $data->ejecuta_upd = $ejecuta_upd;
        $data->resultado = $resultado;
        return $data;
    }



    /**
     *
     * Genera sql con forma de campos para UPDATE
     * @return array|string con sql de campos para update
     * @example
     *     $campos_sql = $this->genera_campos_update();
     * @uses modelo
     * @internal $consultas_base->obten_campos($this->tabla,'modifica', $this->link);
     * @internal $this->obten_campos_update();
     * @param modelo_base $modelo Modelo en ejecucion
     * @version 1.445.48
     */
    private function genera_campos_update(modelo_base $modelo): array|string
    {
        if(count($modelo->registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro_upd de modelo no puede venir vacio',
                data: $modelo->registro_upd);
        }

        $campos = $this->obten_campos_update(modelo: $modelo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos',data: $campos);
        }

        return $campos;
    }

    /**
     * Maqueta el registro para actualizacion
     * @param string $campo Campo a reasignar valor
     * @param string $campos Conjunto de campos a validar
     * @param modelo_base $modelo Modelo en ejecucion
     * @param string|int|float|null $value Valor a ajustar
     * @return array|string
     * @version 1.429.48
     */
    private function maqueta_rows_upd(string $campo, string $campos,
                                      modelo_base $modelo, string|int|float|null $value): array|string
    {
        $campos_ = $campos;
        if(is_numeric($campo)){
            return $this->error->error(mensaje: 'Error ingrese un campo valido es un numero',data: $campo);
        }
        if($campo === ''){
            return $this->error->error(mensaje: 'Error ingrese un campo valido esta vacio',data: $campo);
        }

        $params = $this->params_data_update(campo: $campo, modelo: $modelo,value:  $value);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar parametros', data:$params);
        }

        $campos_ = $this->rows_update(campos: $campos_, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar campos',data:  $campos_);
        }
        return $campos_;
    }

    /**
     *
     * Devuelve la forma de los campos a modifica enb forma de sql
     * @param modelo_base $modelo modelo en ejecucion
     * @return array|string con sql con maquetacion de una modificacion en sql campo = 'valor'
     * @throws errores $this->registro_upd vacio
     * @throws errores $this->registro_upd[campo] campo es un numero
     * @throws errores $this->registro_upd[campo] campo es vacio
     * @example
     *       $campos = $this->obten_campos_update();
     * @version 1.438.48
     *
     */
    private function obten_campos_update(modelo_base $modelo): array|string
    {

        if(count($modelo->registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro no puede venir vacio',data: $modelo->registro_upd);
        }
        $campos = $this->campos(modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar campos',data:  $campos);
        }



        return $campos;
    }

    /**
     * Ajusta los parametros para update
     * @param string $campo Campo a reasignar valor
     * @param modelo_base $modelo Modelo en ejecucion
     * @param string|float|int|null $value Valor a ajustar
     * @return array|stdClass
     * @version 1.425.48
     */
    private function params_data_update(string $campo, modelo_base $modelo, string|float|int|null $value): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo);
        }

        $value_ = $value;
        $value_ = (new monedas())->value_moneda(campo: $campo, modelo: $modelo, value: $value_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar value moneda',data:  $value_);
        }

        $data = $this->slaches_value(campo: $campo,value:  $value_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar value',data:  $data);
        }

        $data->value = $this->value_null(value: $data->value);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar value', data:$data);
        }
        return $data;
    }

    /**
     * Integra un sql para update
     * @param int $id Identificador de registro
     * @param modelo $modelo Modelo en ejecucion
     * @param bool $reactiva Si reactiva el registro sera previamente validado
     * @param array $registro Registro a actualizar
     * @param bool $valida_user si false no se valida la existencia del user en database
     * @return array|string
     * @version 1.566.51
     */
    private function sql_update(int $id, modelo $modelo, bool $reactiva, array $registro, bool $valida_user): array|string
    {

        if(count($modelo->registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro_upd de modelo no puede venir vacio',
                data: $modelo->registro_upd);
        }
        if($id<=0){
            return $this->error->error(mensaje: 'Error $id debe ser mayor a 0', data: $id);
        }
        if($modelo->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $modelo->usuario_id);
        }

        $reactiva_row = $this->reactiva(modelo: $modelo,reactiva:  $reactiva,registro:  $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar transaccion activa', data: $reactiva_row);
        }



        $campos_sql = $this->campos_sql(modelo: $modelo, valida_user: $valida_user);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al AGREGAR USER', data: $campos_sql);
        }


        $sql = (new sql())->update(campos_sql: $modelo->campos_sql, id:$id, tabla: $modelo->tabla);
        if (errores::$error) {
            return $this->error->error('Error al generar sql', $sql);
        }
        return $sql;
    }

    /**
     * Valida siu aplica o no una reactivacion de registro
     * @param modelo $modelo Modelo a validar
     * @param bool $reactiva Si !$reactiva bloquea
     * @param array $registro Registro a verificar
     * @return bool|array
     * @version 1.427.48
     */
    private function reactiva(modelo $modelo, bool $reactiva, array $registro): bool|array
    {
        $valida = false;
        if (!$reactiva) {
            $valida = $modelo->validacion->valida_transaccion_activa(
                aplica_transaccion_inactivo: $modelo->aplica_transaccion_inactivo,
                registro: $registro, registro_id: $modelo->registro_id, tabla: $modelo->tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar transaccion activa', data: $valida);
            }
        }
        return $valida;
    }

    /**
     * Concatena los elementos par aun UPDATE
     * @param string $campos Conjunto de campos a validar
     * @param stdClass $params Parametros para integrar en upd
     * @return string|array
     * @version 1.429.48
     */
    private function rows_update(string $campos, stdClass $params): string|array
    {

        if(!isset($params->campo)){
            return $this->error->error('Error no existe params->campo', $params);
        }
        if(!isset($params->value)){
            return $this->error->error('Error no existe params->value', $params);
        }

        $params->campo = trim($params->campo);
        if($params->campo === ''){
            return $this->error->error('Error params->campo esta vacio', $params);
        }

        $campos .= $campos === "" ? "$params->campo = $params->value" : ", $params->campo = $params->value";
        return $campos;
    }

    /**
     * Ajusta los elementos con slashes
     * @param string $campo Campo a normalizar
     * @param string|int|float|null $value Valor a normalizar
     * @return stdClass|array
     * @version 1.409.47
     */
    private function slaches_value(string $campo, string|int|float|null $value): stdClass|array
    {
        $campo = trim($campo);
        if(is_null($value)){
            $value = "";
        }
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo);
        }
        $campo = addslashes($campo);
        $value = addslashes($value);

        $data = new stdClass();
        $data->campo = $campo;
        $data->value = $value;

        return $data;
    }

    /**
     *
     * Devuelve una variable de tipo booleana que indica si el usuario existe o no
     * @param modelo $modelo
     * @param array $campos_encriptados Campos a validar desencripctacion encriptacion
     * @return bool|array
     * @version 1.145.31
     * @example
     *      $existe_user = $this->usuario_existente();
     *
     * @uses modelo_basico->agrega_usuario_session()
     * @internal modelo_basico->$this->ejecuta_consulta();
     */
    private function usuario_existente(modelo_base $modelo, array $campos_encriptados = array()): bool|array
    {
        if($modelo->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido o no cargado deberia exitir 
            $modelo->usuario_id mayor  a 0',data: $modelo->usuario_id);
        }

        $consulta = /** @lang MYSQL */
            'SELECT count(*) AS existe FROM adm_usuario WHERE adm_usuario.id = '.$modelo->usuario_id;
        $r_usuario_existente = $modelo->ejecuta_consulta(consulta: $consulta, campos_encriptados: $campos_encriptados);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $r_usuario_existente);
        }

        $usuario_existente = $r_usuario_existente->registros[0];

        $update_valido = false;
        if((int)$usuario_existente['existe'] === 1){
            $update_valido = true;
        }

        return $update_valido;

    }

    private function valida_upd(modelo $modelo, int $id): bool|array
    {
        if(count($modelo->registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro_upd de modelo no puede venir vacio',
                data: $modelo->registro_upd);
        }
        if($id<=0){
            return $this->error->error(mensaje: 'Error $id debe ser mayor a 0', data: $id);
        }
        if($modelo->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $modelo->usuario_id);
        }
        return true;
    }

    /**
     * Ajusta un NULL a Value
     * @param string|int|float|null $value Valor a ajustar como NULL
     * @return string
     * @version 1.415.48
     */
    private function value_null(string|int|float|null $value): string
    {
        if ($value === null) {
            $value = 'NULL';
        }
        else {
            $value = "'" . $value . "'";
        }
        return $value;
    }


}
