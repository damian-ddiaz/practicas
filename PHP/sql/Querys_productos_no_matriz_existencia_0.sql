/* 1 desactivar todos los productos a nivel nacional no matriz que tengan stock 0 en sucursales (no activo fijo). */
SELECT 
    ip.codigo_productos, 
    ip.nombre_productos, 
    ip.marca_productos, 
    ip.estatus_productos, 
    ip.manejaser_productos, 
    ip.producto_matriz, 
    ips.codigo_almacen, 
    ia.nombre_almacen,
    ips.stock,
    ip.empresa, 
    ip.sucursal 
FROM inventario_productos ip 
INNER JOIN inventario_productos_stock ips ON ips.codigo_producto = ip.codigo_productos
INNER JOIN inventario_almacen ia ON ia.codigo_almacen = ips.codigo_almacen
WHERE ip.empresa = 'tecnoven' 
  AND (ip.producto_matriz = 'NO' OR ISNULL(ip.producto_matriz))
  AND ips.empresa = ip.empresa AND ips.sucursal = ip.sucursal
  AND ia.empresa = ips.empresa AND ia.sucursal = ips.sucursal
  AND ia.activo_fijo_almacen = 'NO'
HAVING ips.stock = 0
ORDER BY ip.nombre_productos

/* 2 reporte e icarosoft version 3 que se pueda filtrar productos con stock pero también poder 
discriminar matriz y no matriz y por despositos y sucursales ahora a menos que exista un reportes 
por favor indícarnos cual es*/
SELECT 
    ip.codigo_productos, 
    ip.nombre_productos, 
    ip.marca_productos, 
    ip.estatus_productos, 
    ip.manejaser_productos, 
    ip.producto_matriz, 
    ips.codigo_almacen, 
    ia.nombre_almacen,
    ips.stock,
    ip.empresa, 
    ip.sucursal 
FROM inventario_productos ip 
INNER JOIN inventario_productos_stock ips ON ips.codigo_producto = ip.codigo_productos
INNER JOIN inventario_almacen ia ON ia.codigo_almacen = ips.codigo_almacen
WHERE ip.empresa = 'tecnoven' 
  AND ips.empresa = ip.empresa AND ips.sucursal = ip.sucursal
  AND ia.empresa = ips.empresa AND ia.sucursal = ips.sucursal
  AND ia.activo_fijo_almacen = 'NO'
HAVING ips.stock > 0
ORDER BY ip.nombre_productos