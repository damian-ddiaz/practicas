-- Optimizacion Webservices
-- Tabla: banco_conciliacion_importarcion
    -- Create Origigal
    CREATE TABLE `banco_conciliacion_importarcion` (
    `id_banco_conciliacion_importacion` int(11) NOT NULL AUTO_INCREMENT,
    `id_conciliacion` int(10) DEFAULT NULL,
    `codigo_banco` varchar(20) NOT NULL,
    `fecha_banco` date NOT NULL,
    `referencia` varchar(150) NOT NULL,
    `descripcion` varchar(500) DEFAULT NULL,
    `monto` decimal(10,2) NOT NULL,
    `conciliado` varchar(10) NOT NULL,
    `referencia_transaccion` varchar(150) DEFAULT NULL,
    `tipo_conciliacion` varchar(20) DEFAULT NULL,
    `usuario` varchar(200) DEFAULT NULL,
    `fecha` date DEFAULT NULL,
    `ip_estacion` varchar(60) DEFAULT NULL,
    `empresa` varchar(20) DEFAULT NULL,
    `sucursal` varchar(20) DEFAULT NULL,
    `numero_importacion` varchar(10) DEFAULT NULL,
    `tipo` varchar(1) DEFAULT NULL,
    `multiple` int(11) DEFAULT NULL,
    `referencia_padre` varchar(150) DEFAULT NULL,
    `id_banco` int(11) DEFAULT NULL,
    PRIMARY KEY (`id_banco_conciliacion_importacion`),
    KEY `importacion` (`codigo_banco`,`referencia`,`monto`),
    KEY `banco_conciliacion_importarcion_fecha_banco_IDX` (`fecha_banco`,`empresa`) USING BTREE,
    KEY `banco_conciliacion_importarcion_fecha_banco_empre_IDX` (`fecha_banco`,`conciliado`,`empresa`) USING BTREE,
    KEY `banco_conciliacion_importarcion_empresa_1tipo_IDX` (`empresa`,`tipo`) USING BTREE,
    KEY `banco_conciliacion_importarcion_codigo_banco_IDX` (`codigo_banco`,`empresa`,`conciliado`) USING BTREE,
    KEY `id_banco` (`id_banco`),
    KEY `banco_conciliacion_importarcion_id_banco_IDX` (`id_banco`,`empresa`,`codigo_banco`) USING BTREE,
    KEY `banco_conciliacion_importarcion_empresa_IDX` (`empresa`,`codigo_banco`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=410597 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    
    -- Seleccion para Validar si Existen Registros Duplicados con la misma refrencia 
    SELECT 
        b1.id_banco_conciliacion_importacion,
        b1.empresa,
        b1.codigo_banco,
        b1.referencia AS referencia_original,
        b1.fecha_banco,
        b1.monto,
        b1.tipo,
        IF(
            b1.id_banco_conciliacion_importacion = MIN(b1.id_banco_conciliacion_importacion) OVER(
                PARTITION BY b1.empresa, b1.codigo_banco, b1.referencia, b1.fecha_banco, b1.monto, b1.tipo
            ),
            b1.referencia,
            CONCAT(
                b1.referencia, 
                '-', 
                ROW_NUMBER() OVER(
                    PARTITION BY b1.empresa, b1.codigo_banco, b1.referencia, b1.fecha_banco, b1.monto, b1.tipo 
                    ORDER BY b1.id_banco_conciliacion_importacion
                ) - 1
            )
        ) AS nueva_referencia
    FROM banco_conciliacion_importarcion b1
    INNER JOIN (
        SELECT 
            empresa, codigo_banco, referencia, fecha_banco, monto, tipo
        FROM banco_conciliacion_importarcion
        GROUP BY empresa, codigo_banco, referencia, fecha_banco, monto, tipo
        HAVING COUNT(*) > 1
    ) b2 ON b1.empresa = b2.empresa 
        AND b1.codigo_banco = b2.codigo_banco 
        AND b1.referencia = b2.referencia 
        AND b1.fecha_banco = b2.fecha_banco 
        AND b1.monto = b2.monto 
        AND b1.tipo = b2.tipo
    ORDER BY 
        b1.empresa,b1.id_banco_conciliacion_importacion, b1.referencia, b1.fecha_banco;

    -- Seleccion para Validar si Existen Registros Duplicados con la misma refrencia_transaccion
    SELECT 
        b1.id_banco_conciliacion_importacion,
        b1.empresa,
        b1.codigo_banco,
        b1.referencia_transaccion AS referencia_transaccion_original,
        b1.fecha_banco,
        b1.monto,
        b1.tipo,
        IF(
            b1.id_banco_conciliacion_importacion = MIN(b1.id_banco_conciliacion_importacion) OVER(
                PARTITION BY b1.empresa, b1.codigo_banco, b1.referencia_transaccion, b1.fecha_banco, b1.monto, b1.tipo
            ),
            b1.referencia_transaccion,
            CONCAT(
                b1.referencia_transaccion, 
                '-', 
                ROW_NUMBER() OVER(
                    PARTITION BY b1.empresa, b1.codigo_banco, b1.referencia_transaccion, b1.fecha_banco, b1.monto, b1.tipo 
                    ORDER BY b1.id_banco_conciliacion_importacion
                ) - 1
            )
        ) AS nueva_referencia_transaccion
    FROM banco_conciliacion_importarcion b1
    INNER JOIN (
        SELECT 
            empresa, codigo_banco, referencia_transaccion, fecha_banco, monto, tipo
        FROM banco_conciliacion_importarcion
        GROUP BY empresa, codigo_banco, referencia_transaccion, fecha_banco, monto, tipo
        HAVING COUNT(*) > 1
    ) b2 ON b1.empresa = b2.empresa 
        AND b1.codigo_banco = b2.codigo_banco 
        AND b1.referencia_transaccion = b2.referencia_transaccion 
        AND b1.fecha_banco = b2.fecha_banco 
        AND b1.monto = b2.monto 
        AND b1.tipo = b2.tipo
    ORDER BY 
        b1.empresa, b1.id_banco_conciliacion_importacion, b1.referencia_transaccion, b1.fecha_banco;

    
    -- PASO 1
    -- Crear campo de resplado de referencia a referencia_original, y referencia_transaccion a referencia_transaccion_original
    ALTER TABLE `banco_conciliacion_importarcion` 
    ADD COLUMN `referencia_original` varchar(150) DEFAULT NULL AFTER `id_banco`,
    ADD COLUMN `referencia_transaccion_original` varchar(150) DEFAULT NULL AFTER `referencia_original`;

    -- PASO 2
    -- update de respaldo de referencia
    START TRANSACTION;
        UPDATE `banco_conciliacion_importarcion`
        SET 
            `referencia_original` = `referencia`,
            `referencia_transaccion_original` = `referencia_transaccion`
        WHERE `referencia_original` IS NULL 
        OR `referencia_original` = '';
    COMMIT;

    -- PASO 3 
    -- update antes de realizar el ALTER TABLE LLAVE UNIQUEY -- campo referencia
    START TRANSACTION;
        UPDATE banco_conciliacion_importarcion b1
        INNER JOIN (
            SELECT 
                id_banco_conciliacion_importacion,
                IF(
                    id_banco_conciliacion_importacion = MIN(id_banco_conciliacion_importacion) OVER(
                        PARTITION BY empresa, codigo_banco, referencia, fecha_banco, monto, tipo
                    ),
                    referencia,
                    CONCAT(
                        referencia, 
                        '-', 
                        ROW_NUMBER() OVER(
                            PARTITION BY empresa, codigo_banco, referencia, fecha_banco, monto, tipo 
                            ORDER BY id_banco_conciliacion_importacion
                        ) - 1
                    )
                ) AS nueva_referencia
                FROM banco_conciliacion_importarcion
                WHERE (empresa, codigo_banco, referencia, fecha_banco, monto, tipo) IN (
                    SELECT empresa, codigo_banco, referencia, fecha_banco, monto, tipo
                    FROM banco_conciliacion_importarcion
                    GROUP BY empresa, codigo_banco, referencia, fecha_banco, monto, tipo
                    HAVING COUNT(*) > 1
                )
            ) b2 ON b1.id_banco_conciliacion_importacion = b2.id_banco_conciliacion_importacion
            SET b1.referencia = b2.nueva_referencia
            WHERE b1.referencia <> b2.nueva_referencia;
    COMMIT; 

    -- PASO 4 
    -- update antes de realizar el ALTER TABLE LLAVE UNIQUEY -- campo referencia
    START TRANSACTION;
        UPDATE banco_conciliacion_importarcion b1
        INNER JOIN (
            SELECT 
                id_banco_conciliacion_importacion,
                IF(
                    id_banco_conciliacion_importacion = MIN(id_banco_conciliacion_importacion) OVER(
                        PARTITION BY empresa, codigo_banco, referencia_transaccion, fecha_banco, monto, tipo
                    ),
                    referencia_transaccion,
                    CONCAT(
                        referencia_transaccion, 
                        '-', 
                        ROW_NUMBER() OVER(
                            PARTITION BY empresa, codigo_banco, referencia_transaccion, fecha_banco, monto, tipo 
                            ORDER BY id_banco_conciliacion_importacion
                        ) - 1
                    )
                ) AS nueva_referencia_transaccion
            FROM banco_conciliacion_importarcion
            WHERE (empresa, codigo_banco, referencia_transaccion, fecha_banco, monto, tipo) IN (
                SELECT empresa, codigo_banco, referencia_transaccion, fecha_banco, monto, tipo
                FROM banco_conciliacion_importarcion
                GROUP BY empresa, codigo_banco, referencia_transaccion, fecha_banco, monto, tipo
                HAVING COUNT(*) > 1
            )
        ) b2 ON b1.id_banco_conciliacion_importacion = b2.id_banco_conciliacion_importacion
        SET b1.referencia_transaccion = b2.nueva_referencia_transaccion
        WHERE b1.referencia_transaccion <> b2.nueva_referencia_transaccion;
    COMMIT; 


    -- PASO 5
    -- Alter Table, para aplicacion de nuevas llaves
    ALTER TABLE `banco_conciliacion_importarcion` 
    ADD UNIQUE KEY `uk_conciliacion_unica` (`empresa`, `codigo_banco`, `referencia`, `fecha_banco`, `monto`, `tipo`);
-- FIN Tabla: banco_conciliacion_importarcion


-- Tabla: banco_transferencias_movimientos
    -- Create Origigal
    CREATE TABLE `banco_transferencias_movimientos` (
    `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
    `id_banco_origen` int(11) NOT NULL,
    `id_banco_destino` int(11) NOT NULL,
    `id_transferencia` int(11) NOT NULL,
    `referencia` varchar(50) NOT NULL,
    `monto_base` decimal(13,2) NOT NULL,
    `monto_base_bs` decimal(13,2) DEFAULT NULL,
    `comision_fija` decimal(13,2) NOT NULL,
    `comision_porc` decimal(13,2) NOT NULL,
    `monto_debito_origen` decimal(13,2) NOT NULL,
    `monto_a_creditar` decimal(13,2) NOT NULL,
    `monto_debito_origen_bs` decimal(13,2) DEFAULT NULL,
    `monto_a_creditar_bs` decimal(13,2) DEFAULT NULL,
    `tasa_cambio` decimal(12,2) NOT NULL,
    `tasa_bcv_dolar` decimal(12,2) NOT NULL DEFAULT 0.00,
    `tasa_bcv_euro` decimal(12,2) NOT NULL DEFAULT 0.00,
    `tasa_binance` decimal(12,2) NOT NULL DEFAULT 0.00,
    `status` varchar(20) NOT NULL,
    `empresa` varchar(20) NOT NULL,
    `sucursal` varchar(20) NOT NULL,
    `usuario` varchar(200) NOT NULL,
    `ip_estacion` varchar(60) NOT NULL,
    `fecha_registro` datetime NOT NULL,
    `fecha_transaccion` date NOT NULL,
    `descripcion` varchar(200) DEFAULT NULL,
    `tipo` varchar(20) DEFAULT NULL,
    `afecta_teorico` varchar(2) DEFAULT NULL,
    `afecta_conciliado` varchar(2) DEFAULT NULL,
    `conciliado` varchar(20) DEFAULT NULL,
    `nro_conciliacion` varchar(20) DEFAULT NULL,
    `revisado` varchar(20) DEFAULT NULL,
    `tipo_conciliacion` varchar(20) DEFAULT NULL,
    `id_conciliacion` int(11) DEFAULT NULL,
    `perdida_monto` decimal(15,2) DEFAULT NULL,
    `perdida_porcentual` decimal(15,2) DEFAULT NULL,
    PRIMARY KEY (`id_movimiento`) USING BTREE,
    KEY `idx_btm_status_id_transferencia` (`status`,`id_transferencia`),
    KEY `idx_btm_id_movimiento_tipo` (`id_movimiento`,`tipo`),
    KEY `idx_btm_id_banco_origen_id_banco_destino` (`id_banco_origen`,`id_banco_destino`),
    KEY `idx_btm_status` (`status`),
    KEY `idx_btm_id_transferencia` (`id_transferencia`)
    ) ENGINE=InnoDB AUTO_INCREMENT=320 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

    -- Seleccion para Validar si Existen Registros Duplicados
    SELECT empresa, id_banco_origen, referencia, fecha_transaccion, monto_base_bs, tipo, COUNT(*) as total
    FROM banco_transferencias_movimientos  
    GROUP BY empresa, id_banco_origen, id_banco_destino, referencia, fecha_transaccion, monto_base, tipo 
    HAVING total > 1;

    -- PASO 1
    -- Crear campo de resplado de referencia a referencia_original, y referencia_transaccion a referencia_transaccion_original
    ALTER TABLE `banco_transferencias_movimientos` 
    ADD COLUMN `referencia_original` varchar(150) DEFAULT NULL AFTER `perdida_porcentual`;

    -- PASO 2
    START TRANSACTION;
        UPDATE `banco_transferencias_movimientos`
        SET 
            `referencia_original` = `referencia`,
        WHERE `referencia_original` IS NULL 
        OR `referencia_original` = '';
    COMMIT;

    -- PASO 3
    -- update antes de realizar el ALTER TABLE LLAVE UNIQUEY
    START TRANSACTION;
        UPDATE banco_transferencias_movimientos b1
        INNER JOIN (
            SELECT 
                id_movimiento,
                referencia,
                IF(
                    id_movimiento = MIN(id_movimiento) OVER(
                        PARTITION BY empresa, id_banco_origen, id_banco_destino, referencia, fecha_transaccion, monto_base, tipo
                    ),
                    referencia,
                    CONCAT(
                        referencia, 
                        '-', 
                        ROW_NUMBER() OVER(
                            PARTITION BY empresa, id_banco_origen, id_banco_destino, referencia, fecha_transaccion, monto_base, tipo 
                            ORDER BY id_movimiento
                        ) - 1
                    )
                ) AS nueva_referencia
            FROM banco_transferencias_movimientos
            WHERE (empresa, id_banco_origen, id_banco_destino, referencia, fecha_transaccion, monto_base, tipo) IN (
                SELECT empresa, id_banco_origen, id_banco_destino, referencia, fecha_transaccion, monto_base, tipo, status
                FROM banco_transferencias_movimientos
                GROUP BY empresa,id_banco_origen, id_banco_destino, referencia, fecha_transaccion, monto_base, tip, status
                HAVING COUNT(*) > 1
            )
        ) b2 ON b1.id_movimiento = b2.id_movimiento
        SET b1.referencia = b2.nueva_referencia
        WHERE b1.referencia <> b2.nueva_referencia;
    COMMIT;

    -- PASO 4
    -- Alter Table, para aplicacion de nuevas llaves
    ALTER TABLE `banco_transferencias_movimientos` 
    ADD UNIQUE KEY `uk_transferencia_unica` (
        `empresa`,
        `id_banco_origen`, 
        `id_banco_destino`, 
        `referencia`, 
        `fecha_transaccion`, 
        `monto_base`, 
        `tipo`
    );

    -- FIN Tabla: banco_transferencias_movimientos

    -- Tabla: ventas_transacciones_detalles
    -- Create Origigal
    CREATE TABLE `ventas_transacciones_detalles` (
    `id_ventas_transacciones_detalles` int(11) NOT NULL AUTO_INCREMENT,
    `id_ventas_transacciones` int(11) DEFAULT NULL,
    `id_conciliacion` int(11) DEFAULT NULL,
    `tipo_pago` varchar(30) DEFAULT NULL,
    `forma_pago` varchar(40) DEFAULT NULL,
    `tasa_cambio` decimal(10,2) NOT NULL,
    `origen` varchar(30) NOT NULL,
    `cod_formas_pago` int(6) NOT NULL,
    `referencia` varchar(255) NOT NULL,
    `descripcion` varchar(500) DEFAULT NULL,
    `monto` decimal(10,2) NOT NULL,
    `monto_bs` decimal(10,2) NOT NULL,
    `fecha_transaccion` date NOT NULL,
    `conciliado` varchar(3) DEFAULT NULL,
    `nro_conciliacion` varchar(20) DEFAULT NULL,
    `revisado` varchar(3) NOT NULL,
    `status` varchar(10) NOT NULL,
    `tipo_conciliacion` varchar(20) NOT NULL,
    `fecha` date NOT NULL,
    `empresa` varchar(20) NOT NULL,
    `sucursal` varchar(20) NOT NULL,
    `usuario` varchar(200) NOT NULL,
    `usr_nivel` varchar(20) NOT NULL,
    `ip_estacion` varchar(60) NOT NULL,
    `id_cxc_cobro_resumen` int(20) DEFAULT NULL,
    `id_cxc_documento` int(20) DEFAULT NULL,
    `cierre` int(2) DEFAULT NULL,
    `numero_intento` int(4) DEFAULT NULL,
    `id_resumen_nota_entrega` int(11) DEFAULT NULL,
    `monto_abonado` decimal(13,2) DEFAULT NULL,
    `tipo_movimiento` varchar(255) DEFAULT NULL,
    `fecha_aprobacion` varchar(255) DEFAULT NULL,
    `usuario_aprobado` varchar(255) DEFAULT NULL,
    `pre_revisado` varchar(2) DEFAULT NULL,
    `telefono` varchar(20) DEFAULT NULL,
    `id_banco` int(11) DEFAULT NULL,
    `multiple` int(11) DEFAULT NULL,
    `comision_procesada` int(11) DEFAULT 0,
    PRIMARY KEY (`id_ventas_transacciones_detalles`),
    KEY `empresa` (`empresa`),
    KEY `sucursal` (`sucursal`),
    KEY `fecha_transaccion` (`fecha_transaccion`),
    KEY `tipo_pago` (`tipo_pago`),
    KEY `forma_pago` (`forma_pago`),
    KEY `id_cxc_documento` (`id_cxc_documento`),
    KEY `id_cxc_cobro_resumen` (`id_cxc_cobro_resumen`),
    KEY `monto` (`monto`),
    KEY `referencia` (`referencia`),
    KEY `status` (`status`),
    KEY `id_ventas_detalles` (`id_ventas_transacciones_detalles`),
    KEY `fecha` (`fecha`) USING BTREE,
    KEY `tipo_forma` (`tipo_pago`,`forma_pago`,`empresa`,`sucursal`) USING BTREE,
    KEY `idx_ventas_transacciones_detalles_fecha` (`fecha`),
    KEY `idx_vtd_status_empresa_sucursal_tipo_pago_forma_pago` (`status`,`empresa`,`sucursal`,`tipo_pago`,`forma_pago`),
    KEY `idx_vtd_id_ventas_transacciones_detalles` (`id_ventas_transacciones_detalles`),
    KEY `idx_vtd_id_ventas_transacciones` (`id_ventas_transacciones`),
    KEY `idx_vtd_id_ventas_transacciones_id_cxc_documento_monto` (`id_ventas_transacciones`,`id_cxc_documento`,`monto`),
    KEY `idx_v_status_fecha` (`status`,`fecha`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1188380 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

    -- Seleccion psrs Validar si Existen Registros Duplicados
    select status, id_banco, fecha_transaccion, tipo_pago,forma_pago, origen, referencia, monto, monto_bs, empresa, count (*) as total
    from  ventas_transacciones_detalles  
    GROUP BY  empresa, id_banco, referencia, fecha_transaccion, monto_bs, status
    HAVING total > 1

    -- PASO 1
    -- Crear campo de resplado de referencia a referencia_original, y referencia_transaccion a referencia_transaccion_original
    ALTER TABLE `ventas_transacciones_detalles` 
    ADD COLUMN `referencia_original` varchar(150) DEFAULT NULL AFTER `comision_procesada`;

   -- PASO 2
    START TRANSACTION;
        UPDATE `ventas_transacciones_detalles`
        SET 
            `referencia_original` = `referencia`,
        WHERE `referencia_original` IS NULL 
        OR `referencia_original` = '';
    COMMIT;

    SELECT 
        v1.id_ventas_transacciones_detalles,
        v1.empresa,
        v1.sucursal,
        v1.tipo_pago,
        v1.forma_pago,
        v1.referencia AS referencia_original,
        v1.fecha_transaccion,
        v1.monto_bs,
        IF(
            v1.id_ventas_transacciones_detalles = MIN(v1.id_ventas_transacciones_detalles) OVER(
                PARTITION BY v1.empresa, v1.id_banco, v1.referencia, v1.fecha_transaccion, v1.monto_bs, v1.status
            ),
            v1.referencia,
            CONCAT(
                v1.referencia, 
                '-', 
                ROW_NUMBER() OVER(
                    PARTITION BY v1.empresa,v1.id_banco, v1.referencia, v1.fecha_transaccion, v1.monto_bs, v1.status
                    ORDER BY v1.id_ventas_transacciones_detalles
                ) - 1
            )
        ) AS nueva_referencia
    FROM ventas_transacciones_detalles v1
    INNER JOIN (
        SELECT 
            empresa, id_banco, referencia, fecha_transaccion, monto_bs, status
        FROM ventas_transacciones_detalles
        GROUP BY empresa, id_banco, referencia, fecha_transaccion, monto_bs, status
        HAVING COUNT(*) > 1
    ) v2 ON v1.id_banco = v2.id_banco
        AND v1.referencia = v2.referencia
        AND v1.fecha_transaccion = v2.fecha_transaccion
        AND v1.monto_bs = v2.monto_bs
    ORDER BY 
        v1.id_ventas_transacciones_detalles, v1.referencia, v1.fecha_transaccion;
    -- a la fecha 27-10-2026 en produccion hay 4245 registros duplicados
    -- FIN Tabla: ventas_transacciones_detalles

    -- PASO 3
    -- update antes de realizar el ALTER TABLE LLAVE UNIQUEY
    START TRANSACTION;
        UPDATE ventas_transacciones_detalles t
        INNER JOIN (
            SELECT 
                v1.id_ventas_transacciones_detalles,
                IF(
                    v1.id_ventas_transacciones_detalles = MIN(v1.id_ventas_transacciones_detalles) OVER(
                        PARTITION BY v1.empresa, v1.id_banco, v1.referencia, v1.fecha_transaccion, v1.monto_bs, v1.status
                    ),
                    v1.referencia,
                    CONCAT(
                        v1.referencia, 
                        '-', 
                        ROW_NUMBER() OVER(
                            PARTITION BY v1.empresa, v1.id_banco, v1.referencia, v1.fecha_transaccion, v1.monto_bs, v1.status
                            ORDER BY v1.id_ventas_transacciones_detalles
                        ) - 1
                    )
                ) AS nueva_referencia
            FROM ventas_transacciones_detalles v1
            INNER JOIN (
                SELECT 
                    empresa, id_banco, referencia, fecha_transaccion, monto_bs, status
                FROM ventas_transacciones_detalles
                GROUP BY empresa, id_banco, referencia, fecha_transaccion, monto_bs, status
                HAVING COUNT(*) > 1
            ) v2 ON v1.id_banco = v2.id_banco
                AND v1.referencia = v2.referencia
                AND v1.fecha_transaccion = v2.fecha_transaccion
                AND v1.monto_bs = v2.monto_bs
        ) orig ON t.id_ventas_transacciones_detalles = orig.id_ventas_transacciones_detalles
        SET t.referencia = orig.nueva_referencia
        WHERE t.referencia <> orig.nueva_referencia; -- Evita actualizar innecesariamente el primer registro
    COMMIT;

    -- Alter Table, para aplicacion de nuevas llaves
    ALTER TABLE `ventas_transacciones_detalles` 
    ADD UNIQUE KEY `uk_ventas_transacciones_detalles_unica` (
        `empresa`,
        `id_banco`, 
        `referencia`, 
        `fecha_transaccion`, 
        `monto_bs`
    );

    -- Tabla: compras_transacciones_detalles
    -- Create Origigal
        CREATE TABLE `compras_transacciones_detalles` (
    `id_compras_transacciones_detalles` int(20) NOT NULL AUTO_INCREMENT,
    `id_compra` int(11) NOT NULL,
    `id_conciliacion` int(20) DEFAULT NULL,
    `tipo_pago` varchar(20) NOT NULL,
    `forma_pago` varchar(20) NOT NULL,
    `tasa_cambio` decimal(10,2) NOT NULL,
    `origen` varchar(255) DEFAULT NULL,
    `cod_forma_pago` varchar(20) NOT NULL,
    `referencia` varchar(50) NOT NULL,
    `descripcion` longtext DEFAULT NULL,
    `monto` decimal(10,2) NOT NULL,
    `monto_bs` decimal(10,2) NOT NULL,
    `fecha_transaccion` date NOT NULL,
    `conciliado` varchar(20) NOT NULL,
    `nro_conciliacion` varchar(50) NOT NULL,
    `revisado` varchar(20) NOT NULL,
    `status` varchar(20) NOT NULL,
    `tipo_conciliacion` varchar(20) NOT NULL,
    `fecha` date NOT NULL,
    `empresa` varchar(20) NOT NULL,
    `sucursal` varchar(20) NOT NULL,
    `usuario` varchar(200) NOT NULL,
    `usr_nivel` varchar(255) DEFAULT NULL,
    `ip_estacion` varchar(60) NOT NULL,
    `id_cxp_pago_resumen` int(11) DEFAULT NULL,
    `id_factura_resumen` int(11) DEFAULT NULL,
    `tipo_movimiento` varchar(20) DEFAULT NULL,
    `id_cxp_documentos` int(11) DEFAULT NULL,
    `id_banco` int(11) DEFAULT 0,
    PRIMARY KEY (`id_compras_transacciones_detalles`),
    KEY `idx_ctd_status_empresa_sucursal_tipo_pago_forma_pago` (`status`,`empresa`,`sucursal`,`tipo_pago`,`forma_pago`),
    KEY `idx_ctd_id_compras_transacciones_detalles` (`id_compras_transacciones_detalles`)
    ) ENGINE=InnoDB AUTO_INCREMENT=229885 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

    -- Seleccion psrs Validar si Existen Registros Duplicados
    select status, id_banco, fecha_transaccion, tipo_pago,forma_pago, origen, referencia, monto, monto_bs, empresa, count (*) as total
    from  compras_transacciones_detalles  
    GROUP BY empresa, id_banco, referencia, fecha_transaccion, monto_bs
    HAVING total > 1

    SELECT 
        c1.id_compras_transacciones_detalles,
        c1.empresa,
        c1.sucursal,
        c1.tipo_pago,
        c1.forma_pago,
        c1.referencia AS referencia_original,
        c1.fecha_transaccion,
        c1.monto_bs,
        IF(
            c1.id_compras_transacciones_detalles = MIN(c1.id_compras_transacciones_detalles) OVER(
                PARTITION BY c1.id_banco, c1.referencia, c1.fecha_transaccion, c1.monto_bs
            ),
            c1.referencia,
            CONCAT(
                c1.referencia, 
                '-', 
                ROW_NUMBER() OVER(
                    PARTITION BY c1.id_banco, c1.referencia, c1.fecha_transaccion, c1.monto_bs 
                    ORDER BY c1.id_compras_transacciones_detalles
                ) - 1
            )
        ) AS nueva_referencia
    FROM compras_transacciones_detalles c1
    INNER JOIN (
        SELECT 
            id_banco, referencia, fecha_transaccion, monto_bs
        FROM compras_transacciones_detalles
        GROUP BY id_banco, referencia, fecha_transaccion, monto_bs
        HAVING COUNT(*) > 1
    ) c2 ON c1.id_banco = c2.id_banco
        AND c1.referencia = c2.referencia
        AND c1.fecha_transaccion = c2.fecha_transaccion
        AND c1.monto_bs = c2.monto_bs
    ORDER BY 
        c1.id_compras_transacciones_detalles, c1.referencia, c1.fecha_transaccion

   -- update antes de realizar el ALTER TABLE
    UPDATE compras_transacciones_detalles c1
    INNER JOIN (
        SELECT 
            id_compras_transacciones_detalles,
            IF(
                id_compras_transacciones_detalles = MIN(id_compras_transacciones_detalles) OVER(
                    PARTITION BY id_banco, referencia, fecha_transaccion, monto_bs
                ),
                referencia,
                CONCAT(
                    referencia, 
                    '-', 
                    ROW_NUMBER() OVER(
                        PARTITION BY id_banco, referencia, fecha_transaccion, monto_bs 
                        ORDER BY id_compras_transacciones_detalles
                    ) - 1
                )
            ) AS nueva_referencia
        FROM compras_transacciones_detalles
        WHERE referencia = '02073244' -- Filtro aplicado aquí
    ) c2 ON c1.id_compras_transacciones_detalles = c2.id_compras_transacciones_detalles
    SET c1.referencia = c2.nueva_referencia
    WHERE c1.referencia <> c2.nueva_referencia;

    -- Alter Table, para aplicacion de nuevas llaves
    ALTER TABLE `compras_transacciones_detalles` 
    ADD UNIQUE KEY `uk_compras_transacciones_detalles_unica` (
        `id_banco`, 
        `referencia`, 
        `fecha_transaccion`, 
        `monto_bs`
    );

