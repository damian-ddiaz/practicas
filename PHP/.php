'<?php

// Actualizando Ingresos - Egresos - Cruzados - Concliados NO

// Actualizando banco_conciliacion_importarcion
	sc_exec_sql ("UPDATE banco_conciliacion_importarcion SET id_conciliacion = {id_conciliacion}
WHERE id_banco = {id_banco} AND referencia_transaccion <> ' '
  AND (isnull(id_conciliacion)  OR id_conciliacion = 0) AND conciliado = 'SI' AND fecha_banco <= '{fecha_hasta}'");

/*Validar conciliacion*/

$check_sql = "SELECT
    count(*)
FROM
    ventas_transacciones_detalles
WHERE empresa = '[usr_empresa]' AND sucursal  = '[usr_sucursal]' AND conciliado = 'SI' AND status = 'FACTURADO' [formas_pago]  AND (id_conciliacion is null OR id_conciliacion = 0) AND fecha_transaccion BETWEEN '[par_desde]' AND '[par_hasta]' AND status = 'FACTURADO'";
sc_lookup(rs, $check_sql);
if ({rs[0][0]}<=0){
	sc_alert("Debe tener al menos una transacción conciliada");
}
else{
	sc_begin_trans();
	correlativos_documentos('CONCBAN');
	$numero_conciliacion = [correlativo];
	// confirmar conciliacion
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
	//confirmar conciliacion

	//confirmar movimientos
	
	sc_exec_sql ("UPDATE ventas_transacciones_detalles SET id_conciliacion = {id_conciliacion}
WHERE empresa = '[usr_empresa]' AND sucursal  = '[usr_sucursal]' AND conciliado = 'SI' AND [formas_pago] AND (id_conciliacion is null OR id_conciliacion = 0) AND fecha_transaccion BETWEEN '[par_desde]' AND '[par_hasta]' AND status = 'FACTURADO'");
	
	sc_exec_sql ("UPDATE banco_conciliacion_importarcion SET id_conciliacion = {id_conciliacion} WHERE codigo_banco = '{codigo_banco}' AND referencia IN (SELECT s.referencia FROM ventas_transacciones_detalles s WHERE s.empresa = '[usr_empresa]' AND s.sucursal  = '[usr_sucursal]' AND s.conciliado = 'SI' AND s.status = 'FACTURADO' [formas_pago] AND (s.id_conciliacion is null OR s.id_conciliacion = 0) AND fecha_banco BETWEEN '[par_desde]' AND '[par_hasta]')");

	
	/*actualizar saldo banco conciliado*/

	$update_table_banco  = 'bancos';
	$update_where_banco  = "codigo_banco = '{codigo_banco}' AND empresa = '[usr_empresa]'";
	$update_fields_banco = array(
		"saldo_conciliado = saldo_conciliado+{saldo}",
	 );
	$update_sql_banco = 'UPDATE ' . $update_table_banco
		. ' SET '   . implode(', ', $update_fields_banco)
		. ' WHERE ' . $update_where_banco;
	sc_exec_sql($update_sql_banco);*/
	/*actualizar saldo banco conciliado*/

	
	actualizar_correlativos_documentos('CONCBAN');
	
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
/*Validar conciliacion*/
?>