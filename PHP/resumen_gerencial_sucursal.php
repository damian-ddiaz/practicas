<?php

$var_fecha_ini = date('Y-m-d', strtotime({fecha_ini})); // Fecha Inicial
$var_fecha_fin = date('Y-m-d', strtotime({fecha_fin})); // Fecha Final

$var_empresa = [usr_empresa];
$var_sucursal = [usr_sucursal];

// LIMPIANDO TABLA 
sc_exec_sql("delete from resumen_gerencial where empresa = '$var_empresa' and sucursal = '$var_sucursal'");

//***************************************** BLOQUE DE INGRESOS EN BOLIVARES *****************************************
//Venta de Productos Bs
sc_lookup_field(ventas_productos_bs, "select
  vd.id_detalle,
  vd.codigo,
  vd.descripcion,
  sum(vd.sub_total) as sub_total,
  sum(vd.sub_total_bs) as sub_total_bs,
  vtd.emp,
  vtd.suc,
  vtd.tipo_pago,
  MonedaUsadaFormaPago(vtd.tipo_pago, vtd.forma_pago, vr.empresa, vr.sucursal) as codigo_moneda
from
  ventas_detalles vd
  inner join ventas_resumen vr on vr.id_ventas = vd.id_detalle
  inner join vFacturas_transacciones vtd on vtd.id_ventas = vd.id_detalle
where
  vd.codigo_almacen <> ''
  and vd.empresa = '$var_empresa'
  and vd.sucursal = '$var_sucursal'
  and vr.saldo = 0
  and vr.status = 'FACTURADO'
  and vtd.tipo_pago <> 'RT'
  and vd.fecha between '$var_fecha_ini' and '$var_fecha_fin' having codigo_moneda = '0001'");

if(isset({ventas_productos_bs[0]['sub_total_bs']})){
	$var_sub_total_bs		= {ventas_productos_bs[0]['sub_total_bs']};
	$var_codigo_moneda 	= {ventas_productos_bs[0]['codigo_moneda']};
	// INSERTANDO Venta de Productos Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'ventaprod_bs'",
		 'descripcion' => "'Venta Productos Bs'",
	 	 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_sub_total_bs'",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );

	// Insert Venta de Productos Bs
	$insert_ventas_productos_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_ventas_productos_bs);
}

//Venta de Srvicios Bs
sc_lookup_field(ventas_servicios_bs, "select
  vd.id_detalle,
  vd.codigo,
  vd.descripcion,
  sum(vd.sub_total) as sub_total,
  sum(vd.sub_total_bs) as sub_total_bs,
  vd.empresa,
  vd.sucursal,
  vtd.tipo_pago,
  MonedaUsadaFormaPago (
    vtd.tipo_pago,
    vtd.forma_pago,
    vtd.emp,
    vtd.suc
  ) as codigo_moneda
from
  ventas_detalles vd
  inner join ventas_resumen vr on vr.id_ventas = vd.id_detalle
  inner join vFacturas_transacciones vtd on vtd.id_ventas = vd.id_detalle
where
  vd.codigo_almacen = ''
  and vd.empresa = '$var_empresa'
  and vd.sucursal = '$var_sucursal'
  and vr.saldo = 0
  and vr.status = 'FACTURADO'
  and vtd.tipo_pago <> 'RT'
  and vd.fecha between '$var_fecha_ini' and '$var_fecha_fin'
having
  codigo_moneda = '0001'");

if(isset({ventas_servicios_bs[0]['sub_total_bs']})){
	$var_sub_total_bs	= {ventas_servicios_bs[0]['sub_total_bs']};
	$var_codigo_moneda 	= {ventas_servicios_bs[0]['codigo_moneda']};
	// INSERTANDO Venta de Servicios Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'ventaservi_bs'",
		 'descripcion' => "'Venta Servicios Bs'",
		 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_sub_total_bs'",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );
	// Insert Venta de Servicios Bs
	$insert_ventas_servicios_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_ventas_servicios_bs);
}

//**************************************** FIN BLOQUE DE INGRESOS EN BOLIVARES ***************************************

//******************************************* BLOQUE DE EGRESOS EN BOLIVARES *****************************************

//Compra de Productos Bs
sc_lookup_field(compra_productos_bs, "select
  cd.id_detalle,
  cd.codigo_producto,
  cd.nombre_producto,
  sum(cd.subtotal_renglon) as subtotal_renglon,
  sum(cd.subtotal_renglon_bs) as subtotal_renglon_bs,
  ctd.emp,
  ctd.suc,
  ctd.tipo_pago,
  MonedaUsadaFormaPago(ctd.tipo_pago, ctd.forma_pago, ctd.emp, ctd.suc) as codigo_moneda
from
  compras_detalles cd
  inner join compras_resumen cr on cr.id_compra = cd.id_compra
  inner join vFacturas_transacciones_compras ctd on ctd.id_compra = cd.id_compra
where
  cd.codigo_almacen <> ''
  and cd.empresa = '$var_empresa'
  and cd.sucursal = '$var_sucursal'
  and cr.saldo = 0
  and cr.estado = 'EN INVENTARIO'
  and ctd.tipo_pago <> 'RT'
  and cd.fecha between '$var_fecha_ini' and '$var_fecha_fin' having codigo_moneda = '0001'");

if(isset({compra_productos_bs[0]['subtotal_renglon_bs']})){
	$var_subtotal_reglon_bs	= {compra_productos_bs[0]['subtotal_renglon_bs']};
	$var_codigo_moneda 		= {compra_productos_bs[0]['codigo_moneda']};
	// INSERTANDO Compra de Productos Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'compraprod_bs'",
		 'descripcion' => "'Compra Productos Bs'",
		 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_subtotal_reglon_bs' * -1",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );

	// Insert Compra de Productos Bs
	$insert_compra_productos_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_compra_productos_bs);
}

//Compra de Servicios Bs
sc_lookup_field(compra_servicios_bs, "select
  cd.id_detalle,
  cd.codigo_producto,
  cd.nombre_producto,
  sum(cd.subtotal_renglon) as subtotal_renglon,
  sum(cd.subtotal_renglon_bs) as subtotal_renglon_bs,
  ctd.emp,
  ctd.suc,
  ctd.tipo_pago,
  MonedaUsadaFormaPago(ctd.tipo_pago, ctd.forma_pago, ctd.emp, ctd.suc) as codigo_moneda
from
  compras_detalles cd
  inner join compras_resumen cr on cr.id_compra = cd.id_compra
  inner join vFacturas_transacciones_compras ctd on ctd.id_compra = cd.id_compra
where
  cd.codigo_almacen = ''
  and cd.empresa = '$var_empresa'
  and cd.sucursal = '$var_sucursal'
  and cr.saldo = 0
  and cr.estado = 'EN INVENTARIO'
  and ctd.tipo_pago <> 'RT'
  and cd.fecha between '$var_fecha_ini' and '$var_fecha_fin' having codigo_moneda = '0001'");

if(isset({compra_servicios_bs[0]['subtotal_renglon_bs']})){	
	$var_subtotal_reglon_bs		= {compra_servicios_bs[0]['subtotal_renglon_bs']};
	$var_codigo_moneda 			= {compra_servicios_bs[0]['codigo_moneda']};
	// INSERTANDO Compra de Servicios Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'compraservi_bs'",
		 'descripcion' => "'Compra Servicios Bs'",
		 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_subtotal_reglon_bs' * -1",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );

	// Insert Compra de Servicios Bs
	$insert_compra_servicios_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_compra_servicios_bs);
}
//*************************************** FIN BLOQUE DE ENGRESOS EN BOLIVARES ***************************************



//***************************************** BLOQUE DE INGRESOS EN DIVISAS *****************************************
//Venta de Productos Divisas
sc_lookup_field(ventas_productos_divisas, "select
  vd.id_detalle,
  vd.codigo,
  vd.descripcion,
  sum(vd.sub_total) as sub_total,
  sum(vd.sub_total_bs) as sub_total_bs,
  vtd.emp,
  vtd.suc,
  vtd.tipo_pago,
  MonedaUsadaFormaPago(vtd.tipo_pago, vtd.forma_pago, vr.empresa, vr.sucursal) as codigo_moneda
from
  ventas_detalles vd
  inner join ventas_resumen vr on vr.id_ventas = vd.id_detalle
  inner join vFacturas_transacciones vtd on vtd.id_ventas = vd.id_detalle
where
  vd.codigo_almacen <> ''
  and vd.empresa = '$var_empresa'
  and vd.sucursal = '$var_sucursal'
  and vr.saldo = 0
  and vr.status = 'FACTURADO'
  and vtd.tipo_pago <> 'RT'
  and vd.fecha between '$var_fecha_ini' and '$var_fecha_fin' having codigo_moneda = '0002'");

if(isset({ventas_productos_divisas[0]['sub_total_bs']})){
	$var_sub_total_bs		= {ventas_productos_divisas[0]['sub_total_bs']};
	$var_codigo_moneda 	= {ventas_productos_divisas[0]['codigo_moneda']};
	// INSERTANDO Venta de Productos Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'ventaprod_divisas'",
		 'descripcion' => "'Venta Productos Divisas'",
	 	 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_sub_total_bs'",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );

	// Insert Venta de Productos Divisas
	$insert_ventas_productos_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_ventas_productos_bs);
}

//Venta de Srvicios Divisas
sc_lookup_field(ventas_servicios_divisas, "select
  vd.id_detalle,
  vd.codigo,
  vd.descripcion,
  sum(vd.sub_total) as sub_total,
  sum(vd.sub_total_bs) as sub_total_bs,
  vd.empresa,
  vd.sucursal,
  vtd.tipo_pago,
  MonedaUsadaFormaPago (
    vtd.tipo_pago,
    vtd.forma_pago,
    vtd.emp,
    vtd.suc
  ) as codigo_moneda
from
  ventas_detalles vd
  inner join ventas_resumen vr on vr.id_ventas = vd.id_detalle
  inner join vFacturas_transacciones vtd on vtd.id_ventas = vd.id_detalle
where
  vd.codigo_almacen = ''
  and vd.empresa = '$var_empresa'
  and vd.sucursal = '$var_sucursal'
  and vr.saldo = 0
  and vr.status = 'FACTURADO'
  and vtd.tipo_pago <> 'RT'
  and vd.fecha between '$var_fecha_ini' and '$var_fecha_fin'
having
  codigo_moneda = '0002'");

if(isset({ventas_servicios_divisas[0]['sub_total']})){
	$var_sub_total_bs	= {ventas_servicios_divisas[0]['sub_total']};
	$var_codigo_moneda 	= {ventas_servicios_divisas[0]['codigo_moneda']};
	// INSERTANDO Venta de Servicios Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'ventaservi_divisas'",
		 'descripcion' => "'Venta Servicios Divisas'",
		 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_sub_total_bs'",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );
	// Insert Venta de Servicios Bs
	$insert_ventas_servicios_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_ventas_servicios_bs);
}

//**************************************** FIN BLOQUE DE INGRESOS EN DIVISAS ***************************************

//******************************************* BLOQUE DE EGRESOS EN DIVISAS *****************************************

//Compra de Productos Bs
sc_lookup_field(compra_productos_divisas, "select
  cd.id_detalle,
  cd.codigo_producto,
  cd.nombre_producto,
  sum(cd.subtotal_renglon) as subtotal_renglon,
  sum(cd.subtotal_renglon_bs) as subtotal_renglon_bs,
  ctd.emp,
  ctd.suc,
  ctd.tipo_pago,
  MonedaUsadaFormaPago(ctd.tipo_pago, ctd.forma_pago, ctd.emp, ctd.suc) as codigo_moneda
from
  compras_detalles cd
  inner join compras_resumen cr on cr.id_compra = cd.id_compra
  inner join vFacturas_transacciones_compras ctd on ctd.id_compra = cd.id_compra
where
  cd.codigo_almacen <> ''
  and cd.empresa = '$var_empresa'
  and cd.sucursal = '$var_sucursal'
  and cr.saldo = 0
  and cr.estado = 'EN INVENTARIO'
  and ctd.tipo_pago <> 'RT'
  and cd.fecha between '$var_fecha_ini' and '$var_fecha_fin' having codigo_moneda = '0002'");

if(isset({compra_productos_divisas[0]['subtotal_renglon']})){
	$var_subtotal_reglon_bs	= {compra_productos_divisas[0]['subtotal_renglon']};
	$var_codigo_moneda 		= {compra_productos_divisas[0]['codigo_moneda']};
	// INSERTANDO Compra de Productos Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'compraprod_divisas'",
		 'descripcion' => "'Compra Productos Divisas'",
		 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_subtotal_reglon_bs' * -1",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );

	// Insert Compra de Productos Bs
	$insert_compra_productos_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_compra_productos_bs);
}

//Compra de Servicios Bs
sc_lookup_field(compra_servicios_divisas, "select
  cd.id_detalle,
  cd.codigo_producto,
  cd.nombre_producto,
  sum(cd.subtotal_renglon) as subtotal_renglon,
  sum(cd.subtotal_renglon_bs) as subtotal_renglon_bs,
  ctd.emp,
  ctd.suc,
  ctd.tipo_pago,
  MonedaUsadaFormaPago(ctd.tipo_pago, ctd.forma_pago, ctd.emp, ctd.suc) as codigo_moneda
from
  compras_detalles cd
  inner join compras_resumen cr on cr.id_compra = cd.id_compra
  inner join vFacturas_transacciones_compras ctd on ctd.id_compra = cd.id_compra
where
  cd.codigo_almacen = ''
  and cd.empresa = '$var_empresa'
  and cd.sucursal = '$var_sucursal'
  and cr.saldo = 0
  and cr.estado = 'EN INVENTARIO'
  and ctd.tipo_pago <> 'RT'
  and cd.fecha between '$var_fecha_ini' and '$var_fecha_fin' having codigo_moneda = '0002'");

if(isset({compra_servicios_divisas[0]['subtotal_renglon']})){	
	$var_subtotal_reglon_bs		= {compra_servicios_divisas[0]['subtotal_renglon']};
	$var_codigo_moneda 			= {compra_servicios_divisas[0]['codigo_moneda']};
	// INSERTANDO Compra de Servicios Bs
	$insert_table  = 'resumen_gerencial';      // Table name
	$insert_fields = array( //Field list, add as many as needed
		 'id' => "'compraservi_divisas'",
		 'descripcion' => "'Compra Servicios Divisas'",
		 'codigo_moneda' => "'$var_codigo_moneda'",
		 'base_imponible' => "'$var_subtotal_reglon_bs' * -1",
		 'empresa' => "'$var_empresa'",
		 'sucursal' => "'$var_sucursal'",
	 );

	// Insert Compra de Servicios Bs
	$insert_compra_servicios_bs = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
	sc_exec_sql($insert_compra_servicios_bs);
}
//*************************************** FIN BLOQUE DE ENGRESOS EN DIVISAS ***************************************

sc_redir(resumen_gerencial_empresa_sucursal_grid);


?>