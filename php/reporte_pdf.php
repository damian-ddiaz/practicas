<?php
// damian diaz engelman
include_once '/opt/lampp/htdocs/web/librerias/fpdf186/fpdf.php';

/*
if (class_exists('FPDF')) {
    echo "La librería FPDF se cargó correctamente.";
} else {
    echo "Error al cargar la librería FPDF.";
}*/

$nombre_reporte = 'Reporte de Preuba';
// Crear una instancia de la clase FPDF
$pdf = new FPDF('P', 'mm', 'Letter');

// Agregar una página
$pdf->AddPage();

// Establecer la fuente y el tamaño del texto
$pdf->SetFont('Arial', 'B', 16);

// Escribir el texto "¡Hola, Mundo!" en la página
$pdf->Cell(0, 10, utf8_decode('¡Hola, Mundo Mundo !'), 0, 1, 'C');

// Enviar el PDF al navegador
$pdf->Output($nombre_reporte, 'I');
