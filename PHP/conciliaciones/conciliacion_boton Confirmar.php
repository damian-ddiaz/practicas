'<?php
	$check_sql = ("SELECT  id_banco_conciliacion_importacion
FROM
    banco_conciliacion_importarcion
WHERE 
Id_banco = [par_id_banco]
AND referencia_transaccion <> ' '
AND (isnull(id_conciliacion) OR id_conciliacion=0)
AND conciliado = 'SI'
AND fecha_banco <= '[par_fecha_hasta]'");
sc_lookup(rs, $check_sql);

if ({rs[0][0]}<=0){ //NO Existen Transacciones para Conciliar
	sc_alert("Debe tener al menos una Transacción Conciliada");
}else{//SI Existen Transacciones para Conciliar
	sc_begin_trans();
	correlativos_documentos('CONCBAN');
	$numero_conciliacion = [correlativo];
	// Actualizando banco_resumen_conciliacion. Campos: estatus - numero_conciliacion
	$update_table  = 'banco_resumen_conciliacion';
	$update_where  = "id_conciliacion = {id_conciliacion}";
	$update_fields = array(
	    "estatus = 'Confirmado'",
		"numero_conciliacion = '$numero_conciliacion'",
	 );
	$update_sql = 'UPDATE ' . $update_table
		. ' SET '   . implode(', ', $update_fields)
		. ' WHERE ' . $update_where;
	sc_exec_sql($update_sql);
	
	actualizar_correlativos_documentos('CONCBAN');

	$var_fecha = date('Y-m-d');
	$var_tipo_conciliacion = 'Manual';

	// Actualizando Tabla: banco_conciliacion_importarcion
	sc_exec_sql ("UPDATE banco_resumen_conciliacion SET fecha_conciliacion = '$var_fecha' WHERE 
	id_banco = [par_id_banco] AND id_conciliacion = {id_conciliacion}");
	
	// Actualizando Tabla: banco_conciliacion_importarcion
	sc_exec_sql ("UPDATE banco_conciliacion_importarcion SET id_conciliacion = {id_conciliacion} WHERE 
	id_banco = [par_id_banco] AND referencia_transaccion <> '' AND (isnull(id_conciliacion) OR id_conciliacion=0)
	AND conciliado = 'SI' AND fecha_banco <= '[par_fecha_hasta]'");

	// Actualizando Tabla: ventas_transacciones_detalles
	sc_exec_sql ("UPDATE ventas_transacciones_detalles SET id_conciliacion = {id_conciliacion}, 
	nro_conciliacion = '$numero_conciliacion',	id_conciliacion = {id_conciliacion}, 
	tipo_conciliacion = '$var_tipo_conciliacion' WHERE 
	id_banco = [par_id_banco] AND (isnull(id_conciliacion) OR id_conciliacion=0)
	AND conciliado = 'SI' AND fecha_transaccion <= '[par_fecha_hasta]'");

	// Actualizando Tabla: compras_transacciones_detalles
	sc_exec_sql ("UPDATE compras_transacciones_detalles SET id_conciliacion = {id_conciliacion}, 
	nro_conciliacion = '$numero_conciliacion',	id_conciliacion = {id_conciliacion}, 
	tipo_conciliacion = '$var_tipo_conciliacion' WHERE 
	id_banco = [par_id_banco] AND (isnull(id_conciliacion) OR id_conciliacion=0)
	AND conciliado = 'SI' AND fecha_transaccion <= '[par_fecha_hasta]'");
	
	// Actualizando Tabla: banco_transferencias_movimientos
	sc_exec_sql ("UPDATE banco_transferencias_movimientos SET id_conciliacion = {id_conciliacion}, 
	nro_conciliacion = '$numero_conciliacion',	id_conciliacion = {id_conciliacion},
	tipo_conciliacion = '$var_tipo_conciliacion' WHERE 
	(id_banco_origen = [par_id_banco] or id_banco_destino = [par_id_banco]) AND (isnull(id_conciliacion) 
	OR id_conciliacion=0)
	AND conciliado = 'SI' AND fecha_transaccion <= '[par_fecha_hasta]'");
	
	// Actualizando Tabla: Banco - Saldo de Banco - Damian Diaz
	$var_saldo_conciliado = {credito_conciliado} + {debito_conciliado};
	if($var_saldo_conciliado <> 0){
		sc_exec_sql ("UPDATE bancos SET saldo_conciliado = saldo_conciliado + $var_saldo_conciliado WHERE 
		id = [par_id_banco]");
		// Actualizando Tabla: Banco - Saldo de Banco - Damian Diaz - 09-08-2025
		sc_exec_sql ("UPDATE bancos_sucursales SET saldo_conciliado = saldo_conciliado + $var_saldo_conciliado WHERE 
		id_banco = [par_id_banco]");	
	}
	// Actualizando Tabla: - banco_resumen_conciliacion - montos
	 sc_exec_sql ("UPDATE banco_resumen_conciliacion SET 
	 credito_conciliado = {credito_conciliado}, 
	 debito_conciliado = {debito_conciliado},
	 ie_credito_penidiente_conciliar_monto 		= {ie_credito_penidiente_conciliar_monto},
	 ie_credito_penidiente_conciliar_cantidad 	= {ie_credito_penidiente_conciliar_cantidad},
	 ie_debito_penidiente_conciliar_monto 		= {ie_debito_penidiente_conciliar_monto},
	 ie_debito_penidiente_conciliar_cantidad 	= {ie_debito_penidiente_conciliar_cantidad},
	 ec_credito_penidiente_conciliar_monto 		= {ec_credito_penidiente_conciliar_monto},
	 ec_credito_penidiente_conciliar_cantidad	= {ec_credito_penidiente_conciliar_cantidad},
	 ec_debito_penidiente_conciliar_monto 		= {ec_debito_penidiente_conciliar_monto},
	 ec_debito_penidiente_conciliar_cantidad 	= {ec_debito_penidiente_conciliar_cantidad}
    WHERE id_banco = {id_banco} AND id_conciliacion =  {id_conciliacion}");
		
	$control = TRUE;
	if($control == TRUE){
		sc_commit_trans();
		sc_alert("Se ha confirmado la conciliacion");
		sc_ajax_javascript('scBtnFn_sys_format_reload');
	}
	else{
		sc_rollback_trans();
		sc_alert("Hubo un error");
	}
	

}

?>