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
     * Ajusta los campos de un registro si estos no tienen campo los quitar del upd
     * @version 1.79.17
     * @param int $id Identificador del registro
     * @param modelo $modelo Modelo en ejecucion
     * @return array Registro ajustado
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
     * Ajusta los elementos de un row a modificar para comparar los campos con valores que ya esten en base de datos
     * no seas actualizados
     * @version 1.78.18
     * @param string $campo Campo del modelo en ejecucion
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $registro_previo Registro antes de ser modificado
     * @param string|null $value_upd Valor que se pretende modificar
     * @return array
     */
    private function ajusta_registro_upd(string $campo, modelo $modelo, stdClass $registro_previo,
                                        string|null $value_upd): array
    {
        $value_upd = trim($value_upd);
        $campo = trim($campo);

        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio', data:$campo);
        }

        if(!isset($registro_previo->$campo)){
            $registro_previo->$campo = '';
        }
        if(is_null($registro_previo->$campo)){
            $registro_previo->$campo = '';
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
     * Desencripta un conjunto de valores de un registro
     * @version 1.18.9
     * @param array $campos_encriptados Campos a desencriptar del registro
     * @param array $row Registro para la desencriptacion
     * @return array Registro con los campos aplicables desencriptados
     */
    public function asigna_valor_desencriptado(array $campos_encriptados, array $row): array
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
     * @version 1.0.0
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
     * @version 1.0.0
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
     * @version 1.0.0
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

    private function inicializa_atributos(stdClass $attr, modelo $modelo){
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
     * Inicializa los datos minimos necesarios para un upd
     * @version 1.77.17
     * @param int $id Identificador del modelo a actualizar
     * @param modelo $modelo Modelo en ejecucion
     * @param array $registro Registro a con datos a actualizar
     * @return array|stdClass
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-08-08 13:17
     */
    final public function init_upd(int $id, modelo $modelo, array $registro): array|stdClass
    {
        $registro = (new columnas())->campos_no_upd(campos_no_upd: $modelo->campos_no_upd, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar camp no upd',data: $registro);
        }


        $modelo->registro_upd = $registro;
        $modelo->registro_id = $id;

        $valida = (new validaciones())->valida_upd_base(id:$id, registro_upd: $modelo->registro_upd);
        if(errores::$error){
            return $this->error->error('Error al validar datos',$valida);
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
     * Funcion que limpia los valores quita elementos iniciales y finales no imprimibles
     * @version 1.0.0
     * @param string $campo Campo del registro del modelo a limpiar
     * @param string $valor Valor del registro en el campo indicado
     * @return stdClass|array
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

    private function normaliza_key_db(string $key): string
    {
        $key_new = trim($key);
        $key_new = str_replace(' ','',$key_new);
        return strtolower($key_new);
    }

    /**
     * Maqueta eñ registro a insertar
     * @param array $campos_encriptados Conjunto de campos a encriptar en el guardado
     * @param array $registro Registro que se insertara
     * @param string $status_default status activo o inactivo
     * @param array $tipo_campos Tipificacion de campos del modelo
     * @return array
     * @version 1.241.39
     * @verfuncion 1.1.0
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

        $registro = $this->status(integra_datos_base: $integra_datos_base, registro: $registro,status_default:  $status_default);
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
     * Inicializa el resultado en warning cuando no hay elementos a modificar
     * @version 1.79.17
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
     * Asigna a un registro status default
     * @param bool $integra_datos_base si true integra campos base
     * @param array $registro registro a insertar
     * @param string $status_default status = activo o inactivo
     * @return array
     * @version 1.0.0
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
     * Obtiene las tablas base para ejecutar una consulta en select
     * @version 1.0.0
     * @param modelo_base $modelo Modelo para obtencion de nombre de tabla
     * @return array
     */
    final public function tablas_select(modelo_base $modelo): array
    {
        $modelo->tabla = str_replace($modelo->NAMESPACE,'',$modelo->tabla);

        $consulta_base = new sql_bass();
        $consulta_base->estructura_bd[$modelo->tabla]['columnas'] = $modelo->columnas;

        return $consulta_base->estructura_bd[$modelo->tabla]['columnas'] ??
            $this->error->error(mensaje: 'No existen columnas para la tabla ' . $modelo->tabla, data: $modelo->tabla);
    }

    /**
     * Desencripta un valor de un campo seleccionado dentro de un conjunto de registros
     * @version 1.15.9
     * @param string $campo Campo a desencriptar de array
     * @param array $campos_encriptados Campos definidos como encriptables de un registro
     * @param mixed $value Valor a desencriptar en caso de que aplique el camp dentro de los campos encriptados
     * @return array|string|null
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
