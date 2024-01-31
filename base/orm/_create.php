<?php
namespace base\orm;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class _create
{
    private errores  $error;
    private validacion  $valida;

    public function __construct()
    {
        $this->error = new errores();
        $this->valida = new validacion();
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * La función _create.atributo_codigo se encarga de modificar el objeto que se le pasa como parámetro,
     * específicamente añadiendo una nueva propiedad 'codigo' a este objeto.
     * Esta nueva propiedad es de tipo stdclass y se le asigna un valor boolean 'true'.
     *
     * @param stdClass $campos Un objeto stdClass. Este objeto se modifica dentro de la función.
     *
     * @return stdClass Devuelve el objeto stdClass modificado. Este objeto ahora contendrá una nueva propiedad 'codigo',
     *                  que es de tipo stdclass y tiene un valor boolean 'true'.
     *
     * @version 15.17.0
     */
    private function atributo_codigo(stdClass $campos): stdClass
    {
        $campos->codigo = new stdClass();
        $campos->codigo->unique = true;
        return $campos;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método se utiliza para establecer un atributo como un entero y añadirlo a un conjunto de campos.
     *
     * @param stdClass $campos El objeto que contiene todos los campos a los que se debe añadir el nuevo atributo.
     * @param string $campo El nombre del nuevo atributo a añadir.
     * @return stdClass|array Retorna $campos con el nuevo atributo añadido o un array de error si el nombre del campo está vacío.
     * @version 15.19.0
     */
    private function atributo_integer(stdClass $campos, string $campo): stdClass|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
        }
        $campos->$campo = new stdClass();
        $campos->$campo->tipo_dato = 'INT';

        return $campos;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método establece el atributo 'status' en un objeto.
     *
     * Este método recibe un objeto, le añade la propiedad 'status' y le asigna el valor 'activo'.
     * Finalmente, el objeto modificado se devuelve.
     *
     * @param stdClass $campos Recibe un objeto al que se le asignará un nuevo atributo 'status'.
     * @return stdClass Retorna el objeto con el nuevo atributo 'status' con el valor por defecto 'activo'.
     *
     * Ejemplo de uso:
     * ```php
     * $obj = new stdClass();
     * $obj = _create->atributo_status($obj);
     * echo $obj->status->default; // Imprime: activo
     * ```
     * @author Martin Gamboa
     * @version 15.18.0
     */
    private function atributo_status(stdClass $campos): stdClass
    {
        $campos->status = new stdClass();
        $campos->status->default = 'activo';
        return $campos;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función toma como argumento un objeto de la clase estándar de PHP (stdClass) que representa los atributos.
     * Luego, establece los atributos iniciales y se asegura de gestionar cualquier error que pueda ocurrir durante
     * este proceso con el método 'error' de 'this->error'.
     *
     * Después de obtener los atributos iniciales, el método procede para obtener los atributos base (atributos_base),
     * usando el método 'atributos_base'. También maneja los errores para esta operación.
     *
     * Finalmente, se comprueba si el atributo es de tipo TIMESTAMP, si es así, se vacía su longitud.
     *
     * @param stdClass $atributos El objeto que se pasa como argumento que contiene la información sobre los atributos.
     * @return array|stdClass Retorna un array o un objeto que contiene los atributos base.
     * @throws errores Levanta una excepción en caso de error
     *
     * @see _create::atributos_iniciales()
     * @see _create::atributos_base()
     * @see errores::error()
     *
     * @version 15.21.0
     */
    private function atributos(stdClass $atributos): array|stdClass
    {
        $atributos_base = $this->atributos_iniciales();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos iniciales',data: $atributos_base);
        }

        $atributos_base = $this->atributos_base(atributos: $atributos,atributos_base:  $atributos_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos_base',data: $atributos_base);
        }

        if($atributos_base->tipo_dato === 'TIMESTAMP'){
            $atributos_base->longitud = '';
        }

        return $atributos_base;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Configura los atributos base del objeto proporcionado, si están presentes en el objeto atributos.
     *
     * @param stdClass $atributos Contiene los atributos a establecer, puede tener las propiedades tipo_dato, longitud y not_null.
     * @param stdClass $atributos_base El objeto objetivo en el que se establecerán los atributos. Sus valores se sobrescriben si existen en $atributos.
     * @return stdClass|array Retorna el objeto atributos_base con los atributos actualizados error al validar.
     * @version 15.14.0
     */
    private function atributos_base(stdClass $atributos, stdClass $atributos_base): stdClass|array
    {
        $keys = array('tipo_dato','longitud','not_null');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $atributos_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al valida atributos_base',data: $valida);
        }

        if(isset($atributos->tipo_dato)){
            $atributos_base->tipo_dato = trim($atributos->tipo_dato);
        }
        if(isset($atributos->longitud)){
            $atributos_base->longitud = trim($atributos->longitud);
        }
        if(isset($atributos->not_null)){
            $atributos_base->not_null = trim($atributos->not_null);
        }


        return $atributos_base;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función añade atributos de fecha a un objeto de campos proporcionado.
     *
     * @param stdClass $campos Objeto que representa los campos de una entidad.
     * Se le añaden dos nuevos campos: fecha_alta y fecha_update.
     *
     * @return stdClass Retorna el objeto de campos con los nuevos atributos de fecha:
     * fecha_alta y fecha_update. Ambos campos son de tipo TIMESTAMP.
     * fecha_alta se inicializa con el TIMESTAMP actual cuando se crea un nuevo registro.
     * fecha_update se actualiza con el TIMESTAMP actual cada vez que se actualiza el registro.
     *
     * @author Martin Gamboa
     * @version 15.32.1
     */
    private function atributos_fecha_base(stdClass $campos): stdClass
    {
        $campos->fecha_alta = new stdClass();
        $campos->fecha_alta->tipo_dato = 'TIMESTAMP';
        $campos->fecha_alta->default = 'CURRENT_TIMESTAMP';

        $campos->fecha_update = new stdClass();
        $campos->fecha_update->tipo_dato = 'TIMESTAMP';
        $campos->fecha_update->default = 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';

        return $campos;

    }

    /**
     * POR DOCUMENTAR WIKI
     * Define los atributos iniciales para el nuevo objeto que será creado.
     * Esta función crea un objeto estándar en PHP y le asigna tres atributos: tipo_dato, longitud y not_null,
     * los cuales son configurados con las cadenas "VARCHAR", "255" y "NOT NULL" respectivamente.
     * Finalmente, la función retorna este objeto.
     * @return stdClass Retorna una nueva instancia de stdClass con los atributos iniciales configurados.
     * @version 13.21.0
     */
    private function atributos_iniciales(): stdClass
    {
        $data = new stdClass();
        $data->tipo_dato = "VARCHAR";
        $data->longitud = "255";
        $data->not_null = "NOT NULL";

        return $data;
    }

    /**
     * POD DOCUMENTAR EN WIKI
     * La función atributos_integer procesa cada elemento de un array, verifica
     * si el campo está vacío y en caso de que no lo esté, invoca la función
     * atributo_integer para cada campo.
     *
     * @param stdClass $campos Un objeto stdClass que representa los campos a procesar.
     * @param array $campos_integer Un array con los nombres de los campos que se procesarán.
     *
     * @return array|stdClass Retorna el objeto $campos con los campos procesados.
     *
     * @version 15.31.1
     */
    private function atributos_integer(stdClass $campos, array $campos_integer): array|stdClass
    {
        foreach ($campos_integer as $campo){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
            }

            $campos = $this->atributo_integer(campos: $campos,campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener '.$campo,data: $campos);
            }
        }
        return $campos;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método genera el SQL necesario para la creación de atributos en base a un objeto atributos dado.
     *
     * @param stdClass $atributos El objeto de entrada que contiene los atributos para la generación de SQL.
     *
     * @return array|stdClass En caso de éxito, devuelve un objeto stdClass que contiene la longitud de los atributos sql y el sql default.
     * Si ocurre un error, devuelve un array con el error detallado.
     *
     * @throws errores Es posible que se lance una excepción si hay un error al obtener los atributos base, longitud sql o default sql.
     *
     * @example
     * $atributos = new stdClass();
     * $atributos->atributo1 = "valor1";
     * $atributos->atributo2 = "valor2";
     * $atributos_sql = $this->_create()->atributos_sql($atributos);
     * @version 16.2.0
     */
    private function atributos_sql(stdClass $atributos): array|stdClass
    {
        $atributos_base = $this->atributos(atributos: $atributos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos_base',data: $atributos_base);
        }

        $longitud_sql = $this->longitud_sql(atributos_base: $atributos_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener longitud_sql',data: $longitud_sql);
        }

        $atributos_base->default = '';
        if(isset($atributos->default)){
            $atributos_base->default = trim($atributos->default);
        }
        $default_sql = $this->default_sql(atributos_base: $atributos_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener default_sql',data: $default_sql);
        }


        $atributos_base->longitud_sql = $longitud_sql;
        $atributos_base->default_sql = $default_sql;

        return $atributos_base;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método se encarga de establecer y preparar los campos base de la base de datos de la aplicación.
     *
     * Primero, el método añade un atributo código único a los campos y se maneja cualquier error
     * que pueda resultar de esta operación. También incluye una descripción a los campos.
     *
     * Posteriormente, el método añade un atributo de estado a los campos,
     * añade atributos enteros como 'usuario_alta_id' y 'usuario_update_id',
     * e incluye también atributos de fecha.
     *
     * Además, añade descripciones seleccionables, alias, un código adicional
     * y un estado predeterminado a los campos.
     *
     * Este método es una parte esencial en la creación de una instancia del objeto,
     * que se encarga de la configuración inicial correspondiente a los campos de la base de datos.
     *
     * @param stdClass $campos Un objeto stdClass que mantiene los atributos y sus valores.
     * @return stdClass|errores Retorna un objeto stdClass que contiene los campos configurados
     *         para la base de datos, o un objeto de errores si hay un problema
     *         durante la configuración de los campos.
     * @throws errores Se lanza una excepción en caso de que haya un error durante la
     *         configuración de los campos de la base de datos.
     * @version 15.35.1
     */
    final public function campos_base(stdClass $campos): stdClass|array
    {

        $campos = $this->atributo_codigo(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener codigo',data: $campos);
        }

        $campos->descripcion = new stdClass();

        $campos = $this->atributo_status(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener status',data: $campos);
        }

        $campos = $this->atributos_integer(campos: $campos,
            campos_integer: array('usuario_alta_id','usuario_update_id'));
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos user',data: $campos);
        }

        $campos = $this->atributos_fecha_base(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos user',data: $campos);
        }

        $campos->descripcion_select = new stdClass();
        $campos->alias = new stdClass();
        $campos->codigo_bis = new stdClass();
        $campos->predeterminado = new stdClass();
        $campos->predeterminado->default = 'inactivo';

        return $campos;

    }
    /**
     * POR DOCUMENTAR EN WIKI
     * Crea una sentencia SQL para un campo específico de acuerdo a los atributos proporcionados
     *
     * @param stdClass $atributos Un objeto con los atributos del campo, incluyendo su tipo de dato y longitud.
     * @param string $campo El nombre del campo para el que se está construyendo la sentencia SQL.
     *
     * @return string|array Regresa la sentencia SQL generada para el campo. En caso de error, devuelve un objeto
     * de error con detalles sobre el problema encontrado.
     * @version 16.5.0
     */
    private function campo_sql(stdClass $atributos, string $campo):string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
        }
        $atributos_base = $this->atributos_sql(atributos: $atributos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener atributos_base',data: $atributos_base);
        }

        $rs = "$campo $atributos_base->tipo_dato $atributos_base->longitud_sql ";
        $rs .= "$atributos_base->not_null $atributos_base->default_sql, ";

        return $rs;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método se utiliza para generar campos SQL para la operación de creación.
     *
     * @param stdClass $campos Una colección de campos para los cuales se debe generar el SQL.
     *
     * @return string|array Los campos SQL generados como una cadena de texto,
     *                      o un arreglo de error si ocurre un error.
     * @version 16.7.0
     */
    private function crea_campos_sql(stdClass $campos):string|array
    {
        $campos_sql = '';
        foreach ($campos as $campo=>$atributos){
            if(!is_object($atributos)){
                return $this->error->error(mensaje: 'Error atributos debe ser un objeto',data: $atributos);
            }
            $campo_sql = $this->campo_sql(atributos: $atributos, campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener campo_sql',data: $campo_sql);
            }
            $campos_sql.=$campo_sql;
        }

        return $campos_sql;

    }
    /**
     * Genera las sentencias SQL de clave foránea para todos los campos proporcionados.
     * Si no se puede generar la sentencia de clave foránea para un campo, este método retorna un mensaje de error.
     *
     * @param stdClass $campos El objeto que contiene los campos y sus atributos.
     * @return string|array Retorna una cadena conteniendo las sentencias SQL de clave foránea para todos los campos,
     *                      o, si ocurre un error, retorna un array con el mensaje y los datos del error.
     */
    private function crea_foreign_keys(stdClass $campos):string|array
    {
        $foreign_keys = '';
        foreach ($campos as $campo=>$atributos){

            $foreign_key = $this->genera_foreign_key(atributos: $atributos,campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener foreign_key',data: $foreign_key);
            }
            $foreign_keys.=$foreign_key;
        }
        return $foreign_keys;
    }


    final public function datos_tabla(stdClass $campos):array|stdClass
    {
        $campos_sql = $this->crea_campos_sql(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos_sql',data: $campos_sql);
        }

        $foreign_keys = $this->crea_foreign_keys(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener foreign_keys',data: $foreign_keys);
        }



        $data = new stdClass();
        $data->campos = $campos_sql;
        $data->foreigns = $foreign_keys;
        $data->campos_por_integrar  = $campos;

        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera una consulta SQL por defecto basada en un conjunto de atributos.
     *
     * @param stdClass $atributos_base Objeto que contiene los atributos para generar la consulta SQL.
     *
     * $atributos_base->tipo_dato se refiere al tipo de datos de la columna en la consulta SQL.
     * $atributos_base->default se refiere al valor por defecto que se debe usar para la columna en la consulta SQL.
     *
     * @return string Devuelve una consulta SQL por defecto como una cadena de texto. Si hay un error,
     * devuelve un array con la información del error.
     * @version 15.84.1
     *
     */
    private function default_sql(stdClass $atributos_base): string
    {
        $default_sql = '';
        if($atributos_base->tipo_dato !== ''){

            if(!isset($atributos_base->default)){
                $atributos_base->default = '';
            }
            if($atributos_base->default !== '') {
                if ($atributos_base->tipo_dato === 'VARCHAR') {
                    $default_sql = "DEFAULT '$atributos_base->default'";
                } elseif ($atributos_base->tipo_dato === 'TIMESTAMP') {
                    $default_sql = "DEFAULT $atributos_base->default";
                }
            }
        }
        return $default_sql;

    }


    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función es para crear una clave foránea en la base de datos.
     *
     * @param string $campo Es el nombre del campo en la tabla que queremos usar como clave foránea.
     * @param string $references Es el nombre de la tabla a la que hace referencia la clave foránea.
     * @return string|array Retorna una cadena que corresponde a la sentencia SQL para la creación de la clave foránea.
     *                      En el caso de un error al intentar generar esta sentencia, la función retornará un array con detalles sobre el error.
     *
     * @version 16.27.0
     */
    private function foreign_key(string $campo, string $references): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
        }
        $references = trim($references);
        if($references === ''){
            return $this->error->error(mensaje: 'Error references esta vacio',data: $references);
        }
        return   "FOREIGN KEY ($campo) REFERENCES $references(id) ON UPDATE RESTRICT ON DELETE RESTRICT";
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera SQL para las claves foráneas de la base de datos.
     *
     * Esta función toma el nombre de un campo y genera SQL para las claves foráneas.
     * Primero, verifica que el campo no esté vacío y de no ser así, emite un error utilizando la clase 'errores'.
     * Luego, intenta obtener las referencias para el campo especificado.
     * Si algún error ocurre durante este proceso, también devuelve un mensaje de 'errores'.
     * Por último, genera la clave foránea utilizando el campo y las referencias obtenidas y retorna los resultados.
     * Nuevamente, si surge algún error en este paso, retorna un mensaje de 'errores'.
     *
     * @param  string $campo Nombre del campo para el cual se generará la SQL de la clave foránea.
     * @return array|string Retorna un string con la SQL generada para la clave foránea o un array con el error en caso de fallar alguno de los pasos.
     * @version 16.32.0
     */
    private function foreign_key_sql(string $campo): array|string
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data: $campo);
        }

        $references = $this->references(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener references',data: $references);
        }

        $foreign_key = $this->foreign_key(campo: $campo,references:  $references);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener foreign_key',data: $foreign_key);
        }
        return $foreign_key;
    }

    /**
     * POR DOCUMENTAR EN WEB
     * Genera la definición de una clave externa (Foreign Key) para una tabla de base de datos,
     * basándose en los atributos proporcionados.
     *
     * @param stdClass $atributos Contiene los atributos y propiedades del campo para el cual se desea generar la
     * clave externa, incluyendo el atributo `foreign_key` que indica si el campo es una clave externa.
     * @param string $campo Es el nombre del campo para el cual se genera la clave externa.
     *
     * @return string|array Retorna la definición SQL de la clave externa si se pudo generar
     * exitosamente, o un error en caso de un problema.
     * @version 16.33.0
     */
    private function genera_foreign_key(stdClass $atributos, string $campo):string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data: $campo);
        }

        $foreign_key = '';
        if(isset($atributos->foreign_key) && $atributos->foreign_key){

            $foreign_key = $this->foreign_key_sql(campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener foreign_key',data: $foreign_key);
            }
        }
        return $foreign_key;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Calcula y retorna la longitud de un cierto atributo base en SQL.
     *
     * @param stdClass $atributos_base Un objeto stdClass que representa los atributos base de un campo de la base de datos.
     *
     * @return string|array Devuelve una cadena de texto que representa la longitud para la declaración SQL.
     *                      Si el tipo de dato es TIMESTAMP, no se precisa la longitud y se devuelve una cadena vacía.
     *
     * @example
     * // Para un tipo de dato VARCHAR y longitud 255
     * $atributos_base = new stdClass();
     * $atributos_base->longitud = '255';
     * $atributos_base->tipo_dato = 'VARCHAR';
     * $longitud_sql = integra_longitud($atributos_base); // Devolverá "(255)"
     *
     * // Para un tipo de dato TIMESTAMP
     * $atributos_base = new stdClass();
     * $atributos_base->tipo_dato = 'TIMESTAMP';
     * $longitud_sql = integra_longitud($atributos_base); // Devolverá ""
     * @version 15.20.0
     */
    private function integra_longitud(stdClass $atributos_base): string|array
    {
        if(!isset($atributos_base->longitud)){
            $atributos_base->longitud = '255';
        }
        $tipo_dato = '';
        if(isset($atributos_base->tipo_dato)){
            $tipo_dato = strtoupper(trim($atributos_base->tipo_dato));
        }
        $longitud_sql = "($atributos_base->longitud)";
        if($tipo_dato === 'TIMESTAMP'){
            $longitud_sql = '';
        }
        return $longitud_sql;
    }

    /**
     * POR DOCUMENTAR WN WIKI
     * Función longitud_sql
     *
     * Esta función es privada y pertenece a la clase _create. Su propósito principal es generar una cadena que
     * representa una longitud SQL basada en los atributos proporcionados.
     *
     * @access private
     * @param stdClass $atributos_base Un objeto de la clase estándar de PHP que se espera que tenga una propiedad
     * llamada "longitud".
     * @return string|array Esta función devuelve una cadena que representa la longitud SQL. Sin embargo,
     * si ocurre un error durante la obtención de la longitud SQL, se devuelve un array con información de error.
     *
     * @throws errores Si ocurre un error durante la obtención de la longitud SQL, la función
     * arroja una excepción con un mensaje explicativo.
     *
     * @uses _create::integra_longitud Para obtener la cadena de longitud basada en los atributos proporcionados.
     *
     * @example
     * $obj = new stdClass();
     * $obj->longitud = '20';
     * $resultado = $this->longitud_sql($obj);
     *
     * @see _create::integra_longitud
     * @version 15.36.1
     */
    private function longitud_sql(stdClass $atributos_base): string|array
    {
        if(!isset($atributos_base->longitud)){
            $atributos_base->longitud = '255';
        }

        $longitud_sql = '';
        if($atributos_base->longitud !== ''){
            $longitud_sql = $this->integra_longitud(atributos_base: $atributos_base);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener longitud_sql',data: $longitud_sql);
            }
        }

        return $longitud_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función privada se encarga de procesar una referencia.
     *
     * @param string $campo Representa un string que se va a procesar.
     *
     * @return string|array Devuelve un array si el campo está vacío y una cadena recortada en caso contrario.
     *
     * @throws errores si el campo es vacío, lanza un error con un mensaje y el dato del campo.
     * @version 16.15.0
     */
    private function references(string $campo): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data: $campo);
        }
        $explode_ref = explode('_id', $campo);

        return reset($explode_ref);
    }

    /**
     * Genera una sentencia SQL para crear una tabla con los campos y claves foráneas especificados.
     *
     * @param stdClass $datos_tabla Objeto que contiene los datos de los campos y las claves foráneas.
     * @param string $table Nombre de la tabla que se creará.
     * @return string Sentencia de creación de tabla en SQL.
     */
    final public function table(stdClass $datos_tabla, string $table): string
    {
        $coma_key = '';
        if($datos_tabla->foreigns!==''){
            $coma_key = ', ';
        }

        return "CREATE TABLE $table (
                    id bigint NOT NULL AUTO_INCREMENT,
                    $datos_tabla->campos
                    PRIMARY KEY (id) $coma_key
                   $datos_tabla->foreigns
                    );";


    }


}
