<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;
use stdClass;

class inicializacion{

    private errores $error;
    private validaciones $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Ajusta los campos al actualizar un registro.
     *
     * Este método se utiliza para limpiar y verificar los campos
     * que se envían para actualizar un registro en la base de datos.
     * Si encuentra algún error, retorna un error con mensaje detallando el problema.
     *
     * @param int $id El ID del registro que se va a actualizar.
     * @param modelo $modelo El modelo de datos que contiene el registro.
     * @return array Devuelve el registro actualizado si todo va bien, o un error si algo va mal.
     *
     * @throws errores Si algún problema surge durante el proceso,
     * la función lanzará una excepción de un tipo específico definido en su implementación.
     *
     * @version 16.279.1
     */
    final public function ajusta_campos_upd(int $id, modelo $modelo): array
    {
        if($id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $id debe ser mayor a 0',
                data: $id);
        }

        $registro_previo = $modelo->registro(registro_id: $id,columnas_en_bruto: true,retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro previo',data: $registro_previo);
        }

        foreach ($modelo->registro_upd as $campo=>$value_upd){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje:'Error el campo del row esta vacio',data:$campo);
            }
            if(is_numeric($campo)){
                return $this->error->error(mensaje:'Error el campo no puede ser un numero',data:$campo);
            }

            $ajusta = $this->ajusta_registro_upd(campo: $campo,modelo:  $modelo,
                registro_previo: $registro_previo,value_upd:  $value_upd);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al ajustar elemento',data:$ajusta);
            }
        }
        return $modelo->registro_upd;
    }

    /**
     * Ajusta los parametros si no existen en el complemento
     * @param stdClass $complemento Complemento con datos para maquetacion de sql
     * @return array|stdClass
     * @version 1.259.40
     * @verfuncion 1.1.0
     * @fecha 2022-08-02 13:07
     * @author mgamboa
     */
    final public function ajusta_params(stdClass $complemento): array|stdClass
    {
        if(!isset($complemento->params)){
            $complemento = $this->init_params(complemento: $complemento);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar params',data: $complemento);
            }
        }
        return $complemento;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Ajusta la información de registro para una operación de actualización.
     *
     * Esta función compara un valor previo con el nuevo valor proporcionado para un campo específico.
     * Si ambos valores son iguales, entonces el campo respectivo es eliminado del registro de actualización
     *  del Modelo.
     * En el caso de que el campo esté vacío, arroja un error indicando que el campo está vacío.
     * También valida el registro previo para verificar si contiene el campo especificado.
     *
     * @param string $campo El campo que se necesita ajustar.
     * @param modelo $modelo El modelo en el que se realiza la operación de actualización.
     * @param stdClass $registro_previo Registro previo del modelo antes de la operación de actualización.
     * @param string|null $value_upd El nuevo valor que se quiere establecer para el campo.
     *
     * @return array Retorna el registro actualizado del modelo.
     * @throws errores si el campo está vacío, si la integración del registro previo falla o si la validacion del
     *  registro previo falla.
     * @version 16.277.1
     */
    private function ajusta_registro_upd(string $campo, modelo $modelo, stdClass $registro_previo,
                                        string|null $value_upd): array
    {
        $value_upd = trim($value_upd);
        $campo = trim($campo);

        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio', data:$campo);
        }

        $registro_previo = $this->registro_previo_null(campo: $campo,registro_previo:  $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar registro_previo', data:$registro_previo);
        }

        $keys = array($campo);
        $valida = (new validaciones())->valida_existencia_keys(keys: $keys, registro: $registro_previo,
            valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro_previo', data:$valida);
        }

        $value_previo = trim($registro_previo->$campo);

        if($value_previo === $value_upd){
            unset($modelo->registro_upd[$campo]);
        }

        return $modelo->registro_upd;
    }

    private function aplica_status_inactivo(string $key, array $registro): array
    {
        if(!isset($registro[$key])){
            $registro = $this->init_key_status_inactivo(key: $key, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializa status',data: $registro);
            }
        }
        return $registro;
    }

    private function asigna_data_attr(array $atributo, string $field, string $key, string $key_new, modelo $modelo){
        $modelo->atributos->$field->$key_new = $atributo[$key];
        return $modelo->atributos->$field;
    }


    /**
     * Funcion para asignar los parametros de una view
     * @version 1.181.34
     * @param array $campo Campo a validar elementos
     * @param array $bools conjunto de campos de tipo bool en bd activo o inactivo
     * @param stdClass $datos Datos a validar
     * @return array
     */
    private function asigna_data_campo(array $bools, array $campo, stdClass $datos): array
    {



        $datas = $this->init_data(bools:  $bools, campo: $campo,datos:  $datos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa $datos',data: $datas);
        }

        if(!isset($datas->campo['adm_elemento_lista_cols'])){
            $datas->campo['adm_elemento_lista_cols'] = 12;
        }
        if(!isset($datas->campo['adm_elemento_lista_tipo'])){
            $datas->campo['adm_elemento_lista_tipo'] = 'text';
        }
        if(!isset($datas->campo['adm_elemento_lista_tabla_externa'])){
            $datas->campo['adm_elemento_lista_tabla_externa'] = '';
        }
        if(!isset($datas->campo['adm_elemento_lista_etiqueta'])){
            $datas->campo['adm_elemento_lista_etiqueta'] = '';
        }
        if(!isset($datas->campo['adm_elemento_lista_campo'])){
            $datas->campo['adm_elemento_lista_campo'] = '';
        }
        if(!isset($datas->campo['adm_elemento_lista_descripcion'])){
            $datas->campo['adm_elemento_lista_descripcion'] = '';
        }
        if(!isset($datas->campo['adm_elemento_lista_id'])){
            $datas->campo['adm_elemento_lista_id'] = '';
        }

        if(!is_array($datas->datos->valor_extra)){
            $datas->datos->valor_extra = array();
        }

        if(!isset($datas->campo['disabled']) || $datas->campo['disabled'] === '' || $datas->campo['disabled'] === 'inactivo'){
            $datas->campo['disabled'] = false;
        }
        if(isset($datas->campo['disabled']) && $datas->campo['disabled'] === 'activo'){
            $datas->campo['disabled'] = true;
        }


        $data['cols'] = $datas->campo['adm_elemento_lista_cols'];
        $data['disabled'] = $datas->campo['disabled'];
        $data['con_label'] = $datas->bools['con_label'];
        $data['required'] = $datas->bools['required'];
        $data['tipo'] = $datas->campo['adm_elemento_lista_tipo'];
        $data['llaves_foraneas'] = $datas->datos->llaves;
        $data['vista'] = array($datas->datos->vista);
        $data['ln'] = $datas->bools['ln'];
        $data['tabla_foranea'] = $datas->campo['adm_elemento_lista_tabla_externa'];
        $data['columnas'] = $datas->datos->columnas;
        $data['pattern'] = $datas->datos->pattern;
        $data['select_vacio_alta'] = $datas->bools['select_vacio_alta'];
        $data['etiqueta'] = $datas->campo['adm_elemento_lista_etiqueta'];
        $data['campo_tabla_externa'] = $datas->datos->tabla_externa;
        $data['campo_name'] = $datas->campo['adm_elemento_lista_campo'];
        $data['campo'] = $datas->campo['adm_elemento_lista_descripcion'];
        $data['tabla_externa_renombrada'] = $datas->datos->externa_renombrada;
        $data['data_extra'] = $datas->datos->valor_extra;
        $data['separador_select_columnas'] = $datas->datos->separador;
        $data['representacion'] = $datas->datos->representacion;
        $data['css_id'] = $datas->datos->css_id;
        $data['adm_elemento_lista_id'] =$datas->campo['adm_elemento_lista_id'];

        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función toma dos parámetros; una matriz de campos cifrados y una matriz de filas (row). Para cada valor
     * en la matriz fila, verifica si el campo es numérico. Si es así, retorna un mensaje de error.
     * En caso contrario, desencripta el valor usando la función `value_desencriptado`.
     * Si hay un error durante el proceso de desencriptación, retorna un mensaje de error.
     * Si el proceso es exitoso, asigna el valor desencriptado de vuelta a la matriz de fila (row) y procede con el siguiente valor.
     * La función finalmente retorna la matriz de fila (row) con todos los valores desencriptados.
     *
     * @param array $campos_encriptados Una matriz de campos que necesitan ser desencriptados.
     * @param array $row Una matriz de filas que contienen los campos cifrados.
     *
     * @return array Retorna la matriz de filas con los campos desencriptados.
     *
     * @throws errores Si el campo es numérico o si hay un error al desencriptar.
     *
     * @version 14.5.0
     */
    final public function asigna_valor_desencriptado(array $campos_encriptados, array $row): array
    {
        foreach ($row as $campo=>$value){
            if(is_numeric($campo)){
                $fix = ' El campo dentro de row debe ser un texto no numerico puede ser id, registro etc, no puede ';
                $fix .= ' ser 0 o 1 o cualquier numero, ejemplo de envio de row puede ser $row[x] o';
                $fix.= ' $row[cualquier texto no numerico] no puede ser row[0] o row[cualquier numero]';
                return $this->error->error(mensaje: 'Error el campo debe ser un texto', data:$campo, fix: $fix);
            }

            $value_enc = $this->value_desencriptado(campo:$campo,
                campos_encriptados: $campos_encriptados, value: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al desencriptar', data:$value_enc);
            }

            $row[$campo] = $value_enc;
        }
        return $row;
    }

    /**
     * Asigna un valor encriptado a un campo
     * @param stdClass $campo_limpio debe tener obj->valor obj->campo
     * @param array $registro Registro con el valor encriptado
     * @return array
     */
    private function asigna_valor_encriptado(stdClass $campo_limpio, array $registro): array
    {
        $keys = array('valor','campo');
        $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $campo_limpio,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campo_limpio', data: $valida);
        }

        $keys = array('campo');
        $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $campo_limpio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campo_limpio', data: $valida);
        }

        $keys = array($campo_limpio->campo);
        $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $registro, valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $valor = (new encriptador())->encripta(valor:$campo_limpio->valor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al encriptar valor del campo', data: $valor);
        }
        $registro[$campo_limpio->campo] = $valor;
        return $registro;
    }

    private function carga_atributos(stdClass $attr, array $keys, modelo $modelo){
        foreach ($attr->registros as $atributo){
            $attrs = $this->integra_atributos(atributo: $atributo,keys:  $keys,modelo:  $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar atributos ', data: $attrs);
            }
        }
        return $modelo->atributos;
    }

    /**
     * Integra los datos para in in sql
     * @param string $llave LLave= tabla.campo
     * @param array $values_in Conjunto de valores para un in SQL
     * @return array
     * @version 1.527.51
     */
    private function data_in_sql(string $llave, array $values_in): array
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error llave no puede venir vacia', data: $llave);
        }

        if(count($values_in) === 0){
            return $this->error->error(mensaje: 'Error values_in no puede venir vacios', data: $values_in);
        }

        $in = array();
        $in['llave'] = $llave;
        $in['values'] = $values_in;
        return $in;
    }

    /**
     * Encripta los campos indicados desde modelo->campos_encriptados
     * @param string $campo Campo a validar si es aplicable a encriptar
     * @param array $campos_encriptados Conjunto de campos del modelo a encriptar
     * @param array $registro Registro a verificar
     * @param string $valor Valor a encriptar si aplica
     * @return array Registro con el campo encriptado
     */
    private function encripta_valor_registro(string $campo, array $campos_encriptados, array $registro,
                                            mixed $valor): array
    {

        if(is_iterable($valor)){
            return $this->error->error(mensaje: 'Error valor no puede ser iterable', data: $valor);
        }

        $campo = trim($campo);
        if(!is_null($valor)) {
            $valor = trim($valor);
        }

        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo no puede venir vacio', data: $campo);
        }

        $keys = array($campo);
        $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $registro,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $campo_limpio = $this->limpia_valores(campo:$campo,valor:  $valor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar valores'.$campo, data: $campo_limpio);
        }

        if(in_array($campo_limpio->campo, $campos_encriptados, true)){
            $registro = $this->asigna_valor_encriptado(campo_limpio: $campo_limpio, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo encriptado'.$campo, data: $registro);
            }
        }
        return $registro;
    }

    /**
     * Encripta los campos del modelo
     * @param array $campos_encriptados conjunto de campos a encriptar
     * @param array $registro Registro a aplicar la encriptacion
     * @return array Registro con campos encriptados
     */
    private function encripta_valores_registro(array $campos_encriptados, array $registro): array
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error el registro no puede venir vacio', data: $registro);
        }
        foreach($registro as $campo=>$valor){

            if(is_iterable($valor)){
                return $this->error->error(mensaje: 'Error valor no puede ser iterable', data: $valor);
            }

            $registro = $this->encripta_valor_registro(campo:$campo
                , campos_encriptados: $campos_encriptados,registro:  $registro,valor:  $valor);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo encriptado '.$campo, data: $registro);
            }
        }
        return $registro;
    }

    private function genera_atributos(stdClass $attr, modelo $modelo){
        $keys = array('Null','Key','Default','Extra');

        $attrs = $this->inicializa_atributos(attr: $attr,modelo:  $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar atributos ', data: $attrs);

        }

        $attrs = $this->carga_atributos(attr: $attr,keys:  $keys, modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar atributos ', data: $attrs);
        }
        return $attrs;
    }

    public function genera_data_in(string $campo, string $tabla,array $registros): array
    {
        $values_in = $this->values_in(key_value: $tabla.'_'.$campo, rows: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener values in',data:  $values_in);
        }

        $in = $this->data_in_sql(llave:$tabla.'.'.$campo, values_in: $values_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar in',data:  $in);
        }
        return $in;
    }

    /**
     * Obtiene los atributos de un modelo
     * @param modelo $modelo Modelo a obtener atributos
     * @return array|stdClass
     * @version 9.14.0
     *
     */
    private function get_atributos_db(modelo $modelo): array|stdClass
    {
        $tabla = trim($modelo->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        $sql = (new sql())->describe_table(tabla: $modelo->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sql ', data: $sql);
        }

        $attr = $modelo->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener attr ', data: $attr);
        }
        return $attr;
    }

    /**
     * @param stdClass $attr
     * @param modelo $modelo
     * @return array|stdClass
     */
    private function inicializa_atributos(stdClass $attr, modelo $modelo): array|stdClass
    {
        foreach ($attr->registros as $atributo){
            $attrs = $this->init_atributo(atributo: $atributo,modelo:  $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializar atributos ', data: $attrs);
            }
        }
        return $modelo->atributos;
    }

    public function inicializa_statuses(array $keys, array $registro): array
    {
        foreach ($keys as $key) {
            $registro = $this->aplica_status_inactivo(key: $key, registro: $registro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializa status', data: $registro);
            }
        }
        return $registro;

    }

    /**
     * Inicializa un field en attr
     * @param array $atributo Atributo
     * @param modelo $modelo Modelo en ejecucion
     * @return stdClass
     */
    private function init_atributo(array $atributo, modelo $modelo): stdClass
    {
        $field = $atributo['Field'];
        $modelo->atributos->$field = new stdClass();
        return $modelo->atributos;
    }

    /**
     * Inicializa valores booleanos
     * @version 1.148.31
     * @param array $bools conjunto de campos de tipo bool en bd activo o inactivo
     * @return array
     */
    private function init_bools(array $bools): array
    {
        $keys = array('con_label','required','ln','select_vacio_alta', 'disabled');
        foreach ($keys as $key){
            if(!isset($bools[$key])){
                $bools[$key] = '';
            }
        }
        return $bools;
    }

    /**
     * Inicializa un campo a todo vacio
     * @version 1.104.25
     * @param array $campo Campo a validar elementos
     * @return array
     */
    private function init_campo(array $campo): array
    {
        $keys = array('elemento_lista_cols','elemento_lista_tipo','elemento_lista_tabla_externa',
            'elemento_lista_etiqueta','elemento_lista_campo','elemento_lista_descripcion','elemento_lista_id');
        foreach ($keys as $key){
            if(!isset($campo[$key])){
                $campo[$key] = '';
            }
        }
        return $campo;
    }

    /**
     *
     * @param array $campo Campo a validar elementos
     * @version 1.172.33
     * @param array $bools conjunto de campos de tipo bool en bd activo o inactivo
     * @param stdClass $datos Datos a verificar
     * @return array|stdClass
     */
    private function init_data( array $bools, array $campo, stdClass $datos): array|stdClass
    {
        $campo = $this->init_campo(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa $campo',data: $campo);
        }

        $bools = $this->init_bools(bools: $bools);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa $bools',data: $bools);
        }

        $datos = $this->init_datos(datos: $datos);
        if(errores::$error){
            return $this->error->error('Error al inicializa $datos',$datos);
        }

        $data = new stdClass();
        $data->campo = $campo;
        $data->bools = $bools;
        $data->datos = $datos;
        return $data;
    }

    /**
     * Inicializa datos para campos
     * @version 1.148.31
     * @param stdClass $datos Datos a verificar
     * @return stdClass
     */
    private function init_datos(stdClass $datos): stdClass
    {
        $keys = array('llaves','vista','columnas','pattern','tabla_externa','externa_renombrada','valor_extra',
            'separador','representacion','css_id');
        foreach ($keys as $key){
            if(!isset($datos->$key)){
                $datos->$key = '';
            }
        }
        return $datos;
    }

    /** Inicializa un key a inactivo
     * @param string $key Key a integrar
     * @param array $registro Registro en proceso
     * @return array
     *
     *
     */
    private function init_key_status_inactivo(string $key, array $registro): array
    {
        $registro[$key] = 'inactivo';
        return $registro;
    }

    /**
     * Inicializacion de parametros a vacio
     * @param stdClass $complemento Complemento con datos para maquetacion de sql
     * @return stdClass
     * @version 1.258.40
     * @version 1.1.0
     * @fecha 2022-08-02 12:33
     * @author mgamboa
     */
    private function init_params(stdClass $complemento): stdClass
    {
        $complemento->params = new stdClass();
        $complemento->params->offset = '';
        $complemento->params->group_by = '';
        $complemento->params->order = '';
        $complemento->params->limit = '';
        return $complemento;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Método para inicializar los datos que serán actualizados en un modelo.
     *
     * @param int $id El identificador único del registro.
     * @param modelo $modelo Una instancia del modelo donde se realizará la actualización.
     * @param array $registro Los datos que se utilizarán para la actualización.
     * @param bool $valida_row_vacio Un parámetro opcional que sirve para validar si el registro está vacío.
     *
     * @return array|stdClass Devuelve un objeto con los datos de actualización o un array en caso de error.
     * @version 16.267.1
     */
    final public function init_upd(
        int $id, modelo $modelo, array $registro, bool $valida_row_vacio = true): array|stdClass
    {
        $registro_original = $registro;
        $registro = (new columnas())->campos_no_upd(campos_no_upd: $modelo->campos_no_upd, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar camp no upd',data: $registro);
        }


        $modelo->registro_upd = $registro;
        $modelo->registro_id = $id;

        $valida = (new validaciones())->valida_upd_base(id:$id, registro_upd: $modelo->registro_upd,
            valida_row_vacio: $valida_row_vacio);
        if(errores::$error){
            $datos = serialize($registro);
            $registro_original = serialize($registro_original);
            $mensaje = "Error al validar datos del modelo ";
            $mensaje .= $modelo->tabla." del id $id";
            $mensaje .= " registro procesado $datos";
            $mensaje .= " registro original $registro_original";
            return $this->error->error(mensaje: $mensaje, data: $valida);
        }

        $data = new stdClass();
        $data->registro_upd = $modelo->registro_upd;
        $data->id = $modelo->registro_id;

        return $data;
    }

    private function integra_attr(array $atributo, string $field, string $key, modelo $modelo){
        $key_new = $this->normaliza_key_db(key: $key);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al normalizar $key', data: $key_new);
        }

        $attr_r = $this->asigna_data_attr(atributo: $atributo,field:  $field,key:  $key, key_new: $key_new,modelo:  $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar atributo ', data: $attr_r);
        }
        return $attr_r;
    }

    final public function integra_attrs(modelo $modelo){
        if(!isset($_SESSION[$modelo->tabla]['atributos'])) {
            $attr = $this->get_atributos_db(modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener attr ', data: $attr);
            }

            $attrs = $this->genera_atributos(attr: $attr, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializar atributos ', data: $attrs);
            }
            $_SESSION[$modelo->tabla]['atributos'] = $modelo->atributos;
        }
        else{
            $attrs = $modelo->atributos = $_SESSION[$modelo->tabla]['atributos'];
        }
        return $attrs;
    }

    private function integra_atributos(array $atributo, array $keys, modelo $modelo){
        $field = $atributo['Field'];

        foreach ($keys as $key){
            $attr_r =$this->integra_attr(atributo: $atributo,field:  $field, key: $key, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar atributo ', data: $attr_r);
            }
        }
        return $modelo->atributos;
    }

    /**
     * Integra un value para ser utilizado en un IN
     * @param string $value Valor a integrar
     * @param array $values_in Valores previos
     * @return array
     * @version 1.526.51
     */
    private function integra_value_in(string $value, array $values_in): array
    {
        $values_in[] = $value;
        return $values_in;
    }


    /**
     * POR DOCUMENTAR EN WIKI
     * Este método limpia los valores entregados y los agrupa en un objeto de tipo stdClass.
     *
     * @param string $campo El nombre del campo a procesar.
     * @param string $valor El valor del campo a procesar.
     *
     * @return stdClass|array Retorna un objeto con los atributos "campo" y "valor" si todo ha ido bien,
     *                        o un error si el campo de entrada está vacío.
     *
     * @throws errores En caso de que el campo de entrada esté vacío.
     * @version 16.266.1
     */
    private function limpia_valores(string $campo, string $valor): stdClass|array
    {
        $campo = trim($campo);
        $valor = trim($valor);

        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo no puede venir vacio', data: $campo);
        }

        $data = new stdClass();
        $data->campo = $campo;
        $data->valor = $valor;
        return $data;

    }

    /**
     *
     * Funcion para maquetar un array para ser mostrado en las vistas base
     * @version 1.182.34
     * @param array $campo datos del campo
     * @param string $vista vista para su aplicacion en views
     * @param array $valor_extra  datos para anexar extras
     * @param string $representacion para su vista en lista
     * @param array $bools datos booleanos con los keys de los campos a aplicar
     * @example
     *      $campo_envio = $this->maqueta_campo_envio($campo,$vista, $valor_extra,$representacion, $bools);
     *
     * @return array con datos para su utilizacion en views
     * @throws errores por definir
     * @uses consultas_base->inicializa_estructura
     */
    public function maqueta_campo_envio(array $bools, array $campo, string $representacion, array $valor_extra,
                                        string $vista):array{


        $valida = $this->validacion->valida_campo_envio(bools: $bools, campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar campo", data: $valida);
        }

        $datos = new stdClass();

        $campo_tabla_externa = (new elementos())->campo_tabla_externa(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al obtener campo_tabla_externa",data:  $campo_tabla_externa);
        }

        $elemento_lista_columnas = (new elementos())->columnas_elemento_lista(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar columnas",data:  $elemento_lista_columnas);
        }

        $elemento_lista_llaves_valores = (new elementos())->llaves_valores(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al generar llaves", data: $elemento_lista_llaves_valores);
        }

        $elemento_lista_pattern = (new elementos())->pattern(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al generar pattern",data:  $elemento_lista_pattern);
        }


        $elemento_lista_tabla_externa_renombrada = (new elementos())->tabla_ext_renombrada(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al generar tabla externa",data:  $elemento_lista_tabla_externa_renombrada);
        }


        $elemento_lista_separador_select_columnas = (new elementos())->separador_columnas(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al generar separador",data:  $elemento_lista_separador_select_columnas);
        }


        $elemento_lista_css_id = (new elementos())->elemento_lista_css_id(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al obtener $elemento_lista_css_id",data:  $elemento_lista_css_id);
        }

        $datos->tabla_externa = $campo_tabla_externa;
        $datos->columnas = $elemento_lista_columnas;
        $datos->llaves = $elemento_lista_llaves_valores;
        $datos->pattern = $elemento_lista_pattern;
        $datos->externa_renombrada = $elemento_lista_tabla_externa_renombrada;
        $datos->separador = $elemento_lista_separador_select_columnas;
        $datos->css_id = $elemento_lista_css_id;
        $datos->vista = $vista;
        $datos->valor_extra = $valor_extra;
        $datos->representacion = $representacion;

        $datos = $this->asigna_data_campo(bools: $bools, campo: $campo, datos: $datos);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar datos",data: $datos);
        }

        return $datos;

    }

    /**
     * @param string $key
     * @return string
     */
    private function normaliza_key_db(string $key): string
    {
        $key_new = trim($key);
        $key_new = str_replace(' ','',$key_new);
        return strtolower($key_new);
    }

    /**
     * Maqueta eñ registro a insertar
     * @param array $campos_encriptados Conjunto de campos a encriptar en el guardado
     * @param bool $integra_datos_base
     * @param array $registro Registro que se insertara
     * @param string $status_default status activo o inactivo
     * @param array $tipo_campos Tipificacion de campos del modelo
     * @return array
     * @author mgamboa
     * @fecha 2022-08-01 16:08
     */
    final public function registro_ins(array $campos_encriptados, bool $integra_datos_base, array $registro,
                                       string $status_default, array $tipo_campos): array
    {
        $status_default = trim($status_default);
        if($status_default === ''){
            return $this->error->error(mensaje: 'Error status_default no puede venir vacio', data: $status_default);
        }

        $registro = $this->status(integra_datos_base: $integra_datos_base, registro: $registro,
            status_default:  $status_default);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar status ', data: $registro);
        }

        $registro = (new data_format())->ajusta_campos_moneda(registro: $registro, tipo_campos: $tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar campo ', data: $registro);
        }

        $registro = $this->encripta_valores_registro(campos_encriptados: $campos_encriptados,
            registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar campos encriptados', data: $registro);
        }

        return $registro;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Método privado que valida y realiza asignaciones a un objeto stdClass en base a la key proporcionada.
     *
     * @param string $campo        El nombre del campo del stdClass que se quiere validar y asignar.
     * @param stdClass $registro_previo   Objeto stdClass que será validado y modificado.
     *
     * @return stdClass|array   Devuelve el objeto stdClass modificado o un arreglo de errores.
     *
     * @throws errores   Si el $campo proporcionado está vacío.
     * @version 16.275.1
     */
    private function registro_previo_null(string $campo, stdClass $registro_previo): stdClass|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        if(!isset($registro_previo->$campo)){
            $registro_previo->$campo = '';
        }
        if(is_null($registro_previo->$campo)){
            $registro_previo->$campo = '';
        }
        return $registro_previo;

    }

    /**
     * Inicializa el resultado en warning cuando no hay elementos a modificar
     * @param int $id Identificador del registro
     * @param array $registro_upd Registro limpio en upd
     * @param stdClass $resultado Resultado previamente inicializado
     * @return stdClass
     */
    final public function result_warning_upd(int $id, array $registro_upd, stdClass $resultado): stdClass
    {
        $mensaje = 'Info no hay elementos a modificar';
        $resultado->mensaje = $mensaje;
        $resultado->sql = '';
        $resultado->result = '';
        $resultado->registro = $registro_upd;
        $resultado->registro_id = $id;
        $resultado->salida = 'warning';

        return $resultado;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Establece el estado de un registro.
     *
     * Este método ajusta el estado de un registro en base a diversas condiciones:
     * - Si el valor de 'status' no se encuentra dentro del registro, y se desea integrar datos de la base,
     *   se establecerá el estado del registro a 'status_default'.
     * - De lo contrario, se conservará el estado existente en el registro.
     *
     * @param bool $integra_datos_base Define si se integrarán datos de la base en el registro actual.
     * @param array $registro Registro en proceso.
     * @param string $status_default Estado predeterminado en caso que no exista 'status' en el registro.
     *
     * @return array Registro con el estado ajustado.
     *
     * @throws errores Si el parámetro 'status_default' contiene una cadena vacía.
     *
     * @version 15.6.0
     * @fecha 2022-11-02 10:20
     * @author Martin Gamboa
     */
    private function status(bool $integra_datos_base, array $registro, string $status_default): array
    {
        $status_default = trim($status_default);
        if($status_default === ''){
            return $this->error->error(mensaje: 'Error status_default no puede venir vacio', data: $status_default);
        }

        if(!isset($registro['status'])){
            if($integra_datos_base){
                $registro['status'] = $status_default;
            }
        }
        return $registro;
    }

    /**
     * POR DOCUMENTAR WIKI
     * Recupera las columnas de la tabla especificada en el modelo dado.
     *
     * Esta función está diseñada para tomar un objeto modelo_base, obtener el nombre de la tabla del modelo y
     * luego obtener la estructura de columnas de esa tabla especificada en la consulta SQL base. Si no se
     * encuentran columnas para la tabla, la función devuelve un error. En caso contrario, devuelve las columnas
     *
     * @final
     *
     * @param modelo_base $modelo Un objeto de la clase modelo_base. Se esperaría que el objeto tenga una propiedad
     *  'tabla', que contenga el nombre de la tabla.
     *
     * @return array - Devuelve un array con las columnas de la tabla.
     *                Si la tabla no tiene columnas, devuelve un error.
     *
     * @version 13.5.0
     */
    final public function tablas_select(modelo_base $modelo): array
    {
        // Quita el NAMESPACE del nombre de la tabla
        $tabla_sin_namespace = str_replace($modelo->NAMESPACE, '', $modelo->tabla);
        $modelo->tabla = $tabla_sin_namespace;

        // Crea una nueva instancia de consulta SQL
        $consulta_base = new sql_bass();

        // Asigna las columnas del modelo a la estructura de la consulta SQL
        $consulta_base->estructura_bd[$modelo->tabla]['columnas'] = $modelo->columnas;

        $columnas_tabla = $consulta_base->estructura_bd[$modelo->tabla]['columnas'];

        // Si no hay columnas para la tabla, devuelve un error
        if (!isset($columnas_tabla)) {
            return $this->error->error(mensaje: 'No existen columnas para la tabla ' . $modelo->tabla,
                data: $modelo->tabla);
        }
        // Si las columnas están presentes, las devuelve
        return $columnas_tabla;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Desencripta el valor del campo proporcionado si está en la lista de campos encriptados.
     *
     * El método verifica si el nombre del campo está dentro de la lista de campos encriptados.
     * Si el campo está en la lista, el valor se desencripta utilizando el método desencripta de la clase encriptador.
     *
     * @param string $campo El nombre del campo a verificar y desencriptar si corresponde.
     * @param array $campos_encriptados Lista de nombres de campos que están encriptados.
     * @param mixed $value El valor a desencriptar.
     *
     * @return string|null|array El valor desencriptado si el campo está dentro de la lista de campos encriptados,
     * o el propio valor si no lo está. En caso de error, devuelve un array con la información del error.
     *
     * @throws errores Si el valor no se puede desencriptar.
     *
     * @author Martin Gamboa
     * @version 14.4.0
     */
    private function value_desencriptado(string $campo, array $campos_encriptados, mixed $value): array|string|null
    {
        if(is_numeric($campo)){
            $fix = ' El campo debe ser un texto no numerico puede ser id, registro etc, no puede ser 0 o 1 o cualquier 
            numero';
            return $this->error->error(mensaje: 'Error el campo debe ser un texto', data:$campo, fix: $fix);
        }
        $value_enc = $value;

        if(in_array($campo, $campos_encriptados, true)){
            $value_enc = (new encriptador())->desencripta(valor: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al desencriptar', data:$value_enc);
            }
        }
        return $value_enc;
    }

    private function values_in(string $key_value, array $rows): array
    {
        $values_in = array();

        foreach ($rows as $row){
            $value = $row[$key_value];
            $values_in = $this->integra_value_in(value:$value,values_in:  $values_in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar values in', data:$values_in);
            }
        }
        return $values_in;
    }


}
