CREATE TABLE configuracion_municipio_clasificador (
    id_clasificacion INT PRIMARY KEY,
    cod_municipio VARCHAR(50) NOT NULL,                  -- FK a tu tabla de municipios
    nombre_clasificacion VARCHAR(100) NOT NULL, -- Ej: 'Telecomunicaciones', 'Comercio al por menor'
    descripcion TEXT,                           -- Detalle o notas sobre lo que abarca este ramo
    empresa VARCHAR(100) NOT NULL,              -- Código o nombre de la empresa que registra
    usuario VARCHAR(50) NOT NULL,               -- Usuario del sistema que creó el registro
    ip_estacion VARCHAR(45) NOT NULL            -- Dirección IP de la máquina (IPv4 o IPv6)
);



CREATE TABLE configuracion_municipio_impuestos (
    id INT PRIMARY KEY,
    cod_municipio VARCHAR(5) NOT NULL,                  -- FK a tu tabla de municipios
    id_clasificacion INT NOT NULL,              -- FK a configuracion_municipio_clasificador
    impuesto NUMERIC(5,2) NOT NULL,             -- El porcentaje de la alícuota (Ej: 1.50)
    empresa VARCHAR(100) NOT NULL,              -- Código o nombre de la empresa que registra
    usuario VARCHAR(50) NOT NULL,               -- Usuario del sistema que creó el registro
    ip_estacion VARCHAR(45) NOT NULL,           -- Dirección IP de la máquina (IPv4 o IPv6)
    
    -- Relación con la tabla de clasificación que creamos antes
    CONSTRAINT fk_municipio_clasificador 
        FOREIGN KEY (id_clasificacion) 
        REFERENCES configuracion_municipio_clasificador(id_clasificacion)
);