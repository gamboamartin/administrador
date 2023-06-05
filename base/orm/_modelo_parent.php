<?php
namespace base\orm;


use gamboamartin\errores\errores;
use stdClass;

class _modelo_parent extends _base {

    /**
     * Da de alta un registro integrando campos base del modelo como descripcion_select basada en el codigo y descripcion
     * @param array $keys_integra_ds Key a integrar para descripcion select
     * @return array|stdClass
     * @finalrev
     * @version 10.48.2
     */
    public function alta_bd(array  $keys_integra_ds = array('codigo','descripcion')): array|stdClass
    {
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error SESSION no iniciada',data: array());
        }

        $keys = array('descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        $this->registro = $this->campos_base(data:$this->registro,modelo: $this, keys_integra_ds: $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo base',data: $this->registro);
        }

        $registro = $this->limpiar_attrs();
        if(errores::$error) {
            return $this->error->error(mensaje: 'Error al al limpiar', data: $registro);
        }

        $r_alta_bd =  parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
        }
        return $r_alta_bd;
    }

    /**
     * Valida si existe un atributo
     * @param string $campo Campo a validar
     * @return bool|array
     * @version 10.35.2
     */
    private function existe_attr(string $campo): bool|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
        }

        $existe_attr = false;
        $attrs = (array)$this->atributos;
        if(array_key_exists($campo, $attrs)){
            $existe_attr = true;
        }
        return $existe_attr;
    }

    /**
     * Limpia un atributo no existente
     * @param string $campo Campos a limpiar
     * @return array
     * @version 10.16.2
     */
    private function limpiar_attr(string $campo): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
        }
        $existe_attr = $this->existe_attr(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe atributo',data: $existe_attr);
        }
        if(!$existe_attr){
            unset($this->registro[$campo]);
        }
        return $this->registro;
    }

    /**
     * Limpia los atributos de un registro al insertar
     * @return array
     * @version 10.47.2
     */
    private function limpiar_attrs(): array
    {
        foreach ($this->registro as $campo=>$value){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
            }
            $registro = $this->limpiar_attr(campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al al limpiar',data: $registro);
            }
        }
        return $this->registro;
    }

    /**
     * Modifica un registro junto con los campos definidos en keys_integra_ds para descripcion select
     * @param array $registro Datos del registro a modificar
     * @param int $id Identificador del registro
     * @param bool $reactiva Si reactiva validar elementos para validar activacion
     * @param array $keys_integra_ds Campos pra integrar la descripcion select
     * @return array|stdClass
     * @finalrev
     */
    public function modifica_bd(array $registro, int $id, bool $reactiva = false,
                                array $keys_integra_ds = array('codigo','descripcion')): array|stdClass
    {

        $registro = $this->campos_base(data: $registro, modelo: $this, id: $id,keys_integra_ds:$keys_integra_ds );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo base',data: $registro);
        }

        $r_modifica_bd = parent::modifica_bd(
            registro: $registro, id: $id,reactiva:  $reactiva); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar registro '.$this->tabla,data:  $r_modifica_bd);
        }

        return $r_modifica_bd;
    }




}
