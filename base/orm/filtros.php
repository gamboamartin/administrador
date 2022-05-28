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

class filtros{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * P ORDER P INT ERRORREV
     * @param string $consulta
     * @param stdClass $complemento
     * @return string|array
     */
    public function consulta_full_and(stdClass $complemento, string $consulta, modelo $modelo): string|array
    {

        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: 'Error $consulta no puede venir vacia',data: $consulta);
        }

        $complemento = (new where())->limpia_filtros(filtros: $complemento,keys_data_filter:  $modelo->columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar filtros',data:$complemento);
        }

        $complemento_r = (new where())->init_params_sql(complemento: $complemento,keys_data_filter: $modelo->keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar params',data:$complemento_r);
        }


        $modelo->consulta = $consulta.$complemento_r->where.$complemento_r->sentencia.' '.$complemento_r->filtro_especial.' ';
        $modelo->consulta.= $complemento_r->filtro_rango.' '.$complemento_r->filtro_fecha.' ';
        $modelo->consulta.= $complemento_r->filtro_extra.' '.$complemento_r->sql_extra.' '.$complemento_r->not_in.' ';
        $modelo->consulta.= $complemento_r->params->group_by.' '.$complemento_r->params->order.' ';
        $modelo->consulta.= $complemento_r->params->limit.' '.$complemento_r->params->offset;
        return $modelo->consulta;
    }




}
