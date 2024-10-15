<?php

namespace gamboamartin\administrador\models;

use base\orm\_modelo_parent;
use gamboamartin\errores\errores;
use PDO;
use stdClass;
use validacion\accion;

class adm_evento extends _modelo_parent
{
    public function __construct(PDO $link)
    {
        $tabla = 'adm_evento';
        $columnas = array($tabla => false, 'adm_calendario' => $tabla, 'adm_tipo_evento' => $tabla);

        $campos_obligatorios = array('titulo');

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
        $this->validacion = new accion();

        $this->etiqueta = 'Evento';
    }

    public function alta_bd(array  $keys_integra_ds = array('codigo','descripcion')): array|stdClass
    {
        $this->registro = $this->inicializa_campos($this->registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar campo base', data: $this->registro);
        }

        $r_alta_bd = parent::alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta calendario', data: $r_alta_bd);
        }

        $mensaje = "Se ha creado el evento: <b>" . htmlspecialchars($_POST['titulo']) . "</b>\n";
        $mensaje .= "Fecha y hora de inicio: <b>" . htmlspecialchars($_POST['fecha_inicio']) . "</b>\n";
        $mensaje .= "Fecha y hora de finalización: <b>" . htmlspecialchars($_POST['fecha_fin']) . "</b>";
        $notificacion = (new adm_calendario($this->link))->enviar_notificacion(mensaje: $mensaje);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al enviar notificación', data: $notificacion);
        }

        return $r_alta_bd;
    }

    protected function inicializa_campos(array $registros): array
    {
        $registros['codigo'] = $this->get_codigo_aleatorio();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error generar codigo', data: $registros);
        }

        if (!isset($registros['descripcion'])) {
            $registros['descripcion'] = $registros['titulo'];
        }

        return $registros;
    }

}