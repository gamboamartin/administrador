<?php
namespace base\orm;
use config\generales;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class estructuras{
    private errores  $error;
    private PDO $link;
    public function __construct(PDO $link){
        $this->error = new errores();
        $this->link = $link;
    }

    private function asigna_data_modelo(array $modelos, array $row): array
    {
        $key = $this->key_table();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar key', data: $key);
        }
        $data = $row[$key];
        $modelos[] = $data;
        return $modelos;
    }

    private function get_tables_sql(): array
    {
        $sql = (new sql())->show_tables();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }

        $result = (new modelo_base($this->link))->ejecuta_consulta(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }

        return $result->registros;
    }

    /**
     * Funcion que obtiene todas las tablas de una base de datos del sistema en ejecucion
     * @return array|stdClass
     */
    public function modelos(): array|stdClass
    {

        $rows = $this->get_tables_sql();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $rows);
        }

        $modelos = $this->maqueta_modelos(rows: $rows);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar modelos', data: $modelos);
        }

        return $modelos;

    }

    private function key_table(): string
    {
        $pref = 'Tables_in_';
        return $pref.(new generales())->sistema;
    }

    private function maqueta_modelos(array $rows): array
    {
        $modelos = array();
        foreach ($rows as $row){
            $modelos = $this->asigna_data_modelo(modelos:$modelos,row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar modelo', data: $modelos);
            }
        }
        return $modelos;
    }

}
