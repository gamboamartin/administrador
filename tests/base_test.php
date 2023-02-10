<?php
namespace gamboamartin\administrador\tests;
use base\orm\modelo_base;
use gamboamartin\administrador\models\adm_grupo;
use gamboamartin\administrador\models\adm_tipo_dato;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class base_test{

    public function alta_adm_grupo(PDO $link, string $descripcion = 'admin', int $id = 1): array|stdClass
    {

        $registro['id'] = $id;
        $registro['descripcion'] = $descripcion;

        $alta = (new adm_grupo($link))->alta_registro(registro: $registro);
        if(errores::$error){
            return (new errores())->error('Error al insertar', $alta);
        }

        return $alta;
    }

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

    public function alta_adm_usuario(PDO $link, int $adm_grupo_id = 1, string $ap = 'admin',
                                     string $email = 'admin@test.com', int $id = 2, string $nombre = 'admin',
                                     string $password = 'password', string $telefono = '3333333333',
                                     string $user = 'admin'): array|stdClass
    {

        $existe = (new adm_grupo($link))->existe_by_id(registro_id: $adm_grupo_id);
        if(errores::$error){
            return (new errores())->error('Error al validar', $existe);
        }
        if(!$existe){
            $alta = $this->alta_adm_grupo(link: $link,id: $adm_grupo_id);
            if(errores::$error){
                return (new errores())->error('Error al insertar', $alta);
            }
        }

        $registro['id'] = $id;
        $registro['user'] = $user;
        $registro['password'] = $password;
        $registro['email'] = $email;
        $registro['adm_grupo_id'] = $adm_grupo_id;
        $registro['telefono'] = $telefono;
        $registro['nombre'] = $nombre;
        $registro['ap'] = $ap;
        $alta = (new adm_usuario($link))->alta_registro(registro: $registro);
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

    public function del_adm_usuario(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\administrador\\models\\adm_usuario');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }


}
