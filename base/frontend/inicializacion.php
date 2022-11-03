<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;


class inicializacion{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
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
