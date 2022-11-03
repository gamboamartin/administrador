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
    private function obten_registros_select(array $filtro, PDO $link, string $name_modelo): array
    {

        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }

        $registros = $this->registros_activos(filtro: $filtro, link: $link, name_modelo: $name_modelo);
        if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
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
     * @param string $tabla Tabla de datos
     * @return array
     * @version 1.455.49
     */
    private function registros_for_select(array $filtro, PDO $link, array $registros, string $tabla): array
    {

        $valida = $this->validacion->valida_data_modelo(name_modelo: $tabla);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar tabla",data: $valida);
        }

        $registros = $this->registros_select(filtro: $filtro, link: $link, name_modelo: $tabla, registros: $registros);
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
     * @return array
     * @version 1.453.49
     */
    private function registros_select(array $filtro, PDO $link, string $name_modelo, array $registros): array
    {

        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }

        if(count($registros)===0 ) {
            $registros = $this->obten_registros_select(filtro: $filtro, link: $link, name_modelo: $name_modelo);
            if(errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registros '.$name_modelo,data: $registros);
            }
        }
        return $registros;
    }


}
