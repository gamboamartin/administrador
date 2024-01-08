<?php
namespace gamboamartin\administrador\models;

use gamboamartin\errores\errores;
use PDO;

class _init_sistema
{
    private adm_sistema $adm_sistema_modelo;
    private array  $adm_sistemas;
    public function __contruct(array $adm_sistemas, PDO $link): void
    {
        $link->beginTransaction();

        $this->adm_sistema_modelo = new adm_sistema(link: $link);
        $this->adm_sistemas = $adm_sistemas;

        $adm_sistema = $this->adm_sistema();
        if(errores::$error){
            $link->rollBack();
            $error = (new errores())->error(mensaje: 'Error al inicializar adm_sistema',data: $adm_sistema);
            print_r($error);
            exit;
        }
        $link->commit();

    }
    private function adm_sistema(): array|true
    {
        foreach ($this->adm_sistemas as $descripcion){
            $filtro = array();
            $filtro['adm_sistema.descripcion'] = $descripcion;
            $existe = $this->adm_sistema_modelo->existe(filtro: $filtro);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al validar si existe menu',data: $existe);
            }
            if(!$existe){
                $registro = array();
                $registro['descripcion'] = $descripcion;
                $alta_bd = $this->adm_sistema_modelo->alta_registro(registro: $registro);
                if(errores::$error){
                    return (new errores())->error(mensaje: 'Error al insertar registro',data: $alta_bd);
                }
                print_r($alta_bd);
            }
            else{
                echo 'Ya existe registro '.$descripcion;
            }
            echo "<br>";
        }
        return true;

    }

}