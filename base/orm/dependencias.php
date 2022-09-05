<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use PDO;
use stdClass;

class dependencias{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * PHPUNIT
     * @param string $name_modelo
     * @return string|array
     */
    private function ajusta_modelo_comp(string $name_modelo): string|array
    {
        $name_modelo = trim($name_modelo);
        if($name_modelo === ''){
            return $this->error->error('Error name_modelo no puede venir vacio', $name_modelo);
        }
        $name_modelo = str_replace('models\\','',$name_modelo);
        $name_modelo = 'models\\'.$name_modelo;

        if($name_modelo === 'models\\'){
            return $this->error->error('Error name_modelo no puede venir vacio', $name_modelo);
        }
        return trim($name_modelo);
    }

    /**
     * P INT P ORDER
     * @param bool $desactiva_dependientes Si desactiva busca dependientes
     * @param array $models_dependientes Conjunto de modelos hijos
     * @param PDO $link Conexion a la base de datos
     * @param int $registro_id Registro en ejecucion
     * @param string $tabla Tabla origen
     * @return array
     * @throws JsonException
     */
    public function aplica_eliminacion_dependencias(bool $desactiva_dependientes, PDO $link,array $models_dependientes,
                                                    int $registro_id, string $tabla): array
    {
        $data = array();
        if($desactiva_dependientes) {
            $elimina = $this->elimina_data_modelos_dependientes(
                models_dependientes:$models_dependientes,link: $link,registro_id: $registro_id,
                tabla:$tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar dependiente', data: $elimina);
            }
            $data = $elimina;
        }
        return $data;
    }

    /**
     * Obtiene los dependientes de una tabla
     * @param PDO $link Conexion a la base de datos
     * @param int $parent_id Registro padre
     * @param string $tabla Tabla origen
     * @param string $tabla_children Tabla hija
     * @return array
     * @version 1.400.45
     */
    private function data_dependientes(PDO $link, int $parent_id, string $tabla, string $tabla_children): array
    {
        $valida = $this->validacion->valida_name_clase(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla',data: $valida);
        }
        if($parent_id<=0){
            return $this->error->error(mensaje: 'Error $parent_id debe ser mayor a 0',data: $parent_id);
        }
        $tabla_children = trim($tabla_children);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $tabla_children);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_children',data: $valida);
        }

        $modelo_children = (new modelo_base(link: $link))->genera_modelo(modelo: $tabla_children);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data: $modelo_children);
        }

        $key_id = $tabla.'.id';
        $filtro[$key_id] = $parent_id;

        $result = $modelo_children->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener dependientes',data: $result);
        }
        return $result->registros;
    }

    /**
     * PHPUNIT
     * @param modelo_base $modelo
     * @param string $modelo_dependiente
     * @return array
     * @throws JsonException
     */
    private function desactiva_data_modelo(modelo_base $modelo, string $modelo_dependiente): array
    {
        $modelo_dependiente_ajustado = $this->modelo_dependiente_val(modelo: $modelo, modelo_dependiente: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error('Error al ajustar modelo',$modelo_dependiente_ajustado);
        }

        $modelo_ = $this->model_dependiente(modelo: $modelo, modelo_dependiente: $modelo_dependiente_ajustado);
        if (errores::$error) {
            return $this->error->error('Error al generar modelo', $modelo_);
        }

        $desactiva = $this->desactiva_dependientes($modelo_, $modelo->registro_id, $modelo_->tabla);
        if (errores::$error) {
            return $this->error->error('Error al desactivar dependiente', $desactiva);
        }
        return $desactiva;
    }

    /**
     *
     * @param modelo_base $modelo
     * @return array
     * @throws JsonException
     */
    public function desactiva_data_modelos_dependientes(modelo_base $modelo): array
    {
        $data = array();
        foreach ($modelo->models_dependientes as $dependiente) {
            $desactiva = $this->desactiva_data_modelo(modelo: $modelo,modelo_dependiente:  $dependiente);
            if (errores::$error) {
                return $this->error->error('Error al desactivar dependiente', $desactiva);
            }
            $data[] = $desactiva;
        }
        return $data;
    }

    /**
     * PHPUNIT
     * @param modelo $modelo
     * @param int $parent_id
     * @param string $tabla_dep
     * @return array
     * @throws JsonException
     */
    private function desactiva_dependientes(modelo_base $modelo, int $parent_id, string $tabla_dep): array
    {
        $valida = $this->validacion->valida_name_clase($modelo->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar tabla',$valida);
        }
        if($parent_id<=0){
            return $this->error->error('Error $parent_id debe ser mayor a 0',$parent_id);
        }

        $dependientes = $this->data_dependientes(link: $modelo->link,parent_id: $parent_id,
            tabla: $modelo->tabla, tabla_children: $tabla_dep);
        if(errores::$error){
            return $this->error->error('Error al obtener dependientes',$dependientes);
        }

        $key_dependiente_id = $tabla_dep.'_id';

        $modelo_dep = $modelo->genera_modelo($tabla_dep);
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo_dep);
        }


        $result = array();
        foreach($dependientes as $dependiente){

            $modelo_dep->registro_id = $dependiente[$key_dependiente_id];

            $desactiva_bd = $modelo_dep->desactiva_bd();
            if(errores::$error){
                return $this->error->error('Error al desactivar dependiente',$desactiva_bd);
            }
            $result[] = $desactiva_bd;
        }
        return $result;

    }

    /**
     * Elimina los registros dependientes de un modelo
     * @param string $modelo_dependiente Modelo Hijo
     * @param PDO $link Conexion a la bd
     * @param int $registro_id Registro en proceso
     * @param string $tabla Tabla origen
     * @return array
     * @throws JsonException
     * @version 1.410.47
     */
    private function elimina_data_modelo(string $modelo_dependiente,PDO $link, int $registro_id, string $tabla): array
    {
        $modelo_dependiente = trim($modelo_dependiente);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo dependiente $modelo_dependiente",
                data: $valida);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje:'Error $this->registro_id debe ser mayor a 0',data:$registro_id);
        }

        $valida = $this->validacion->valida_name_clase(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla',data: $valida);
        }

        $modelo = (new modelo_base($link))->genera_modelo(modelo: $modelo_dependiente);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar modelo', data:$modelo);
        }
        $desactiva = $this->elimina_dependientes(model:  $modelo, parent_id: $registro_id,
            tabla: $tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al desactivar dependiente',data: $desactiva);
        }
        return $desactiva;
    }

    /**
     * Elimina los datos de un modelo dependiente
     * @param array $models_dependientes Modelos dependendientes
     * @param PDO $link Conexion a la base de datos
     * @param int $registro_id Registro en ejecucion
     * @param string $tabla Tabla origen
     * @return array
     * @throws JsonException
     *
     */
    PUBLIC function elimina_data_modelos_dependientes(array $models_dependientes, PDO $link, int $registro_id,
                                                       string $tabla): array
    {
        $data = array();
        foreach ($models_dependientes as $dependiente) {
            $dependiente = trim($dependiente);
            $valida = $this->validacion->valida_data_modelo(name_modelo: $dependiente);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
            }
            if($registro_id<=0){
                return $this->error->error(mensaje:'Error $this->registro_id debe ser mayor a 0',
                    data:$registro_id);
            }
            $valida = $this->validacion->valida_name_clase(tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar tabla',data: $valida);
            }

            $desactiva = $this->elimina_data_modelo(modelo_dependiente: $dependiente,
                link: $link,registro_id: $registro_id,tabla: $tabla);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al desactivar dependiente', data:$desactiva);
            }
            $data[] = $desactiva;
        }
        return $data;
    }

    /**
     * Elimina los registros dependientes de un modelo
     * @param modelo $model Modelo en ejecucion
     * @param int $parent_id Id origen
     * @param string $tabla Tabla origen
     * @return array
     * @throws JsonException
     * @version 1.401.45
     */
    private function elimina_dependientes(modelo $model, int $parent_id, string $tabla): array
    {
        $valida = $this->validacion->valida_name_clase(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla',data: $valida);
        }
        if($parent_id<=0){
            return $this->error->error(mensaje:'Error $parent_id debe ser mayor a 0',data: $parent_id);
        }

        $dependientes = $this->data_dependientes(link: $model->link, parent_id: $parent_id,
            tabla: $tabla,tabla_children:  $model->tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener dependientes',data:$dependientes);
        }

        $key_dependiente_id = $model->tabla.'_id';

        $result = array();
        foreach($dependientes as $dependiente){
            $elimina_bd = $model->elimina_bd(id: $dependiente[$key_dependiente_id]);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al desactivar dependiente',data:$elimina_bd);
            }
            $result[] = $elimina_bd;
        }
        return $result;

    }

    private function model_dependiente(modelo_base $modelo, string $modelo_dependiente): modelo_base|array
    {
        $modelo_dependiente_ajustado = $this->modelo_dependiente_val(modelo: $modelo, modelo_dependiente: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error('Error al ajustar modelo',$modelo_dependiente);
        }
        $modelo_ = $modelo->genera_modelo($modelo_dependiente_ajustado);
        if (errores::$error) {
            return $this->error->error('Error al generar modelo', $modelo_);
        }
        return $modelo_;
    }


    private function modelo_dependiente_val(modelo_base $modelo, string $modelo_dependiente): array|string
    {
        $modelo_dependiente_ajustado = $this->ajusta_modelo_comp($modelo_dependiente);
        if(errores::$error ){
            return  $this->error->error('Error al ajustar modelo',$modelo_dependiente);
        }

        $valida = $this->valida_data_desactiva(modelo: $modelo, modelo_dependiente: $modelo_dependiente_ajustado);
        if(errores::$error){
            return $this->error->error('Error al validar modelos',$valida);
        }

        return $modelo_dependiente_ajustado;
    }

    private function valida_data_desactiva(modelo_base $modelo, string $modelo_dependiente): bool|array
    {
        $valida = $this->valida_names_model(modelo_dependiente: $modelo_dependiente,
            tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar modelos',$valida);
        }

        if($modelo->registro_id<=0){
            return $this->error->error('Error $this->registro_id debe ser mayor a 0',$modelo->registro_id);
        }
        return true;
    }

    private function valida_names_model(string $modelo_dependiente, string $tabla): bool|array
    {
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error("Error al validar modelo",$valida);
        }

        $valida = $this->validacion->valida_name_clase(tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al validar tabla',$valida);
        }

        return true;
    }

}
