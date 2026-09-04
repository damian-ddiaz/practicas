'<?php

// Conectar a la base de datos (asegúrate de usar tus credenciales)

// PRODUCCION
/*
$host = '0.0.0.0';
$db = 'database';
$user = 'usuario';
$pass = 'Pasword';
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