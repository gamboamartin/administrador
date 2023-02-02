<?php
namespace tests;
use base\orm\modelo_base;
use gamboamartin\administrador\models\adm_tipo_dato;
use gamboamartin\errores\errores;
use PDO;

class base_test{

    public function alta_adm_tipo_dato(PDO $link, int $id = 1, $descripcion = '1'): array|\stdClass
    {

        $registro['id'] = $id;
        $registro['descripcion'] = $descripcion;
        $alta = (new adm_tipo_dato($link))->alta_registro(registro: $registro);
        if(errores::$error){
            return (new errores())->error('Error al insertar', $alta);
        }

        return $alta;
    }

    public function del(PDO $link, string $name_model): array
    {
        $model = (new modelo_base($link))->genera_modelo(modelo: $name_model);
        $del = $model->elimina_todo();
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al eliminar '.$name_model, data: $del);
        }
        return $del;
    }

    public function del_adm_tipo_dato(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\administrador\\models\\adm_tipo_dato');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }


}
