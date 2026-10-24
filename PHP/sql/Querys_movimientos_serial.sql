-- Querys para obtener los Saldo de los Bancos 
-- INgresos (ventas_transacciones_detalles) - Cancelacion de Facturas y Abonos de Clientes
-- Menos las Retenciones de Ventas (tipo_pago = RTV)
select id_ventas_transacciones_detalles as id, id_banco, fecha_transaccion, fecha as fecha_registro,
id_conciliacion, conciliado, nro_conciliacion, tipo_conciliacion,
tipo_pago, forma_pago,
origen, referencia, descripcion, monto, tasa_cambio, monto_bs, status,  
empresa, sucursal
from ventas_transacciones_detalles where empresa = 'J181228500' and status = 'FACTURADO' and tipo_pago <> 'RTV'

-- INgresos (banco_transferencias_movimientos) - Transferencias desde otros bancos
select btm.id_movimiento as id, btm.id_banco_origen, btm.id_banco_destino, btm.fecha_transaccion, 
date(btm.fecha_registro) as fecha_registro, 
btm.id_conciliacion, btm.conciliado, btm.nro_conciliacion, btm.tipo_conciliacion,
if(btm.id_transferencia = 0,'DEPOSITO','TRANSFERENCIA') as tipo_pago,
if(btm.id_transferencia = 0,'DEPOSITO',bt.nombre_transferencia) as forma_pago, '' as origen, 
btm.referencia, btm.descripcion
from banco_transferencias_movimientos btm 
left join banco_transferencias bt 
on bt.id_transferencias = btm.id_transferencia
where btm.empresa = 'J181228500' and status = 'CONFIRMADO'
and (id_banco_origen = 0 and id_banco_destino > 0)