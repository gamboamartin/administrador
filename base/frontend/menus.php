<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;


class menus{
    private errores $error;

    #[Pure] public function __construct(){
        $this->error = new errores();
    }

    /**
     *
     * PROBADO - PARAMS ORDER PARAMS INT Genera el breadcrumb en forma html
     *
     * @param string $etiqueta
     *
     * @example
     *      $br_active = $this->breadcrumb_active($active);
     *
     * @return array|string html para breadcrumbs
     * @throws errores$etiqueta === ''
     * @uses  $directivas
     * @internal   $this->genera_texto_etiqueta($etiqueta,'capitalize');
     */
    private function breadcrumb_active(string $etiqueta):array|string{
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error al $etiqueta no puede venir vacia',data: $etiqueta,
                params: get_defined_vars());
        }

        return "<button class='btn btn-info btn-sm disabled no-print'>$etiqueta</button>";
    }

    /**
     *
     * Genera el breadcrumbs en forma html
     *
     * @param array $breadcrumbs
     * @param string $active
     * @param string $seccion
     * @param string $session_id
     * @return array|string html para breadcrumbs
     * @example
     *       $breadcrumbs_html = $this->breadcrumbs($breadcrumbs, $accion, $seccion);
     *
     * @uses  $directivas
     * @internal   $this->breadcrumb($seccion,'');
     * @internal   $this->genera_texto_etiqueta($etiqueta,'capitalize');
     * @internal   $this->breadcrumb($etiqueta, $link);
     * @internal   $this->breadcrumb_active($active);
     */
    public function breadcrumbs(array $breadcrumbs, string $active, string $seccion, string $session_id):array|string{
        if($seccion === ''){
            return $this->error->error("Error la seccion esta vacia",$seccion);
        }
        if($active === ''){
            return $this->error->error('Error $active no puede venir vacio',$active);
        }

        $html = '';


        $br_active = $this->breadcrumb_active($active);

        if(errores::$error){
            return $this->error->error('Error al generar bread active',$br_active);
        }

        $html .= $br_active;
        return $html;
    }

    /**
     *
     * Ajusta el texto enviado para breadcrumbs
     *
     * @param string $seccion seccion tabla modelo
     * @param string $accion accion
     * @return array|stdClass con datos para generar html
     * @example
     *      $breads = $this->breadcrumbs_con_label($link, $seccion, $accion,$valida_accion);
     *
     * @uses  directivas->genera_texto_etiqueta
     * @internal   $this->valida_estructura_seccion_accion($seccion,$accion);
     * @internal   $accion_modelo->filtro_and($filtro,'numeros',array(),array(),0,0,array());
     */
    public function breadcrumbs_con_label( string $seccion, string $accion): array|stdClass{

        $seccion_br = str_replace('_',' ', $seccion);
        $seccion_br = ucwords($seccion_br);
        $accion_br = str_replace('_',' ', $accion);
        $accion_br = ucwords($accion_br);




        $data = new stdClass();
        $data->seccion = $seccion;
        $data->seccion_br = $seccion_br;
        $data->accion = $accion;
        $data->accion_br = $accion_br;

        return $data;
    }


}
