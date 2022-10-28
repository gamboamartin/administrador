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


    /**
     * Genera un html con un input de fecha
     * @param string $campo Campo a integra = name
     * @param string $size sm o md para div
     * @param string $tipo Tipo de input
     * @param string $value Valor default
     * @return string|array
     * @version 1.352.41
     */
    protected function html_fecha(string $campo, string $size, string $tipo,
                                string $value): string|array
    {

        $tipo = trim($tipo);
        if($tipo === ''){
            return $this->error->error(mensaje: 'Error tipo no puede venir vacio',data:  $tipo);
        }

        $size = trim($size);
        if($size === ''){
            return $this->error->error(mensaje: 'Error $size no puede venir vacio',data:  $size);
        }

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo no puede venir vacio', data: $campo);
        }

        $html ="<input ";
        $html.=" type='$tipo' ";
        $html.=" class='form-control-$size form-control input-$size ' ";
        $html.=" name='$campo' ";
        $html.="  placeholder='Ingresa ' ";
        $html.="  title='Ingrese una $campo' ";
        $html.="  value='$value' ";
        $html.="  > ";

        return $html;
    }



}
