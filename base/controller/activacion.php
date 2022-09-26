<?php
namespace base\controller;


use base\orm\modelo;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use stdClass;

class activacion{
    private errores $error;
    private base_modelos $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new base_modelos();
    }

    /**
     * ERRORREV P INT P ORDER
     * @param modelo $modelo
     * @param int $registro_id
     * @param string $seccion
     * @return array|stdClass
     */
    public function activa_bd_base(modelo $modelo, int $registro_id, string $seccion): array|stdClass{
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
