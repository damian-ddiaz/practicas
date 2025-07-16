//     REPLACE(direccion, ' ', '\n') AS direccion,
sc_include_library("sys", "fpdf", "fpdf.php", true, true);

$nombre_reporte = 'Conversacion desde Helpdesk';
$nombre_modulo = 'Conversacion desde Helpdesk';

$sql_empresa ="SELECT
	descripcion as empresa,
	numero_identificacion,
	CONCAT_WS('',
    UPPER(SUBSTRING(direccion, 1, 1)),
    LOWER(SUBSTRING(direccion, 2))
  	)  as direccion,
    telefono
FROM
	configuracion_empresa 
WHERE
	codigo = '[usr_empresa]'";
sc_lookup_field(rs_empresa, $sql_empresa,"conn_example");

$sql ="SELECT
    id_resumen,
    numero_perfil,
    numero_cliente,
    nombre_cliente,
    empresa,
    sucursal,
    id_cliente
FROM
    ws_conversacion_resumen
WHERE  
id_resumen = '[par_id_resumen]'";
sc_lookup_field(rs, $sql,"conn_helpdesk"); 
//$id_proveedor = {rs[0]['id_proveedor']};

// Crear un nuevo objeto PDF
$pdf = new FPDF('P', 'mm', 'Letter');
// Agregar una página al PDF
$pdf->AddPage();

// Agregando el logo de la Empresa 
$nombre_archivo = logo_emp([usr_empresa]);
if(!empty($nombre_archivo)){
	// Mostrar la imagen en el PDF
	$pdf->Image($nombre_archivo, 5, 3, 40, 7);
}
// Eliminar el archivo temporal
unlink($nombre_archivo);
// Buscando Imagen de la Empresa
// Asignando valores al formato de la fuente a variables
$pdf->Rect(0,0, $pdf->GetPageWidth(), $pdf->GetPageHeight());

$font_nom = 'Arial';
$font_tam = 9;
$font_atl= 3;

$line_ini= 13;
$line_salto = 3;
$pdf->SetFont($font_nom, '', 8);
//Datos de la Empresa
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(100, $font_atl, 'Empresa: '.{rs_empresa[0]['empresa']}, 0,'L');
$pdf->SetXY(110, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(100, $font_atl, 'Nombre: '.utf8_decode({rs[0]['nombre_cliente']}), 0,'L');

$line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(60, $font_atl, 'Sucursal: '.{rs_empresa[0]['telefono']}, 0,'L');
$pdf->SetXY(110, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(60, $font_atl, 'Numero: '.utf8_decode({rs[0]['numero_cliente']}), 0,'L');
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(80,$font_atl, utf8_decode('Id Chat: '.{rs[0]['id_resumen']}), $font_atl,'L'); 
$pdf->SetXY(40, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(60, $font_atl, 'Nro. Receptor: '.{rs[0]['numero_perfil']}, 0,'L');
$pdf->SetXY(110, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(60, $font_atl, 'Cliente: '.utf8_decode({rs[0]['nombre_cliente']}), 0,'L');
// $pdf->Line(5, 50, 250, 50); // (x1, y1, x2, y2) Dibujando Linea

//Encabezado
$pdf->SetFont($font_nom,'B', 9);
$line_salto = 4;
$line_ini = 5;
$font_atl= 0;
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(200,$font_atl, utf8_decode($nombre_modulo), $font_atl,'C');
$pdf->SetFont($font_nom, '', 8);

// Detalle 
$line_ini = $line_ini + 20; // Salto de Bloque
$pdf->SetXY(5, $line_ini);
$font_tam = 8;
$pdf->SetFont($font_nom, 'B', $font_tam);
$pdf->SetFillColor(146, 140, 139);
$pdf->MultiCell(57,4, utf8_decode('Usuario Fecha-Hora '), 1,'C',true); 
$pdf->SetXY(62,$line_ini);
$pdf->SetFont($font_nom, 'B', $font_tam);
$pdf->MultiCell(152,4, utf8_decode('Mensaje'), 1,'C',true); 	

// Leyendo Detalle
$sql_det ="
SELECT 
    CONCAT(usuario, ' - ', fecha) AS usuario_fecha,
    mensaje,
	concat(perfil_api,'',numero) as perfil,
    (CASE WHEN perfil_api = numero THEN 1 ELSE 0 END) as res
FROM 
    ws_conversacion_detalle
WHERE 
    id_resumen = '[par_id_resumen]'";
sc_lookup_field(rs_det, $sql_det,"conn_helpdesk"); 

$font_tam = 9;
$font_atl= 5;
$line_ini = $line_ini + 7; // Salto de Bloque
$pdf->SetXY(5, $line_ini);
$pdf->SetFont($font_nom, '', $font_tam);
if (is_array($rs_det) && count($rs) > 0) {//Verificar si se 
	foreach ($rs_det as $row) {		
		if($row['res'] == 0){		
            $pdf->SetFillColor(192, 181, 178);
        } else {
            $pdf->SetFillColor(255, 255, 255); 
		}
		$pdf->SetXY(5, $line_ini);
		$pdf->Cell(57, $font_atl, $row['usuario_fecha'], 0,0,'L',true);
		$pdf->Cell(152, $font_atl, $row['mensaje'],0,0,'L',true);
		$line_ini = $line_ini + 5; // Salto de Bloque	
	}	
}

// Generar el archivo PDF
$pdf->Output($nombre_reporte, 'I');