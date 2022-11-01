<?php
namespace base\frontend;
use base\orm\modelo_base;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use PDO;
use stdClass;

class selects{
    private errores $error;
    private validaciones_directivas $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }




    /**
     * Obtiene los datos de un select desde la base de datos
     * @param PDO $link conexion a base de datos
     * @param string $name_modelo nombre del modelo
     * @return array|stdClass registros obtenidos por registros activos o todos los registros segun bool $todos
     *
     * @version 1.441.48
     */
    private function data_bd( PDO $link, string $name_modelo): array|stdClass
    {
        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }

        $modelo = (new modelo_base($link))->genera_modelo(modelo: $name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar modelo', data: $modelo);
        }
        $resultado = $modelo->obten_registros();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros del modelo '.$modelo->tabla,
                data: $resultado);
        }

        return $resultado;
    }


    /**
     * Genera los datos para un select
     * @param PDO $link Conexion a la base de datos
     * @param string $name_modelo Nombre del modelo del select
     * @return array
     * @version 1.442.8
     */
    private function data_select(PDO $link, string $name_modelo):array{

        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }

        $resultado = $this->data_bd(link: $link, name_modelo: $name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros', data: $resultado);
        }

        if(count($resultado->registros) === 0){
            return $this->error->error(mensaje: 'Error no existen registros del modelo '.$name_modelo,data:  $resultado);
        }
        return $resultado->registros;
    }

    /**
     * PROBADO-PARAMS ORDER P INT
     * @param string $llave_json
     * @return array|stdClass
     */
    private function elemento_select_fijo(string $llave_json): array|stdClass
    {
        $llave_json = trim($llave_json);
        if($llave_json === ''){
            return $this->error->error('Error $llave_json esta vacia',$llave_json);
        }
        $explode_datos = explode(":", $llave_json);
        if(count($explode_datos)!==2){
            return $this->error->error('Error $llaves_valores debe venir en formato json string',$explode_datos);
        }

        $data = new stdClass();
        $data->key = trim($explode_datos[0]);
        $data->dato = trim($explode_datos[1]);

        return $data;
    }

    /**
     * PARAMS ORDER P INT
     * @param string $llaves_valores
     * @return array
     */
    public function elementos_for_select_fijo(string $llaves_valores): array
    {
        $elementos_select = array();
        $explode_llaves = explode(',',$llaves_valores);
        foreach ($explode_llaves as  $value){

            $data = (new selects())->elemento_select_fijo(llave_json: $value);
            if(errores::$error){
                return $this->error->error('Error al data para option', $data);
            }

            $elementos_select[$data->key] = $data->dato;
        }
        return $elementos_select;

    }



    /**
     *
     * Genera los registros a mostrar en un select
     * @param array $filtro Filtro para obtencion de datos de un select
     * @param PDO $link Conexion a la base de datos
     * @param string $name_modelo Nombre del modelo de datos
     * @param bool $select_vacio_alta Si true no genera options
     * @return array conjunto de datos del resultado del modelo
     * @example
     *      $registros = $this->obten_registros_select($select_vacio_alta,$modelo, $filtro,$todos);
     *
     * @uses directivas
     * @internal $modelo->obten_registros_activos(array(), $filtro);
     * @internal $modelo->obten_registros();
     * @internal $modelo->obten_registros_activos(array(), $filtro);
     * @version 1.449.48
     */
    private function obten_registros_select(array $filtro, PDO $link, string $name_modelo, bool $select_vacio_alta): array
    {

        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }

        $registros = array();

        if(!$select_vacio_alta) {
            $registros = $this->data_select( link: $link, name_modelo: $name_modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registros del modelo '.$name_modelo,
                    data: $registros);
            }
        }
        elseif(count($filtro)>0) {
            $registros = $this->registros_activos(filtro: $filtro, link: $link, name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
            }
        }

        return $registros;
    }



    /**
     * PARAMS ORDER P INT
     * @param string $key
     * @param string $value_data
     * @param string $value_select
     * @return array|string
     */
    private function option_for_select(string $key, string $value_data, string $value_select): array|string
    {
        $selected = (new selects())->selected_value(value_base: $value_data,value_tabla: $value_select);
        if(errores::$error){
            return $this->error->error('Error al generar selected', $selected);
        }

        $option = (new selects())->option_value(key: $key, selected: $selected, value: $value_data);
        if(errores::$error){
            return $this->error->error('Error al generar option', $option);
        }
        return $option;
    }

    /**
     * PARAMS ORDER P INT
     * @param array $elementos_select
     * @param string $valor
     * @return array|string
     */
    public function options_for_select(array $elementos_select, string $valor): array|string
    {
        $options = '';
        foreach ($elementos_select as $key => $value){

            $option = (new selects())->option_for_select(key:  $key, value_data: $value,value_select:  $valor);
            if(errores::$error){
                return $this->error->error('Error al generar option', $option);
            }

            $options .= $option;
        }
        return $options;
    }


    /**
     * PARAMS ORDER P INT
     * @param string $key
     * @param string $selected
     * @param string $value
     * @return string
     */
    private function option_value(string $key, string $selected, string $value): string
    {
        return "<option value = '$value' $selected>".$key."</option>";
    }





    /**
     * Obtiene los registros activos para un select
     * @param array $filtro Filtro de datos para select
     * @param PDO $link Conexion a la base de datos
     * @param string $name_modelo Nombre del modelo de datos
     * @return array
     * @version 1.442.48
     */
    private function registros_activos(array $filtro, PDO $link, string $name_modelo): array
    {
        $name_modelo = trim($name_modelo);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }
        $modelo = (new modelo_base($link))->genera_modelo($name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar modelo', data: $modelo);
        }
        $resultado = $modelo->obten_registros_activos(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $resultado);
        }
        return $resultado->registros;
    }

    /**
     * Obtiene los registros de un select para integrar los options
     * @param array $filtro Filtro para obtencion de datos de un select
     * @param PDO $link Conexion a la base de datos
     * @param array $registros Conjunto de registros a asignar a options
     * @param string $select_vacio_alta Si true no genera options
     * @param string $tabla Tabla de datos
     * @return array
     * @version 1.455.49
     */
    private function registros_for_select(array $filtro, PDO $link, array $registros, string $select_vacio_alta, string $tabla): array
    {

        $valida = $this->validacion->valida_data_modelo(name_modelo: $tabla);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar tabla",data: $valida);
        }

        $registros = $this->registros_select(filtro: $filtro, link: $link, name_modelo: $tabla, registros: $registros,
            select_vacio_alta: $select_vacio_alta);
        if(errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros '.$tabla,data: $registros);
        }

        return $registros;
    }

    /**
     * Obtiene los registros para un select
     * @param array $filtro Filtro para obtencion de datos de un select
     * @param PDO $link Conexion a la base de datos
     * @param string $name_modelo Nombre del modelo de datos
     * @param array $registros Registros precargados
     * @param bool $select_vacio_alta Si true no genera options
     * @return array
     * @version 1.453.49
     */
    private function registros_select(array $filtro, PDO $link, string $name_modelo, array $registros,
                                      bool $select_vacio_alta): array
    {

        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }

        if(count($registros)===0 ) {
            $registros = $this->obten_registros_select(filtro: $filtro, link: $link, name_modelo: $name_modelo,
                select_vacio_alta: $select_vacio_alta);
            if(errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registros '.$name_modelo,data: $registros);
            }
        }
        return $registros;
    }

    /**
     * PROBADO-PARAMS ORDER P INT
     * @param string $value_base
     * @param string $value_tabla
     * @return string|array
     */
    private function selected_value(string $value_base, string $value_tabla): string|array
    {
        $value_base = trim($value_base);
        $value_tabla = trim($value_tabla);

        if($value_base === ''){
            return $this->error->error('Error $value_base esta vacio ',$value_base);
        }


        $selected = '';
        if($value_base === $value_tabla){
            $selected = 'selected';
        }
        return $selected;
    }

}
