<?php
namespace base\orm;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;

class _defaults{

    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }
    final public function ajusta_data_catalogo(array $catalogo, modelo $modelo){

        $campos = array('id','descripcion','codigo');
        foreach ($campos as $campo) {
            $catalogo = $this->ajusta_datas_catalogo(catalogo: $catalogo,campo:  $campo,modelo:  $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    private function ajusta_datas_catalogo(array $catalogo, string $campo, modelo $modelo){
        foreach ($catalogo as $indice => $row) {
            $catalogo = $this->ajusta_row(campo: $campo, catalogo: $catalogo, indice: $indice, modelo: $modelo, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    private function ajusta_row(string $campo, array $catalogo, int $indice, modelo $modelo, array $row){
        if(isset($row[$campo])) {
            $filtro = $this->filtro(campo: $campo, modelo: $modelo, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
            }

            $catalogo = $this->limpia_si_existe(catalogo: $catalogo, filtro: $filtro, indice: $indice, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    final public function alta_defaults(array $catalogo, modelo $entidad, array $filtro = array()){

        $catalogo = $this->ajusta_data_catalogo(catalogo: $catalogo,modelo:  $entidad);
        if (errores::$error) {
            $error = $this->error->error(mensaje: 'Error al ajustar catalogo', data: $catalogo);
            print_r($error);
            exit;
        }

        foreach ($catalogo as $row) {
            $r_alta_bd = $this->inserta_default(entidad: $entidad,row:  $row, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $catalogo;
    }

    /**
     * Verifica si existe un codigo
     * @param modelo $entidad Entidad en proceso
     * @param array $row Registro a validar
     * @param array $filtro Filtro a validar
     * @return array|bool
     */
    private function existe_cod_default(modelo $entidad, array $row, array $filtro = array()): bool|array
    {
        $existe = false;
        if(isset($row[$entidad->tabla.'.codigo'])) {
            $filtro = $this->filtro_default(entidad: $entidad, row: $row, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
            }

            $existe = $entidad->existe(filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar si existe cat_sat_tipo_de_comprobante', data: $existe);
            }
        }
        return $existe;
    }

    /**
     * Genera un filtro para default
     * @param string $campo Campo a verificar
     * @param modelo $modelo Modelo a integrar
     * @param array $row Registro a integrar
     * @return array
     */
    private function filtro(string $campo, modelo $modelo, array $row): array
    {
        $filtro = array();
        $filtro[$modelo->tabla.'.'.$campo] = $row[$campo];
        return $filtro;
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
            return $this->error->error(mensaje: 'Error al validar si existe entidad'.$entidad->tabla, data: $existe);
        }

        if (!$existe) {
            $r_alta_bd = $entidad->alta_registro(registro: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $row;
    }

    private function limpia_si_existe(array $catalogo, array $filtro, int $indice, modelo $modelo){
        $existe = $modelo->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al verificar si existe', data: $existe);
        }
        if($existe){
            unset($catalogo[$indice]);
        }
        return $catalogo;
    }
}
