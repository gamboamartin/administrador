<?php
namespace base\orm;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\errores\errores;

class seguridad_dada{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Integra el filtro para implementacion de seguridad data
     * @param modelo $modelo Modelo de aplicacion
     * @return array
     * @version 1.520.51
     */
    final  function aplica_filtro_seguridad(modelo $modelo): array
    {
        $filtro_seguridad = array();
        if($modelo->aplica_seguridad) {

            $filtro_seguridad = $this->filtro_seguridad(modelo: $modelo);
            if (errores::$error) {
                return $this->error->error( mensaje: 'Error al obtener filtro de seguridad', data: $filtro_seguridad);
            }
        }
        return $filtro_seguridad;
    }

    /**
     * Genera un filtro de tipo seguridad
     * @param modelo $modelo Modelo para integracion de filtro de seguridad
     * @return array
     * @version 1.487.49
     *
     */
    private function filtro_seguridad(modelo $modelo): array
    {
        $usuario_modelo = new adm_usuario($modelo->link);

        $seguridad = $usuario_modelo->filtro_seguridad();
        if (errores::$error) {
            return $this->error->error( mensaje: 'Error al obtener filtro de seguridad', data: $seguridad);
        }
        $modelo->filtro_seguridad = $seguridad;
        return $seguridad;
    }
}
