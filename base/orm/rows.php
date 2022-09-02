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
     * Asigna el filtro necesario para traer elementos dependiendes de una consulta
     * @version 1.0.0
     * @param string $campo_row Nombre del campo del registro el cual se utiliza para la obtencion de los registros
     * ligados
     * @param string $campo_filtro Nombre del campo del registro el cual se utiliza como valor del filtro
     * @param array $filtro Filtro precargado, es recursivo hace push con el nuevo resultado
     * @param array $row Registro donde se obtendra el valor y el campo para retornar el filtro nuevo
     * @return array
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
     *
     * Devuelve un arreglo con los datos necesarios para obtener un filtro y ser utilizado en las sentencias de consulta
     * para la obtención de los registros esto de todos las columnas que se mandan por el filtro.
     * Genera arreglo
     * @version 1.0.0
     * @param array $filtros arreglo de filtros para la obtencion de registros de hijos
     * @param array $row Registro donde se obtendra el valor y el campo para retornar el filtro nuevo
     * @return array
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
     *
     * Funcion que genera un filtro para ser enviado en forma de array para consultas posteriores
     * @version 1.0.0
     * @param array $data_modelo datos de la configuracion del modelo a procesar los filtros
     * @param array $row registro formado en forma modelo->registro
     * @example
     *     $filtro = $this->obten_filtro_para_hijo($data_modelo,$row);
     *
     * @return array con filtro maquetado para su procesamiento filtro[$campo_filtro] = $value;
     * @throws errores $data_modelo['filtros'] no existe
     * @throws errores $data_modelo['filtros_con_valor'] no existe
     * @throws errores $data_modelo['filtros'] no es un array
     * @throws errores $data_modelo['filtros_con_valor'] no es un array
     * @throws errores $data_modelo['filtros'][$campo] =  ''
     * @throws errores $data_modelo['filtros'][$campo] no existe
     *
     */
    public function obten_filtro_para_hijo(array $data_modelo, array $row):array{
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
