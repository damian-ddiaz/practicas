-- webservices.Vista_movimiento_conciliacion_concliados_no source
'<?php

CREATE OR REPLACE ALGORITHM = UNDEFINED VIEW `Vista_movimiento_conciliacion_concliados_no` AS
select
    'VENTAS' AS `modulo`,
    'CREDITO' AS `tipo`,
    `vtd`.`fecha_transaccion` AS `fecha_transaccion`,
    `btp`.`nombre_tipo_pago` AS `tipo_pago`,
    `bfp`.`nombre_formas_pago` AS `forma_pago`,
    `bfp`.`codigo_moneda` AS `codigo_moneda`,
    `vtd`.`referencia` AS `referencia`,
    `vtd`.`tasa_cambio` AS `tasa_cambio`,
    `vtd`.`monto` AS `monto`,
    `vtd`.`monto_bs` AS `monto_bs`,
    `vtd`.`status` AS `status`,
    `vtd`.`id_banco` AS `id_banco_origen`,
    0 AS `id_banco_destino`,
    if(`vtd`.`descripcion` is null,
    '',
    `vtd`.`descripcion`) AS `descripcion`,
    `vtd`.`id_ventas_transacciones` AS `id_ventas_compras`,
    `vtd`.`conciliado` AS `conciliado`,
    `vtd`.`empresa` AS `empresa`,
    `vtd`.`sucursal` AS `sucursal`,
    `vtd`.`id_ventas_transacciones_detalles` AS `id_ventas_compras_bancos_detalles`,
    ifnull(`vtd`.`id_cxc_documento`, 0) AS `id_cxc_cxp_documento`,
    ifnull(`vtd`.`id_cxc_cobro_resumen`, 0) AS `id_cxc_cxp_resumen`,
    `vtd`.`tipo_conciliacion` AS `tipo_conciliacion`,
    ifnull(`vtd`.`multiple`, 0) AS `multiple`,
    ifnull(`vtd`.`id_conciliacion`, 0) AS `id_conciliacion`,
    0 AS `id_transferencia`
from
    ((`ventas_transacciones_detalles` `vtd`
left join `banco_tipo_pago` `btp` on
    (`btp`.`codigo_tipo_pago` = `vtd`.`tipo_pago`))
left join `banco_formas_pago` `bfp` on
    (`bfp`.`codigo_formas_pago` = `vtd`.`forma_pago`))
where
    `vtd`.`status` = 'FACTURADO'
    and `btp`.`empresa` = `vtd`.`empresa`
    and `btp`.`sucursal` = `vtd`.`sucursal`
    and `bfp`.`empresa` = `vtd`.`empresa`
    and `bfp`.`sucursal` = `vtd`.`sucursal`
    and (`vtd`.`conciliado` <> 'SI'
        and `vtd`.`id_conciliacion` = 0
        or `vtd`.`conciliado` is null
        or `vtd`.`conciliado` = 'NO'
        or `vtd`.`conciliado` = '')
group by
    `vtd`.`id_ventas_transacciones_detalles`
union all
select
    'COMPRAS' AS `modulo`,
    'DEBITO' AS `tipo`,
    `ctd`.`fecha_transaccion` AS `fecha_transaccion`,
    `btp`.`nombre_tipo_pago` AS `nombre_tipo_pago`,
    `bfp`.`nombre_formas_pago` AS `nombre_formas_pago`,
    `bfp`.`codigo_moneda` AS `codigo_moneda`,
    `ctd`.`referencia` AS `referencia`,
    `ctd`.`tasa_cambio` AS `tasa_cambio`,
    `ctd`.`monto` AS `monto`,
    `ctd`.`monto_bs` AS `monto_bs`,
    `ctd`.`status` AS `status`,
    `ctd`.`id_banco` AS `id_banco_origen`,
    0 AS `id_banco_destino`,
    if(`ctd`.`descripcion` is null,
    '',
    `ctd`.`descripcion`) AS `descripcion`,
    `ctd`.`id_compra` AS `id_ventas_compras`,
    `ctd`.`conciliado` AS `conciliado`,
    `ctd`.`empresa` AS `empresa`,
    `ctd`.`sucursal` AS `sucursal`,
    `ctd`.`id_compras_transacciones_detalles` AS `id_ventas_compras_bancos_detalles`,
    ifnull(`ctd`.`id_cxp_documentos`, 0) AS `id_cxc_cxp_documento`,
    ifnull(`ctd`.`id_cxp_pago_resumen`, 0) AS `id_cxc_cxp_resumen`,
    `ctd`.`tipo_conciliacion` AS `tipo_conciliacion`,
    0 AS `multiple`,
    ifnull(`ctd`.`id_conciliacion`, 0) AS `id_conciliacion`,
    0 AS `id_transferencia`
from
    ((`compras_transacciones_detalles` `ctd`
left join `banco_tipo_pago` `btp` on
    (`btp`.`codigo_tipo_pago` = `ctd`.`tipo_pago`))
left join `banco_formas_pago` `bfp` on
    (`bfp`.`codigo_formas_pago` = `ctd`.`forma_pago`))
where
    `ctd`.`status` = 'PROCESADO'
    and `btp`.`empresa` = `ctd`.`empresa`
    and `btp`.`sucursal` = `ctd`.`sucursal`
    and `bfp`.`empresa` = `ctd`.`empresa`
    and `bfp`.`sucursal` = `ctd`.`sucursal`
    and (`ctd`.`conciliado` <> 'SI'
        and `ctd`.`id_conciliacion` = 0
        or `ctd`.`conciliado` is null
        or `ctd`.`conciliado` = 'NO'
        or `ctd`.`conciliado` = '')
group by
    `ctd`.`id_compras_transacciones_detalles`
union all
select
    'BANCOS' AS `modulo`,
    case
        when `btm`.`id_banco_origen` = 0
        and `btm`.`id_banco_destino` > 0
        and `btm`.`tipo` = 'CREDITO' then 'CREDITO'
        when `btm`.`id_banco_origen` = 0
        and `btm`.`id_banco_destino` > 0
        and `btm`.`tipo` = 'DEBITO' then 'DEBITO'
    end AS `tipo`,
    `btm`.`fecha_transaccion` AS `fecha_transaccion`,
    case
        when `btm`.`id_transferencia` = 0
        and `btm`.`tipo` = 'DEBITO' then 'RETIRO'
        when `btm`.`id_transferencia` = 0
        and `btm`.`tipo` = 'CREDITO' then 'DEPOSITO'
        else 'TRANSFERENCIA'
    end AS `tipo_pago`,
    case
        when `btm`.`id_transferencia` = 0
        and `btm`.`tipo` = 'DEBITO' then 'RETIRO'
        when `btm`.`id_transferencia` = 0
        and `btm`.`tipo` = 'CREDITO' then 'DEPOSITO'
        else `bt`.`nombre_transferencia`
    end AS `forma_pago`,
    `bc`.`codigo_moneda` AS `codigo_moneda`,
    `btm`.`referencia` AS `referencia`,
    `btm`.`tasa_cambio` AS `tasa_cambio`,
    if(`btm`.`monto_debito_origen` = 0,
    `btm`.`monto_a_creditar`,
    `btm`.`monto_debito_origen`) AS `monto`,
    if(`btm`.`monto_debito_origen_bs` = 0,
    `btm`.`monto_a_creditar_bs`,
    `btm`.`monto_debito_origen_bs`) AS `monto_bs`,
    `btm`.`status` AS `status`,
    `btm`.`id_banco_origen` AS `id_banco_origen`,
    `btm`.`id_banco_destino` AS `id_banco_destino`,
    `btm`.`descripcion` AS `descripcion`,
    0 AS `id_ventas_compras`,
    'NO' AS `conciliado`,
    `btm`.`empresa` AS `empresa`,
    `btm`.`sucursal` AS `sucursal`,
    `btm`.`id_movimiento` AS `id_ventas_compras_bancos_detalles`,
    0 AS `id_cxc_cxp_documento`,
    0 AS `id_cxc_cxp_resumen`,
    `btm`.`tipo_conciliacion` AS `tipo_conciliacion`,
    0 AS `multiple`,
    ifnull(`btm`.`id_conciliacion`, 0) AS `id_conciliacion`,
    `btm`.`id_transferencia` AS `id_transferencia`
from
    ((`banco_transferencias_movimientos` `btm`
left join `banco_transferencias` `bt` on
    (`bt`.`id_transferencias` = `btm`.`id_transferencia`))
left join `bancos` `bc` on
    (`bc`.`id` = `btm`.`id_banco_origen`
        or `bc`.`id` = `btm`.`id_banco_destino`))
where
    `btm`.`status` = 'CONFIRMADO'
    and (`btm`.`conciliado` <> 'SI'
        and `btm`.`id_conciliacion` = 0
        or `btm`.`conciliado` is null
        or `btm`.`conciliado` = 'NO'
        or `btm`.`conciliado` = '')
group by
    `btm`.`id_movimiento`;
?>