<?php
// Incluir la librería TCPDF
require_once '/opt/lampp/htdocs/web/tcpdf/tcpdf.php';

// Crear una nueva instancia de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Establecer los márgenes del documento
$pdf->SetMargins(15, 15, 15, 15);

// Agregar una nueva página
$pdf->AddPage();

// Establecer el color de fondo (background) en gris
$pdf->SetFillColor(220, 220, 220); // Color gris claro

// Dibujar el recuadro con el color de fondo
$pdf->Rect(5, 40, 30, 30, 'F');

// URL que deseas incluir en el código QR
$url = "http://developer.icarosoft.com:8092/scriptcase/app/webservice/facturacion_ventas_proformas_pdf/";

// Agregar el código QR que apunta al enlace
$pdf->write2DBarcode($url, 'QRCODE,L', 35, 20, 30, 30, '', 'N');

// Agregar el contenido del PDF
$pdf->writeHTML('<h1>Descarga el PDF a través del código QR</h1>', true, false, true, false, '');

// Generar el PDF
$pdf->Output('ejemplo.pdf', 'I');
