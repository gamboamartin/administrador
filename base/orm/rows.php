<?php
/**
 * @author Martin Gamboa Vazquez
 * Clase definida para activar elementos en la base de datos
 * @version 1.110.27
 */
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;


class rows{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }


    /**
     * POR DOCUMENTAR EN WIKI
     * Filtro aplicado a los campos de las filas en una operación
     *
     * @param string $campo_filtro El campo específico para el filtrado
     * @param string $campo_row El campo específico de la fila
     * @param array $filtro El arreglo que contiene los filtros
     * @param array $row La fila especificada para el funcionamiento de la operación
     *
     * @return array Devuelve el resultado del filtro aplicado. Si se produce un error, devuelve un arreglo con información sobre el error
     *
     * @throws errores Si el contenido de $campo_filtro o $campo_row no es válido o está vacío, se genera un error
     * @version 14.6.0
     */
    private function filtro_hijo(string $campo_filtro, string $campo_row, array $filtro, array $row):array{
        if($campo_row===''){
            return $this->error->error(mensaje: "Error campo vacio",data: $campo_row);
        }
        if($campo_filtro===''){
            return $this->error->error(mensaje: "Error filtro",data: $campo_filtro);
        }
        if(!isset($row[$campo_row])){
            $row[$campo_row] = '';
        }
        $filtro[$campo_filtro] = (string)$row[$campo_row];

        return $filtro;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * La función filtro_para_hijo genera un array de filtros basándose en los filtros y la fila de datos proporcionados.
     *
     * @param array $filtros Los filtros a aplicar.
     * @param array $row Los datos sobre los que se aplicarán los filtros.
     * @return array Los filtros generados.
     * @throws errores En caso de error al generar el filtro.
     * @version 14.7.0
     */
    private function filtro_para_hijo(array $filtros, array $row):array{
        $filtro = array();
        foreach($filtros as $campo_filtro=>$campo_row){
            if($campo_row===''){
                return $this->error->error(mensaje: "Error campo vacio",data: $campo_filtro);
            }
            $filtro = $this->filtro_hijo(campo_filtro: $campo_filtro, campo_row: $campo_row,filtro: $filtro,
                row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro);
            }
        }
        return $filtro;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Obtiene el filtro para un hijo a partir de los datos del modelo y de una fila específica.
     *
     * @param array $data_modelo Los datos del modelo, que deben incluir las claves 'filtros' y 'filtros_con_valor'.
     *                           Ambos deben ser arrays. Si no es así, se devuelve un mensaje de error con sugerencias de corrección.
     * @param array $row Una fila específica que se pasa a la función `filtro_para_hijo`.
     *
     * @return array Retorna el array de filtros. Si ocurre un error, se retorna un mensaje de error.
     *
     * @version 14.8.0
     */
    final public function obten_filtro_para_hijo(array $data_modelo, array $row):array{
        if(!isset($data_modelo['filtros'])){
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }
        if(!isset($data_modelo['filtros_con_valor'])){
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros_con_valor] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }
        if(!is_array($data_modelo['filtros'])){
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }
        if(!is_array($data_modelo['filtros_con_valor'])){
            $fix = 'En data_modelo debe existir un key filtros_con_valor como array data_modelo[filtros_con_valor] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }

        $filtros = $data_modelo['filtros'];
        $filtros_con_valor = $data_modelo['filtros_con_valor'];

        $filtro = $this->filtro_para_hijo(filtros: $filtros,row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: "Error filtro",data: $filtro);
        }

        foreach($filtros_con_valor as $campo_filtro=>$value){
            $filtro[$campo_filtro] = $value;
        }

        return $filtro;
    }


}
