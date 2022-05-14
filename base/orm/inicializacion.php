<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class inicializacion{

    private errores $error;
    private validaciones $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * P ORDER P INT ERRORREV
     * @param stdClass $complemento
     * @return array|stdClass
     */
    public function ajusta_params(stdClass $complemento): array|stdClass
    {
        if(!isset($complemento->params)){
            $complemento = $this->init_params(complemento: $complemento);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar params',data: $complemento,
                    params: get_defined_vars());
            }
        }
        return $complemento;
    }

    /**
     * P INT P ORDER
     * @param array $campo
     * @param array $bools
     * @param stdClass $datos
     * @return array
     */
    private function asigna_data_campo(array $bools, array $campo, stdClass $datos): array
    {

        $datas = $this->init_data(bools:  $bools, campo: $campo,datos:  $datos);
        if(errores::$error){
            return $this->error->error('Error al inicializa $datos',$datas);
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


        $data['cols'] = $datas->campo['elemento_lista_cols'];
        $data['disabled'] = $datas->campo['disabled'];
        $data['con_label'] = $datas->bools['con_label'];
        $data['required'] = $datas->bools['required'];
        $data['tipo'] = $datas->campo['elemento_lista_tipo'];
        $data['llaves_foraneas'] = $datas->datos->llaves;
        $data['vista'] = array($datas->datos->vista);
        $data['ln'] = $datas->bools['ln'];
        $data['tabla_foranea'] = $datas->campo['elemento_lista_tabla_externa'];
        $data['columnas'] = $datas->datos->columnas;
        $data['pattern'] = $datas->datos->pattern;
        $data['select_vacio_alta'] = $datas->bools['select_vacio_alta'];
        $data['etiqueta'] = $datas->campo['elemento_lista_etiqueta'];
        $data['campo_tabla_externa'] = $datas->datos->tabla_externa;
        $data['campo_name'] = $datas->campo['elemento_lista_campo'];
        $data['campo'] = $datas->campo['elemento_lista_descripcion'];
        $data['tabla_externa_renombrada'] = $datas->datos->externa_renombrada;
        $data['data_extra'] = $datas->datos->valor_extra;
        $data['separador_select_columnas'] = $datas->datos->separador;
        $data['representacion'] = $datas->datos->representacion;
        $data['css_id'] = $datas->datos->css_id;
        $data['elemento_lista_id'] =$datas->campo['elemento_lista_id'];

        return $data;
    }


    /**
     * PROBADA P ORDER P INT
     * @param array $bools
     * @return array
     */
    PUBLIC function init_bools(array $bools): array
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
     * PROBADA P ORDER P INT
     * @param array $campo
     * @return array
     */
    PUBLIC function init_campo(array $campo): array
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
     * P INT P ORDER
     * @param array $campo
     * @param array $bools
     * @param stdClass $datos
     * @return array|stdClass
     */
    private function init_data( array $bools, array $campo, stdClass $datos): array|stdClass
    {
        $campo = $this->init_campo(campo: $campo);
        if(errores::$error){
            return $this->error->error('Error al inicializa $campo',$campo);
        }

        $bools = $this->init_bools(bools: $bools);
        if(errores::$error){
            return $this->error->error('Error al inicializa $bools',$bools);
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
     * PROBADA P ORDER P INT
     * @param stdClass $datos
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

    /**
     * P ORDER P INT ERRORREV
     * @param stdClass $complemento
     * @return stdClass
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
     * P INT P ORDER
     * Funcion para maquetar un array para ser mostrado en las vistas base
     *
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
    public function maqueta_campo_envio(array $bools, array $campo, string $representacion, array $valor_extra, string $vista):array{


        $valida = $this->validacion->valida_campo_envio(bools: $bools, campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al validar campo", $valida);
        }

        $datos = new stdClass();

        $campo_tabla_externa = (new elementos())->campo_tabla_externa(campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al obtener campo_tabla_externa", $campo_tabla_externa);
        }

        $elemento_lista_columnas = (new elementos())->columnas_elemento_lista(campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al asignar columnas", $elemento_lista_columnas);
        }

        $elemento_lista_llaves_valores = (new elementos())->llaves_valores(campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al generar llaves", $elemento_lista_llaves_valores);
        }

        $elemento_lista_pattern = (new elementos())->pattern(campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al generar pattern", $elemento_lista_pattern);
        }


        $elemento_lista_tabla_externa_renombrada = (new elementos())->tabla_ext_renombrada(campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al generar tabla externa", $elemento_lista_tabla_externa_renombrada);
        }


        $elemento_lista_separador_select_columnas = (new elementos())->separador_columnas(campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al generar separador", $elemento_lista_separador_select_columnas);
        }


        $elemento_lista_css_id = (new elementos())->elemento_lista_css_id(campo: $campo);
        if(errores::$error){
            return $this->error->error("Error al obtener $elemento_lista_css_id", $elemento_lista_css_id);
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
            return $this->error->error("Error al asignar datos",$datos);
        }

        return $datos;

    }

    /**
     * Asigna a un registro status default
     * @version 1.0.0
     * @param array $registro registro a insertar
     * @param string $status_default status = activo o inactivo
     * @return array
     */
    public function status(array $registro, string $status_default): array
    {
        $status_default = trim($status_default);
        if($status_default === ''){
            return $this->error->error(mensaje: 'Error status_default no puede venir vacio',
                data: $status_default, params: get_defined_vars());
        }

        if(!isset($registro['status'])){
            $registro['status'] = $status_default;
        }
        return $registro;
    }

    /**
     * Obtiene las tablas base para ejecutar una consulta en select
     * @version 1.0.0
     * @param modelo_base $modelo Modelo para obtencion de nombre de tabla
     * @return array
     */
    public function tablas_select(modelo_base $modelo): array
    {
        $modelo->tabla = str_replace('models\\','',$modelo->tabla);

        $consulta_base = new sql_bass();
        $consulta_base->estructura_bd[$modelo->tabla]['columnas'] = $modelo->columnas;

        return $consulta_base->estructura_bd[$modelo->tabla]['columnas'] ??
            $this->error->error(mensaje: 'No existen columnas para la tabla ' . $modelo->tabla, data: $modelo->tabla);
    }


}
