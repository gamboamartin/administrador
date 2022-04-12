<?php
namespace orm;

use base\dropbox;

use base\funciones;

use controller\controler;
use controller\files;
use gamboamartin\errores\errores;
use gamboamartin\orm\modelo;
use models\prospecto;
use models\prospecto_ubicacion;
use models\tipo_documento;
use PDO;
use SplFileInfo;
use stdClass;
use validacion\val_extension;
use validacion\val_modelo_documentos;

class modelo_documentos extends modelo { //PRUEBAS EN PROCESO
    public array $tablas_children;
    public _model_documentos_rutas $_mdr;
    public _model_documentos_dbx $_mddbx;

    public string $key_finalizado = '';
    public string $key_id = '';
    public string $key_rename_doc = '';
    public string $folder_doc_abs = '';
    public string $folder_doc = '';



    public function __construct(PDO $link, string $tabla, array $columnas_extra = array(),
                                array $campos_obligatorios = array(), array $tipo_campos = array(),
                                array $columnas = array(), array $sub_querys = array(),
                                bool $aplica_transaccion_inactivo = true, bool $aplica_bitacora = false,
                                bool $aplica_seguridad = false, array $extension_estructura = array(),
                                array $renombres = array(), bool $validation = false)
    {
        parent::__construct($link, $tabla, $columnas_extra, $campos_obligatorios, $tipo_campos, $columnas, $sub_querys,
            $aplica_transaccion_inactivo, $aplica_bitacora, $aplica_seguridad, $extension_estructura, $renombres,
            $validation);

        $this->validacion = new val_modelo_documentos();
        $this->tablas_children = array('documento','documento_prospecto','documento_prospecto_ubicacion');
        $this->_mddbx = new _model_documentos_dbx($this);
        $this->_mdr = new _model_documentos_rutas($this);

        $this->key_id = $this->tabla.'_id';
        $this->key_finalizado = $this->tabla.'_finalizado';
        $this->key_rename_doc = $this->tabla.'_rename_doc';

        $this->folder_doc = 'archivos/'.$this->tabla.'/';
        $this->folder_doc_abs = PATH_BASE.$this->folder_doc;


    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id Identificador del documento
     * @return bool|array
     */
    public function actualiza_cambiado(int $id): stdClass|array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $init = $this->inicializa_recarga($id);
        if(errores::$error){
            return $this->error->error("Error al inicializar doc", $init);
        }

        $upd = $this->upd_init($id);
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }

        $ejecuta = $this->ejecuta_cambios($id);
        if(errores::$error){
            return $this->error->error('Error al ejecuta cambios documento', $ejecuta);
        }

        $upd = $this->upd_init_cambiado($id);
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }

        return $ejecuta;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $documento Documento proveniente de FILES forma[tipo_documento_id][data de FILES]
     * @param string $key Key permitido para hacer validacion de documento
     * @param array $data_file_maquetado
     * @return array
     */
    public function ajusta_data_file_maquetado(array $documento, string $key, array $data_file_maquetado): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error al $key no puede venir vacio', $key);
        }
        foreach ($documento as $tipo_documento_id => $data) {
            if ((string)$data === '') {
                continue;
            }
            if((int)$tipo_documento_id <= 0 || (string)$tipo_documento_id === ''){
                return $this->error->error('Error el tipo documento id debe de ser mayor a 0', $tipo_documento_id);
            }
            $data_file_maquetado[$tipo_documento_id][$key] = $data;
        }
        return $data_file_maquetado;
    }



    /**
     * ALFABETICO
     */


    /**
     * PRUEBAS FINALIZADAS
     * @param int $id  Identificador del documento
     * @return array|stdClass
     */
    public function ejecuta_cambios(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $carga = $this->_mddbx->carga_doc_dbx($id);
        if(errores::$error){
            return $this->error->error('Error al cargar documento', $carga);
        }
        $verifica = $this->_mddbx->verifica_dropbox($id);
        if(errores::$error){
            return $this->error->error('Error al verificar documento', $verifica);
        }
        $full = $this->_mddbx->envia_a_full($id);
        if(errores::$error){
            return $this->error->error('Error al enviar documento', $full);
        }
        $data = new stdClass();
        $data->carga = $carga;
        $data->verifica = $verifica;
        $data->full = $full;
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @return array
     */
    public function alta_bd(): array{

        $valida = (new val_extension())->valida_data_alta_documento($this->registro, $this->link,$this->tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener extension',$valida);
        }

        $this->registro['dropbox'] = 'inactivo';
        $this->registro['fecha_alta_dropbox'] = '9999-01-01';
        $this->registro['en_full'] = 'inactivo';
        $this->registro['fecha_full'] = '9999-01-01';
        $this->registro['rename_doc'] = 'activo';
        $this->registro['finalizado'] = 'inactivo';

        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al dar de alta documento',$r_alta_bd);
        }
        return $r_alta_bd;
    }

    /**
     * PHPUNIT
     * @param array $documento
     * @return bool|array
     */
    private function aplica_eliminacion(array $documento): bool|array
    {
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla esta vacia', $this->tabla);
        }

        $keys_docs = $this->keys_docs();
        if(errores::$error){
            return $this->error->error('Error al obtener keys', $keys_docs);
        }

        $keys[] = $keys_docs->key_el;
        $keys[] = $keys_docs->key_rd;
        $keys[] = $keys_docs->key_f;

        $valida = $this->validacion->valida_statuses($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento', $valida);
        }

        $aplica_el = false;
        if($documento[$keys_docs->key_el] === 'inactivo' && $documento[$keys_docs->key_rd] === 'activo' && $documento[$keys_docs->key_f] === 'activo'){
            $aplica_el = true;
        }
        return $aplica_el;
    }

    /**
     * PHPUNIT
     * @param array $documento Registro de tipo documento modelo->registro documento_prospecto->registro modelo[0][key][value]
     * @return bool|array
     */
    private function aplica_finaliza(array $documento): bool|array
    {
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla no puede venir vacia', $this->tabla);
        }

        if(!in_array($this->tabla, $this->tablas_children)){
            return $this->error->error('Error $this->tabla no pertenece a estructura de documentos', $this->tabla);
        }

        $key_f = $this->tabla.'_finalizado';
        $key_r = $this->tabla.'_rename_doc';

        $keys = array($key_f, $key_r);
        $valida = $this->validacion->valida_statuses($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento',$valida);
        }

        $aplica = true;
        if($documento[$key_f] === 'activo'){
            $aplica = false;
        }

        if($documento[$key_r] === 'inactivo'){
            $aplica = false;
        }
        return $aplica;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function aplica_finalizado(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $documento = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }
        $aplica = $this->aplica_finaliza($documento);
        if(errores::$error){
            return $this->error->error('Error al validar si aplica finaliza', $aplica);
        }

        $documento = $this->_mddbx->finaliza_doc($aplica, $documento, $id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }

         return $documento;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $tipo_documento_id
     * @param int $id
     * @param array $guarda
     * @param string $key_id
     * @return array
     */
    public function asigna_data_documento(int $tipo_documento_id, int $id, array $guarda, string $key_id):array{

        if($tipo_documento_id<=0){
            return $this->error->error('Error tipo_documento_id debe ser mayor a 0', $tipo_documento_id);
        }
        if($id<=0){
            return $this->error->error('Error $id debe ser mayor a 0', $id);
        }

        $key_id = trim($key_id);
        if($key_id===''){
            return $this->error->error('Error $key_id no puede venir vacio', $key_id);
        }
        $keys = array('ruta','ruta_relativa','ruta_absoluta');
        $valida = $this->validacion->valida_existencia_keys($guarda, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar guarda', $valida);
        }
        if(!file_exists($guarda['ruta_absoluta'])){
            return $this->error->error('Error no existe documento en ruta', $guarda['ruta_absoluta']);
        }

        $keys_id_validos = array('prospecto_ubicacion_id','prospecto_id');

        if(!in_array($key_id, $keys_id_validos)){
            return $this->error->error('Error key id invalido '.$key_id.' Keys validos en data', $keys_id_validos);
        }

        $this->registro = array();
        $this->registro['status'] = 'activo';
        $this->registro['tipo_documento_id'] = $tipo_documento_id;
        $this->registro[$key_id] = $id;
        $this->registro['ruta'] = $guarda['ruta'];
        $this->registro['ruta_relativa'] = $guarda['ruta_relativa'];
        $this->registro['ruta_absoluta'] = $guarda['ruta_absoluta'];
        $this->registro['redimencionado'] = 'inactivo';
        $this->registro['error_dbx'] = 'inactivo';
        $this->registro['en_full'] = 'inactivo';
        $this->registro['dropbox'] = 'inactivo';
        $this->registro['cambiado'] = 'inactivo';
        $this->registro['subido_drive'] = 'inactivo';
        $this->registro['eliminado_local'] = 'inactivo';

        return $this->registro;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $documento Registro de tipo documento con prefijo de tabla
     * @param array $rutas_documentos Rutas para la asignacion de documentos temporales
     * @return array
     */
    public function asigna_data_ruta_paquete(array $documento, array $rutas_documentos): array
    {
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla no puede venir vacio', $this->tabla);
        }

        $key = $this->tabla.'_ruta_relativa';

        $keys = array($key);
        $valida = $this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }


        $valida = $this->validacion->valida_data_doc_base($this, $documento);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }

        $documento = $this->recarga_doc_full($documento);
        if(errores::$error){
            return $this->error->error('Error al recargar documento', $documento);
        }

        $name = $this->_mdr->name_temp();
        $name = PATH_BASE.'archivos/temporales/'.$name;

        $doc_dbx = (new dropbox())->download($documento[$key],false,$name);
        if(errores::$error){
            return $this->error->error('Error descargar de dbx', $doc_dbx);
        }
        file_put_contents($name, $doc_dbx->contenido);
        chmod($name, 0777);

        $rutas_documentos[] = $name;
        return $rutas_documentos;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param array $documento Registro de tipo documento con tabla como prefijo en datos
     * @return array|stdClass
     */
    public function asigna_parents(array $documento): array|stdClass
    {
        if(count($documento) === 0){
            return $this->error->error('El documento no tiene datos', $documento);
        }
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla no puede venir vacio', $this->tabla);
        }
        $data = $this->init_data_parents($documento);
        if(errores::$error){
            return $this->error->error('Error al inicializar datos', $data);
        }

        $data = $this->doc_parent($documento, $data);
        if(errores::$error){
            return $this->error->error('Error al obtener parents', $data);
        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $data
     * @param string $ruta
     * @param int $registro_id
     * @return array|stdClass
     */
    public function base_carga_doc(array $data, string $ruta, int $registro_id): array|stdClass
    {
        $valida = $this->validacion->valida_data_doc($data, $ruta, $registro_id);
        if(errores::$error){
            return $this->error->error('Error validar data', $valida);
        }
        $guarda = $this->guarda_documento($data,$ruta,$registro_id);
        if(errores::$error){
            return $this->error->error('Error al guardar archivo', $guarda);
        }

        $data = new stdClass();
        $data->guarda = $guarda;
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $documento_id
     * @param string $key_parent_id
     * @return array|string|stdClass
     */
    public function carga_doc_drive(int $documento_id, string $key_parent_id): array|string|stdClass
    {
        if($documento_id<=0){
            return $this->error->error('Error el $documento_id debe ser mayor a 0', $documento_id);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return $this->error->error('Error $key_id_parent viene vacia', $key_parent_id);
        }

        $data = $this->data_file($documento_id,$key_parent_id);//model
        if(errores::$error){
            return $this->error->error('Error al generar data', $data);
        }

        $data_file = (new gdrive())->carga_doc($data->name, $data->ruta_origen, $data->ruta_destino,
            $this->link,$data->tipo_documento_id);
        if(errores::$error){
            return $this->error->error('Error al subir documento', $data_file);
        }
        return $data_file;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $data_file_maquetado
     * @param string $ruta
     * @param int $registro_id
     * @param string $key_id
     * @return array
     */
    public function carga_documentos(array $data_file_maquetado, string $ruta, int $registro_id, string $key_id):array{

        $result = array();
        foreach($data_file_maquetado as $tipo_documento_id=>$data){
            if(!is_array($data)){
                return $this->error->error('Error data debe se run array', $data);
            }
            if($tipo_documento_id<=0){
                return $this->error->error('Error $tipo_documento_id debe ser mayor a 0',$tipo_documento_id);
            }
            $tipo_documento = (new tipo_documento($this->link))->tipo_documento($tipo_documento_id);
            if(errores::$error){
                return $this->error->error('Error al obtener tipo_documento', $tipo_documento);
            }
            $valida = $this->validacion->valida_carga_doc($key_id, $registro_id, $tipo_documento_id, $data, $ruta, $this);
            if(errores::$error){
                return $this->error->error('Error al validar datos', $valida);
            }

            $r_alta_doc = $this->inserta_documento_ctl($data,$ruta,$tipo_documento_id,$registro_id,$key_id);
            if(errores::$error){
                return $this->error->error('Error al guardar registro', $r_alta_doc);
            }
            $result[] = $r_alta_doc;

        }
        return $result;
    }

    /**
     * PHPUNIT
     * @param array $registro
     * @param string $tabla
     * @param string $key
     * @return array|string
     */
    private function data_doc(array $registro, string $tabla, string $key): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error $tabla no puede venia vacia', $tabla);
        }
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error $key no puede venia vacia', $key);
        }
        $key_compuesto = $tabla.'_'.$key;

        $keys = array($key_compuesto);
        $valida = $this->validacion->valida_existencia_keys($registro, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar registro', $valida);
        }
        return (string)$registro[$tabla.'_'.$key];
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @param string $key_id_parent
     * @return array|stdClass
     */
    public function data_file(int $id, string $key_id_parent): array|stdClass
    {
        if($id<=0){
            return $this->error->error('Error el id debe ser mayor a 0', $id);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key_id_parent = trim($key_id_parent);
        if($key_id_parent === ''){
            return $this->error->error('Error $key_id_parent viene vacia', $key_id_parent);
        }

        $documento = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }

        $name = $this->_mdr->name_doc($documento);//model
        if(errores::$error){
            return $this->error->error('Error al generar name', $name);
        }

        $key_ro = $this->tabla.'_ruta_absoluta';

        $keys = array($key_id_parent);
        $valida = $this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }

        $ruta_origen = $documento[$key_ro];


        $ruta_destino = $this->_mdr->ruta_destino($documento[$key_id_parent]);//model
        if(errores::$error){
            return $this->error->error('Error al generar ruta_destino', $ruta_destino);
        }

        $data = new stdClass();
        $data->name = $name;
        $data->ruta_origen = $ruta_origen;
        $data->ruta_destino = $ruta_destino;
        $data->tipo_documento_id = $documento['tipo_documento_id'];

        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS/NO SE MUEVE
     * @param int $documento_id $id Identificador del documento
     * @param bool $name_file
     * @return array
     */
    public function data_file_detalle(int $documento_id, bool $name_file = true): array
    {
        if($documento_id<=0){
            return $this->error->error('Error $documento_id debe ser mayor a 0',$documento_id);
        }

        $documento = $this->registro($documento_id);
        if(errores::$error){
            return $this->error->error('Error al obtener datos del documento',$documento);
        }

        $data_file = $this->_mdr->data_file_para_zip($documento, $name_file);
        if(errores::$error){
            return $this->error->error('Error al obtener data_file',$data_file);
        }

        return array('data_file'=>$data_file,'documento'=>$documento);
    }

    /**
     * PRUEBAS FINALIZADAS
     * Devuelve un array que maqueta en un array el tipo de documento a insertarse
     *
     * @param array $data_file_maquetado array de todos los documentos
     * @param array $documento Documento proveniente de FILES forma[tipo_documento_id][data de FILES]
     * @param string $key Key permitido para hacer validacion de documento
     *
     * @return array array con datos maquetados de los documentos
     * @throws errores $documento['tipo_documento_id'] <= 0
     * @throws errores (string)$key == ''
     *
     * @uses  ctl_bass
     */
    public function data_file_maquetado(array $data_file_maquetado, array $documento, string $key):array{
        if($key === ''){
            return $this->error->error('Error al $key no puede venir vacio', $key);
        }
        $keys_aceptados = array('name','type','tmp_name');
        if(in_array($key,$keys_aceptados)) {
            $data_file_maquetado = $this->ajusta_data_file_maquetado($documento, $key, $data_file_maquetado);
            if(errores::$error){
                return $this->error->error('Error al ajustar data file', $data_file_maquetado);
            }
        }
        return $data_file_maquetado;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @return array
     */
    public function data_files():array{
        if(!isset($_FILES['tipo_documento_id'])){
            return $this->error->error('Error no existe FILES[tipo_documento_id]', $_GET);
        }
        if(!is_array($_FILES['tipo_documento_id'])){
            return $this->error->error('Error no es array FILES[tipo_documento_id]', $_FILES);
        }
        $data_file_maquetado = array();
        foreach($_FILES['tipo_documento_id'] as $key =>$documento){
            if(!is_array($documento)){
                return $this->error->error('Error documento debe ser un array', $documento);
            }
            $data_file_maquetado = $this->data_file_maquetado($data_file_maquetado,$documento, $key);
            if(errores::$error) {
                return $this->error->error('Error al maquetar documento', $data_file_maquetado);
            }
        }
        return $data_file_maquetado;
    }

    /**
     * @param int $documento_id Identificador del documento
     * @return array|stdClass
     */
    public function data_for_download(int $documento_id): array|stdClass
    {
        $data = $this->get_doc_min_validado($documento_id);
        if(errores::$error){
            return $this->error->error('Error al obtener datos de descarga',$data);
        }

        $mime_type = $this->mime_type($data->ruta_doc);
        if(errores::$error){
            return $this->error->error('Error al obtener $name_file', $mime_type);
        }

        $contenido = file_get_contents($data->ruta_doc);

        $data_dw = new stdClass();
        $data_dw->data = $data;
        $data_dw->mime_type = $mime_type;
        $data_dw->contenido = $contenido;
        return $data_dw;

    }

    /**
     * PARAMETROS
     * @param int $documento_id Identificador del documento
     * @return array|stdClass
     */
    public function data_parents(int $documento_id): array|stdClass
    {

        $documento = $this->registro($documento_id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }

        $data = $this->asigna_parents($documento);
        if(errores::$error){
            return $this->error->error('Error al inicializar datos', $data);
        }

        return $data;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $documento
     * @param string $parent
     * @param string $ruta_dbx
     * @param bool $header
     * @return array|stdClass
     */
    public function descarga(array $documento, string $parent, string $ruta_dbx, bool $header): array|stdClass
    {
        $valida = $this->validacion->valida_data_name_descarga($parent, $documento);
        if(errores::$error){
            return $this->error->error('Error al validar datos del nombre del doc', $valida);
        }
        $ruta_dbx = trim($ruta_dbx);
        if($ruta_dbx === ''){
            return $this->error->error('Error $ruta_dbx no puede venir vacio', $ruta_dbx);
        }

        $name = $this->_mdr->name_doc_download($documento, $parent);
        if(errores::$error){
            return $this->error->error('Error al generar name', $name);
        }

        $descarga = (new dropbox())->download($ruta_dbx,$header,$name);
        if(errores::$error){
            return $this->error->error('Error al descargar info', $descarga);
        }
        return $descarga;
    }

    /**
     * @param int $documento_id Identificador del documento
     * @param bool $header
     * @param string $name_download
     * @return array|stdClass
     */
    public function descarga_comprimido_calidad(int $documento_id, bool $header, string $name_download): array|stdClass
    {
        $data_dw = $this->data_for_download($documento_id);
        if(errores::$error){
            return $this->error->error('Error al obtener $name_file', $data_dw);
        }

        if($name_download!==''){
            $data_dw->data->name_file = $name_download;
        }

        $ejecuta_header = $this->header($header, $data_dw->data->name_file,
            $data_dw->mime_type, $data_dw->contenido);
        if(errores::$error){
            return $this->error->error('Error al aplicar header', $ejecuta_header);
        }

        return $data_dw;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @param bool $header
     * @return array|string
     */
    public function descarga_doc_comprimido(int $id, bool $header): array|string
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $data_file_detalle = $this->data_file_detalle( $id);

        if(errores::$error){
            return $this->error->error('Error al obtener data file',$data_file_detalle);
        }

        $doc = $this->_mddbx->genera_doc_temp($id, $data_file_detalle['documento']);
        if(errores::$error){
            return $this->error->error('Error al generar documento',$doc);
        }

        $descarga = compresor::descarga_zip($doc->ruta_temp,$data_file_detalle['data_file']['name_file_sin_extension'],
            $data_file_detalle['documento']['extension_ext'],$header);
        if(errores::$error){
            return $this->error->error('Error al descargar documento',$descarga);
        }
        unlink($doc->ruta_temp);
        return $descarga;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $drive_ruta_id
     * @return array
     */
    public function doc_drive_upd(int $drive_ruta_id): array
    {
        if($drive_ruta_id<=0){
            return $this->error->error('Error $drive_ruta_id debe ser mayor a 0', $drive_ruta_id);
        }
        $documento_upd['drive_ruta_id'] = $drive_ruta_id;
        $documento_upd['fecha_subida_drive'] = date('Y-m-d');
        $documento_upd['subido_drive'] = 'activo';
        $documento_upd['cambiado'] = 'inactivo';

        return $documento_upd;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param array $documento Registro de tipo documento con tabla como prefijo en datos
     * @param stdClass $data Obj con datos de los parents data->ubicacion, data->prospecto_ubicacion,data->cliente, data->prospecto
     * @return array|stdClass
     */
    public function doc_parent(array $documento, stdClass $data): array|stdClass
    {
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla no puede venir vacio', $this->tabla);
        }
        if(count($documento) === 0){
            return $this->error->error('Error el documento esta vacio', $documento);
        }

        $data = $this->doc_parent_pu($documento, $data);
        if(errores::$error){
            return $this->error->error('Error al obtener parents', $data);
        }
        $data = $this->doc_parent_p($documento, $data);
        if(errores::$error){
            return $this->error->error('Error al obtener parents', $data);
        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param array $documento Registro de tipo documento con tabla como prefijo en datos
     * @param stdClass $data Obj con datos de los parents data->ubicacion, data->prospecto_ubicacion,data->cliente, data->prospecto
     * @return array|stdClass
     */
    public function doc_parent_p(array $documento, stdClass $data): array|stdClass
    {
        if(count($documento) === 0){
            return $this->error->error('Error el documento esta vacio', $documento);
        }
        if($this->tabla === 'documento_prospecto'){
            $keys = array('prospecto_id');
            $valida = $this->validacion->valida_ids($documento, $keys);
            if(errores::$error){
                return $this->error->error('Error al validar documento', $valida);
            }

            $parents = $this->parents_documento_prospecto($documento['prospecto_id']);
            if(errores::$error){
                return $this->error->error('Error al obtener parents', $parents);
            }
            $data->prospecto = $parents->prospecto;
            $data->cliente = $parents->cliente;

        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param array $documento Registro de tipo documento con tabla como prefijo en datos
     * @param stdClass $data Obj con datos de los parents data->ubicacion, data->prospecto_ubicacion,data->cliente, data->prospecto
     * @return array|stdClass
     */
    public function doc_parent_pu(array $documento, stdClass $data): array|stdClass
    {
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla no puede venir vacio', $this->tabla);
        }
        if($this->tabla === 'documento_prospecto_ubicacion'){
            $keys= array('prospecto_ubicacion_id');
            $valida = $this->validacion->valida_ids($documento, $keys);
            if(errores::$error){
                return $this->error->error('Error al validar documento', $valida);
            }

            $parents = $this->parents_documento_prospecto_ubicacion($documento['prospecto_ubicacion_id']);
            if(errores::$error){
                return $this->error->error('Error al obtener parents', $parents);
            }
            $data->prospecto_ubicacion = $parents->prospecto_ubicacion;
            $data->ubicacion = $parents->ubicacion;
        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $limit
     * @return array
     */
    public function docs_cambiados(int $limit): array
    {
        if($limit<=0){
            return $this->error->error('Error limit debe ser mayor a 0', $limit);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key = $this->tabla.'.cambiado';
        $filtro[$key] = 'activo';

        $r_doc = $this->filtro_and(filtro:$filtro,order: array($this->tabla.'.id'=>'ASC'), limit: $limit);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $r_doc);
        }
        return $r_doc['registros'];
    }


    /**
     * PRUEBAS FINALIZADAS
     * @param int $limit
     * @return array
     */
    public function docs_con_error(int $limit): array
    {
        if($limit<=0){
            return $this->error->error('Error limit debe ser mayor a 0', $limit);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key = $this->tabla.'.error_dbx';
        $filtro[$key] = 'activo';

        $r_doc = $this->filtro_and(filtro:$filtro,order: array($this->tabla.'.id'=>'ASC'), limit: $limit);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $r_doc);
        }
        return $r_doc['registros'];
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $limit
     * @return array
     */
    public function docs_sin_dropbox(int $limit): array
    {
        if($limit<=0){
            return $this->error->error('Error limit debe ser mayor a 0', $limit);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key = $this->tabla.'.dropbox';
        $filtro[$key] = 'inactivo';

        $r_doc = $this->filtro_and(filtro:$filtro,order: array($this->tabla.'.fecha_alta_dropbox'=>'ASC'), limit: $limit);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $r_doc);
        }
        return $r_doc['registros'];

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $limit
     * @return array
     */
    public function docs_sin_finalizar(int $limit): array
    {
        if($limit<=0){
            return $this->error->error('Error limit debe ser mayor a 0', $limit);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key = $this->tabla.'.finalizado';
        $filtro[$key] = 'inactivo';

        $key = $this->tabla.'.rename_doc';
        $filtro[$key] = 'activo';

        $r_doc = $this->filtro_and(filtro:$filtro,order: array($this->tabla.'.id'=>'ASC'), limit: $limit);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $r_doc);
        }
        return $r_doc['registros'];
    }

    /**
     * PHPUNIT
     * @param int $limit
     * @return array
     */
    public function docs_sin_full(int $limit): array
    {
        if($limit<=0){
            return $this->error->error('Error limit debe ser mayor a 0', $limit);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key = $this->tabla.'.en_full';
        $filtro[$key] = 'inactivo';

        $r_doc = $this->filtro_and(filtro:$filtro,order: array($this->tabla.'.fecha_full'=>'ASC'), limit: $limit);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $r_doc);
        }
        return $r_doc['registros'];
    }

    /**
     *
     * @param int $limit
     * @return array
     */
    public function docs_sin_redimencionar(int $limit): array
    {
        if($limit<=0){
            return $this->error->error('Error limit debe ser mayor a 0', $limit);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key = $this->tabla.'.redimencionado';
        $filtro[$key] = 'inactivo';

        $r_doc = $this->filtro_and(filtro:$filtro,order: array($this->tabla.'.id'=>'DESC'), limit: $limit);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $r_doc);
        }
        return $r_doc['registros'];
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $documento
     * @param string $tabla
     * @return array|string
     */
    public function documento_id_data(array $documento, string $tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error $tabla no puede venia vacia', $tabla);
        }

        $data = $this->data_doc($documento,$tabla,'id');
        if(errores::$error){
            return $this->error->error('Error al generar dato',$data);
        }
        if(!is_numeric($data)){
            return $this->error->error('Error el resultado debe ser un numero',$data);
        }

        return (string)$data;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @return array
     */
    public function documento_validado(int $id, string $parent): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $documento = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener $documento', $documento);
        }
        $valida = $this->validacion->valida_data_name_descarga($parent, $documento);
        if(errores::$error){
            return $this->error->error('Error al validar datos del nombre del doc', $valida);
        }
        return $documento;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $parent_id
     * @param string $tabla_paren
     * @return array
     */
    public function documentos(int $parent_id , string $tabla_paren): array
    {
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        if($parent_id<=0){
            return $this->error->error('Error $parent_id deb ser mayor a 0', $parent_id);
        }
        $tabla_paren = trim($tabla_paren);
        if($tabla_paren === ''){
            return $this->error->error('Error $tabla_paren viene vacia', $tabla_paren);
        }

        $key_filtro = $tabla_paren.'.id';
        $filtro[$key_filtro] = $parent_id;
        $result = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $result);
        }
        return $result['registros'];
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @param bool $header
     * @param string $parent
     * @return array|stdClass
     */
    public function download(int $id, bool $header, string $parent): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $parent = trim($parent);
        if($parent === ''){
            return $this->error->error('Error parent no puede venir vacio', $parent);
        }

        $data = $this->_mddbx->data_download($id);
        if(errores::$error){
            return $this->error->error('Error al obtener info del documento', $data);
        }

        $descarga = $this->descarga($data->documento, $parent, $data->ruta_relativa, $header);
        if(errores::$error){
            return $this->error->error('Error al descargar info', $descarga, $header);
        }
        return $descarga;
    }

    /**
     * @param string $parent_name Nombre del catalogo padre para documento_prospecto es prospecto
     * @param int $id $id Identificador del documento
     * @param bool $header Si es header ejecutara descarga desde navegador
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @return array|stdClass
     */
    public function download_con_name(string $parent_name, int $id, bool $header, string $parent): array|stdClass
    {
        $name = $this->_mdr->name_download_parent($id, $parent_name);
        if(errores::$error){
            return $this->error->error('Error al obtener name', $name);
        }

        $documento = $this->_mddbx->descarga_segura_c_name($id, $name, $parent, $header);
        if(errores::$error){
            return $this->error->error('Error al obtener $documento', $documento);
        }
        return $documento;
    }

    /**
     * @param stdClass $full
     * @param bool $header Si es header ejecutara descarga desde navegador
     * @param string $parent
     * @return array|stdClass
     */
    public function download_data_full(stdClass $full, bool $header, string $parent): array|stdClass
    {
        $name = $this->_mdr->name_doc_download($full->documento, $parent);
        if(errores::$error){
            return $this->error->error('Error al obtener name', $name);
        }
        $descarga = ((new dropbox()))->download($full->ruta_full, $header,$name);
        if(errores::$error){
            return $this->error->error('Error al descargar doc', $descarga);
        }
        return $descarga;
    }

    /**
     * @param int $id $id Identificador del documento
     * @param bool $header Si es header ejecutara descarga desde navegador
     * @param string $parent
     * @return array|stdClass
     */
    public function download_doc_full(int $id, bool $header, string $parent): array|stdClass
    {
        $full = $this->_mddbx->data_download_full($id);
        if(errores::$error){
            return $this->error->error('Error al enviar a full', $full);
        }
        $descarga = $this->download_data_full($full, $header, $parent);
        if(errores::$error){
            return $this->error->error('Error al obtener $descarga', $descarga);
        }
        return $descarga;
    }

    public function download_parent_data(int $id, string $parent_name, bool $header): array|stdClass
    {
        $name = $this->_mdr->name_download_parent($id, $parent_name);
        if(errores::$error){
            return $this->error->error('Error al obtener name', $name);
        }

        if($parent_name === 'cliente'){
            $parent = 'prospecto';
        }

        $documento = $this->_mddbx->descarga_segura_c_name($id, $name, $parent, $header);
        if(errores::$error){
            return $this->error->error('Error al obtener $documento', $documento);
        }
        return $documento;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @return array|string
     */
    public function ejecuta_eliminacion(int $id): array|string
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $ruta_absoluta = $this->_mdr->ruta_absoluta($id);
        if(errores::$error){
            return $this->error->error('Error al obtener ruta absoluta', $ruta_absoluta);
        }

        if(!file_exists($ruta_absoluta)){
            return $this->error->error('Error no existe documento', $ruta_absoluta);
        }
        unlink($ruta_absoluta);
        $upd = $this->upd_eliminacion_local($id);
        if(errores::$error){
            return $this->error->error('Error al actualizar registro', $upd);
        }
        return $ruta_absoluta;
    }

    public function elimina_bd(int $id): array
    {
        $documento = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }

        $r_elimina_bd = parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al eliminar documento', $r_elimina_bd);
        }

        $key_base = $this->tabla;
        $key_ruta_relativa = $key_base.'_ruta_relativa';

        $ruta_server = $documento[$key_ruta_relativa];
        $ruta_dbx = $documento[$key_ruta_relativa];
        $ruta_dbx_full = 'full/'.$documento[$key_ruta_relativa];

        if(file_exists($ruta_server)){
            unlink($ruta_server);
        }
        $ruta_doc_min = $this->ruta_doc_min_abs($documento);
        if(errores::$error){
            return $this->error->error('Error al obtener $ruta_doc_min', $ruta_doc_min);
        }

        if(file_exists($ruta_doc_min)){
            unlink($ruta_doc_min);
        }

        $del = (new dropbox())->elimina_si_existe($ruta_dbx);
        if(errores::$error){
            return $this->error->error('Error al eliminar de dbx', $del);
        }

        $del = (new dropbox())->elimina_si_existe($ruta_dbx_full);
        if(errores::$error){
            return $this->error->error('Error al eliminar de dbx', $del);
        }


        return $r_elimina_bd;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $documento
     * @param int $id
     * @return bool|array
     */
    public function elimina_doc_local(array $documento, int $id): bool|array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $aplica_el = $this->aplica_eliminacion($documento);
        if(errores::$error){
            return $this->error->error('Error al validar eliminacion', $aplica_el);
        }

        if($aplica_el){
            $del = $this->ejecuta_eliminacion($id);
            if(errores::$error){
                return $this->error->error('Error al eliminar ruta de local', $del);
            }
        }
        return $aplica_el;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $parent_id
     * @param string $tabla_parent
     * @return array
     */
    public function elimina_docs_dbx(int $parent_id, string $tabla_parent): array
    {
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        if($parent_id<=0){
            return $this->error->error('Error $parent_id deb ser mayor a 0', $parent_id);
        }
        $tabla_paren = trim($tabla_parent);
        if($tabla_paren === ''){
            return $this->error->error('Error $tabla_paren viene vacia', $tabla_paren);
        }

        $documentos = $this->documentos($parent_id, $tabla_parent);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos', $documentos);
        }
        foreach($documentos as $documento){
            $key = 'documento_'.$tabla_parent.'_id';
            $elimina = $this->elimina_to_dbx_full($documento[$key]);
            if(errores::$error){
                return $this->error->error('Error al eliminar rutas', $elimina);
            }
        }
        return $documentos;
    }

    /**
     * PRUEBAS FINALIZADAS/NO MOVER
     * @param int $id
     * @return array
     */
    public function elimina_local(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $documento = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }

        $recarga = $this->_mddbx->revalida_docs_dbx( $id);
        if(errores::$error){
            return $this->error->error('Error al aplicar finalizado', $recarga);
        }

        $del = $this->elimina_doc_local($documento, $id);
        if(errores::$error){
            return $this->error->error('Error al eliminar ruta de local', $del);
        }

        return $documento;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @return array|stdClass
     */
    public function elimina_to_dbx(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $rutas = $this->_mdr->rutas($id);
        if(errores::$error){
            return $this->error->error('Error al obtener ruta', $rutas);
        }

        $elimina = (new dropbox())->elimina_si_existe($rutas->ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al $elimina si existe', $elimina);
        }
        $elimina = (new dropbox())->elimina_si_existe($rutas->ruta_full);
        if(errores::$error){
            return $this->error->error('Error al $elimina si existe', $elimina);
        }
        return $rutas;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @return array|stdClass
     */
    public function elimina_to_dbx_full(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $elimina = $this->elimina_to_dbx($id);
        if(errores::$error){
            return $this->error->error('Error al eliminar rutas', $elimina);
        }
        $init = $this->upd_init_cambiado_si($id);
        if(errores::$error){
            return $this->error->error('Error al inicializar registro rutas', $init);
        }
        return $elimina;
    }


    /**
     * @param int $documento_id Identificador del documento
     * @return array|stdClass
     */
    public function get_doc_min(int $documento_id): array|stdClass
    {
        $documento = $this->registro($documento_id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }

        $name_file = $this->name_file_min($documento_id, $documento['extension_ext']);
        if(errores::$error){
            return $this->error->error('Error al obtener $name_file', $name_file);
        }

        $ruta_doc = $this->ruta_doc_min($name_file);
        if(errores::$error){
            return $this->error->error('Error al obtener $name_file', $ruta_doc);
        }
        $data = new stdClass();
        $data->documento = $documento;
        $data->name_file = $name_file;
        $data->ruta_doc = $ruta_doc;
        return $data;
    }

    /**
     * @param int $documento_id Identificador del documento
     * @return array|stdClass
     */
    public function get_doc_min_validado(int $documento_id): array|stdClass
    {
        $data = $this->get_doc_min($documento_id);
        if(errores::$error){
            return $this->error->error('Error al obtener $name_file', $data->ruta_doc);
        }

        if(!file_exists($data->ruta_doc)){
            $comprime = $this->comprime_documento_calidad($documento_id);
            if(errores::$error){
                return $this->error->error('Error al obtener documento', $comprime);
            }
        }
        return $data;
    }

    public function header(bool $header, string $name_file, string $mime_type, string $contenido ){

        if($header) {
            ob_clean();
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$name_file");
            header("Content-Type: $mime_type");
            header("Content-Transfer-Encoding: binary");
            echo $contenido;
            exit;
        }
        return $contenido;
    }

    /**
     * PHPUNIT
     * @param array $documento Registro de tipo documento con tabla como prefijo en datos
     * @return stdClass|array
     */
    private function init_data_parents(array $documento): stdClass|array
    {
        if(count($documento) === 0){
            return $this->error->error('El documento no tiene datos', $documento);
        }
        $data = new stdClass();

        $data->prospecto_ubicacion = array();
        $data->ubicacion = array();
        $data->prospecto = array();
        $data->cliente = array();
        $data->documento = $documento;

        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @param int $tipo_documento_id
     * @param string $key_id
     * @return array
     */
    public function maqueta_filtro_para_documento(int $id, int $tipo_documento_id, string $key_id): array{

        $valida = $this->validacion->valida_registro_doc($key_id, $id, $tipo_documento_id, $this);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }


        $filtro[$this->tabla.'.'.$key_id] = $id;
        $filtro[$this->tabla.'.tipo_documento_id'] = $tipo_documento_id;
        return $filtro;

    }

    public function mime_type(string $ruta_doc): bool|string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($finfo, $ruta_doc);

    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param int $documento_id Identificador del documento
     * @param string $extension Extension del documento
     * @return string|array
     */
    public function name_file_min(int $documento_id, string $extension): string|array
    {
        if($documento_id<=0){
            return $this->error->error('Error documento_id debe ser mayor a 0', $documento_id);
        }
        $extension = trim($extension);
        if($extension === ''){
            return $this->error->error('Error $extension esta vacia', $extension);
        }
        return "min_$documento_id.$extension";
    }

    public function name_temp_sin_ext(): string
    {
        return time().'_'.mt_rand(100,999).'_'.mt_rand(100,999);
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param int $prospecto_ubicacion_id
     * @return array|stdClass
     */
    public function parents_documento_prospecto_ubicacion(int $prospecto_ubicacion_id): array|stdClass
    {
        if($prospecto_ubicacion_id<=0){
            return $this->error->error('Error $prospecto_ubicacion_id debe ser mayor a 0', $prospecto_ubicacion_id);
        }
        $prospecto_ubicacion = (new prospecto_ubicacion($this->link))->registro($prospecto_ubicacion_id);
        if(errores::$error){
            return $this->error->error('Error al obtener prospecto_ubicacion', $prospecto_ubicacion);
        }
        $ubicacion = (new prospecto_ubicacion($this->link))->ubicacion($prospecto_ubicacion_id);
        if(errores::$error){
            return $this->error->error('Error al obtener ubicacion', $ubicacion);
        }

        $data = new stdClass();
        $data ->prospecto_ubicacion = $prospecto_ubicacion;
        $data ->ubicacion = $ubicacion;

        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param int $prospecto_id Identificador de prospecto
     * @return array|stdClass
     */
    public function parents_documento_prospecto(int $prospecto_id): array|stdClass
    {
        if($prospecto_id<=0){
            return $this->error->error('Error $prospecto_id debe ser mayor a 0', $prospecto_id);
        }
        $prospecto = (new prospecto($this->link))->registro($prospecto_id);
        if(errores::$error){
            return $this->error->error('Error al obtener prospecto_ubicacion', $prospecto);
        }
        $cliente = (new prospecto($this->link))->obten_data_cliente($prospecto_id);
        if(errores::$error){
            return $this->error->error('Error al obtener ubicacion', $cliente);
        }

        $data = new stdClass();
        $data ->prospecto = $prospecto;
        $data ->cliente = $cliente;

        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @param int $tipo_documento_id
     * @param string $key_id
     * @return array
     */
    public function obten_documento(int $id, int $tipo_documento_id, string $key_id):array{
        $valida = $this->validacion->valida_registro_doc($key_id, $id, $tipo_documento_id, $this);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $filtro = $this->maqueta_filtro_para_documento($id,$tipo_documento_id,$key_id);
        if(errores::$error){
            return $this->error->error('Error al maquetar filtro', $filtro);
        }
        $r_doc = $this->filtro_and($filtro);

        if(errores::$error){
            return $this->error->error('Error al obtener img', $r_doc);
        }
        return $r_doc;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $ruta_dbx
     * @param string $origen
     * @param int $id
     * @return array
     */
    public function recarga_doc(string $ruta_dbx, string $origen, int $id): array
    {
        $origen = trim($origen);
        if($origen === ''){
            return $this->error->error('Error $origen no puede venir vacio', $origen);
        }
        $ruta_dbx = trim($ruta_dbx);
        if($ruta_dbx === ''){
            return $this->error->error('Error $ruta_dbx no puede venir vacio', $ruta_dbx);
        }
        if(!file_exists($origen)){
            return $this->error->error('Error no existe el documento origen', $origen);
        }

        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $crea = (new dropbox())->crea_doc($ruta_dbx, $origen);
        if(errores::$error){
            return $this->error->error('Error al cargar documento', $crea);
        }


        return $crea;
    }

    /**
     * @param array $documento Registro de tipo documento con prefijo de tabla
     * @return array
     */
    public function recarga_doc_full(array $documento): array
    {
        $valida = $this->validacion->valida_data_doc_base($this, $documento);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }

        if($documento[$this->key_rename_doc] === 'inactivo'){
            $rename = $this->rename_doc($documento[$this->key_id]);
            if(errores::$error){
                return $this->error->error('Error al renombrar documento', $rename);
            }
        }
        if($documento[$this->key_finalizado] === 'inactivo'){
            $finaliza = $this->aplica_finalizado($documento[$this->key_id]);
            if(errores::$error){
                return $this->error->error('Error al $finaliza documento', $finaliza);
            }
        }
        $documento = $this->registro($documento[$this->key_id]);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }
        return $documento;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $documentos
     * @return array
     */
    public function recarga_docs(array $documentos): array
    {
        if($this->tabla === ''){
            return $this->error->error('Error $this->tablas esta vacia', $this->tabla);
        }
        $key_rr = $this->tabla.'_ruta_relativa';
        $key_ra = $this->tabla.'_ruta_absoluta';
        $key_id = $this->tabla.'_id';

        $keys = array($key_rr,$key_ra,$key_id);
        foreach($documentos as $documento){
            if(!is_array($documento)){
                return $this->error->error('Error documentos[] debe ser array', $documentos);
            }
            $valida = $this->validacion->valida_existencia_keys($documento, $keys);
            if(errores::$error){
                return $this->error->error('Error al validar documento', $valida);
            }


            $carga = $this->recarga_doc($documento[$key_rr], $documento[$key_ra], $documento[$key_id]);
            if(errores::$error){
                return $this->error->error('Error al cargar documento', $carga);
            }
        }
        return $documentos;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $parent_id
     * @param string $tabla_parent
     * @return array
     */
    public function recarga_docs_full(int $parent_id, string $tabla_parent): array
    {
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        if($parent_id<=0){
            return $this->error->error('Error $parent_id deb ser mayor a 0', $parent_id);
        }
        $tabla_paren = trim($tabla_parent);
        if($tabla_paren === ''){
            return $this->error->error('Error $tabla_paren viene vacia', $tabla_paren);
        }

        $documentos = $this->elimina_docs_dbx($parent_id, $tabla_parent);
        if(errores::$error){
            return $this->error->error('Error al obtener documentos '.$tabla_parent.' Id: '.$parent_id, $documentos);
        }

        $carga = $this->recarga_docs($documentos);
        if(errores::$error){
            return $this->error->error('Error al cargar documentos '.$tabla_parent.' Id: '.$parent_id, $carga);
        }

        $upd = $this->upd_parent($tabla_parent, $parent_id);
        if(errores::$error){
            return $this->error->error('Error al actualizar recarga de docs '.$tabla_parent.' Id: '.$parent_id, $upd);
        }

        return $documentos;
    }

    /**
     * PARAMETROS
     * @param string $name_file Nombre del documento con extension
     * @return string
     */
    private function ruta_doc_min(string $name_file): string
    {
        return $this->folder_doc_abs.$name_file;
    }

    /**
     * @param array $documento
     * @return array|string
     */
    public function ruta_doc_min_abs(array $documento): array|string
    {
        $name_file = $this->name_file_min($documento[$this->tabla.'_id'], $documento['extension_ext']);
        if(errores::$error){
            return $this->error->error('Error al obtener $name_file', $name_file);
        }

        $ruta_doc = $this->ruta_doc_min($name_file);
        if(errores::$error){
            return $this->error->error('Error al obtener $name_file', $ruta_doc);
        }
        return $ruta_doc;
    }

    /**
     *
     * @param array $r_doc
     * @return array|string
     */
    public function tipo_documento_descripcion(array $r_doc): array|string
    {
        $data = $this->data_doc($r_doc['registros'][0],'tipo_documento','descripcion');

        if(errores::$error){
            return $this->error->error('Error al generar dato',$data);
        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $documento_id
     * @param string $key_parent_id
     * @return array|string
     */
    public function sube_drive(int $documento_id, string $key_parent_id): array|string
    {

        $data_file = $this->carga_doc_drive($documento_id,$key_parent_id);//model
        if(errores::$error){
            return $this->error->error('Error al subir documento', $data_file);
        }

        $upd = $this->upd_doc($data_file->drive_ruta_id, $documento_id);//model
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }

        return $upd;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function rename_doc(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $data_base = $this->_mddbx->data_para_rename($id);
        if(errores::$error){
            return $this->error->error('Error al obtener info base', $data_base);
        }

        $crea = $this->_mddbx->transacciones_rename($data_base->rutas_transaccion, $data_base->rutas, $id);
        if(errores::$error){
            return $this->error->error('Error al crear', $crea);
        }

        return $crea;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $drive_ruta_id
     * @param int $documento_id
     * @return array
     */
    public function upd_doc(int $drive_ruta_id, int $documento_id): array
    {
        $documento_prospecto_upd = $this->doc_drive_upd($drive_ruta_id);//model
        if(errores::$error){
            return $this->error->error('Error al generar doc upd', $documento_prospecto_upd);
        }

        $upd = $this->modifica_bd($documento_prospecto_upd, $documento_id);
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }
        return $upd;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function upd_doc_dbx(int $id): array
    {

        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $doc_upd = $this->_mddbx->data_dbx();
        if(errores::$error){
            return $this->error->error('Error al asignar datos para upd', $doc_upd);
        }
        $doc = parent::modifica_bd($doc_upd, $id);
        if(errores::$error){
            return $this->error->error('Error actualizar doc', $doc);
        }
        return $doc;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function upd_doc_full(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $doc_upd = $this->_mddbx->data_doc_full();
        if(errores::$error){
            return $this->error->error('Error al obtener datos', $doc_upd);
        }

        $upd = parent::modifica_bd($doc_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar doc', $upd);
        }
        return $upd;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @return array
     */
    private function upd_eliminacion_local(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $data_upd['eliminado_local'] = 'activo';
        $data_upd['fecha_eliminado_local'] = date('Y-m-d');
        $upd = parent::modifica_bd($data_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar registro', $upd);
        }
        return $upd;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function upd_error(int $id): array
    {
        errores::$error = false;
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $doc_upd['error_dbx'] = 'activo';
        $upd = parent::modifica_bd($doc_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }
        return $upd;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function upd_finalizado(int $id): array
    {
        if($id<=0){
            return $this->error->error('Error el id debe sr mayor a 0', $id);
        }
        $data_upd['finalizado'] = 'activo';
        $upd = parent::modifica_bd($data_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al modificar doc', $upd);
        }
        return $upd;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function upd_init(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $data_upd['dropbox'] = 'inactivo';
        $data_upd['en_full'] = 'inactivo';
        $data_upd['error_dbx'] = 'inactivo';

        $upd = parent::modifica_bd($data_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }
        return $upd;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id Identificador del documento
     * @return array
     */
    public function upd_init_cambiado(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $data_upd['cambiado'] = 'inactivo';
        $data_upd['en_full'] = 'inactivo';
        $data_upd['error_dbx'] = 'inactivo';
        $data_upd['dropbox'] = 'inactivo';

        $upd = parent::modifica_bd($data_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }
        return $upd;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @return array
     */
    public function upd_init_cambiado_si(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $data_upd['cambiado'] = 'activo';
        $data_upd['en_full'] = 'inactivo';
        $data_upd['error_dbx'] = 'inactivo';
        $data_upd['dropbox'] = 'inactivo';

        $upd = parent::modifica_bd($data_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar documento', $upd);
        }
        return $upd;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $tabla_parent
     * @param int $parent_id
     * @return array
     */
    public function upd_parent(string $tabla_parent, int $parent_id): array
    {
        $tabla_parent = trim($tabla_parent);
        if($tabla_parent === ''){
            return $this->error->error('Error $tabla_parent esta vacia', $tabla_parent);
        }
        if($parent_id<=0){
            return $this->error->error('Error $parent_id es menor a 1', $parent_id);
        }

        $data_upd['ultima_recarga'] = date('Y-m-d');
        $data_upd['recargado_dbx'] = 'activo';

        if($tabla_parent === 'prospecto'){
            $model = new prospecto($this->link);
            $upd = $model->modifica_bd(registro: $data_upd, id:$parent_id);
            if(errores::$error){
                return $this->error->error('Error al actualizar recarga de docs', $upd);
            }
        }
        if($tabla_parent === 'prospecto_ubicacion'){
            $model = new prospecto_ubicacion($this->link);
            $upd = $model->modifica_bd(registro: $data_upd, id:$parent_id, upd_de_ubicacion: true);
            if(errores::$error){
                return $this->error->error('Error al actualizar recarga de docs', $upd);
            }
        }
        $upd['id'] = $parent_id;
        $upd['tabla'] = $tabla_parent;

        return $upd;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas_transaccion Rutas que debería tener el documento ruta_relativa, ruta_relativa_new ruta_absoluta, ruta
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function upd_rename(stdClass $rutas_transaccion, int $id): array
    {

        $valida = $this->validacion->valida_rename($rutas_transaccion, $id, $this);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $upd['ruta'] = $rutas_transaccion->ruta;
        $upd['ruta_relativa'] = $rutas_transaccion->ruta_relativa_new;
        $upd['ruta_absoluta'] = $rutas_transaccion->ruta_absoluta;
        $upd['rename_doc'] = 'activo';

        $upd_bd = parent::modifica_bd($upd, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar', $upd_bd);
        }
        return $upd_bd;
    }

    /**
     *
     * @param array $r_doc
     * @param string $tabla
     * @param bool $comprimido
     * @return array|string
     */
    public function url(array $r_doc, string $tabla, bool $comprimido = false): array|string
    {
        $key_id_doc_parent = 'prospecto_id';
        if($tabla === 'documento_prospecto_ubicacion'){
            $key_id_doc_parent = 'prospecto_ubicacion_id';
        }

        $data = $this->data_doc($r_doc['registros'][0],$tabla,'ruta');
        if(errores::$error){
            return $this->error->error('Error al generar dato',$data);
        }
        $original = $data;

        if($comprimido){
            $data_comprimido = $this->data_comprimido($tabla, $r_doc['registros'][0], $key_id_doc_parent, $original);
            if(errores::$error){
                return $this->error->error('Error al generar dato comprimido',$data_comprimido);
            }
        }

        return $data;
    }

    /**
     * ALFABAETICO
     */


    /**
     * PHPUNIT
     * @param string $tabla
     * @param array $tipo_documento
     * @param string $key_id_doc_parent
     * @param string $original
     * @return string|array
     */
    public function data_comprimido(string $tabla, array $tipo_documento, string $key_id_doc_parent, string $original): string|array
    {
        $keys = array('tipo_documento_id');
        $valida = $this->validacion->valida_ids($tipo_documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar tipo_documento',$valida);
        }
        $key_id_doc_parent = trim($key_id_doc_parent);
        if($key_id_doc_parent === ''){
            return $this->error->error('Error $key_id_doc_parent esta vacio',$key_id_doc_parent);
        }

        $keys = array($key_id_doc_parent);
        $valida = $this->validacion->valida_ids($tipo_documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar tipo_documento',$valida);
        }

        $keys = array('extension_ext');
        $valida = $this->validacion->valida_existencia_keys($tipo_documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar tipo_documento',$valida);
        }

        $data = 'archivos/'.$tabla.'/min_'.$tipo_documento['tipo_documento_id'];
        $data.='.'.$tipo_documento[$key_id_doc_parent];
        $data.='.'.$tipo_documento['extension_ext'];

        if(!file_exists($data)){
            $data = $original;
        }
        return $data;
    }


    /**
     * PRUEBAS FINALIZADAS
     * @param array $data
     * @return string|array
     */
    public function extension_doc(array $data): string|array
    {
        $keys = array('name');
        $valida = $this->validacion->valida_existencia_keys($data, $keys);
        if(errores::$error){
            return $this->error->error("Error al validar data", $valida);
        }
        $info = new SplFileInfo($data['name']);
        $extension = $info->getExtension();
        if(trim($extension) === ''){
            return $this->error->error("Error la extension viene vacia", $extension);
        }
        return strtolower($extension);
    }

    public function genera_name_doc(string $name_temp_sie, array $documento, string $contenido): array|string
    {
        $name_temp = $this->name_temp($name_temp_sie, $documento);
        if(errores::$error){
            return $this->error->error('Error al generar name', $name_temp);
        }

        $name_doc = $this->name_doc_temp_absoluto($name_temp);
        if(errores::$error){
            return $this->error->error('Error al generar $name_doc', $name_doc);
        }

        $guarda_name_doc = $this->guarda_doc_fisico($name_doc,  $contenido);
        if(errores::$error){
            return $this->error->error('Error al guardar documento', $guarda_name_doc);
        }
        return $name_doc;
    }

    /**
     * @param int $documento_id Identificador del documento
     * @return array|string
     */
    public function comprime_documento_calidad(int $documento_id): array|string
    {
        $documento = $this->registro($documento_id);
        if(errores::$error){
            return $this->error->error('Error al obtener $documento', $documento);
        }

        $name_doc_min = $this->ruta_doc_min_abs($documento);
        if(errores::$error){
            return $this->error->error('Error al obtener ruta', $name_doc_min);
        }
        if(!file_exists($name_doc_min)){
            $name_doc_min = $this->crea_doc_min($documento,$this->tabla);
            if(errores::$error){
                return $this->error->error('Error al generar $name_doc_min', $name_doc_min);
            }
        }

        $data_upd['redimencionado'] = 'activo';
        $upd = parent::modifica_bd($data_upd, $documento_id);
        if(errores::$error){
            return $this->error->error('Error al actualizar doc', $upd);
        }

        return $name_doc_min;
    }

    public function crea_doc_min(array $documento, string $parent): array|string
    {

        $descarga = $this->download($documento[$this->tabla.'_id'], false, $parent);
        if(errores::$error){
            return $this->error->error('Error al descargar $documento', $descarga);
        }

        $name_doc_min = $this->name_doc_min($documento);
        if(errores::$error){
            return $this->error->error('Error al generar $name_doc_min', $name_doc_min);
        }

        $name_doc_new = $this->ejecuta_compresion( $documento, $descarga->contenido);
        if(errores::$error){
            return $this->error->error('Error al generar $name_doc_new', $name_doc_new);
        }
        copy($name_doc_new,$name_doc_min);
        if(!file_exists($name_doc_min)){
            return $this->error->error('Error al no se guardo el $name_doc_min', $name_doc_min);
        }
        chmod($name_doc_min, 0777);
        unlink($name_doc_new);
        return $name_doc_min;
    }

    public function ejecuta_compresion(array $documento, string $contenido): array|string
    {
        $name_temp_sie = $this->name_temp_sin_ext();
        if(errores::$error){
            return $this->error->error('Error al generar name', $name_temp_sie);
        }

        $name_doc = $this->genera_name_doc($name_temp_sie, $documento, $contenido);
        if(errores::$error){
            return $this->error->error('Error al generar $name_doc', $name_doc);
        }

        $name_doc_new = $this->doc_comprimido($name_temp_sie, $documento, $name_doc);
        if(errores::$error){
            return $this->error->error('Error al generar $name_doc_new', $name_doc_new);
        }
        return $name_doc_new;
    }

    public function doc_comprimido(string $name_temp_sie, array $documento, string $name_doc): array|string
    {
        $name_doc_new = $this->genera_name_doc_new($name_temp_sie, $documento, $name_doc);
        if(errores::$error){
            return $this->error->error('Error al generar $name_doc_new', $name_doc_new);
        }

        $name_doc_new = $this->comprime_name_doc($name_doc_new, $name_doc);
        if(errores::$error){
            return $this->error->error('Error al comprimir $documento', $name_doc_new);
        }
        return $name_doc_new;
    }

    public function comprime_name_doc(string $name_doc_new, string $name_doc): array|string
    {
        $data = compresor::descarga_pdf_comprimido($name_doc_new, '',false,true,$name_doc);
        if(errores::$error){
            return $this->error->error('Error al comprimir $documento', $data);
        }
        unlink($name_doc_new);

        (new funciones())->sh_exec("ghostscript -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH  -sOutputFile=$name_doc_new $name_doc");
        chmod($name_doc_new, 0777);

        unlink($name_doc);
        return $name_doc_new;
    }

    public function genera_name_doc_new(string $name_temp_sie, array $documento, string $name_doc): array|string
    {
        $name_temp_new = $this->name_temp_new($name_temp_sie, $documento);
        if(errores::$error){
            return $this->error->error('Error al generar $name_temp_new', $name_temp_new);
        }
        $name_doc_new = $this->name_doc_new_absoluto($name_temp_new);
        if(errores::$error){
            return $this->error->error('Error al generar $name_doc_new', $name_doc_new);
        }
        (new funciones())->sh_exec("ghostscript -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH  -sOutputFile=$name_doc_new $name_doc");
        if(!file_exists($name_doc_new)){
            return $this->error->error('Error no se genero el documento', $name_doc_new);
        }
        chmod($name_doc_new, 0777);

        unlink($name_doc);

        return $name_doc_new;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $ruta_doc
     * @param array $data
     * @param string $ruta_min Ruta fisica donde está guardado el documento en el server redimencionado
     * @return array|stdClass
     */
    public function guarda_doctos_completos(string $ruta_doc,array $data, string $ruta_min): array|stdClass
    {
        $keys = array('tmp_name');
        $valida = $this->validacion->valida_existencia_keys($data, $keys);
        if(errores::$error){
            return $this->error->error('Error validar data', $valida);
        }

        if($ruta_doc === ''){
            return $this->error->error('Error $ruta_doc esta vacia', $ruta_doc);
        }
        $ruta_doc = strtolower($ruta_doc);
        $ruta_min = strtolower($ruta_min);


        if(!file_exists($data['tmp_name'])){
            return $this->error->error('Error no existe archivo', $data);
        }

        $guarda = (new files())->guarda_archivo_fisico($ruta_doc,file_get_contents($data['tmp_name']));
        if(errores::$error){
            return $this->error->error('Error al guardar archivo', $guarda);
        }

        $rutas = $this->_mdr->ajusta_rutas_doc($ruta_doc, $ruta_min);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }
        return $rutas;
    }

    /**
     *
     * PRUEBAS FINALIZADAS
     * @param array $data
     * @param string $ruta
     * @param int $registro_id
     * @return array
     */
    public function guarda_documento(array $data, string $ruta, int $registro_id):array{


        $valida = $this->validacion->valida_data_doc($data, $ruta, $registro_id);
        if(errores::$error){
            return $this->error->error('Error validar data', $valida);
        }

        $extension = $this->extension_doc($data);
        if(errores::$error){
            return $this->error->error('Error al obtener extension', $extension);
        }
        if(trim($extension) === ''){
            return $this->error->error('Error la extension no puede venir vacia', $extension);
        }

        $name = $this->_mdr->name_upload($registro_id);
        if(errores::$error){
            return $this->error->error('Error al generar name', $name);
        }

        $data_rutas =  $this->_mdr->data_rutas($ruta, $name, $extension);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $data_rutas);
        }

        $rutas = $this->guarda_doctos_completos($data_rutas->ruta_doc, $data, $data_rutas->ruta_min);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }

        return array('ruta_absoluta'=>$data_rutas->ruta_doc,'ruta_relativa'=>$rutas->ruta_relativa,'ruta'=>$rutas->ruta_html);
    }

    public function guarda_doc_fisico(string $destino, string $contenido): array|string
    {
        file_put_contents($destino, $contenido);
        if(!file_exists($destino)){
            return $this->error->error('Error al no se guardo el documento', $destino);
        }
        chmod($destino, 0777);
        return $destino;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param int $id $id Identificador del documento
     * @return array|stdClass
     */
    public function inicializa_recarga(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $init = $this->_mddbx->init_carga_dbx($id);
        if(errores::$error){
            return $this->error->error("Error al inicializar doc", $init);
        }
        if($init->existe){
            $del = (new dropbox())->elimina($init->ruta);
            if(errores::$error){
                return $this->error->error('Error al eliminar destino', $del);
            }
        }
        return $init;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @return bool|array
     */
    private function init_dbx(int $id): bool|array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->tabla, $this->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $con_db = $this->_mddbx->con_dropbox($id);
        if(errores::$error){
            return $this->error->error('Erro al verificas si esta cargado', $con_db);
        }
        if(!$con_db){
            $verifica = $this->_mddbx->verifica_dropbox($id);
            if(errores::$error){
                return $this->error->error('Erro al verificar', $verifica);
            }
        }
        $en_full = $this->_mddbx->con_full($id);
        if(errores::$error){
            return $this->error->error('Erro al validar en full', $en_full);
        }
        if(!$en_full){
            $envia = $this->_mddbx->envia_a_full($id);
            if(errores::$error){
                return $this->error->error('Erro al enviar a full', $envia);
            }
        }
        return true;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $tipo_documento_id
     * @param int $id
     * @param array $guarda
     * @param string $key_id
     * @return array
     */
    public function inserta_documento(int $tipo_documento_id, int $id, array $guarda, string $key_id):array{
        if($tipo_documento_id<=0){
            return $this->error->error('Error tipo_documento_id debe ser mayor a 0', $tipo_documento_id);
        }
        $keys = array('ruta','ruta_relativa','ruta_absoluta');
        $valida = $this->validacion->valida_existencia_keys($guarda, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar guarda', $valida);
        }
        if($id<=0){
            return $this->error->error('Error $id debe ser mayor a 0', $id);
        }
        $key_id = trim($key_id);
        if($key_id===''){
            return $this->error->error('Error $key_id no puede venir vacio', $key_id);
        }
        $keys_id_validos = array('prospecto_ubicacion_id','prospecto_id');

        if(!in_array($key_id, $keys_id_validos)){
            return $this->error->error('Error key id invalido '.$key_id.' Keys validos en data', $keys_id_validos);
        }


        $data = $this->asigna_data_documento($tipo_documento_id,$id,$guarda,$key_id);
        if(errores::$error){
            return $this->error->error('Error al maquetar datos', $data);
        }

        $valida = (new val_extension())->valida_data_alta_documento($this->registro, $this->link,$this->tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener extension',$valida);
        }

        $r_alta_doc = $this->alta_bd();
        if(errores::$error){
            return $this->error->error('Error al guardar registro', $r_alta_doc);
        }

        return $r_alta_doc;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $data
     * @param string $ruta
     * @param int $tipo_documento_id
     * @param int $registro_id
     * @param string $key_id
     * @return array
     */
    public function inserta_documento_ctl(array $data, string $ruta,  int $tipo_documento_id,
                                          int $registro_id, string $key_id):array{

        $valida = $this->validacion->valida_carga_doc($key_id, $registro_id, $tipo_documento_id, $data, $ruta, $this);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $r_doc = $this->obten_documento($registro_id,$tipo_documento_id,$key_id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $r_doc);
        }

        if((int)$r_doc['n_registros']===1) {
            $key_doc_id = $this->tabla.'_id';
            $del = $this->elimina_bd($r_doc['registros'][0][$key_doc_id]);
            if(errores::$error){
                return $this->error->error('Error al eliminar registro', $del);
            }
        }

        $base_doc = $this->base_carga_doc($data, $ruta, $registro_id);
        if(errores::$error){
            return $this->error->error('Error al generar base de documentos', $base_doc);
        }


        $r_alta_doc = $this->inserta_documento($tipo_documento_id, $registro_id, $base_doc->guarda, $key_id);
        if (errores::$error) {
            return $this->error->error('Error al guardar registro', $r_alta_doc);
        }

        return $r_alta_doc;
    }

    /**
     *
     * @param int $registro_id
     * @param string $key_id
     * @return array
     */
    public function inserta_documentos( int $registro_id, string $key_id):array{

        $data_file_maquetado = $this->data_files();
        if(errores::$error) {
            return $this->error->error('Error al maquetar documento', $data_file_maquetado);
        }

        $ruta = $this->_mdr->ruta_documentos('archivos/'.$this->tabla);
        if(errores::$error){
            return $this->error->error('Error al generar ruta', $ruta);
        }

        $carga = $this->carga_documentos($data_file_maquetado,$ruta,$registro_id,$key_id);
        if(errores::$error){
            return $this->error->error('Error al cargar documentos', $carga);
        }

        return $carga;
    }

    /**
     * PHPUNIT
     * @return stdClass|array
     */
    private function keys_docs(): stdClass|array
    {
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla esta vacia', $this->tabla);
        }
        $key_el = $this->tabla.'_eliminado_local';
        $key_rd = $this->tabla.'_rename_doc';
        $key_f = $this->tabla.'_finalizado';

        $data = new stdClass();
        $data->key_el = $key_el;
        $data->key_rd = $key_rd;
        $data->key_f = $key_f;

        return $data;
    }

    public function name_doc_temp_absoluto(string $name_temp): string
    {
        return RUTA_TEMP.$name_temp;
    }

    public function name_doc_new_absoluto(string $name_temp_new): string
    {
        return RUTA_TEMP.$name_temp_new;
    }

    public function name_doc_min(array $documento): string
    {
        $key_id = $this->tabla.'_id';
        $id = $documento[$key_id];

        return RUTA_ARCHIVOS.$this->tabla.'/min_'.$id.'.'.$documento['extension_ext'];
    }

    public function name_temp(string $name_temp_sie, array $documento): string
    {
        return $name_temp_sie.'.'.$documento['extension_ext'];
    }

    public function name_temp_new(string $name_temp_sie, array $documento): string
    {
        return  $name_temp_sie.'_.'.$documento['extension_ext'];
    }








}
