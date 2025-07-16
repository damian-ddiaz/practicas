<?php
// Incluir la librería TCPDF
// require_once '/opt/lampp/htdocs/web/tcpdf/tcpdf.php';
// Incluir la librería Endroid QR Code
// require_once '/opt/lampp/htdocs/web/endroid/qrcode/src/QrCode/QrCodeInterface.php';
require_once '/home/icarosoft02/web/practicas/Librerias/phpqrcode/qrcode.php';

if (class_exists('qrcode')) {
    echo "La librería FPDF se cargó correctamente.";
} else {
    echo "Error al cargar la librería FPDF.";
}
// Crear una nueva instancia de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Establecer los márgenes del documento
$pdf->SetMargins(15, 15, 15, 15);

// Agregar una nueva página
$pdf->AddPage();

// Establecer el color de fondo (background) en gris
$pdf->SetFillColor(220, 220, 220); // Color gris claro

// Dibujar el recuadro con el color de fondo
$pdf->Rect(5, 20, 20, 20, 'F');

// URL que deseas incluir en el código QR
$url = "http://developer.icarosoft.com:8092/scriptcase/app/webservice/facturacion_ventas_proformas_pdf/";

// Generar el código QR con Endroid QR Code
$qrCode = new \Endroid\QrCode\QrCode($url);
$qrCode->setSize(100);
$qrCode->setMargin(10);
$qrImageData = $qrCode->writeString();

// Insertar el código QR en el PDF
$pdf->Image('@' . $qrImageData, 35, 20, 30, 30, 'PNG', $url, 'N', true, 300, '', false, false, 0, false, false, false);

// Agregar el contenido del PDF
$pdf->writeHTML('<h1>Descarga el PDF a través del código QR</h1>', true, false, true, false, '');

// Generar el PDF
$pdf->Output('ejemplo.pdf', 'I');