<?php
namespace base\controller;

use base\frontend\directivas;
use base\orm\modelo;
use config\generales;
use gamboamartin\errores\errores;



use models\accion;
use PDO;
use stdClass;


class controler{
    public modelo $modelo;
    public int $registro_id = -1;
    public string $seccion = '';

    public errores $errores;

    public valida_controller $validacion;



    public PDO $link ;
    public array $registro = array();
    public string $tabla = '';
    public string $accion = '';
    public array $inputs = array();
    public directivas $directiva;
    public string $breadcrumbs = '';
    public array $registros = array();
    public array $orders = array();
    public array $filtro_boton_lista = array();
    public array $valores_filtrados  = array();
    public array $valores = array();
    public array $filtro = array();


    public array $clientes = array();
    public string $campo_busca = 'registro_id';
    public string $valor_busca_fault = '';
    public string $btn_busca = '';
    public array $valor_filtro;
    public array $campo_filtro;
    public bool $selected = false;
    public array $campo;
    public bool $campo_resultado=false;
    public stdclass $pestanas ;
    public string $path_base;
    public string $session_id;


    public function __construct(){
        if(!isset($_SESSION['grupo_id'])){
            if(isset($_GET['seccion'], $_GET['accion']) && $_GET['seccion'] !== 'session' && $_GET['accion'] !== 'login') {
                $url = 'index.php?seccion=session&accion=login';
                header('Location: '.$url);
            }
        }

        $generals = (new generales());

        $this->errores = new errores();
        $this->validacion = new valida_controller();
        $this->directiva = new directivas();
        $this->pestanas = new stdClass();
        $this->pestanas->includes = array();
        $this->pestanas->targets = array();

        if(!isset($generals->path_base)){
            $error =  $this->errores->error('path base en generales debe existir','');
            print_r($error);
            exit;
        }
        if(!isset($generals->session_id)){
            $error =  $this->errores->error('session_id en generales debe existir','');
            print_r($error);
            exit;
        }

        $this->path_base = $generals->path_base;
        $this->session_id = $generals->session_id;


    }

    public function data_bread():array|string{
        if(!isset($_SESSION['grupo_id']) && $_GET['seccion'] !== 'session' && $_GET['accion'] !== 'login') {
            header('Location: index.php?seccion=session&accion=login');
            exit;
        }

        $es_vista = false;
        $file_view = $this->path_base.'views/'.$this->seccion.'/'.$this->accion.'.php';
        if(file_exists($file_view)){
            $es_vista = true;
        }
        $file_view_base = $this->path_base.'views/vista_base/'.$this->accion.'.php';
        if(file_exists($file_view_base)){
            $es_vista = true;
        }
        if($this->seccion === 'session' && $this->accion === 'login'){
            $es_vista = false;
        }
        $breadcrumbs = '';
        if($es_vista) {

            $accion_modelo = new accion($this->link);

            $accion_registro = $accion_modelo->accion_registro(accion:  $this->accion, seccion: $this->seccion);
            if(errores::$error){
                return  $this->errores->error('Error al obtener acciones',$accion_registro);
            }
            $acciones =  $accion_modelo->acciones_permitidas(seccion: $this->seccion,accion: $this->accion,modelo: $this->modelo);
            if(errores::$error){
                return  $this->errores->error('Error al obtener acciones',$acciones);
            }

            $breadcrumbs = $this->directiva->genera_breadcrumbs( $this->seccion, $this->accion, $acciones, $this->link,
                $accion_registro,$this->session_id);
            if (errores::$error) {
                return $this->errores->error('Error al generar nav breads', $breadcrumbs);
            }
        }
        return $breadcrumbs;
    }


    /**
     * P INT P ORDER
     * @param int $limit
     * @param int $offset
     * @param array $filtro
     * @param array $orders
     * @param array $filtro_especial
     * @param array $columnas
     * @return array|stdClass
     */
    private function asigna_registros(array $columnas, array $filtro, array $filtro_especial, int $limit, int $offset,
                                      array $orders): array|stdClass{
        if($limit < 0){
            return $this->errores->error(
                'Error limit debe ser mayor o igual a 0  con 0 no aplica limit',$limit);
        }

        $resultado = $this->modelo->filtro_and(columnas: $columnas, filtro: $filtro, filtro_especial: $filtro_especial,
            group_by: array(), limit: $limit, offset: $offset, order: $orders, tipo_filtro: 'textos');
        if(errores::$error){
            return $this->errores->error('Error al filtrar',$resultado);
        }

        return $resultado;
    }


    /**
     *
     * @param string $name_modelo
     * @return array
     */
    public function data_galeria(string $name_modelo):array{
        $name_modelo = trim($name_modelo);
        $valida = $this->validacion->valida_data_modelo($name_modelo);
        if(errores::$error){
            return  $this->errores->error('Error al validar entrada para generacion de modelo en '.$name_modelo,$valida);
        }
        if($this->registro_id<=0){
            return  $this->errores->error('Error registro_id debe ser mayor a 0 ',$this->registro_id);
        }
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return  $this->errores->error('Error this->tabla no puede venir vacio',$this->tabla);
        }

        $r_foto = $this->modelo->get_data_img($name_modelo,$this->registro_id);
        if(errores::$error){
            return $this->errores->error('Error al obtener fotos',$r_foto);
        }

        $data = (new normalizacion())->maqueta_data_galeria(controler: $this, r_fotos: $r_foto,tabla: $name_modelo);
        if(errores::$error){
            return $this->errores->error('Error al maquetar galeria',$data);
        }
        return $data;
    }



    /**
     * P ORDER P INT
     * @param array $data_para_boton
     * @param string $filtro_boton_lista
     * @return array
     */
    private function genera_data_btn(array $data_para_boton, string $filtro_boton_lista):array{
        if($filtro_boton_lista === ''){
            return $this->errores->error('Error $filtro_boton_lista no puede venir vacio',array($this->seccion));
        }

        $key_id = $filtro_boton_lista.'_id';
        $key_descripcion = $filtro_boton_lista.'_descripcion';
        if(!isset($data_para_boton[$key_id])){
            return $this->errores->error('Error $data_para_boton['.$key_id.'] no existe',$data_para_boton);
        }
        if(!isset($data_para_boton[$key_descripcion])){
            return $this->errores->error('Error $data_para_boton['.$key_descripcion.'] no existe',$data_para_boton);
        }
        $data_btn = array();
        $data_btn['id'] = $data_para_boton[$key_id];
        $data_btn['filtro'] = array($filtro_boton_lista.'.id'=>$data_para_boton[$key_id]);
        $data_btn['etiqueta'] = $data_para_boton[$key_descripcion];
        $class = 'outline-primary';
        if(isset($_GET['filtro_btn'][$filtro_boton_lista.'.id'])){
            if((int)$_GET['filtro_btn'][$filtro_boton_lista.'.id'] === (int)$data_btn['id']) {
                $class = 'warning';
            }
        }
        $data_btn['class'] = $class;


        return $data_btn;
    }

    /**
     * P INT P ORDER
     * @param int $limit
     * @param int $pag_seleccionada
     * @param array $filtro
     * @param array $orders
     * @param array $filtro_especial
     * @param array $columnas
     * @return array
     */
    private function genera_resultado_filtrado( array $columnas, array $filtro, array $filtro_especial, int $limit,
                                                array $orders, int $pag_seleccionada):array{

        if($limit < 0){
            return $this->errores->error('Error limit debe ser mayor o igual a 0  con 0 no aplica limit',$limit);
        }
        if($pag_seleccionada < 0){
            return $this->errores->error(
                'Error $pag_seleccionada debe ser mayor o igual a 0 ',$pag_seleccionada);
        }
        $offset = ($pag_seleccionada - 1) * $limit;
        $resultado = $this->asigna_registros(columnas: $columnas, filtro: $filtro, filtro_especial:  $filtro_especial,
            limit: $limit, offset: $offset, orders: $orders);
        if(errores::$error){
            return $this->errores->error('Error al asignar registros',$resultado);
        }

        return $resultado->registros;
    }

    /**
     * P ORDER P INT
     * @return string
     */
    public function get_real_ip():string{
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        }
        else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }



    /**
     * PHPUNIT
     * @param string $name
     * @return controlador_base|array
     */
    public function genera_controlador(string $name):controlador_base|array{
        $namespace = 'controllers\\';
        $name = str_replace($namespace,'',$name);
        $class = $namespace.$name;
        if($name === ''){
            return $this->errores->error('Error name controlador puede venir vacio',$name);
        }
        if(!class_exists($class)){
            return $this->errores->error('Error no existe la clase',$class);
        }
        return new $class($this->link);
    }



    /**
     * P INT
     * @return array
     */
    protected function obten_botones_para_filtro():array{
        $botones_filtro = array();
        foreach($this->filtro_boton_lista as $filtro_boton_lista){
            $registros_botones_filtro = $this->obten_registros_para_boton_filtro(filtro_boton_lista: $filtro_boton_lista['tabla']);
            if(errores::$error){
                return $this->errores->error('Error al obtener registros de filtro',$registros_botones_filtro);
            }
            $data_para_botones = $registros_botones_filtro['registros'];
            foreach ($data_para_botones as $data_para_boton){
                $data_btn = $this->genera_data_btn(data_para_boton: $data_para_boton,filtro_boton_lista: $filtro_boton_lista['tabla']);
                if(errores::$error){
                    return  $this->errores->error('Error al generar datos para el boton',$data_btn);
                }
                $botones_filtro[$filtro_boton_lista['tabla']][] = $data_btn;
            }
        }

        return $botones_filtro;
    }


    /**
     * PHPUNIT
     * @param array $campos
     * @return array
     */
    protected function obten_encabezados_xls(array $campos):array{
        $valida_seccion = $this->validacion->valida_seccion_base($this->seccion);
        if(errores::$error){
            return $this->errores->error('Error al validar datos de la seccion',$valida_seccion);
        }

        $campos = $this->obten_estructura($campos);
        if(errores::$error){
            return $this->errores->error('Error al obtener campos',$campos);
        }
        $keys = (new normalizacion())->genera_campos_lista($campos);
        if(errores::$error){
            return $this->errores->error('Error al genera keys',$keys);
        }


        return $keys;
    }

    /**
     * PHPUNIT
     * @param array $campos
     * @return array
     */
    protected function obten_estructura(array $campos): array
    {
        $valida_seccion = $this->validacion->valida_seccion_base($this->seccion);
        if(errores::$error){
            return $this->errores->error('Error al validar datos de la seccion',$valida_seccion);
        }

        return $campos['campos_completos'];

    }

    /**
     * PHPUNIT/AMBITO
     * @param string $mensaje Mensaje a mostrar
     * @param errores|array|string|stdClass $data Complemento y/o detalle de error
     * @param bool $header si header retorna error en navegador y corta la operacion
     * @param bool $ws si ws retorna error en navegador via json
     * @return array
     */
    protected function retorno_error(string $mensaje, mixed $data, bool $header, bool $ws): array
    {
        $error = $this->errores->error($mensaje, $data);
        if($ws){
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;
        }
        if(!$header){
            return $error;
        }
        $aplica_header = false;
        $seccion_header = '';
        $accion_header = '';

        if(isset($_SESSION['seccion_header']) && isset($_SESSION['accion_header'])) {
            if (trim($_SESSION['seccion_header']) !== '' && trim($_SESSION['accion_header']) !== '') {
                $seccion_header = trim($_SESSION['seccion_header']);
                $accion_header = trim($_SESSION['accion_header']);
                unset($_SESSION['seccion_header'],$_SESSION['accion_header']);
                $aplica_header = true;
            }
        }

        if($aplica_header){
            $liga = './index.php?seccion='.$seccion_header.'&accion='.$accion_header.'&registro_id='.$_GET['registro_id'].'&session_id='.SESSION_ID;
            header("Location: $liga");
            exit;
        }
        print_r($error);
        die('Error');
    }

    /**
     * PHPUNIT
     * @return array
     */
    protected function resultado_filtrado(): array
    {
        if(!isset($_POST['filtros'])){
            return $this->errores->error('Error no existe filtros en POST',$_POST);
        }
        if(!is_array($_POST['filtros'])){
            return $this->errores->error('Error al generar filtros en POST debe ser un array',$_POST);
        }
        $filtros = (new normalizacion())->genera_filtros_envio($_POST['filtros']);
        if(errores::$error){
            return $this->errores->error('Error al generar filtros',$filtros);
        }

        $r_modelo = $this->filtra($filtros);
        if(errores::$error){
            return $this->errores->error('Error al obtener datos',$r_modelo);
        }
        return $r_modelo;
    }



    /**
     * PHPUNIT
     * @param array $filtros
     * @return array
     */
    private function filtra(array $filtros): array
    {
        $r_modelo = $this->modelo->filtro_and($filtros,'numeros',array(),array(),0,0,array());
        if(errores::$error){
            return $this->errores->error('Error al obtener datos',$r_modelo);
        }
        return $r_modelo;
    }


    /**
     * P INT
     * Obtiene todos los registros de un modelo para la muestra de los botones de filtros rapidos
     * @param string $filtro_boton_lista nombre del modelo para traerse todos
     * @example
     *       $registros_botones_filtro = $this->obten_registros_para_boton_filtro($filtro_boton_lista['tabla']);
     *
     * @return array conjunto de registros obtenidos
     * @throws errores $filtro_boton_lista===''
     * @uses  controler
     */
    private function obten_registros_para_boton_filtro(string $filtro_boton_lista):array{ //FIN
        $filtro_boton_lista = str_replace('models\\','', $filtro_boton_lista);
        $class = 'models\\'.$filtro_boton_lista;
        if($filtro_boton_lista===''){
            return $this->errores->error('Error $filtro_boton_lista no puede venir vacio', $filtro_boton_lista);

        }
        if(!class_exists($class)){
            return  $this->errores->error('Error modelo no existe '.$filtro_boton_lista,$filtro_boton_lista);
        }
        $modelo_filtro_btns = $this->modelo->genera_modelo(modelo:$filtro_boton_lista);
        if(errores::$error){
            return  $this->errores->error('Error al generar modelo', $modelo_filtro_btns);
        }
        $registros_botones_filtro = $modelo_filtro_btns->obten_registros();
        if(errores::$error){
            return $this->errores->error('Error al obtener registros de filtro', $registros_botones_filtro);
        }
        return $registros_botones_filtro;
    }

    /**
     * P INT
     * @param int $limit
     * @param int $pag_seleccionada
     * @param array $filtro
     * @param array $filtro_btn
     * @param array $columnas
     * @return array
     */
    protected function obten_registros_para_lista(array $filtro, int $limit, int $pag_seleccionada, array $filtro_btn = array(),
                                               array $columnas = array()): array{
        $this->seccion = str_replace('models\\','',$this->seccion);
        $class = 'models\\'.$this->seccion;
        if($this->seccion === ''){
            return $this->errores->error("Error la seccion esta vacia",$this->seccion);
        }
        if(!class_exists($class)){
            return $this->errores->error("Error la clase es invalida",$class);
        }
        if($limit < 0){
            return $this->errores->error('Error limit debe ser mayor o igual a 0  con 0 no aplica limit',$limit);
        }
        if($pag_seleccionada < 0){
            return $this->errores->error('Error $pag_seleccionada debe ser mayor o igual a 0 ',$pag_seleccionada);
        }



        $filtro_modelado = (new normalizacion())->genera_filtro_modelado(controler:  $this, filtro: $filtro);
        if(errores::$error){
            return $this->errores->error('Error al generar filtro modelado',$filtro_modelado);

        }
        $filtro_especial = array();
        $contador = 0;
        foreach($filtro_btn as $campo => $valor){
            $filtro_especial[$contador][$campo]['operador'] = '=';
            $filtro_especial[$contador][$campo]['valor'] = $valor;
            $contador++;
        }
        $registros = $this->genera_resultado_filtrado(columnas: $columnas, filtro: $filtro_modelado,
            filtro_especial: $filtro_especial, limit: $limit, orders: $this->orders,
            pag_seleccionada: $pag_seleccionada);
        if(errores::$error){
            return  $this->errores->error('Error al generar resultado filtrado',$registros);
        }
        return $registros;

    }

    /**
     * DEBUG INI ERROR DEF
     * @param array $filtro
     * @param array $filtro_btn
     * @return array|int
     */
    public function obten_total_registros_filtrados(array $filtro, array $filtro_btn = array()): array|int
    {
        
        $registros = $this->obten_registros_para_lista(0,1,$filtro,$filtro_btn,array($this->tabla.'_id'));
        if(errores::$error){
            return  $this->errores->error('Error al generar resultado filtrado',$registros);
        }


        return count($registros);
    }



}