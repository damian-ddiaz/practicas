CREATE TABLE `configuracion_municipio_clasificador` (
  `id_clasificacion` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_estado` varchar(4) NOT NULL,
  `cod_municipio` varchar(50) NOT NULL,
  `codigo_tipo_productos` varchar(50) NOT NULL,
  `nombre_clasificacion` varchar(100) NOT NULL,
  `impuesto` decimal(5,2) NOT NULL,
  `id_proveedor` int(11) DEFAULT 0,
  `codigo_productos` varchar(20) NOT NULL,
  `fecha_inicio_impuesto` date DEFAULT NULL,
  `empresa` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `ip_estacion` varchar(45) NOT NULL,
  PRIMARY KEY (`id_clasificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE `configuracion_otros_impuestos` (
  `id_otros_impuestos` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_impuesto` varchar(100) NOT NULL,        -- IVA, ISLR Anticipo, Alcaldía, etc.
  `impuesto` decimal(5,2) NOT NULL,
  `id_proveedor` int(11) DEFAULT 0,
  `codigo_productos` varchar(20) NOT NULL,
  `fecha_inicio_impuesto` date DEFAULT NULL,
  `frecuencia` varchar(50) DEFAULT NULL,          -- Quincenal, Mensual, Anual, Trimestral
  `empresa` varchar(20) NOT NULL,                 -- Campo solicitado
  `usuario` varchar(200) DEFAULT NULL,            -- Campo solicitado
  `ip_estacion` varchar(60) DEFAULT NULL,         -- Campo solicitado
  `fecha_registro` datetime DEFAULT current_timestamp(), -- Auditoría automática
  PRIMARY KEY (`id_otros_impuestos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;