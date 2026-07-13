'<?php

// Conectar a la base de datos (asegúrate de usar tus credenciales)

// DEVELOPER

$host   = '172.16.7.50';
$db     = 'webservices';
$user   = 'scryptcase';
$pass   = 'Mt*1329*--1';

// PRODUCCION
/*-
$host   = '45.179.164.7';
$db     = 'webservices';
$user   = 'scryptcase';
$pass   = 'Mt*1329*--1';
*/
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: {$conn->connect_error}");
}else{
    echo "CONEXION EXITOSA<br>";
}

?>