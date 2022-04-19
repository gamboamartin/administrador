<?php
namespace models;
use base\controller\valida_controller;
use base\orm\modelo;
use base\orm\sql_bass;
use gamboamartin\errores\errores;


use PDO;
use stdClass;

class elemento_lista extends modelo{ //PRUEBAS FINALIZADAS

    /**
     * DEBUG INI
     * elemento_lista constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'seccion'=>$tabla, 'menu'=>'seccion');
        $campos_obligatorios = array('orden','etiqueta','filtro','campo','alta','modifica','tipo','cols','lista');
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas);
    }

    /**
     * PHPUNIT
     * @return array
     */
    public function alta_bd(): array{
        if(!isset($this->registro['orden'])){
            return $this->error->error('Error orden debe existir',$this->registro);
        }
        if(!isset($this->registro['seccion_menu_id'])){
            return $this->error->error('Error seccion_menu_id debe existir',$this->registro);
        }
        $r_elemento_lista = parent::alta_bd();
        if(errores::$error){
            return $this->error->error('Error al dar de alta registro',$r_elemento_lista);
        }


        $registro_id = $r_elemento_lista['registro_id'];

        $orden = $this->registro['orden'];
        $seccion_menu_id = $this->registro['seccion_menu_id'];

        $consulta = /** @lang text */
            "SELECT *FROM elemento_lista WHERE seccion_menu_id=$seccion_menu_id AND orden >=$orden AND id <> $registro_id ORDER BY orden ASC";

        $resultado = $this->link->query($consulta);


        while($row = $resultado->fetch()){
            $id = $row['id'];
            $orden++;
            $consulta = /** @lang text */
                "UPDATE elemento_lista SET orden='$orden' WHERE id = $id";
            $this->link->query($consulta);
        }

        $resultado->closeCursor();

        return array('mensaje'=>'Registro insertado con éxito',  'registro_id'=>$registro_id);

    }

    public function encabezados(string $seccion):array{
        $filtro['elemento_lista.status'] = 'activo';
        $filtro['seccion_menu.descripcion'] = $seccion;
        $filtro['elemento_lista.encabezado'] = 'activo';

        $resultado = $this->obten_registros_filtro_and_ordenado($filtro,
            'elemento_lista.orden','ASC');
        if(errores::$error){
            return $this->error->error('Error al obtener registros',$resultado);
        }

        return $resultado['registros'];
    }

    /**
     * PHPUNIT
     * @param string $seccion
     * @return array
     */
    public function obten_elemento_lista_alta(string $seccion):array{
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($seccion === ''){
            return $this->error->error('Error al seccion no puede venir vacia', $seccion);

        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase',$seccion);
        }

        $seccion_menu_id = (new seccion($this->link))->seccion_menu_id($seccion);
        if(errores::$error){
            return $this->error->error('Error al obtener seccion_menu_id',$seccion_menu_id);
        }
        $registros = $this->elementos_para_view($seccion_menu_id);
        if(errores::$error){
            return $this->error->error('Error al obtener elementos',$registros);
        }

        return $registros;
    }

    /**
     * PHPUNIT
     * @param int $seccion_menu_id
     * @return array
     */
    private function elementos_para_view(int $seccion_menu_id):array{
        if($seccion_menu_id<=0){
            return $this->error->error('Error $seccion_menu_id debe ser mayor a 0',$seccion_menu_id);
        }
        $filtro['elemento_lista.seccion_menu_id'] = $seccion_menu_id;
        $filtro['elemento_lista.alta'] = 'activo';
        $filtro['elemento_lista.modifica'] = 'activo';
        $r_elemento_lista = $this->filtro_and(filtro: $filtro, order: array('elemento_lista.orden'=>'ASC'));
        if(errores::$error){
            return $this->error->error('Error al obtener elementos',$r_elemento_lista);
        }
        return $r_elemento_lista['registros'];
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $tabla_externa
     * @param string $campo
     * @param string $seccion
     * @return array
     */
    public function filtro_el(string $campo, string $seccion, string $tabla_externa): array
    {
        $valida = (new valida_controller())->valida_el(campo: $campo, seccion: $seccion, tabla_externa: $tabla_externa);
        if(errores::$error){
            return $this->error->error('Error al validar datos',$valida);
        }

        $filtro_el['elemento_lista.tabla_externa'] = $tabla_externa;
        $filtro_el['elemento_lista.campo'] = $campo;
        $filtro_el['elemento_lista.filtro'] = 'activo';
        $filtro_el['seccion.descripcion'] = $seccion;
        return $filtro_el;
    }

    /**
     * P INT P ORDER
     * @param string $tabla_externa
     * @param string $campo
     * @param string $seccion
     * @return array|stdClass
     */
    public function elemento_para_filtro(string $campo, string $seccion, string $tabla_externa): array|stdClass
    {
        $valida = (new valida_controller())->valida_el(campo: $campo, seccion: $seccion, tabla_externa: $tabla_externa);
        if(errores::$error){
            return $this->error->error('Error al validar datos',$valida);
        }

        $filtro_el = $this->filtro_el(campo: $campo, seccion:  $seccion, tabla_externa:$tabla_externa);
        if(errores::$error){
            return $this->error->error('Error al obtener filtro', $filtro_el);
        }

        $data_el = $this->filtro_and(filtro: $filtro_el);
        if (errores::$error) {
            return $this->error->error('Error al obtener elemento', $data_el);
        }
        return $data_el;
    }

    /**
     * P INT P ORDER
     * @param string $tabla_externa
     * @param string $campo
     * @param string $seccion
     * @return array
     */
    public function elemento_para_lista(string $campo, string $seccion, string $tabla_externa):array{

        $valida = (new valida_controller())->valida_el(campo: $campo, seccion: $seccion, tabla_externa: $tabla_externa);
        if(errores::$error){
            return $this->error->error('Error al validar datos',$valida);
        }

        $data_el = $this->elemento_para_filtro(campo:  $campo, seccion:  $seccion, tabla_externa: $tabla_externa);
        if (errores::$error) {
            return $this->error->error('Error al obtener elemento', $data_el);
        }
        if ((int)$data_el->n_registros === 0) {
            return $this->error->error('Error no existe el elemento lista', $data_el);
        }
        if ((int)$data_el->n_registros > 1) {
           return $this->error->error('Error existe mas de un elemento lista con filtro', $data_el);

        }
        return $data_el->registros[0];
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion obtener los elementos lista de una vista
     *
     * @param string $tabla tabla de la bd
     * @param string $vista vista para su ejecucion
     * @return array|stdClass conjunto de registros de elementos lista
     * @example
     *      $resultado = $this->elementos_lista($link,$tabla,$vista);
     *
     * @uses consultas_base->genera_estructura_tabla
     * @internal $elemento_lista_modelo->obten_registros_filtro_and_ordenado($filtro,'elemento_lista.orden','ASC');
     */
    public function elementos_lista(string $tabla, string $vista):array|stdClass{
        $valida = $this->validacion->valida_modelo(tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al validar '.$tabla,$valida);
        }
        if($vista === ''){
            return $this->error->error('Error $vista no puede venir vacia',$vista);
        }

        $filtro['elemento_lista.status'] = 'activo';
        $filtro['seccion.descripcion'] = $tabla;
        $filtro['elemento_lista.'.$vista] = 'activo';

        $resultado = $this->obten_registros_filtro_and_ordenado(campo:  'elemento_lista.orden', filtros: $filtro,
            orden: 'ASC');

        if(errores::$error){
            return $this->error->error('Error al obtener registros filtrados',$resultado);
        }
        if((int)$resultado->n_registros === 0){
            return   $this->error->error('Error al obtener registros filtrados',$resultado);
        }

        return $resultado;
    }

    /**
     * P INT P ORDER
     * Funcion para la generacion de la estructura para ser utilizada en views
     *
     * @param string $tabla tabla del modelo o estructura
     * @param string $vista vista en la que se aplicaran los ajustes
     * @param array $campos_obligatorios informacion inicializada para su utilizacion n las vistas
     * @param array $estructura_bd
     * @return array conjunto de elementos para ser utilizados en views
     * @throws \JsonException
     * @example
     *      $estructura = $this->genera_estructura_tabla($link,$tabla,$vista,$campos_obligatorios);
     * @uses consultas_base->genera_estructura_bd
     * @internal  $this->elementos_lista($link,$tabla,$vista);
     * @internal  $this->maqueta_estructuras($estructura_init,$campos_obligatorios,$vista,$tabla);
     */
    public function genera_estructura_tabla(array $campos_obligatorios, array $estructura_bd, string $tabla, string $vista): array{
        $valida = $this->validacion->valida_modelo(tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al validar '.$tabla,$valida);
        }
        if($vista === ''){
            return $this->error->error('Error $vista no puede venir vacia',$vista);
        }
        $resultado = $this->elementos_lista(tabla: $tabla,vista: $vista);
        if(errores::$error){
            return $this->error->error('Error al obtener registros',$resultado);
        }
        $estructura_init = $resultado->registros;
        $estructura_bd[$tabla]['campos'] = array();
        $estructura_bd[$tabla]['campos_completos']= array();
        $estructura_bd = (new sql_bass())->maqueta_estructuras(campos_obligatorios: $campos_obligatorios,
            estructura_bd:  $estructura_bd, estructura_init: $estructura_init, tabla: $tabla,vista: $vista);
        if(errores::$error){
            return $this->error->error('Error al generar estructura',$estructura_bd);
        }

        return $estructura_bd;
    }

    /**
     * P INT P ORDER
     * Funcion para la generacion de la estructura para ser utilizada en views
     *
     * @param modelo $modelo
     * @param string $vista vista a la que se le aplicara la estructura
     * @param array $estructura_bd
     * @return array conjunto de elementos para ser utilizados en views
     * @throws \JsonException
     * @uses consultas_base->obten_campos
     * @internal  $modelo_base->genera_modelo($tabla);
     * @internal  $this->genera_estructura_tabla($link,$tabla,$vista,$campos_obligatorios);
     * @example
     *      $estructura = $this->genera_estructura_bd($link,$tabla,$vista);
     *
     */
    public function genera_estructura_bd(array $estructura_bd,  modelo $modelo, string $vista): array{

        if($vista === ''){
            return $this->error->error('Error $vista no puede venir vacia',$vista);
        }

        $campos_obligatorios = $modelo->campos_obligatorios;

        if(!isset($estructura_bd[$modelo->tabla]['campos'])) {
            $estructura_bd = $this->genera_estructura_tabla(campos_obligatorios: $campos_obligatorios,
                estructura_bd:  $estructura_bd, tabla: $modelo->tabla,vista: $vista);
            if(errores::$error){
                return $this->error->error('Error al generar estructura',$estructura_bd);
            }

        }

        return $estructura_bd;
    }

    /**
     * P INT P ORDER
     * Funcion para obtener los campos de una vista
     *
     * @param modelo $modelo
     * @param string $vista vista para su aplicacion en views
     * @param array $estructura_bd
     * @return array con datos para su utilizacion en views
     * @throws \JsonException
     * @example
     *      $consultas_base = new consultas_base();
     * $campos = $consultas_base->obten_campos($this->seccion,'lista',$this->link);
     *
     * @uses ctl_bass
     * @uses modelo_basico
     * @uses templates
     * @internal  $estructura = $this->genera_estructura_bd($link,$tabla,$vista);
     */
    public function obten_campos(array $estructura_bd, modelo $modelo, string $vista): array{

        $estructura_bd = $this->genera_estructura_bd(estructura_bd:  $estructura_bd, modelo: $modelo,vista: $vista);
        if(errores::$error){
            return $this->error->error('Error al generar estructura',$estructura_bd);
        }

        return $estructura_bd[$modelo->tabla];
    }



}
