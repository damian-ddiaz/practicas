<?php
// Conectar a la base de datos (asegúrate de usar tus credenciales)
// DEVELOPER
/*
$host = '172.16.7.50';
$db = 'webservices';
$user = 'scryptcase';
$pass = 'Mt*1329*--1';
*/
// PRODUCCION

$host = '45.179.164.7';
$db = 'webservices';
$user = 'scryptcase';
$pass = 'Mt*1329*--1';

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} else {
    echo 'CONEXION EXITOSA' . '<br>';
}

//$var_empresa = 'tecnoven';
// $var_sucursal = 'cabimas';

// Actualizando la tabla gastos_recurrentes_detalles en función de la tabla "gastos_recurrentes_facturas_resumen"
$sql_compras = "SELECT id_compra, 
DATE(fecha_factura) AS fecha_factura, 
fecha_registro, 
id_gasto_resumen, 
empresa, 
sucursal 
FROM compras_resumen 
WHERE (isnull(id_gasto_resumen) or id_gasto_resumen ='') 
AND YEAR(fecha_factura) = 2025 LIMIT 10";
/*AND  empresa = '$var_empresa' AND sucursal = '$var_sucursal' limit 1";*/

$result = $conn->query($sql_compras);
if (!$result) {
    echo "Error al acceder a la base de datos: " . $conn->error;
} else {
    while ($row = $result->fetch_assoc()) {
    echo 'Primer WHILE';

        $var_id_compra          = $row['id_compra'];        // fecha_factura
        $var_fecha_factura      = $row['fecha_factura'];    // fecha_factura
        $var_fecha_registro     = $row['fecha_registro'];   // fecha_registro  
        $var_empresa            = $row['empresa'];          // empresa  
        $var_sucursal           = $row['sucursal'];         // sucursal

        $sql_gastos_recurrentes_resumen = "SELECT
            id_gasto_resumen, fecha, fecha_fin, empresa, sucursal
        FROM
            gastos_recurrentes_resumen
        WHERE
            '$var_fecha_factura' BETWEEN fecha AND fecha_fin
            AND empresa = '$var_empresa'
            AND sucursal = '$var_sucursal'";

        $result_gastos_recurrentes_resumen = $conn->query($sql_gastos_recurrentes_resumen);  
        if (!$result_gastos_recurrentes_resumen) {
            echo "Error al acceder a la base de datos: " . $conn->error;
        } else {

            while ($row = $result_gastos_recurrentes_resumen->fetch_assoc()) {
            echo 'Segundo WHILE';
                $var_id_gasto_resumen      = $row['id_gasto_resumen'];   // id_gasto_resumen 
      
                $conn->query("UPDATE compras_resumen
                SET id_gasto_resumen = $var_id_gasto_resumen 
                WHERE id_compra = $var_id_compra");

            }
        }

    }
}


// Cerrar conexión
$conn->close();
echo 'PROCESO CULMINADO';
?>