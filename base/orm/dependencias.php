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
     * P INT P ORDER
     * @param bool $desactiva_dependientes
     * @param array $models_dependientes
     * @param PDO $link
     * @param int $registro_id
     * @param string $tabla
     * @return array
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
     * P INT P ORDER
     * @param PDO $link
     * @param int $parent_id
     * @param string $tabla
     * @param string $tabla_children
     * @return array
     */
    public function data_dependientes(PDO $link, int $parent_id, string $tabla, string $tabla_children): array
    {
        $valida = $this->validacion->valida_name_clase(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla',data: $valida);
        }
        if($parent_id<=0){
            return $this->error->error(mensaje: 'Error $parent_id debe ser mayor a 0',data: $parent_id);
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
     * P INT P ORDER
     * @param string $modelo_dependiente
     * @param PDO $link
     * @param int $registro_id
     * @param string $tabla
     * @return array
     */
    private function elimina_data_modelo(string $modelo_dependiente,PDO $link, int $registro_id, string $tabla): array
    {
        $modelo_dependiente = trim($modelo_dependiente);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje:'Error $this->registro_id debe ser mayor a 0',data:$registro_id);
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
     * P INT P ORDER
     * @param array $models_dependientes
     * @param PDO $link
     * @param int $registro_id
     * @param string $tabla
     * @return array
     */
    private function elimina_data_modelos_dependientes(array $models_dependientes, PDO $link, int $registro_id,
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
     * P INT P ORDER
     * @param modelo $model
     * @param int $parent_id
     * @param string $tabla
     * @return array
     * @throws JsonException
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

}
