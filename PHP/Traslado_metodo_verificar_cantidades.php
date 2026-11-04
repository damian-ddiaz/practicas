<?php
	//cantidades de productos 
	$tabla = "movimientos_producto_seriales m, inventario_productos i ";
	$campos = "count(m.serial)";

	$condicion = "m.id_resumen = {id_res_traslado_almacen}
		AND i.codigo_productos = m.codigo_producto
		AND 	i.manejaser_productos ='SI'
		AND m.modulo = 'inventario' AND m.proceso = 'traslados'
		AND i.empresa='[usr_empresa]' AND i.sucursal='[usr_sucursal]'";

	// SELECT sum(cantidad) from inventario_ajustes_detalles where numero_ajustes_resumen=1042
	// verificando cantidad de seriales incluidos
	$tabla2 = "inventario_traslados_detalle a,  inventario_productos i";
	$campos2 = "sum(a.cantidad_det_traslado)";
	$condicion2 = "a.numero_det_traslado_almacen = {id_res_traslado_almacen} 
			AND i.codigo_productos = a.codigo_producto_det_traslado
			AND i.manejaser_productos ='SI' 
			AND i.empresa='[usr_empresa]' AND i.sucursal='[usr_sucursal]'";
		
	sc_lookup (rs,"SELECT $campos FROM $tabla WHERE $condicion"); //cantidades productos con seriales

	sc_lookup (rss,"SELECT $campos2 FROM $tabla2 WHERE $condicion2"); // cantidades seriales

	$canti_pro={rs[0][0]}; // movimientos_producto_seriales m
	$canti_ser={rss[0][0]}; // inventario_traslados_detalle a

	//echo "cantidad productos". $canti_pro."<br>";
	//echo "cantidad seriales". $canti_ser."<br>";;

	if($canti_pro<>$canti_ser){
	//	echo 'Condicion # 1'.'<br>';
		$err = true;
		//$res_err  = true;
	}
	else{
		//echo 'Condicion # 2'.'<br>';

		$err = false;

		//es_err  = false;
	}
	//echo 'error'.'<br>'.$err;
return $err;
?>