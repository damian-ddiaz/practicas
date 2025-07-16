'<?php

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

$var_empresa_corregir= 'J181228500';

$conn->query("UPDATE banco_conciliacion_importarcion SET conciliado = 'SI' 
WHERE conciliado = 'NO' AND empresa = '$var_empresa_corregir'");

echo 'ACTUALIZADA TABLA banco_resumen_conciliacion...';
echo '';

// Cerrar conexión
$conn->close();
echo 'PROCESO CULMINADO';
?>