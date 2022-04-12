<?php
namespace orm;


use config\empresas;
use gamboamartin\errores\errores;

use gamboamartin\orm\modelo;
use models\alias_correo;
use PDO;
use stdClass;

class _model_prospecto extends modelo {
    private array $modelos_rel ;
    private array $keys_correo;

    public function __construct(PDO $link, string $tabla, array $columnas_extra = array(),
                                array $campos_obligatorios = array(), array $tipo_campos = array(),
                                array $columnas = array(), array $sub_querys = array(),
                                bool $aplica_transaccion_inactivo = true, bool $aplica_bitacora = false,
                                bool $aplica_seguridad = false, array $extension_estructura = array(),
                                array $renombres = array(), bool $validation = false)
    {
        parent::__construct($link, $tabla, $columnas_extra, $campos_obligatorios, $tipo_campos, $columnas,
            $sub_querys, $aplica_transaccion_inactivo, $aplica_bitacora, $aplica_seguridad,
            $extension_estructura, $renombres, $validation);

        $this->modelos_rel = array('alias_prospecto','alias_ubicacion');
        $this->keys_correo = array('correo_tramite','correo_tramite_pass','cel_tramite','correo_info',
            'pass_correo_info','pass_info','cel_info');
    }

    private function alias_mail_bd(int $id, string $key_correo, string $name_modelo_rel, array $keys): array|string
    {

        $valida = $this->valida_base($id,$keys,$key_correo,$name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $registro = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener $registro',$registro);
        }

        $correo_name = $this->correo_tramite($registro,$keys);
        if(errores::$error){
            return $this->error->error('Error al generar correo',$correo_name);
        }
        $upd = $this->upd_correo_name($correo_name, $id,$key_correo);
        if(errores::$error){
            return $this->error->error('Error al modificar $registro',$upd);
        }

        $r_alta_alias = $this->transaccion_email_alias($correo_name,$id,$name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al generar alias',$r_alta_alias);
        }

        return $correo_name;
    }

    /**
     * PHPUNIT
     * @param int $alias_correo_id
     * @param int $id
     * @return array
     */
    private function alias_prospecto_ins(int $alias_correo_id, int $id): array
    {
        if($alias_correo_id<=0 ){
            return $this->error->error('Error $alias_correo_id debe ser mayor a 0', $alias_correo_id);
        }
        if($id<=0 ){
            return $this->error->error('Error $id debe ser mayor a 0', $id);
        }
        $alias_prospecto_ins['alias_correo_id'] = $alias_correo_id;
        $alias_prospecto_ins[$this->tabla.'_id'] = $id;
        $alias_prospecto_ins['status'] = 'activo';
        return $alias_prospecto_ins;
    }

    /**
     * PHPUNIT
     * @param array $registro
     * @param string $key
     * @param array $registro_upd
     * @return bool|array
     */
    private function compara_data_email(array $registro, string $key, array $registro_upd): bool|array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error key esta vacio ', $key);
        }
        $key_data = $this->tabla.'_'.$key;
        if(isset($registro[$key_data]) && $registro[$key_data]!==''){
            if(isset($registro_upd[$key]) && $registro_upd[$key] !==''){
                if($registro[$key_data]!==$registro_upd[$key]) {
                    return $this->error->error('Error no se puede modificar este dato: ' . $key, $registro[$key_data]);
                }
            }
        }
        return  true;
    }

    /**
     * PHPUNIT
     * @param array $registro
     * @param array $keys
     * @return string|array
     */
    private function correo_tramite(array $registro, array $keys): string|array
    {
        $valida = $this->valida_correo_tramite_data($registro, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar prospecto',$valida);
        }
        if(!isset($keys[0])){
            return $this->error->error('Error $keys[0] no existe',$keys);
        }
        if(!isset($keys[1])){
            return $this->error->error('Error $keys[1] no existe',$keys);
        }

        $data = new stdClass();
        $data->name = $keys[0];
        $data->ap = $keys[1];

        $correo_name = $this->genera_correo_tramite_base($registro, $data->name,$data->ap);
        if(errores::$error){
            return $this->error->error('Error al generar correo',$correo_name);
        }

        $correo_name = $this->limpia_correo_tramite($correo_name);
        if(errores::$error){
            return $this->error->error('Error al limpiar correo',$correo_name);
        }

        return strtolower($correo_name);

    }

    public function genera_alias_mail(int $id, array $keys, string $key_correo, string $name_modelo_rel): array|string
    {
        if($id<=0){
            return $this->error->error('Error $id debe ser mayor a 0', $id);
        }
        $registro = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener $registro', $registro);
        }
        if(isset($registro[$this->tabla.'_'.$key_correo]) && trim($registro[$this->tabla.'_'.$key_correo])!==''){
            return $this->error->error('Error el $registro ya tiene correo asignado', $registro);
        }

        $correo_name = $this->alias_mail_bd($id,$key_correo,$name_modelo_rel,$keys);
        if(errores::$error){
            return $this->error->error('Error al modificar prospecto',$correo_name);
        }

        return $correo_name;
    }

    /**
     * PHPUNIT
     * @param array $registro
     * @param string $key_name
     * @param string $key_ap
     * @return string|array
     */
    private function genera_correo_tramite_base(array $registro, string $key_name, string $key_ap): string|array
    {
        $key_name = trim($key_name);
        if($key_name === ''){
            return $this->error->error('Error key_name esta vacio', $key_name);
        }
        $key_ap = trim($key_ap);
        if($key_ap === ''){
            return $this->error->error('Error $key_ap esta vacio', $key_ap);
        }

        $keys = array($key_name, $key_ap);
        $valida = $this->validacion->valida_existencia_keys($registro, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar registro', $valida);
        }

        $empresas = new empresas();
        $correo_name = $registro[$key_name].".".$registro[$key_ap].".".mt_rand(1,9999);
        return $correo_name."@".$empresas->empresas[1]['url_mail'];
    }

    /**
     * PHPUNIT
     * @param string $correo_name
     * @return array
     */
    private function init_alias_correo(string $correo_name): array
    {
        $correo_name = trim($correo_name);
        if($correo_name ===''){
            return $this->error->error('Error correo_name vacio',$correo_name);
        }

        $valida = $this->validacion->valida_correo($correo_name);
        if(errores::$error){
            return $this->error->error('Error al validar correo_name',$valida);
        }


        $empresa = (new empresas())->empresas['1'];
        $alias_correo_ins['correo'] = $correo_name;
        $alias_correo_ins['status'] = 'activo';
        $alias_correo_ins['server_id'] = $empresa['server_correo_id'];
        $alias_correo_ins['id_mailbox'] = -1;
        $alias_correo_ins['destino'] = $empresa['redireccion'];
        return $alias_correo_ins;
    }

    /**
     * PHPUNIT
     * @param string $correo_name
     * @return array
     */
    private function inserta_alias(string $correo_name): array
    {
        $correo_name = trim($correo_name);
        if($correo_name ===''){
            return $this->error->error('Error correo_name vacio',$correo_name);
        }

        $valida = $this->validacion->valida_correo($correo_name);
        if(errores::$error){
            return $this->error->error('Error al validar correo_name',$valida);
        }

        $alias_correo_ins = $this->init_alias_correo($correo_name);
        if(errores::$error){
            return $this->error->error('Error al asignar datos de alias',$alias_correo_ins);
        }

        $r_alta_alias = (new alias_correo($this->link))->alta_registro($alias_correo_ins);
        if(errores::$error){
            return $this->error->error('Error al generar alias',$r_alta_alias);
        }
        return $r_alta_alias;
    }

    /**
     * PHPUNIT
     * @param int $alias_correo_id
     * @param int $prospecto_id
     * @param string $name_modelo_rel
     * @return array
     */
    private function inserta_alias_prospecto(int $alias_correo_id, int $prospecto_id, string $name_modelo_rel): array
    {
        if($alias_correo_id<=0 ){
            return $this->error->error('Error $alias_correo_id debe ser mayor a 0', $alias_correo_id);
        }
        if($prospecto_id<=0 ){
            return $this->error->error('Error $prospecto_id debe ser mayor a 0', $prospecto_id);
        }
        $valida = $this->valida_modelo_rel($name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al validar name_modelo_rel', $valida);
        }

        $alias_prospecto_ins = $this->alias_prospecto_ins($alias_correo_id,$prospecto_id);
        if(errores::$error){
            return $this->error->error('Error al generar alias',$alias_prospecto_ins);
        }

        $valida = $this->validacion->valida_data_modelo($name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al validar name_modelo_rel', $valida);
        }

        $modelo = $this->genera_modelo($name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo);
        }
        $r_alta_alias_prospecto = $modelo->alta_registro($alias_prospecto_ins);
        if(errores::$error){
            return $this->error->error('Error al insertar relacion de alias',$r_alta_alias_prospecto);
        }
        return $r_alta_alias_prospecto;
    }

    /**
     * PHPUNIT
     * @param string $correo_name
     * @return string|array
     */
    private function limpia_correo_tramite(string $correo_name): string|array
    {
        $correo_name = trim($correo_name);
        if($correo_name===''){
            return $this->error->error('Error correo_name esta vacio', $correo_name);
        }
        $correo_name = strtolower($correo_name);
        $correo_name = str_replace(' ','',$correo_name);
        $correo_name = str_replace('á','a',$correo_name);
        $correo_name = str_replace('é','e',$correo_name);
        $correo_name = str_replace('í','i',$correo_name);
        $correo_name = str_replace('ó','o',$correo_name);
        $correo_name = str_replace('ú','u',$correo_name);
        $correo_name = str_replace('ñ','n',$correo_name);
        $correo_name = str_replace('Ñ','N',$correo_name);
        return strtolower($correo_name);
    }


    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array
    {
        $valida = $this->valida_upd($id, $registro);
        if(errores::$error){
            return $this->error->error('Error al validar registro', $valida);
        }
        $r_modifica = parent::modifica_bd($registro, $id, $reactiva); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al modificar prospecto', $r_modifica);
        }
        return $r_modifica;
    }

    private function transaccion_email_alias(string $correo_name, int $id, string $name_modelo_rel): array
    {
        $valida = $this->validacion->valida_data_modelo($name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al validar name_modelo_rel', $valida);
        }

        $r_alta_alias = $this->inserta_alias($correo_name);
        if(errores::$error){
            return $this->error->error('Error al generar alias',$r_alta_alias);
        }

        $r_alta_alias_prospecto = $this->inserta_alias_prospecto($r_alta_alias['registro_id'], $id,$name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al insertar relacion de alias',$r_alta_alias_prospecto);
        }

        return $r_alta_alias;
    }

    /**
     * PHPUNIT
     * @param string $correo_name
     * @param int $id
     * @param string $key
     * @return array
     */
    private function upd_correo_name(string $correo_name, int $id, string $key): array
    {
        if($id<=0){
            return $this->error->error('Error $id debe ser mayor a 0',$id);
        }
        $correo_name = trim($correo_name);
        if($correo_name === ''){
            return $this->error->error('Error correo name no puede venir vacio',$correo_name);
        }
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error $key no puede venir vacio',$key);
        }

        $valida = $this->validacion->valida_correo($correo_name);
        if(errores::$error){
            return $this->error->error('Error al validar correo_name',$valida);
        }


        $registro_upd[$key] = $correo_name;

        $modelo = (new modelo($this->link,$this->tabla));

        $upd = $modelo->modifica_bd($registro_upd, $id);
        if(errores::$error){
            return $this->error->error('Error al modificar prospecto',$upd);
        }


        return $upd;
    }

    /**
     * PHPUNIT
     * @param int $id
     * @param array $keys
     * @param string $key_correo
     * @param string $name_modelo_rel
     * @return bool|array
     */
    private function valida_base(int $id, array $keys, string $key_correo, string $name_modelo_rel): bool|array
    {
        if($id<=0){
            return $this->error->error('Error $id debe ser mayor a 0',$id);
        }

        $valida = $this->valida_keys($keys);
        if(errores::$error){
            return $this->error->error('Error al validar keys',$valida);
        }

        $key_correo = trim($key_correo);
        if($key_correo === ''){
            return $this->error->error('Error $key_correo no puede venir vacio',$key_correo);
        }

        $valida = $this->valida_modelo_rel($name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al validar name_modelo_rel', $valida);
        }

        return true;
    }

    /**
     * PHPUNIT
     * @param array $registro
     * @param array $keys
     * @return bool|array
     */
    private function valida_correo_tramite_data(array $registro, array $keys): bool|array
    {
        $valida = $this->validacion->valida_existencia_keys($registro, $keys);
        if(errores::$error){
            return $this->error->error('Error al validar registro',$valida);
        }

        foreach ($keys as $key){
            $value = $registro[$key];
            $value = trim($value);
            if(is_numeric($value[0])){
                return $this->error->error('Error el valor '.$key.' no puede iniciar con un numero',$valida);
            }
        }
        return true;
    }

    private function valida_datos_mail(array $registro, array $registro_upd): bool|array
    {
        foreach($this->keys_correo as $key){
            $valida = $this->compara_data_email($registro, $key,  $registro_upd);
            if(errores::$error){
                return $this->error->error('Error al validar registro', $valida);
            }
        }
        return true;
    }

    /**
     * PHPUNIT
     * @param string $name_modelo_rel
     * @return bool|array
     */
    private function valida_modelo_rel(string $name_modelo_rel): bool|array
    {
        $valida = $this->validacion->valida_data_modelo($name_modelo_rel);
        if(errores::$error){
            return $this->error->error('Error al validar name_modelo_rel', $valida);
        }

        if(!in_array($name_modelo_rel, $this->modelos_rel)){
            return $this->error->error('Error el modelo relacionado es invalido', $name_modelo_rel);
        }
        return true;
    }

    /**
     * PHPUNIT
     * @param array $keys
     * @return bool|array
     */
    private function valida_keys(array $keys): bool|array
    {
        if(!isset($keys[0])){
            return $this->error->error('Error $keys[0] no existe',$keys);
        }
        if(!isset($keys[1])){
            return $this->error->error('Error $keys[1] no existe',$keys);
        }
        if(trim($keys[0]) === ''){
            return $this->error->error('Error $keys[0] esta vacio',$keys);
        }
        if(trim($keys[1]) === ''){
            return $this->error->error('Error $keys[1] esta vacio',$keys);
        }
        return true;
    }

    private function valida_upd(int $id, array $registro_upd): bool|array
    {
        $registro = $this->registro($id);
        if(errores::$error){
            return $this->error->error('Error al obtener registro', $registro);
        }

        $valida = $this->valida_datos_mail($registro,  $registro_upd);
        if(errores::$error){
            return $this->error->error('Error al validar registro', $valida);
        }
        return true;
    }
}