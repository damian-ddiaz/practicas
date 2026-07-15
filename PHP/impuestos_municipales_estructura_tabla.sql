CREATE TABLE `configuracion_municipio_clasificador` (
  `id_clasificacion` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_estado` varchar(4) NOT NULL,
  `cod_municipio` varchar(50) NOT NULL,
  `codigo_tipo_productos` varchar(50) NOT NULL,
  `nombre_clasificacion` varchar(100) NOT NULL,
  `impuesto` decimal(5,2) NOT NULL,
  `id_proveedor` int(11) DEFAULT 0, -- SI
  `id_producto` int(11) DEFAULT 0, -- SI
   `fecha_inicio_impuesto` date DEFAULT NULL, -- SI
   `empresa` varchar(100) NOT NULL,
   `usuario` varchar(50) NOT NULL,
   `ip_estacion` varchar(45) NOT NULL,
  PRIMARY KEY (`id_clasificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci


CREATE TABLE configuracion_municipio_clasificador ( // Nueva estructura
    id_clasificacion INT PRIMARY KEY,
    codigo_estado VARCHAR(4) NOT NULL,                  
    cod_municipio VARCHAR(50) NOT NULL,                  -- FK a tu tabla de municipios
    nombre_clasificacion VARCHAR(100) NOT NULL, -- Ej: 'Telecomunicaciones', 'Comercio al por menor'
    descripcion TEXT,                           -- Detalle o notas sobre lo que abarca este ramo
    generar_comision_bancaria int(11) DEFAULT 0,-- NO
    porcentaje_comision_bancaria decimal(15,2) DEFAULT 0.01, -- NO
    id_proveedor int(11) DEFAULT 0, -- SI
    id_producto int(11) DEFAULT 0, -- SI
    fecha_inicio_impuesto date DEFAULT NULL, -- SI
    generar_comision_bancaria_cxp int(11) DEFAULT NULL, --
    empresa VARCHAR(100) NOT NULL,              -- Código o nombre de la empresa que registra
    usuario VARCHAR(50) NOT NULL,               -- Usuario del sistema que creó el registro
    ip_estacion VARCHAR(45) NOT NULL            -- Dirección IP de la máquina (IPv4 o IPv6)
);