<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;



class data_format{

    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     *  P INT P ORDER ERRORREV
     * @param array $registro
     * @param array $tipo_campos
     * @return array
     */
    public function ajusta_campos_moneda(array $registro, array $tipo_campos): array
    {
        foreach($tipo_campos as $campo =>$tipo_dato){
            $registro = $this->asignacion_campo_moneda(campo: $campo, registro: $registro,tipo_dato:  $tipo_dato);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro,
                    params: get_defined_vars());
            }
        }
        return $registro;
    }

    /**
     * P INT P ORDER PROBADO ERRORREV
     * @param string $campo
     * @param array $registro
     * @return array
     */
    private function asigna_campo_moneda(string $campo, array $registro): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio', data: $campo, params: get_defined_vars());
        }
        if(!isset($registro[$campo])){
            return $this->error->error(mensaje: 'Error $registro['.$campo.'] no existe',data:  $registro,
                params: get_defined_vars());
        }
        $registro[$campo] = str_replace('$', '', $registro[$campo]);
        $registro[$campo] = str_replace(',', '', $registro[$campo]);
        return $registro;
    }

    /**
     * P INT P ORDER PROBADO ERROREV
     * @param string $campo
     * @param array $registro
     * @param string $tipo_dato
     * @return array
     */
    private function asignacion_campo_moneda(string $campo, array $registro, string $tipo_dato): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo, params: get_defined_vars());
        }
        $tipo_dato = trim($tipo_dato);
        if($tipo_dato === ''){
            return $this->error->error(mensaje: 'Error el tipo_dato esta vacio', data: $tipo_dato,
                params: get_defined_vars());
        }
        if(isset($registro[$campo]) && ($tipo_dato === 'double' || $tipo_dato === 'moneda')){
            $registro = $this->asigna_campo_moneda(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro,
                    params: get_defined_vars());
            }
        }
        return $registro;
    }


}