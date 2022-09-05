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

class sumas{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * PHPUNIT
     *
     * Funcion que recorre el arreglo de $campos para maquetar una cadena de texto. A su vez
     * verificando que no esté vacio y que sean validos los caracteres.
     *
     * @param array $campos Campos a verificar
     * @return array|string
     *
     * @function $data = $sumas->data_campo_suma(alias: $alias, campo:$campo, columnas:  $columnas);
     * La funcion enlista y maqueta el nombre de $campo y $alias para completar una cadena de texto.
     */
    public function columnas_suma(array $campos): array|string
    {
        if(count($campos)===0){
            return $this->error->error('Error campos no puede venir vacio',$campos);
        }
        $columnas = '';
        foreach($campos as $alias =>$campo){
            if(is_numeric($alias)){
                return $this->error->error('Error $alias no es txt $campos[alias]=campo',$campos);
            }
            if($campo === ''){
                return $this->error->error('Error $campo esta vacio $campos[alias]=campo',$campos);
            }

            $data = $this->data_campo_suma(alias: $alias, campo:$campo, columnas:  $columnas);
            if(errores::$error){
                return $this->error->error('Error al agregar columna',$data);
            }
            $columnas .= "$data->coma $data->column";

        }
        return $columnas;
    }

    /**
     * PROBADO P ORDER P INT
     * @param string $campo
     * @param string $alias
     * @param string $columnas
     * @return array|stdClass
     */
    private function data_campo_suma(string $alias, string $campo, string $columnas): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio', $campo);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error('Error $alias no puede venir vacio', $alias);
        }

        $column = (new columnas())->add_column(alias: $alias, campo: $campo);
        if(errores::$error){
            return $this->error->error('Error al agregar columna',$column);
        }

        $coma = (new sql_bass())->coma_sql(columnas: $columnas);
        if(errores::$error){
            return $this->error->error('Error al agregar coma',$coma);
        }

        $data = new stdClass();
        $data->column = $column;
        $data->coma = $coma;

        return $data;
    }


}
