<?php

	/*
	
##### #####    ##    ####  #        ##   #####   ####   ####  
  #   #    #  #  #  #      #       #  #  #    # #    # #      
  #   #    # #    #  ####  #      #    # #    # #    #  ####  
  #   #####  ######      # #      ###### #    # #    #      # 
  #   #   #  #    # #    # #      #    # #    # #    # #    # 
  #   #    # #    #  ####  ###### #    # #####   ####   ####	
	
	
		
	*/
	
	
//funcion para modulo de traslado actualiza tabla de resumen y campos en los maestros 
function actualiza_master()
{
	$totcosto = "
	select
	COALESCE(sum(total_det_traslado),0)
	from 
	inventario_traslados_detalle
	where 
	numero_det_traslado_almacen = '{numero_det_traslado_almacen}'
	";

sc_lookup(Dataset, $totcosto);
$t_costo = {Dataset [0][0]};
	
//sc_master_value('total_costo', $t_costo);

//modificado campo numero_res_traslado_almacen por id_res_traslado_almacen, 
$t_costo_sql = "
UPDATE 
inventario_traslados_resumen 
SET total_costo = '$t_costo' 
WHERE 
(id_res_traslado_almacen = '{numero_det_traslado_almacen}')
";

sc_exec_sql($t_costo_sql);

}


//funcion para modulo de traslado actualiza tabla de resumen y campos en los maestros 


// funcion para actualizar la cantidad de renglones de los traslados entre almacenes

function actualiza_renglones()
{
	$totrenglones = "
	select
	count(numero_det_traslado_almacen) 
	from 
	inventario_traslados_detalle 
	where 
	numero_det_traslado_almacen = '{numero_det_traslado_almacen}'
	";

sc_lookup(Dataset1, $totrenglones);
$t_renglones = {Dataset1 [0][0]};
//sc_master_value('total_renglones', $t_renglones);

//modificado campo numero_res_traslado_almacen por id_res_traslado_almacen, 
$t_renglones_sql = "
UPDATE 
inventario_traslados_resumen 
SET total_renglones = '$t_renglones' 
WHERE 
(id_res_traslado_almacen = '{numero_det_traslado_almacen}')
";

sc_exec_sql($t_renglones_sql);
	
}

// funcion para actualizar la cantidad de renglones de los traslados entre almacenes

// funcion para confirmar los traslados
function confirmar_traslado()
{
	$conf_trasl = "
	UPDATE 
	inventario_traslados_resumen 
	SET estatus = 'CONFIRMADO' 
	WHERE 
	(id_res_traslado_almacen = '{id_res_traslado_almacen}')
	";

sc_exec_sql($conf_trasl);
	
	$conf_trasl = "
	UPDATE 
	inventario_traslados_detalle 
	SET estatus = 'CONFIRMADO' 
	WHERE 
	(numero_det_traslado_almacen = '{id_res_traslado_almacen}')
	";

sc_exec_sql($conf_trasl);
		
}// funcion para confirmar los traslados

// funcion para actualizar el stock en los almacenes
function actualiza_stock_traslado()
{
	$totstock_det = "
	SELECT 
	codigo_producto_det_traslado,
	cantidad_det_traslado,
	codigo_almacen_origen,
	codigo_almacen_destino
	FROM
	inventario_traslados_detalle
	WHERE 
	numero_det_traslado_almacen = '{id_res_traslado_almacen}'
	";

sc_select(ds_actualiza_stock_traslado_det, $totstock_det);

	
if ({ds_actualiza_stock_traslado_det} == false){
	echo "No hay registros disponibles...".{ds_actualiza_stock_traslado_det_erro};
}
else
{

	while (!{ds_actualiza_stock_traslado_det}->EOF)
	{
	$t_codigo_producto = {ds_actualiza_stock_traslado_det}->fields["codigo_producto_det_traslado"];
	$t_cant_producto = {ds_actualiza_stock_traslado_det}->fields["cantidad_det_traslado"];
	$t_almacen_origen = {ds_actualiza_stock_traslado_det}->fields["codigo_almacen_origen"];
	$t_almacen_destino = {ds_actualiza_stock_traslado_det}->fields["codigo_almacen_destino"];
	
// consultar stock de almacen de origen devuelve variable global t_stock
//	consultar_stock($t_codigo_producto, $t_almacen_origen,'[usr_empresa]', '[usr_sucursal]');
// el resultado es null porque el producto no ha tenido movimientos  insertar linea en 0 en la tabla de inventario_productos_stock
$sql_consultar_stock_origen = "
SELECT
   stock
FROM
   inventario_productos_stock
WHERE 
   (codigo_producto = '$t_codigo_producto') AND
   (codigo_almacen = '$t_almacen_origen') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
";

sc_lookup(ds_consultar_stock, $sql_consultar_stock_origen);

if (!empty ({ds_consultar_stock})){
	 $t_stock_origen = {ds_consultar_stock[0][0]};
	 sc_set_global ($t_stock_origen); // variable global a devolver
}
else{
	sc_set_global ($t_stock_origen); // variable global a devolver
	[t_stock_origen] = 0;
	sc_exec_sql("
	INSERT INTO inventario_productos_stock (codigo_producto,codigo_almacen,stock,empresa,sucursal,fecha)
   VALUES  ('$t_codigo_producto','$t_almacen_origen','[t_stock_origen]','[usr_empresa]','[usr_sucursal]',NOW())
	");		
		
}		
		
	// restar resultado de almacen origen para actualizar en inventario_productos_stock 
	[cant_nueva_almacen_origen] = [t_stock_origen] - $t_cant_producto ; //resultado de operacion de resta
	// 	actualizar almacen de origen segun el resultado
	   sc_exec_sql("
	UPDATE inventario_productos_stock
   SET  stock = [cant_nueva_almacen_origen]
 WHERE 
   (codigo_producto = '$t_codigo_producto') AND
   (codigo_almacen = '$t_almacen_origen') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
	");
		
		
		
		
// consultar stock de almacen de destino devuelve variable global t_stock
//	consultar_stock($t_codigo_producto, $t_almacen_destino, '[usr_empresa]', '[usr_sucursal]');
// el resultado es null porque el producto no ha tenido movimientos  insertar linea en 0 en la tabla de inventario_productos_stock	
	$sql_consultar_stock_destino = "
SELECT
   stock
FROM
   inventario_productos_stock
WHERE 
   (codigo_producto = '$t_codigo_producto') AND
   (codigo_almacen = '$t_almacen_destino') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
";

sc_lookup(ds_consultar_stock, $sql_consultar_stock_destino);

if (!empty ({ds_consultar_stock})){
	 $t_stock_destino = {ds_consultar_stock[0][0]};
	 sc_set_global ($t_stock_destino); // variable global a devolver
}
else{
	sc_set_global ($t_stock_destino); // variable global a devolver
	[t_stock_destino] = 0;
	sc_exec_sql("
	INSERT INTO inventario_productos_stock (codigo_producto,codigo_almacen,stock,empresa,sucursal,fecha)
   VALUES  ('$t_codigo_producto','$t_almacen_destino','[t_stock_destino]','[usr_empresa]','[usr_sucursal]',NOW())
	");						
		
		}
		
		
		// sumar resultado de almacen destino para actualizar en inventario_productos_stock 
	[cant_nueva_almacen_destino] = [t_stock_destino] + $t_cant_producto ; //resultado de operacion de suma
	// 	actualizar almacen de destino segun el resultado
	   sc_exec_sql("
	UPDATE inventario_productos_stock
   SET  stock = [cant_nueva_almacen_destino]
 WHERE 
   (codigo_producto = '$t_codigo_producto') AND
   (codigo_almacen = '$t_almacen_destino') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
	");
		
		
		
		
		
		
	
		{ds_actualiza_stock_traslado_det}->MoveNext();
	}
		{ds_actualiza_stock_traslado_det}->Close();
}

}// final de la functcion de actualiza_stock_traslado





/*
  ##        # #    #  ####  ##### ######  ####  
 #  #       # #    # #        #   #      #      
#    #      # #    #  ####    #   #####   ####  
######      # #    #      #   #   #           # 
#    # #    # #    # #    #   #   #      #    # 
#    #  ####   ####   ####    #   ######  ####  
*/



//funcion para modulo de ajustes actualiza tabla de resumen y campos en los maestros 
function actualiza_master_ajustes()
{
	$totcostoajuste = "
	select
	sum(total) 
	from 
	inventario_ajustes_detalles
	where 
	numero_ajustes_resumen = '{numero_ajustes_resumen}'
	";

sc_lookup(Dataset, $totcostoajuste);
$t_costo_ajustes = {Dataset [0][0]};
	
sc_master_value('total_costo', $t_costo_ajustes);

	if ($t_costo_ajustes == ''){
	$t_costo_ajustes = 0;
	
	}	
	

$t_costo_sql_ajustes = "
UPDATE 
inventario_ajustes_resumen 
SET total_costo = '$t_costo_ajustes' 
WHERE 
(id_ajustes_resumen = '{numero_ajustes_resumen}')
";

sc_exec_sql($t_costo_sql_ajustes);

}
//funcion para modulo de ajustes actualiza tabla de resumen y campos en los maestros 

// funcion para actualizar la cantidad de renglones de los ajustes entre almacenes

function actualiza_renglones_ajustes()
{
	$totrenglones_ajustes = "
	select
	count(numero_ajustes_resumen) 
	from 
	inventario_ajustes_detalles 
	where 
	numero_ajustes_resumen = '{numero_ajustes_resumen}'
	";

sc_lookup(Dataset1, $totrenglones_ajustes);
$t_renglones_ajustes = {Dataset1 [0][0]};
sc_master_value('total_renglones', $t_renglones_ajustes);

	if ($t_renglones_ajustes == ''){
	$t_renglones_ajustes = 0;
	
	}
	
	

$t_renglones_sql_ajustes = "
UPDATE 
inventario_ajustes_resumen 
SET total_renglones = '$t_renglones_ajustes' 
WHERE 
(id_ajustes_resumen = '{numero_ajustes_resumen}')
";

sc_exec_sql($t_renglones_sql_ajustes);
	
}

// funcion para actualizar la cantidad de renglones de los ajustes entre almacenes


// funcion para actualizar el stock en los almacenes e ajustes
function actualiza_stock_ajustes()
{
	$totstock_ajustes_det = "
    SELECT 
	codigo_productos,
	cantidad,
	codigo_almacen,
	tipo_de_movimiento
	FROM
	inventario_ajustes_detalles
	WHERE 
	numero_ajustes_resumen = '{id_ajustes_resumen}'
	";

sc_select(ds_actualiza_stock_ajustes_det, $totstock_ajustes_det);

	
if ({ds_actualiza_stock_ajustes_det} == false){
	echo "No hay registros disponibles...     .$ds_actualiza_stock_ajustes_det ";
}
else
{

	while (!{ds_actualiza_stock_ajustes_det}->EOF)
	{
	$t_codigo_productos = {ds_actualiza_stock_ajustes_det}->fields["codigo_productos"];
	$t_cantidad = {ds_actualiza_stock_ajustes_det}->fields["cantidad"];
	$t_codigo_almacen = {ds_actualiza_stock_ajustes_det}->fields["codigo_almacen"];
	$t_tipo_de_movimiento = {ds_actualiza_stock_ajustes_det}->fields["tipo_de_movimiento"];
	
// consultar stock de almacen de origen devuelve variable global t_stock
//	consultar_stock($t_codigo_productos, $t_codigo_almacen,'[usr_empresa]', '[usr_sucursal]');
// el resultado es null porque el producto no ha tenido movimientos  insertar linea en 0 en la tabla de inventario_productos_stock
$sql_consultar_stock_origen_ajustes = "
SELECT
   stock
FROM
   inventario_productos_stock
WHERE 
   (codigo_producto = '$t_codigo_productos') AND
   (codigo_almacen = '$t_codigo_almacen') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
";

sc_lookup(ds_consultar_stock_ajustes, $sql_consultar_stock_origen_ajustes);

if (!empty ({ds_consultar_stock_ajustes})){
	 $t_stock_origen_ajustes = {ds_consultar_stock_ajustes[0][0]};
	 sc_set_global ($t_stock_origen_ajustes); // variable global a devolver
}
else{
	sc_set_global ($t_stock_origen_ajustes); // variable global a devolver
	[t_stock_origen_ajustes] = 0;
	sc_exec_sql("
	INSERT INTO inventario_productos_stock (codigo_producto,codigo_almacen,stock,empresa,sucursal,fecha)
   VALUES  ('$t_codigo_productos','$t_codigo_almacen','[t_stock_origen_ajustes]','[usr_empresa]','[usr_sucursal]',NOW())
	");		
		
}	
		
		// toma de deciciones para sumar o restar depende del tipo de movimiento realizado
		if ($t_tipo_de_movimiento == 'DESCARGO DE PRODUCTOS' )
		{
		
			// restar resultado de almacen origen para actualizar en inventario_productos_stock 
	[cant_nueva_almacen_origen] = [t_stock_origen_ajustes] - $t_cantidad ; //resultado de operacion de resta o suma dependiendo del operador matematico
	// 	actualizar almacen de origen segun el resultado
	   sc_exec_sql("
	UPDATE inventario_productos_stock
   SET  stock = [cant_nueva_almacen_origen]
 WHERE 
   (codigo_producto = '$t_codigo_productos') AND
   (codigo_almacen = '$t_codigo_almacen') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
	");
			
			
		
		}
		
		if ($t_tipo_de_movimiento == 'CARGO DE PRODUCTOS' )
		{
		
			
			// restar resultado de almacen origen para actualizar en inventario_productos_stock 
	[cant_nueva_almacen_origen] = [t_stock_origen_ajustes] + $t_cantidad ; //resultado de operacion de resta o suma dependiendo del operador matematico
	// 	actualizar almacen de origen segun el resultado
	   sc_exec_sql("
	UPDATE inventario_productos_stock
   SET  stock = [cant_nueva_almacen_origen]
 WHERE 
   (codigo_producto = '$t_codigo_productos') AND
   (codigo_almacen = '$t_codigo_almacen') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
	");		
	
			
		
		}
		
	
		
	
		{ds_actualiza_stock_ajustes_det}->MoveNext();
	}
		{ds_actualiza_stock_ajustes_det}->Close();
}

}// final de la functcion de actualiza_stock_ajustes

/*
###### #    # #    #  ####  #  ####  #    # ######  ####     #####  ######    # #    # #    # ###### #    # #####   ##   #####  #  ####  
#      #    # ##   # #    # # #    # ##   # #      #         #    # #         # ##   # #    # #      ##   #   #    #  #  #    # # #    # 
#####  #    # # #  # #      # #    # # #  # #####   ####     #    # #####     # # #  # #    # #####  # #  #   #   #    # #    # # #    # 
#      #    # #  # # #      # #    # #  # # #           #    #    # #         # #  # # #    # #      #  # #   #   ###### #####  # #    # 
#      #    # #   ## #    # # #    # #   ## #      #    #    #    # #         # #   ##  #  #  #      #   ##   #   #    # #   #  # #    # 
#       ####  #    #  ####  #  ####  #    # ######  ####     #####  ######    # #    #   ##   ###### #    #   #   #    # #    # #  ####  
*/


// funcion que realiza los calculos de inventario para el stock en los traslados activara t_stock como variable global a devolver
function consultar_stock($codigo_producto, $almacen, $empresa, $sucursal){
$sql_consultar_stock = "
SELECT
   stock
FROM
   inventario_productos_stock
WHERE 
   (codigo_producto = '$codigo_producto') AND
   (codigo_almacen = '$almacen') AND
   (empresa = '$empresa') AND
   (sucursal = '$sucursal')
";

sc_lookup(ds_consultar_stock, $sql_consultar_stock);

if (!empty ({ds_consultar_stock})){
	 $t_stock = {ds_consultar_stock[0][0]};
	 sc_set_global ($t_stock); // variable global a devolver
}
else{
	sc_set_global ($t_stock); // variable global a devolver
	[t_stock] = '0';
	sc_exec_sql("
	INSERT INTO inventario_productos_stock (codigo_producto,codigo_almacen,stock,empresa,sucursal,fecha)
   VALUES  ('$codigo_producto','$almacen','[t_stock]','$empresa','$sucursal',NOW())
	");

} 
}//finaliza funcion de consultar stock





/*
#    #  ####  #####   ##      ###### #    # ##### #####  ######  ####    ##   
##   # #    #   #    #  #     #      ##   #   #   #    # #      #    #  #  #  
# #  # #    #   #   #    #    #####  # #  #   #   #    # #####  #      #    # 
#  # # #    #   #   ######    #      #  # #   #   #####  #      #  ### ###### 
#   ## #    #   #   #    #    #      #   ##   #   #   #  #      #    # #    # 
#    #  ####    #   #    #    ###### #    #   #   #    # ######  ####  #    # */


// funcion para actualizar el stock en los almacenes de nota de entrega
function actualiza_stock_nota_entrega_cargo()
{
	$totstock_nota_det = "
    SELECT 
	codigo_producto,
	cantidad,
	codigo_almacen_salida
	FROM
	nota_entrega_detalle
	WHERE 
	id_resumen = '{id_resumen}'
	";

sc_select(ds_actualiza_stock_nota_det, $totstock_nota_det);

	
if ({ds_actualiza_stock_nota_det} == false){
	echo "No hay registros disponibles...   nota_entrega_detalle  .$ds_actualiza_stock_nota_det ";
}
else
{

	while (!{ds_actualiza_stock_nota_det}->EOF)
	{
	$t_codigo_productos = {ds_actualiza_stock_nota_det}->fields["codigo_producto"];
	$t_cantidad = {ds_actualiza_stock_nota_det}->fields["cantidad"];
	$t_codigo_almacen = {ds_actualiza_stock_nota_det}->fields["codigo_almacen_salida"];
	

	
// consultar stock de almacen de origen devuelve variable global t_stock
//	consultar_stock($t_codigo_productos, $t_codigo_almacen,'[usr_empresa]', '[usr_sucursal]');
// el resultado es null porque el producto no ha tenido movimientos  insertar linea en 0 en la tabla de inventario_productos_stock
$sql_consultar_stock_origen_nota = "
SELECT
   stock
FROM
   inventario_productos_stock
WHERE 
   (codigo_producto = '$t_codigo_productos') AND
   (codigo_almacen = '$t_codigo_almacen') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
";

sc_lookup(ds_consultar_stock_nota, $sql_consultar_stock_origen_nota);

if (!empty ({ds_consultar_stock_nota})){
	 $t_stock_origen_nota = {ds_consultar_stock_nota[0][0]};
	 sc_set_global ($t_stock_origen_nota); // variable global a devolver
}
else{
	sc_set_global ($t_stock_origen_nota); // variable global a devolver
	[t_stock_origen_nota] = 0;
	sc_exec_sql("
	INSERT INTO inventario_productos_stock (codigo_producto,codigo_almacen,stock,empresa,sucursal,fecha)
   VALUES  ('$t_codigo_productos','$t_codigo_almacen','[t_stock_origen_nota]','[usr_empresa]','[usr_sucursal]',NOW())
	");		
		
}	
		
		// toma de deciciones para sumar o restar depende del tipo de movimiento realizado

		
			// restar resultado de almacen origen para actualizar en inventario_productos_stock 
	[cant_nueva_almacen_origen] = [t_stock_origen_nota] - $t_cantidad ; //resultado de operacion de resta o suma dependiendo del operador matematico
	// 	actualizar almacen de origen segun el resultado
	   sc_exec_sql("
	UPDATE inventario_productos_stock
   SET  stock = [cant_nueva_almacen_origen]
 WHERE 
   (codigo_producto = '$t_codigo_productos') AND
   (codigo_almacen = '$t_codigo_almacen') AND
   (empresa = '[usr_empresa]') AND
   (sucursal = '[usr_sucursal]')
	");
			
			
		
		
		
	
	
		
	
		{ds_actualiza_stock_nota_det}->MoveNext();
	}
		{ds_actualiza_stock_nota_det}->Close();
}

}// final de la functcion // funcion para actualizar el stock en los almacenes de nota de entrega






?>