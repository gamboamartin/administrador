<?php
namespace base\controller;
class base_html{
    public function close_btn(): string
    {
        return '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>';
    }
    public function head(string $titulo): string
    {
        $errores_html = '<h4 class="alert-heading">';
        $errores_html .= $titulo;
        $errores_html .= '</h4>';
        return $errores_html;
    }
}
