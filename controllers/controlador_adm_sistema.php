<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use config\google;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\plugins\google_calendar_api;
use PDO;
use stdClass;


class controlador_adm_sistema extends controlador_base {
    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $modelo = new adm_sistema(link: $link);
        parent::__construct(link: $link,modelo:  $modelo,paths_conf: $paths_conf);
    }

    public function google_calendar_redirect(bool $header, bool $ws = false, array $not_actions = array())
    {
        if (!defined('config\google::GOOGLE_CLIENT_ID')) {
            $mensaje = "No existe la propiedad GOOGLE_CLIENT_ID en la clase de configuración 'google'";
            return $this->retorno_error(mensaje: $mensaje, data: $mensaje, header: $header, ws: $ws);
        }

        if(!isset($_POST['link_google_calendar_redirect'])){
            $mensaje = "No se ha enviado el link de redirección a google calendar";
            return $this->retorno_error(mensaje: $mensaje, data: $mensaje, header: $header, ws: $ws);
        }

        $link_google_calendar_redirect = $_POST['link_google_calendar_redirect'];

        $google_oauth_url = (new google_calendar_api())->get_oauth_url(google_client_id: google::GOOGLE_CLIENT_ID,
            google_redirect_uri: $link_google_calendar_redirect);

        header("Location: $google_oauth_url");
    }
}