
<?php
    sc_include_library("sys", "fpdf", "fpdf.php", true, true);
    $nombre_reporte='Factura Fiscal';

    $sql ="SELECT
        id_ventas,
        fecha_emision,
        nro_factura,
        corr_fiscal,
        cod_cliente,
        SUBSTR(descripcion,1,250) as descripcion,
        nombre_cliente,
        rif_fiscal,
        direccion,
        telefono,
        fecha_emision,
        fecha_vencimiento,
        total_neto,
        total_factura,
        sub_total,
        abono,
        saldo,
        base_imp,
        tasa_iva,
        iva,
        exento,
        status,
        descuento,
        t_descuento,
        cantidad_renglon,
        tasa_cambio,
        total_bsd,
        total_fact_bsd,
        nro_control,
        fecha,
        ip_estacion,
        usuario,
        empresa,
        sucursal,
        usr_nivel,
        id_telecomunicaciones,
        rif_fiscal,
        razon_social,
        ciudad,
        iva_bs,
        sub_total_bs,
        tasa_iva,
        total_fact_bsd,
        sucursal
    FROM
        ventas_resumen
    WHERE
    id_ventas = [par_id_ventas]";
    sc_lookup_field(rs, $sql); 
    //$id_proveedor = {rs[0]['id_proveedor']};

    $sucursalReg = {rs[0]['sucursal']};

    // Crear un nuevo objeto PDF
    $pdf = new FPDF('P', 'mm', 'Letter');
    // Agregar una página al PDF
    $pdf->AddPage();
    $pdf->Rect(0,0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    $font_nom = 'Arial';
    $font_tam = 9;
    $font_atl= 5;

    // Datos de la Compra
    $pdf->SetFont($font_nom,'B', 8);
    $line_salto = 4;
    $line_ini = 31;
    $colu_ini = 5;
    $font_atl= 0;
    $line_ini = $line_ini + $line_salto;
    $alinea = 'L';
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode('Cliente: '.{rs[0]['nombre_cliente']}), $font_atl,$alinea);
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(80,$font_atl, utf8_decode('RIF / CI: '.{rs[0]['rif_fiscal']}), 0,$alinea); 

    /*
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(150,$font_atl, utf8_decode('Direccion: '.{rs[0]['direccion']}), 0,$alinea); 	*/

    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(160, $font_atl, utf8_decode('Direccion: ').utf8_decode(substr({rs[0]['direccion']},0,74)), 0,'L');


    if(substr({rs[0]['direccion']},75,76) <>''){
        $line_ini = $line_ini + $line_salto;
        $pdf->SetXY(5, $line_ini);//Coordenadas X/Y
        $pdf->MultiCell(150, $font_atl, utf8_decode(substr({rs[0]['direccion']},75,100)), 0,'L');
    }

    // $line_ini = $line_ini + $line_salto;

    $pdf->SetFont($font_nom,'B', 12);
    $pdf->SetXY(148, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(60,$font_atl, utf8_decode('Fecha: '.date('d/m/Y', strtotime({rs[0]['fecha_emision']}))), 0,'R');
    $pdf->SetFont($font_nom,'B', 8);
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(60,$font_atl, utf8_decode('Cod. Cliente: '.{rs[0]['cod_cliente']}), 0,$alinea); 	
    $pdf->SetXY(60, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(60,$font_atl, utf8_decode('Telefono: '.{rs[0]['telefono']}),0,$alinea); 
    $pdf->SetXY(110, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(60,$font_atl, utf8_decode('Vendedor: '.' '), 0,$alinea); 
    $pdf->SetFont($font_nom,'B', 12);
    $pdf->SetXY(148, $line_ini);//Coordenadas X/Y 
    $pdf->MultiCell(60,$font_atl, utf8_decode('Factura No.: '.{rs[0]['corr_fiscal']}), 0,'R'); 

    // Detalle 
    $line_ini = $line_ini + 3; // Salto de Bloque
    $pdf->SetXY(5, $line_ini);
    $font_tam = 8;
    $pdf->SetFont($font_nom, 'B', $font_tam);
    $pdf->SetFillColor(146, 140, 139);
    $pdf->MultiCell(20,4, utf8_decode('Codigo'), 1,'C'); 
    $pdf->SetXY(25,$line_ini);
    $pdf->MultiCell(110,4, utf8_decode('Descripcion'), 1,'C'); 	
    $pdf->SetXY(135,$line_ini);
    $pdf->MultiCell(21,4, utf8_decode('Cantidad'), 1,'C');
    $pdf->SetXY(156,$line_ini);
    $pdf->MultiCell(25,4, utf8_decode('Precio'), 1,'C');
    $pdf->SetXY(181,$line_ini);
    $pdf->MultiCell(25,4, utf8_decode('Subtotal'), 1,'C'); 

    // Leyendo Detalle
    $sql_det ="SELECT
        vd.codigo,
        ip.nombre_productos,
        vd.cantidad,
        vd.precio_unitario,
        vd.precio_unitario_bs,
        vd.iva,
        vd.total_iva,
        vd.sub_total,
        vd.sub_total_bs
    FROM
        ventas_detalles vd
        LEFT JOIN inventario_productos ip ON ip.codigo_productos = vd.codigo AND ip.empresa = vd.empresa AND ip.sucursal = vd.sucursal 
    WHERE
        vd.id_detalle = [par_id_ventas]";
    sc_lookup_field(rs_det, $sql_det); 

    $font_tam = 9;
    $font_atl= 5;
    $line_ini = $line_ini + 4; // Salto de Bloque
    $pdf->SetFont($font_nom, '', $font_tam);
    if (is_array($rs_det) && count($rs) > 0) {//Verificar si se 
        foreach ($rs_det as $row) {		
            $pdf->SetXY(5, $line_ini);
            $pdf->Cell(20, $font_atl, $row['codigo'], 0,0,'C');
            $pdf->Cell(110, $font_atl, substr(utf8_decode($row['nombre_productos']),0,60),0,0,'L');
            $pdf->Cell(21, $font_atl, number_format($row['cantidad'],2), 0,0,'R');
            $pdf->Cell(25, $font_atl, number_format($row['precio_unitario_bs'],2),0,0,'R');
            $pdf->Cell(25, $font_atl, number_format($row['sub_total_bs'],2), 0,0,'R');
            $line_ini = $line_ini + 4; // Salto de Bloque	
        }	
    }
    // Foother
    // Mostrando TOTALES
    $pdf->SetFont($font_nom, 'B', 8);
    $font_atl= 0;
    $line_salto = 4;
    // $line_ini = $line_ini + 7;
    $line_ini = 90;
    $ancho = 60;
    $ancho_campo = 10;

    // Dibujar un recuadro
    $pdf->Rect(5, $line_ini, 201, 8, 'D');	
    $line_ini = $line_ini + 4;
    $pdf->SetXY(5, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Sub Total Bs.: ' .str_pad(number_format({rs[0]['sub_total_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'L');
    // $line_ini = $line_ini + $line_salto;
    $pdf->SetXY(60, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('I.V.A (' .str_pad(number_format({rs[0]['tasa_iva']},2,',','.').')',$ancho_campo,' ',STR_PAD_LEFT).'Bs.: '.str_pad(number_format({rs[0]['iva_bs']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'L');
    $pdf->SetXY(120, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Descuento Bs.: ' .str_pad(number_format({rs[0]['descuento']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'L');
    $pdf->SetXY(146, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Total Bs.: ' .str_pad(number_format({rs[0]['total_fact_bsd']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');

    $colu_ini = 5;
    $line_salto = 7;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetFont($font_nom, 'B', 8);
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(60,$font_atl, utf8_decode('Condiciones de Pago: '), 0,$alinea); 
    $pdf->SetXY(120, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(60,$font_atl, utf8_decode('Detalles del Pago: '), 0,$alinea); 
    $line_salto = 3;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetFont($font_nom, 'B', 6);
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(35,4, utf8_decode('Banco'), 1,'C'); 
    $pdf->SetXY(40,$line_ini);
    $pdf->MultiCell(30,4, utf8_decode('No. Cuenta'), 1,'C'); 	
    $pdf->SetXY(70,$line_ini);
    $pdf->MultiCell(15,4, utf8_decode('RIF'), 1,'C');
    $pdf->SetXY(85,$line_ini);
    $pdf->MultiCell(25,4, utf8_decode('Titular'), 1,'C');
    $pdf->SetXY(110,$line_ini);
    $pdf->MultiCell(12,4, utf8_decode('Cuenta'), 1,'C'); 


    $line_salto = 3;
    $font_tam = 6;
    $pdf->SetXY(125, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(20,4, utf8_decode('Tipo Pago'), 1,'C'); 
    $pdf->SetXY(145,$line_ini);
    $pdf->MultiCell(20,4, utf8_decode('Forma Pago'), 1,'C'); 	
    $pdf->SetXY(165,$line_ini);
    $pdf->MultiCell(21,4, utf8_decode('Referencia'), 1,'C');
    $pdf->SetXY(186,$line_ini);
    $pdf->MultiCell(20,4, utf8_decode('Monto Bs'), 1,'C');

    // Condiciones de Pago:
    $sql_pagos ="SELECT
        nombre_banco,
        nro_cuenta,
        rif_empresa,
        tipo_empresa,
        tipo_cuenta AS cuenta 
    FROM
        banco_facturas 
    WHERE
        empresa = '[usr_empresa]' AND
        sucursal = '$sucursalReg'";
    sc_lookup_field(rs_pagos, $sql_pagos);

    $font_tam = 6;
    $line_salto = 6;
    $line_ini = $line_ini + $line_salto;
    $line_fother = $line_ini;
    $pdf->SetFont($font_nom, '', $font_tam);

    if (is_array($rs_pagos) && count($rs) > 0) {//Verificar si se 
        foreach ($rs_pagos as $row) {		
            $pdf->SetXY(5, $line_ini);//Coordenadas X/Y
            $pdf->Cell(35, $font_atl, $row['nombre_banco'], 0,0,'L');
            $pdf->Cell(30, $font_atl, $row['nro_cuenta'], 0,0,'L');
            $pdf->Cell(15, $font_atl, $row['rif_empresa'], 0,0,'L');
            $pdf->Cell(25, $font_atl, $row['tipo_empresa'], 0,0,'L');
            $pdf->Cell(25, $font_atl, $row['cuenta'], 0,0,'L');
            $line_ini = $line_ini + 3; // Salto de Bloque	
        }	
    }

    //Detalles del Pago:
    $sql_deta_pagos ="SELECT
        btp.nombre_tipo_pago,
        vtd.tipo_pago,
        vtd.forma_pago,
        bfp.nombre_formas_pago,
        vtd.referencia,
        vtd.monto,
        vtd.monto_bs 
        FROM
            ventas_transacciones_detalles vtd
        LEFT JOIN banco_tipo_pago btp 
        ON btp.codigo_tipo_pago = vtd.tipo_pago
        LEFT JOIN banco_formas_pago bfp 
        ON bfp.codigo_formas_pago = vtd.forma_pago
        WHERE
        btp.empresa='[usr_empresa]' AND btp.sucursal ='$sucursalReg'  AND 
        bfp.empresa='[usr_empresa]' AND bfp.sucursal ='$sucursalReg'  AND 
        bfp.codigo_tipo_pago = vtd.tipo_pago AND 
        vtd.id_ventas_transacciones = [par_id_ventas]";
    sc_lookup_field(rs_deta_pagos, $sql_deta_pagos);

    $font_tam = 6;
    $line_salto = 6;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetFont($font_nom, '', $font_tam);

    if (is_array($rs_deta_pagos) && count($rs) > 0) {//Verificar si se 
        foreach ($rs_deta_pagos as $row) {		
            $pdf->SetXY(120, $line_fother);//Coordenadas X/Y
            $pdf->Cell(25, $font_atl, $row['nombre_tipo_pago'], 0,0,'L');
            $pdf->Cell(20, $font_atl, $row['nombre_formas_pago'], 0,0,'L');
            $pdf->Cell(18, $font_atl, $row['referencia'], 0,0,'L');
            $pdf->Cell(23, $font_atl, $row['monto_bs'], 0,0,'R');
            $line_fother = $line_fother + 3; // Salto de Bloque	
        }	
    }

    $line_salto = -4;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetFont($font_nom, 'B', 8);
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(60,$font_atl, utf8_decode('Descripcion: '), 0,'L'); 
    $line_salto = 5;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetFont($font_nom, '', $font_tam);
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
    $pdf->Cell(250, $font_atl, utf8_decode({rs[0]['descripcion']}), 0,0,'L');

    // Generar el archivo PDF				
    $pdf->Output($nombre_reporte, 'I');

?>