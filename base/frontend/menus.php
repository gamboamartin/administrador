<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use PDO;
use stdClass;


class menus{
    private errores $error;
    private validaciones_directivas $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }

    /**
     *
     * Genera el breadcrumb en forma html
     *
     * @param string $etiqueta Etiqueta a mostrar en un boton de breadcrumb
     * @param string $accion Accion para la ejecucion en un controlador
     *
     * @example
     *      $breadcrumb  = $this->breadcrumb($seccion,'');
     *
     * @return array|string html para breadcrumbs
     * @throws errores$etiqueta === ''
     * @uses  $directivas->genera_texto_etiqueta
     * @internal   $this->genera_texto_etiqueta($etiqueta,'capitalize');
     */
    private function breadcrumb(string $etiqueta, string $accion, string $seccion, string $session_id):array|string{
        if($etiqueta === ''){
            return $this->error->error("Error texto vacio",$etiqueta);
        }


        $etiqueta = str_replace('_', ' ', $etiqueta);
        if($accion === ''){
            $link = '#';
        }
        else{
            $link = './index.php?seccion='.$seccion.'&session_id='.$session_id."&accion=$accion";
        }
        return "<a type='button' class='btn btn-info btn-sm no-print' href='$link'>$etiqueta</a>";
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

        foreach ($breadcrumbs as $value ) {
            if(!is_array($value)){
                return $this->error->error('Error elemento invalido $breadcrumbs[]=array(etiqueta=>txt,link=>txt)',$value);
            }
            if(!isset($value['etiqueta'])){
                return $this->error->error('Error etiqueta vacia elemento invalido $breadcrumbs[]=array(etiqueta=>txt,link=>txt)',$value);
            }
            if(!isset($value['link'])){
                return $this->error->error('Error link vacia elemento invalido $breadcrumbs[]=array(etiqueta=>txt,link=>txt)',$value);
            }
            if($value['etiqueta']===''){
                return $this->error->error('Error etiqueta vacia elemento invalido $breadcrumbs[]=array(etiqueta=>txt,link=>txt)',$value);

            }
            if($value['link']===''){
                return $this->error->error('Error link vacia elemento invalido $breadcrumbs[]=array(etiqueta=>txt,link=>txt)',$value);

            }

            $etiqueta = strtolower($value['etiqueta']);
            $link = strtolower($value['link']);


            $bread = $this->breadcrumb(etiqueta: $etiqueta, accion: $link, seccion: $seccion,session_id:  $session_id);

            if(errores::$error){
                return $this->error->error('Error al generar bread',$bread);
            }

            $html = $html.$bread;
        }

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
     * @param PDO|bool $link Conexion a bd
     * @param string $seccion seccion tabla modelo
     * @param string $accion accion
     * @param array $accion_registro
     * @param bool $valida_accion valida o no la existencia de un accion en acciones
     * @return array|stdClass con datos para generar html
     * @example
     *      $breads = $this->breadcrumbs_con_label($link, $seccion, $accion,$valida_accion);
     *
     * @uses  directivas->genera_texto_etiqueta
     * @internal   $this->valida_estructura_seccion_accion($seccion,$accion);
     * @internal   $accion_modelo->filtro_and($filtro,'numeros',array(),array(),0,0,array());
     */
    public function breadcrumbs_con_label(PDO|bool $link, string $seccion, string $accion, array $accion_registro,
                                          bool $valida_accion = true): array|stdClass{

        $seccion_br = str_replace('_',' ', $seccion);
        $seccion_br = ucwords($seccion_br);
        $accion_br = str_replace('_',' ', $accion);
        $accion_br = ucwords($accion_br);


        $data_link = (new links())->aplica_data_link_validado(link: $link, valida_accion: $valida_accion,seccion:  $seccion,accion:  $accion,accion_registro:  $accion_registro);
        if(errores::$error){
            return   $this->error->error(mensaje: 'Error al generar datos ',data:  $data_link, params: get_defined_vars());
        }
        if(is_object($data_link)) {
            if($data_link->seccion!=='' && $data_link->accion !=='') {
                $seccion_br = $data_link->seccion;
                $accion_br = $data_link->accion;
            }
        }

        $data = new stdClass();
        $data->seccion = $seccion;
        $data->seccion_br = $seccion_br;
        $data->accion = $accion;
        $data->accion_br = $accion_br;

        return $data;
    }

    /**
     *
     * Genera los breadcrumbs de un html
     *
     * @param array $etiquetas_accion conjunto de etiquetas para la creacion de un breadcrums
     * @param string $seccion Seccion de un controlador o modelo
     * @return array arreglo con parametros
     * @example
     *      $breadcrumbs = $controlador->breadcrumbs_con_label(array('alta', 'lista'));
     *
     * @uses clientes
     * @uses controlador_cliente
     */
    public function breadcrumbs_con_label_html(array $etiquetas_accion, string $seccion):array{
        $seccion = str_replace('models\\','',$seccion);
        if($seccion === ''){
            return $this->error->error('Error seccion no puede venir vacio',$seccion);
        }

        $etiquetas_array = $this->etiquetas_array(etiquetas_accion: $etiquetas_accion);
        if(errores::$error){
            return $this->error->error('Error al generar etiquetas', $etiquetas_array);
        }

        return $etiquetas_array;
    }

    /**
     * Obtiene los datos de un menu
     * @param array $etiqueta Datos para mostrar una etiqueta en un menu
     * @return array
     * @version 1.568.51
     */
    private function data_menu(array $etiqueta): array
    {
        $keys = array('adm_accion_descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $etiqueta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar etiqueta', data: $valida);
        }

        if(!isset($etiqueta['adm_accion_icono'])){
            $etiqueta['adm_accion_icono'] = '';
        }

        $data['etiqueta'] = $etiqueta['adm_accion_descripcion'];
        $data['link'] = $etiqueta['adm_accion_descripcion'];
        $data['icon'] = $etiqueta['adm_accion_icono'];

        return$data;
    }

    /**
     * Funcion la generar las etiquetas de una accion mostrable en el menu
     * @param array $etiquetas_accion Datos con las etiquetas a mostrar
     * @return array
     * @version 1.568.51
     */
    private function etiquetas_array(array $etiquetas_accion): array
    {
        $etiquetas_array = array();
        foreach($etiquetas_accion as $etiqueta){
            if(!is_array($etiqueta)){
                return  $this->error->error('Error etiqueta debe ser un array',$etiqueta);
            }
            $data = $this->data_menu($etiqueta);
            if(errores::$error){
                return $this->error->error('Error al generar data', $data);
            }
            $etiquetas_array[] = $data;
        }
        return $etiquetas_array;
    }



}
