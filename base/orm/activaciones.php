<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use models\bitacora;
use models\seccion;
use stdClass;

class activaciones{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    public function init_activa(modelo $modelo, bool $reactiva): array|stdClass
    {
        $name_model = $this->normaliza_name_model(modelo:$modelo);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al normalizar modelo '.$modelo->tabla,data:$name_model);
        }

        $data_activacion = $this->maqueta_activacion(modelo:$modelo, reactiva: $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar datos de activacion '.$modelo->tabla,
                data:$data_activacion);
        }
        $modelo->consulta = $data_activacion->consulta;
        $modelo->transaccion = $data_activacion->transaccion;

        $data_activacion->name_model = $name_model;
        return $data_activacion;
    }

    private function maqueta_activacion(modelo $modelo, bool $reactiva): array|stdClass
    {
        $valida = $this->verifica_reactivacion(modelo:$modelo,reactiva:  $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al validar transaccion activa en '.$modelo->tabla,data:$valida);
        }

        $sql = $this->sql_activa(registro_id:$modelo->registro_id,tabla:  $modelo->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar sql '.$modelo->tabla,data:$valida);
        }

        $data = new stdClass();
        $data->consulta = $sql;
        $data->transaccion = 'ACTIVA';

        return $data;
    }

    private function normaliza_name_model(modelo $modelo): array|string
    {
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        return $modelo->tabla;
    }

    private function sql_activa(int $registro_id, string $tabla): string
    {
        return "UPDATE " . $tabla . " SET status = 'activo' WHERE id = " . $registro_id;
    }

    private function valida_activacion(modelo $modelo): bool|array
    {
        $registro = $modelo->registro(registro_id: $modelo->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al obtener registro '.$modelo->tabla,data:$registro);
        }

        $valida = $modelo->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $modelo->aplica_transaccion_inactivo, registro: $registro,
            registro_id: $modelo->registro_id,tabla:  $modelo->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al validar transaccion activa en '.$modelo->tabla,data:$valida);
        }
        return $valida;
    }

    private function verifica_reactivacion(modelo $modelo,bool $reactiva): bool|array
    {
        $valida = true;
        if(!$reactiva) {
            $valida = $this->valida_activacion(modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al validar transaccion activa en '.$modelo->tabla,data:$valida);
            }
        }
        return $valida;
    }



}
