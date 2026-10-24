CREATE TABLE `odc_resumen` (
  `id_odc` int(12) NOT NULL AUTO_INCREMENT,
  `numero` varchar(12) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_registro` date NOT NULL,
  `moneda` varchar(50) NOT NULL,
  `tasa_cambio` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `IVA` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `total_bolivares` decimal(12,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `tasa_iva` decimal(10,2) NOT NULL,
  `tasa_iva_redu` decimal(10,2) NOT NULL,
  `iva_bs` decimal(11,2) NOT NULL,
  `iva_reduc_bs` decimal(11,2) NOT NULL,
  `sub_total_bs` decimal(11,2) NOT NULL,
  `base_impo_bs` decimal(11,2) NOT NULL,
  `base_exenta_bs` decimal(11,2) NOT NULL,
  `base_exonera_bs` decimal(11,2) NOT NULL,
  `base_alicu_redu_bs` decimal(11,2) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `empresa` varchar(50) NOT NULL,
  `sucursal` varchar(50) NOT NULL,
  `ip_estacion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_odc`) USING BTREE,
  KEY `id_odc` (`id_odc`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `odc_detalle` (
  `id_detalle` int(12) NOT NULL AUTO_INCREMENT,
  `id_odc` int(12) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `codigo_producto` varchar(20) NOT NULL,
  `nombre_producto` longtext NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `impuesto_productos` varchar(3) NOT NULL,
  `tipo_impuesto` decimal(12,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `cantidad_aprobada` int(11) NOT NULL,
  `cantidad_recibida` int(11) NOT NULL,
  `subtotal_renglon` decimal(12,2) NOT NULL,
  `total_renglon` decimal(12,2) NOT NULL,
  `tasa_cambio` decimal(12,2) NOT NULL,
  `tipo_unidad` varchar(20) NOT NULL,
  `iva` decimal(6,2) NOT NULL,
  `iva_total` decimal(12,2) NOT NULL,
  `precio_unitario_bs` decimal(13,2) NOT NULL,
  `subtotal_renglon_bs` decimal(13,2) NOT NULL,
  `total_renglon_bs` decimal(13,2) NOT NULL,
  `total_iva_bs` decimal(13,2) NOT NULL,
  `empresa` varchar(50) NOT NULL,
  `sucursal` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `ip_estacion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_detalle`) USING BTREE,
  KEY `id_odc` (`id_odc`),
  KEY `codigo_producto` (`codigo_producto`) USING BTREE,
  KEY `empresa` (`empresa`) USING BTREE,
  KEY `sucursal` (`sucursal`) USING BTREE,
  CONSTRAINT `odc_detalles_ibfk_1` FOREIGN KEY (`id_odc`) REFERENCES `odc_resumen` (`id_odc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `gastos_recurrentes_detalles` 
ADD COLUMN `gasto_comprometido` decimal(11,2) NOT NULL AFTER `ip_estacion`;

ALTER TABLE `compras_resumen` 
ADD COLUMN `odc_numero` varchar(12) NOT NULL AFTER `numero`;