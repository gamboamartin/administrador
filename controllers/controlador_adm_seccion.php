<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use base\frontend\templates;
use config\generales;
use gamboamartin\errores\errores;
use JsonException;
use models\adm_accion;
use models\adm_accion_basica;
use models\adm_seccion;
use PDO;
use stdClass;


class controlador_adm_seccion extends controlador_base{
    public $operaciones_controlador;
    public $accion_modelo;
    public $accion_basica_modelo;
    public $seccion_menu_id = false;
    public $template_accion;


    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){

        $modelo = new adm_seccion($link);

        parent::__construct(link: $link,modelo:  $modelo, paths_conf: $paths_conf);

        $this->accion_modelo = new adm_accion($link);
        $this->accion_basica_modelo = new adm_accion_basica($link);
        $this->seccion_menu_modelo = new adm_seccion($link);
        $this->template_accion = new templates($link,'accion');

    }

    public function asigna_accion(){
        $breadcrumbs = array('lista', 'alta');

        $accion_modelo = new adm_accion($this->link);
        if(errores::$error){
            return  $this->errores->error(mensaje: 'Error al generar modelo',data: $accion_modelo);
        }

        $accion_registro = $accion_modelo->accion_registro(accion: $this->accion, seccion: $this->seccion);
        if(errores::$error){
            return  $this->errores->error('Error al obtener acciones',$accion_registro);
        }

        $this->breadcrumbs = $this->directiva->nav_breadcumbs(breadcrumbs: $breadcrumbs,seccion: $this->seccion,
            accion: $this->accion, link: $this->link,accion_registro:  $accion_registro, session_id: $this->session_id);
        $this->seccion_menu_id = $_GET['registro_id'];
        $this->operaciones_controlador->encabezados($this);
        setcookie('seccion_menu_id' , $this->seccion_menu_id);

    }

    public function accion_alta_bd(){

    }

    /**
     * @param bool $header Si header muestra resultado en front
     * @param bool $ws
     * @return array
     * @throws JsonException
     */
    public function alta_bd(bool $header, bool $ws): array{
        $this->link->beginTransaction();
        $r_alta_bd = parent::alta_bd(false, false);
        if(errores::$error){
            $this->link->rollBack();
            $error =   $this->errores->error(mensaje: 'Error al dar de alta registro',data: $r_alta_bd);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $this->link->commit();

        if($header){
            header('Location: index.php?seccion=adm_seccion&accion=lista&mensaje=Agregado con éxito&tipo_mensaje=exito&session_id=' . (new generales())->session_id);
            exit;
        }
        return $r_alta_bd;
    }

    public function get_adm_seccion(bool $header, bool $ws = true): array|stdClass
    {

        $keys['adm_menu'] = array('id','descripcion','codigo','codigo_bis');
        $keys['adm_seccion'] = array('id','descripcion','codigo','codigo_bis');



        $salida = $this->get_out(header: $header,keys: $keys, ws: $ws);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar salida',data:  $salida,header: $header,ws: $ws);

        }


        return $salida;


    }

}
