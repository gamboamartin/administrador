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


}
