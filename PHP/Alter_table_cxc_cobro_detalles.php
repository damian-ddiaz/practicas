<?php
	// Create Original
	CREATE TABLE `cxc_cobro_detalles` (
	`id_cxc_cobro_detalles` int(20) NOT NULL AUTO_INCREMENT,
	`id_cxc_cobro_resumen` int(20) NOT NULL,
	`id_cxc_documento` int(20) NOT NULL,
	`cod_cliente` varchar(20) NOT NULL,
	`tipo_documento` varchar(20) NOT NULL,
	`numero_documento` varchar(20) NOT NULL,
	`estatus` varchar(20) NOT NULL,
	`total_neto` decimal(10,2) NOT NULL,
	`saldo` decimal(10,2) NOT NULL,
	`monto` decimal(10,2) NOT NULL,
	`tasa` decimal(10,2) NOT NULL,
	`fecha` date NOT NULL,
	`empresa` varchar(20) NOT NULL,
	`sucursal` varchar(20) NOT NULL,
	`usuario` varchar(200) NOT NULL,
	`monto_bs` decimal(20,2) DEFAULT NULL,
	PRIMARY KEY (`id_cxc_cobro_detalles`),
	KEY `resumen` (`id_cxc_cobro_resumen`),
	KEY `cxc_cobro_detalles_id_cxc_documento_IDX` (`id_cxc_documento`,`estatus`) USING BTREE,
	KEY `cxc_cobro_detalles_id_cxc_cobro_resumen_IDX` (`id_cxc_cobro_resumen`,`estatus`,`empresa`) USING BTREE,
	KEY `id_cxc_doc` (`id_cxc_documento`),
	KEY `saldo_cal` (`id_cxc_cobro_resumen`,`id_cxc_documento`),
	KEY `status` (`estatus`),
	KEY `idx_cxd_id_cxc_documento_id_cxc_cobro_resumen_monto` (`id_cxc_documento`,`id_cxc_cobro_resumen`,`monto`),
	KEY `idx_ccd_id_cxc_cobro_resumen_tipo_doc_id_cxc_documento` (`id_cxc_cobro_resumen`,`tipo_documento`,`id_cxc_documento`)
	) ENGINE=InnoDB AUTO_INCREMENT=3233030 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci


	// Alter Table campo monto_bs
	ALTER TABLE cxc_cobro_detalles 
	ADD COLUMN monto_cruzado DECIMAL(20,2) 
	AS (monto * tasa) VIRTUAL;


	ALTER TABLE cxc_cobro_detalles 
	ADD COLUMN monto_cruzado DECIMAL(20,2) 
	AS (IFNULL(monto, 0) * IFNULL(tasa, 0)) STORED NOT NULL;


?>