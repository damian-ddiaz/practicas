<?php
//* llamando una libreria
include_once '/opt/lampp/htdocs/web/fpdf186/fpdf.php';
// verificando si se cargo la libreria 
if (class_exists('FPDF')) {
	echo "La librería FPDF se cargó correctamente.";
} else {
	echo "Error al cargar la librería FPDF.";
}
