<?php
namespace orm;

use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use models\prospecto;
use models\prospecto_ubicacion;
use stdClass;


use validacion\val_modelo_documentos;

class _model_documentos_rutas {
    public base_modelos $validacion;
    public modelo_documentos $modelo;
    public errores $error;
    public string $tabla;
    public function __construct(modelo_documentos $modelo)
    {
        $this->validacion = new val_modelo_documentos();
        $this->modelo = $modelo;
        $this->error = new errores();
        $this->tabla = $modelo->tabla;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * @param string $ruta_doc Ruta fisica donde está guardado el documento en el server
     * @param string $ruta_min Ruta fisica donde está guardado el documento en el server redimencionado
     * @return array|stdClass
     */
    public function ajusta_rutas_doc(string $ruta_doc, string $ruta_min): array|stdClass
    {
        $ruta_doc = trim($ruta_doc);
        if($ruta_doc===''){
            return $this->error->error('Error ruta_doc no puede venir vacia', $ruta_doc);
        }
        $rutas = $this->rutas_doc($ruta_doc);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }
        if(!file_exists($ruta_doc)){
            return $this->error->error('Error no existe la ruta', $ruta_doc);
        }
        chmod($ruta_doc, 0777);

        if(file_exists($ruta_min)){
            unlink($ruta_min);
        }
        return $rutas;
    }

    /**
     * PHPUNIT
     * @param array $documento Registro de tipo modelo->registro de un documento
     * @return array|stdClass
     */
    public function data_base_rename(array $documento): array|stdClass
    {
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }
        $key_id = $this->tabla.'_id';

        $keys = array($key_id);
        $valida = $this->validacion->valida_ids($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento', $valida);
        }
        $keys = array($this->tabla.'_ruta_absoluta');
        $valida = $this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento', $valida);
        }

        $new_name_sin_ext = $this->name_upload($documento[$key_id]);
        if(errores::$error){
            return $this->error->error('Error al obtener name', $new_name_sin_ext);
        }

        $ruta_actual = $documento[$this->tabla.'_ruta_absoluta'];

        $ruta_actual = str_replace(PATH_BASE, '', $ruta_actual);
        $ruta_actual = PATH_BASE.$ruta_actual;

        if(!file_exists($ruta_actual)){
            return $this->error->error('Error no existe la ruta', $ruta_actual);
        }

        $data = new stdClass();
        $data->new_name_sin_ext = $new_name_sin_ext;
        $data->ruta_actual = $ruta_actual;

        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * Genera un arreglo con la informacion para la integracion de du doc para zip
     * @param array $documento arreglo en forma de modelo->registro con informacion del documento
     * @param bool $name_file Si existe name file asi se guardara el doc si no no genera nada en name
     * @return array
     */
    public function data_file_para_zip(array $documento, bool $name_file = true): array
    {
        $key_parent_id = '';
        $key_base = '';
        if($this->tabla === 'documento_prospecto'){
            $key_parent_id = 'prospecto_id';
            $key_base = 'cliente_id';
        }
        if($this->tabla === 'documento_prospecto_ubicacion'){
            $key_parent_id = 'prospecto_ubicacion_id';
            $key_base = 'ubicacion_id';
        }

        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla no puede venir vacio',$this->tabla);
        }

        $valida = $this->validacion->valida_data_file($documento, $key_parent_id);
        if(errores::$error){
            return $this->error->error('Error al validar entrada de datos',$valida);
        }

        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return  $this->error->error( 'Error $key_parent_id esta vacio',  $key_parent_id);
        }
        $keys = array('tipo_documento_codigo',$key_parent_id);
        $valida = $this->validacion->valida_existencia_keys($documento,$keys);
        if(errores::$error){
            return  $this->error->error( 'Error al validar documento',  $valida);
        }


        $origen = $this->ruta_origen_documento($documento, $key_parent_id);
        if(errores::$error){
            return $this->error->error('Error al obtener origen',$origen);
        }
        $name_file_sin_extension = 'no_aplica';
        if($name_file) {
            $name_file_sin_extension = $this->name_file_sin_extension($documento, $key_base);
            if (errores::$error) {
                return $this->error->error('Error al obtener name_file', $name_file_sin_extension);
            }
        }

        return array('name_file_sin_extension'=>$name_file_sin_extension,'origen'=>$origen);
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $parent_name Nombre del catalogo padre para documento_prospecto es prospecto
     * @param array $documento arreglo en forma de modelo->registro de un documento
     * @param array $data
     * @return array
     */
    public function data_name_doc_parent(string $parent_name, array $documento, array $data): array
    {
        $parent_name = trim($parent_name);
        if($parent_name === ''){
            return $this->error->error('Error paren_name esta vacio', $parent_name);
        }
        if($parent_name === 'cliente'){
            $keys = array('cliente_codigo');
            $valida = $this->validacion->valida_existencia_keys($data, $keys);
            if(errores::$error){
                return $this->error->error('Error al validar $data', $valida);
            }
            $keys = array('tipo_documento_descripcion');
            $valida = $this->validacion->valida_existencia_keys($documento, $keys);
            if(errores::$error){
                return $this->error->error('Error al validar $documento', $valida);
            }

            $data['cliente_id'] = $data['cliente_codigo'];
            $data['tipo_documento_descripcion'] = $documento['tipo_documento_descripcion'];
        }
        else{
            $data = $documento;
        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * Obtiene la informacion del catalogo perteneciente del documento puede ser prospecto o prospecto_ubicacion
     * @param string $parent_name Nombre del catalog padre para documento_prospecto es prospecto
     * @param array $documento arreglo en forma de modelo->registro de un documento
     * @param int $id Identificador del documento
     * @return array
     */
    public function data_para_name(string $parent_name, array $documento, int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $parent_name = trim($parent_name);
        if($parent_name === ''){
            return $this->error->error('Error parent esta vacio ', $parent_name);
        }

        $data = $this->data_parent_por_doc_id($id, $parent_name);
        if(errores::$error){
            return $this->error->error('Error al obtener $datos', $data);
        }

        $data = $this->data_name_doc_parent($parent_name, $documento, $data);
        if(errores::$error){
            return $this->error->error('Error al obtener $datos', $data);
        }
        return $data;
    }

    /**
     *
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @param array $documento
     * @return array
     */
    public function data_parent_doc(string $parent, array $documento): array
    {
        $parent = trim($parent);
        if($parent === ''){
            return $this->error->error('Error parent esta vacio ', $parent);
        }
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla esta vacio ', $this->tabla);
        }

        $data = array();

        if($this->tabla === 'documento_prospecto') {
            $data = $this->data_parent_comercial($parent, $documento);
            if (errores::$error) {
                return $this->error->error('Error al obtener datos', $data);
            }
        }
        if($this->tabla === 'documento_prospecto_ubicacion') {
            $data = $this->data_parent_ubicacion($parent, $documento);
            if (errores::$error) {
                return $this->error->error('Error al obtener datos', $data);
            }
        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $parent Nombre del catalog padre para documento_prospecto es prospecto
     * @param array $documento Registro de documento en forma documento[tabla_campo]
     * @return array
     */
    public function data_parent_comercial(string $parent, array $documento): array
    {
        $parent = trim($parent);
        if($parent === ''){
            return $this->error->error('Error parent esta vacio ', $parent);
        }
        $keys = array('prospecto_id');
        $valida = $this->validacion->valida_ids($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento ', $valida);
        }
        $data = array();
        if($parent === 'prospecto'){

            $data = (new prospecto($this->modelo->link))->registro($documento['prospecto_id']);
            if(errores::$error){
                return $this->error->error('Error al obtener datos ', $data);
            }
        }
        if($parent === 'cliente'){
            $data = (new prospecto($this->modelo->link))->obten_data_cliente($documento['prospecto_id']);
            if(errores::$error){
                return $this->error->error('Error al obtener datos ', $data);
            }
        }
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @return array
     */
    public function data_parent_por_doc_id(int $id, string $parent): array
    {

        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $parent = trim($parent);
        if($parent === ''){
            return $this->error->error('Error parent esta vacio ', $parent);
        }

        $documento = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }

        $data = $this->data_parent_doc($parent, $documento);
        if (errores::$error) {
            return $this->error->error('Error al obtener datos', $data);
        }

        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $ruta
     * @param string $name
     * @param string $extension
     * @return stdClass|array
     */
    public function data_rutas(string $ruta, string $name, string $extension): stdClass|array
    {
        $ruta = trim($ruta);
        if($ruta === ''){
            return $this->error->error("Error ruta no puede venir vacia", $ruta);
        }
        if(trim($extension) === ''){
            return $this->error->error('Error la extension no puede venir vacia', $extension);
        }
        if(trim($name) === ''){
            return $this->error->error('Error la $name no puede venir vacia', $name);
        }

        $extension = strtolower($extension);
        $ruta_doc = $ruta.'/'.$name.'.'.$extension;
        $ruta_min = $ruta.'min_'.$name.'.'.$extension;

        $data = new stdClass();
        $data->ruta_doc = $ruta_doc;
        $data->ruta_min = $ruta_min;

        return $data;
    }

    /**
     *
     * @param string $parent Nombre del catalog padre para documento_prospecto es prospecto
     * @param array $documento
     * @return array
     */
    public function data_parent_ubicacion(string $parent, array $documento): array
    {
        $parent = trim($parent);
        if($parent === ''){
            return $this->error->error('Error $parent esta vacio ', $parent);
        }
        $keys = array('prospecto_ubicacion_id');
        $valida = $this->validacion->valida_ids($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar documento ', $valida);
        }

        $data = array();
        if($parent === 'prospecto_ubicacion'){

            $data = (new prospecto_ubicacion($this->modelo->link))->registro($documento['prospecto_ubicacion_id']);
            if(errores::$error){
                return $this->error->error('Error al obtener datos ', $data);
            }
        }
        if($parent === 'ubicacion'){
            $data = (new prospecto_ubicacion($this->modelo->link))->ubicacion($documento['prospecto_ubicacion_id']);
            if(errores::$error){
                return $this->error->error('Error al obtener datos ', $data);
            }
        }

        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $documento
     * @return string|array
     */
    public function name_doc(array $documento): string|array
    {
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }

        $key_id = $this->tabla.'_id';
        $keys = array($key_id,'extension_ext');
        $valida = $this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }
        return  $documento[$key_id].'.'.$documento['extension_ext'];
    }

    /**
     * PHPUNIT
     * @param array $documento
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @return string|array
     */
    public function name_doc_download(array $documento, string $parent): string|array
    {
        $valida = $this->validacion->valida_data_name_descarga($parent, $documento);
        if(errores::$error){
            return $this->error->error('Error al validar datos del nombre del doc', $valida);
        }
        return $documento[$parent.'_id'].'.'.$documento['tipo_documento_descripcion'];
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $parent_name Nombre del catalogo padre para documento_prospecto es prospecto
     * @param array $documento arreglo en forma de modelo->registro de un documento
     * @param int $id $id Identificador del documento
     * @return array|string
     */
    public function name_doc_parent(string $parent_name, array $documento, int $id): array|string
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $parent_name = trim($parent_name);
        if($parent_name === ''){
            return $this->error->error('Error parent esta vacio ', $parent_name);
        }
        $keys = array('extension_ext');
        $valida = $this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }

        $data = $this->data_para_name($parent_name, $documento, $id);
        if(errores::$error){
            return $this->error->error('Error al obtener $datos', $data);
        }

        $name = $this->name_doc_download($data, $parent_name);
        if(errores::$error){
            return $this->error->error('Error al generar name', $name);
        }
        $name.='.'.$documento['extension_ext'];
        return $name;
    }

    /**
     * @param int $id $id Identificador del documento
     * @param string $parent_name Nombre del catalogo padre para documento_prospecto es prospecto
     * @return array|string
     */
    public function name_download_parent(int $id, string $parent_name): array|string
    {
        $documento = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener $datos', $documento);
        }

        $name = $this->name_doc_parent($parent_name, $documento,$id);
        if(errores::$error){
            return $this->error->error('Error al obtener name', $name);
        }
        return $name;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * Genera en nombre del documento sin extension con base en la info de documento
     * @param array $documento es un arreglo de tipo modelo->registro de un documento
     * @param string $key_parent_id identificador del catalogo superior depende del documento ejemplo
     * para documento_prospecto es prospecto
     * @return array|string
     */
    public function name_file_sin_extension(array $documento, string $key_parent_id): array|string
    {
        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return  $this->error->error( 'Error $key_parent_id esta vacio',  $key_parent_id);
        }
        $keys = array('tipo_documento_codigo',$key_parent_id);
        $valida = $this->validacion->valida_existencia_keys($documento,$keys);
        if(errores::$error){
            return  $this->error->error( 'Error al validar documento',  $valida);
        }
        $name_file_sin_extension = $documento['tipo_documento_codigo'];
        $name_file_sin_extension.='.'.$documento[$key_parent_id];
        return $name_file_sin_extension;
    }

    /**
     * PHPUNIT
     * @return string
     */
    public function name_temp(): string
    {
        return time().mt_rand(100,999).mt_rand(100,999).mt_rand(100,999);
    }

    /**
     * PHPUNIT
     * @param int $registro_id Identificador del documento
     * @return string|array
     */
    public function name_upload(int $registro_id): string|array
    {
        if($registro_id<=0){
            return $this->error->error('Error registro_id debe ser mayor a 0', $registro_id);
        }
        return $registro_id.'_'.time().'_'.mt_rand(10,99).mt_rand(10,99).mt_rand(10,99).mt_rand(1000,9999);
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array|string
     */
    public function ruta_absoluta(int $id): array|string
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $documento = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }
        $key_rr = $this->tabla.'_ruta_absoluta';

        $ra = trim( $documento[$key_rr]);
        $ra = str_replace(PATH_BASE, '', $ra);
        $ra = PATH_BASE.$ra;

        return trim( $ra);
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @return string|array
     */
    public function ruta_destino(int $id): string|array
    {
        if($id<=0){
            return $this->error->error('Error id debe ser mayor a 0', $id);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }

        return $this->tabla.'/'.$id;
    }

    /**
     *
     * @param string $ruta_relativa
     * @return array|string
     */
    public function ruta_documentos(string $ruta_relativa):array|string{
        if($ruta_relativa === ''){
            return $this->error->error('Error ruta_relativa no puede venir vacia', $ruta_relativa);
        }
        $ruta = PATH_BASE.$ruta_relativa;
        if(!is_dir($ruta)){
            if(!mkdir($ruta, 0777, true)&& !is_dir($ruta)) {
                return $this->error->error('Error al guardar archivo', $ruta);
            }
            chmod($ruta, 0777);
        }
        return $ruta;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array|string
     */
    private function ruta_full(int $id): array|string
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $ruta_relativa = $this->ruta_relativa($id);
        if(errores::$error){
            return $this->error->error('Error al obtener ruta', $ruta_relativa);
        }

        return 'full/'.$ruta_relativa;
    }

    /**
     *
     * Genera la ruta de origen de documento
     * @param array $documento arreglo en forma de modelo->registro con informacion del documento
     * @param string $key_id Key del identificador a validar o nombre del campo completo tabla_id
     * @return string|array
     */
    public function ruta_origen_documento(array $documento, string $key_id): string|array
    {

        $valida = $this->validacion->valida_data_file($documento, $key_id);
        if(errores::$error){
            return $this->error->error('Error al validar entrada de datos',$valida);
        }
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla no puede venir vacio',$this->tabla);
        }

        $origen = 'archivos/'.$this->tabla.'/'.$documento['tipo_documento_id'].'.';
        $origen .= $documento[$key_id];
        $origen .= '.'.$documento['extension_ext'];

        return $origen;
    }

    /**
     * PHPUNIT
     * Obtiene la ruta relativa de un documento buscado por su identificador, la ruta relativa regularmente es
     * archivos/nombre_tabla/nombre_doc
     * @param int $id $id Identificador del documento
     * @return array|string
     */
    public function ruta_relativa(int $id): array|string
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $documento = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }
        $key_rr = $this->tabla.'_ruta_relativa';

        return trim( $documento[$key_rr]);
    }

    /**
     * PHPUNIT
     * @param string $name Nombre del documento carpeta
     * @return string|array
     */
    private function ruta_temp(string $name): string|array
    {
        $name = trim($name);
        if($name === ''){
            return $this->error->error('Error $name no puede venir vacio',$name);
        }
        return PATH_BASE.'archivos/temporales/'.$name;
    }

    /**
     * PHPUNIT
     * @return array|string
     */
    public function ruta_temporal_doc(): array|string
    {
        $name = $this->name_temp();
        if(errores::$error){
            return $this->error->error('Error al obtener name temp',$name);
        }
        $ruta_temp = $this->ruta_temp($name);
        if(errores::$error){
            return $this->error->error('Error al generar ruta temp',$ruta_temp);
        }

        return $ruta_temp;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array|stdClass $data->ruta_relativa
     * @return array|stdClass $data->ruta_full
     */
    public function rutas(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $ruta_relativa = $this->ruta_relativa($id);
        if(errores::$error){
            return $this->error->error('Error al obtener ruta', $ruta_relativa);
        }

        $ruta_full = $this->ruta_full($id);
        if(errores::$error){
            return $this->error->error('Error al obtener ruta full', $ruta_full);
        }

        $data = new stdClass();
        $data->ruta_relativa = $ruta_relativa;
        $data->ruta_full = $ruta_full;

        return $data;

    }

    /**
     * PHPUNIT
     * @param string $new_name_sin_ext
     * @param array $documento Registro de tipo modelo->registro de un documento
     * @return stdClass|array
     */
    public function rutas_basicas_rename(string $new_name_sin_ext, array $documento): stdClass|array
    {
        $new_name_sin_ext = trim($new_name_sin_ext);
        if($new_name_sin_ext === ''){
            return $this->error->error('Error $new_name_sin_ext esta vacio', $new_name_sin_ext);
        }
        $keys = array('extension_ext');
        $valida = $this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento', $valida);
        }
        $ruta_relativa = 'archivos/'.$this->tabla.'/';//si return
        $base = PATH_BASE.$ruta_relativa; //no return
        $url_base = URL_BASE.$ruta_relativa;//si return

        $new_name = $new_name_sin_ext.'.'.$documento['extension_ext'];//si return
        $ruta_full_new = $base.$new_name; //si return

        $data = new stdClass();
        $data->ruta_relativa = $ruta_relativa;
        $data->base = $base;
        $data->url_base = $url_base;
        $data->new_name = $new_name;
        $data->ruta_full_new = $ruta_full_new;
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $ruta_doc Ruta fisica donde está guardado el documento en el server
     * @return stdClass|array
     */
    public function rutas_doc(string $ruta_doc): stdClass|array
    {
        $ruta_doc = trim($ruta_doc);
        if($ruta_doc===''){
            return $this->error->error('Error ruta_doc no puede venir vacia', $ruta_doc);
        }
        $ruta_relativa = str_replace(PATH_BASE,'',$ruta_doc);
        $ruta_html = URL_BASE.$ruta_relativa;

        $data = new stdClass();
        $data->ruta_relativa = $ruta_relativa;
        $data->ruta_html = $ruta_html;
        return $data;
    }

    /**
     * PHPUNIT
     * @param string $url_base
     * @param string $new_name
     * @param string $ruta_relativa
     * @param string $ruta_full_new
     * @return stdClass|array
     */
    public function rutas_transaccion(
        string $url_base, string $new_name, string $ruta_relativa, string $ruta_full_new): stdClass|array
    {
        $url_base = trim($url_base);
        if($url_base === ''){
            return $this->error->error('Error $url_base no pude venir vacia', $url_base);
        }
        $new_name = trim($new_name);
        if($new_name === ''){
            return $this->error->error('Error $new_name no pude venir vacia', $new_name);
        }
        $ruta_relativa = trim($ruta_relativa);
        if($ruta_relativa === ''){
            return $this->error->error('Error $ruta_relativa no pude venir vacia', $ruta_relativa);
        }
        $ruta_full_new = trim($ruta_full_new);
        if($ruta_full_new === ''){
            return $this->error->error('Error $ruta_full_new no pude venir vacia', $ruta_full_new);
        }
        $ruta = $url_base.$new_name;
        $ruta_relativa_new = $ruta_relativa.$new_name;
        $ruta_absoluta = $ruta_full_new;

        $data = new stdClass();
        $data->ruta = $ruta;
        $data->ruta_relativa_new = $ruta_relativa_new;
        $data->ruta_absoluta = $ruta_absoluta;
        return $data;
    }

}
