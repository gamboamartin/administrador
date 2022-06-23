<?php
namespace models;
use base\orm\columnas;
use base\orm\modelo;
use gamboamartin\errores\errores;


use PDO;
use stdClass;

class adm_seccion extends modelo{
    /**
     * DEBUG INI
     * seccion_menu constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'adm_menu'=>$tabla);
        $campos_obligatorios = array('status','descripcion','adm_menu_id');
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios,columnas:  $columnas);
    }



    /**
     *
     * @param int $menu_id
     * @return array
     */
    public function obten_submenu_permitido(int $menu_id): array
    { //FIN PROT
        $valida = $this->validacion->valida_estructura_menu($menu_id);
        if(errores::$error){
            return $this->error->error('Error al validar ',$valida);
        }

	    $grupo_id = $_SESSION['grupo_id'];

	    $where_menu = " AND adm_seccion.adm_menu_id = $menu_id";

	    $consulta = "SELECT 
               		adm_seccion.id AS id ,
                		adm_seccion.icono AS icono,
                		adm_seccion.descripcion AS descripcion,
                		adm_seccion.etiqueta_label AS etiqueta_label,
                		adm_seccion.adm_menu_id AS menu_id
                		FROM adm_seccion
                	INNER JOIN adm_accion  ON adm_accion.adm_seccion_id = adm_seccion.id
                	INNER JOIN adm_accion_grupo AS permiso ON permiso.adm_accion_id = adm_accion.id
                	INNER JOIN adm_grupo  ON adm_grupo.id = permiso.adm_grupo_id
                	INNER JOIN adm_menu  ON adm_menu.id = adm_seccion.adm_menu_id
                WHERE 
                	adm_seccion.status = 'activo' 
                	AND adm_accion.status = 'activo' 
                	AND adm_grupo.status = 'activo' 
                	AND permiso.adm_grupo_id = $grupo_id $where_menu
                        AND adm_accion.visible = 'activo'
                GROUP BY adm_seccion.id
                ";
	    $result = $this->link->query($consulta);
	    $n_registros = $result->rowCount();

	    if($this->link->errorInfo()[1]){
	        return $this->error->error(mensaje: 'Error al ejecutar sql',data: array(array($this->link->errorInfo(),$consulta)));
	    }

	    $new_array = array();
	    while( $row = $result->fetchObject()){
	        $new_array[] = (array)$row;
	    }
        $result->closeCursor();
	    return array('registros' => $new_array, 'n_registros' => $n_registros);
	}
}