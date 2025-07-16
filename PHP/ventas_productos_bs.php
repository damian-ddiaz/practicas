select
  tfp.id_ventas_transacciones as id_ventas_transacciones,
  tfp.id_ventas_transacciones_detalles,
  DATE_FORMAT(tfp.fecha_transaccion, '%Y-%m') as fecha,
  tfp.tipo_pago,
  tfp.forma_pago,
  /*
  SUM(tfp.monto) AS sub_total,
  SUM(tfp.monto_bs) AS sub_total_bs,
  */
  tfp.fecha_transaccion,
  bfp.codigo_moneda,
  vd.codigo_almacen as codigo_almacen
from
  transacciones_x_formas_pago tfp
left join banco_formas_pago bfp ON bfp.codigo_tipo_pago = tfp.tipo_pago AND bfp.codigo_formas_pago = tfp.forma_pago
left join ventas_detalles vd ON vd.id_detalle = tfp.id_ventas_transacciones
where
  tfp.empresa = 'Will Invest'
  AND tfp.sucursal = 'La lagunita 2'
  AND bfp.empresa = 'Will Invest'
  AND bfp.sucursal = 'La lagunita 2' 
  AND tfp.id_ventas_transacciones_detalles > 0
  AND DATE_FORMAT(tfp.fecha_transaccion, '%Y-%m') = '2025-06'
  AND vd.codigo_almacen <> ' '
  