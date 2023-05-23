<?php

namespace gamboamartin\administrador\models;

use base\orm\_modelo_parent;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_categoria_usuario extends _modelo_parent
{
    public function __construct(PDO $link)
    {
        $tabla = 'adm_categoria_usuario';
        $columnas = array($tabla => false, 'adm_usuario' => $tabla, 'adm_categoria_sistema' => $tabla,
            'adm_categoria' => 'adm_categoria_sistema', 'adm_sistema' => 'adm_categoria_sistema');
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }

    public function get_categorias_usuario(string $sistema, string $usuario): array
    {
        $filtro['adm_sistema.descripcion'] = $sistema;
        $filtro['adm_usuario.user'] = $usuario;

        $data = (new adm_categoria_usuario($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error('Error al filtrar menu categorias del usuario', $data);
        }

        return $data->registros;
    }
}