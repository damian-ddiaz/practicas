<?php
// Incluir la librería para generar códigos QR
require_once "/opt/lampp/htdocs/web/librerias/phpqrcode/qrlib.php";

// Incluir la librería FPDF
require_once "/opt/lampp/htdocs/web/librerias/fpdf186/fpdf.php";

// Declarar una carpeta temporal para guardar las imágenes generadas
$dir = 'temp/';

// Verificar si la carpeta existe, si no, crearla
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}

// Parámetros de configuración para generar el código QR
$tamaño = 10; // Tamaño de pixel
$level = 'L'; // Precisión baja
$framSize = 3; // Tamaño en blanco
// $contenido = "http://codigosdeprogramacion.com"; // Texto

$contenido = "https://www.uc3m.es/orientacionyempleo/media/orientacionyempleo/doc/archivo/doc_plantillascv/plantillas-de-cv-para-descargar-gratis.pdf"; // Texto


// Crear el objeto FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Generar el código QR y agregarlo al PDF
$tempFileName = $dir . 'test.png';
QRcode::png($contenido, $tempFileName, $level, $tamaño, $framSize);
$pdf->Image($tempFileName, 10, 10, 40, 40);

// Agregar más contenido al PDF
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Códigos de Programación '), 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(5, 50); //Coordenadas X/Y 
$pdf->MultiCell(0, 15, utf8_decode('Este es un PDF generado con PHP y la librería FPDF, que incluye un código QR.'), 0, 'L', false);

// Guardar el PDF
$pdf->Output('test.pdf', 'D');
