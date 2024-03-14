<?php
namespace gamboamartin\controllers\_controlador_adm_reporte;

use gamboamartin\errores\errores;
use stdClass;

class _filtros
{
    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function filtro_rango(string $table): array
    {
        $filtro_rango = array();
        if(isset($_POST['fecha_inicial'])){
            $filtro_rango = $this->filtro_rango_post(table: $table);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener filtro_rango',data:  $filtro_rango);
            }
        }
        return $filtro_rango;

    }

    private function filtro_rango_post(string $table): array
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia',data:  $table);
        }

        $init = (new _fechas())->asigna_data_fechas();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error inicializar POST',data:  $init);
        }

        $filtro_rango[$table.'.fecha']['valor1'] = $_POST['fecha_inicial'];
        $filtro_rango[$table.'.fecha']['valor2'] = $_POST['fecha_final'];
        return $filtro_rango;

    }





}