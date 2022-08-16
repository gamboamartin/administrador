<?php
namespace base\orm;
use gamboamartin\errores\errores;
use models\adm_usuario;

class seguridad_dada{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    public function aplica_filtro_seguridad(modelo $modelo): array
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
     *
     */
    private function filtro_seguridad(modelo $modelo): array
    {
        $usuario_modelo = new adm_usuario($modelo->link);

        $seguridad = $usuario_modelo->filtro_seguridad(tabla: $modelo->tabla);
        if (errores::$error) {
            return $this->error->error( mensaje: 'Error al obtener filtro de seguridad', data: $seguridad);
        }
        $modelo->filtro_seguridad = $seguridad;
        return $seguridad;
    }
}
