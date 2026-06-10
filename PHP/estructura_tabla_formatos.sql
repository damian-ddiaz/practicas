CREATE TABLE configuracion_formatos (
    id_formato INT AUTO_INCREMENT,
    id_modulo_maestro INT NOT NULL,
    codigo_formato VARCHAR(20) NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    aplicacion VARCHAR(100) NOT NULL,
    tipo_papel VARCHAR(20) DEFAULT 'Letter',
    cantidad_item INT DEFAULT 8,
    orientacion CHAR(1) DEFAULT 'P', -- 'P' = Portrait (Vertical), 'L' = Landscape (Horiz
    usuario VARCHAR(50) NOT NULL,
    ip_estacion VARCHAR(45) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_formato),
    UNIQUE KEY uid_empresa_codigo (empresa, sucursal, codigo_formato)
);

CREATE TABLE plantilla_formatos (
    id_plantilla_formato INT AUTO_INCREMENT,
    id_modulo_maestro INT NOT NULL,
    codigo_formato VARCHAR(20) NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    aplicacion VARCHAR(100) NOT NULL,
    tipo_papel VARCHAR(20) DEFAULT 'Letter',
    cantidad_item INT DEFAULT 8,
    orientacion CHAR(1) DEFAULT 'P', -- 'P' = Portrait (Vertical), 'L' = Landscape (Horiz
    empresa VARCHAR(50) NOT NULL,
    sucursal VARCHAR(50) NOT NULL,
    usuario VARCHAR(50) NOT NULL,
    ip_estacion VARCHAR(45) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_plantilla_formato),
    UNIQUE KEY uid_empresa_codigo (empresa, sucursal, codigo_formato)
);