<?php
namespace gamboamartin\administrador\ctl;


use base\orm\modelo;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class activacion{
    private errores $error;
    private base_modelos $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new base_modelos();
    }

    /**
     * Activacion base
     * @param modelo $modelo Modelo en ejecucion
     * @param int $registro_id Identificador
     * @param string $seccion Seccion en ejecucion
     * @return array|stdClass
     * @version 1.531.51
     *
     */
    final public function activa_bd_base(modelo $modelo, int $registro_id, string $seccion): array|stdClass{
        if($registro_id <= 0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0',data: $registro_id);

        }
        $modelo->registro_id = $registro_id;

        $registro = $modelo->registro(registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data: $registro);
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $modelo->aplica_transaccion_inactivo,  registro: $registro,
            registro_id: $registro_id, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar transaccion activa',data: $valida);
        }
        $registro = $modelo->activa_bd();

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al activar registro en '.$seccion,data: $registro);
        }

        return $registro;
    }
}
