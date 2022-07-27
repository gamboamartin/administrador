<?php
namespace base\orm;
use gamboamartin\errores\errores;
use stdClass;

class upd{
    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Verifica si aplica ejecucion de modificacion de datos
     * @version 1.80.17
     * @param int $id Identificador del modelo
     * @param modelo $modelo Modelo en ejecucion
     * @return array|stdClass
     */
    public function ejecuta_upd(int $id, modelo $modelo): array|stdClass
    {
        $resultado = new stdClass();
        $ejecuta_upd = true;
        if(count($modelo->registro_upd) === 0){
            $ejecuta_upd = false;

            $resultado = (new inicializacion())->result_warning_upd(id:$id,
                registro_upd: $modelo->registro_upd,resultado:  $resultado);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al inicializar elemento',data:$resultado);
            }
        }
        $data = new stdClass();
        $data->ejecuta_upd = $ejecuta_upd;
        $data->resultado = $resultado;
        return $data;
    }
}
