<?php
include '../config/constantes.php';
$directorio = opendir('./');

$archivos = array();
while ($archivo = readdir($directorio)){
    if (!is_dir($archivo) && ($archivo!=='index.php')) {
        $archivos[] = $archivo;
    }
}

asort($archivos);

foreach($archivos as $archivo){
    if (!is_dir($archivo) && ($archivo!=='index.php')) {
        $liga = '<a href="'.URL_BASE.'services/'.$archivo.'">';
        $liga .= $archivo;
        $liga .= '</a><br><br>';
        echo $liga;
    }
}



