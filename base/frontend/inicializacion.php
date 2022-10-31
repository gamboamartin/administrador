<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;


class inicializacion{
    private errores $error;
    private validaciones_directivas $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }



    /**
     * Ajusta los elementos para ser mostrados en lista
     * @version 1.30.14
     * @param array $elementos_lista Registro de tipo elemento lista
     * @return array|stdClass
     */
    private function asigna_datos_campo(array $elementos_lista): array|stdClass
    {

        $campos = array();
        $etiqueta_campos = array();
        foreach ($elementos_lista as $registro){
            if(!is_array($registro)){
                return $this->error->error(mensaje: 'Error $elementos_lista[] debe ser un array',data:  $registro);
            }
            if(!isset($registro['adm_elemento_lista_representacion'])){
                $registro['adm_elemento_lista_representacion'] = '';
            }

            $valida = $this->validacion->valida_elemento_lista_template(registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al validar registro", data: $valida);
            }
            $keys = array('adm_elemento_lista_etiqueta');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al validar registro",data:  $valida);
            }

            $datos_campo = $this->datos_campo(registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar $datos_campo', data: $datos_campo);
            }
            $campos[] = $datos_campo;
            $etiqueta_campos[] = $registro['adm_elemento_lista_etiqueta'];
        }

        $data = new stdClass();
        $data->campos = $campos;
        $data->etiqueta_campos = $etiqueta_campos;

        return $data;
    }

    public function campo_filtro(array $elemento_lista): array|string
    {
        $datas = $this->limpia_datas_minus($elemento_lista);
        if(errores::$error){
            return $this->error->error('Error al limpiar datos', $datas);
        }

        return $datas->seccion . '.' . $datas->campo;
    }
    /**
     * Obtiene los campos para ser mostrados en una lista
     * @version 1.30.14
     * @param array $elementos_lista Registro de tipo elementos para lista
     *
     *
     * @return stdClass|array
     */
    public function campos_lista(array $elementos_lista): stdClass|array{

        $data = $this->asigna_datos_campo(elementos_lista: $elementos_lista);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar $datos',data:  $data);
        }

        return $data;

    }

    /**
     * Asigna datos para el mostrado en la lista de un registro basado en elementos lista
     * @version 1.24.14
     * @param array $registro Registro de tipo elemento lista
     * @return array
     */
    private function datos_campo(array $registro): array
    {
        if(!isset($registro['adm_elemento_lista_representacion'])){
            $registro['adm_elemento_lista_representacion'] = '';
        }
        $valida = $this->validacion->valida_elemento_lista_template($registro);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar registro",data:  $valida);
        }

        $datos_campo['nombre_campo'] = $registro['adm_elemento_lista_descripcion'];
        $datos_campo['tipo'] = $registro['adm_elemento_lista_tipo'];
        $datos_campo['representacion'] = $registro['adm_elemento_lista_representacion'];

        return $datos_campo;
    }

    /**
     * P ORDER P INT
     * @param array $data
     * @param string $key
     * @return string
     */
    private function limpia_minus(array $data, string $key): string
    {
        $txt = $data[$key];
        $txt = trim($txt);
        return strtolower($txt);
    }

    private function limpia_datas_minus(array $elemento_lista): array|stdClass
    {
        $seccion = $this->limpia_minus($elemento_lista, 'adm_seccion_descripcion');
        if(errores::$error){
            return $this->error->error('Error al limpiar txt', $seccion);
        }
        $campo = $this->limpia_minus($elemento_lista, 'adm_elemento_lista_campo');
        if(errores::$error){
            return $this->error->error('Error al limpiar txt', $campo);
        }
        $data = new stdClass();
        $data->seccion = $seccion;
        $data->campo = $campo;
        return $data;

    }





}
