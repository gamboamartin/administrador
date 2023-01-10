<?php
namespace base\orm;

use gamboamartin\errores\errores;
use stdClass;

class _modelo_children extends _base {

    private function alias_alta_default(string $descripcion): array|string
    {
        return strtoupper($descripcion);
    }

    public function alias_default(array $registro): array
    {
        if(!isset($registro['alias'])){
            $alias = $this->alias_alta_default(descripcion: $registro['descripcion']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar alias',data: $alias);
            }
            $registro['alias'] = $alias;
        }
        return $registro;
    }

    private function campos_necesarios(int $id, array $keys, array $registro): array
    {
        $registro_previo = $this->registro(registro_id: $id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data: $registro_previo);
        }

        $registro = $this->integra_campos_necesarios(keys: $keys,registro: $registro,registro_previo:  $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar registro',data: $registro);
        }
        return $registro;
    }

    public function codigo_bis_default(array $registro): array
    {
        if(!isset($registro['codigo_bis'])){
            $codigo_bis = $this->codigo_bis_alta_default(codigo: $registro['codigo']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo',data: $codigo_bis);
            }
            $registro['codigo_bis'] = $codigo_bis;
        }
        return $registro;
    }

    public function codigo_default(array $parents_data, array $registro): array
    {
        if(!isset($registro['codigo'])){
            $codigo = $this->codigo_alta_default(parents_data: $parents_data, registro: $registro,anexo_codigo: $registro['descripcion']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo',data: $codigo);
            }
            $registro['codigo'] = $codigo;
        }
        return $registro;
    }

    private function codigo_alta_default(array $parents_data, array $registro, string $anexo_codigo = ''): array|string
    {

        $value_default = $anexo_codigo;
        foreach ($parents_data as $name_model=>$data){
            $value_default = $this->value_default(data:$data,name_model:  $name_model, registro: $registro,value_default:  $value_default);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar valor default',data: $value_default);
            }
        }

        return $value_default;
    }

    private function codigo_bis_alta_default(string $codigo): array|string
    {
        return strtoupper($codigo);
    }

    private function data_default(array $data, string $name_model, array $registro): array|stdClass
    {

        $keys = array('key_id');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al validar data en modelo '.$this->tabla,data: $valida);
        }

        $key_id = $data['key_id'];

        $keys = array($key_id);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al validar registro en modelo '.$this->tabla,data: $valida);
        }

        $data['registro_id'] = $registro[$key_id];
        $valida = $this->valida_value_default(name_model: $name_model, data: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al validar data modelo en '.$this->tabla,data: $valida);
        }

        $modelo_parent = $this->genera_modelo(modelo: $name_model, namespace_model: $data['namespace']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo parent en modelo '.$this->tabla,
                data: $modelo_parent);
        }
        $row_parent = $modelo_parent->registro(registro_id: $data['registro_id'],retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener row parent en modelo '.$this->tabla,data: $row_parent);
        }

        $keys_parents = $data['keys_parents'];

        $datos = new stdClass();
        $datos->data = $data;
        $datos->modelo_parent = $modelo_parent;
        $datos->row_parent = $row_parent;
        $datos->keys_parents = $keys_parents;

        return $datos;

    }

    private function descripcion_alta_default(array $parents_data, array $registro, string $anexo_descripcion = ''): array|string
    {

        $value_default = $anexo_descripcion;
        foreach ($parents_data as $name_model=>$data){
            $value_default = $this->value_default(data: $data, name_model: $name_model, registro: $registro, value_default: $value_default);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar valor default',data: $value_default);
            }
        }

        return $value_default;
    }

    public function descripcion_default(array $parents_data, array $registro): array
    {
        if(!isset($registro['descripcion'])){
            $descripcion = $this->descripcion_alta_default($parents_data, registro: $registro,anexo_descripcion: $registro['codigo']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar descripcion',data: $descripcion);
            }
            $registro['descripcion'] = $descripcion;
        }
        return $registro;
    }

    private function descripcion_select_alta_default(array $parents_data, array $registro, string $anexo_descripcion_select = ''): array|string
    {

        $descripcion_select = $anexo_descripcion_select;

        foreach ($parents_data as $name_model=>$data){

            $data_default = $this->data_default(data: $data,name_model:  $name_model, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener datos en modelo '.$this->tabla,data: $data_default);
            }

            $valida = $this->validacion->valida_existencia_keys(keys: $data_default->keys_parents, registro: $data_default->row_parent);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al validar row_parent en modelo '.$this->tabla,data: $valida);
            }

            foreach ($data_default->keys_parents as $key_parent){

                $descripcion_select .= ' '.$data_default->row_parent->$key_parent.' ';
            }
            $descripcion_select = trim($descripcion_select);
        }
        return strtoupper($descripcion_select);
    }

    public function descripcion_select(array $parents_data, array $registro): array
    {
        if(!isset($registro['descripcion_select'])){
            $descripcion_select = $this->descripcion_select_alta_default(parents_data: $parents_data,registro: $registro ,anexo_descripcion_select: $registro['descripcion']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar descripcion en modelo '.$this->tabla,data: $descripcion_select);
            }

            $registro['descripcion_select'] = $descripcion_select;
        }
        return $registro;
    }

    protected function init_row_alta(array $defaults, array $parents_data, array $registro): array
    {
        $valida = $this->valida_alta_bd(registro:$registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro en modelo '.$this->tabla,data: $valida);
        }

        $registro = $this->asigna_full_status_alta(registro:$registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar status en modelo '.$this->tabla,data: $registro);
        }

        foreach ($defaults as $campo=>$value){
            if(!isset($registro[$campo])){
                $registro[$campo] = $value;
            }
        }

        $registro = $this->codigo_default(parents_data: $parents_data, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar codigo en modelo '.$this->tabla,data: $registro);
        }

        $registro = $this->descripcion_default(parents_data: $parents_data, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar descripcion en modelo '.$this->tabla,data: $registro);
        }

        $registro = $this->codigo_bis_default( registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar codigo_bis en modelo '.$this->tabla,data: $registro);
        }

        $registro = $this->alias_default( registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar alias en modelo '.$this->tabla,data: $registro);
        }

        $registro = $this->descripcion_select(parents_data: $parents_data, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar descripcion_select en modelo '.$this->tabla,data: $registro);
        }

        return $registro;
    }

    protected function init_row_upd(int $id, array $keys, array $registro): array
    {
        $registro = $this->campos_necesarios(id: $id, keys: $keys,registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar registro',data: $registro);
        }

        $registro = $this->init_row_alta(defaults: $this->defaults, parents_data: $this->parents_data, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar registro',data: $registro);
        }
        return $registro;
    }

    /**
     * Integra un campo para inicializado en upd
     * @param string $key Campo a integrar
     * @param array $registro Registro en proceso
     * @param stdClass $registro_previo Registro precio a la actualizacion
     * @return array
     * @version 2.109.12
     */
    private function integra_campo_necesario(string $key, array $registro, stdClass $registro_previo): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
        }
        $key_data = $this->tabla.'_'.$key;
        if(!isset($registro[$key])){
            $key_data = trim($key_data);
            if(!isset($registro_previo->$key_data)){
                return $this->error->error(mensaje: 'Error no existe registro['.$key_data.']', data: $registro_previo);
            }

            $registro[$key] = $registro_previo->$key_data;
        }
        return $registro;
    }

    private function integra_campos_necesarios(array $keys, array $registro, stdClass $registro_previo): array
    {
        foreach ($keys as $key) {
            $registro = $this->integra_campo_necesario(key: $key, registro: $registro, registro_previo: $registro_previo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar registro', data: $registro);
            }
        }
        return $registro;
    }

    private function integra_valor_default(array $keys_parents, stdClass $row_parent, string $value_previo): string
    {
        foreach ($keys_parents as $key_parent){
            $value_previo .= $row_parent->$key_parent;
        }
        return $value_previo;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false, array $keys = array('descripcion')): array|stdClass
    {
        if($id <=0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0',data: $id);
        }
        if($this->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $this->usuario_id);
        }

        $registro = $this->init_row_upd(id: $id,keys:  $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar registro',data: $registro);
        }


        $r_modifica_bd = parent::modifica_bd(registro: $registro,id:  $id, reactiva: $reactiva); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al modificar registro '.$this->tabla, $r_modifica_bd);
        }
        return $r_modifica_bd;
    }

    private function valida_value_default(mixed $name_model, mixed $data): bool|array
    {
        if(!is_string($name_model)){
            return $this->error->error(mensaje: 'Error name_model no es un texto',data: $name_model);
        }

        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error name_model esta vacio',data: $name_model);
        }

        $keys = array('namespace','registro_id','keys_parents');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al validar data',data: $valida);
        }

        $keys = array('registro_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al validar data',data: $valida);
        }

        if(!is_array($data)){
            return $this->error->error(mensaje: 'Error data no es un array',data: $data);
        }

        $keys = array('keys_parents');
        $valida = $this->validacion->valida_arrays(keys: $keys, row: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al validar data',data: $valida);
        }
        return true;
    }

    private function value_default(array $data, string $name_model, array $registro, string $value_default): array|string
    {
        $data_default = $this->data_default(data: $data,name_model:  $name_model, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos',data: $data_default);
        }

        $valida = $this->validacion->valida_existencia_keys(keys: $data_default->keys_parents, registro: $data_default->row_parent);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al al validar row_parent',data: $valida);
        }

        $value_default = $this->integra_valor_default(keys_parents: $data_default->keys_parents,row_parent:  $data_default->row_parent,value_previo:  $value_default);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar valor default',data: $value_default);
        }


        return trim($value_default);
    }


}
