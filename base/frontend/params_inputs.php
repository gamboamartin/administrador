<?php
namespace base\frontend;

use gamboamartin\errores\errores;
use stdClass;

/**
 * Clase `params_inputs`
 *
 * Esta clase proporciona métodos utilitarios para generar atributos HTML dinámicos destinados a inputs y elementos
 * de formularios en general. Su enfoque está en simplificar la integración de parámetros como `class`, `id`,
 * `required`, `disabled`, `multiple`, `pattern`, `title`, y configuraciones estándar para inputs tipo `radio` o `checkbox`.
 *
 * Todos los métodos implementan validaciones básicas para asegurar consistencia en los datos utilizados en los
 * formularios del frontend. En caso de error, retornan estructuras compatibles con la clase `errores`, la cual
 * centraliza el manejo de errores.
 *
 * @package base\frontend
 * @author Gamboa
 * @version 1.0.0
 * @since 2025-03-21
 *
 * @example Uso básico
 * ```php
 * use base\frontend\params_inputs;
 *
 * $inputs = new params_inputs();
 *
 * echo "<input type='text' "
 *      . $inputs->class_html(['form-control', 'text-primary']) . " "
 *      . $inputs->ids_html(['input-nombre']) . " "
 *      . $inputs->required_html(true) . ">";
 * ```
 *
 * @example Generar parámetros para input tipo checkbox:
 * ```php
 * $params = $inputs->params_base_chk('activo', '¿Activo?');
 * if (!is_array($params)) {
 *     echo "<input type='checkbox' class='" . implode(' ', $params->class_radio) . "' "
 *          . "id='" . implode(' ', $params->ids_css) . "' "
 *          . "name='" . $params->name . "'>";
 *     echo "<label class='" . implode(' ', $params->class_label) . "' for='" . $params->for . "'>"
 *          . $params->label_html . "</label>";
 * }
 * ```
 *
 * Esta clase es ideal para la generación dinámica de formularios con estándares de accesibilidad,
 * consistencia visual y validación mínima integrada.
 */
class params_inputs{


    /**
     * Instancia de la clase `errores` para el manejo interno de errores.
     *
     * Esta propiedad se utiliza en todos los métodos para devolver errores estructurados
     * cuando las validaciones no se cumplen. Permite unificar el formato de errores en la clase
     * y facilita el rastreo de problemas durante la generación de HTML dinámico.
     *
     * @var errores
     */
    private errores $error;



    /**
     * Constructor de la clase `params_inputs`.
     *
     * Inicializa internamente una instancia de la clase `errores`, la cual se utiliza para
     * gestionar errores en los distintos métodos de generación de parámetros HTML.
     *
     * @example Uso básico
     * ```php
     * use base\frontend\params_inputs;
     *
     * $inputs = new params_inputs();
     * ```
     */
    public function __construct()
    {
        $this->error = new errores();
    }

    /**
     * REG
     * Genera una cadena HTML con la lista de clases CSS proporcionadas.
     *
     * Valida que las clases no estén vacías y las concatena en una cadena con el formato `class="clase1 clase2"`.
     * Si una clase dentro del array está vacía, devuelve un error.
     *
     * @version 11.10.0
     * @stable true
     *
     * @param array $class_css Lista de clases CSS a integrar en el atributo `class` del HTML.
     * @return string|array Devuelve una cadena con las clases formateadas o un array de error si alguna clase es inválida.
     *
     * @example
     * ```php
     * $obj = new params_inputs();
     * echo $obj->class_html(['btn', 'btn-primary']);  // Salida: class='btn btn-primary'
     * echo $obj->class_html([]);  // Salida: (cadena vacía)
     * ```
     */
    final public function class_html(array $class_css): string|array
    {
        $class_html = '';
        foreach ($class_css as $class){
            $class = trim($class);
            if($class === ''){
                return $this->error->error(mensaje: 'Error class vacio', data: $class);
            }
            $class_html .= " $class ";
        }
        $class_html = trim($class_html);
        if($class_html !== ''){
            $class_html = "class='$class_html'";
        }
        return $class_html;
    }


    /**
     * REG
     * Genera el atributo `disabled` en HTML si el valor proporcionado es `true`.
     *
     * Si `$disabled` es `true`, retorna la cadena `"disabled"`, lo que hace que el elemento HTML sea deshabilitado.
     * Si `$disabled` es `false`, retorna una cadena vacía, permitiendo que el elemento siga habilitado.
     *
     * @version 1.0.0
     * @stable true
     *
     * @param bool $disabled Determina si el atributo `disabled` debe incluirse en el HTML.
     * @return string Retorna `"disabled"` si `$disabled` es `true`, o una cadena vacía `""` si es `false`.
     *
     * @example
     * ```php
     * $obj = new params_inputs();
     * echo "<input type='text' " . $obj->disabled_html(true) . ">";
     * ```
     * **Salida esperada:** `<input type='text' disabled>`
     *
     * ```php
     * echo "<input type='text' " . $obj->disabled_html(false) . ">";
     * ```
     * **Salida esperada:** `<input type='text'>`
     */
    final public function disabled_html(bool $disabled): string
    {
        $disabled_html = '';
        if ($disabled) {
            $disabled_html = 'disabled';
        }
        return $disabled_html;
    }


    /**
     * REG
     * Genera el atributo `id` en formato HTML a partir de un arreglo de identificadores CSS.
     *
     * Este método construye el atributo `id` concatenando todos los identificadores proporcionados en el arreglo.
     * Si alguno de los elementos del arreglo está vacío, retorna un error.
     * Si el arreglo está vacío, retorna una cadena vacía.
     *
     * @version 1.0.0
     * @stable true
     *
     * @param array $ids_css Arreglo de identificadores CSS que se usarán para formar el atributo `id` en HTML.
     *                       Cada valor debe ser una cadena no vacía.
     *
     * @return string|array Devuelve una cadena con el atributo `id='...'` si todos los valores son válidos.
     *                      Si hay un identificador vacío, devuelve un array de error con mensaje y datos asociados.
     *
     * @example Uso correcto:
     * ```php
     * $obj = new params_inputs();
     * echo $obj->ids_html(['input-user', 'form-control']);
     * // Salida: id='input-user form-control'
     * ```
     *
     * @example Uso con identificador vacío:
     * ```php
     * echo $obj->ids_html(['input-user', '']);
     * // Salida (error): [
     * //     'mensaje' => 'Error id_css vacio',
     * //     'data' => '',
     * //     'es_final' => true,
     * //     ...
     * // ]
     * ```
     *
     * @example Uso con arreglo vacío:
     * ```php
     * echo $obj->ids_html([]);
     * // Salida: (cadena vacía)
     * ```
     */
    final public function ids_html(array $ids_css): string|array
    {
        $id_html = '';
        foreach ($ids_css as $id_css){
            $id_css = trim($id_css);
            if($id_css === ''){
                return $this->error->error(mensaje: 'Error id_css vacio',data:  $id_css, es_final: true);
            }
            $id_html.=" $id_css ";
        }
        $id_html = trim($id_html);
        if($id_html!==''){
            $id_html = "id='$id_html'";
        }
        return $id_html;
    }


    /**
     * REG
     * Genera los parámetros base para un input de tipo radio o checkbox.
     *
     * Este método crea una estructura estándar con clases CSS, atributos de accesibilidad
     * y etiquetas, necesarios para renderizar correctamente un campo `radio` o `checkbox` en HTML.
     *
     * Si el parámetro `$tag` está vacío, se genera automáticamente a partir del `$campo`,
     * reemplazando guiones bajos por espacios y capitalizando las palabras.
     *
     * @version 11.9.0
     * @stable true
     *
     * @param string $campo Nombre del campo, también utilizado como identificador y parte del nombre del input.
     *                      Ejemplo: `'estatus_activo'`
     * @param string $tag Texto que se mostrará como etiqueta (`label`) del input. Si está vacío, se genera automáticamente.
     *                    Ejemplo: `'¿Activo?'`
     *
     * @return stdClass|array Retorna un objeto con los siguientes atributos:
     * - `class_label` (array): Clases CSS para el label.
     * - `class_radio` (array): Clases CSS para el input tipo radio.
     * - `for` (string): Atributo `for` del label.
     * - `ids_css` (array): Lista de identificadores CSS.
     * - `label_html` (string): Texto para el label.
     * - `title` (string): Título o tooltip.
     * - `name` (string): Nombre del campo.
     *
     * Si ocurre un error (por ejemplo, si `$campo` está vacío), se devuelve un array con información del error.
     *
     * @example Generación básica:
     * ```php
     * $obj = new params_inputs();
     * $resultado = $obj->params_base_chk('activo', '¿Activo?');
     *
     * print_r($resultado);
     * // Salida:
     * // stdClass Object
     * // (
     * //     [class_label] => ['form-check-label', 'chk']
     * //     [class_radio] => ['form-check-input', 'activo']
     * //     [for] => ¿Activo?
     * //     [ids_css] => ['activo']
     * //     [label_html] => ¿Activo?
     * //     [title] => ¿Activo?
     * //     [name] => activo
     * // )
     * ```
     *
     * @example Sin `tag` (se genera automáticamente):
     * ```php
     * $resultado = $obj->params_base_chk('estatus_activo', '');
     * // `tag` será: 'Estatus Activo'
     * ```
     */
    final public function params_base_chk(string $campo, string $tag): stdClass|array{

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data:  $campo, es_final: true);
        }

        $tag = trim($tag);
        if($tag === ''){
            $tag = $campo;
            $tag = str_replace('_', ' ', $tag);
            $tag = ucwords($tag);
        }
        $tag = trim($tag);

        if($tag === '' ){
            return $this->error->error(mensaje: 'Error tag vacio',data:  $tag);
        }

        $class_label[] = 'form-check-label';
        $class_label[] = 'chk';

        $class_radio[] = 'form-check-input';
        $class_radio[] = $campo;

        $for = $tag;
        
        $ids_css[] = $campo;

        $label_html = $tag;
        $title = $tag;

        $data = new stdClass();

        $data->class_label = $class_label;
        $data->class_radio = $class_radio;
        $data->for = $for;
        $data->ids_css = $ids_css;
        $data->label_html = $label_html;
        $data->title = $title;
        $data->name = $campo;

        return $data;
    }


    /**
     * REG
     * Genera el atributo `pattern` en formato HTML a partir de una expresión regular proporcionada.
     *
     * Este método permite integrar validación por expresión regular directamente en un input HTML.
     * Si el valor de `$regex` no está vacío, se construye el atributo `pattern='...'`.
     * Si `$regex` está vacío, se devuelve una cadena vacía.
     *
     * @version 1.0.0
     * @stable true
     *
     * @param string $regex Expresión regular que se desea aplicar como patrón de validación en un input HTML.
     *                      Debe estar en formato válido para el atributo `pattern` de HTML5.
     *
     * @return string Devuelve una cadena con el atributo `pattern='...'` si `$regex` tiene contenido.
     *                Si `$regex` está vacío, devuelve una cadena vacía.
     *
     * @example Uso con expresión regular válida:
     * ```php
     * $obj = new params_inputs();
     * echo $obj->regex_html("[A-Za-z]{3,}");
     * // Salida: pattern='[A-Za-z]{3,}'
     * ```
     *
     * @example Uso con cadena vacía:
     * ```php
     * echo $obj->regex_html("");
     * // Salida: (cadena vacía)
     * ```
     */
    final public function regex_html(string $regex): string
    {
        $regex_html = '';
        if($regex){
            $regex_html = "pattern='$regex'";
        }
        return $regex_html;
    }



    /**
     * REG
     * Genera el atributo `required` en formato HTML para ser integrado en un input.
     *
     * @version 1.87.19
     * @stable true
     *
     * @param bool $required Indica si el input debe ser requerido (`true`) o no (`false`).
     * @return string Devuelve `'required'` si `$required` es `true`, de lo contrario, devuelve una cadena vacía.
     *
     * @example
     * ```php
     * $obj = new params_inputs();
     * echo $obj->required_html(true);  // Salida: required
     * echo $obj->required_html(false); // Salida: (cadena vacía)
     * ```
     */
    final public function required_html(bool $required): string
    {
        return $required ? 'required' : '';
    }


    /**
     * REG
     * Genera el atributo `multiple` en formato HTML para ser utilizado en inputs como select o file.
     *
     * Este método permite agregar el atributo `multiple` en elementos HTML que admiten múltiples valores,
     * como `<select multiple>` o `<input type="file" multiple>`. Si el parámetro `$multiple` es verdadero,
     * se retornará la cadena `"multiple"`, de lo contrario se retornará una cadena vacía.
     *
     * @version 1.0.0
     * @stable true
     *
     * @param bool $multiple Define si el input debe aceptar múltiples valores (`true`) o no (`false`).
     *                       - `true`: Se retorna el atributo `multiple`
     *                       - `false`: Se retorna una cadena vacía
     *
     * @return string Retorna la cadena `"multiple"` si `$multiple` es verdadero, de lo contrario una cadena vacía.
     *
     * @example Uso con select:
     * ```php
     * $obj = new params_inputs();
     * echo "<select name='opciones[]' " . $obj->multiple_html(true) . ">";
     * // Salida: <select name='opciones[]' multiple>
     * ```
     *
     * @example Uso con input file:
     * ```php
     * echo "<input type='file' name='archivos[]' " . $obj->multiple_html(true) . ">";
     * // Salida: <input type='file' name='archivos[]' multiple>
     * ```
     *
     * @example Si se pasa `false`:
     * ```php
     * echo "<select name='opciones[]' " . $obj->multiple_html(false) . ">";
     * // Salida: <select name='opciones[]'>
     * ```
     */
    final public function multiple_html(bool $multiple): string
    {
        $multiple_html = '';
        if ($multiple) {
            $multiple_html = 'multiple';
        }
        return $multiple_html;
    }


    /**
     * REG
     * Genera el atributo `title` en formato HTML para un elemento de formulario.
     *
     * Este método valida y construye el atributo `title`, que se utiliza como texto emergente (tooltip)
     * cuando el usuario pasa el cursor sobre el elemento. Si el título proporcionado está vacío,
     * se utiliza el texto del `place_holder` como valor alternativo.
     *
     * @version 1.0.0
     * @stable true
     *
     * @param string $place_holder Texto alternativo que se usará como `title` si el título está vacío.
     *                              Ejemplo: `'Ingrese su nombre completo'`
     * @param string $title Texto que se mostrará como tooltip del campo.
     *                      Ejemplo: `'Nombre completo del usuario'`
     *
     * @return string Devuelve una cadena con el formato `title='valor'` o una cadena vacía si no hay texto válido.
     *
     * @example Título personalizado definido:
     * ```php
     * $obj = new params_inputs();
     * echo $obj->title_html('Ingrese su nombre completo', 'Nombre completo del usuario');
     * // Salida esperada: title='Nombre completo del usuario'
     * ```
     *
     * @example Título vacío, se usa el `place_holder`:
     * ```php
     * echo $obj->title_html('Ingrese su nombre completo', '');
     * // Salida esperada: title='Ingrese su nombre completo'
     * ```
     *
     * @example Ambos vacíos:
     * ```php
     * echo $obj->title_html('', '');
     * // Salida esperada: (cadena vacía)
     * ```
     */
    public function title_html(string $place_holder, string $title): string
    {
        $title = trim($title);
        if($title === ''){
            $title = $place_holder;
        }

        $title_html = '';
        if($title !== ''){
            $title_html = "title='$title'";
        }
        return $title_html;
    }

}
