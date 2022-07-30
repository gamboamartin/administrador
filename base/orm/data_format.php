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
     * Funcion ajusta los campos en formato moneda previo a la insersion, los limpia y los integra como double
     * @param array $registro Registro donde se ajustaran los valores
     * @param array $tipo_campos Son los tipos de campos declarados en el modelo
     * @version 1.224.37
     * @verfuncion 1.1.0
     * @fecha 2022-07-30 13:26
     * @return array
     */
    public function ajusta_campos_moneda(array $registro, array $tipo_campos): array
    {
        foreach($tipo_campos as $campo =>$tipo_dato){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo);
            }
            $tipo_dato = trim($tipo_dato);
            if($tipo_dato === ''){
                return $this->error->error(mensaje: 'Error el tipo_dato esta vacio', data: $tipo_dato);
            }

            $registro = $this->asignacion_campo_moneda(campo: $campo, registro: $registro,tipo_dato:  $tipo_dato);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro);
            }
        }
        return $registro;
    }

    /**
     * Asigna un valor double para guardado en BD cuando se envia un numero con formato de pais
     * @version 1.0.0
     * @param string $campo Campo a ajustar valor
     * @param array $registro Registro donde de ajustara el valor
     * @return array registro con valores ajustados
     */
    private function asigna_campo_moneda(string $campo, array $registro): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio', data: $campo);
        }
        if(!isset($registro[$campo])){
            return $this->error->error(mensaje: 'Error $registro['.$campo.'] no existe',data:  $registro);
        }
        $registro[$campo] = str_replace('$', '', $registro[$campo]);
        $registro[$campo] = str_replace(',', '', $registro[$campo]);
        return $registro;
    }

    /**
     * Regenera los valores de moneda a double cuando estos vienen en formato de moneda local
     * @version 1.0.0
     * @param string $campo Campo a ajustar valor
     * @param array $registro Registro al que se le ajustaran los valores antes de insertar
     * @param string $tipo_dato Ajustara el dato si viene como double o moneda el tipo de dato
     * @return array
     */
    private function asignacion_campo_moneda(string $campo, array $registro, string $tipo_dato): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo);
        }
        $tipo_dato = trim($tipo_dato);
        if($tipo_dato === ''){
            return $this->error->error(mensaje: 'Error el tipo_dato esta vacio', data: $tipo_dato);
        }
        if(isset($registro[$campo]) && ($tipo_dato === 'double' || $tipo_dato === 'moneda')){
            $registro = $this->asigna_campo_moneda(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro);
            }
        }
        return $registro;
    }


}