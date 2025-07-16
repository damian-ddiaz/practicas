<?php
    sc_include_library("sys", "fpdf", "fpdf.php", true, true);
    sc_include_library("sys", "html2pdf", "html2pdf.php", true, true);
    
    $nombre_reporte = 'Contrato de Cliente';
    
    // Buscar datos de la empresa
        $sql_empresa ="SELECT
            descripcion as empresa,
            numero_identificacion,
            CONCAT_WS('', UPPER(SUBSTRING(direccion, 1, 1)), LOWER(SUBSTRING(direccion, 2))) as direccion,
            telefono
        FROM
            configuracion_empresa 
        WHERE
            codigo = '[usr_empresa]'";
        sc_lookup_field(rs_empresa, $sql_empresa);
    
        // Buscar datos del contrato
        $sql_contrato ="SELECT
        id_contrato,
        descripcion_contrato,
        header_imagen_izquierdo,
        header_text_izquierdo,
        header_imagen_centro,
        header_text_centro,
        header_imagen_derecho,
        header_text_derecho,
        body,
        footer_imagen_izquierdo,
        footer_text_izquierdo,
        footer_imagen_centro,
        footer_text_centro,
        footer_text_derecho,
        footer_imagen_derecho 
        FROM
            configuracion_contratos 
        WHERE
            empresa = '[usr_empresa]' and id_contrato = '[par_id_contrato]'";
        sc_lookup_field(rs_contrato, $sql_contrato);
    
        // Buscar variables fijas
        $sql_contrato_variables ="SELECT
        id_contrato,
        nombre_variable_campo,
        valor_variable_campo
        FROM
            configuracion_contratos_variables_campos 
        WHERE
            empresa = '[usr_empresa]' and tipo = 'V' and id_contrato = '[par_id_contrato]'";
        sc_lookup_field(rs_contrato_variables, $sql_contrato_variables);
    
        // Buscar variables dinámicas de tablas
        $sql_contrato_tablas_campos ="SELECT
            descripcion_variable_campo, 
            nombre_tabla, 
            nombre_variable_campo
        FROM
            configuracion_contratos_variables_campos 
        WHERE
            empresa = '[usr_empresa]' AND tipo = 'C' AND id_contrato = '[par_id_contrato]'";
        sc_lookup_field(rs_contrato_tablas_campos, $sql_contrato_tablas_campos);
    
        // Crear un nuevo objeto PDF
        $pdf = new PDF_HTML('P', 'mm', 'Letter');
    
        // Función para agregar el header en la página
        function add_header($pdf) {
           $line_ini = 13; // Posición inicial del header
           $pdf->SetFont('Arial', '', 10);
    
           $resultado_header_izquierdo = obtener_imagen_header_izquierdo([par_id_contrato]);
           $nombre_archivo_izquierdo = $resultado_header_izquierdo['nombre_archivo'];
           $texto_header_izquierdo = $resultado_header_izquierdo['header_text'];
           if (!empty($nombre_archivo_izquierdo)) {
                $pdf->Image($nombre_archivo_izquierdo, 8, $line_ini, 40, 7);
                unlink($nombre_archivo_izquierdo);
           }
           $pdf->SetXY(8, $line_ini + 10);
           $pdf->MultiCell(60, $font_atl, utf8_decode($texto_header_izquierdo), 0,'L');
        
            
            $resultado_header_central = obtener_imagen_header_central([par_id_contrato]);
          $nombre_archivo_central = $resultado_header_central['nombre_archivo'];
          $texto_header_central = $resultado_header_central['header_text'];
          if (!empty($nombre_archivo_central)) {
                $pdf->Image($nombre_archivo_central, 90, $line_ini, 40, 7);
                unlink($nombre_archivo_central);
          }		
          $pdf->SetXY(82, $line_ini + 10);
          $pdf->MultiCell(60, $font_atl, utf8_decode($texto_header_central), 0,'C');		
            
            
            $resultado_header_derecho = obtener_imagen_header_derecho([par_id_contrato]);
          $nombre_archivo_derecho = $resultado_header_derecho['nombre_archivo'];
          $texto_header_derecho = $resultado_header_derecho['header_text'];
          if (!empty($nombre_archivo_derecho)) {
                $pdf->Image($nombre_archivo_derecho, 164, $line_ini, 40, 7);
                unlink($nombre_archivo_derecho);
          }		
          $pdf->SetXY(146, $line_ini + 10);
          $pdf->MultiCell(60, $font_atl, utf8_decode($texto_header_derecho), 0,'R');			
        }
    
        // Función para agregar el footer en la página
        function add_footer($pdf) {
            $line_ini = 256; // Posición inicial del footer
    
            // Agregar las imágenes del footer (Izquierdo, Central y Derecho)
            $nombre_archivo = obtener_imagen_footer_izquierdo([par_id_contrato]);
            if (!empty($nombre_archivo)) {
                $pdf->Image($nombre_archivo, 5, $line_ini, 40, 7);
                unlink($nombre_archivo);
            }
    
            $nombre_archivo = obtener_imagen_footer_central([par_id_contrato]);
            if (!empty($nombre_archivo)) {
                $pdf->Image($nombre_archivo, 90, $line_ini, 40, 7);
                unlink($nombre_archivo);
            }
    
            $nombre_archivo = obtener_imagen_footer_derecho([par_id_contrato]);
            if (!empty($nombre_archivo)) {
                $pdf->Image($nombre_archivo, 168, $line_ini, 40, 7);
                unlink($nombre_archivo);
            }
    
            // Agregar los textos del footer
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(5, $line_ini + 4);
            $pdf->MultiCell(60, 3, utf8_decode({rs_contrato[0]['footer_text_izquierdo']}), 0, 'L');
            $pdf->SetXY(82, $line_ini + 4);
            $pdf->MultiCell(60, 3, utf8_decode({rs_contrato[0]['footer_text_centro']}), 0, 'C');
            $pdf->SetXY(152, $line_ini + 4);
            $pdf->MultiCell(60, 3, utf8_decode({rs_contrato[0]['footer_text_derecho']}), 0, 'R');
        }
    
        // Crear una página inicial
        $pdf->AddPage();
    
        // Agregar el Header en la primera página
        add_header($pdf);
    
        // ********************* B O D Y ********************* //
        $font_atl = 5;
        $contenido_final = {rs_contrato[0]['body']};
    
        // Remplazando Variables Fijas
        if (is_array($rs_contrato_variables) && count($rs_contrato) > 0) {
            foreach ($rs_contrato_variables as $row) {
                $contenido_final = str_replace($row['nombre_variable_campo'], $row['valor_variable_campo'], $contenido_final);
            }
        }
    
        // Remplazando Variables de Tablas
        if (is_array($rs_contrato_tablas_campos) && count($rs_contrato) > 0 && [par_id_cliente] > 0) {
            foreach ($rs_contrato_tablas_campos as $row) {
                $var_sql = 'SELECT ' . $row['nombre_variable_campo'] . ' as valor_variable_campo FROM ' . $row['nombre_tabla'] . ' WHERE id_otros_clientes = ' . [par_id_cliente];
                sc_lookup_field(rs_var_sql, $var_sql);
    
                if (is_array($rs_var_sql) && count($rs_var_sql) > 0) {
                    foreach ($rs_var_sql as $var_row) {
                        $contenido_final = str_replace($row['nombre_variable_campo'], $var_row['valor_variable_campo'], $contenido_final);
                    }
                }
            }
        }
    
        // Escribir el contenido del body
        $pdf->SetXY(5, $line_ini + 10);
        $pdf->WriteHTML_Justify(utf8_decode($contenido_final));
    
        // Comprobamos si el contenido excede la página
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();  // Solo agregar una página nueva si el contenido excede el límite
        //    add_header($pdf);  // Agregar header en la nueva página
            $pdf->SetXY(5, $line_ini + 10);
            $pdf->WriteHTML_Justify(utf8_decode($contenido_final));  // Escribir el contenido en la nueva página
        }
    
        // Agregar el footer solo en la última página
      //  add_footer($pdf);
    
        // Salida del PDF
        $pdf->Output($nombre_reporte, 'I');
?>