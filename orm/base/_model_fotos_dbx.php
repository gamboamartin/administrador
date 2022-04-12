<?php
namespace orm;

use base\dropbox;


use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use stdClass;


use validacion\val_modelo_fotos;

class _model_fotos_dbx {
    public base_modelos $validacion;
    public modelo_fotos $modelo;
    public errores $error;
    public string $tabla;
    public function __construct(modelo_fotos $modelo)
    {
        $this->validacion = new val_modelo_fotos();
        $this->modelo = $modelo;
        $this->error = new errores();
        $this->tabla = $modelo->tabla;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $foto
     * @return bool|array|stdClass
     */
    public function finaliza(array $foto): bool|array|stdClass
    {
        $valida = $this->validacion->valida_data_subida($this->modelo);
        if(errores::$error){
            return $this->error->error('Error validar docs ', $valida);
        }

        $keys = array($this->modelo->key_finalizado);
        $valida = $this->validacion->valida_statuses($foto, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar foto', $valida);
        }
        $keys = array($this->modelo->key_id);
        $valida = $this->validacion->valida_ids($foto, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar foto', $valida);
        }
        $finaliza = false;
        if($foto[$this->modelo->key_finalizado] === 'inactivo'){
            $finaliza = $this->transaccion_finalizado($foto[$this->modelo->key_id]);
            if(errores::$error){
                return $this->error->error('Error al subir doc a dropbox', $finaliza);
            }
        }
        return $finaliza;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @return array|stdClass
     */
    public function finaliza_en_dbx(): array|stdClass
    {

        $valida = $this->validacion->valida_data_subida($this->modelo);
        if(errores::$error){
            return $this->error->error('Error validar docs ', $valida);
        }

        $del = (new dropbox())->elimina_si_existe($this->modelo->ruta_relativa);
        if(errores::$error){
            return $this->error->error('Error al eliminar de dropbox', $del);
        }
        $crea_doc = (new dropbox())->crea_doc($this->modelo->ruta_relativa, $this->modelo->ruta_absoluta);
        if(errores::$error){
            return $this->error->error('Error al subir doc a dropbox', $crea_doc);
        }
        $data = new stdClass();
        $data->del = $del;
        $data->crea_doc = $crea_doc;
        return $data;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $id
     * @return array|stdClass
     */
    public function transaccion_finalizado(int $id): array|stdClass
    {


        $valida = $this->validacion->valida_transaccion_upd($id, $this->modelo->tabla, $this->modelo->tablas_children);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $valida = $this->validacion->valida_data_subida($this->modelo);
        if(errores::$error){
            return $this->error->error('Error validar docs ', $valida);
        }

        $finaliza_en_dbx = $this->finaliza_en_dbx();
        if(errores::$error){
            return $this->error->error('Error al subir doc a dropbox', $finaliza_en_dbx);
        }
        $upd = $this->modelo->upd_finalizado($id);
        if(errores::$error){
            return $this->error->error('Error al actualizar en bd', $upd);
        }
        $data = new stdClass();
        $data->finaliza_en_dbx = $finaliza_en_dbx;
        $data->upd = $upd;
        return $data;
    }

}
