<?php

namespace base\frontend;

class sidebar
{

    public function print_categorias(array $registros, string $titulo_categoria): string
    {

        $html = '';

        foreach ($registros as $index => $registro) {

            $html .= '<li class="nav-title">'.$registro[$titulo_categoria].'.</li>';

            if (count($registros)-1 > $index){
                $html .= '<div class="menu-divider"></div>';
            }
        }

        return $html;
    }

}