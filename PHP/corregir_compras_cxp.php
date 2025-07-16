<?php

// Conectar a la base de datos (asegúrate de usar tus credenciales)
// DEVELOPER

$host = '172.16.7.50';
$db = 'webservices';
$user = 'scryptcase';
$pass = 'Mt*1329*--1';

// PRODUCCION
/*
$host = '45.179.164.7';
$db = 'webservices';
$user = 'scryptcase';
$pass = 'Mt*1329*--1';
*/
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}else{
    echo 'CONEXION EXITOSA'.'<br>';

}

// Consulta SQL
$sql = "SELECT 
    cr.numero AS numero_documento, 
    cr.numero_factura AS nro_fiscal, 
    cr.numero_control AS nro_control, 
    cr.descripcion, 
    cr.codigo_proveedor, 
    cr.subtotal AS sub_total, 
    cr.total AS total_neto, 
    IF(ISNULL(cr.saldo), 0.00, cr.saldo) AS saldo, 
    cr.tasa_cambio, 
    cr.fecha_factura AS fecha_emision, 
    cr.fecha_vencimiento, 
    ' ' AS tipo_documento_afect, 
    ' ' AS numero_documento_afect, 
    'AUTOMATICO' AS tipo, 
    cr.fecha_registro AS fecha, 
    cr.usuario, 
    cr.empresa, 
    cr.sucursal, 
    cr.ip_estacion, 
    IF(ISNULL(cr.id_proveedor), 0, cr.id_proveedor) AS id_proveedor, 
    cr.id_compra, 
    cr.estado
FROM 
    compras_resumen cr
LEFT JOIN 
    cxp_documentos cd ON cr.id_compra = cd.id_compra
WHERE 
    cd.id_compra IS NULL AND (cr.estado ='EN INVENTARIO' OR cr.estado = 'ANULADO') /*AND cr.empresa = 'tecnoven' 
    AND cr.sucursal = 'punto fijo'*/";

// Ejecutar la consulta
$result = $conn->query($sql);

if (!$result) {
    echo "Error al acceder a la base de datos: " . $conn->error;
} else {
    while ($row = $result->fetch_assoc()) {
        // Asignando valores a las variables
        $estatus = 'PROCESADO';
        $tipo_documento = 'FACTURC';
        $numero_documento = $row['numero_documento'];
        $numero_factura = $row['nro_fiscal'];
        $numero_control = $row['nro_control'];
        $descripcion = "FACTURA A CREDITO " . $row['descripcion'];
        $codigo_proveedor = $row['codigo_proveedor'];
        $sub_total = $row['sub_total'];
        $total_neto = $row['total_neto'];
        $var_saldo = $row['saldo'];
        $tasa_cambio = $row['tasa_cambio'];
        $fecha_emision = $row['fecha_emision'];
        $fecha_vencimiento = $row['fecha_vencimiento'];
        $tipo_documento_afect = '';
        $numero_documento_afect = '';
        $tipo = $row['tipo'];
        $fecha = $row['fecha'];
        $usuario = $row['usuario'];
        $empresa = $row['empresa'];
        $sucursal = $row['sucursal'];
        $ip_estacion = $row['ip_estacion'];
        $id_proveedor = $row['id_proveedor'];
        $id_compra = $row['id_compra'];

        // Insertando en cxp_documentos
        $insert_table = 'cxp_documentos';
        $insert_fields = array(
            'id_cxp_documentos' => '0',
            'tipo_documento' => "'$tipo_documento'",
            'numero_documento' => "'$numero_documento'",
            'nro_fiscal' => "'$numero_factura'",
            'nro_control' => "'$numero_control'",
            'descripcion' => "'$descripcion'",
            'cod_proveedor' => "'$codigo_proveedor'",
            'sub_total' => "'$sub_total'",
            'total_neto' => "'$total_neto'",
            'saldo' => "'$var_saldo'",
            'tasa_cambio' => "'$tasa_cambio'",
            'fecha_emision' => "'$fecha_emision'",
            'fecha_vencimiento' => "'$fecha_vencimiento'",
            'tipo_documento_afect' => "'$tipo_documento_afect'",
            'numero_documento_afect' => "'$numero_documento_afect'",
            'estatus' => "'$estatus'",
            'tipo' => "'AUTOMATICO'",
            'fecha' => "'$fecha'",
            'usuario' => "'$usuario'",
            'empresa' => "'$empresa'",
            'sucursal' => "'$sucursal'",
            'ip_estacion' => "'$ip_estacion'",
            'id_proveedor' => "'$id_proveedor'",
            'id_compra' => "'$id_compra'",
        );

        // Insert record
        $insert_sql = 'INSERT INTO ' . $insert_table
            . ' (' . implode(', ', array_keys($insert_fields)) . ')'
            . ' VALUES (' . implode(', ', array_values($insert_fields)) . ')';

        // Ejecutar la inserción
        if (!$conn->query($insert_sql)) {
            echo "Error al insertar en cxp_documentos: " . $conn->error;
        }

        // Actualizando el saldo del proveedor
       // $conn->query("UPDATE proveedores_datos SET saldo = $var_saldo WHERE id_proveedor = $id_proveedor");
    }

    $result->free(); // Liberar el conjunto de resultados
}

// Cerrar conexión
$conn->close();

echo 'PROCESO CULMINADO';
?>