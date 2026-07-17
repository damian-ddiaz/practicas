<?php
// --- INICIO: Estilos para una salida más clara ---
echo <<<HTML
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; }
    h1, h2, h3, h4, h5 { margin-top: 1.5em; }
    .proceso-header, .proceso-footer { 
        text-align: center; background-color: #f2f2f2; padding: 10px; border-bottom: 3px solid #4CAF50; border-top: 3px solid #4CAF50; 
    }
    .empresa { border-left: 5px solid #4CAF50; padding-left: 15px; margin-top: 20px; background-color: #f9f9f9; }
    .sucursal { border-left: 5px solid #2196F3; padding-left: 15px; margin-top: 15px; background-color: #fdfdff; }
    .banco { border-left: 5px solid #FF9800; padding-left: 15px; margin-top: 10px; }
    .fecha-proceso { border-top: 1px dashed #ccc; padding-top: 10px; margin-left: 20px; }
    p { margin: 5px 0 5px 20px; }
    .exito { color: #4CAF50; }
    .error { color: #f44336; font-weight: bold; }
    .aviso { color: #ff9800; }
    .info { color: #607d8b; font-style: italic; }
    .resumen { font-weight: bold; background-color: #eef; padding: 5px; border-top: 1px solid #ddd; margin-top: 10px; }
</style>
HTML;

// Inicia el procesamiento general
echo "<div class='proceso-header'><h1>Inicio del Proceso de Verificación de Comisiones</h1></div>";
echo "<p class='info'>ℹ️ El proceso determina la fecha de inicio dinámicamente para cada banco, comenzando desde su fecha más antigua configurada.</p>";

// 1. OBTENER EMPRESA
//sc_select(my_data_empresa, "SELECT codigo, descripcion FROM configuracion_empresa where empresa = 'J181228500' ORDER BY codigo");

sc_select(my_data_empresa, "SELECT codigo, descripcion FROM configuracion_empresa  ORDER BY codigo");

if ({my_data_empresa} === false) {
    echo "<h2 class='error'>❌ Error Crítico al Cargar Empresas</h2>";
    echo "<p class='error'>Mensaje del sistema: " . {my_data_empresa_erro} . "</p>";
} else {
    while (!{my_data_empresa}->EOF) {
        $emp = {my_data_empresa}->fields[0];
        $nombreEmp = {my_data_empresa}->fields[1];
        
        echo "<div class='empresa'>";
        echo "<h2>Iniciando Empresa: {$nombreEmp} ({$emp})</h2>";
        
        // 2. OBTENER SUCURSALES DE LA EMPRESA
        $cont_sucursales_ok = 0;
        sc_select(my_data_sucursal, "SELECT codigo, descripcion FROM configuracion_sucursal WHERE tipo_empresa = '$emp'");
        if ({my_data_sucursal} === false) {
            echo "<p class='error'>❌ Error al cargar sucursales. Mensaje: " . {my_data_sucursal_erro} . "</p>";
        } else {
            while (!{my_data_sucursal}->EOF) {
                $suc = {my_data_sucursal}->fields[0];
                $nombreSuc = {my_data_sucursal}->fields[1];

                echo "<div class='sucursal'>";
                echo "<h3>Sucursal: {$nombreSuc} ({$suc})</h3>";
                
                $periodo = periodoAbierto($emp, $suc);
                
                if($periodo['status']){
                    // 3. OBTENER BANCOS DE LA EMPRESA
                    $cont_bancos_proc = 0;
                    $cont_compras_sucursal = 0;
                    sc_select(my_data_bancos, "SELECT codigo_banco, id_proveedor, nombre_banco FROM bancos WHERE empresa = '$emp'");
                    if ({my_data_bancos} === false) {
                        echo "<p class='error'>❌ Error al cargar bancos. Mensaje: " . {my_data_bancos_erro} . "</p>";
                    } else {
                        while (!{my_data_bancos}->EOF) {
                            $codigo_banco = {my_data_bancos}->fields[0];
                            $id_proveedor = {my_data_bancos}->fields[1];
                            $nombre_banco = {my_data_bancos}->fields[2];
                            
                            echo "<div class='banco'>";
                            echo "<h4>Banco: {$nombre_banco}</h4>";

                            $cont_dias_proc = 0;
                            $cont_compras_banco = 0;

                            // Verificaciones del proveedor...
                            if (empty($id_proveedor) || $id_proveedor == 0) {
                                echo "<p class='aviso'>⚠️ <strong>AVISO:</strong> Proveedor no asignado. Saltando este banco.</p>";
                                {my_data_bancos}->MoveNext(); echo "</div>"; continue;
                            }
                            sc_lookup(rs_proveedores_datos, "SELECT nombre_proveedor, codigo_proveedor, direccion_proveedor, telefono_movil FROM proveedores_datos WHERE id_proveedor = $id_proveedor");
                            if (empty({rs_proveedores_datos})) {
                                echo "<p class='aviso'>⚠️ <strong>AVISO:</strong> El proveedor con ID {$id_proveedor} no fue encontrado. Saltando este banco.</p>";
                                {my_data_bancos}->MoveNext(); echo "</div>"; continue;
                            }
                            $proveedor = {rs_proveedores_datos[0][0]}; 
							$codigo_proveedor = {rs_proveedores_datos[0][1]}; 
							$direccion_proveedor = {rs_proveedores_datos[0][2]}; 
							$telefono_movil = {rs_proveedores_datos[0][3]};

                            // DETERMINAR LA FECHA DE INICIO PARA ESTE BANCO ESPECÍFICO
                            $query_fecha_inicio = "SELECT MIN(fecha_inicio_comision) FROM banco_formas_pago WHERE generar_comision_bancaria_cxp = 1 AND empresa = '$emp' AND sucursal = '$suc' AND codigo_banco = '$codigo_banco' AND fecha_inicio_comision IS NOT NULL";
                            sc_lookup(rs_fecha_inicio, $query_fecha_inicio);

                            if (empty({rs_fecha_inicio}) || empty({rs_fecha_inicio[0][0]})) {
                                echo "<p class='info'>ℹ️ No se encontró configuración de fecha de inicio de comisión. Saltando banco.</p>";
                                {my_data_bancos}->MoveNext(); 
								echo "</div>"; 
								continue;
                            }

                            $fecha_inicio_str = {rs_fecha_inicio[0][0]};
                            echo "<p class='info'>ℹ️ Fecha de inicio de comisión para este banco: <strong>{$fecha_inicio_str}</strong></p>";

                            try {
                                $fecha_inicio = new DateTime($fecha_inicio_str);
                                $fecha_fin = new DateTime(); 
                                $fecha_fin->modify('+1 day');
                                if ($fecha_inicio >= $fecha_fin) {
                                    echo "<p class='info'>ℹ️ La fecha de inicio está en el futuro. No hay nada que procesar.</p>";
                                    {my_data_bancos}->MoveNext(); echo "</div>"; continue;
                                }
                                $intervalo = new DateInterval('P1D');
                                $periodo_fechas = new DatePeriod($fecha_inicio, $intervalo, $fecha_fin);
                            } catch (Exception $e) {
                                echo "<p class='error'>❌ <strong>ERROR:</strong> Formato de fecha de inicio inválido ('{$fecha_inicio_str}'). Mensaje: ". $e->getMessage() ."</p>";
                                {my_data_bancos}->MoveNext(); echo "</div>"; continue;
                            }

                            // BUCLE DE FECHAS ANIDADO
                            foreach ($periodo_fechas as $fecha) {
                                $fecha_actual_str = $fecha->format('Y-m-d');
                                $contexto_log = "[Emp: {$emp}, Suc: {$suc}, Banco: {$codigo_banco}]";
                                
                                echo "<div class='fecha-proceso'>";
                                echo "<h5>✔️ Procesando Día: {$fecha_actual_str}</h5>";
                                $cont_dias_proc++;

                                // OBTENER TASA DE CAMBIO
                                $tasa = 0;
                                if (consultar_valor_regla('tasa_de_cambio_automatica', $emp) == 'NO') { 
									$tasa = consultar_valor_regla('tasa_de_cambio', $emp); 
								} 
								else { 
									$tasa = tasa_cambio_bcv(); 
								}
								
                                if (empty($tasa) || $tasa == 0) {
                                    echo "<p class='error'>❌ Tasa de cambio no pudo ser obtenida para el día {$fecha_actual_str}. Saltando este día.</p>";
                                    echo "</div>"; continue;
                                }
                                
								$monto_total_banco = 0; 
								$primer_producto_encontrado = null; 
								$comision_a_aplicar = 0; 
								$monedaConvertible = 'NO';
								
								// 4. OBTENER Y RECORRER FORMAS DE PAGO PARA ACUMULAR EL MONTO
								$query_formas_pago = "SELECT codigo_formas_pago, codigo_tipo_pago, id_producto, moneda_convertible FROM banco_formas_pago WHERE generar_comision_bancaria_cxp = 1 AND empresa = '$emp' AND sucursal = '$suc' AND codigo_banco = '$codigo_banco'";
								sc_select(my_data_formas_pagos, $query_formas_pago);

								if({my_data_formas_pagos} === false){ 
									echo "<p class='error'>❌ {$contexto_log} Error al buscar formas de pago. Mensaje: ".{my_data_formas_pagos_erro}."</p>"; 
								} 
								else {
									while (!{my_data_formas_pagos}->EOF) {
										$codigo_formas_pago = {my_data_formas_pagos}->fields[0]; 
										$codigo_tipo_pago = {my_data_formas_pagos}->fields[1]; 
										$id_producto = {my_data_formas_pagos}->fields[2]; 
										$monedaConvertible = {my_data_formas_pagos}->fields[3]; 
										

										sc_lookup(rs_inventario_productos, "SELECT nombre_productos, impuesto_productos, codigo_productos, codigo_padre, codigo_hijo FROM inventario_productos WHERE id_productos = $id_producto");
										if (empty({rs_inventario_productos})) {
											{my_data_formas_pagos}->MoveNext();
											continue; 
										}
										
										if ($primer_producto_encontrado === null) {
											$primer_producto_encontrado = [ 
												'nombre' => {rs_inventario_productos[0][0]}, 
												'impuesto' => {rs_inventario_productos[0][1]}, 
												'codigo' => {rs_inventario_productos[0][2]}, 
												'codigo_padre' => {rs_inventario_productos[0][3]}, 
												'codigo_hijo' => {rs_inventario_productos[0][4]} 
											];
										}
										
										$query_suma = "SELECT 
											COALESCE(SUM(ABS(ip.monto)), 0.00) AS total_comision
											FROM ventas_transacciones_detalles vt
											INNER JOIN banco_conciliacion_importarcion ip ON ip.tipo = 'D' AND RIGHT(ip.referencia, 8) = RIGHT(vt.referencia, 8)  AND ip.empresa = vt.empresa AND ip.codigo_banco = '$codigo_banco'
											WHERE vt.forma_pago = '$codigo_formas_pago' AND vt.tipo_pago = '$codigo_tipo_pago' AND vt.empresa = '$emp' AND vt.sucursal = '$suc' AND DATE(vt.fecha_transaccion) = '$fecha_actual_str' AND vt.comision_procesada = 0 AND vt.status = 'FACTURADO'"; 
										sc_lookup(rs_suma, $query_suma); 
										if ({rs_suma} !== false && !empty({rs_suma}) && {rs_suma[0][0]} > 0) { 
											$monto_total_banco += {rs_suma[0][0]}; 
										} 
										
										
										{my_data_formas_pagos}->MoveNext();
									}
									{my_data_formas_pagos}->Close();
								}
								
								$montoValidar = 0;

								if ($monto_total_banco > 0) { 
									$monto_comision_total = $monto_total_banco;
									$montoValidar = $monto_comision_total;
								}
                                echo $montoValidar;
                                
                                // --- CREACIÓN DE LA COMPRA ÚNICA ---
                                if ($montoValidar > 0 && $primer_producto_encontrado !== null) {
									

                                    if($monedaConvertible == 'SI'){
										$monto_om = round($monto_total_banco/$tasa,2);
										$monto_comision_total_bs = $monto_total_banco;
										$moneda = 'BOLÍVARES';
                                        echo "<p>✔️ <strong>Acción:</strong> Generando Factura de Compra. Total comisión: <strong>" . number_format($monto_comision_total_bs, 2, ',', '.') . " Bs.</strong></p>";
                                    } 
									else {
										$monto_om = $monto_total_banco;
										$monto_comision_total_bs = round($monto_total_banco*$tasa,2);
										$moneda = 'DOLARES';
                                        echo "<p>✔️ <strong>Acción:</strong> Generando Factura de Compra. Total comisión: <strong>" . number_format($monto_om, 2, ',', '.') . " USD</strong></p>";
                                    }
									$numero_factura_unico = "'CB" . $fecha->format('Ymd') . "-" . $codigo_banco . "'"; 
									$corr = buscar_correlativo('FACTURC', $emp, $suc);
/*Damian Diaz 26-02-2026*/
/*Buscando el Peiodo Aceual, es decir, el Abierto*/
		sc_lookup_field(periodo_act, "select id_gasto_resumen, fecha, fecha_fin, cierre from gastos_recurrentes_resumen where empresa = '$emp' AND sucursal = '$suc' and cierre=1");

	if({periodo_act[0]['id_gasto_resumen']} > 0){
		$var_id_gasto_resumen  = {periodo_act[0]['id_gasto_resumen']};
	}
									
									// ... [LÓGICA DE INSERT DEL RESUMEN DE COMPRA] ... (sin cambios)
									$insert_table_resumen  = 'compras_resumen'; 
									$insert_fields_resumen = [ 
										'numero_factura' => $numero_factura_unico, 
										'numero_control' => $numero_factura_unico, 
										'proveedor' => "'$proveedor'", 
										'descripcion' => "'Comision Bancaria - $nombre_banco - {$fecha_actual_str}'", 
										'fecha_factura' => "NOW()", 
										'fecha_vencimiento' => "NOW()", 
										'fecha_registro' => "NOW()", 
										'moneda' => "'$moneda'", 
										'tasa_cambio' => "'$tasa'", 
										'subtotal' => "'$monto_om'", 
										'IVA' => "'0'", 
										'total' => "'$monto_om'", 
										'usuario' => "'SISTEMA'", 
										'empresa' => "'$emp'", 
										'sucursal' => "'$suc'", 
										'total_bolivares' => "'$monto_comision_total_bs'", 
										'codigo_proveedor' => "'$codigo_proveedor'", 
										'estado' => "'EN INVENTARIO'", 
										'saldo' => "'0'", 
										'id_proveedor' => "'$id_proveedor'", 
										'direccion' => "'$direccion_proveedor'", 
										'telefono' => "'$telefono_movil'", 
										'sub_total_bs' => "'$monto_comision_total_bs'", 
										'base_exenta_bs' => "'$monto_comision_total_bs'", 
										'numero' => "'$corr'", 
										'ip_estacion' => "'SISTEMA'", 
										'descuento' => 0, 
										'cod_almacen' => "''", 
										'cod_ubicacion' => "''", 
										'cod_nivel' => "''", 
										'compras_proveedor' => "''", 
										'tasa_iva' => "'0'", 
										'tasa_iva_redu' => "'0'", 
										'iva_bs' => "'0'", 
										'iva_reduc_bs' => "'0'", 
										'iva_ret' => "'0'", 
										'iva_ret_porc' => "'0'", 
										'nacio_extran' => "''", 
										'base_impo_bs' => "'0'", 
										'base_exonera_bs' => "'0'", 
										'base_alicu_redu_bs' => "'0'", 
										'original' => "'NO'",
										'id_gasto_resumen' => "'$var_id_gasto_resumen'"
									];
									$insert_sql_resumen = 'INSERT INTO ' . $insert_table_resumen . ' (' . implode(', ', array_keys($insert_fields_resumen)) . ') VALUES (' . implode(', ', array_values($insert_fields_resumen)) . ')';
									sc_exec_sql($insert_sql_resumen); sc_commit_trans();
									sc_lookup(rs_last_id, "SELECT LAST_INSERT_ID()"); $lastID = !empty({rs_last_id}) ? {rs_last_id[0][0]} : 0;

                                    if ($lastID > 0) {
										if(!empty($primer_producto_encontrado['codigo_padre']) AND !empty($primer_producto_encontrado['codigo_hijo'])){
											$montosPlantilla = montosPlantillaGasto($periodo['id_gasto_resumen'], $primer_producto_encontrado['codigo_padre'], $primer_producto_encontrado['codigo_hijo']);

											if($montosPlantilla['status']){
												$montoPermitido = $montosPlantilla['monto_permitido'];
												$montoDisponibleUpdate = $montosPlantilla['monto_concepto'] + $monto_om;
												$montoDisponible = $montosPlantilla['monto_permitido'] - $montoDisponibleUpdate;
												sc_exec_sql ("UPDATE gastos_recurrentes_detalles SET gasto_concepto = $montoDisponibleUpdate WHERE id_gasto_detalles = '".$montosPlantilla['id_gasto_detalles']."'");
												echo "<p class='info' style='margin-left: 40px;'>ℹ️ <strong>Presupuesto:</strong> Validado contra plantilla de gastos. Permitido: " . number_format($montoPermitido, 2, ',', '.') . " USD, Disponible ahora: " . number_format($montoDisponible, 2, ',', '.') . " USD.</p>";
											} else {
												$montoPermitido = 0; $montoDisponibleUpdate = $monto_om; $montoDisponible = $monto_om;
												
												// *** INICIO DE CAMBIO EN MENSAJE ***
												echo "<p class='aviso' style='margin-left: 40px;'>⚠️ <strong>Presupuesto:</strong> No se encontró una entrada para este concepto. ✔️ Se creará un nuevo registro en la plantilla de gastos.</p>";
												// *** FIN DE CAMBIO EN MENSAJE ***
												
												$insert_table_gasto_detalle  = 'gastos_recurrentes_detalles';
												$insert_fields_gasto_detalle = array( 'id_gastos_resumen' => "'".$periodo['id_gasto_resumen']."'", 'monto_permitido' => "'$montoPermitido'", 'codigo_cuenta_padre' => "'".$primer_producto_encontrado['codigo_padre']."'", 'codigo_cuenta_hijo' => "'".$primer_producto_encontrado['codigo_hijo']."'", 'usuario' => "'SISTEMA'", 'empresa' => "'$emp'", 'sucursal' => "'$suc'", 'ip_estacion' => "'SISTEMA'", 'gasto_concepto' => "'".$montoDisponibleUpdate."'");
												$insert_sql_gasto_detalle = 'INSERT INTO ' . $insert_table_gasto_detalle . ' ('   . implode(', ', array_keys($insert_fields_gasto_detalle))   . ')' . ' VALUES ('    . implode(', ', array_values($insert_fields_gasto_detalle)) . ')';
												sc_exec_sql($insert_sql_gasto_detalle);
											}
										} else {
											$montoPermitido = 0; $montoDisponibleUpdate = 0; $montoDisponible = 0;
											echo "<p class='info' style='margin-left: 40px;'>ℹ️ <strong>Presupuesto:</strong> Producto de comisión no está asociado a una plantilla de gastos. Se omite la validación.</p>";
										}
										
										// ... [LÓGICA DE INSERTS DE DETALLE, CXP Y TRANSACCIONES] ... (sin cambios)
										$insert_table_detalle  = 'compras_detalles'; $insert_fields_detalle = [ 'id_compra' => "'$lastID'", 'estado' => "'EN INVENTARIO'", 'nombre_producto' => "'" . $primer_producto_encontrado['nombre'] . "'", 'codigo_producto' => "'" . $primer_producto_encontrado['codigo'] . "'", 'impuesto_productos' => "'" . $primer_producto_encontrado['impuesto'] . "'", 'precio_unitario' => "'$monto_om'", 'cantidad' => "'1'", 'subtotal_renglon' => "'$monto_om'", 'total_renglon' => "'$monto_om'", 'empresa' => "'$emp'", 'sucursal' => "'$suc'", 'usuario' => "'SISTEMA'", 'fecha' => "NOW()", 'tasa_cambio' => "'$tasa'", 'precio_unitario_bs' => "'$monto_comision_total_bs'", 'subtotal_renglon_bs' => "'$monto_comision_total_bs'", 'total_renglon_bs' => "'$monto_comision_total_bs'", 'tipo_impuesto' => "'0.00'", 'ip_estacion' => "'SISTEMA'", 'tipo_unidad' => "'UN'", 'iva' => "'0'", 'iva_total' => "'0'", 'total_iva_bs' => "'0'", 'monto_permitido' => "$montoPermitido", 'monto_disponible' => "$montoDisponible" ];
										$insert_sql_detalle = 'INSERT INTO ' . $insert_table_detalle . ' (' . implode(', ', array_keys($insert_fields_detalle)) . ') VALUES (' . implode(', ', array_values($insert_fields_detalle)) . ')';
										sc_exec_sql($insert_sql_detalle); sc_commit_trans();
										
										$insert_table_cxp  = 'cxp_documentos'; $insert_fields_cxp = [ 'tipo_documento' => "'FACTURC'", 'numero_documento' => "'$corr'", 'nro_fiscal' => "$numero_factura_unico", 'nro_control' => "$numero_factura_unico", 'descripcion' => "'Comision Bancaria - $nombre_banco - $fecha_actual_str'", 'cod_proveedor' => "'$codigo_proveedor'", 'sub_total' => "'$monto_om'", 'total_neto' => "'$monto_om'", 'saldo' => "'0'", 'tasa_cambio' => "'$tasa'", 'fecha_emision' => "NOW()", 'fecha_vencimiento' => "NOW()", 'tipo_documento_afect' => "''", 'numero_documento_afect' => "''", 'estatus' => "'PROCESADO'", 'tipo' => "'AUTOMATICO'", 'fecha' => "NOW()", 'usuario' => "'SISTEMA'", 'empresa' => "'$emp'", 'sucursal' => "'$suc'", 'ip_estacion' => "'SISTEMA'", 'id_proveedor' => "'$id_proveedor'", 'id_compra' => "'$lastID'" ];
										$insert_sql_cxp = 'INSERT INTO ' . $insert_table_cxp . ' (' . implode(', ', array_keys($insert_fields_cxp)) . ') VALUES (' . implode(', ', array_values($insert_fields_cxp)) . ')';
										sc_exec_sql($insert_sql_cxp);
										
										$query_detalles_transacciones = " SELECT ip.monto, vtd.referencia, vtd.tipo_pago, vtd.forma_pago, idBanco(vtd.tipo_pago, vtd.forma_pago, vtd.empresa, vtd.sucursal), vtd.fecha_transaccion, vtd.monto, vtd.id_ventas_transacciones_detalles, moneda_convertible FROM ventas_transacciones_detalles vtd INNER JOIN banco_formas_pago fp ON fp.codigo_formas_pago = vtd.forma_pago 
	AND fp.codigo_tipo_pago = vtd.tipo_pago 
	AND fp.sucursal = vtd.sucursal 
	AND fp.empresa = vtd.empresa INNER JOIN banco_conciliacion_importarcion ip ON ip.tipo = 'D' AND RIGHT(ip.referencia, 8) = RIGHT(vtd.referencia, 8) AND ip.empresa = vtd.empresa AND ip.codigo_banco = fp.codigo_banco WHERE vtd.empresa = '$emp' AND vtd.status = 'FACTURADO' AND vtd.sucursal = '$suc' AND DATE(vtd.fecha_transaccion) = '$fecha_actual_str' AND vtd.comision_procesada = 0 AND EXISTS ( SELECT 1 FROM banco_formas_pago bfp WHERE bfp.codigo_banco = '$codigo_banco' AND bfp.empresa = vtd.empresa AND bfp.sucursal = vtd.sucursal AND bfp.generar_comision_bancaria_cxp = 1 AND bfp.codigo_formas_pago = vtd.forma_pago AND bfp.codigo_tipo_pago = vtd.tipo_pago )";
										sc_select(my_data_detalles, $query_detalles_transacciones);
										
										if({my_data_detalles} === false){ echo "<p class='error'>❌ ERROR al obtener detalles de transacciones. Mensaje: ".{my_data_detalles_erro}."</p>"; } else {
											$contador_detalles = 0;
											while (!{my_data_detalles}->EOF) {
												$referencia_detalle = {my_data_detalles}->fields[1];
												$tipo_pago_detalle = {my_data_detalles}->fields[2]; 
												$forma_pago_detalle = {my_data_detalles}->fields[3]; 
												$id_banco = {my_data_detalles}->fields[4]; 
												$fecha_transaccion = {my_data_detalles}->fields[5]; 
												$id_ventas_transacciones_detalles = {my_data_detalles}->fields[7];
												$monedaConvert = {my_data_detalles}->fields[8];
												$montoDetalle = {my_data_detalles}->fields[0];
												
												if($monedaConvert == 'SI'){
													$monto_om_detalle = round($montoDetalle/$tasa,2);
													$monto_bs_detalle = $montoDetalle;
												}
												else{
													$monto_om_detalle = $montoDetalle;
													$monto_bs_detalle = round($montoDetalle*$tasa,2);
												}
													
													
												$insert_table_trans_detalle = 'compras_transacciones_detalles'; 
												$insert_fields_trans_detalle = [ 
													'id_compra' => $lastID, 
													'tipo_pago' => "'$tipo_pago_detalle'", 
													'forma_pago' => "'$forma_pago_detalle'", 
													'cod_forma_pago' => "'$forma_pago_detalle'", 
													'referencia' => "'$referencia_detalle'",
													'descripcion' => "'Pago Comisión Bancaria'",
													'monto' => $monto_om_detalle, 
													'monto_bs' => $monto_bs_detalle,
													'fecha_transaccion' => "'$fecha_transaccion'", 
													'fecha' => "NOW()", 
													'tasa_cambio' => $tasa,
													'conciliado' => "'NO'",
													'revisado' => "'NO'",
													'status' => "'PROCESADO'", 
													'tipo_movimiento' => "'D'",
													'empresa' => "'$emp'",
													'sucursal' => "'$suc'",
													'usuario' => "'SISTEMA'", 
													'ip_estacion' => "'SISTEMA'", 
													'id_banco' => "'$id_banco'", 
													'nro_conciliacion' => "''", 
													'tipo_conciliacion' => "''" 
												];
												$insert_sql_trans_detalle = 'INSERT INTO ' . $insert_table_trans_detalle . ' (' . implode(', ', array_keys($insert_fields_trans_detalle)) . ') VALUES (' . implode(', ', array_values($insert_fields_trans_detalle)) . ')';
												sc_exec_sql($insert_sql_trans_detalle);
												sc_exec_sql ("UPDATE ventas_transacciones_detalles SET comision_procesada = 1 WHERE id_ventas_transacciones_detalles = $id_ventas_transacciones_detalles");
												$contador_detalles++; {my_data_detalles}->MoveNext();
											}
											{my_data_detalles}->Close();
											
                                            echo "<p class='exito' style='margin-left: 40px;'>✅ <strong>Éxito:</strong> Factura de Compra #{$lastID} creada.</p>";
                                            echo "<p class='info' style='margin-left: 40px;'>ℹ️ Se asociaron {$contador_detalles} transacciones a esta compra.</p>";
											
                                            suma_correlativo('FACTURC', $emp, $suc); sc_commit_trans();
                                            $cont_compras_banco++; $cont_compras_sucursal++;
                                        }
                                    } else {
                                        echo "<p class='error'>❌ <strong>CRÍTICO:</strong> Se intentó crear la compra pero no se pudo obtener el ID de la inserción.</p>";
                                    }
                                } else {
                                    echo "<p class='info'>ℹ️ No se encontraron transacciones pendientes de comisión para este día.</p>";
                                }
                                echo "</div>"; // Cierre de .fecha-proceso
                            } // Fin del bucle de fechas

                            echo "<p class='resumen'><b>Resumen Banco {$nombre_banco}:</b> Se procesaron <b>{$cont_dias_proc}</b> día(s) y se generaron <b>{$cont_compras_banco}</b> factura(s) de compra.</p>";
                            echo "</div>"; // Cierre de .banco
                            {my_data_bancos}->MoveNext();
                            $cont_bancos_proc++;
                        }
                        {my_data_bancos}->Close();
                    }
                    
                    echo "<p class='resumen'><b>Resumen Sucursal {$nombreSuc}:</b> Se procesaron <b>{$cont_bancos_proc}</b> banco(s) y se generaron un total de <b>{$cont_compras_sucursal}</b> factura(s) de compra.</p>";

                } else {
                    echo "<p class='aviso'>⚠️ Periodo contable cerrado para esta sucursal. No se procesará.</p>";
                }
                echo "</div>"; // Cierre de .sucursal
                {my_data_sucursal}->MoveNext();
                $cont_sucursales_ok++;
            }
            {my_data_sucursal}->Close();
        }
        echo "</div>"; // Cierre de .empresa
        {my_data_empresa}->MoveNext();
    }
    {my_data_empresa}->Close();
}

echo "<div class='proceso-footer'><h1>Fin del Proceso</h1></div>";
?>