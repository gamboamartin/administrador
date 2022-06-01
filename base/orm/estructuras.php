<?php
namespace base\orm;
use config\generales;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class estructuras{
    private errores  $error;
    public stdClass $estructura_bd;
    private PDO $link;
    public function __construct(PDO $link){
        $this->error = new errores();
        $this->estructura_bd = new stdClass();
        $this->link = $link;
    }

    private function asigna_dato_estructura(array $campo, array $keys_no_foraneas, string $name_modelo): array|stdClass
    {
        $init = $this->init_estructura_campo(campo: $campo,name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $init);
        }

        $campo_init = $this->inicializa_campo(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo', data: $campo_init);
        }

        $estructura_bd = $this->maqueta_estructura(campo: $campo,campo_init: $campo_init,name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
        }
        return $estructura_bd;
    }


    public function asigna_datos_estructura(): array|stdClass
    {
        $modelos = $this->modelos();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener modelos', data: $modelos);
        }
        $keys_no_foraneas = array('usuario_alta','usuario_update');
        $estructura_bd = $this->genera_estructura(keys_no_foraneas: $keys_no_foraneas, modelos:$modelos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
        }

        $estructura_bd = $this->asigna_foraneas(estructura_bd: $estructura_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
        }


        $this->estructura_bd = $estructura_bd;

        return $estructura_bd;
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

    private function asigna_dato_foranea(stdClass $data, stdClass $estructura_bd, stdClass $foraneas,
                                         string $modelo): stdClass
    {
        $tabla_foranea = $data->tabla_foranea;
        $foraneas->$tabla_foranea = new stdClass();
        $estructura_bd->$modelo->tiene_foraneas = true;
        return $estructura_bd;
    }

    private function asigna_datos_modelo(array $data_table, array $keys_no_foraneas, string $name_modelo): array|stdClass
    {
        $estructura_bd = array();
        foreach ($data_table as $campo){

            $estructura_bd = $this->asigna_dato_estructura(campo: $campo, keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
            }

        }
        return $estructura_bd;
    }

    private function asigna_foraneas(stdClass $estructura_bd): array|stdClass
    {
        $estructura_bd_r = $estructura_bd;
        foreach ($estructura_bd as $modelo=>$estructura){
            $estructura_bd_r = $this->calcula_foranea(estructura: $estructura,estructura_bd: $estructura_bd_r,modelo: $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
            }
        }
        return $estructura_bd_r;
    }

    private function calcula_foranea(stdClass $estructura, stdClass $estructura_bd, string $modelo): array|stdClass
    {
        $estructura_bd_r = $estructura_bd;

        $estructura_bd_r->$modelo->tiene_foraneas = false;
        $data_campos = $estructura->data_campos;
        $foraneas = new stdClass();

        $estructura_bd_r = $this->genera_foranea(data_campos: $data_campos,estructura_bd: $estructura_bd_r,
            foraneas: $foraneas,modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd_r);
        }

        $estructura_bd_r->$modelo->foraneas = $foraneas;
        return $estructura_bd_r;
    }

    private function genera_foranea(stdClass $data_campos, stdClass $estructura_bd, stdClass $foraneas,
                                    string $modelo): array|stdClass
    {
        foreach ($data_campos as $data){
            if($data->es_foranea){
                $estructura_bd = $this->asigna_dato_foranea(data: $data,estructura_bd: $estructura_bd,
                    foraneas: $foraneas,modelo: $modelo);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
                }
            }
        }
        return $estructura_bd;
    }

    private function inicializa_campo(array $campo, array $keys_no_foraneas): array|stdClass
    {
        $permite_null = $this->permite_null(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar permite null', data: $permite_null);
        }
        $es_primaria = $this->es_primaria(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_primaria', data: $es_primaria);
        }
        $es_auto_increment = $this->es_auto_increment(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_auto_increment', data: $es_auto_increment);
        }
        $es_foranea = $this->es_foranea(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_foranea', data: $es_foranea);
        }
        $tabla_foranea = $this->tabla_foranea(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $tabla_foranea', data: $tabla_foranea);
        }

        $data = new stdClass();
        $data->permite_null = $permite_null;
        $data->es_primaria = $es_primaria;
        $data->es_auto_increment = $es_auto_increment;
        $data->es_foranea = $es_foranea;
        $data->tabla_foranea = $tabla_foranea;

        return $data;
    }




    private function es_auto_increment(array $campo): bool
    {
        $es_auto_increment = false;
        if($campo['Extra'] === 'auto_increment'){
            $es_auto_increment = true;
        }
        return $es_auto_increment;
    }

    private function es_foranea(array $campo, array $keys_no_foraneas): bool
    {
        $es_foranea = false;
        $explode_campo = explode('_id', $campo['Field']);

        if((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if(!$es_no_foranea){
                $es_foranea = true;
            }


        }
        return $es_foranea;
    }

    private function es_primaria(array $campo): bool
    {
        $es_primaria = false;
        if($campo['Key'] === 'PRI'){
            $es_primaria = true;
        }
        return $es_primaria;
    }

    private function genera_estructura(array $keys_no_foraneas, array $modelos): array|stdClass
    {
        $estructura_bd = array();
        $modelo_base = new modelo_base($this->link);
        foreach ($modelos as $name_modelo){

            $data_table = $this->init_dato_estructura(modelo_base: $modelo_base,name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $data_table);
            }
            $estructura_bd = $this->asigna_datos_modelo(data_table: $data_table, keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
            }
        }
        return $estructura_bd;
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

    private function init_dato_estructura(modelo_base $modelo_base, string $name_modelo): array
    {
        $data_table = (new columnas())->columnas_bd_native(modelo: $modelo_base, tabla_bd: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos', data: $data_table);
        }

        $init = $this->init_estructura_modelo(name_modelo:$name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $init);
        }

        return $data_table;
    }


    private function init_estructura_campo(array $campo, string $name_modelo): stdClass 
    {
        $campo_name = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name = new stdClass();
        return $this->estructura_bd;
    }

    private function init_estructura_modelo(string $name_modelo): stdClass
    {
        $this->estructura_bd->$name_modelo = new stdClass();
        $this->estructura_bd->$name_modelo->campos = array();
        $this->estructura_bd->$name_modelo->data_campos = new stdClass();
        return $this->estructura_bd;
    }

    private function maqueta_estructura(array $campo, stdClass $campo_init, string $name_modelo): stdClass
    {
        $campo_name = $campo['Field'];

        $this->estructura_bd->$name_modelo->campos[] = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tabla_foranea =  $campo_init->tabla_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_foranea = $campo_init->es_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->permite_null = $campo_init->permite_null;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->campo_name = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tipo_dato = $campo['Type'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_primaria = $campo_init->es_primaria;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->valor_default = $campo['Default'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->extra = $campo['Extra'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_auto_increment = $campo_init->es_auto_increment;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tipo_llave = $campo['Key'];
        return $this->estructura_bd;
    }

    /**
     * Funcion que obtiene todas las tablas de una base de datos del sistema en ejecucion
     * @return array|stdClass
     */
    private function modelos(): array|stdClass
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

    private function permite_null(array $campo): bool
    {
        $permite_null = true;
        if($campo['Null'] === 'NO'){
            $permite_null = false;
        }
        return $permite_null;
    }

    private function tabla_foranea(array $campo, array $keys_no_foraneas): string
    {
        $tabla_foranea = '';
        $explode_campo = explode('_id', $campo['Field']);
        if((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if(!$es_no_foranea){
                $tabla_foranea = $explode_campo[0];
            }


        }
        return $tabla_foranea;
    }

}
