<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;


use config\generales;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_seccion_pertenece extends modelo{
    /**
     * DEBUG INI
     * @param PDO $link
     */
    public function __construct(PDO $link){
        $tabla = 'adm_seccion_pertenece';
        $columnas = array($tabla=>false, 'adm_seccion'=>$tabla,'adm_sistema'=>$tabla,'adm_menu'=>'adm_seccion');
        $campos_obligatorios = array('adm_seccion_id','adm_sistema_id');
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios,columnas:  $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(): array|stdClass
    {

        $keys = array('adm_seccion_id','adm_sistema_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }


        $registro = $this->data_init_alta_bd(registro:$this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $registro);
        }

        $this->registro = $registro;


        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar seccion pertenece', data: $r_alta_bd);
        }
        return $r_alta_bd;
    }

    private function data_init_alta_bd(array $registro): array
    {
        $data = $this->init_alta_bd(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos', data: $data);
        }


        $registro = $this->row_alta_bd(data: $data,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $registro);
        }
        return $registro;
    }

    /**
     * Obtiene los elementos por separado de seccion y sistema
     * @param array $registro registro en proceso
     * @return array|stdClass
     * @version 2.3.2
     */
    private function init_alta_bd(array $registro): array|stdClass
    {
        $keys = array('adm_seccion_id','adm_sistema_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $adm_seccion = (new adm_seccion(link: $this->link))->registro(registro_id: $registro['adm_seccion_id'],
            columnas_en_bruto: true, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener seccion', data: $adm_seccion);
        }

        $adm_sistema = (new adm_sistema(link: $this->link))->registro(registro_id: $registro['adm_sistema_id'],
            columnas_en_bruto: true, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_sistema', data: $adm_sistema);
        }
        $data = new stdClass();
        $data->adm_seccion = $adm_seccion;
        $data->adm_sistema = $adm_sistema;
        return $data;
    }

    private function row_alta_bd(stdClass $data, array $registro): array
    {
        if(!isset($registro['codigo'])){

            $codigo = $data->adm_seccion->codigo.' '.$data->adm_sistema->codigo;
            $registro['codigo'] = $codigo;
        }
        if(!isset($registro['descripcion'])){

            $descripcion = $data->adm_seccion->descripcion.' '.$data->adm_sistema->descripcion;
            $registro['descripcion'] = $descripcion;
        }
        return $registro;
    }

    /**
     * Obtiene las secciones del paquete en ejecucion
     * @return array
     * @version 2.7.2
     */
    public function secciones_paquete(): array
    {
        $filtro['adm_sistema.descripcion'] = (new generales())->sistema;
        $r_seccion_pertenece = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener secciones', data: $r_seccion_pertenece);
        }
        $seccion_paquete = array();
        foreach ($r_seccion_pertenece->registros as $seccion_pertenece){
            $seccion_paquete[] = $seccion_pertenece['adm_seccion_descripcion'];
        }
        return $seccion_paquete;
    }


}