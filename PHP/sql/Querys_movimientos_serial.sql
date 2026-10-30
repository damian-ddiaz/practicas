-- Querys Movimientos de Aeriales
SELECT
  mps.id_movimiento,
  mps.id_resumen,
  mps.codigo_producto,
  mps.cod_almacen AS cod_almacen_salida,
  ia.nombre_almacen AS almacen_salida,
  itd.codigo_almacen_destino,
  mps.modulo,
  mps.proceso,
  mps.serial,
  /* Esta es la forma precisa de obtener la fecha según el proceso */
  DATE(
    CASE 
      WHEN mps.proceso = 'compras' THEN cd.fecha
      WHEN mps.proceso = 'traslados' THEN itd.fecha
      WHEN mps.proceso = 'ajuste descargo' THEN iad.fecha
      WHEN mps.proceso = 'ajuste cargo' THEN iad.fecha
      WHEN mps.proceso = 'factura ventas' THEN vd.fecha
      WHEN mps.proceso = 'nota entrega' THEN ntd.fecha
      ELSE NULL 
    END
  ) AS fecha_movimiento
FROM
  movimientos_producto_seriales mps
LEFT JOIN compras_detalles cd ON cd.id_compra = mps.id_resumen
LEFT JOIN inventario_almacen ia ON ia.codigo_almacen = mps.cod_almacen 
  AND ia.empresa = 'J181228500' AND ia.sucursal = 'J181228500-SUC00M'
LEFT JOIN inventario_traslados_detalle itd ON itd.numero_det_traslado_almacen = mps.id_resumen
  AND itd.empresa = 'J181228500' AND itd.sucursal = 'J181228500-SUC00M'
LEFT JOIN inventario_ajustes_detalles iad ON iad.numero_ajustes_resumen = mps.id_resumen
  AND iad.empresa = 'J181228500' AND iad.sucursal = 'J181228500-SUC00M'
LEFT JOIN ventas_detalles vd ON vd.id_detalle = mps.id_resumen
  AND vd.empresa = 'J181228500' AND vd.sucursal = 'J181228500-SUC00M'
LEFT JOIN nota_entrega_detalle ntd ON ntd.id_resumen = mps.id_resumen
  AND ntd.empresa = 'J181228500' AND ntd.sucursal = 'J181228500-SUC00M'
WHERE
  mps.empresa = 'J181228500'
  AND mps.sucursal = 'J181228500-SUC00M'
  AND mps.codigo_producto = 'Vt064'
  AND mps.serial = 'TPLINK-00001'
ORDER BY
  mps.id_movimiento
