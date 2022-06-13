<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use models\atributo;
use models\bitacora;
use models\seccion;
use PDO;
use stdClass;

class atributos{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * P ORDER P INT ERROREV
     * @param string $tabla
     * @return array
     */
    private function atributos(PDO $link, string $tabla): array
    {
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla esta vacia',data:  $tabla);
        }
        $modelo_atributo = new atributo($link);
        $filtro['adm_seccion.descripcion'] = $tabla;
        $r_atributo = $modelo_atributo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos', data: $r_atributo);
        }
        return $r_atributo->registros;
    }

    /**
     * P INT P ORDER ERRORREV
     * @return string
     */
    private function class_attr(string $tabla): string
    {
        $namespace = 'models\\';
        $clase_attr = str_replace($namespace,'',$tabla);
        return 'models\\attr_'.$clase_attr;
    }

    /**
     * P ORDER P INT ERROREV
     * @param array $atributo Registro de tipo modelo atributo
     * @param int $registro_id
     * @return array
     */
    private function data_inst_attr(array $atributo, modelo $modelo, int $registro_id): array
    {
        $keys = array('adm_atributo_descripcion','adm_atributo_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }
        $keys = array('atributo_id');
        $valida = $this->validacion->valida_ids(keys:  $keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $registro_id);
        }
        $modelo->tabla = trim($modelo->tabla);
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error $this->tabla esta vacia',data: $modelo->tabla);
        }

        $data_ins['descripcion'] = $atributo['adm_atributo_descripcion'];
        $data_ins['status'] = 'activo';
        $data_ins['adm_atributo_id'] = $atributo['adm_atributo_id'];
        $data_ins[$modelo->tabla.'_id'] = $registro_id;
        $data_ins['valor'] = '';
        return $data_ins;
    }

    /**
     * P INT ERRORREV
     * @param int $registro_id Identificador de la tabla u objeto de tipo modelo un entero positivo mayor a 0
     * @return array|string
     * @throws \JsonException
     */
    public function ejecuta_insersion_attr(modelo $modelo, int $registro_id): array|string
    {
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id);
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
     * P INT ERRORREV
     * @param array $atributo Registro de tipo modelo atributo
     * @param int $registro_id
     * @param string $tabla
     * @return array
     * @throws JsonException
     */
    private function inserta_atributo(array $atributo, modelo $modelo_base, int $registro_id, string $tabla): array
    {
        $keys = array('atributo_descripcion','atributo_id');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }
        $keys = array('atributo_id');
        $valida = $this->validacion->valida_ids( keys:$keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $registro_id);
        }

        $data_ins = $this->data_inst_attr(atributo: $atributo, modelo: $modelo_base,registro_id:  $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar atributos', data: $data_ins);
        }

        $modelo = $modelo_base->genera_modelo(modelo: $tabla);
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
     * P INT ERROREV
     * @param int $registro_id Identificador de la tabla u objeto de tipo modelo un entero positivo mayor a 0
     * @param string $tabla_attr
     * @return array
     * @throws JsonException
     */
    private function inserta_atributos( modelo $modelo, int $registro_id, string $tabla_attr): array
    {
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla esta vacia',data:  $modelo->tabla);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $registro_id);
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
     * P INT ERROREV
     * @param string $clase_attr
     * @param int $registro_id Identificador de la tabla u objeto de tipo modelo un entero positivo mayor a 0
     * @return array
     * @throws JsonException
     */
    public function inserta_data_attr(string $clase_attr,modelo $modelo, int $registro_id): array
    {
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id);
        }

        $model_attr = $modelo->genera_modelo(modelo: $clase_attr);
        if(errores::$error){
            return $this->error->error('Error al generar modelo', $model_attr);
        }

        $r_ins = $this->inserta_atributos(modelo:$modelo, registro_id:  $registro_id,
            tabla_attr:  $model_attr->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar atributos', data: $r_ins);
        }
        return $r_ins;
    }


}
