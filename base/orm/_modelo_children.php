<?php
namespace base\orm;

use gamboamartin\errores\errores;

class _modelo_children extends modelo {

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
            $codigo = $this->codigo_alta_default($parents_data,anexo_codigo: $registro['descripcion']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo',data: $codigo);
            }
            $registro['codigo'] = $codigo;
        }
        return $registro;
    }

    private function codigo_alta_default(array $parents_data, string $anexo_codigo = ''): array|string
    {

        $codigo = $anexo_codigo;
        foreach ($parents_data as $name_model=>$data){
            $data['registro_id'] = $this->registro[$data['key_id']];
            $valida = $this->valida_codigo_default(name_model: $name_model, data: $data);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al validar data',data: $valida);
            }

            $modelo_parent = $this->genera_modelo(modelo: $name_model, namespace_model: $data['namespace']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar modelo parent',data: $modelo_parent);
            }
            $row_parent = $modelo_parent->registro(registro_id: $data['registro_id'],retorno_obj: true);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener row parent',data: $row_parent);
            }

            $keys_parents = $data['keys_parents'];

            $valida = $this->validacion->valida_existencia_keys(keys: $keys_parents, registro: $row_parent);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al validar row_parent',data: $valida);
            }

            foreach ($keys_parents as $key_parent){

                $codigo .= $row_parent->$key_parent;
            }
            $codigo = trim($codigo);
        }

        return $codigo;
    }

    private function codigo_bis_alta_default(string $codigo): array|string
    {
        return strtoupper($codigo);
    }

    private function descripcion_alta_default(array $parents_data, string $anexo_descripcion = ''): array|string
    {

        $descripcion = $anexo_descripcion;
        foreach ($parents_data as $name_model=>$data){
            $data['registro_id'] = $this->registro[$data['key_id']];
            $valida = $this->valida_descripcion_default(name_model: $name_model, data: $data);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al validar data',data: $valida);
            }

            $modelo_parent = $this->genera_modelo(modelo: $name_model, namespace_model: $data['namespace']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar modelo parent',data: $modelo_parent);
            }
            $row_parent = $modelo_parent->registro(registro_id: $data['registro_id'],retorno_obj: true);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener row parent',data: $row_parent);
            }

            $keys_parents = $data['keys_parents'];

            $valida = $this->validacion->valida_existencia_keys(keys: $keys_parents, registro: $row_parent);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al validar row_parent',data: $valida);
            }

            foreach ($keys_parents as $key_parent){

                $descripcion .= $row_parent->$key_parent;
            }
            $descripcion = trim($descripcion);
        }

        return $descripcion;
    }

    public function descripcion_default(array $parents_data, array $registro): array
    {
        if(!isset($registro['descripcion'])){
            $descripcion = $this->descripcion_alta_default($parents_data,anexo_descripcion: $registro['codigo']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar descripcion',data: $descripcion);
            }
            $registro['descripcion'] = $descripcion;
        }
        return $registro;
    }

    private function descripcion_select_alta_default(array $parents_data, string $anexo_descripcion_select = ''): array|string
    {

        $descripcion_select = $anexo_descripcion_select;

        foreach ($parents_data as $name_model=>$data){
            $data['registro_id'] = $this->registro[$data['key_id']];
            $valida = $this->valida_descripcion_default(name_model: $name_model, data: $data);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al validar data',data: $valida);
            }

            $modelo_parent = $this->genera_modelo(modelo: $name_model, namespace_model: $data['namespace']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar modelo parent',data: $modelo_parent);
            }
            $row_parent = $modelo_parent->registro(registro_id: $data['registro_id'],retorno_obj: true);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener row parent',data: $row_parent);
            }

            $keys_parents = $data['keys_parents'];

            $valida = $this->validacion->valida_existencia_keys(keys: $keys_parents, registro: $row_parent);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al validar row_parent',data: $valida);
            }

            foreach ($keys_parents as $key_parent){

                $descripcion_select .= ' '.$row_parent->$key_parent.' ';
            }
            $descripcion_select = trim($descripcion_select);
        }
        return strtoupper($descripcion_select);
    }

    public function descripcion_select(array $parents_data, array $registro): array
    {
        if(!isset($registro['descripcion_select'])){
            $descripcion_select = $this->descripcion_select_alta_default($parents_data,anexo_descripcion_select: $registro['descripcion']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar descripcion',data: $descripcion_select);
            }

            $registro['descripcion_select'] = $descripcion_select;
        }
        return $registro;
    }

    private function valida_codigo_default(mixed $name_model, mixed $data): bool|array
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

    private function valida_descripcion_default(mixed $name_model, mixed $data): bool|array
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


}
