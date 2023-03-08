<?php
namespace base\orm;
use gamboamartin\errores\errores;

class _defaults{

    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    final public function alta_defaults(array $catalago, modelo $entidad, array $filtro = array()){
        foreach ($catalago as $row) {
            $r_alta_bd = $this->inserta_default(entidad: $entidad,row:  $row, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $catalago;
    }

    private function existe_cod_default(modelo $entidad, array $row, array $filtro = array()){
        $filtro = $this->filtro_default(entidad: $entidad, row: $row, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
        }

        $existe = $entidad->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe cat_sat_tipo_de_comprobante', data: $existe);
        }
        return $existe;
    }

    /**
     * Genera un filtro para default
     * @param modelo $entidad Entidad en ejecucion
     * @param array $row Registro a insertar
     * @param array $filtro filtro custom
     * @return array
     */
    private function filtro_default(modelo $entidad, array $row, array $filtro = array()): array
    {
        if(count($filtro) >0) {
            $filtro[$entidad->tabla . '.codigo'] = $row['codigo'];
        }
        return $filtro;
    }

    private function inserta_default(modelo $entidad, array $row, array $filtro = array()){
        $existe = $this->existe_cod_default(entidad: $entidad,row:  $row, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe cat_sat_tipo_de_comprobante', data: $existe);
        }

        if (!$existe) {
            $r_alta_bd = $entidad->alta_registro(registro: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $row;
    }
}
