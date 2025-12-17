'<?php
	$check_sql = "SELECT
	tipo,
	referencia,
	monto,
	id_banco,
	id_banco_conciliacion_importacion
	FROM
	banco_conciliacion_importarcion
	where empresa = '{empresa}' 
	AND conciliado = 'NO'
	AND id_banco = {id_banco} 
	AND fecha_banco <= '{fecha_hasta}'
	Limit 100";
	sc_select(my_data, $check_sql);

	if ({my_data} === false){
		sc_alert("Hubo un error al ejecutar la consulta");
	}else{
		sc_begin_trans();
		while (!$my_data->EOF){
			$var_tipo 								= $my_data->fields[0];
			$var_referencia_conciliacion			= $my_data->fields[1];
			$var_monto_conciliacion					= $my_data->fields[2];
			$var_id_banco							= $my_data->fields[3];
			$var_id_banco_conciliacion_importacion	= $my_data->fields[4];

			sc_lookup_field(banco_moneda, "SELECT codigo_moneda 
			from bancos 
			WHERE id = {id_banco}");
			$var_codigo_moneda = {banco_moneda[0]['codigo_moneda']};
					
			if($var_codigo_moneda == '0001'){ // Bolivares
				sc_lookup_field(vista_conciliacion, "SELECT modulo, tipo, referencia, monto, monto_bs 
				from Vista_movimiento_conciliacion_concliados_no 
				WHERE empresa = '{empresa}'
				AND (id_banco_origen = {id_banco} OR id_banco_destino = {id_banco})
				AND fecha_transaccion <= '{fecha_hasta}' 
				AND RIGHT(referencia, 6) = RIGHT('$var_referencia_conciliacion',6) AND monto_bs = $var_monto_conciliacion");
			}else{ // Divisas 
				sc_lookup_field(vista_conciliacion, "SELECT modulo, tipo, referencia, monto, monto_bs 
				from Vista_movimiento_conciliacion_concliados_no 
				WHERE empresa = '{empresa}'
				AND (id_banco_origen = {id_banco} OR id_banco_destino = {id_banco})
				AND fecha_transaccion <= '{fecha_hasta}' 
				AND RIGHT(referencia, 6) = RIGHT('$var_referencia_conciliacion',6) AND monto = $var_monto_conciliacion");		
			}
			
			if({vista_conciliacion[0]['referencia']} <> ''){		
				$var_modulo								= {vista_conciliacion[0]['modulo']};
				$var_tipo								= {vista_conciliacion[0]['tipo']};
				$var_referencia							= {vista_conciliacion[0]['referencia']};
				$var_monto	 							= {vista_conciliacion[0]['monto']};
				$var_monto_bs 							= {vista_conciliacion[0]['monto_bs']};
			
				
				$var_tipo_conciliacion = 'Automatica';

				if($var_codigo_moneda == '0001'){ // Bolivares
					sc_exec_sql ("update banco_conciliacion_importarcion set conciliado = 'SI', referencia_transaccion = '$var_referencia',
					tipo_conciliacion = '$var_tipo_conciliacion'
					WHERE id_banco_conciliacion_importacion = $var_id_banco_conciliacion_importacion
					AND monto = $var_monto_bs");
				}else{// Divisas
					sc_exec_sql ("update banco_conciliacion_importarcion set conciliado = 'SI', referencia_transaccion = '$var_referencia',
					tipo_conciliacion = '$var_tipo_conciliacion'
					WHERE id_banco_conciliacion_importacion = $var_id_banco_conciliacion_importacion
					AND monto = $var_monto");
				}

				if($var_modulo == 'VENTAS'){
					if($var_codigo_moneda == '0001'){ // Bolivares
						sc_exec_sql ("update ventas_transacciones_detalles set conciliado = 'SI'
						WHERE empresa = '{empresa}'
						AND (conciliado = 'NO' OR conciliado = ' ' OR isnull(conciliado)) 
						AND id_banco = {id_banco} 
						AND fecha_transaccion <= '{fecha_hasta}'
						AND RIGHT(referencia, 6) = RIGHT('$var_referencia',6)
						AND monto_bs = $var_monto_bs 
						AND status = 'FACTURADO'");
					}else{// Divisas
					sc_exec_sql ("update ventas_transacciones_detalles set conciliado = 'SI'
						WHERE empresa = '{empresa}'
						AND (conciliado = 'NO' OR conciliado = ' ' OR isnull(conciliado)) 
						AND id_banco = {id_banco} 
						AND fecha_transaccion <= '{fecha_hasta}'
						AND RIGHT(referencia, 6) = RIGHT('$var_referencia',6)
						AND monto = $var_monto
						AND status = 'FACTURADO'");
					}			
				}elseif($var_modulo == 'COMPRAS'){
					$var_monto 		= $var_monto 	* -1;
					$var_monto_bs 	= $var_monto_bs * -1;	
					if($var_codigo_moneda == '0001'){ // Bolivares
						sc_exec_sql ("update compras_transacciones_detalles set conciliado = 'SI'
						WHERE empresa = '{empresa}'
						AND (conciliado = 'NO' OR conciliado = ' ' OR isnull(conciliado)) 
						AND id_banco = {id_banco} 
						AND fecha_transaccion <= '{fecha_hasta}'
						AND RIGHT(referencia, 6) = RIGHT('$var_referencia',6)
						AND monto_bs = $var_monto_bs
						AND status = 'PROCESADO'");
					}else{// Divisas
						sc_exec_sql ("update compras_transacciones_detalles set conciliado = 'SI'
						WHERE empresa = '{empresa}'
						AND (conciliado = 'NO' OR conciliado = ' ' OR isnull(conciliado)) 
						AND id_banco = {id_banco} 
						AND fecha_transaccion <= '{fecha_hasta}'
						AND RIGHT(referencia, 6) = RIGHT('$var_referencia',6)
						AND monto = $var_monto
						AND status = 'PROCESADO'");
					}
				}elseif($var_modulo == 'BANCOS'){
					if($var_codigo_moneda == '0001'){ // Bolivares
						sc_exec_sql ("update banco_transferencias_movimientos set conciliado = 'SI'
						WHERE empresa = '{empresa}'
						AND (conciliado = 'NO' OR conciliado = ' ' OR isnull(conciliado)) 
						AND (id_banco_origen = {id_banco} OR id_banco_destino = {id_banco}) 
						AND fecha_transaccion <= '{fecha_hasta}'
						AND RIGHT(referencia, 6) = RIGHT('$var_referencia',6)
						AND monto_base_bs = $var_monto_bs
						AND status = 'PROCESADO'");
					}else{ // Divisas
						sc_exec_sql ("update banco_transferencias_movimientos set conciliado = 'SI'
						WHERE empresa = '{empresa}'
						AND (conciliado = 'NO' OR conciliado = ' ' OR isnull(conciliado)) 
						AND (id_banco_origen = {id_banco} OR id_banco_destino = {id_banco}) 
						AND fecha_transaccion <= '{fecha_hasta}'
						AND RIGHT(referencia, 6) = RIGHT('$var_referencia',6)
						AND monto_base = $var_monto
						AND status = 'CONFIRMADO'");
					}
				}		
			
			}
			
			$var_modulo = '';
		$my_data->MoveNext();
		}
		$my_data->Close();
	}


	$control = TRUE;
	if($control = TRUE){
		sc_commit_trans();
		sc_alert("Conciliacion Automatica Finalizada");
		sc_ajax_javascript("scBtnFn_sys_format_reload");
	}else{
		sc_rollback_trans();
		sc_alert("Hubo un error");
	}

?>