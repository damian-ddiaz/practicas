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

$var_empresa = 'icarosoft';
$var_sucursal = 'icarosofmcbo';

// Actualizando la tabla gastos_recurrentes_detalles en función de la tabla "gastos_recurrentes_facturas_resumen"
$sql_plantilla = "SELECT id_gasto_resumen, fecha, fecha_fin, gasto_total 
FROM gastos_recurrentes_resumen 
WHERE cierre = 1 AND empresa = '$var_empresa' AND sucursal = '$var_sucursal'";

$result = $conn->query($sql_plantilla);
if (!$result) {
    echo "Error al acceder a la base de datos: " . $conn->error;
} else {
    while ($row = $result->fetch_assoc()) {
        $id_gasto_resumen = $row['id_gasto_resumen']; // if_factura_resumen
        $fecha = $row['fecha']; // fecha
        $fecha_fin = $row['fecha_fin']; // fecha_fin  
        $gasto_total = $row['gasto_total']; // gasto_total

        // Inicializando gastos_recurrentes_resumen
        $conn->query("UPDATE gastos_recurrentes_resumen
        SET gasto_total = 0.00 
        WHERE id_gasto_resumen = '$id_gasto_resumen'");

        // Inicializando gastos_recurrentes_detalles
        $conn->query("UPDATE gastos_recurrentes_detalles
        SET gasto_concepto = 0.00 
        WHERE id_gastos_resumen = '$id_gasto_resumen'");

        $sql_facturas_resumen = "SELECT id_factura_resumen, corr_interno, codigo_padre, codigo_hijo, total_neto, ip_estacion, empresa, sucursal, usuario 
        FROM gastos_recurrentes_facturas_resumen 
        WHERE status IN ('APROBADO','APROBADO / PAGADO')
        AND corr_interno = '$id_gasto_resumen' 
        AND codigo_padre > 0 
        AND codigo_hijo <> ' ' 
        AND fecha_emision BETWEEN '$fecha' AND '$fecha_fin'";

        $result_facturas = $conn->query($sql_facturas_resumen);

        if (!$result_facturas) {
            echo "Error al acceder a la base de datos: " . $conn->error;
        } else {
            while ($row = $result_facturas->fetch_assoc()) {
                $id_factura_resumen = $row['id_factura_resumen']; // if_factura_resumen
                $corr_interno = $row['corr_interno']; // corr_interno
                $var_codigo_padre1 = $row['codigo_padre']; // codigo_padre  
                $var_codigo_hijo1 = $row['codigo_hijo']; // codigo_hijo
                $total_neto = $row['total_neto']; // total_neto
                $ip_estacion = $row['ip_estacion']; // ip_estacion
                $empe = $row['empresa']; // empresa
                $sucu = $row['sucursal']; // sucursal
                $usuario = $row['usuario']; // usuario

                $sql_concepto = "SELECT codigo_cuenta_padre, codigo_cuenta_hijo 
                FROM gastos_recurrentes_detalles
                WHERE id_gastos_resumen = $corr_interno
                AND codigo_cuenta_hijo = '$var_codigo_hijo1' 
                AND codigo_cuenta_padre = $var_codigo_padre1";

                $result_concepto = $conn->query($sql_concepto);

                if (!$result_concepto || $result_concepto->num_rows == 0) {   
                    // Si no existe, insertar nuevo registro
                    $insert_table = 'gastos_recurrentes_detalles'; // Table name
                    $insert_fields = array( // Field list, add as many as needed
                        'id_gasto_detalles' => "'0'",
                        'id_gastos_resumen' => "'$corr_interno'",
                        'monto_permitido' => "'0.00'",
                        'codigo_cuenta_padre' => "'$var_codigo_padre1'",
                        'codigo_cuenta_hijo' => "'$var_codigo_hijo1'",
                        'usuario' => "'$usuario'",
                        'empresa' => "'$empe'",
                        'sucursal' => "'$sucu'",
                        'ip_estacion' => "'$ip_estacion'",
                        'gasto_concepto' => "'$total_neto'",
                    );

                    // Insert record
                    $insert_sql = 'INSERT INTO ' . $insert_table
                        . ' (' . implode(', ', array_keys($insert_fields)) . ')'
                        . ' VALUES (' . implode(', ', array_values($insert_fields)) . ')';

                    // Ejecutar la inserción
                    $conn->query($insert_sql);                        
                } else {
                    // // Actualizando gastos_recurrentes_detalles
                    $conn->query("UPDATE gastos_recurrentes_detalles
                    SET gasto_concepto = gasto_concepto + $total_neto 
                    WHERE codigo_cuenta_padre = '$var_codigo_padre1' 
                    AND codigo_cuenta_hijo = '$var_codigo_hijo1' 
                    AND id_gastos_resumen = '$corr_interno'");
                }
            }
            //* Calculando el gasto total 
            $sql_facturas_detalles = "SELECT SUM(gasto_concepto) AS total_gasto_concepto FROM gastos_recurrentes_detalles WHERE id_gastos_resumen = '$id_gasto_resumen'";
            $result_detalles = $conn->query($sql_facturas_detalles);
            
            // Verificar si la consulta se ejecutó correctamente
            if ($result_detalles && $result_detalles->num_rows > 0) {

                $row = $result_detalles->fetch_assoc(); // Obtener la fila de resultados
                $var_total_gasto_concepto = $row['total_gasto_concepto']; // total_gasto_concepto
               echo 'estoy en la condición'.'<be>'.$var_total_gasto_concepto;
            
                // Actualizando gastos_recurrentes_resumen
                $conn->query("UPDATE gastos_recurrentes_resumen
                SET gasto_total = $var_total_gasto_concepto 
                WHERE id_gasto_resumen = '$id_gasto_resumen'");
            }

            $result_facturas->free(); // Liberar el conjunto de resultados
        }
    }
}

// Cerrar conexión
$conn->close();
echo 'PROCESO CULMINADO';
?>