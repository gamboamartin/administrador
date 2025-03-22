<?php
namespace base\frontend;



use gamboamartin\errores\errores;
use stdClass;

class params_inputs{
    private errores $error;

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
     * Obtiene los parametros base para un input de tipo radio
     * @param string $campo Campo a integrar
     * @param string $tag Tag de input
     * @return stdClass|array
     * @version 11.9.0
     */
    final public function params_base_chk(string $campo, string $tag): stdClass|array{

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data:  $campo);
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
     * Integra un regex a un pattern input
     * @param string $regex
     * @return string
     */
    public function regex_html(string $regex): string
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
     * @param string $place_holder
     * @param string $title
     * @return string
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
