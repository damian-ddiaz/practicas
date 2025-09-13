'<?php
$check_sql = "SELECT
tipo,
referencia,
monto,
id_banco
FROM
banco_conciliacion_importarcion
where empresa = '{empresa}' 
AND conciliado = 'NO'
AND id_banco = {id_banco} 
AND fecha_banco <= '{fecha_hasta}'";
	
sc_select(my_data, $check_sql);

if ({my_data} === false){
	sc_alert("Hubo un error al ejecutar la consulta");
}else{
	sc_begin_trans();
	while (!$my_data->EOF){
		$var_tipo 			= $my_data->fields[0];
		$var_referencia 	= $my_data->fields[1];
		$var_monto 			= $my_data->fields[2];
		$va_id_banco		= $my_data->fields[3];

		
		sc_lookup_field(banco_moneda, "SELECT codigo_moneda 
		from bancos 
		WHERE id = {id_banco}");
        $var_codigo_moneda = {banco_moneda[0]['codigo_moneda']};
		
		if($var_codigo_moneda == '0001'){ // Bolivares
		/*	sc_lookup_field(vista_conciliacion, "SELECT modulo, referencia, monto, monto_bs 
			from Vista_movimiento_conciliacion_concliados_no 
			WHERE empresa = '{empresa}'
			AND (id_banco_origen = {id_banco} OR id_banco_destino = {id_banco})
			AND fecha_transaccion <= '{fecha_hasta}' 
			AND referencia = '$var_referencia' AND monto_bs = $var_monto");*/
		}else{ // Divisas
			
		}
		
		if({vista_conciliacion[0]['referencia']} <> ''){// SI TIENE PAGOS	
			echo 'Estoy rn la Condicion' 
			
	//		$var_modulo								= {vista_conciliacion[0]['modulo']};
	//		$var_referencia							= {vista_conciliacion[0]['referencia']};
	//		$var_monto	 							= {vista_conciliacion[0]['monto']};
	//		$var_monto_bs 							= {vista_conciliacion[0]['monto_bs']};
	//		$var_id_banco_conciliacion_importacion	= {vista_conciliacion[0]['id_banco_conciliacion_importacion']};
							
	//		sc_exec_sql ("update banco_conciliacion_importarcion set conciliado = 'SI', referencia_transaccion = '$var_referencia'	
	 //		WHERE id_banco_conciliacion_importacion = $var_id_banco_conciliacion_importacion");
		}		
	$my_data->MoveNext();
	}
	$my_data->Close();
}

$control = TRUE;
if($control = TRUE){
	sc_commit_trans();
	//sc_alert("Proceso realizado");
	sc_ajax_javascript("scBtnFn_sys_format_reload");
}else{
	sc_rollback_trans();
	sc_alert("Hubo un error");
	//sc_ajax_javascript("scBtnFn_sys_format_reload");
}
/*Validar conciliacion*/
?>