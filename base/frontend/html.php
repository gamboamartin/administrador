<?php
namespace base\frontend;

use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;

use stdClass;

class html  {

    private errores $error;
    public validaciones_directivas $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $label
     * @param string $contenido
     * @return array|string
     */
    protected function crea_elemento_encabezado(string $contenido, string $label):array|string{
        $label = trim($label);
        if($label === ''){
            return $this->error->error('Error el label no puede venir vacio',$label);
        }

        return "
            <div class='col-md-3'>
            <label>
                $label
            </label>
            <br>
            $contenido
            </div>
            ";

    }






}
