'<?php
    $check_sql = "-- *******************************************************************
-- BLOQUE 1: TRANSACCIONES DE VENTAS (Módulo: VENTAS, Tipo: CREDITO)
-- *******************************************************************
SELECT
    'VENTAS' AS modulo,
    'CREDITO' AS tipo,
    vtd.fecha_transaccion,
    btp.nombre_tipo_pago AS tipo_pago,
    bfp.nombre_formas_pago AS forma_pago,
    bfp.codigo_moneda,
    vtd.referencia,
    vtd.tasa_cambio,
    vtd.monto,
    vtd.monto_bs,
    vtd.status,
    vtd.id_banco AS id_banco_origen,
    0 AS id_banco_destino,
    IF(vtd.descripcion IS NULL, '', vtd.descripcion) AS descripcion,
    vtd.id_ventas_transacciones AS id_ventas_compras,
    vtd.conciliado,
    vtd.empresa,
    vtd.sucursal,
    vtd.id_ventas_transacciones_detalles AS id_ventas_compras_bancos_detalles,
    IFNULL(vtd.id_cxc_documento, 0) AS id_cxc_cxp_documento,
    IFNULL(vtd.id_cxc_cobro_resumen, 0) AS id_cxc_cxp_resumen,
    vtd.tipo_conciliacion,
    IFNULL(vtd.multiple, 0) AS multiple,
    IFNULL(vtd.id_conciliacion, 0) AS id_conciliacion,
    0 AS id_transferencia
FROM
    webservices.ventas_transacciones_detalles vtd
LEFT JOIN
    webservices.banco_tipo_pago btp ON btp.codigo_tipo_pago = vtd.tipo_pago
                                     AND btp.empresa = vtd.empresa
                                     AND btp.sucursal = vtd.sucursal
LEFT JOIN
    webservices.banco_formas_pago bfp ON bfp.codigo_formas_pago = vtd.forma_pago
                                      AND bfp.empresa = vtd.empresa
                                      AND bfp.sucursal = vtd.sucursal
WHERE
    vtd.status = 'FACTURADO'
    AND (vtd.conciliado <> 'SI' AND vtd.id_conciliacion = 0 OR 
    vtd.conciliado IS NULL OR vtd.conciliado = 'NO' OR vtd.conciliado = '')
GROUP BY
    vtd.id_ventas_transacciones_detalles

UNION ALL

-- *******************************************************************
-- BLOQUE 2: TRANSACCIONES DE COMPRAS (Módulo: COMPRAS, Tipo: DEBITO)
-- *******************************************************************
SELECT
    'COMPRAS' AS modulo,
    'DEBITO' AS tipo,
    ctd.fecha_transaccion,
    btp.nombre_tipo_pago AS tipo_pago,
    bfp.nombre_formas_pago AS forma_pago,
    bfp.codigo_moneda,
    ctd.referencia,
    ctd.tasa_cambio,
    ctd.monto * -1 AS monto,             -- Monto negado
    ctd.monto_bs * -1 AS monto_bs,       -- Monto BS negado
    ctd.status,
    ctd.id_banco AS id_banco_origen,
    0 AS id_banco_destino,
    IF(ctd.descripcion IS NULL, '', ctd.descripcion) AS descripcion,
    ctd.id_compra AS id_ventas_compras,
    ctd.conciliado,
    ctd.empresa,
    ctd.sucursal,
    ctd.id_compras_transacciones_detalles AS id_ventas_compras_bancos_detalles,
    IFNULL(ctd.id_cxp_documentos, 0) AS id_cxc_cxp_documento,
    IFNULL(ctd.id_cxp_pago_resumen, 0) AS id_cxc_cxp_resumen,
    ctd.tipo_conciliacion,
    0 AS multiple,
    IFNULL(ctd.id_conciliacion, 0) AS id_conciliacion,
    0 AS id_transferencia
FROM
    webservices.compras_transacciones_detalles ctd
LEFT JOIN
    webservices.banco_tipo_pago btp ON btp.codigo_tipo_pago = ctd.tipo_pago
                                     AND btp.empresa = ctd.empresa
                                     AND btp.sucursal = ctd.sucursal
LEFT JOIN
    webservices.banco_formas_pago bfp ON bfp.codigo_formas_pago = ctd.forma_pago
                                      AND bfp.empresa = ctd.empresa
                                      AND bfp.sucursal = ctd.sucursal
WHERE
    ctd.status = 'PROCESADO'
    AND (ctd.conciliado <> 'SI' AND ctd.id_conciliacion = 0 OR
     ctd.conciliado IS NULL OR ctd.conciliado = 'NO' OR ctd.conciliado = '')
GROUP BY
    ctd.id_compras_transacciones_detalles

UNION ALL

-- *******************************************************************
-- BLOQUE 3: MOVIMIENTOS BANCARIOS (Módulo: BANCOS, Tipo: Variable)
-- *******************************************************************
SELECT
    'BANCOS' AS modulo,
    CASE
        WHEN btm.id_banco_origen = 0 AND btm.id_banco_destino > 0 AND btm.tipo = 'CREDITO' THEN 'CREDITO'
        WHEN btm.id_banco_origen = 0 AND btm.id_banco_destino > 0 AND btm.tipo = 'DEBITO' THEN 'DEBITO'
    END AS tipo, -- Define si es Crédito (Entrada) o Débito (Salida)
    btm.fecha_transaccion,
    CASE
        WHEN btm.id_transferencia = 0 AND btm.tipo = 'DEBITO' THEN 'RETIRO'
        WHEN btm.id_transferencia = 0 AND btm.tipo = 'CREDITO' THEN 'DEPOSITO'
        ELSE 'TRANSFERENCIA'
    END AS tipo_pago,
    CASE
        WHEN btm.id_transferencia = 0 AND btm.tipo = 'DEBITO' THEN 'RETIRO'
        WHEN btm.id_transferencia = 0 AND btm.tipo = 'CREDITO' THEN 'DEPOSITO'
        ELSE bt.nombre_transferencia
    END AS forma_pago,
    bc.codigo_moneda,
    btm.referencia,
    btm.tasa_cambio,
    IF(btm.tipo = 'DEBITO', btm.monto_base * -1, btm.monto_base) AS monto,        -- Negado si es DEBITO
    IF(btm.tipo = 'DEBITO', btm.monto_base_bs * -1, btm.monto_base_bs) AS monto_bs, -- Negado si es DEBITO (BS)
    btm.status,
    btm.id_banco_origen,
    btm.id_banco_destino,
    btm.descripcion,
    0 AS id_ventas_compras,
    'NO' AS conciliado, -- Asumido 'NO' en movimientos bancarios
    btm.empresa,
    btm.sucursal,
    btm.id_movimiento AS id_ventas_compras_bancos_detalles,
    0 AS id_cxc_cxp_documento,
    0 AS id_cxc_cxp_resumen,
    btm.tipo_conciliacion,
    0 AS multiple,
    IFNULL(btm.id_conciliacion, 0) AS id_conciliacion,
    btm.id_transferencia
FROM
    webservices.banco_transferencias_movimientos btm
LEFT JOIN
    webservices.banco_transferencias bt ON bt.id_transferencias = btm.id_transferencia
LEFT JOIN
    webservices.bancos bc ON bc.id = btm.id_banco_origen OR bc.id = btm.id_banco_destino
WHERE
    btm.status = 'CONFIRMADO'
    AND (btm.conciliado <> 'SI' AND btm.id_conciliacion = 0 OR btm.conciliado IS NULL OR 
    btm.conciliado = 'NO' OR btm.conciliado = '')
GROUP BY
    btm.id_movimiento;";
        
    sc_select(my_data, $check_sql);


?>