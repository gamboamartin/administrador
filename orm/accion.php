<?php
namespace models;
use gamboamartin\errores\errores;
use gamboamartin\orm\modelo;
use PDO;
use stdClass;


class accion extends modelo{ //FINALIZADAS
    /**
     * DEBUG INI
     * accion constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'seccion'=>'accion', 'menu'=>'seccion');
        $campos_obligatorios = array('seccion_menu_id','visible','inicio','lista');
        $tipo_campos['seccion_menu_id'] = 'id';
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas:  $columnas,
            tipo_campos:  $tipo_campos );
        $this->validacion = new \validacion\accion();
    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @param modelo $modelo
     * @return array
     */
    public function acciones_permitidas(string $seccion, string $accion, modelo $modelo):array{
        if(!isset($_SESSION['grupo_id']) && $seccion !== 'session' && $accion !== 'login'){
            return $this->error->error('Error debe existir grupo_id',$_SESSION);
        }
        if(isset($_SESSION['grupo_id'])&&(int)$_SESSION['grupo_id']<=0 && $seccion !== 'session' && $accion !== 'login'){
            return $this->error->error('Error grupo_id debe ser mayor o igual a 0',$_SESSION);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error('Error seccion no puede venir vacio',$seccion);
        }
        $seccion_menu_id = $modelo->seccion_menu_id(seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error obtener seccion_menu_id',$seccion_menu_id);
        }

        $r_acciones = (new accion_grupo($this->link))->obten_accion_permitida(seccion_menu_id: $seccion_menu_id);
        if(errores::$error){
            return $this->error->error('Error obtener acciones permitidas',$r_acciones);
        }
        return $r_acciones->registros;
    }

    /**
     * inserta un registro de ti po accion y agrega permisos a usuarios de tipo root
     * @example
     *      $r_alta_accion = $this->accion_modelo->alta_bd();
     *
     * @return array con datos del registro insertado
     * @throws errores count($this->registro) === 0
     * @throws errores si algun key de registro es numerico
     * @throws errores definidos en internals
     * @internal  $this->valida_campo_obligatorio();
     * @internal  $this->valida_estructura_campos();
     * @internal  $this->asigna_data_user_transaccion();
     * @internal  $this->bitacora($this->registro,__FUNCTION__,$consulta);
     * @internal  $grupo_modelo->filtro_and($filtro);
     * @internal  $accion_grupo_modelo->alta_bd();
     */
    public function alta_bd(): array{

        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(isset($r_alta_bd['error'])){
            return $this->error->error('Error al dar de alta accion',$r_alta_bd);
        }

        $grupo_modelo = new grupo($this->link);
        $filtro['grupo.root']['campo'] = 'grupo.root';
        $filtro['grupo.root']['value'] = 'activo';

        $r_grupo = $grupo_modelo->filtro_and($filtro);
        if(isset($r_grupo['error'])){
            return $this->error->error('Error al obtener grupo',$r_grupo);
        }
        $grupos = $r_grupo['registros'];
        $accion_grupo_modelo = new accion_grupo($this->link);

        foreach($grupos as $grupo){
            $accion_grupo_modelo->registro['accion_id'] = $r_alta_bd['registro_id'];
            $accion_grupo_modelo->registro['grupo_id'] = $grupo['grupo_id'];
            $accion_grupo_modelo->registro['status'] = 'activo';
            $r_accion_grupo = $accion_grupo_modelo->alta_bd();
            if(isset($r_accion_grupo['error'])){
                return $this->error->error('Error al insertar accion a root',$r_accion_grupo);
            }
        }

        return $r_alta_bd;
    }

    public function cuenta_acciones(){ //FIN PROT
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error('Error debe existir grupo_id',array($_SESSION));
        }

        $grupo_id = $_SESSION['grupo_id'];
        if(isset($_SESSION['n_permisos']) && (int)$_SESSION['n_permisos']>0){
            return $_SESSION['n_permisos'];
        }
        $consulta = "SELECT COUNT(*) AS n_permisos
              		  FROM accion 
              	      INNER JOIN seccion  ON seccion.id = accion.seccion_id
              	      INNER JOIN accion_grupo AS permiso ON permiso.accion_id = accion.id
                      INNER JOIN grupo  ON grupo.id = permiso.grupo_id
                    WHERE  
                	 accion.status = 'activo' 
                	AND grupo.status = 'activo'
                	AND seccion.status = 'activo'         	
                	AND permiso.grupo_id = $grupo_id
                ";

        $result = $this->link->query($consulta);
        if($this->link->errorInfo()[1]){
            return $this->error->error('Error al ejecutar sql',array($this->link->errorInfo(),$consulta));
        }
        $row = (array)$result->fetchObject();
        $result->closeCursor();
        $n_permisos = $row['n_permisos'];
        $_SESSION['n_permisos'] = $n_permisos;
        return $n_permisos;
    }

    /**
     *
     * @param string $seccion
     * @param string $accion
     * @return bool|array
     */
    public function obten_accion_permitida_session(string $seccion, string $accion): bool|array{


        $valida = $this->validacion->valida_accion_permitida(accion: $accion,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error('Error al validar datos',$valida);
        }

        $grupo_id = $_SESSION['grupo_id'];

        if(isset($_SESSION['accion_permitida'][$seccion][$grupo_id][$accion])){
            return $_SESSION['accion_permitida'][$seccion][$grupo_id][$accion];
        }

        $accion = strtolower(trim($accion));
        $seccion = strtolower(trim($seccion));


        $filtro = array();
        $filtro['seccion.descripcion'] = $seccion;
        $filtro['grupo.id'] = $grupo_id;
        $filtro['accion.visible'] = 'inactivo';
        $filtro['accion.descripcion'] = $accion;
        $filtro['accion.status'] = 'activo';
        $filtro['seccion.status'] = 'activo';
        $filtro['grupo.status'] = 'activo';

        $existe = (new accion_grupo($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$existe);
        }

        $_SESSION['accion_permitida'][$seccion][$grupo_id][$accion] = $existe;

        return $existe;

    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @return array
     */
    private function filtro_accion_seccion(string $seccion, string $accion):array{

        $valida = $this->validacion->seccion_accion( accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error('Error al validar seccion',$valida);
        }

        $filtro['seccion.descripcion'] = strtolower(trim($seccion));
        $filtro['accion.descripcion'] = strtolower(trim($accion));

        return $filtro;
    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @return array|stdClass
     */
    private function accion_seccion(string $seccion, string $accion):array|stdClass{
        $valida = $this->validacion->seccion_accion(accion:  $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error('Error al validar seccion',$valida);
        }

        $filtro = $this->filtro_accion_seccion(seccion: $seccion, accion: $accion);
        if(errores::$error){
            return  $this->error->error('Error al obtener filtros',$filtro);
        }
        $r_accion = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return  $this->error->error('Error al obtener acciones',$r_accion);
        }
        return $r_accion;
    }

    /**
     * PRUEBAS FINALIZADAS
     * Obtiene el registro por busqueda de seccion_descripcion uy accion_descripcion
     *
     * @param string $seccion nombre de modelo seccion tabla
     * @param string  $accion accion de sistema
     * @example
     *      $accion = str_replace('_',' ', $etiqueta);
            $accion_registro = $accion_modelo->obten_accion_por_seccion_accion($this->seccion,$etiqueta);
     *
     * @return array registro encontrado
     * @throws errores seccion vacio
     * @throws errores $accion vacia
     * @throws errores la seccion no es una clase
     * @throws errores si no se encontro accion
     * @internal  $this->filtro_and($filtro,'numeros',array(),array(),0,0,array());
     * @uses  controler->genera_etiqueta_accion

     */
    public function obten_accion_por_seccion_accion(string $seccion, string $accion):array{ //FIN PROT
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($seccion === ''){
            return  $this->error->error('Error seccion no puede venir vacia',array($seccion,$accion));
        }
        if($accion === ''){
            return  $this->error->error('Error accion no puede venir vacia',array($seccion,$accion));

        }
        if(!class_exists($clase)){
            return  $this->error->error('Error no existe la clase',$clase);
        }
        if(isset($_SESSION['acciones_breads'][$seccion][$accion])){
            return $_SESSION['acciones_breads'][$seccion][$accion];
        }
        $accion_registro = $this->accion_registro($seccion, $accion);
        if(errores::$error){
            return  $this->error->error('Error al obtener acciones',$accion_registro);
        }

        $_SESSION['acciones_breads'][$seccion][$accion] = $accion_registro;

        return $accion_registro;
    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @return array
     */
    public function accion_registro(string $seccion, string $accion):array{
        $valida = $this->validacion->seccion_accion(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error('Error al validar seccion',$valida);
        }
        $r_accion = $this->accion_seccion(seccion: $seccion,accion: $accion);
        if(errores::$error){
            return  $this->error->error('Error al obtener acciones',$r_accion);
        }
        if($r_accion->n_registros===0) {
            return  $this->error->error('Error no existen acciones',$r_accion);
        }
        return $r_accion->registros[0];
    }

    /**
     *
     * @return array
     */
    public function obten_acciones_iniciales():array{
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error('Error debe existir grupo_id',array($_SESSION));
        }
        $grupo_id = $_SESSION['grupo_id'];
        if(isset($_SESSION['acciones_iniciales'])){
            return $_SESSION['acciones_iniciales'];
        }

        $consulta = "SELECT 
                      seccion.descripcion AS seccion_descripcion,
                      accion.descripcion AS accion_descripcion,
                      accion.icono as accion_icono
                    FROM accion 
                      INNER JOIN accion_grupo ON accion_grupo.accion_id = accion.id
                      INNER JOIN seccion ON seccion.id = accion.seccion_id
                      WHERE accion_grupo.grupo_id = $grupo_id AND accion.inicio = 'activo'";

        $resultado = $this->ejecuta_consulta(consulta: $consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$resultado);
        }
        $_SESSION['acciones_iniciales'] = $resultado;

        return $resultado;
    }

	public function valida_permiso(string $seccion, string $accion){//FIN PROT
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error('Error debe existir grupo_id',array($_SESSION));
        }
        if($seccion === ''){
            return $this->error->error('Error seccion esta vacia',array($seccion, $accion));
        }
        if($accion === ''){
            return $this->error->error('Error accion esta vacia',array($seccion, $accion));
        }
        $grupo_id = $_SESSION['grupo_id'];
        if(isset($_SESSION['valida_permiso'][$grupo_id][$seccion][$accion])){
            return $_SESSION['valida_permiso'][$grupo_id][$seccion][$accion];
        }

        $consulta = "SELECT count(*) AS existe
              		  FROM accion 
              	      INNER JOIN seccion  ON seccion.id = accion.seccion_id
              	      INNER JOIN accion_grupo AS permiso ON permiso.accion_id = accion.id
                      INNER JOIN grupo  ON grupo.id = permiso.grupo_id
                    WHERE  
                	 accion.status = 'activo' 
                	AND grupo.status = 'activo'
                	AND seccion.status = 'activo'         	
                	AND permiso.grupo_id = $grupo_id 
                	AND seccion.descripcion = '$seccion' AND accion.descripcion = '$accion'
                ";


        $result = $this->link->query($consulta);
        if($this->link->errorInfo()[1]){
            return $this->error->error('Error al ejecutar sql',array($this->link->errorInfo(),$consulta));
        }
        $row = (array)$result->fetchObject();
        $permiso = $row['existe'];
        $result->closeCursor();
        if($permiso == 1){
            $_SESSION['valida_permiso'][$grupo_id][$seccion][$accion] = True;
            return True;
        }
        else{
            $_SESSION['valida_permiso'][$grupo_id][$seccion][$accion] = False;
            return False;
        }
	}

}