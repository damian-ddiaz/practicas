<?php
    // echo 'Evento onAfterUpdate'.{codigo_producto_antes};
/*
if({codigo_producto_antes} <> {codigo_producto}){
		$check_sql_conf_prod = "SELECT CFP.id_configuracion_factura_productos, CF.id
		FROM configuracion_factura CF
		INNER JOIN configuracion_factura_productos CFP ON CF.id = CFP.id_configuracion_factura 
		WHERE CF.id_servicio = {id_servicio_cliente} AND codigo_producto = '{codigo_producto_antes}'";
		sc_lookup(rs_conf_prod, $check_sql_conf_prod);

		if (isset({rs_conf_prod[0][0]})){
			$id_configuracion_factura_productos = {rs_conf_prod[0][0]};
			$last_id_conf = {rs_conf_prod[0][1]};
			
		// 	echo 'Configuracion Factura'.$last_id_conf;
			//borrar configuracion del plan anterior para facturar
			//sc_exec_sql ("DELETE FROM configuracion_factura_productos WHERE id_configuracion_factura_productos = $id_configuracion_factura_productos");
			
			sc_exec_sql ("SELECT * FROM configuracion_factura_productos WHERE empresa = 'usr_empresa' AND sucursal = '[usr_sucursal]' 
and codigo_producto <> '{codigo_producto_antes}' and id_configuracion_factura = 40112");
			
			
			
			//echo "voy a eliminar el registro $id_configuracion_factura_productos";
			//echo "</br>";
			//echo "voy a insertar el id $last_id_conf";
			
			//realizar nuevo insert con plan nuevo
			$sql = "
			INSERT INTO configuracion_factura_productos
			SELECT
				0 AS id_configuracion_factura_productos,
				$last_id_conf AS id_configuracion_factura,
				sc.codigo_producto,
				ProductoDescripcion(sc.codigo_producto, sc.empresa, sc.sucursal) as descripcion,
				1 as cantidad,
				'precio1_productos' as tipo_precio,
				PrecioServicioSucursal(sc.codigo_producto, sc.empresa, sc.sucursal)/((ProductoImpuestoProcentaje(codigo_producto, empresa, sc.sucursal)/100)+1) as precio_unitario,
				ProductoImpuestoProcentaje(codigo_producto, empresa, sc.sucursal) as porc_iva,
				PrecioServicioSucursal(sc.codigo_producto, sc.empresa, sc.sucursal)-(PrecioServicioSucursal(sc.codigo_producto, sc.empresa, sc.sucursal)/((ProductoImpuestoProcentaje(sc.codigo_producto, sc.empresa, sc.sucursal)/100)+1)) as iva_monto,
				0.00 as porc_desc,
				0.00 as desc_monto,
				PrecioServicioSucursal(sc.codigo_producto, sc.empresa, sc.sucursal) as total,
				PrecioServicioSucursal(sc.codigo_producto, sc.empresa, sc.sucursal) as total_renglon,
				PrecioServicioSucursal(sc.codigo_producto, sc.empresa, sc.sucursal) as costo,
				'[usr_login]' as usuario,
				sc.empresa,
				sc.sucursal,
				now() as fecha,
				1 as ip_estacion
			FROM 
				servicio_cliente sc
			WHERE 
				id_cliente = '{id_cliente}'
				AND id_servicio_cliente = {id_servicio_cliente}";
			sc_exec_sql($sql, "conn_example");
		}
	}
*/
$var_codigo_producto = {codigo_producto};
sc_lookup(rs_conf_prod, "SELECT id FROM configuracion_factura WHERE id_servicio = '{id_servicio_cliente}'");

if (isset({rs_conf_prod[0][0]})){
	$idConf = {rs_conf_prod[0][0]};
}

// ELIMINANDO CODIGO ANTERIOR - Damian Diaz - 03-06-2026
sc_exec_sql("delete from configuracion_factura_productos where codigo_producto = '[codigo_producto_anterior]' and empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]' and id_configuracion_factura = $idConf");

// BUSCANDO PRODUCTO - Damian Diaz - 03-06-2026
sc_lookup_field(producto, "select
  ip.nombre_productos,
  ip.impuesto_productos,
  ip.costo_ultimo_productos as costo,
  case '[par_tipo_precio]'
    when 'precio1_productos' then ip.precio1_productos
    when 'precio2_productos' then ip.precio2_productos
    when 'precio3_productos' then ip.precio3_productos
    else ip.precio1_productos
  end as precio_seleccionado,
  ci.valor_iva as porc_iva
from
  inventario_productos ip
left join configuracion_iva ci
  on ci.codigo_iva = ip.impuesto_productos
where
  ip.codigo_productos = '$var_codigo_producto'
  and ip.empresa = '[usr_empresa]'
  and ip.sucursal = '[usr_sucursal]'
  and ci.empresa = '[usr_empresa]'
  and ci.sucursal = '[usr_sucursal]'");

$var_nombre_productos = {producto[0]['nombre_productos']};
$var_precio_unitario = {producto[0]['precio_seleccionado']};
$var_porc_iva 		 = {producto[0]['porc_iva']};
$var_monto_iva		 = round(($var_precio_unitario * $var_porc_iva) /100,2);
$var_costo 		 	 = {producto[0]['costo']};

// ip - fecha
$var_ip_estacion 	= {ip_estacion};
$var_fecha_registro = {fecha_registro};


// INSERTANDO EL NUEVO CODIGO

// 1. Hacemos la operación matemática primero en PHP de forma segura
$var_total_renglon = $var_precio_unitario + $var_monto_iva;

// 2. Ejecutamos el INSERT con las comillas y comentarios corregidos
sc_exec_sql("INSERT INTO `configuracion_factura_productos` (
    `id_configuracion_factura`,
    `codigo_producto`,
    `descripcion`,
    `cantidad`,
    `tipo_precio`,
    `precio_unitario`,
    `porc_iva`,
    `iva_monto`,
    `porc_desc`,
    `desc_monto`,
    `total`,
    `total_renglon`,
    `costo`,
    `usuario`,
    `empresa`,
    `sucursal`,
    `fecha`,
    `ip_estacion`
)
VALUES
(
    $idConf,
    '$var_codigo_producto',
    '$var_nombre_productos',
    1,
    '[par_tipo_precio]',
    $var_precio_unitario,
    $var_porc_iva,
    $var_monto_iva,
    0,
    0,
    $var_precio_unitario,
    $var_total_renglon,
    $var_costo,
    '[usr_login]',
    '[usr_empresa]',
    '[usr_sucursal]',
    '$var_fecha_registro',
    '$var_ip_estacion'
)");


sc_ajax_javascript("scBtnFn_sys_format_reload");
?>