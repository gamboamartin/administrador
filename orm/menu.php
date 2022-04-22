<?php
namespace models;

use base\orm\modelo;
use PDO;

class menu extends modelo{ //PRUEBAS FINALIZADAS
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
        		menu.id AS id ,
        		menu.icono AS icono,
        		menu.descripcion AS descripcion,
        		menu.etiqueta_label AS etiqueta_label 
        		FROM menu 
        	INNER JOIN seccion  ON seccion.menu_id = menu.id
        	INNER JOIN accion  ON accion.seccion_id = seccion.id
        	INNER JOIN accion_grupo AS permiso ON permiso.accion_id = accion.id
        	INNER JOIN grupo  ON grupo.id = permiso.grupo_id
        WHERE 
        	menu.status = 'activo'
        	AND seccion.status = 'activo'
        	AND accion.status = 'activo' 
        	AND grupo.status = 'activo' 
        	AND permiso.grupo_id = $grupo_id 
                AND accion.visible = 'activo'
        GROUP BY menu.id
        ";
        $result = $this->link->query($consulta);
        if($this->link->errorInfo()[1]){
            return $this->error->error('Error al ejecutar sql',array($this->link->errorInfo(),$consulta));
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