/* Servicios con plantillas */
select cf.id as id_plantilla, cf.configuracion_plantilla, cf.id_cliente, cf.id_servicio,
cf.cod_cliente, cd.nombre_cliente,direccion_cliente,
cd.sucursal as sucursal_cliente,
sc.codigo_producto, ip.nombre_productos,
sc.direccion_servicio, sc.sucursal as sucursal_servicio, sc.estado,
sc.tipo_corte, sc.tipo_servicio
from configuracion_factura cf 
LEFT JOIN clientes_datos cd ON cd.id_cliente = cf.id_cliente
LEFT JOIN servicio_cliente sc ON sc.id_servicio_cliente = cf.id_servicio
LEFT JOIN inventario_productos ip ON ip.codigo_productos = sc.codigo_producto
where cf.empresa = 'tecnoven' 
and ip.sucursal = sc.sucursal

/* Servicios sin plantillas */
select sc.id_cliente, sc.id_servicio_cliente,
cd.cod_cliente, cd.nombre_cliente, cd.direccion_cliente, 
cd.sucursal as sucursal_cliente,
sc.codigo_producto, ip.nombre_productos,
sc.direccion_servicio, sc.sucursal as sucursal_servicio, sc.estado, sc.cod_plantilla
from servicio_cliente sc 
left join clientes_datos cd on cd.id_cliente = sc.id_cliente
left join inventario_productos ip ON ip.codigo_productos = sc.codigo_producto
where sc.empresa = 'tecnoven' and sc.sucursal = 'maracaibo'
and (sc.cod_plantilla = '' or isnull(sc.cod_plantilla) or sc.cod_plantilla = 'Desactivado')
and ip.sucursal = sc.sucursal