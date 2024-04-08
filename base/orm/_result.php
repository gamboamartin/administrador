<?php

namespace base\orm;

use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use PDOStatement;
use stdClass;

class _result
{
    private errores $error;
    private validaciones $validacion;

    public function __construct()
    {
        $this->error = new errores();
        $this->validacion = new validaciones();

    }

    /**
     * Ajusta el contenido de un registro asignando valores encriptados y elementos con dependencia basada en modelos
     * hijos
     * @param array $campos_encriptados Conjunto de campos a encriptar desencriptar declarados en el modelo en ejecucion
     * @param array $modelos_hijos Conjunto de modelos que dependen del modelo en ejecucion
     * @param array $row Registro a integrar elementos encriptados o con dependientes
     * @return array Registro con los datos ajustados tanto en la encriptacion como de sus dependientes
     */
    private function ajusta_row_select(array $campos_encriptados, modelo_base $modelo_base, array $modelos_hijos,
                                       array $row): array
    {
        $row = (new inicializacion())->asigna_valor_desencriptado(campos_encriptados: $campos_encriptados,
            row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al desencriptar', data:$row);
        }


        if(count($modelos_hijos)>0) {
            $row = $this->genera_registros_hijos(modelo_base: $modelo_base, modelos_hijos: $modelos_hijos, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: "Error en registro",data: $row);
            }
        }
        return $row;
    }

    /**
     * Asigna registros hijos al modelo dado según el filtro proporcionado.
     *
     * @param array  $filtro El filtro a aplicar al conjunto de registros.
     * @param string $name_modelo Nombre del modelo.
     * @param string $namespace_model Namespace del modelo.
     * @param string $nombre_estructura Nombre de la estructura en la que se asignarán los registros.
     * @param array  $row El array al cual se asignarán los registros del modelo.
     *
     * @return array Retorna un array con los registros asignados, o un error si algo sale mal.
     */
    private function asigna_registros_hijo(array $filtro, modelo_base $modelo_base, string $name_modelo,
                                           string $namespace_model, string $nombre_estructura, array $row):array{
        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar entrada para modelo',data: $valida);
        }
        if($nombre_estructura === ''){
            return  $this->error->error(mensaje: 'Error nombre estructura no puede venir vacia',
                data: $nombre_estructura,es_final: true);
        }

        $modelo = $modelo_base->genera_modelo(modelo: $name_modelo, namespace_model: $namespace_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data: $modelo);
        }
        $data = $modelo->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar registro hijos', data: $data);
        }
        $row[$nombre_estructura] = $data->registros;


        return $row;
    }

    /**
     * Método data_result de la clase modelo_base
     *
     * Este método procesa los resultados obtenidos de una consulta SQL, a partir de
     * una lista de campos encriptados y columnas con totales. Además, verifica que la
     * consulta no esté vacía y maneja posibles errores en el proceso.
     *
     * @param array $campos_encriptados Lista de campos que han sido encriptados
     * @param array $columnas_totales Lista de columnas que contienen totales
     * @param string $consulta La consulta SQL a ejecutar
     *
     * @throws errores Si la consulta está vacía, si hay errores al ejecutar la consulta SQL
     * o al parsear los registros
     *
     * @return array|stdClass Si no hay errores, retorna los datos obtenidos.
     * Si hay errores, retorna los mensajes de error correspondientes
     *
     * Algoritmo:
     * 1. Se verifica que la consulta SQL no esté vacía
     * 2. Si la consulta está vacía, se devuelve un mensaje de error
     * 3. De lo contrario, se ejecuta la consulta SQL
     * 4. Si hay errores al ejecutar la consulta SQL, se devuelve un mensaje de error
     * 5. Se obtienen y maquetan los resultados de la consulta SQL
     * 6. Si hay errores al parsear los registros, se devuelve un mensaje de error
     * 7. Si no hay errores, se retornan los datos obtenidos
     *
     */
    final public function data_result(array $campos_encriptados,array $columnas_totales, string $consulta,
                                 modelo_base $modelo): array|stdClass
    {
        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$modelo->tabla,
                es_final: true);
        }
        $result_sql = $this->result_sql(campos_encriptados: $campos_encriptados,
            columnas_totales: $columnas_totales, consulta: $consulta,modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al ejecutar sql", data: $result_sql);
        }

        $data = $this->maqueta_result(consulta: $consulta, modelo: $modelo,
            n_registros: $result_sql->n_registros, new_array: $result_sql->new_array, totales_rs: $result_sql->totales);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $data);
        }
        return $data;
    }

    /**
     * Maqueta un arreglo para la generacion de modelos y sus registros asignados a un query para obtener sus
     * dependientes o dependencias
     * de la siguiente forma $registro['tabla']= $reg[0][campos de registro], $reg[n][campos de registro]
     *

     *
     * @example
     *      $modelos_hijos = $this->genera_modelos_hijos();
    if(isset($modelos_hijos['error'])){
     *          return $this->error->error('Error al generar $modelos_hijos',
     *          __LINE__,__FILE__,$modelos_hijos);
     *      }
     *
     *
     * @return array
     *      $modelos_hijos[$key]['filtros']= $modelo['filtros'];
     *      $modelos_hijos[$key]['filtros_con_valor']= $modelo['filtros_con_valor'];
     *      $modelos_hijos[$key]['nombre_estructura']= $modelo['nombre_estructura'];
     * @throws errores $this->hijo[$key] key debe ser un txt con nombre del campo a asignar
     * @throws errores $this->hijo[$key][filtros] filtros debe existir
     * @throws errores $this->hijo[$key][filtros_con_valor] filtros_con_valor debe existir
     * @throws errores $this->hijo[$key][filtros] debe ser un array
     * @throws errores $this->hijo[$key][filtros_con_valor] debe ser un array
     * @throws errores $this->hijo[$key][nombre_estructura] debe existir
     */
    private function genera_modelos_hijos(modelo_base $modelo): array{
        $modelos_hijos = array() ;
        foreach($modelo->hijo as $key=>$modelo){
            if(is_numeric($key)){
                return $this->error->error(mensaje: "Error en key",data: $modelo->hijo, es_final: true);
            }
            if(!isset($modelo['filtros'])){
                return $this->error->error(mensaje: "Error filtro",data: $modelo->hijo, es_final: true);
            }
            if(!isset($modelo['filtros_con_valor'])){
                return $this->error->error(mensaje:"Error filtro",data:$modelo->hijo, es_final: true);
            }
            if(!is_array($modelo['filtros'])){
                return $this->error->error(mensaje:"Error filtro",data:$modelo->hijo, es_final: true);
            }
            if(!is_array($modelo['filtros_con_valor'])){
                return $this->error->error(mensaje:"Error filtro",data:$modelo->hijo, es_final: true);
            }
            if(!isset($modelo['nombre_estructura'])){
                return $this->error->error(mensaje:"Error en estructura",data:$modelo->hijo, es_final: true);
            }

            $modelos_hijos[$key]['filtros']= $modelo['filtros'];
            $modelos_hijos[$key]['filtros_con_valor']= $modelo['filtros_con_valor'];
            $modelos_hijos[$key]['nombre_estructura']= $modelo['nombre_estructura'];
            $modelos_hijos[$key]['namespace_model']= $modelo['namespace_model'];
        }
        return $modelos_hijos;
    }

    /**
     * Genera un registro hijo.
     *
     * Este método se encarga de generar un registro hijo a partir de los datos proporcionados en
     * $data_modelo y $row. De verificarse errores durante este proceso se retorna una anomalía
     * con detalles del error.
     *
     * @param array $data_modelo Datos del modelo.
     * @param string $name_modelo Nombre del modelo.
     * @param array $row Registro actual.
     * @return array Resultado de proceso.
     */
    private function genera_registro_hijo(array $data_modelo, modelo_base $modelo_base, string $name_modelo,
                                          array $row):array{

        $keys = array('nombre_estructura','namespace_model');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $data_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar data_modelo",data: $valida);
        }

        if(!isset($data_modelo['nombre_estructura'])){
            return $this->error->error(mensaje: 'Error debe existir $data_modelo[\'nombre_estructura\'] ',
                data: $data_modelo, es_final: true);
        }
        $filtro = (new rows())->obten_filtro_para_hijo(data_modelo: $data_modelo,row: $row);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error filtro",data: $filtro);
        }
        $row = $this->asigna_registros_hijo(filtro: $filtro, modelo_base: $modelo_base,
            name_modelo: $name_modelo, namespace_model: $data_modelo['namespace_model'],
            nombre_estructura: $data_modelo['nombre_estructura'], row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar registros de hijo', data: $row);
        }
        return $row;
    }

    /**
     * Funcion que asigna y genera los registros encontrados de hijos en un registro
     * @param array $modelos_hijos datos de parametrizacion de datos para la ejecucion de obtencion de los registros
     * @param array $row registro padre al que se le asignaran los hijos
     * @example
     *      $row = (array) $row;
     *      $row = $this->genera_registros_hijos($modelos_hijos,$row);
     * @return array registro del modelo con registros hijos asignados
     * @throws errores $data_modelo['nombre_estructura'] no existe
     */
    private function genera_registros_hijos(modelo_base $modelo_base, array $modelos_hijos, array $row):array{
        foreach($modelos_hijos as $name_modelo=>$data_modelo){
            if(!is_array($data_modelo)){
                $fix = '$modelos_hijos debe ser un array asociativo de la siguiente forma';
                $fix.= ' $modelos_hijos[name_modelo][nombre_estructura] = nombre d ela tabla dependiente';
                $fix.= ' $modelos_hijos[name_modelo][filtros] = array() con configuracion de filtros';
                $fix.= ' $modelos_hijos[name_modelo][filtros_con_valor] = array() con configuracion de filtros';
                return $this->error->error(mensaje: "Error en datos",data: $modelos_hijos, fix: $fix);
            }
            $keys = array('nombre_estructura','namespace_model');
            $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $data_modelo);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al validar data_modelo",data: $valida);
            }

            if(!isset($data_modelo['nombre_estructura'])){
                return  $this->error->error(mensaje: 'Error debe existir $data_modelo[\'nombre_estructura\'] ',
                    data: $data_modelo, es_final: true);
            }
            if(!is_string($name_modelo)){
                $fix = '$modelos_hijos debe ser un array asociativo de la siguiente forma';
                $fix.= ' $modelos_hijos[name_modelo][nombre_estructura] = nombre d ela tabla dependiente';
                $fix.= ' $modelos_hijos[name_modelo][filtros] = array() con configuracion de filtros';
                $fix.= ' $modelos_hijos[name_modelo][filtros_con_valor] = array() con configuracion de filtros';
                $this->error->error(mensaje: 'Error $name_modelo debe ser un string ', data: $data_modelo,
                    es_final: true, fix: $fix);
            }

            $row = $this->genera_registro_hijo(data_modelo: $data_modelo, modelo_base: $modelo_base,
                name_modelo: $name_modelo, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar registros de hijo', data: $row);
            }

        }

        return $row;
    }

    /**
     * Inicializa la base del resultado a partir de la consulta, el número de registros y los registros obtenidos.
     *
     * @param string $consulta       La consulta SQL que se ejecutó para obtener los registros
     * @param int $n_registros       El número total de registros devueltos por la consulta
     * @param array $new_array       Los registros obtenidos por la consulta
     *
     * @return stdClass              Retorna un objeto que contiene los registros, el número de registros y la consulta SQL
     *
     *
     */
    private function init_result_base(string $consulta, modelo_base $modelo, int $n_registros, array $new_array,
                                      stdClass $totales_rs): stdClass
    {

        $modelo->registros = $new_array;
        $modelo->n_registros = $n_registros;
        $modelo->sql = $consulta;
        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = $n_registros;
        $data->sql = $consulta;
        $data->totales = $totales_rs;

        return $data;
    }

    /**
     * Maqueta el arreglo de registros de un modelo de base de datos
     *
     * Este método recorre una lista de registros devueltos de una consulta SQL
     * y los ajusta según los campos específicos de cada modelo. Después,
     * retorna un nuevo arreglo ajustado.
     *
     * @param array $modelos_hijos Los modelos dependientes del modelo principal
     * @param PDOStatement $r_sql Un objeto de consulta SQL que contiene los datos
     * @param array $campos_encriptados Lista de campos a encriptar
     * @return array Retorna un arreglo de registros ajustado
     * @throws errores Si hay un error, produce una excepción con los detalles del error
     */
    private function maqueta_arreglo_registros(array $campos_encriptados, modelo_base $modelo_base,
                                               array $modelos_hijos, PDOStatement $r_sql):array{
        $new_array = array();
        while( $row = $r_sql->fetchObject()){
            $row = (array) $row;

            $row_new = $this->ajusta_row_select(campos_encriptados: $campos_encriptados,
                modelo_base: $modelo_base, modelos_hijos: $modelos_hijos, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar rows', data:$row_new);
            }

            $new_array[] = $row_new;
        }

        return $new_array;
    }

    /**
     * Método maqueta_result de la clase modelo_base
     *
     * Este método recibe una consulta SQL, el número de registros obtenidos,
     * un arreglo con los nuevos resultados y los resultados obtenidos.
     *
     * @param string $consulta La consulta SQL a ejecutar
     * @param int $n_registros El número de registros obtenidos
     * @param array $new_array Arreglo donde se almacenan los nuevos resultados
     * @param stdClass $totales_rs Los resultados obtenidos previamente acumulables
     *
     * @throws errores Si surgen problemas al parsear el resultado o los registros
     *
     * @return array|stdClass Si no hay errores, retorna los nuevos datos obtenidos.
     * Si hay errores, retorna los mensajes de error correspondientes
     *
     * Algoritmo:
     * 1. Se inicializa el resultado base con los parámetros recibidos
     * 2. Si hay errores en la inicialización, se devuelve un mensaje de error
     * 3. Se obtienen los nuevos resultados con los parámetros recibidos
     * 4. Si hay errores al obtener los nuevos resultados, se devuelve un mensaje de error
     * 5. Si no hay errores, se devuelven los nuevos datos obtenidos
     */
    private function maqueta_result(string $consulta, modelo_base $modelo, int $n_registros, array $new_array, stdClass $totales_rs ): array|stdClass
    {
        $init = $this->init_result_base(consulta: $consulta, modelo: $modelo, n_registros: $n_registros,
            new_array: $new_array, totales_rs: $totales_rs);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear resultado", data: $init);
        }

        $data = $this->result(consulta: $consulta, modelo: $modelo, n_registros: $n_registros,
            new_array: $new_array, totales_rs: $totales_rs);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $new_array);
        }
        return $data;
    }

    /**
     * Procesa los registros devueltos por una declaración SQL de PDO
     *
     * Esta función toma una declaración SQL de PDO y un arreglo de campos encriptados.
     * Genera modelos hijos y reformatea el arreglo de registros.
     *
     * @param PDOStatement $r_sql Declaración SQL de PDO.
     * @param array $campos_encriptados Un arreglo de campos para encriptar.
     * @return array Un arreglo con los registros procesados.
     * @throws errores Si hay un error al generar modelos hijos o al generar el arreglo de registros.
     */
    private function parsea_registros_envio(array $campos_encriptados, modelo_base $modelo_base,
                                            PDOStatement $r_sql):array{

        $modelos_hijos = $this->genera_modelos_hijos(modelo: $modelo_base);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al general modelo",data: $modelos_hijos);
        }
        $new_array = $this->maqueta_arreglo_registros(campos_encriptados: $campos_encriptados, modelo_base: $modelo_base,
            modelos_hijos: $modelos_hijos, r_sql: $r_sql);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al generar arreglo con registros',data: $new_array);
        }

        return $new_array;
    }

    /**
     * La función "result" ajusta el resultado de una consulta y lo devuelve en un formato específico.
     *
     * @param string $consulta La consulta SQL que se ejecutó.
     * @param int $n_registros El número de registros devueltos por la consulta.
     * @param array $new_array Un array que contiene los registros devueltos por la consulta.
     * @return object $data Un objeto que contiene los resultados de la consulta.
     * $data tiene las siguientes propiedades:
     * registros: array de registros devueltos por la consulta en formato de array asociativo.
     * n_registros: número de registros devueltos por la consulta.
     * sql: la consulta SQL que se ejecutó.
     * campos_entidad: una lista de los campos de la entidad con la que se está trabajando.
     * registros_obj: array de registros devueltos por la consulta en formato de objeto.
     *
     * La función convierte cada fila de $new_array de un array asociativo a un objeto y lo almacena en $data->registros_obj.
     */
    private function result(string $consulta, modelo_base $modelo, int $n_registros, array $new_array, stdClass $totales_rs): stdClass
    {

        $campos_entidad = $modelo->campos_entidad;

        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = (int)$n_registros;
        $data->sql = $consulta;
        $data->campos_entidad = $campos_entidad;
        $data->totales = $totales_rs;


        $data->registros_obj = array();
        foreach ($data->registros as $row) {
            $row_obj = (object)$row;
            $data->registros_obj[] = $row_obj;
        }
        return $data;
    }


    /**
     * Ejecuta una consulta SQL y devuelve los registros obtenidos
     *
     * Esta función toma una consulta SQL y un arreglo de campos encriptados.
     * Luego, ejecuta la consulta, procesa los resultados y regresa un objeto con los datos y los registros procesados.
     *
     * @param array $campos_encriptados Un arreglo de campos para encriptar.
     * @param string $consulta La consulta SQL a ejecutar.
     * @return array|stdClass Un objeto con los datos y registros procesados, o un mensaje de error en caso de falla.
     * @throws errores Si la consulta está vacía, hay un error al ejecutar la consulta SQL, o hay un error al procesar los registros.
     */
    private function result_sql(array $campos_encriptados, array $columnas_totales, string $consulta,
                                modelo_base $modelo): array|stdClass
    {
        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$modelo->tabla);
        }
        $result = $modelo->ejecuta_sql(consulta: $consulta);

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }

        $r_sql = $result->result;

        $new_array = $this->parsea_registros_envio(campos_encriptados: $campos_encriptados, modelo_base: $modelo,
            r_sql: $r_sql);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $new_array);
        }


        $totales_rs = $this->totales_rs(columnas_totales: $columnas_totales,new_array:  $new_array);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear totales_rs", data: $totales_rs);
        }


        $n_registros = $r_sql->rowCount();
        $r_sql->closeCursor();

        $data = new stdClass();
        $data->result = $result;
        $data->r_sql = $r_sql;
        $data->new_array = $new_array;
        $data->n_registros = $n_registros;
        $data->totales = $totales_rs;
        return $data;
    }

    /**
     * Esta función acumula los totales en un campo específico.
     *
     * @param string $campo El nombre del campo en el que se acumulan los totales.
     * @param array $row Los datos de la fila actual.
     * @param stdClass $totales_rs El objeto en el que se guardan los totales acumulados.
     *
     * @return stdClass|array Devuelve un objeto con los totales acumulados.
     * Si hay un error, devuelve un array con los detalles del error.
     */
    private function total_rs_acumula(string $campo, array $row, stdClass $totales_rs): stdClass|array
    {

        $valida = $this->valida_totales(campo: $campo, row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row',data: $valida);
        }

        if(!isset($totales_rs->$campo)){
            $totales_rs->$campo = 0;
        }

        $totales_rs->$campo += $row[$campo];
        return $totales_rs;

    }

    /**
     * Esta función calcula y acumula el total para un campo específico.
     *
     * @param string       $campo      El nombre del campo para el que se va a calcular el total.
     * @param array        $new_array  Conjunto de datos que posiblemente contienen valores para el campo especificado.
     * @param stdClass     $totales_rs Objeto que almacena los totales acumulados para los diferentes campos.
     *
     * @return stdClass|array    Devuelve el objeto $totales_rs que contiene el total acumulado para el campo especificado.
     *
     * @throws errores   Lanza una excepción si el campo específico está vacío o si ocurre un error al acumular el total.
     */
    private function total_rs_campo(string $campo, array $new_array, stdClass $totales_rs): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo, es_final: true);
        }
        $totales_rs->$campo = 0;
        $totales_rs = $this->totales_rs_acumula(campo: $campo,new_array:  $new_array,totales_rs:  $totales_rs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error acumular total',data: $totales_rs);
        }
        return $totales_rs;

    }

    /**
     * Esta función acumula total de respuestas por campo.
     *
     * A través del método se recorren las columnas totales y se acumula el total de
     * respuestas por campo específico utilizando el método total_rs_campo.
     *
     * @param array $columnas_totales Las columnas que se desean totalizar.
     * @param array $new_array Los datos que se desean procesar.
     *
     * @return stdClass|array Devuelve un objeto con los totales por campo,
     * o devuelve un error si uno ocurre durante la acumulación.
     */
    private function totales_rs(array $columnas_totales, array $new_array): stdClass|array
    {
        $totales_rs = new stdClass();
        foreach ($columnas_totales as $campo){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo, es_final: true);
            }

            $totales_rs = $this->total_rs_campo(campo: $campo,new_array:  $new_array,totales_rs:  $totales_rs);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error acumular total',data: $totales_rs);
            }
        }
        return $totales_rs;

    }

    /**
     * Acumula los totales de un conjunto de datos.
     *
     * Este método recorre un array y acumula los totales para un campo específico.
     * Este campo y el array son pasados como argumentos al método.
     *
     * @param string $campo El nombre del campo para el cual se acumularán los totales.
     * @param array $new_array El array de datos que se recorrerá para acumular los totales.
     * @param stdClass $totales_rs Objeto en el que se almacenarán los totales acumulados.
     *
     * @return array|stdClass Retorna el objeto totales_rs con los totales acumulados,
     *                  o retorna el error si surge algún problema durante el proceso.
     *
     */
    private function totales_rs_acumula(string $campo, array $new_array, stdClass $totales_rs): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo, es_final: true);
        }
        foreach ($new_array as $row){
            if(!is_array($row)){
                return $this->error->error(mensaje: 'Error row debe ser un array',data: $row, es_final: true);
            }
            $valida = $this->valida_totales(campo: $campo, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar row',data: $valida);
            }
            $totales_rs = $this->total_rs_acumula(campo: $campo,row:  $row,totales_rs:  $totales_rs);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error acumular total',data: $totales_rs);
            }
        }
        return $totales_rs;

    }


    /**
     * Valida si el valor correspondiente al campo proporcionado en la fila es numérico.
     *
     * @param string $campo El nombre del campo para verificar.
     * @param array $row Un arreglo que contiene los datos de la fila.
     *
     * @return true|array Retorna verdadero si el valor es numérico, de lo contrario, un mensaje de error.
     *
     * @throws errores Si el campo está vacío o no existe en la fila.
     */
    private function valida_totales(string $campo, array $row): true|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo, es_final: true);
        }
        if(!isset($row[$campo])){
            return $this->error->error(mensaje: 'Error row['.$campo.'] NO EXISTE',data: $row, es_final: true);
        }
        if(!is_numeric($row[$campo])){
            return $this->error->error(mensaje: 'Error row['.$campo.'] no es un numero valido',data: $row,
                es_final: true);
        }
        return true;

    }

}
