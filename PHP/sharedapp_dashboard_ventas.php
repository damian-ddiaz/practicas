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
    die("Error de conexión: {$conn->connect_error}");
}else{
    echo "CONEXION EXITOSA<br>";
}

$$sql_formato = "select 
 codigo_retencion_formato,
 fecha_gaceta
 from configuracion_retencion_formato
 where codigo_retencion_formato ='formato_01'";
 
 $var_fecha_formato_02 = '2025-07-16';

$conn->query("UPDATE compras_resumen SET codigo_retencion_formato = 'formato_01' WHERE 
iva_ret > 0 fecha_factura < '$var_fecha_formato_02'");

$conn->query("UPDATE compras_resumen SET codigo_retencion_formato = 'formato_02' WHERE 
iva_ret > 0 fecha_factura >= '$var_fecha_formato_02'");

// Cerrar conexión
$conn->close();
echo 'PROCESO CULMINADO';
?>