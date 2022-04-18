<?php
namespace base\frontend;

use JetBrains\PhpStorm\Pure;


class modals{

    #[Pure] public function __construct(){

    }
    /**
     *
     * @param string $id_css
     * @param $url_min
     * @return string
     */
    public function modal_base_img(string $id_css, $url_min): string
    {
        return '<div class="modal fade" id="_'.$id_css.'" tabindex="-1" role="dialog" aria-labelledby="'.$id_css.'" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">'.$id_css.'</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-xl-center">
        <img alt="Imagen" class="rounded imagen-modal" src="'.$url_min.'" " >
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';
    }

    private function modal_form(
        string $id_css, string $title, string $content, string $seccion, string $accion, int $registro_id, string $session_id): string
    {
        return '
<form method="POST" action="index.php?seccion='.$seccion.'&accion='.$accion.'&session_id='.$session_id.'&registro_id='.$registro_id.'">
    <div class="modal fade" id="'.$id_css.'" tabindex="-1" role="dialog" aria-labelledby="'.$id_css.'" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">'.$title.'</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                '.$content.'
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</form>
';
    }

}
