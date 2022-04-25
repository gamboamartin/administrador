<?php
namespace base\controller;


use base\orm\modelo;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;

class activacion{
    private errores $error;
    private base_modelos $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new base_modelos();
    }

    /**
     *
     * @param int $registro_id
     * @param modelo $modelo
     * @param string $seccion
     * @return array
     */
    public function activa_bd_base(modelo $modelo, int $registro_id, string $seccion): array{
        if($registro_id <= 0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0',data: $registro_id,
                params: get_defined_vars());

        }
        $modelo->registro_id = $registro_id;

        $registro = $modelo->registro(registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data: $registro,
                params: get_defined_vars());
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $modelo->aplica_transaccion_inactivo,  registro: $registro,
            registro_id: $registro_id, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar transaccion activa',data: $valida,
                params: get_defined_vars());
        }
        $registro = $modelo->activa_bd();

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al activar registro en '.$seccion,data: $registro,
                params: get_defined_vars());
        }

        return $registro;
    }
}
