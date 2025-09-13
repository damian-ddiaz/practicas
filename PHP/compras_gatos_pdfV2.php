<?php
//     REPLACE(direccion, ' ', '\n') AS direccion,
sc_include_library("sys", "fpdf", "fpdf.php", true, true);
$nombre_reporte = 'Reporte de Gastos';
$sql_empresa ="SELECT
	descripcion as empresa,
	numero_identificacion,
	CONCAT_WS('',
    UPPER(SUBSTRING(direccion, 1, 1)),
    LOWER(SUBSTRING(direccion, 2))
  	)  as direccion,
    telefono,
	sucursal
FROM
	configuracion_empresa 
WHERE
	codigo = '[usr_empresa]'";
sc_lookup_field(rs_empresa, $sql_empresa);

$sql_sucursal ="SELECT
	codigo,
	descripcion,
	direccion,
	rif 
FROM
	configuracion_sucursal 
WHERE
	empresa = '[usr_empresa]'
	AND codigo = '[usr_sucursal]'";
sc_lookup_field(rs_sucursal, $sql_sucursal);

$sql ="select
   numero,
  numero_factura,
  numero_control,
  fecha_factura,
  fecha_vencimiento,
  id_compra,
  descripcion as descripcion_gasto,
  direccion,
  codigo_proveedor  as rif_proveedor,
  estado,
  sucursal,
  fecha_registro as fecha_reg,
  tasa_cambio,
  total,
  total_bolivares,
  id_proveedor,
  original,
  id_gasto_resumen as corr_interno,
  usuario,
  round(total * tasa_cambio,2) as total_bs,
  round(iva_ret / tasa_cambio,2) as iva_ret,
  iva_ret as iva_ret_bs, 
  round(islr_ret / tasa_cambio,2) as islr_ret,
  islr_ret as islr_ret_bs
  from
  compras_resumen
where
   id_compra = [par_id_compra] and empresa ='[usr_empresa]'";
sc_lookup_field(rs, $sql); 


$sql_linea ="select
 count(*) as lineas
from
  compras_detalles
where
   $condicion_rs";
sc_lookup_field(rs_linea, $sql_linea); 

if({rs_linea[0]['lineas']}>=10){
	[par_media_carta] ='NO'; // Carta
}

$sql_compra ="SELECT
	iva_ret,
	islr_ret,
	saldo_bs
FROM
	compras_resumen 
WHERE
	id_compra = '[par_id_compra]'";
sc_lookup_field(rs_compras, $sql_compra); 

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
$font_atl= 4;

$pdf->SetXY(0,5);//Coordenadas X e Y Titulo del Reporte
$pdf->SetFont($font_nom, 'B', 12);
$pdf->Cell(0, $font_atl, utf8_decode($nombre_reporte), 0, 1, 'C');

$pdf->SetFont($font_nom, 'B', 9);
$pdf->SetXY(150, 4);//Coordenadas X/Y 
$pdf->MultiCell(60, $font_atl, utf8_decode('Numero Gasto: '.{rs[0]['numero']}), 0,'R');

$fecha_factura = {rs[0]['fecha_reg']}; 
$fecha_factura_final = date('d/m/Y', strtotime($fecha_factura));
$pdf->SetXY(150, 8);//Coordenadas X/Y 
$pdf->MultiCell(60, $font_atl, utf8_decode('Fecha: '.$fecha_factura_final), 0,'R');

$pdf->SetFont($font_nom, '', 8);
$line_ini= 13;
$line_salto = 4;
//Datos de la Empresa
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(100, $font_atl, utf8_decode('Empresa: '.{rs_empresa[0]['empresa']}), 0,'L');

$pdf->SetXY(95, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(80, $font_atl,utf8_decode('Sucursal: '.{rs_sucursal[0]['descripcion']}), 0,'L');

$pdf->SetFont($font_nom, 'B', 9);
$pdf->SetXY(150, 12);//Coordenadas X/Y 
$pdf->MultiCell(60, $font_atl, utf8_decode('Estatus: '.{rs[0]['estado']}),0,'R');
$pdf->SetFont($font_nom, '', 8);

$line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(60, $font_atl, utf8_decode('R.I.F.: '.{rs_empresa[0]['numero_identificacion']}),0,'L');

// $var_corr_interno = {rs[0]['corr_interno']};

$fecha_desde = {rs_periodo[0]['fecha']}; 
$fecha_desde_final = date('d/m/Y', strtotime($fecha_desde));
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(60, $font_atl, utf8_decode('Periodo Desde: '.$fecha_desde_final),0,'L');

$fecha_hasta = {rs_periodo[0]['fecha_fin']}; 
$fecha_hasta_final = date('d/m/Y', strtotime($fecha_hasta));
$pdf->SetXY(50, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(60, $font_atl, utf8_decode('Hasta: '.$fecha_hasta_final),0,'L');

$line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(207, $font_atl, utf8_decode('Descripcion: '.substr({rs[0]['descripcion_gasto']},0,180).'-'), 0,'L');

if(substr({rs[0]['descripcion_gasto']},181,182) <>''){
	$line_ini = $line_ini + $line_salto;
	$pdf->SetXY(5, $line_ini);//Coordenadas X/Y
	$pdf->MultiCell(150, $font_atl, utf8_decode(substr({rs[0]['descripcion_gasto']},114,200)), 0,'L');
}

// Datos del Proveedor
$colu_ini = 5;
$id_proveedor={rs[0]['id_proveedor']};
$sql_proveedor ="select
  id_proveedor,
  nombre_proveedor,
  direccion_proveedor,
  concat(tipo_rif, ' ', rif_proveedor) as rif_proveedor,
  telefono_movil
from
  proveedores_datos
where
  id_proveedor  = '$id_proveedor'";
sc_lookup_field(rs_prove, $sql_proveedor);

$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(100, $font_atl, utf8_decode('Proveedor: '.{rs_prove[0]['nombre_proveedor']}), 0,'L');

// Buscando Nommbre Usuario
$var_usuaro = {rs[0]['usuario']};
$sql_usuario ="select
  name
from
  seguridad_users
where
  login = '$var_usuaro'";
sc_lookup_field(rs_usuario, $sql_usuario);

$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(20,$font_atl, utf8_decode('Direccion: '), 0,'l'); 
$pdf->SetFont($font_nom, '', 8);
$pdf->SetXY(18, $line_ini); 
$pdf->MultiCell(200, $font_atl, utf8_decode(substr({rs_prove[0]['direccion_proveedor']},0,106).'-'), 0,'L');

$pdf->SetXY(120, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(90, $font_atl, utf8_decode('Tasa: '.{rs[0]['tasa_cambio']}),0,'R');

if(substr({rs_prove[0]['direccion_proveedor']},106,107) <>''){
	$line_ini = $line_ini + $line_salto;
	$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
	$pdf->MultiCell(102, $font_atl, utf8_decode(substr({rs_prove[0]['direccion_proveedor']},106,200)), 0,'L');
}

$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
$pdf->MultiCell(60, $font_atl, utf8_decode('R.I.F.: '.{rs_prove[0]['rif_proveedor']}), 0,'L');
$pdf->SetXY(95, $line_ini); //Coordenadas X/Y
$pdf->MultiCell(60, $font_atl, utf8_decode('Telefono(s): '.{rs_prove[0]['telefono_movil']}), 0,'L');
$pdf->SetXY(120, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(90, $font_atl, utf8_decode('Realizado por: '.{rs_usuario[0]['name']}),0,'R');

// Leyendo Detalle
$sql_det_gasto ="select 
 cd.codigo_producto,
 cd.nombre_producto as nombre_prod, 
 ip.codigo_padre as codigo_padre,
 ip.codigo_hijo as codigo_hijo,
 cd.cantidad, 
 cd.precio_unitario as precio_uni,
 cd.tipo_impuesto as iva,
 sum(cd.total_renglon_bs) as total_renglon_bs,
 sum(cd.total_renglon) as total_renglon,
 if(isnull(cd.monto_permitido),0,cd.monto_permitido) as monto_permitido,
 if(isnull(cd.monto_disponible),0,cd.monto_disponible) as monto_disponible,
 if(isnull(cpp.nombre_padre) or cpp.nombre_padre ='','SIN CLASIFICAR',cpp.nombre_padre) as nombre_padre,
  if(isnull(cph.nombre_hijo) or cph.nombre_hijo ='','SIN CLASIFICAR',cph.nombre_hijo) as nombre_hijo,
 cd.empresa,
 cd.sucursal
from
  compras_detalles cd
 left join inventario_productos ip 
 on ip.codigo_productos = codigo_producto 
 and ip.empresa = cd.empresa and ip.sucursal = cd.sucursal
 left join contabilidad_plan_de_cuentas_padre cpp 
 on cpp.codigo_padre = ip.codigo_padre
 AND cpp.empresa = cd.empresa
 left join contabilidad_plan_de_cuentas_hijo cph 
 on cph.codigo_hijo = ip.codigo_hijo
 and cph.empresa = cd.empresa
where
  cd.id_compra = [par_id_compra]
   group by ip.codigo_hijo";
sc_lookup_field(rs_det_gasto, $sql_det_gasto); 

if({rs_det_gasto[0]['total_renglon']}>0 and [par_id_compra] >0){ // hay detalles de Gastos
	$line_ini = $line_ini + 7; // Salto de Bloque
	$pdf->SetXY(5, $line_ini);
	$font_tam = 8;
	$pdf->SetFont($font_nom, 'B', $font_tam);
	$pdf->SetFillColor(146, 140, 139);
	$pdf->MultiCell(40,4, utf8_decode('Tipo Gasto'), 1,'C',true); 
	$pdf->SetXY(45,$line_ini);
	$pdf->MultiCell(75,4, utf8_decode('Concepto'), 1,'C',true); 
	$pdf->SetXY(120,$line_ini);
	$pdf->MultiCell(30,4, utf8_decode('Monto'), 1,'C',true); 	
	$pdf->SetXY(150,$line_ini);
	$pdf->MultiCell(30,4, utf8_decode('Monto Permitido'), 1,'C',true); 
	$pdf->SetXY(180,$line_ini);
	$pdf->MultiCell(30,4, utf8_decode('Monto Disponible'), 1,'C',true); 
		
	$font_tam = 9;
	$font_atl= 5;
	$line_ini = $line_ini + 4; // Salto de Bloque
	$pdf->SetXY(5, $line_ini);
	$pdf->SetFont($font_nom, '', $font_tam);
	if (is_array($rs_det_gasto) && count($rs) > 0) {//Verificar si se 
		foreach ($rs_det_gasto as $row) {	
			$pdf->SetXY(5, $line_ini);
			$pdf->Cell(40, $font_atl, utf8_decode($row['nombre_padre']),0,0,'L');
			$pdf->Cell(75, $font_atl, utf8_decode($row['nombre_hijo']),0,0,'L');
			$pdf->Cell(30, $font_atl, $row['total_renglon'], 0,0,'R');
			$pdf->Cell(30, $font_atl, $row['monto_permitido'], 0,0,'R');
			$pdf->Cell(30, $font_atl, $row['monto_disponible'],0,0,'R');
			$line_ini = $line_ini + 5; // Salto de Bloque	
			
			//Agrgando el Detalle de los gastos - Damian Diaz 06-08-2025
			$var_codigo_padre = $row['codigo_padre'];
			$var_codigo_hijo  = $row['codigo_hijo'];
			// Leyendo Detalle Productos
			$rs_det_gasto_productos ="select 
			 cd.codigo_producto,
			 cd.nombre_producto as nombre_prod, 
			 ip.codigo_padre,
			 ip.codigo_hijo,
			 cd.cantidad, 
			 cd.precio_unitario as precio_uni,
			 cd.tipo_impuesto as iva,
			 cd.total_renglon_bs as total_renglon_bs,
			 cd.total_renglon as total_renglon,
			 cd.empresa,
			 cd.sucursal
			from
			  compras_detalles cd
			 left join inventario_productos ip 
			 on ip.codigo_productos = codigo_producto 
			 and ip.empresa = cd.empresa and ip.sucursal = cd.sucursal
			where
			  cd.id_compra = [par_id_compra]
			  AND ip.codigo_padre 	= '$var_codigo_padre'
			  AND ip.codigo_hijo 	= '$var_codigo_hijo'";
			sc_lookup_field(rs_det_gasto_productos, $rs_det_gasto_productos); 
			
			$pdf->SetXY(5, $line_ini);
			$pdf->MultiCell(40,4, utf8_decode('Codigo'), 1,'C'); 
			$pdf->SetXY(45,$line_ini);
			$pdf->MultiCell(75,4, utf8_decode('Nombre del Pruducto'), 1,'C'); 
			$pdf->SetXY(120,$line_ini);
			$pdf->MultiCell(30,4, utf8_decode('Precio UND'), 1,'C'); 	
			$pdf->SetXY(150,$line_ini);
			$pdf->MultiCell(30,4, utf8_decode('Cantidad'), 1,'C'); 
			$pdf->SetXY(180,$line_ini);
			$pdf->MultiCell(30,4, utf8_decode('Total Renglon'), 1,'C'); 

			$line_ini = $line_ini + 5; // Salto de Bloque	

			if (is_array($rs_det_gasto_productos) && count($rs) > 0) {//Verificar si se 
				foreach ($rs_det_gasto_productos as $row) {	
					$pdf->SetXY(5, $line_ini);
					$pdf->Cell(40, $font_atl, utf8_decode($row['codigo_producto']),0,0,'C');
					$pdf->Cell(75, $font_atl, utf8_decode($row['nombre_prod']),0,0,'L');
					$pdf->Cell(30, $font_atl, $row['precio_uni'],0,0,'R');
					$pdf->Cell(30, $font_atl, $row['cantidad'],0,0,'R');
					$pdf->Cell(30, $font_atl, utf8_decode($row['total_renglon']),0,0,'R');
					$line_ini = $line_ini + 5; // Salto de Bloque	
				}
			}			
			
		}	
	}	
}

// Detalle de los pagos
if([par_media_carta] =='SI'){;
	$line_ini = $line_ini + 5; // Salto de Bloque
}else{// Carta
	$line_ini = $line_ini + 10; // Salto de Bloque
}

$pdf->SetXY(5, $line_ini);
$font_tam = 8;
$pdf->SetFont($font_nom, 'B', $font_tam);
$pdf->SetFillColor(146, 140, 139);
$pdf->MultiCell(50,4, utf8_decode('Tipo Pago'), 1,'C',true); 
$pdf->SetXY(55,$line_ini);
$pdf->MultiCell(40,4, utf8_decode('Forma Pago'), 1,'C',true); 	
$pdf->SetXY(95,$line_ini);
$pdf->MultiCell(28,4, utf8_decode('Referencia'), 1,'C',true);
$pdf->SetXY(123,$line_ini);
$pdf->MultiCell(22,4, utf8_decode('Monto'), 1,'C',true);
$pdf->SetXY(145,$line_ini);
$pdf->MultiCell(22,4, utf8_decode('Monto Bs'), 1,'C',true); 
$pdf->SetXY(167,$line_ini);
$pdf->MultiCell(22,4, utf8_decode('Tasa Cambio'), 1,'C',true); 
$pdf->SetXY(189,$line_ini);
$pdf->MultiCell(22,4, utf8_decode('Fecha'), 1,'C',true);

// Leyendo Detalle
$sql_det ="select
  cd.id_compra,
  cd.tipo_pago,
  CONCAT(UPPER(SUBSTRING(btp.nombre_tipo_pago, 1, 1)),
  LOWER(SUBSTRING(btp.nombre_tipo_pago, 2))) AS nombre_tipo_pago,  cd.forma_pago,
  CONCAT(UPPER(SUBSTRING(bfp.nombre_formas_pago, 1, 1)),
  LOWER(SUBSTRING(bfp.nombre_formas_pago, 2))) AS nombre_formas_pago,  cd.referencia,
  cd.monto,
  cd.monto_bs,
  cd.tasa_cambio,
  cd.fecha_transaccion,
  cd.id_factura_resumen
from
  compras_transacciones_detalles cd
  left join banco_tipo_pago btp ON 
  btp.codigo_tipo_pago = cd.tipo_pago
  left join banco_formas_pago bfp ON
  bfp.codigo_formas_pago = cd.forma_pago
where
  cd.id_compra = [par_id_compra]
  and btp.empresa='[usr_empresa]' and btp.sucursal ='[usr_sucursal]'
  and bfp.empresa='[usr_empresa]' and bfp.sucursal ='[usr_sucursal]'
  and bfp.codigo_tipo_pago = cd.tipo_pago
";
sc_lookup_field(rs_det, $sql_det); 

$font_tam = 9;
$font_atl= 5;
$line_ini = $line_ini + 6; // Salto de Bloque
$pdf->SetXY(5, $line_ini);
$pdf->SetFont($font_nom, '', $font_tam);
if (is_array($rs_det) && count($rs) > 0) {//Verificar si se 
	foreach ($rs_det as $row) {		
		$pdf->SetXY(5, $line_ini);
		$pdf->Cell(50, $font_atl, utf8_decode($row['nombre_tipo_pago']),0,0,'L');
		$pdf->Cell(40, $font_atl, utf8_decode(substr($row['nombre_formas_pago'],0,24)),0,0,'L');		$pdf->Cell(28, $font_atl, $row['referencia'], 0,0,'L');
		$pdf->Cell(22, $font_atl, $row['monto'],0,0,'R');
		$pdf->Cell(22, $font_atl, $row['monto_bs'],0,0,'R');
		$pdf->Cell(22, $font_atl, $row['tasa_cambio'], 0,0,'R');
		$fecha_transac = $row['fecha_transaccion']; 
		$fecha_transac_final = date('d/m/Y', strtotime($fecha_transac));
		$pdf->Cell(22, $font_atl, $fecha_transac_final, 0,0,'C');
		$line_ini = $line_ini + 5; // Salto de Bloque		
	}	
}

// Foother
// Mostrando TOTALES

$pdf->SetFont($font_nom, 'B', 10);

if([par_media_carta] =='SI'){
	$line_ini = 100;
}else{
	$line_ini = 235;
}

//$line_ini = $line_ini + 5; // Salto de Bloque		
$font_atl= 0;
$line_salto = 4;
// $line_ini = 100;
$colu_ini = 120;
$ancho = 90;
$ancho_campo = 20;

$var_gasto	  = {rs[0]['total']} - ({rs[0]['iva_ret']} + {rs[0]['islr_ret']});
$var_gasto_bs = {rs[0]['total_bs']} - ({rs[0]['iva_ret_bs']} + {rs[0]['islr_ret_bs']});

$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
$pdf->MultiCell($ancho,$font_atl, utf8_decode('Total a Pagar $: ' .str_pad(number_format($var_gasto,2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');

$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
$pdf->MultiCell($ancho,$font_atl, utf8_decode('Total a Pagar Bs: ' .str_pad(number_format($var_gasto_bs,2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');

// Generar el archivo PDF
$pdf->Output($nombre_reporte, 'I');

?>
