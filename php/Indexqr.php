<?php
//Agregamos la libreria para genera códigos QR
require "/opt/lampp/htdocs/web/librerias/phpqrcode/qrlib.php";

//Declaramos una carpeta temporal para guardar la imagenes generadas
$dir = 'temp/';

//Si no existe la carpeta la creamos
if (!file_exists($dir))
    mkdir($dir);

//Declaramos la ruta y nombre del archivo a generar
$filename = $dir . 'test.png';

//Parametros de Condiguración

$tamaño = 10; //Tamaño de Pixel
$level = 'L'; //Precisión Baja
$framSize = 3; //Tamaño en blanco
$contenido = "http://codigosdeprogramacion.com"; //Texto

//Enviamos los parametros a la Función para generar código QR
QRcode::png($contenido, $filename, $level, $tamaño, $framSize);

//Mostramos la imagen generada
echo '<img src="' . $dir . basename($filename) . '" /><hr/>';
