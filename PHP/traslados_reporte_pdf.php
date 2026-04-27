<?php
    sc_include_library("sys", "fpdf", "fpdf.php", true, true);
    $nombre_reporte='Traslado entre Almacenes';
    $nombre_modulo = 'TRASLADO ENTRE ALMACENES';
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
        itr.id_res_traslado_almacen,
        itr.numero_res_traslado_almacen as numero,
        DATE(itr.fecha_res_traslado_almacen) as fecha,
        itr.codigo_almacen_origen,
        iao.nombre_almacen as nombre_almacen_origen,
        itr.codigo_almacen_destino,
        iad.nombre_almacen as nombre_almacen_destino,
        itr.descripcion_traslado as observaciones,
        itr.total_costo,
        itr.total_renglones,
        itr.estatus as estado,
        itr.tasa_cambio,
        itr.usuario,
        su.name AS nombre_usuario
    FROM
        inventario_traslados_resumen itr
    LEFT JOIN inventario_almacen iao
    ON iao.codigo_almacen = itr.codigo_almacen_origen
    LEFT JOIN inventario_almacen iad
    ON iad.codigo_almacen = itr.codigo_almacen_destino
    LEFT JOIN seguridad_users su
    ON su.login = itr.usuario
    WHERE
    itr.id_res_traslado_almacen = '[par_id_res_traslado_almacen]'
        AND itr.empresa = '[usr_empresa]'
        AND itr.sucursal = '[usr_sucursal]'
        AND iao.empresa = '[usr_empresa]'
        AND iao.sucursal = '[usr_sucursal]'
        AND iad.empresa = '[usr_empresa]'
        AND iad.sucursal = '[usr_sucursal]'
        group by id_res_traslado_almacen";
    sc_lookup_field(rs, $sql);
    //$id_proveedor = {rs[0]['id_proveedor']};
    $var_id_resumen = {rs[0]['id_res_traslado_almacen']};

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
    $pdf->MultiCell(80,$font_atl, utf8_decode('FECHA: '.{rs[0]['fecha']}), 0,'R');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(80,$font_atl, utf8_decode('ESTADO: '.{rs[0]['estado']}), 0,'R');
    $pdf->SetFont($font_nom, '', $font_tam);
    $line_salto = 7;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY(5, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(100,$font_atl, utf8_decode('Almacen Origen: '.{rs[0]['nombre_almacen_origen']}), 0,'L');
    $pdf->SetXY(80, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(100,$font_atl, utf8_decode('Almacen Destino: '.{rs[0]['nombre_almacen_destino']}), 0,'R');
    $line_salto = 5;
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY(5, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(200,$font_atl, utf8_decode('Observaciones: '.{rs[0]['observaciones']}), 0,'L');
    $line_ini = $line_ini + $line_salto;
    $pdf->SetXY(5, $line_ini);//Coordenadas X/Y
    $pdf->MultiCell(150,$font_atl, utf8_decode('Usuario: '.{rs[0]['nombre_usuario']}), 0,'L');

    // Detalle
    $line_ini = $line_ini + 5; // Salto de Bloque
    $pdf->SetXY(5, $line_ini);
    $font_tam = 8;
    $pdf->SetFont($font_nom, 'B', $font_tam);
    $pdf->SetFillColor(146, 140, 139);

    // Headers for product details
    if([par_incluir_precio] == 'SI'){
        $pdf->MultiCell(30,4, utf8_decode('Codigo'."\n".' '), 1,'C',true);
        $pdf->SetXY(35,$line_ini);
        $pdf->SetFont($font_nom, 'B', $font_tam);
        $pdf->MultiCell(87,4, utf8_decode('Nombre Producto'."\n".' '), 1,'C',true);
        $pdf->SetXY(122,$line_ini);
        $pdf->MultiCell(20,4, utf8_decode('UNIDAD'."\n".' '), 1,'C',true);
        $pdf->SetXY(142,$line_ini);
        $pdf->MultiCell(20,4, utf8_decode('Cantidad'."\n".' '), 1,'C',true);
        $pdf->SetXY(162,$line_ini);
        $pdf->MultiCell(22,4, utf8_decode('Costo'."\n".' '), 1,'C',true);
        $pdf->SetXY(184,$line_ini);
        $pdf->MultiCell(27,4, utf8_decode('Total Costo'."\n".' '), 1,'C',true);
    }else{
        $pdf->MultiCell(30,4, utf8_decode('Codigo'."\n".' '), 1,'C',true);
        $pdf->SetXY(30,$line_ini);
        $pdf->SetFont($font_nom, 'B', $font_tam);
        $pdf->MultiCell(141,4, utf8_decode('Nombre Producto'."\n".' '), 1,'C',true);
        $pdf->SetXY(171,$line_ini);
        $pdf->MultiCell(20,4, utf8_decode('UNIDAD'."\n".' '), 1,'C',true);
        $pdf->SetXY(191,$line_ini);
        $pdf->MultiCell(20,4, utf8_decode('Cantidad'."\n".' '), 1,'C',true);
    }

    // Reading Detail
    $sql_det ="SELECT
        itd.codigo_producto_det_traslado as codigo_producto,
        ip.nombre_productos as nombre_producto,
        itd.cantidad_det_traslado as cantidad,
        ip.codigo_unidad_productos as unidad,
        itd.costo_det_traslado as costo,
        itd.total_det_traslado as costo_total,
        itd.tasa_cambio,
        ip.manejaser_productos
        FROM
        inventario_traslados_detalle itd
        LEFT JOIN inventario_productos ip
        ON ip.codigo_productos = itd.codigo_producto_det_traslado
        WHERE
        itd.numero_det_traslado_almacen = '[par_id_res_traslado_almacen]'
        AND itd.empresa = '[usr_empresa]'
        AND itd.sucursal = '[usr_sucursal]'
    AND ip.empresa = '[usr_empresa]'
        AND ip.sucursal = '[usr_sucursal]'";
    sc_lookup_field(rs_det, $sql_det);

    $font_tam = 9;
    $font_atl= 5;
    $line_ini = $line_ini + 8; // Initial vertical position after headers
    $pdf->SetFont($font_nom, '', $font_tam);

    if (is_array($rs_det) && count($rs_det) > 0) { // Check if results exist
        foreach ($rs_det as $row) {
            // Check for page break before printing each product detail
            if ($pdf->GetY() + $font_atl > $pdf->GetPageHeight() - 30) { // 30 is a margin for footer
                $pdf->AddPage();
                // Reset Y position for new page, adjust as needed for header
                $pdf->SetY(15); // Adjust this value to your desired starting Y on a new page
            }

            if([par_incluir_precio] == 'SI'){
                $pdf->SetX(5);
                $pdf->Cell(30, $font_atl, $row['codigo_producto'], 0,0,'C');
                $pdf->Cell(90, $font_atl, substr(utf8_decode($row['nombre_producto']),0,40),0,0,'L');
                $pdf->Cell(15, $font_atl,utf8_decode($row['unidad']),0,0,'R');
                $pdf->Cell(20, $font_atl, number_format($row['cantidad'],2),0,0,'R');
                $pdf->Cell(22, $font_atl, number_format($row['costo'],2),0,0,'R');
                $pdf->Cell(27, $font_atl, number_format($row['costo_total'],2), 0,1,'R');
            }else{
                $pdf->SetX(5);
                $pdf->Cell(30, $font_atl, $row['codigo_producto'], 0,0,'C');
                $pdf->Cell(135, $font_atl, substr(utf8_decode($row['nombre_producto']),0,40),0,0,'L');
                $pdf->Cell(15, $font_atl,utf8_decode($row['unidad']),0,0,'R');
                $pdf->Cell(22, $font_atl, number_format($row['cantidad'],2),0,1,'R');
            }

            // Handle serial numbers
            if([par_imprime_serial] =='SI' && $row['manejaser_productos']=='SI'){
                $codigo_productos = $row['codigo_producto'];

                // Check for page break before printing serials header
                if ($pdf->GetY() + $font_atl + 5 > $pdf->GetPageHeight() - 30) {
                    $pdf->AddPage();
                    $pdf->SetY(15);
                }
                
                $pdf->SetX(10);
                $pdf->MultiCell(60,4, utf8_decode('Seriale(s) de este producto:'), 0,'L');

                // Buscando Seriales del Producto
                $sql_det_serial ="SELECT
                    serial
                FROM
                    movimientos_producto_seriales
                WHERE
                    id_resumen = '[par_id_res_traslado_almacen]'
                    AND codigo_producto = '$codigo_productos'
                    AND empresa = '[usr_empresa]'
                    AND sucursal = '[usr_sucursal]'
                    AND id_resumen = '$var_id_resumen'"; 
                sc_lookup_field(rs_det_serial, $sql_det_serial);

                if (is_array($rs_det_serial) && count($rs_det_serial) > 0) {
                    foreach ($rs_det_serial as $row_serial){
                        // Check for page break before printing each serial
                        if ($pdf->GetY() + $font_atl > $pdf->GetPageHeight() - 30) {
                            $pdf->AddPage();
                            $pdf->SetY(15);
                        }
                        $pdf->SetX(10);
                        $pdf->Cell(17, $font_atl, utf8_decode($row_serial['serial']), 0,1,'L'); // Use 0,1 for line break after each serial
                    }
                }
            }
            $pdf->Ln(2); // Small line break after each product (or its serials) for better spacing
        }
    }

    // Foother
    // Mostrando TOTALES
    $pdf->SetFont($font_nom, 'B', 10);
    $font_atl= 0;
    $line_salto = 4;

    // Determine footer Y position
    $footer_start_y = $pdf->GetPageHeight() - 20; // Default position
    if ($pdf->GetY() > $footer_start_y) { // If current Y is already past footer start, add a new page
        $pdf->AddPage();
        $pdf->SetY(15); // Reset Y for new page
    }

    $colu_ini = 120;
    $ancho = 90;
    $ancho_campo = 23;

    if([par_incluir_precio] == 'SI'){
        $pdf->SetXY($colu_ini, $pdf->GetY() + $line_salto); // Use GetY() to continue from last printed position
        $pdf->MultiCell($ancho,$font_atl, utf8_decode('Total Costo: ' .str_pad(number_format({rs[0]['total_costo']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');
    }

    $pdf->SetXY($colu_ini, $pdf->GetY() + $line_salto);// Use GetY() to continue from last printed position
    $pdf->MultiCell($ancho,$font_atl, utf8_decode('Total Renglones: ' .str_pad(number_format({rs[0]['total_renglones']},2,',','.'),$ancho_campo,' ',STR_PAD_LEFT)), 0,'R');

    // Generar el archivo PDF
    $pdf->Output($nombre_reporte, 'I');
?>