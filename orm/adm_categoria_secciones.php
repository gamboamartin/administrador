<?php

namespace gamboamartin\administrador\models;

use base\orm\_modelo_parent;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_categoria_secciones extends _modelo_parent
{
    public function __construct(PDO $link)
    {
        $tabla = 'adm_categoria_secciones';
        $columnas = array($tabla => false, 'adm_seccion' => $tabla, 'adm_categoria_usuario' => $tabla,
            'adm_usuario' => 'adm_categoria_usuario', 'adm_categoria_sistema' => 'adm_categoria_usuario',
            'adm_categoria' => 'adm_categoria_sistema', 'adm_sistema' => 'adm_categoria_sistema');
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
        
        $this->NAMESPACE = __NAMESPACE__;
    }

    public function get_categorias_usuario(string $sistema, string $usuario): array
    {
        $filtro['adm_sistema.descripcion'] = $sistema;
        $filtro['adm_usuario.user'] = $usuario;

        $data = (new adm_categoria_secciones($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error('Error al filtrar secciones del menu categorias del usuario', $data);
        }

        return $data->registros;
    }
}