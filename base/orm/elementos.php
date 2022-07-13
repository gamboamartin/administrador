<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;

class elementos{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * Obtiene los campos de una tabla para ser mostrados en lista
     * @version 1.93.19
     * @param array $campo Campo a validar elementos
     * @return string|array
     */
    public function campo_tabla_externa(array $campo): string|array
    {
        $keys = array('adm_elemento_lista_campo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar campo", data: $valida);
        }

        $aplica_el_campo = !isset($campo['adm_elemento_lista_campo_tabla_externa'])
            ||(string)$campo['adm_elemento_lista_campo_tabla_externa']==='';

        if($aplica_el_campo){
            $campo_tabla_externa = $campo['adm_elemento_lista_campo'];
        }
        else{
            $campo_tabla_externa = $this->data_campo_tabla_externa(campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al obtener campo tabla externa",data:  $campo_tabla_externa);
            }
        }
        return $campo_tabla_externa;
    }

    /**
     * Carga las columnas de un elemento para select
     * @version 1.96.21
     * @param array $campo Campo a validar elementos
     * @return string
     */
    public function columnas_elemento_lista(array $campo): string
    {
        if(!isset($campo['adm_elemento_lista_columnas']) ||(string)$campo['adm_elemento_lista_columnas']===''){
            $elemento_lista_columnas = '';
        }
        else{
            $elemento_lista_columnas = $campo['adm_elemento_lista_columnas'];
        }
        return $elemento_lista_columnas;
    }

    /**
     * Valida el elemento de tabla externa exista y sea un string
     * @version 1.82.19
     * @param array $campo Campo a validar elementos
     * @return array|string
     */
    private function data_campo_tabla_externa(array $campo): array|string
    {
        $keys = array('adm_elemento_lista_campo_tabla_externa');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar campo", data: $valida);
        }
        return (string)$campo['adm_elemento_lista_campo_tabla_externa'];
    }

    /**
     * Integra el id de tipo css para un input
     * @version 1.132.30
     * @param array $campo Campo a validar elementos
     * @return string
     */
    public function elemento_lista_css_id(array $campo): string
    {
        if(!isset($campo['adm_elemento_lista_css_id']) ||(string)$campo['adm_elemento_lista_css_id']===''){
            $elemento_lista_css_id = '';
        }
        else{
            $elemento_lista_css_id = $campo['adm_elemento_lista_css_id'];
        }
        return $elemento_lista_css_id;
    }

    /**
     *
     * Asigna los elementos para obtener las llaves en forma json para select
     * @version 1.104.25
     * @param array $campo Campo a validar elementos
     * @return string
     */
    public function llaves_valores(array $campo): string
    {
        if(!isset($campo['adm_elemento_lista_llaves_valores']) ||(string)$campo['adm_elemento_lista_llaves_valores']===''){
            $elemento_lista_llaves_valores = '';
        }
        else{
            $elemento_lista_llaves_valores = $campo['adm_elemento_lista_llaves_valores'];
        }
        return $elemento_lista_llaves_valores;
    }

    /**
     * Asigna el pattern para un input
     * @version 1.108.26
     * @param array $campo Campo a validar elementos
     * @return string
     */
    public function pattern(array $campo): string
    {
        if(!isset($campo['adm_elemento_lista_pattern']) ||(string)$campo['adm_elemento_lista_pattern']===''){
            $elemento_lista_pattern = '';
        }
        else{
            $elemento_lista_pattern = $campo['adm_elemento_lista_pattern'];
        }
        return $elemento_lista_pattern;
    }

    /**
     * Asigna el separador puesto entre columnas para se mostrado en select
     * @version 1.110.27
     * @param array $campo Campo a validar elementos
     * @return string
     */
    public function separador_columnas(array $campo): string
    {
        if(!isset($campo['adm_elemento_lista_separador_select_columnas']) ||(string)$campo['adm_elemento_lista_separador_select_columnas']===''){
            $elemento_lista_separador_select_columnas = '';
        }
        else{
            $elemento_lista_separador_select_columnas = $campo['adm_elemento_lista_separador_select_columnas'];
        }
        return $elemento_lista_separador_select_columnas;
    }

    /**
     * Asigna el valor d ela tabla si esta renombrada
     * @version  1.113.28
     * @param array $campo Campo a validar elementos
     * @return string
     */
    public function tabla_ext_renombrada(array $campo): string
    {
        if(!isset($campo['adm_elemento_lista_tabla_externa_renombrada'])
            ||(string)$campo['adm_elemento_lista_tabla_externa_renombrada']===''){
            $elemento_lista_tabla_externa_renombrada = '';
        }
        else{
            $elemento_lista_tabla_externa_renombrada = $campo['adm_elemento_lista_tabla_externa_renombrada'];
        }
        return $elemento_lista_tabla_externa_renombrada;
    }

}
