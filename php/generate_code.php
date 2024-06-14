<?php
// if (isset($_POST) && !empty($_POST)) {
// include '/opt/lampp/htdocs/web/phpqrcode/qrlib.php';
include_once '/opt/lampp/htdocs/web/phpqrcode/qrlib.php';

if (class_exists('qrlib')) {
    echo "La librería FPDF se cargó correctamente.";
} else {
    echo "Error al cargar la librería FPDF.";
}

$codesDir = "codes/";
$codeFile = date('d-m-Y-h-i-s') . '.png';
QRcode::png($_POST['formData'], $codesDir . $codeFile, $_POST['ecc'], $_POST['size']);
echo '<img class="img-thumbnail" src="' . $codesDir . $codeFile . '" />';
// } else {
//    header('location:./');
// }
