<?php
namespace orm;

use base\dropbox;


use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use stdClass;


use validacion\val_modelo_documentos;

class _model_documentos_dbx {
    public base_modelos $validacion;
    public modelo_documentos $modelo;
    public errores $error;
    public string $tabla;
    public _model_documentos_rutas $_mdr;
    public function __construct(modelo_documentos $modelo)
    {
        $this->validacion = new val_modelo_documentos();
        $this->modelo = $modelo;
        $this->error = new errores();
        $this->tabla = $modelo->tabla;
        $this->_mdr = new _model_documentos_rutas($modelo);
    }

    /**
     * PHPUNIT
     * @param string $rr
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    private function actualiza_dbx(string $rr, int $id): bool|array
    {
        $ruta_relativa = trim($rr);
        if($ruta_relativa === ''){
            return $this->error->error('Error $rr viene vacia', $rr);
        }


        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $existe = $this->verifica_si_dbx($rr);
        if(errores::$error){
            $upd = $this->modelo->upd_error($id);
            if(errores::$error){
                return $this->error->error('Error al actualizar documento', $upd);
            }
            errores::$error = true;
            return $this->error->error('Error al verificar si existe doc', $existe);
        }

        if($existe){
            $doc_upd = $this->modelo->upd_doc_dbx($id);
            if(errores::$error){
                return $this->error->error('Error al asignar datos para upd', $doc_upd);
            }
        }
        return $existe;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    private function ajusta_dbx(int $id): bool|array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $rr = $this->_mdr->ruta_relativa($id);
        if(errores::$error){
            return $this->error->error('Error al obtener ruta', $rr);
        }

        $doc_upd = $this->actualiza_dbx($rr, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar doc', $doc_upd);
        }

        return $doc_upd;
    }

    /**
     * PHPUNIT
     * @param array $documento
     * @param int $id $id Identificador del documento
     * @return array
     */
    private function ajusta_finalizado(array $documento, int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $key = $this->tabla.'_finalizado';
        $keys = array($key);
        $valida = $this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento', $valida);
        }

        if($documento[$key] === 'inactivo'){
            $finaliza = $this->modelo->aplica_finalizado($id);
            if(errores::$error){
                return $this->error->error('Error al aplicar finalizacion $documento', $finaliza);
            }
        }
        return $documento;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    public function carga_doc_dbx(int $id): bool|array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $con_db = $this->con_dropbox($id);
        if(errores::$error){
            return $this->error->error("Error al validar si esta en dbx", $con_db);
        }

        if(!$con_db){
            $carga = $this->carga_documento_dbx($id);
            if(errores::$error){
                return $this->error->error("Error al asignar datos", $carga);
            }
        }
        return $con_db;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array|stdClass
     */
    private function carga_documento_dbx(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $data = $this->init_carga_dbx($id);
        if(errores::$error){
            return $this->error->error("Error al asignar datos", $data);
        }

        if(!$data->existe){
            $carga = $this->sube_dbx($id, $data->ruta);
            if(errores::$error){
                return $this->error->error("Error al cargar doc", $carga);
            }
        }
        return $data;
    }

    /**
     * PHPUNIT
     * @param bool $existe_rr
     * @param bool $existe_ruta_full
     * @param stdClass $rutas $rutas Rutas obtenidas del documento en ejecucion
     * @param array $documento Registro de tipo documento modelo->registro documento_prospecto->registro modelo[0][key][value]
     * @return array|stdClass
     */
    private function carga_docs_fin(bool $existe_rr, bool $existe_ruta_full, stdClass $rutas, array $documento): array|stdClass
    {

        $valida = $this->validacion->valida_data_fin($rutas, $documento, $this->modelo);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }

        $carga = false;
        $copia = false;
        if(!$existe_rr){
            $carga = $this->crea_doc($rutas, $documento);
            if(errores::$error){
                return $this->error->error('Error al cargar documento', $carga);
            }
        }
        if(!$existe_ruta_full){
            $copia = (new dropbox())->copia_doc($rutas->ruta_relativa, $rutas->ruta_full);
            if(errores::$error){
                return $this->error->error('Error al copiar doc', $copia);
            }
        }
        $data = new stdClass();
        $data->carga = $carga;
        $data->copia = $copia;
        return $data;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas $rutas Rutas obtenidas del documento en ejecucion
     * @param array $documento Registro de tipo documento modelo->registro documento_prospecto->registro modelo[0][key][value]
     * @return array|stdClass
     */
    private function carga_documentos_finalizados(stdClass $rutas, array $documento): array|stdClass
    {
        $valida = $this->validacion->valida_data_fin($rutas, $documento, $this->modelo);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }

        $existe_rr = (new dropbox())->existe_path_dbx($rutas->ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al validar si existe ruta', $existe_rr);
        }

        $existe_ruta_full = (new dropbox())->existe_path_dbx($rutas->ruta_full);
        if(errores::$error){
            return $this->error->error('Error al validar si existe ruta', $existe_ruta_full);
        }

        $carga = $this->carga_docs_fin($existe_rr, $existe_ruta_full, $rutas, $documento);
        if(errores::$error){
            return $this->error->error('Error al cargar documento', $carga);
        }
        return $carga;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    public function con_dropbox(int $id): bool|array
    {
        if($id<=0){
            return $this->error->error('Error id debe ser mayor a 0', $id);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }

        $doc = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $doc);
        }
        $con_db = true;
        $key = $this->tabla.'_dropbox';
        if($doc[$key] === 'inactivo'){
            $con_db = false;
        }
        return $con_db;

    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    public function con_full(int $id): bool|array
    {
        if($id<=0){
            return $this->error->error('Error id debe ser mayor a 0', $id);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }

        $con_db = $this->revalida_con_dropbox($id);
        if(errores::$error){
            return $this->error->error('Error al verificar con dropbox', $con_db);
        }

        if(!$con_db){
            return $this->error->error('Error aun no se verifica en dropbox', $con_db);
        }

        $doc = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $doc);
        }
        $con_full = true;
        $key = $this->tabla.'_en_full';
        if($doc[$key] === 'inactivo'){
            $con_full = false;
        }
        return $con_full;

    }

    /**
     * PHPUNIT
     * @param string $ruta_full_new
     * @param string $ruta_actual
     * @return array|string
     */
    private function copia_doc(string $ruta_full_new, string $ruta_actual): array|string
    {
        $ruta_full_new = trim($ruta_full_new);
        if($ruta_full_new === ''){
            return $this->error->error('Error $ruta_full_new esta vacia', $ruta_full_new);
        }
        $ruta_actual = trim($ruta_actual);
        if($ruta_actual === ''){
            return $this->error->error('Error $ruta_actual esta vacia', $ruta_actual);
        }
        if(file_exists($ruta_full_new)){
            return $this->error->error('Error ya existe la ruta', $ruta_full_new);
        }
        if(!file_exists($ruta_actual)){
            return $this->error->error('Error no existe $ruta_actual', $ruta_actual);
        }

        copy($ruta_actual,$ruta_full_new);
        if(!file_exists($ruta_full_new)){
            return $this->error->error('Error no existe la ruta', $ruta_full_new);
        }
        if(!file_exists($ruta_actual)){
            return $this->error->error('Error la ruta actual no existe', $ruta_actual);
        }
        chmod($ruta_full_new, 0777);

        return $ruta_full_new;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    private function copia_en_full(int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $rutas = $this->_mdr->rutas($id);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }

        $copia = (new dropbox())->copia_doc($rutas->ruta_relativa, $rutas->ruta_full);
        if(errores::$error){
            return $this->error->error('Error al copiar doc', $copia);
        }

        return $copia;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas $rutas Rutas obtenidas del documento en ejecucion
     * @param array $documento
     * @return array
     */
    private function crea_doc(stdClass $rutas, array $documento): array
    {
        $valida = $this->validacion->valida_carga_docs_fin($rutas, $documento, $this->modelo);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }
        $key = $this->tabla.'_ruta_absoluta';

        $carga = (new dropbox())->crea_doc($rutas->ruta_relativa, $documento[$key]);
        if(errores::$error){
            return $this->error->error('Error al cargar documento', $carga);
        }
        return $carga;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas_transaccion Rutas que debería tener el documento ruta_relativa, ruta_relativa_new ruta_absoluta
     * @return array
     */
    private function crea_doc_completo_dbx(stdClass $rutas_transaccion): array
    {
        $obj_array = (array)$rutas_transaccion;
        $keys = array('ruta_relativa_new','ruta_absoluta');
        $valida = $this->validacion->valida_existencia_keys($obj_array, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar rutas_transaccion', $valida);
        }

        $crea = (new dropbox())->crea_doc($rutas_transaccion->ruta_relativa_new, $rutas_transaccion->ruta_absoluta);
        if(errores::$error){
            return $this->error->error('Error al crear', $crea);
        }

        $copia = (new dropbox())->copia_doc($rutas_transaccion->ruta_relativa_new, 'full/'.$rutas_transaccion->ruta_relativa_new);
        if(errores::$error){
            return $this->error->error('Error al crear', $copia);
        }

        return $crea;
    }

    /**
     * PHPUNIT
     * @return array
     */
    public function data_dbx(): array
    {
        $doc_upd['dropbox'] = 'activo';
        $doc_upd['fecha_alta_dropbox'] = date('Y-m-d');
        $doc_upd['error_dbx'] = 'inactivo';
        return $doc_upd;
    }

    /**
     * PHPUNIT
     * @return array
     */
    public function data_doc_full(): array
    {
        $doc_upd['en_full'] = 'activo';
        $doc_upd['fecha_full'] = date('Y-m-d');
        return $doc_upd;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @return array|stdClass
     */
    public function data_download(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $documento = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener info del documento', $documento);
        }
        $ruta_relativa = $this->init_download($id);
        if(errores::$error){
            return $this->error->error('Error al cargar doc', $ruta_relativa);
        }
        $data = new stdClass();
        $data->documento = $documento;
        $data->ruta_relativa = $ruta_relativa;
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @return array|stdClass
     */
    public function data_download_full(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }


        $data_documentos = $this->datas_documentos($id);
        if(errores::$error){
            return $this->error->error('Error al obtener datos', $data_documentos);
        }

        $full = $this->init_full($data_documentos->rutas->ruta_relativa, $id);
        if(errores::$error){
            return $this->error->error('Error al enviar a full', $full);
        }
        $data = new stdClass();
        $data->documento = $data_documentos->documento;
        $data->ruta_full = $data_documentos->rutas->ruta_full;
        return $data;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @return array|stdClass
     */
    private function datas_documentos(int $id): array|stdClass
    {
        if($id<=0){
            return $this->error->error('Error id debe ser mayor a 0', $id);
        }
        $documento = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener doc', $documento);
        }

        $rutas = $this->_mdr->rutas($id);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }

        $data = new stdClass();
        $data->documento = $documento;
        $data->rutas = $rutas;
        return $data;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array|stdClass
     */
    public function data_para_rename(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }


        $data_documentos = $this->datas_documentos($id);
        if(errores::$error){
            return $this->error->error('Error al obtener datos', $data_documentos);
        }

        $data_base_rename = $this->_mdr->data_base_rename($data_documentos->documento);
        if(errores::$error){
            return $this->error->error('Error al obtener satos base', $data_base_rename);
        }

        $rutas_basicas = $this->_mdr->rutas_basicas_rename($data_base_rename->new_name_sin_ext, $data_documentos->documento);
        if(errores::$error){
            return $this->error->error('Error al crear rutas basicas', $rutas_basicas);
        }

        $copia = $this->copia_doc($rutas_basicas->ruta_full_new, $data_base_rename->ruta_actual);
        if(errores::$error){
            return $this->error->error('Error al copiar', $copia);
        }

        $rutas_transaccion = $this->_mdr->rutas_transaccion($rutas_basicas->url_base, $rutas_basicas->new_name,
            $rutas_basicas->ruta_relativa, $rutas_basicas->ruta_full_new);
        if(errores::$error){
            return $this->error->error('Error al generar rutas', $rutas_transaccion);
        }

        $data = new stdClass();

        $data->rutas = $data_documentos->rutas;
        $data->rutas_transaccion = $rutas_transaccion;

        return $data;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas
     * @return bool|array
     */
    private function del_dropbox_completo(stdClass $rutas): bool|array
    {
        $rutas_arr = (array)$rutas;
        $keys = array('ruta_relativa','ruta_full');
        $valida = $this->validacion->valida_existencia_keys($rutas_arr, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $rutas', $valida);
        }

        $del = (new dropbox())->elimina_si_existe($rutas->ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al eliminar', $del);
        }
        $del = (new dropbox())->elimina_si_existe($rutas->ruta_full);
        if(errores::$error){
            return $this->error->error('Error al eliminar', $del);
        }

        return true;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @param bool $header Si es header ejecutara descarga desde navegador
     * @return array|stdClass
     */
    public function descarga_por_id(int $id, string $parent, bool $header): array|stdClass
    {
        $documento = $this->modelo->documento_validado($id, $parent);
        if(errores::$error){
            return $this->error->error('Error al obtener $documento', $documento);
        }

        $rutas = $this->_mdr->rutas($id);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }
        $name = $this->_mdr->name_doc_download($documento, $parent);
        if(errores::$error){
            return $this->error->error('Error al obtener name', $name);
        }

        $descarga = (new dropbox())->download($rutas->ruta_relativa,$header,$name);
        if(errores::$error){
            return $this->error->error('Error al descargar info', $descarga);
        }
        return $descarga;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @param bool $header Si es header ejecutara descarga desde navegador
     * @return array|stdClass
     */
    public function descarga_segura(int $id, string $parent, bool $header): array|stdClass
    {

        $documento = $this->modelo->documento_validado($id, $parent);
        if(errores::$error){
            return $this->error->error('Error al obtener $documento', $documento);
        }

        $finaliza = $this->ajusta_finalizado($documento, $id);
        if(errores::$error){
            return $this->error->error('Error al aplicar finalizacion $documento', $finaliza);
        }
        $descarga = $this->descarga_por_id($id, $parent, $header);
        if(errores::$error){
            return $this->error->error('Error al descargar $documento', $descarga);
        }
        return $descarga;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @param string $name
     * @param string $parent Nombre del catalogo padre para documento_prospecto es prospecto
     * @param bool $header Si es header ejecutara descarga desde navegador
     * @return array|stdClass
     */
    public function descarga_segura_c_name(int $id, string $name, string $parent, bool $header): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $name = trim($name);
        if($name === ''){
            return $this->error->error('Error $name no puede venir vacio', $name);
        }

        $descarga = $this->descarga_segura($id, $parent,false);
        if(errores::$error){
            return $this->error->error('Error al obtener descarga', $descarga);
        }
        if($header) {
            ob_clean();
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$name");
            header("Content-Type: ''");
            header("Content-Transfer-Encoding: binary");
            echo $descarga->contenido;
            exit;
        }
        return $descarga;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @return array|stdClass
     */
    public function descarga_temp(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $ruta_temp = $this->_mdr->ruta_temporal_doc();
        if(errores::$error){
            return $this->error->error('Error al generar ruta temp',$ruta_temp);
        }

        $doc = $this->descarga_segura_c_name($id, $ruta_temp, $this->tabla, false);
        if(errores::$error){
            return $this->error->error('Error al generar documento',$doc);
        }
        $doc->ruta_temp = $ruta_temp;
        return $doc;
    }

    /**
     * PHPUNIT
     * @param array $documento Registro de tipo documento modelo->registro documento_prospecto->registro modelo[0][key][value]
     * @param int $id $id Identificador del documento
     * @return array
     */
    private function ejecuta_finalizacion(array $documento, int $id): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $rutas = $this->_mdr->rutas($id);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }
        $carga = $this->genera_finalizado($rutas, $documento, $id);
        if(errores::$error){
            return $this->error->error('Error al cargar documento', $carga);
        }
        $documento = $this->modelo->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }
        return $documento;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function envia_a_full(int $id): array
    {
        if($id<=0){
            return $this->error->error('Error id debe ser mayor a 0', $id);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }

        $copia = $this->copia_en_full($id);
        if(errores::$error){
            return $this->error->error('Error al copiar doc', $copia);
        }

        $upd = $this->modelo->upd_doc_full($id);
        if(errores::$error){
            return $this->error->error('Error al actualizar doc', $upd);
        }
        return $upd;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @param array $documento
     * @return array|stdClass
     */
    public function finaliza_desde_parent(int $id, array $documento): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }


        $key_rd = $this->tabla.'_rename_doc';
        $key_f = $this->tabla.'_finalizado';

        $keys = array($key_rd,$key_f);
        $valida =$this->validacion->valida_existencia_keys($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $documento', $valida);
        }


        $rename = array();
        $finaliza = array();

        if($documento[$key_rd] === 'inactivo'){
            $rename = $this->modelo->rename_doc($id);
            if(errores::$error){
                return $this->error->error('Error al aplicar rename',$rename);
            }
        }
        if($documento[$key_f] === 'inactivo'){
            $finaliza = $this->modelo->aplica_finalizado($id);
            if(errores::$error){
                return $this->error->error('Error al aplicar rename',$finaliza);
            }
        }
        $data = new stdClass();
        $data->rename = $rename;
        $data->finaliza = $finaliza;
        return $data;
    }

    /**
     * PHPUNIT
     * @param bool $aplica aplica ejecuta la finalizacion del doc si no renombra y ejecuta finalizacion
     * @param array $documento Registro de tipo documento modelo->registro documento_prospecto->registro modelo[0][key][value]
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function finaliza_doc(bool $aplica, array $documento, int $id): array
    {
        if($id<=0){
            return $this->error->error('Error el id debe ser mayor a 0', $id);
        }
        if($aplica){
            $documento = $this->ejecuta_finalizacion( $documento, $id);
            if(errores::$error){
                return $this->error->error('Error al obtener documento', $documento);
            }
        }
        else{
            $documento = $this->rename_verificado_y_fin($documento, $id);
            if(errores::$error){
                return $this->error->error('Error al renombrar documento', $documento);
            }
        }
        return $documento;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas $rutas Rutas obtenidas del documento en ejecucion
     * @param array $documento Registro de tipo documento modelo->registro documento_prospecto->registro modelo[0][key][value]
     * @param int $id $id Identificador del documento
     * @return array
     */
    private function genera_finalizado(stdClass $rutas, array $documento, int $id): array
    {
        $valida = $this->validacion->valida_data_fin($rutas, $documento, $this->modelo);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }
        $carga = $this->carga_documentos_finalizados($rutas, $documento);
        if(errores::$error){
            return $this->error->error('Error al cargar documento', $carga);
        }

        $upd = $this->modelo->upd_finalizado($id);
        if(errores::$error){
            return $this->error->error('Error al modificar doc', $upd);
        }
        return $upd;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id $id Identificador del documento
     * @param array $documento
     * @return array|stdClass
     */
    public function genera_doc_temp(int $id, array $documento): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $finaliza = $this->finaliza_desde_parent($id, $documento);
        if(errores::$error){
            return $this->error->error('Error al aplicar finalizacion',$finaliza);
        }

        $doc = $this->descarga_temp($id);
        if(errores::$error){
            return $this->error->error('Error al generar documento',$doc);
        }

        file_put_contents($doc->ruta_temp, $doc->contenido);
        chmod($doc->ruta_temp, 0777);
        return $doc;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return array|stdClass
     */
    public function init_carga_dbx(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $ruta = $this->_mdr->ruta_relativa($id);
        if(errores::$error){
            return $this->error->error("Error al obtener ruta", $ruta);
        }
        $existe = (new dropbox())->existe_path_dbx($ruta);
        if(errores::$error){
            return $this->error->error("Error al validar si existe", $existe);
        }
        $data = new stdClass();
        $data->existe = $existe;
        $data->ruta = $ruta;
        return $data;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @return array|string
     */
    private function init_download(int $id): array|string
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $regenera = $this->regenera_documento($id);
        if(errores::$error){
            return $this->error->error('Error al cargar doc', $regenera);
        }
        $verifica = $this->verifica_si_no_dbx($id);
        if(errores::$error){
            return $this->error->error('Error al verificar en dp', $verifica);
        }
        return $regenera->ruta_relativa;
    }

    /**
     * PHPUNIT
     * @param string $ruta_relativa
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    private function init_full(string $ruta_relativa, int $id): bool|array
    {
        $valida = $this->validacion->valida_ruta($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al validar ruta', $valida);
        }
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $full = $this->recarga_full($ruta_relativa, $id);
        if(errores::$error){
            return $this->error->error('Error al enviar a full', $full);
        }

        $en_full = $this->con_full($id);
        if(errores::$error){
            return $this->error->error('Error validar si esta en full', $en_full);
        }
        if(!$en_full){
            $full = $this->envia_a_full($id);
            if(errores::$error){
                return $this->error->error('Error al enviar a full', $full);
            }
        }
        return $en_full;
    }

    /**
     * PHPUNIT
     * @param string $ruta_relativa
     * @param int $id $id Identificador del documento
     * @return array
     */
    private function init_to_full(string $ruta_relativa, int $id): array
    {
        $valida = $this->validacion->valida_ruta($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al validar ruta', $valida);
        }
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $existe = (new dropbox())->elimina_si_existe($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al validar si existe carpeta', $existe);
        }

        $crea = (new dropbox())->crea_doc($ruta_relativa, $ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error crear doc', $crea);
        }

        $full = $this->envia_a_full($id);
        if(errores::$error){
            return $this->error->error('Error al enviar a full', $full);
        }
        return $full;
    }

    /**
     * PHPUNIT
     * @param string $ruta_relativa
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    private function recarga_full(string $ruta_relativa, int $id): bool|array
    {
        $valida = $this->validacion->valida_ruta($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al validar ruta', $valida);
        }
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $existe_dbx = (new dropbox())->existe_path_dbx($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error validar si existe doc', $existe_dbx);
        }
        if(!$existe_dbx){
            $full = $this->init_to_full($ruta_relativa, $id);
            if(errores::$error){
                return $this->error->error('Error al enviar a full', $full);
            }
        }
        return $existe_dbx;
    }

    /**
     * PHPUNIT
     * @param string $ruta_relativa ruta del documento en dropbox
     * @return array
     */
    private function regenera_doc(string $ruta_relativa): array
    {
        $ruta_relativa = trim($ruta_relativa);
        if($ruta_relativa === ''){
            return $this->error->error('Error el $ruta_relativa no puede venir vacio', $ruta_relativa);
        }

        $ruta_fisica_server = $ruta_relativa;
        $ruta_fisica_server = str_replace(PATH_BASE, '', $ruta_fisica_server);
        $ruta_fisica_server = PATH_BASE.$ruta_fisica_server;

        if(!file_exists($ruta_fisica_server)){
            return $this->error->error('Error no existe el documento $ruta_fisica_server', $ruta_fisica_server);
        }

        $carga = (new dropbox())->regenera_doc($ruta_relativa, $ruta_fisica_server);
        if(errores::$error){
            return $this->error->error('Error al cargar doc', $carga);
        }
        return $carga;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @return array|stdClass
     */
    private function regenera_documento(int $id): array|stdClass
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $rutas = $this->_mdr->rutas($id);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }

        $regenera = $this->regenera_si_no_existe($rutas->ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al cargar doc', $regenera);
        }
        return $rutas;
    }

    /**
     * PHPUNIT
     * @param string $ruta_relativa ruta del documento en dropbox
     * @return array
     */
    private function regenera_si_no_existe(string $ruta_relativa): array
    {
        $ruta_relativa = trim($ruta_relativa);
        if($ruta_relativa === ''){
            return $this->error->error('Error $ruta_relativa debe existir', $ruta_relativa);
        }

        $ruta_absoluta_server_doc = str_replace(PATH_BASE, '', $ruta_relativa);
        $ruta_absoluta_server_doc = PATH_BASE.$ruta_absoluta_server_doc;

        if(!file_exists($ruta_absoluta_server_doc)){
            return $this->error->error('Error no existe el documento en server local', $ruta_absoluta_server_doc);
        }
        $carga = array();
        $existe = (new dropbox())->existe_path_dbx($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al verificar si existe doc', $existe);
        }
        if(!$existe){
            $carga = $this->regenera_doc($ruta_relativa);
            if(errores::$error){
                return $this->error->error('Error al cargar doc', $carga);
            }
        }
        return $carga;
    }

    /**
     * PHPUNIT
     * @param array $documento
     * @param int $id $id Identificador del documento
     * @return array
     */
    private function rename_verificado_y_fin(array $documento, int $id): array
    {
        if($id<=0){
            return $this->error->error('Error el id debe ser mayor a 0', $id);
        }
        $key_r = $this->tabla.'_rename_doc';
        $keys = array($key_r);
        $valida = $this->validacion->valida_statuses($documento, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar documento', $valida);
        }

        if($documento[$key_r] === 'inactivo'){
            $data = $this->modelo->rename_doc($id);
            if(errores::$error){
                return $this->error->error('Error al renombrar documento', $data);
            }
        }
        $documento = $this->ejecuta_finalizacion( $documento, $id);
        if(errores::$error){
            return $this->error->error('Error al obtener documento', $documento);
        }
        return $documento;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    private function revalida_con_dropbox(int $id): bool|array
    {
        $con_db = $this->con_dropbox($id);
        if(errores::$error){
            return $this->error->error('Error al verificar con dropbox', $con_db);
        }
        if(!$con_db) {
            $verifica = $this->verifica_dropbox($id);
            if (errores::$error) {
                return $this->error->error('Error al verificar dropbox', $verifica);
            }
        }
        $con_db = $this->con_dropbox($id);
        if(errores::$error){
            return $this->error->error('Error al verificar con dropbox', $con_db);
        }
        return $con_db;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @return bool|array
     */
    public function revalida_docs_dbx( int $id): bool|array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $rutas = $this->_mdr->rutas($id);
        if(errores::$error){
            return $this->error->error('Error al obtener rutas', $rutas);
        }

        $existe = (new dropbox())->existe_path_dbx($rutas->ruta_full);
        if(errores::$error){
            return $this->error->error('Error al validar si existe en dbx', $existe);
        }
        if(!$existe){
            $recarga = $this->modelo->aplica_finalizado($id);
            if(errores::$error){
                return $this->error->error('Error al aplicar finalizado', $recarga);
            }
        }
        return $existe;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas
     * @param stdClass $rutas_transaccion Rutas que debería tener el documento ruta_relativa, ruta_relativa_new ruta_absoluta
     * @return array
     */
    private function transacciones_dbx(stdClass $rutas, stdClass $rutas_transaccion): array
    {
        $rutas_arr = (array)$rutas;
        $keys = array('ruta_relativa','ruta_full');
        $valida = $this->validacion->valida_existencia_keys($rutas_arr, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $rutas', $valida);
        }
        $obj_array = (array)$rutas_transaccion;
        $keys = array('ruta_relativa_new','ruta_absoluta');
        $valida = $this->validacion->valida_existencia_keys($obj_array, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar rutas_transaccion', $valida);
        }

        $del = $this->del_dropbox_completo($rutas);
        if(errores::$error){
            return $this->error->error('Error al eliminar', $del);
        }

        $crea = $this->crea_doc_completo_dbx($rutas_transaccion);
        if(errores::$error){
            return $this->error->error('Error al crear', $crea);
        }
        return $crea;
    }

    /**
     * PHPUNIT
     * @param stdClass $rutas_transaccion Rutas que debería tener el documento ruta_relativa, ruta_relativa_new ruta_absoluta, ruta
     * @param stdClass $rutas
     * @param int $id $id Identificador del documento
     * @return array
     */
    public function transacciones_rename(stdClass $rutas_transaccion, stdClass $rutas, int $id): array
    {
        $valida = $this->validacion->valida_rename($rutas_transaccion, $id, $this->modelo);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $rutas_arr = (array)$rutas;
        $keys = array('ruta_relativa','ruta_full');
        $valida = $this->validacion->valida_existencia_keys($rutas_arr, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar $rutas', $valida);
        }

        $upd_bd = $this->modelo->upd_rename($rutas_transaccion, $id);
        if(errores::$error){
            return $this->error->error('Error al actualizar', $upd_bd);
        }

        $crea = $this->transacciones_dbx($rutas, $rutas_transaccion);
        if(errores::$error){
            return $this->error->error('Error al crear', $crea);
        }
        return $crea;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @param string $ruta Ruta de Dropbox
     * @return array
     */
    private function sube_dbx(int $id, string $ruta): array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $ruta = trim($ruta);
        if($ruta === ''){
            return $this->error->error("Error la ruta esta vacia", $ruta);
        }

        $origen = $this->_mdr->ruta_absoluta($id);
        if(errores::$error){
            return $this->error->error("Error al obtener ruta", $origen);
        }
        $carga = (new dropbox())->crea_doc($ruta, $origen);
        if(errores::$error){
            return $this->error->error("Error al cargar doc", $carga);
        }
        return $carga;
    }

    /**
     * PHPUNIT
     * @param int $id $id Identificador del documento
     * @return bool|array
     */
    public function verifica_dropbox(int $id): bool|array
    {
        if($id<=0){
            return $this->error->error('Error id debe ser mayor a 0', $id);
        }
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla viene vacia', $this->tabla);
        }

        $con_dp = $this->con_dropbox($id);
        if(errores::$error){
            return $this->error->error('Error al verificar si con dropbox', $con_dp);
        }
        if(!$con_dp){
            $doc_upd = $this->ajusta_dbx($id);
            if(errores::$error){
                return $this->error->error('Error al actualizar doc', $doc_upd);
            }
        }

        return $con_dp;

    }

    /**
     * PHPUNIT
     * @param string $ruta_relativa
     * @return bool|array
     */
    private function verifica_si_dbx(string $ruta_relativa): bool|array
    {
        $ruta_relativa = trim($ruta_relativa);
        if($ruta_relativa === ''){
            return $this->error->error('Error $ruta_relativa viene vacia', $ruta_relativa);
        }
        $existe_db = (new dropbox())->error_si_no_existe($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error no existe la ruta', $existe_db);
        }
        $existe = (new dropbox())->existe_path_dbx($ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al verificar si existe doc', $existe);
        }
        return $existe;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @return bool|array
     */
    private function verifica_si_no_dbx(int $id): bool|array
    {
        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }
        $con_db = $this->con_dropbox($id);
        if(errores::$error){
            return $this->error->error('Error al obtener info del documento', $con_db);
        }
        if(!$con_db){
            $verifica = $this->verifica_dropbox($id);
            if(errores::$error){
                return $this->error->error('Error al verificar en dp', $verifica);
            }
        }
        return $con_db;
    }


}
