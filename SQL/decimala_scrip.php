<?php
$host = '10.10.10.114';
$user = 'remote';
$pass = 'Mt*1329*--1';
$target_db = 'ddiazbd';
$var_decimal = 'DECIMAL(15,6)'; // Variable agregada

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $target_db);
    echo "✅ CONEXIÓN Y SELECCIÓN DE BD EXITOSA<br>";

    // --- 1. ALTER CLIENTES ---
    $alter_clientes = [
        "MODIFY COLUMN ci_rif VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci",
        "MODIFY COLUMN nombre_razon_social VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci",
        "MODIFY COLUMN email VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL",
        "MODIFY COLUMN telefono VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL",
        "MODIFY COLUMN direccion TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL",
        "MODIFY COLUMN empresa VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci",
        "MODIFY COLUMN sucursal VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci",
        "MODIFY COLUMN usuario VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci",
        "MODIFY COLUMN fec_reg DATETIME DEFAULT CURRENT_TIMESTAMP"
    ];
    foreach ($alter_clientes as $sql) { $conn->query("ALTER TABLE clientes $sql"); }
    echo "🛠 Tabla 'clientes' actualizada.<br>";

    // --- 2. ALTER PRODUCTOS ---
    $alter_productos = [
        "MODIFY COLUMN codigo VARCHAR(50) NOT NULL",+
        "MODIFY COLUMN nombre VARCHAR(100) NOT NULL",
        "MODIFY COLUMN descripcion TEXT DEFAULT NULL",
        "MODIFY COLUMN costo $var_decimal NOT NULL",
        "MODIFY COLUMN precio $var_decimal NOT NULL",
        "MODIFY COLUMN impuesto var_decimal NOT NULL",
        "MODIFY COLUMN stock INT(11) NOT NULL DEFAULT 0",
        "MODIFY COLUMN empresa VARCHAR(50) NOT NULL",
        "MODIFY COLUMN sucursal VARCHAR(50) NOT NULL",
        "MODIFY COLUMN usuario VARCHAR(50) NOT NULL",
        "MODIFY COLUMN fec_reg DATETIME NOT NULL DEFAULT current_timestamp()"
    ];
    foreach ($alter_productos as $sql) { $conn->query("ALTER TABLE productos $sql"); }
    echo "🛠 Tabla 'productos' actualizada.<br>";

    // --- 3. ALTER CORRELATIVOS ---
    $alter_correlativos = [
        "MODIFY COLUMN tipo_documento ENUM('FA','ND','NC','CT','FC') NOT NULL",
        "MODIFY COLUMN serie VARCHAR(255) NOT NULL",
        "MODIFY COLUMN ultimo_documento INT(11) NOT NULL DEFAULT 0",
        "MODIFY COLUMN ultimo_control VARCHAR(11) DEFAULT NULL",
        "MODIFY COLUMN empresa VARCHAR(50) NOT NULL",
        "MODIFY COLUMN usuario VARCHAR(50) NOT NULL",
        "MODIFY COLUMN fecha_reg DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
    ];
    foreach ($alter_correlativos as $sql) { $conn->query("ALTER TABLE correlativos $sql"); }
    // Actualizar índice único
    try { $conn->query("ALTER TABLE correlativos DROP INDEX uc_tipo_serie_empresa"); } catch (Exception $e) {}
    $conn->query("ALTER TABLE correlativos ADD UNIQUE KEY uc_tipo_serie_empresa (tipo_documento, serie, empresa)");
    echo "🛠 Tabla 'correlativos' actualizada.<br>";

    // --- 4. ALTER TIPO_DOCUMENTO ---
    $alter_tipo_doc = [
        "MODIFY COLUMN tipo_documento VARCHAR(10) NOT NULL",
        "MODIFY COLUMN desc_tipo_documento VARCHAR(255) DEFAULT NULL",
        "MODIFY COLUMN tipo_movimiento VARCHAR(10) DEFAULT NULL",
        "MODIFY COLUMN feg_reg DATETIME NOT NULL DEFAULT current_timestamp()"
    ];
    foreach ($alter_tipo_doc as $sql) { $conn->query("ALTER TABLE tipo_documento $sql"); }
    echo "🛠 Tabla 'tipo_documento' actualizada.<br>";

    // --- 5. ALTER DOCUMENTOS ---
    $alter_documentos = [
        "MODIFY COLUMN registro_fiscal VARCHAR(20) DEFAULT NULL",
        "MODIFY COLUMN usuario VARCHAR(200) DEFAULT NULL",
        "MODIFY COLUMN nombre_vendedor TINYINT(100) DEFAULT NULL",
        "MODIFY COLUMN sucursal VARCHAR(50) NOT NULL"
    ];
    foreach ($alter_documentos as $sql) { $conn->query("ALTER TABLE documentos $sql"); }
    // Actualizar índice único de documentos
    try { $conn->query("ALTER TABLE documentos DROP INDEX uq_tipo_doc_num_doc_empresa"); } catch (Exception $e) {}
    $conn->query("ALTER TABLE documentos ADD UNIQUE KEY uq_tipo_doc_num_doc_empresa (tipo_documento, numero_documento, empresa) USING BTREE");
    echo "🛠 Tabla 'documentos' actualizada.<br>";

    // --- 6. ALTER DOCUMENTO_DETALLE ---
    $alter_detalle = [
        "MODIFY COLUMN precio_unitario DECIMAL(15,2) NOT NULL",
        "MODIFY COLUMN monto_iva DECIMAL(15,2) NOT NULL",
        "MODIFY COLUMN empresa VARCHAR(255) NOT NULL"
    ];
    foreach ($alter_detalle as $sql) { $conn->query("ALTER TABLE documento_detalle $sql"); }
    // Refrescar Llave Foránea
    try { $conn->query("ALTER TABLE documento_detalle DROP FOREIGN KEY documento_detalle_ibfk_1"); } catch (Exception $e) {}
    $conn->query("ALTER TABLE documento_detalle ADD CONSTRAINT documento_detalle_ibfk_1 FOREIGN KEY (id_documento) REFERENCES documentos (id_documento)");
    echo "🛠 Tabla 'documento_detalle' actualizada.<br>";

    // --- 7. ALTER DOCUMENTO_PAGOS ---
    $alter_pagos = [
        "MODIFY COLUMN monto DECIMAL(15,2) NOT NULL",
        "MODIFY COLUMN tasa_cambio $var_decimal NOT NULL", // Uso de la variable agregada
        "MODIFY COLUMN status VARCHAR(50) NOT NULL"
    ];
    foreach ($alter_pagos as $sql) { $conn->query("ALTER TABLE documento_pagos $sql"); }
    // Refrescar Llave Foránea
    try { $conn->query("ALTER TABLE documento_pagos DROP FOREIGN KEY documento_pagos_ibfk_1"); } catch (Exception $e) {}
    $conn->query("ALTER TABLE documento_pagos ADD CONSTRAINT documento_pagos_ibfk_1 FOREIGN KEY (id_documento) REFERENCES documentos (id_documento)");
    echo "🛠 Tabla 'documento_pagos' actualizada.<br>";

    echo "<br>✅ --- PROCESO DE ALTER TABLES FINALIZADO ---";
    $conn->close();

} catch (mysqli_sql_exception $e) {
    die("❌ Error SQL: " . $e->getMessage());
}
?>