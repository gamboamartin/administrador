<?php
namespace controllers;
use gamboamartin\errores\errores;
use controller\controlador_base;
use models\accion_grupo;

class controlador_accion_grupo extends controlador_base{
    public function __construct($link){
        $modelo = new accion_grupo($link);
        parent::__construct($link, $modelo);
    }

    /**
     *
     * @param bool $header
     * @return array|int
     */
    public function elimina_permiso_bd(bool $header){ //FIN PROT
        if(!isset($_POST)){
            $error = $this->errores->error('Error debe existir POST',$_GET);
            if(!$header){
                return $error;
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;
        }
        if(!isset($_POST['accion_id'])){
            $error = $this->errores->error('Error existir $_POST[accion_id]',$_POST);

            if(!$header){
                return $error;
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;

        }
        if((int)$_POST['accion_id']<=0){
            $error = $this->errores->error('Error debe ser mayor a 0 $_POST[accion_id]',$_POST);

            if(!$header){
                return $error;
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;

        }
        if(!isset($_POST['grupo_id'])){
            $error = $this->errores->error('Error existir $_POST[grupo_id]',$_POST);

            if(!$header){
                return $error;
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;

        }
        if((int)$_POST['grupo_id']<=0){
            $error = $this->errores->error('Error debe ser mayor a 0 $_POST[grupo_id]',$_POST);

            if(!$header){
                return $error;
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;

        }

        $accion_id = $_POST['accion_id'];
        $grupo_id = $_POST['grupo_id'];

        $accion_grupo_modelo = new accion_grupo($this->link);

        $accion_grupo_id = $accion_grupo_modelo->obten_permiso_id($accion_id,$grupo_id);

        if(errores::$error){
            $error = $this->errores->error('Error al obtener registro',$accion_grupo_id);
            if(!$header){
                return $error;
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;
        }

        $r_elimina_bd = $accion_grupo_modelo->elimina_bd($accion_grupo_id);
        if(errores::$error){
            $error = $this->errores->error('Error al eliminar registro',$r_elimina_bd);
            if(!$header){
                return $error;
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($error);
            exit;
        }

        if(!$header){
            return $accion_grupo_id;
        }

        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($accion_grupo_id);
        exit;

    }
}
