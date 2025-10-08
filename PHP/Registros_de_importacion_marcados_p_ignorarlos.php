/* TECNOVEN C.A. - ASLEIDI - importacion no. 0000000268 - id_conciliacion No. 1244 */
/* Estas transacciones se les coloco conciliado = 'PENDIENTE'
SELECT fecha_banco, referencia, referencia_transaccion, descripcion, tipo, multiple, conciliado, monto,
 id_banco_conciliacion_importacion, id_conciliacion, codigo_banco, tipo_conciliacion, usuario, fecha, 
 ip_estacion, empresa, sucursal, numero_importacion from banco_conciliacion_importarcion
  where (empresa = 'UNION NETWORK, C.A' AND conciliado = 'NO' AND id_banco = 332 AND
   fecha_banco <= '2025-09-08') order by numero_importacion desc LIMIT 0,9  
/*1458 - Registros */


select * from banco_conciliacion_importarcion  where numero_importacion = '0000000268' and conciliado = 'NO'
order by fecha_banco desc
/*169 - Registros */


update banco_conciliacion_importarcion set conciliado = 'NO' 
where (empresa = 'UNION NETWORK, C.A' AND conciliado = 'PENDIENTE' 
AND id_banco = 332 AND   fecha_banco <= '2025-09-08')
   
/** SE HIZO ESTO */
UPDATE banco_conciliacion_importarcion I SET conciliado = 'SI', I.referencia_transaccion = I.referencia 
WHERE I.empresa = 'UNION NETWORK, C.A' 
and I.ID_BANCO = 332 AND I.CONCILIADO = 'NO' 
AND RIGHT(I.REFERENCIA,6) in 
(SELECT RIGHT(V.referencia,6) FROM ventas_transacciones_detalles V 
WHERE V.CONCILIADO = 'SI' AND V.EMPRESA = I.empresa AND I.ID_BANCO = V.id_banco 
AND I.MONTO = V.monto_bs AND I.fecha_banco = V.fecha_transaccion AND STATUS = 'FACTURADO') AND I.MONTO > 0 AND TIPO = 'c'