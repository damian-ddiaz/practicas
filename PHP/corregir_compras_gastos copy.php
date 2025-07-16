<?php
// Actualizandos la tabla gastos_recurrentes_detalles en funcion de la tabla "gastos_recurrentes_facturas_resumen"
    sc_select(my_data_plantilla,"select id_gasto_resumen, fecha, fecha_fin,gasto_total from gastos_recurrentes_resumen where cierre = 1 and empresa = '[usr_empresa]' and sucursal='[usr_sucursal]'");

    if ({my_data_plantilla} === false){
        echo "Error al acceder a la base de datos =". {my_data_plantilla_erro};
    }
    else{
        while (!$my_data_plantilla->EOF){
       //     echo 'entrando en el ciclo';
            $id_gasto_resumen	= $my_data_plantilla->fields[0];//if_factura_resumen
            $fecha				= $my_data_plantilla->fields[1];//fecha
            $fecha_fin			= $my_data_plantilla->fields[2];//fecha_fin	
            $gasto_total		= $my_data_plantilla->fields[3];//gasto_totalo
            /***********************************************/
            // inicializando el saldo de las plantillas en 0
	//		echo 'COLOCANDO EN CERO'.$id_gasto_resumen.'<br>';

			
            sc_exec_sql("Update gastos_recurrentes_resumen
                        set gasto_total =  0.00 
                        where 
                        id_gasto_resumen = '$id_gasto_resumen'");
            
            sc_exec_sql("Update gastos_recurrentes_detalles
                        set gasto_concepto =  0.00 
                        where 
                        id_gastos_resumen = '$id_gasto_resumen'");
            
            sc_select(my_data2,"select id_factura_resumen,corr_interno,codigo_padre,codigo_hijo, total_neto,ip_estacion,empresa,sucursal,usuario from gastos_recurrentes_facturas_resumen where status IN ('APROBADO','APROBADO / PAGADO')
			AND corr_interno = '$id_gasto_resumen' and codigo_padre>0 and codigo_hijo <>' ' and fecha_emision between '$fecha' and '$fecha_fin'");

            if ({my_data2} === false){
                echo "Error al acceder a la base de datos =". {my_data2_erro};
            }
            else{
                while (!$my_data2->EOF){
                    $id_factura_resumen	= $my_data2->fields[0];//if_factura_resumen
                    $corr_interno		= $my_data2->fields[1];//corr_interno
                    $var_codigo_padre1	= $my_data2->fields[2];//codigo_padre	
                    $var_codigo_hijo1	= $my_data2->fields[3];//codigo_hijo
                    $total_neto 		= $my_data2->fields[4];//total_neto
                    $ip_estacion		= $my_data2->fields[5];//ip_estacion
                    $empe				= $my_data2->fields[6];//codigo_hijo
                    $sucu 				= $my_data2->fields[7];//total_neto
                    $usuario			= $my_data2->fields[8];//ip_estacion
                    // Insertango Gastos Concepto
                    sc_lookup_field(concepto, "SELECT codigo_cuenta_padre, codigo_cuenta_hijo 
                    from gastos_recurrentes_detalles
                WHERE 
                    id_gastos_resumen =  $corr_interno
                AND 
                    codigo_cuenta_hijo = '$var_codigo_hijo1' and codigo_cuenta_padre = $var_codigo_padre1");

                    if(!isset({concepto[0]['codigo_cuenta_hijo']})){//NO EXISTE
                // SQL statement parameters
                        $insert_table  = 'gastos_recurrentes_detalles';// Table name
                        $insert_fields = array( //Field list, add as many as needed
                        'id_gasto_detalles' => "'0'",
                        'id_gastos_resumen' => "'$corr_interno'",
                        'monto_permitido' => "'0.00'",
                        'codigo_cuenta_padre' => "'$var_codigo_padre1'",
                        'codigo_cuenta_hijo' => "'$var_codigo_hijo1'",
                        'usuario' => "'$usuario'",
                        'empresa' => "'$empe'",
                        'sucursal' => "'$sucu'",
                        'ip_estacion' => "'$ip_estacion'",
                        'gasto_concepto' => "'$total_neto'",
                        );
                        // Insert record
                        $insert_sql_detalles = 'INSERT INTO ' . $insert_table
                        . ' ('   . implode(', ', array_keys($insert_fields))   . ')'
                        . ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
                        sc_exec_sql($insert_sql_detalles);		
                    }else{
                        sc_exec_sql("Update gastos_recurrentes_detalles
                        set gasto_concepto =  gasto_concepto + $total_neto 
                        where codigo_cuenta_padre = '$var_codigo_padre1' AND 
                        codigo_cuenta_hijo = '$var_codigo_hijo1' AND 
                        id_gastos_resumen = '$corr_interno'"); 
                    }
                    $my_data2->MoveNext();
                }
                $my_data2->Close();
            }



            // ddiaz 30-10-2024
            // Actualizandos las compras en la tabla de gatos gastos_recurrentes_facturas_resumen y los saldos de los conceptos en gastos_recurrentes_detalles

            sc_select(my_data, "SELECT 
		cr.id_gasto_resumen,
		cr.id_compra,
		cr.empresa,
		cr.sucursal as sucursal2,
		cr.ip_estacion,
		cr.fecha_factura,
		cr.fecha_vencimiento,
		cr.fecha_registro, 
		cr.sucursal,
		cr.tasa_cambio,
		0.00 as porcentaje_desc,
		cr.tasa_iva,
		cr.base_exenta_bs,
		cr.subtotal,
		cr.sub_total_bs,
		cr.iva,
		cr.iva_bs,
		'APROBADO' as status,
		cr.descripcion,
		cr.codigo_hijo,
		cr.id_proveedor,
		cr.saldo,
		cr.total,
		cr.total_bolivares,
		cr.codigo_padre,
		cr.original,
		cr.base_exonera_bs,
		cr.usuario
				FROM compras_resumen cr
				LEFT JOIN gastos_recurrentes_facturas_resumen gr ON cr.id_compra = gr.id_compra
				WHERE gr.id_compra IS NULL  and  cr.estado = 'EN INVENTARIO' and cr.id_gasto_resumen > 0 and cr.codigo_padre >0 and cr.codigo_hijo <> '' AND cr.fecha_factura between '$fecha' and '$fecha_fin'");

            if ({my_data} === false){
                echo "Error al acceder a la base de datos =". {my_data_erro};
            }
            else{
                while (!$my_data->EOF){
                    $id_gasto_resumen	= $my_data->fields[0]; // id_gasto_resumen
                    $id_compra		= $my_data->fields[1]; // id_compra
                    $emp			= $my_data->fields[2]; // empresa
                    $sucursal		= $my_data->fields[3]; // sucursal
                    $ip_estacion	= $my_data->fields[4]; // ip_estacion
                    $fecha_factura	= $my_data->fields[5]; // fecha_factura
                    $fecha_vencimiento	= $my_data->fields[6]; // fecha_vencimientto
                    $fecha_registro	= $my_data->fields[7]; // fecha_registro
                    $sucursal		= $my_data->fields[8]; // sucursal
                    $tasa_cambio	= $my_data->fields[9]; // tasa_cambio
                    $porcentaje_desc= $my_data->fields[10]; // porcentaje_desc
                    $tasa_iva		= $my_data->fields[11]; // tasa_iva
                    $base_exenta_bs	= $my_data->fields[12]; // base_exenta_bs
                    $subtotal		= $my_data->fields[13]; // subtotal
                    $sub_total_bs	= $my_data->fields[14]; // sub_total_bs
                    $iva			= $my_data->fields[15]; // iva
                    $iva_bs			= $my_data->fields[16]; // iva_bs
                    $status			= $my_data->fields[17]; // status
                    $descripcion	= $my_data->fields[18]; // descripcion
                    $var_codigo_hijo2	= $my_data->fields[19]; // codigo_hijo
                    $id_proveedor	= $my_data->fields[20]; // id_proveedor
                    $saldo			= $my_data->fields[21]; // saldo
                    $total			= $my_data->fields[22]; // total		
                    $total_bolivares= $my_data->fields[23]; // total_bolivares
                    $var_codigo_padre2	= $my_data->fields[24]; // codigo_padre
                    $original		= $my_data->fields[25]; // id_original	
                    $base_exonera_bs= $my_data->fields[26]; // base_exonera_bs
                    $usuario		= $my_data->fields[27]; // base_exonera_bs
            //Buscando el concepto en la plantilla 
                
                    sc_lookup_field(dt_partida, "select id_gastos_resumen,codigo_cuenta_padre,codigo_cuenta_hijo, monto_permitido,gasto_concepto, monto_permitido - gasto_concepto as monto_restante from gastos_recurrentes_detalles where id_gastos_resumen =  '$id_gasto_resumen' 
                AND codigo_cuenta_hijo = '$var_codigo_hijo2' and codigo_cuenta_padre = '$var_codigo_padre2'");

            //Buscando el nombre de la empresa 
                    sc_lookup_field(empresa, "select descripcion from configuracion_empresa where codigo = '$emp'");

                    $nombre_empresa  = {empresa[0]['descripcion']};

                    //Para acceder a la primera línea (dataset), debemos informar:
                    if(!isset({dt_partida[0]['monto_permitido']})){
                        $var_monto_permitido = 0.00;
                    }else{	
                        $var_monto_permitido = {dt_partida[0]['monto_permitido']};
                    }

                    if(!isset({dt_partida[0]['monto_restante']})){
                        $var_monto_restante  = 0.00;
                    }else{
                        $var_monto_restante  = {dt_partida[0]['monto_restante']};
                    }

                    if(!isset({dt_partida[0]['gasto_concepto']})){
                        $var_gasto_concepto  = 0.00;
                    }else{
                        $var_gasto_concepto  = {dt_partida[0]['gasto_concepto']};
                    }
                    
                    
                    $insert_table_resumen  = 'gastos_recurrentes_facturas_resumen';      // Table name
                    $insert_fields_resumen = array( // Field list, add as many as needed
                        'corr_interno' => "'$id_gasto_resumen'",
                        'original' => "'$original'",
                        'id_compra' => "'$id_compra'",
                        'empresa2' => "'$nombre_empresa '",
                        'sucursal2' => "'$sucursal'",
                        'usuario' => "'$usuario'",
                        'ip_estacion' => "'$ip_estacion'",
                        'fecha' => "'$fecha_registro'",	
                        'codigo_padre' => "'$var_codigo_padre2'",
                        'fecha_emision' => "'$fecha_factura'",
                        'fecha_vencimiento' => "'$fecha_vencimiento'",
                        'fecha_reg' => "'$fecha_registro'",
                        'empresa' => "'$emp'",
                        'sucursal' => "'$sucursal'",
                        'tasa_cambio' => "'$tasa_cambio'",
                        'porcentaje_desc' => "'0.00'",
                        'monto_desc' => "'$var_monto_restante'",//Monto restante que queda en la partida
                        'porcentaje_iva' => "'$tasa_iva'",
                        'monto_exento' => "'$base_exenta_bs'",
                        'total_neto' => "'$subtotal'",
                        'total_neto_bs' => "'$sub_total_bs'",
                        'monto_iva' => "'$iva'",
                        'monto_iva_bs' => "'$iva_bs'",
                        'sub_total' => "'$subtotal'",
                        'status' => "'$status'",
                        'descripcion' => "'$descripcion'",
                        'sucursal' => "'$sucursal'",
                        'saldo' => "'$var_monto_permitido'",//saldo que queda en la partida
                        'codigo_hijo' => "'$var_codigo_hijo2'",
                        'gasto_concepto' => "'$total'",
                        'referencia' => "' '",
                        'codigo_proveedor' => "'$id_proveedor'",
                        'saldo_fact' =>  "'$saldo'",//saldo pendiente de la compra "'{saldo}'"
                        'total' => "'$total'",
                        'total_bs' => "'$total_bolivares'",
                        'base_exenta_bs' => "'$base_exenta_bs'",
                        'base_exonera_bs' => "'$base_exonera_bs'",
                        'usuario' => "'$usuario'",
                    );

                    // Insert record
                    $insert_sql_resumen = 'INSERT INTO ' . $insert_table_resumen
                        . ' ('   . implode(', ', array_keys($insert_fields_resumen))   . ')'
                        . ' VALUES ('    . implode(', ', array_values($insert_fields_resumen)) . ')';
                    sc_exec_sql($insert_sql_resumen);
            
                    // Insertango Gastos Concepto
                    sc_lookup_field(concepto, "SELECT codigo_cuenta_padre, codigo_cuenta_hijo 
                    from gastos_recurrentes_detalles
                WHERE 
                    id_gastos_resumen =  '$id_gasto_resumen'
                AND 
                    codigo_cuenta_hijo = '$var_codigo_hijo2' and codigo_cuenta_padre = '$var_codigo_padre2'");

                    if(!isset({concepto[0]['codigo_cuenta_hijo']})){//NO EXISTE
                // SQL statement parameters
                        $insert_table_detalles  = 'gastos_recurrentes_detalles';// Table name
                        $insert_fields_detalles = array( //Field list, add as many as needed
                        'id_gasto_detalles' => "'0'",
                        'id_gastos_resumen' => "'$id_gasto_resumen'",
                        'monto_permitido' => "'0.00'",
                        'codigo_cuenta_padre' => "'$var_codigo_padre2'",
                        'codigo_cuenta_hijo' => "'$var_codigo_hijo2'",
                        'usuario' => "'$usuario'",
                        'empresa' => "'$emp'",
                        'sucursal' => "'$sucursal'",
                        'ip_estacion' => "'$ip_estacion'",
                        'gasto_concepto' => "'$saldo'",
                        );
                        // Insert record
                        $insert_sql_detalles = 'INSERT INTO ' . $insert_table_detalles
                        . ' ('   . implode(', ', array_keys($insert_fields_detalles))   . ')'
                        . ' VALUES ('    . implode(', ', array_values($insert_fields_detalles)) . ')';
                        sc_exec_sql($insert_sql_detalles);				
                    }else{
                        sc_exec_sql("Update gastos_recurrentes_detalles
                        set gasto_concepto =  gasto_concepto + $total 
                        where codigo_cuenta_padre = '$var_codigo_padre2' AND 
                        codigo_cuenta_hijo = '$var_codigo_hijo2' AND 
                        id_gastos_resumen = '$id_gasto_resumen'");
                    }
                    $my_data->MoveNext();
                }
                $my_data->Close();
            }		
            /***********************************************/
            sc_lookup_field(platilla,"select id_gastos_resumen,codigo_cuenta_padre,codigo_cuenta_hijo, sum(gasto_concepto)  as gasto_total from gastos_recurrentes_detalles where id_gastos_resumen = $id_gasto_resumen");
            if(!isset({platilla[0]['id_gastos_resumen']})){
                $total_gasto = 0;
            }else{
                $total_gasto = {platilla[0]['gasto_total']};
            }
            sc_exec_sql("Update gastos_recurrentes_resumen
                        set gasto_total =  gasto_total + $total_gasto 
                        where 
                        id_gasto_resumen = $id_gasto_resumen");
        
            $my_data_plantilla->MoveNext();
        }
        $my_data_plantilla->Close();
    }
    ?>