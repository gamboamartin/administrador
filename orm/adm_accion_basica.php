<?php
namespace gamboamartin\administrador\models;

use base\orm\_modelo_parent;
use base\orm\inicializacion;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_accion_basica extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'adm_accion_basica';
        $columnas = array($tabla=> false);
        $campos_obligatorios=array('descripcion','codigo','codigo_bis','visible','seguridad','inicio','lista',
            'status','es_view','descripcion_select','etiqueta_label','es_modal','titulo','css','es_status','alias',
            'es_lista');

        $no_duplicados[] = 'descripcion';
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas,
            no_duplicados: $no_duplicados);
        $this->NAMESPACE = __NAMESPACE__;
    }

    private function alias(array $registro): string
    {
        return strtoupper($registro['descripcion']);
    }

    public function alta_bd(): array|stdClass
    {
        $registro = $this->init_alta_bd(registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar registro',data: $registro);
        }
        $this->registro = $registro;

        $r_alta_bd = parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar accion basica',data: $r_alta_bd);
        }
        return $r_alta_bd;
    }


    private function css(): string
    {
        return 'info';
    }

    /**
     * Genera la etiqueta para label
     * @param array $registro Registro en proceso
     * @return string|array
     * @version 2.82.6
     */
    private function etiqueta_label(array $registro): string|array
    {
        $keys = array('descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }
        $etiqueta_label = str_replace('_', ' ', $registro['descripcion']);
        return ucwords($etiqueta_label);
    }


    private function init_alias(array $registro): array
    {
        if(!isset($registro['alias'])){

            $alias = $this->alias(registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al genera alias',data: $alias);
            }
            $registro['alias'] = $alias;
        }
        return $registro;
    }

    private function init_alta_bd(array $registro): array
    {
        $registro = $this->init_alta_bd_base(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar registro',data: $registro);
        }

        $registro = $this->init_statuses_alta(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa status',data: $registro);
        }

        return $registro;
    }

    private function init_alta_bd_base(array $registro): array
    {

        $registro = $this->init_etiqueta_label(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera etiqueta_label',data: $registro);
        }

        $registro = $this->init_titulo(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera titulo',data: $registro);
        }

        $registro = $this->init_css(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera css',data: $registro);
        }


        $registro = $this->init_alias(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera alias',data: $registro);
        }
        return $registro;
    }

    private function init_css(array $registro): array
    {
        if(!isset($registro['css'])){
            $css = $this->css();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al genera css',data: $css);
            }

            $registro['css'] = $css;
        }
        return $registro;
    }



    private function init_etiqueta_label(array $registro): array
    {
        if(!isset($registro['etiqueta_label'])){

            $etiqueta_label = $this->etiqueta_label(registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al genera etiqueta_label',data: $etiqueta_label);
            }

            $registro['etiqueta_label'] = $etiqueta_label;
        }
        return $registro;
    }


    private function init_statuses_alta(array $registro): array
    {
        $keys_statuses = array('es_lista','es_modal','es_status','es_view','inicio','lista','seguridad','visible');

        $registro = (new inicializacion())->inicializa_statuses(keys: $keys_statuses, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa status',data: $registro);
        }
        return $registro;
    }

    private function init_titulo(array $registro): array
    {
        if(!isset($registro['titulo'])){
            $titulo = $this->titulo(registro:$registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al genera titulo',data: $titulo);
            }
            $registro['titulo'] = $titulo;
        }
        return $registro;
    }

    private function titulo(array $registro){
        return $registro['etiqueta_label'];
    }
}
