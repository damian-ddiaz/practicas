<?php
// Llamar a la librería
// Damian Diaz Creando un PDF
sc_include_library("sys", "fpdf", "fpdf.php", true, true);
$nombre_reporte='Movimiento de Inventario';
// Cargando logo de la Empresa
$sql_logo = "SELECT
		imagen
		from 
		configuracion_empresa
		WHERE
	    codigo = '[usr_empresa]'";
sc_lookup_field(rs_logo, $sql_logo); 
$imagen_logo = {rs_logo[0]['imagen']};
/*
?>
	<img src='$imagen_logo'>;
<?php
*/
// Realizar la consulta a la base de datos
$sql = "SELECT 
	tfr.fecha,
	tfr.numero_toma_fisica_resumen,
	tfr.tipo_de_movimiento,
	tfr.concepto,
	ia.nombre_almacen,
	tfr.descripcion,
	tfr.empresa,
	tfr.sucursal,
	id_toma_fisica_resumen,
	tfr.numero_toma_fisica_resumen,
	tfr.tasa_cambio 
FROM
	inventario_toma_fisica_resumen tfr
	LEFT JOIN inventario_almacen ia ON ia.codigo_almacen = tfr.codigo_almacen 
WHERE
	tfr.id_toma_fisica_resumen = [par_id_toma_fisica_resumen] 
	AND tfr.empresa = '[usr_empresa]' 
	AND tfr.sucursal = '[usr_sucursal]'
	AND ia.empresa='[usr_empresa]' 
	AND ia.sucursal='[usr_sucursal]'";		
sc_lookup_field(rs, $sql); 

// Crear un nuevo objeto PDF
$pdf = new FPDF();

// Establecer márgenes y salto automático de página
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

// Agregar una página al PDF
$pdf->AddPage();

// Agregando el logo de la Empresa 
$pdf->Image('https://tecnoveninternet.com/wp-content/uploads/2022/09/Logo-Final.png', 8, 3, 40, 7);

// Buscando Imagen de la Empresa

// Asignando valores al formato de la fuente a variables
$font_nom = 'Arial';
$font_tam = 12;
$font_atl= 5;

$pdf->SetFont($font_nom, 'B', 12);
$pdf->Cell(0, 0, $nombre_reporte, 0, 1, 'C');

// Recorrer los resultados de la consulta y agregarlos al PDF
// Contruyendo Resumen del PDF
if (is_array($rs) && count($rs) > 0) { // Verificar si se encontraron resultados
    foreach ($rs as $row) {			
		$pdf->SetXY(117,4);//Coordenadas X e Y del Bloque Fecha - Numero
		$pdf->Cell(19, $font_atl, 'Fecha:', 0,0,'L');
		$pdf->SetFont($font_nom, '', $font_tam);
		$fechaFormato = date('d/m/Y', strtotime($row['fecha']));
        $pdf->Cell(20, $font_atl, $fechaFormato, 0, 0, 'R');
		
		$pdf->SetFont($font_nom, 'B', $font_tam);
		$pdf->Cell(25,$font_atl, utf8_decode('Número:'), 0,0,'L');
		$pdf->SetFont($font_nom, '', $font_tam);
        $pdf->Cell(20,$font_atl, $row['numero_toma_fisica_resumen'], 0,1,'R');
		
		$pdf->SetXY(8,12);//Coordenadas X e Y del Bloque Almacen - Total Renglones
		$pdf->SetFont($font_nom, 'B', $font_tam);
		$pdf->Cell(27,$font_atl, utf8_decode('Almacén:'), 0,0,'L');
		$pdf->SetFont($font_nom, '', $font_tam);
        $pdf->Cell(70,$font_atl, $row['nombre_almacen'], 0,0,'l');	
		$pdf->SetFont($font_nom, 'B', $font_tam);
		$pdf->Cell(43,$font_atl, utf8_decode('Tipo de Movimiento:'), 0,0,'L');
		$pdf->SetFont($font_nom, '', $font_tam);
        $pdf->Cell(90,$font_atl, $row['tipo_de_movimiento'], 0,0,'l');
		
		$pdf->SetXY(8,COMPROBANTE DE RETENCION DE IMPUESTO AL VALOR AGREGADO I.V.A. 17); //Coordenadas X e Y del Bloque Total Productos 
		$pdf->SetFont($font_nom, 'B', $font_tam);
		$pdf->Cell(98,$font_atl, utf8_decode('Descripción:'), 0,0,'L');
		$pdf->Cell(42,$font_atl, utf8_decode('Concepto:'), 0,0,'R');
		$pdf->SetFont($font_nom, '', $font_tam);
		$pdf->Cell(90,$font_atl, $row['concepto'], 0,0,'l');
		$pdf->MultiCell(190,$font_atl, $row['descripcion'], 0,'J'); // Utilizar MultiCell para contenido en múltiples líneas
		// Dibujar un rectángulo alrededor de toda la página
		$pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    }	
}	
	// Contruyendo Detalle del PDF
	// Asignando valores al formato de la fuente a variables
	$font_nom = 'Arial';
	$font_tam = 10;
	$font_atl= 5;
	
	$pdf->SetFont($font_nom, 'B', $font_tam); // Asignando fuente y tamaño
	$pdf->SetFillColor(151, 157, 152); // Establecer el color de fondo a rojo (RGB: 255, 0, 0)

	$pdf->Cell(20, $font_atl, 'Codigo', 1,0,'C',true);
	$pdf->Cell(90, $font_atl, utf8_decode('Descripción'),1,0,'C',true);	
	$pdf->Cell(27, $font_atl, 'Canttidad',1,0,'C',true);
	$pdf->Cell(27, $font_atl, 'Costo',1,0,'C',true);
	$pdf->Cell(27, $font_atl, 'Total',1,0,'C',true);
		
	$sql_det = "SELECT 
	tfd.fecha,
	tfd.codigo_productos,
	ip.nombre_productos,
	tfd.cantidad,
	tfd.costo,
	tfd.total,
	tfd.tasa_de_cambio,
	tfd.estatus
	FROM
		inventario_toma_fisica_detalles tfd
	LEFT JOIN inventario_productos ip
	ON ip.codigo_productos = tfd.codigo_productos
	WHERE tfd.numero_toma_fisica_resumen =3135
		AND tfd.empresa = '[usr_empresa]' 
		AND tfd.sucursal = '[usr_sucursal]'
		AND ip.empresa = '[usr_empresa]' 
		AND ip.sucursal = '[usr_sucursal]'
	";
	sc_lookup_field(rs_det, $sql_det);

    $pdf->Ln();
    $pdf->SetFont($font_nom, '', $font_tam); // Asignando fuente y tamaño
$total_reporte = 0;
	if (is_array($rs_det) && count($rs) > 0) { // Verificar si se encontraron resultados
	    foreach ($rs_det as $row) {
			$pdf->Cell(20, $font_atl, $row['codigo_productos'], 0, 0, 'C');		
			$pdf->Cell(91, $font_atl, $row['nombre_productos'], 0, 0, 'L');		
			$pdf->Cell(25, $font_atl, $row['cantidad'], 0, 0, 'R');	
			$pdf->Cell(27, $font_atl, $row['costo'], 0, 0, 'R');		
			$pdf->Cell(27, $font_atl, $row['total'], 0, 0, 'R');	
			$total_reporte += $row['total']; // Calculando el Total del campo diferencia
		}
		$pdf->SetXY(10,265); // Establecer las coordenadas X e Y del Bloque Dibujar línea
		$pdf->SetDrawColor(0, 0, 0); // Establecer color de línea a negro
        $pdf->SetLineWidth(0.2); // Establecer el ancho de línea
        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY()); // Dibujar línea
		
		$pdf->SetXY(145,266); // Establecer las coordenadas X e Y del Bloque Pie de Pagina
		$pdf->SetFont($font_nom, 'B', $font_tam); // Asignando fuente y tamaño
		$pdf->Cell(0, $font_atl, 'Total: '. number_format($total_reporte, 2), 0, 1, 'R');
        $pdf->Cell(0, $font_atl,utf8_decode('Página: ').$pdf->PageNo(), 0, 0, 'R');	
	}

// Generar el archivo PDF
$pdf->Output($nombre_reporte, 'I');

//  codigo_producto_onChange 
sc_lookup(prod, "select nombre_productos from inventario_productos where codigo_productos = '{codigo_producto}' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'");

{nombre_producto} = {prod[0][0]};

// function conteo_renglones 
/*TOTAL RENGLONES*/
sc_lookup(dt_total_reng, "select count(id_compra) as total_tenglones from compras_detalles where id_compra={id_compra} and empresa='[usr_empresa]' and sucursal='[usr_sucursal]'");
if(isset({dt_total_reng[0][0]})){
	$tot_reg = {dt_total_reng[0][0]};
}else{
	$tot_reg=0;
}
return $tot_reg;




?>