<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

/**
 * PARAMS ORDER, PARAMS INT
 */
class etiquetas{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }


    /**
     * PROBADO PARAMS INT PARAMS ORDER ERRORREV
     * @param string $campo_busca
     * @return string|string[]
     */
    public function etiqueta_campo_vista(string $campo_busca): array|string
    {
        return str_replace(array('_', '.', '[', ']'), ' ', $campo_busca);
    }


    /**
     * Genera la etiqueta de unb input
     * @param string $etiqueta Etiqueta input Prioridad
     * @param string $tabla Tabla o estructura base para input
     * @return array|string
     * @version 1.455.49
     */
    public function etiqueta_label(string $etiqueta, string $tabla): array|string
    {
        $etiqueta_label = strtoupper($tabla);

        $etiqueta_label_mostrable = $this->etiqueta_label_mostrable(etiqueta:  $etiqueta, etiqueta_label: $etiqueta_label);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar etiqueta',data: $etiqueta_label_mostrable);
        }
        return $etiqueta_label_mostrable;
    }

    /**
     * Genera una etiqueta para un input
     * @param string $etiqueta_label Etiqueta input
     * @param string $etiqueta Etiqueta input Prioridad
     * @return string
     * @version 1.455.49
     */
    private function etiqueta_label_mostrable(string $etiqueta, string $etiqueta_label): string
    {
        $etiqueta = trim($etiqueta);
        $etiqueta_label = trim($etiqueta_label);
        $etiqueta_label_mostrable = $etiqueta_label;
        if($etiqueta!==''){
            $etiqueta_label_mostrable = $etiqueta;
        }
        return $etiqueta_label_mostrable;
    }



    /**
     * Genera un label de un input de tipo file
     * @param string $etiqueta Etiqueta a mostrar
     * @return string|array
     * @version 1.312.41
     */
    private function label_input_upload(string $etiqueta): string|array
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return  $this->error->error('Error etiqueta esta vacio',$etiqueta);
        }
        return '<label class="custom-file-label" for="'.$etiqueta.'">'.$etiqueta.'</label>';
    }

    /**
     * Genera la etiqueta de un input file
     * @param string $codigo Codigo de input mostrado en file
     * @return string|array
     * @version 1.311.41
     */
    private function label_upload(string $codigo): string|array
    {
        $codigo = trim($codigo);
        if($codigo === ''){
            return  $this->error->error(mensaje: 'Error codigo esta vacio',data: $codigo);
        }

        $html =     '<div class="input-group-prepend">';
        $html.=         '<span class="input-group-text" >'.$codigo.'</span>';
        $html.=     '</div>';

        return $html;
    }

    /**
     * Genera label de files multiple
     * @param string $codigo Codigo para ser mostrado en label
     * @param string $etiqueta Etiqueta a mostrar
     * @return array|stdClass
     * @version 1.314.41
     */
    public function labels_multiple(string $codigo, string $etiqueta): array|stdClass
    {
        $codigo = trim($codigo);
        if($codigo === ''){
            return  $this->error->error(mensaje: 'Error codigo esta vacio',data: $codigo);
        }

        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return  $this->error->error(mensaje: 'Error etiqueta esta vacio',data: $etiqueta);
        }

        $label_upload = $this->label_upload(codigo: $codigo);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener label',data: $label_upload);
        }

        $label_input_upload = $this->label_input_upload(etiqueta: $etiqueta);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener label',data: $label_input_upload);
        }

        $data = new stdClass();
        $data->label_upload = $label_upload;
        $data->label_input_upload = $label_input_upload;

        return $data;
    }




    /**
     * PROBADO - PARAMS ORDER PARAMS INT ERROREV
     * @param string $txt
     * @return string|array
     */
    public function title(string $txt): string|array
    {
        $title = trim(str_replace('_',' ',$txt));
        if($title === ''){
            return  $this->error->error(mensaje: 'Error title esta vacio',data: $title, params: get_defined_vars());
        }
        return ucwords(trim($title));
    }
}
