<?php

namespace gamboamartin\administrador\models;

use base\orm\_modelo_parent;
use config\telegram;
use gamboamartin\errores\errores;
use gamboamartin\plugins\telegram_api;
use PDO;
use stdClass;
use validacion\accion;

class adm_calendario extends _modelo_parent
{
    public function __construct(PDO $link)
    {
        $tabla = 'adm_calendario';
        $columnas = array($tabla => false, 'adm_usuario' => $tabla, 'adm_seccion' => $tabla);

        $campos_obligatorios = array('titulo');

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
        $this->validacion = new accion();

        $this->etiqueta = 'Calendario';
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

        $mensaje = "Se ha creado el calendario: <b>" . htmlspecialchars($_POST['titulo']) . "</b>\n";
        $notificacion = $this->enviar_notificacion(mensaje: $mensaje);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al enviar notificación', data: $notificacion);
        }

        return $r_alta_bd;
    }

    public function enviar_notificacion(string $mensaje)
    {
        $usuario = (new adm_usuario($this->link))->registro(registro_id: $_SESSION['usuario_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener usuario', data: $usuario);
        }

        if (!isset($usuario['adm_usuario_id_chat_telegram'])) {
            return "";
        }

        $opciones = [
            'parse_mode' => 'HTML'
        ];

        $enviar_notificacion = (new telegram_api())->enviar_mensaje(bot_token: telegram::TELEGRAM_BOT_TOKEN,
            chat_id: $usuario['adm_usuario_id_chat_telegram'], mensaje: $mensaje, opciones: $opciones);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al enviar notificación', data: $enviar_notificacion);
        }

        return $enviar_notificacion;
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