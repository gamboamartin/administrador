<?php
namespace gamboamartin\administrador\ctl;


use base\orm\modelo;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class activacion{
    private errores $error;
    private base_modelos $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new base_modelos();
    }

    /**
     * REG
     * Activa un registro en la base de datos validando que la transacción sea permitida.
     *
     * Esta función realiza el proceso de activación de un registro en la base de datos.
     * Se asegura de que el ID del registro sea válido, obtiene el registro asociado,
     * valida que la transacción de activación esté permitida y finalmente activa el registro.
     *
     * ### Funcionamiento:
     * 1. **Valida que `$registro_id` sea mayor a 0.**
     * 2. **Asigna el ID al modelo (`$modelo->registro_id`).**
     * 3. **Obtiene el registro de la base de datos (`$modelo->registro`).**
     * 4. **Verifica que la transacción de activación esté permitida (`valida_transaccion_activa`).**
     * 5. **Ejecuta la activación del registro (`activa_bd`).**
     * 6. **Retorna el resultado de la activación o un error en caso de fallas.**
     *
     * @param modelo $modelo Modelo en ejecución.
     * @param int $registro_id Identificador único del registro a activar.
     * @param string $seccion Nombre de la sección donde se ejecuta la activación.
     *
     * @return array|stdClass Devuelve un objeto con los datos del registro activado o un **array de error** en caso de fallas.
     *
     * @throws errores Si `$registro_id` es inválido, si no se puede obtener el registro,
     * si la transacción de activación no es permitida o si ocurre un error durante la activación.
     *
     * @example **Ejemplo de uso:**
     * ```php
     * $modelo = new modelo();
     * $registro_id = 15;
     * $seccion = "usuarios";
     *
     * $activacion = new activacion();
     * $resultado = $activacion->activa_bd_base(modelo: $modelo, registro_id: $registro_id, seccion: $seccion);
     *
     * print_r($resultado);
     * ```
     *
     * ### **Posibles Salidas:**
     *
     * **Caso 1: Éxito (Registro activado correctamente)**
     * ```php
     * stdClass Object
     * (
     *     [mensaje] => "Registro activado con éxito en usuarios"
     *     [registro_id] => 15
     * )
     * ```
     *
     * **Caso 2: Error (`$registro_id` es inválido)**
     * ```php
     * Array
     * (
     *     [error] => "Error id debe ser mayor a 0"
     *     [data] => -1
     *     [es_final] => true
     * )
     * ```
     *
     * **Caso 3: Error (`modelo->registro` no encuentra el registro)**
     * ```php
     * Array
     * (
     *     [error] => "Error al obtener registro"
     *     [data] => []
     * )
     * ```
     *
     * **Caso 4: Error (`valida_transaccion_activa` impide la activación)**
     * ```php
     * Array
     * (
     *     [error] => "Error al validar transaccion activa"
     *     [data] => false
     * )
     * ```
     *
     * **Caso 5: Error al activar el registro en la base de datos**
     * ```php
     * Array
     * (
     *     [error] => "Error al activar registro en usuarios"
     *     [data] => []
     * )
     * ```
     */
    final public function activa_bd_base(modelo $modelo, int $registro_id, string $seccion): array|stdClass{
        if($registro_id <= 0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0',data: $registro_id, es_final: true);

        }
        $modelo->registro_id = $registro_id;

        $registro = $modelo->registro(registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data: $registro);
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $modelo->aplica_transaccion_inactivo,  registro: $registro,
            registro_id: $registro_id, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar transaccion activa',data: $valida);
        }
        $registro = $modelo->activa_bd();

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al activar registro en '.$seccion,data: $registro);
        }

        return $registro;
    }
}
