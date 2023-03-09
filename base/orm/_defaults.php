<?php
namespace base\orm;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;

class _defaults{

    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    final public function ajusta_catalogo_by_code(array $catalogo, modelo $modelo){
        foreach ($catalogo as $key=>$row){
            $filtro = array();
            $filtro[$modelo->tabla.'.codigo'] = $row['codigo'];

            $existe = $modelo->existe(filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al verificar si existe', data: $existe);
            }
            if($existe){
                unset($catalogo[$key]);
            }
        }
        return $catalogo;
    }

    final public function ajusta_catalago_by_id(array $catalogo, modelo $modelo){
        foreach ($catalogo as $key=>$row){
            $existe = $modelo->existe_by_id(registro_id: $row['id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al verificar si existe', data: $existe);
            }
            if($existe){
                unset($catalogo[$key]);
            }
        }
        return $catalogo;
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
     * @version 9.129.5
     */
    private function filtro_default(modelo $entidad, array $row, array $filtro = array()): array
    {
        $tabla = trim($entidad->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        if(count($filtro) === 0) {
            $keys = array('codigo');
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar row', data: $valida);
            }

            $filtro[$tabla . '.codigo'] = $row['codigo'];
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
