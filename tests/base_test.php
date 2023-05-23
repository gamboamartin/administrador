<?php
namespace gamboamartin\administrador\tests;
use base\orm\modelo_base;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_grupo;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_namespace;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\administrador\models\adm_tipo_dato;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class base_test{

    public function alta_adm_accion(PDO $link, string $adm_seccion_descripcion = 'adm_seccion', int $adm_seccion_id = 1,
                                    string $descripcion = 'alta', int $id = 1, string $lista = 'inactivo',
                                    string $visible = 'inactivo'): array|stdClass
    {

        $existe = (new adm_seccion($link))->existe_by_id(registro_id: $adm_seccion_id);
        if(errores::$error){
            return (new errores())->error('Error al validar', $existe);
        }
        if(!$existe){
            $alta = $this->alta_adm_seccion(link: $link, descripcion: $adm_seccion_descripcion, id: $adm_seccion_id);
            if(errores::$error){
                return (new errores())->error('Error al insertar', $alta);
            }
        }

        $filtro['adm_seccion.descripcion'] = $adm_seccion_descripcion;
        $filtro['adm_accion.descripcion'] = $descripcion;
        $existe = (new \gamboamartin\administrador\models\adm_accion($link))->existe(filtro: $filtro);
        if(errores::$error){
            return (new errores())->error('Error al validar si existe', $existe);
        }

        if($existe) {
            $del = (new adm_accion($link))->elimina_con_filtro_and(filtro: $filtro);
            if (errores::$error) {
                return (new errores())->error('Error al eliminar', $del);
            }
        }
        $registro['id'] = $id;
        $registro['descripcion'] = $descripcion;
        $registro['adm_seccion_id'] = $adm_seccion_id;
        $registro['lista'] = $lista;
        $registro['visible'] = $visible;

        $alta = (new adm_accion($link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return (new errores())->error('Error al insertar', $alta);
        }

        return $alta;
    }

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

    public function alta_adm_menu(PDO $link, string $descripcion = 'acl', int $id = 1): array|stdClass
    {

        $registro['id'] = $id;
        $registro['descripcion'] = $descripcion;

        $alta = (new adm_menu($link))->alta_registro(registro: $registro);
        if(errores::$error){
            return (new errores())->error('Error al insertar', $alta);
        }

        return $alta;
    }

    public function alta_adm_namespace(PDO $link, string $descripcion = 'administrador', int $id = 1): array|stdClass
    {

        $registro['id'] = $id;
        $registro['descripcion'] = $descripcion;

        $alta = (new adm_namespace($link))->alta_registro(registro: $registro);
        if(errores::$error){
            return (new errores())->error('Error al insertar', $alta);
        }

        return $alta;
    }

    public function alta_adm_seccion(PDO $link, int $adm_menu_id = 1, int $adm_namespace_id = 1,
                                     string $descripcion = 'adm_seccion', int $id = 1): array|stdClass
    {

        $existe = (new adm_menu($link))->existe_by_id(registro_id: $adm_menu_id);
        if(errores::$error){
            return (new errores())->error('Error al validar', $existe);
        }
        if(!$existe){
            $alta = $this->alta_adm_menu(link: $link,id: $adm_menu_id);
            if(errores::$error){
                return (new errores())->error('Error al insertar', $alta);
            }
        }

        $existe = (new adm_namespace($link))->existe_by_id(registro_id: $adm_namespace_id);
        if(errores::$error){
            return (new errores())->error('Error al validar', $existe);
        }
        if(!$existe){
            $alta = $this->alta_adm_namespace(link: $link,id: $adm_namespace_id);
            if(errores::$error){
                return (new errores())->error('Error al insertar', $alta);
            }
        }

        $registro['id'] = $id;
        $registro['descripcion'] = $descripcion;
        $registro['adm_menu_id'] = $adm_menu_id;
        $registro['adm_namespace_id'] = $adm_namespace_id;

        $alta = (new adm_seccion($link))->alta_registro(registro: $registro);
        if(errores::$error){
            return (new errores())->error('Error al insertar', $alta);
        }

        return $alta;
    }

    public function alta_adm_seccion_pertenece(PDO $link, string $adm_seccion_descripcion = 'adm_seccion',
                                               int $adm_seccion_id = 1, int $adm_sistema_id = 1,
                                               int $id = 1): array|stdClass
    {

        $existe = (new adm_seccion($link))->existe_by_id(registro_id: $adm_seccion_id);
        if(errores::$error){
            return (new errores())->error('Error al validar', $existe);
        }
        if(!$existe){
            $alta = $this->alta_adm_seccion(link: $link, descripcion: $adm_seccion_descripcion, id: $adm_seccion_id);
            if(errores::$error){
                return (new errores())->error('Error al insertar', $alta);
            }
        }

        $existe = (new adm_sistema($link))->existe_by_id(registro_id: $adm_sistema_id);
        if(errores::$error){
            return (new errores())->error('Error al validar', $existe);
        }
        if(!$existe){
            $alta = $this->alta_adm_sistema(link: $link,id: $adm_sistema_id);
            if(errores::$error){
                return (new errores())->error('Error al insertar', $alta);
            }
        }

        $registro['id'] = $id;
        $registro['adm_seccion_id'] = $adm_seccion_id;
        $registro['adm_sistema_id'] = $adm_sistema_id;


        $alta = (new adm_seccion_pertenece($link))->alta_registro(registro: $registro);
        if(errores::$error){
            return (new errores())->error('Error al insertar', $alta);
        }

        return $alta;
    }

    public function alta_adm_sistema(PDO $link, string $descripcion = 'administrador', int $id = 1): array|stdClass
    {

        $registro['id'] = $id;
        $registro['descripcion'] = $descripcion;

        $alta = (new adm_sistema($link))->alta_registro(registro: $registro);
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

    public function del_adm_accion(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\administrador\\models\\adm_accion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_adm_menu(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\administrador\\models\\adm_menu');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_adm_namespace(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\administrador\\models\\adm_namespace');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }



    public function del_adm_seccion(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\administrador\\models\\adm_seccion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_adm_sistema(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\administrador\\models\\adm_sistema');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
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
