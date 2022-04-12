<?php
namespace base;
require 'vendor/autoload.php';
$data = '1500';

$lector = new lector($data);

echo '<img src="'.$lector->qrcode.'" alt="QR Code" />';