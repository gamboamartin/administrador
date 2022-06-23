<?php
namespace base\controller;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class altas{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }

    /**
     * ERROREV
     * @param array $registro Registro que se insertara
     * @param controler $controler Controlador de ejecucion
     * @return array|stdClass
     */
    public function alta_base(array $registro, controler $controler): array|stdClass{

        $registro_r = (new normalizacion())->asigna_registro_alta(controler: $controler,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar registro',data:  $registro_r);
        }

        $resultado = $controler->modelo->alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta registros', data: $resultado);
        }

        return $resultado;
    }




}
