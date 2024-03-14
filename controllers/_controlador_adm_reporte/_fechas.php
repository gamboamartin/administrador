<?php
namespace gamboamartin\controllers\_controlador_adm_reporte;

use gamboamartin\errores\errores;
use stdClass;

class _fechas
{
    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function asigna_data_fechas(): array|stdClass
    {
        $data = $this->data_fechas();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error inicializar filtros',data:  $data);
        }

        $init = $this->init_data_post_fecha(data: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error inicializar POST',data:  $init);
        }
        return $init;

    }
    private function data_fechas(): array|stdClass
    {
        $data = $this->init_filtro_fecha();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error inicializar filtros',data:  $data);
        }

        $data = $this->init_fecha_inicial(data: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error inicializar filtros',data:  $data);
        }
        $data = $this->init_fecha_final(data: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error inicializar filtros',data:  $data);
        }
        return $data;

    }

    private function init_data_post_fecha(stdClass $data): array|stdClass
    {
        if(!$data->existe_alguna_fecha){
            $init = $this->init_post_fecha();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error inicializar POST',data:  $init);
            }
        }

        if(!$data->existe_fecha_inicial){
            $init = $this->init_post_fecha_inicial();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error inicializar POST',data:  $init);
            }
        }
        if(!$data->existe_fecha_final){
            $init = $this->init_post_fecha_final();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error inicializar POST',data:  $init);
            }
        }
        return $data;


    }

    private function init_fecha_final(stdClass $data): stdClass
    {
        if(isset($_POST['fecha_final'])){
            $data->existe_alguna_fecha = true;
            $data->existe_fecha_final = true;
        }
        return $data;

    }
    private function init_fecha_inicial(stdClass $data): stdClass
    {
        if(isset($_POST['fecha_inicial'])) {
            $data->existe_alguna_fecha = true;
            $data->existe_fecha_inicial = true;
        }
        return $data;

    }

    private function init_filtro_fecha(): stdClass
    {
        $data = new stdClass();
        $data->existe_alguna_fecha = false;
        $data->existe_fecha_inicial = false;
        $data->existe_fecha_final = false;
        return $data;

    }

    private function init_post_fecha(): array
    {
        $_POST['fecha_inicial'] = date('Y-m-01');
        $_POST['fecha_final'] = date('Y-m-d');
        return $_POST;

    }

    private function init_post_fecha_final(): array
    {
        $_POST['fecha_final'] = date('Y-m-d');
        return $_POST;

    }

    private function init_post_fecha_inicial(): array
    {
        $_POST['fecha_inicial'] = date('Y-m-01');
        return $_POST;

    }

}