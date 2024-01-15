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
    private modelo_base $modelo;

    public function __construct(PDO $link)
    {
        $this->error = new errores();
        $this->modelo = new modelo_base(link: $link);

    }

    final public function foreign_key_existente(string $relacion_table, string $table)
    {
        $sql = (new sql())->foreign_key(table: $table,relacion_table:  $relacion_table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    final public function create_table(stdClass $campos, string $table):array|stdClass
    {
        $sql = (new sql())->create_table(campos: $campos,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }


        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    final public function drop_table(string $table):array|stdClass
    {
        $sql = (new sql())->drop_table(table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

}
