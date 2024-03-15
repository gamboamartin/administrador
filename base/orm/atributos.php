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
     * P ORDER P INT ERROREV
     *
     * Funcion para obtener los atributos de una tabla. En caso de error, lanzará un mensaje.
     *
     * @param string $tabla Conjunto de datos obtenidos de la database
     *
     * @param PDO $link Enlace del servidor con la base de datos
     *
     * @return array
     *
     * @functions $r_atributo = $modelo_atributo->filtro_and(filtro: $filtro). Obtiene los atributos
     * basado en los datos de "$filtro". En caso de error lanzará un mensaje.
     * @version 1.500.49
     */
    private function atributos(PDO $link, string $tabla): array
    {
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla esta vacia',data:  $tabla);
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
     * Ajusta una clase de tipo modelo
     * @param string $tabla Tabla a integrar
     * @return string|array
     * @version 1.510.51
     */
    private function class_attr(string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia',data: $tabla);
        }
        $namespace = 'models\\';
        $clase_attr = str_replace($namespace,'',$tabla);
        return 'models\\attr_'.$clase_attr;
    }

    /**
     * Obtiene los datos de insersion para un atributo
     * @param array $atributo Registro de tipo modelo atributo
     * @param modelo $modelo Modelo a validar
     * @param int $registro_id registro id
     * @return array
     * @version 1.529.51
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
     * Ejecuta la aplicacion de atributos
     * @param modelo $modelo Modelo en ejecucion
     * @param int $registro_id Identificador de la tabla u objeto de tipo modelo un entero positivo mayor a 0
     * @return array|string
     */
    final public function ejecuta_insersion_attr(modelo $modelo, int $registro_id): array|string
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
            return $this->error->error(mensaje: 'Error la tabla esta vacia',data: $tabla);
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
            return $this->error->error(mensaje: 'Error this->tabla esta vacia',data:  $modelo->tabla);
        }

        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $registro_id);
        }
        if($tabla_attr === ''){
            return $this->error->error(mensaje: 'Error tabla_attr esta vacia',data:  $tabla_attr);
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
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id);
        }
        $clase_attr = trim($clase_attr);
        if($clase_attr === ''){
            return $this->error->error(mensaje: 'Error clase_attr esta vacia', data: $clase_attr);
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
     * Valida que los datos de un atributos sean validos
     * @param array $atributo Atributo a validar
     * @param array $keys Keys a validar
     * @param int $registro_id Registro a validar identificador
     * @return bool|array
     * @version 1.513.51
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
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $registro_id);
        }

        return true;
    }


}
