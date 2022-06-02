<?php
namespace base\orm;
use gamboamartin\errores\errores;

class sql{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Genera sql DESCRIBE nombre_table
     * @param string $tabla Nombre de la tabla a verificar
     * @return string|array Sql a ejecutar
     * @version 1.12.8
     */
    public function describe_table(string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        return "DESCRIBE $tabla";
    }

    public function show_tables(): string
    {
        return "SHOW TABLES";
    }

}
