<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use models\atributo;
use models\bitacora;
use models\seccion;
use stdClass;

class sumas{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     *
     * TOTAL
     * Funcion que recorre el arreglo de $campos para maquetar una cadena de texto. A su vez
     * verificando que no esté vacio y que sean validos los caracteres.
     *
     * @param array $campos Campos a verificar
     * @return array|string
     *
     * @function $data = $sumas->data_campo_suma(alias: $alias, campo:$campo, columnas:  $columnas);
     * La funcion enlista y maqueta el nombre de $campo y $alias para completar una cadena de texto.
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.sumas.columnas_suma.21.16.0
     */
    final public function columnas_suma(array $campos): array|string
    {
        if(count($campos)===0){
            return $this->error->error(mensaje:'Error campos no puede venir vacio',data: $campos, es_final: true);
        }
        $columnas = '';
        foreach($campos as $alias =>$campo){
            if(is_numeric($alias)){
                return $this->error->error(mensaje: 'Error $alias no es txt $campos[alias]=campo',data: $campos,
                    es_final: true);
            }
            if($campo === ''){
                return $this->error->error(mensaje: 'Error $campo esta vacio $campos[alias]=campo',data: $campos,
                    es_final: true);
            }
            $alias = trim($alias);
            if($alias === ''){
                return $this->error->error(mensaje: 'Error $alias esta vacio',data: $alias, es_final: true);
            }

            $data = $this->data_campo_suma(alias: $alias, campo:$campo, columnas:  $columnas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al agregar columna',data: $data);
            }
            $columnas .= "$data->coma $data->column";

        }
        return $columnas;
    }

    /**
     *
     * TOTAL
     * Funcion que obtiene el nombre del campo y el alias de la columna a sumar, validando
     * que no vengan espacios vacios y retornando un objeto que continene 2 cadenas maquetadas
     * con el valor de esas variables.
     *
     * @param string $campo Campo a verificar
     * @param string $alias Alias a verificar
     * @param string $columnas Columna a verificar
     * @return array|stdClass
     *
     * @function $column = (new columnas())->add_column(alias: $alias, campo: $campo); Funcion que
     * hace llamado a la funcion "add_column" para maquetar una cadena de texto con los valores de
     * $campo y $alias
     *
     * @function $coma = (new sql_bass())->coma_sql(columnas: $columnas); Funcion que hace llamado
     * al metodo "coma_sql" para maquetar una cadena de texto conforme a los valores de la variable $columnas
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.sumas.data_campo_suma.21.16.0
     */
    private function data_campo_suma(string $alias, string $campo, string $columnas): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje:'Error $campo no puede venir vacio',data:  $campo, es_final: true);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error(mensaje: 'Error $alias no puede venir vacio', data: $alias, es_final: true);
        }

        $column = (new columnas())->add_column(alias: $alias, campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al agregar columna',data: $column);
        }

        $coma = (new sql_bass())->coma_sql(columnas: $columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al agregar coma',data: $coma);
        }

        $data = new stdClass();
        $data->column = $column;
        $data->coma = $coma;

        return $data;
    }


}
