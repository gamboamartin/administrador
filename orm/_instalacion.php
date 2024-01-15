<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo_base;
use base\orm\sql;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class _instalacion
{
    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function create_table(stdClass $campos, PDO $link, string $table):array|stdClass
    {
        $sql = (new sql())->create_table(campos: $campos,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }

        $modelo = new modelo_base(link: $link);

        $exe = $modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    final public function drop_table(PDO $link, string $table):array|stdClass
    {
        $sql = (new sql())->drop_table(table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $modelo = new modelo_base(link: $link);

        $exe = $modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

}
