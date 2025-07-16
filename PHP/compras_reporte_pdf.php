<?php
  //     REPLACE(direccion, ' ', '\n') AS direccion,
    sc_include_library("sys", "fpdf", "fpdf.php", true, true);
    $nombre_reporte = 'Reporte de Compra';
    $nombre_modulo = 'COMPRA';

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
    sc_lookup_field(rs_empresa, $sql_empresa);
        
    $sql ="SELECT
        fecha_registro,
        numero,
        numero_factura,
        numero_control,
        fecha_factura,
        fecha_vencimiento,
        id_proveedor,
        codigo_proveedor,
        proveedor,
        direccion,
        telefono,
        no_com_ret,
        no_com_ret_islr,
        moneda,
        estado,
        base_impo_bs,
        base_exenta_bs,
        base_exonera_bs,
        base_alicu_redu_bs,
        iva_ret,
        islr_ret,
        iva_reduc_bs,
        sub_total_bs,
        total_bolivares,
        iva_bs,
        iva_reduc_bs,
        estado
    FROM
        compras_resumen 
    WHERE
        id_compra = '[par_id_compra]'";
    sc_lookup_field(rs, $sql); 
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
    $pdf->SetXY(4, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(100, $font_atl, {rs_empresa[0]['empresa']}, 0,'L');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(160, $font_atl, {rs_empresa[0]['direccion']}, 0,'L');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(60, $font_atl, {rs_empresa[0]['numero_identificacion']},0,'L');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY(5, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(60, $font_atl, {rs_empresa[0]['telefono']}, 0,'L');
    // Datos de la Compra
    $pdf->SetFont($font_nom,'B', 9);
    $line_salto = 4;
    $line_ini = 10;
    $colu_ini = 132;
    $font_atl= 0;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode($nombre_modulo), $font_atl,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode('NUMERO: '.{rs[0]['numero']}), $font_atl,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode('No. FACTURA: '.{rs[0]['numero_factura']}), 0,'R'); 	
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode('No. CONTROL: '.{rs[0]['numero_control']}), 0,'R'); 	
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $fecha_factura = {rs[0]['fecha_factura']}; 
    $fecha_factura_final = date('d/m/Y', strtotime($fecha_factura));
    $pdf->MultiCell(80,$font_atl, utf8_decode('FECHA EMISION: '.$fecha_factura_final), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $fecha_vencimiento = {rs[0]['fecha_vencimiento']}; 
    $fecha_vencimiento_final = date('d/m/Y', strtotime($fecha_vencimiento));
    $pdf->MultiCell(80,$font_atl, utf8_decode('FECHA VENCIMIENTO: '.$fecha_vencimiento_final), 0,'R'); 	
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode('MONEDA: '.{rs[0]['moneda']}), 0,'R'); 	
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode('ESTADO: '.{rs[0]['estado']}), 0,'R'); 	

    // Datos del Proveedor
    $pdf->SetFont($font_nom, 'B', 9);
    $font_atl= 0;
    $line_salto = 3;
    $line_ini = 30;
    $colu_ini = 5;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(80,$font_atl, utf8_decode('Datos del Proveedor'), 0,'l'); 
    $pdf->SetFont($font_nom,'', 8);
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y

    $pdf->MultiCell(150, $font_atl, utf8_decode('Nombre: '.{rs[0]['proveedor']}), 0,'L');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(20,$font_atl, utf8_decode('Direccion: '), 0,'l'); 
    $pdf->SetFont($font_nom, '', 8);
    $pdf->SetXY(18, $line_ini); 
    $pdf->MultiCell(140, $font_atl, utf8_decode(substr({rs[0]['direccion']},0,81)), 0,'L');

    if(substr({rs[0]['direccion']},82,83) <>''){
        $line_ini = $line_ini + $line_salto;
        $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
        $pdf->MultiCell(80, $font_atl, '-'.utf8_decode(substr({rs[0]['direccion']},81,150)), 0,'L');
    }
    $line_salto = 4;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(40, $font_atl, utf8_decode('R.I.F.: '.{rs[0]['codigo_proveedor']}), 0,'L');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini); //Coordenadas X/Y
    $pdf->MultiCell(40, $font_atl, utf8_decode('Telefono: '.{rs[0]['telefono']}), 0,'L');

    // Detalle 
    $line_ini = $line_ini + 7; // Salto de Bloque
    $pdf->SetXY(5, $line_ini);
    $font_tam = 8;
    $pdf->SetFont($font_nom, 'B', $font_tam);
    $pdf->SetFillColor(146, 140, 139);
    $pdf->MultiCell(17,4, utf8_decode('Codigo'."\n".' '), 1,'C',true); 
    $pdf->SetXY(22,$line_ini);
    $pdf->SetFont($font_nom, 'B', $font_tam);
    $pdf->MultiCell(65,4, utf8_decode('Nombre Producto'."\n".' '), 1,'C',true); 	
    $pdf->SetXY(85,$line_ini);
    $pdf->MultiCell(20,4, utf8_decode('Precio'."\n".'Und Bs'), 1,'C',true);
    $pdf->SetXY(105,$line_ini);
    $pdf->MultiCell(16,4, utf8_decode('Tipo'."\n".'Impuesto'), 1,'C',true);
    $pdf->SetXY(121,$line_ini);
    $pdf->MultiCell(15,4, utf8_decode('Tipo'."\n".'Unidad'), 1,'C',true); 
    $pdf->SetXY(136,$line_ini);
    $pdf->MultiCell(16,4, utf8_decode('Cantidad'."\n".' '), 1,'C',true); 
    $pdf->SetXY(152,$line_ini);
    $pdf->MultiCell(19,4, utf8_decode('Total'."\n".'Iva Bs'), 1,'C',true);
    $pdf->SetXY(171,$line_ini);
    $pdf->MultiCell(20,4, utf8_decode('Subtotal'."\n".'Renglon Bs'), 1,'C',true); 
    $pdf->SetXY(191,$line_ini);
    $pdf->MultiCell(20,4, utf8_decode('Total'."\n".'Renglon Bs'), 1,'C',true); 

    // Leyendo Detalle
    $sql_det ="SELECT
        codigo_producto,
        nombre_producto,
        precio_unitario_bs,
        tipo_impuesto,
        tipo_unidad,
        cantidad,
        total_iva_bs,
        subtotal_renglon_bs,
        total_renglon_bs 
    FROM
        compras_detalles 
    WHERE
        id_compra ='[par_id_compra]'";
    sc_lookup_field(rs_det, $sql_det); 

    $font_tam = 9;
    $font_atl= 5;
    $line_ini = $line_ini + 8; // Salto de Bloque
    $pdf->SetXY(5, $line_ini);
    $pdf->SetFont($font_nom, '', $font_tam);
    if (is_array($rs_det) && count($rs) > 0) {//Verificar si se 
        foreach ($rs_det as $row) {		
            $pdf->SetXY(5, $line_ini);
            $pdf->Cell(17, $font_atl, $row['codigo_producto'], 0,0,'C');
            $pdf->Cell(63, $font_atl, substr($row['nombre_producto'],0,35),0,0,'L');
            $pdf->Cell(20, $font_atl, number_format($row['precio_unitario_bs'],2), 0,0,'R');
            $pdf->Cell(16, $font_atl, number_format($row['tipo_impuesto'],2),0,0,'R');	
            $pdf->Cell(15, $font_atl, $row['tipo_unidad'],0,0,'R');
            $pdf->Cell(16, $font_atl, number_format($row['cantidad'],2),0,0,'R');
            $pdf->Cell(19, $font_atl, number_format($row['total_iva_bs'],2),0,0,'R');
            $pdf->Cell(20, $font_atl, number_format($row['subtotal_renglon_bs'],2), 0,0,'R');
            $pdf->Cell(20, $font_atl, number_format($row['total_renglon_bs'],2), 0,1,'R');
            $line_ini = $line_ini + 5; // Salto de Bloque	
        }	
    }
    // Foother
    // Mostrando TOTALES
    $pdf->SetFont($font_nom, 'B', 10);
    $font_atl= 0;
    $line_salto = 4;
    $line_ini = 223;
    $colu_ini = 120;
    $ancho = 90;
    $ancho_campo = 23;

    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Base Imponible Bs.: ' .str_pad(number_format({rs[0]['base_impo_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Base Exenta Bs.: ' .str_pad(number_format({rs[0]['base_exenta_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Base Alicuota Reduc. Bs.: ' .str_pad(number_format({rs[0]['base_alicu_redu_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Sub Total Bs.: ' .str_pad(number_format({rs[0]['sub_total_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Iva Bs.: ' .str_pad(number_format({rs[0]['iva_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Iva Reducido Bs.: ' .str_pad(number_format({rs[0]['iva_reduc_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Total Bs.: ' .str_pad(number_format({rs[0]['total_bolivares']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Iva Reducido Bs.: ' .str_pad(number_format({rs[0]['iva_reduc_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Iva Retenido Bs.: ' .str_pad(number_format({rs[0]['iva_ret']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Islr Retenido Bs.: ' .str_pad(number_format({rs[0]['islr_ret']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');

    // Generar el archivo PDF
    $pdf->Output($nombre_reporte, 'I');

?>
