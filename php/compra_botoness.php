<?php

/*Boton Totalizar*/
$res_err = verifica_cantidades({id_compra});
$almacen = {cod_almacen};
$hoy = date("Y-m-d");

if({estado} == 'EN ESPERA'){	
	sc_begin_trans(); //comienza transaccion	
	sc_lookup(item, "select codigo_producto, cantidad from compras_detalles where id_compra = {id_compra} and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'");
	foreach({item} as $it){

		$cod_prod = $it[0];
		$canti = $it[1];
		
		sc_lookup(stock, "select codigo_almacen, stock from inventario_productos_stock where codigo_producto = '$cod_prod' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]' and codigo_almacen = '$almacen'");

		if(isset({stock[0][0]})){
			if({stock[0][0]} >= 0.00){
				$stoc = {stock[0][1]} + $canti;
			}
			else{
				$stoc = {stock[0][1]} - $canti;
			}
			
			if($stoc < 0.00){
				$error = "El stock del producto $cod_prod es negativo en el almacen $almacen <br>
				stock actual: ".{stock[0][1]}."<br>
				stock despues de la transaccion $stoc";
			}
			
			sc_exec_sql("update inventario_productos_stock set stock = $stoc where codigo_almacen = '$almacen' and codigo_producto = '$cod_prod' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'");
		}
		else{
			sc_exec_sql("insert into inventario_productos_stock (codigo_producto, codigo_almacen, stock, empresa, sucursal, fecha) values ('$cod_prod', '$almacen', $stock, '[usr_empresa]', '[usr_sucursal]', '$hoy')");
		}

	}
	
	
	$corr = buscar_correlativo('FACTURC');
	sc_exec_sql("update compras_resumen set estado = 'EN INVENTARIO', numero = '$corr' WHERE id_compra = '{id_compra}'");
	suma_correlativo('FACTURC');
	$err = actualizar_seriales({id_compra});
	$control = TRUE; // control = verdadero  
	
	// Actualizando status de compras_detalles ddiaz - 23-02-2023
	sc_exec_sql("update compras_detalles set estado = 'EN INVENTARIO', numero = '$corr' WHERE id_compra = '{id_compra}'");	/*Actualizando status de compras_detalles*/
	
	if($control = TRUE AND empty($error) AND empty($err)){ // Se ha realizado el registro
		sc_commit_trans();		
	}
	else{ //error y devolver 
		sc_rollback_trans();
		if(empty($error)){
			$error = 'hubo un error';
		}
		sc_alert($error. ' '.$err);				
	}	
}
sc_ajax_javascript("scBtnFn_sys_format_reload");
/* FIN Boton Totalizar*/



/* Boton ANULAR */
sc_begin_trans(); //comienza transaccion
$almacen = {cod_almacen};
$hoy = date("Y-m-d");
sc_lookup(item, "select codigo_producto, cantidad from compras_detalles where id_compra = '{id_compra}' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'");
foreach({item} as $it){

	$cod_prod = $it[0];
	$canti = $it[1];

	sc_lookup(stock, "select codigo_almacen, stock from inventario_productos_stock where codigo_producto = '$cod_prod' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]' and codigo_almacen = '$almacen'");

	if(isset({stock[0][0]})){
		if({stock[0][0]} >= 0.00){
			$stoc = {stock[0][1]} - $canti;
		}
		else{
			$stoc = {stock[0][1]} + $canti;
		}
		if($stoc < 0.00){
			$error = "El stock del producto $cod_prod es negativo en el almacen $almacen <br>
				stock actual: ".{stock[0][1]}."<br>
				stock despues de la transaccion $stoc";
		}
		
		sc_exec_sql("update inventario_productos_stock set stock = $stoc where codigo_almacen = '$almacen' and codigo_producto = '$cod_prod' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'");
	}
	else{
		sc_exec_sql("insert into inventario_productos_stock (codigo_producto, codigo_almacen, stock, empresa, sucursal, fecha) values ('$cod_prod', '$almacen', $stock, '[usr_empresa]', '[usr_sucursal]', '$hoy')");
	}
	
}
	
sc_exec_sql("update compras_resumen set estado = 'ANULADO' WHERE id_compra = '{id_compra}'");
anular_cxp('FACTURC', {numero});

//eliminados registro de la tabla de inventario_productos_seriales
sc_exec_sql ("delete from inventario_productos_seriales where CONCAT(codigo_productos,serial) in (SELECT
	CONCAT(codigo_producto,serial)
FROM
	movimientos_producto_seriales m,
	compras_resumen c
	where c.id_compra=m.id_resumen
	and m.id_resumen = '{id_compra}')");

$control = TRUE; // control = verdadero
if($control = TRUE AND empty($error)){
	sc_commit_trans();
	/* Se ha realizado el registro*/
}
else{
	sc_rollback_trans();
	if(empty($error)){
		$error = 'hubo un error';
	}
	sc_alert($error);
	
	/* error y devolver */
}
sc_ajax_javascript("scBtnFn_sys_format_reload");

/* FIN Boton ANULAR */

/* FIN Boton CREDITO */

$almacen = {cod_almacen};
$hoy = date("Y-m-d");

if({estado} == 'EN ESPERA'){
	
	sc_begin_trans(); //comienza transaccion
	
	sc_lookup(item, "select codigo_producto, cantidad from compras_detalles where id_compra = {id_compra} and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'");
	foreach({item} as $it){

		$cod_prod = $it[0];
		$canti = $it[1];
		
		sc_lookup(stock, "select codigo_almacen, stock from inventario_productos_stock where codigo_producto = '$cod_prod' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]' and codigo_almacen = '$almacen'");

		if(isset({stock[0][0]})){
			if({stock[0][0]} >= 0.00){
				$stoc = {stock[0][1]} + $canti;
			}
			else{
				$stoc = {stock[0][1]} - $canti;
			}
			
			if($stoc < 0.00){
				$error = "El stock del producto $cod_prod es negativo en el almacen $almacen <br>
				stock actual: ".{stock[0][1]}."<br>
				stock despues de la transaccion $stoc";
			}
			
			sc_exec_sql("update inventario_productos_stock set stock = $stoc where codigo_almacen = '$almacen' and codigo_producto = '$cod_prod' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'");
		}
		else{
			sc_exec_sql("insert into inventario_productos_stock (codigo_producto, codigo_almacen, stock, empresa, sucursal, fecha) values ('$cod_prod', '$almacen', 0.00, '[usr_empresa]', '[usr_sucursal]', '$hoy')");
		}

	}
	
	$corr = buscar_correlativo('FACTURC');
	sc_exec_sql("update compras_resumen set estado = 'EN INVENTARIO', numero = '$corr', saldo = 0.00 WHERE id_compra = '{id_compra}'");
	suma_correlativo('FACTURC');
	
	$descripcion = {descripcion};
	$descripcion .= "FACTURA A CREDITO ".$descripcion;
	enviar_cxp('FACTURC', $corr, {numero_factura}, {numero_control}, $descripcion, {codigo_proveedor}, {subtotal}, {total}, {saldo}, {tasa_cambio}, {fecha_factura}, {fecha_vencimiento}, '', '', 'PROCESADO');
	$err = actualizar_seriales({id_compra});
	$control = TRUE; // control = verdadero
	if($control = TRUE AND empty($error) AND empty($err)){
		sc_commit_trans();
		/* Se ha realizado el registro*/
	}
	else{
		sc_rollback_trans();
		if(empty($error)){
			$error = 'hubo un error.';
		}
		sc_alert($error. ' '.$err);
		
		/* error y devolver */
	}
	
}
sc_ajax_javascript("scBtnFn_sys_format_reload");

/* FIN Boton CREDITO */





?>