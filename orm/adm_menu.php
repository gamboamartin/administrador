<?php
namespace models;

use base\orm\modelo;
use PDO;

class adm_menu extends modelo{ //PRUEBAS FINALIZADAS
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        $campos_obligatorios = array('etiqueta_label');
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas);
    }

    /**
     * 
     * @return array
     */
	public function obten_menu_permitido(): array
    { //FIN PROT
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error(mensaje: 'Error debe existir grupo_id',data: $_SESSION, params: get_defined_vars());
        }
        if($_SESSION['grupo_id']<=0){
            return $this->error->error('Error grupo_id debe ser mayor a 0',$_SESSION);
        }
        
        $grupo_id = $_SESSION['grupo_id'];	

        $consulta = "SELECT 
        		adm_menu.id AS id ,
        		adm_menu.icono AS icono,
        		adm_menu.descripcion AS descripcion,
        		adm_menu.etiqueta_label AS etiqueta_label 
        		FROM adm_menu 
        	INNER JOIN adm_seccion  ON adm_seccion.adm_menu_id = adm_menu.id
        	INNER JOIN adm_accion  ON adm_accion.adm_seccion_id = adm_seccion.id
        	INNER JOIN adm_accion_grupo AS permiso ON permiso.adm_accion_id = adm_accion.id
        	INNER JOIN adm_grupo  ON adm_grupo.id = permiso.adm_grupo_id
        WHERE 
        	adm_menu.status = 'activo'
        	AND adm_seccion.status = 'activo'
        	AND adm_accion.status = 'activo' 
        	AND adm_grupo.status = 'activo' 
        	AND permiso.adm_grupo_id = $grupo_id 
                AND adm_accion.visible = 'activo'
        GROUP BY adm_menu.id
        ";
        $result = $this->link->query($consulta);
        if($this->link->errorInfo()[1]){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: array(array($this->link->errorInfo(),$consulta)));
        }
        $n_registros = $result->rowCount();

        $new_array = array();
        while( $row = $result->fetchObject()){
		    $new_array[] = (array)$row;
		}
        $result->closeCursor();
		return array('registros' => $new_array, 'n_registros' => $n_registros);

	}
}