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
    die("Error de conexión: {$conn->connect_error}");
}else{
    echo "CONEXION EXITOSA<br>";
}

$var_eliminar = 'NO';
$var_id_banco = 690;
$var_id_conciliacion = 1188;
$var_fecha_banco = '2025-08-31';

$conn->query(query: "UPDATE banco_conciliacion_importarcion SET id_conciliacion = 0, conciliado ='NO', referencia_transaccion = ' ',
tipo_conciliacion = ' '
WHERE id_banco = $var_id_banco AND referencia_transaccion <> '' AND conciliado = 'SI' 
AND fecha_banco <= '$var_fecha_banco'"); 
echo '1.- ACTUALIZADA TABLA banco_conciliacion_importarcion...';
echo '';

if($var_eliminar == 'SI'){
	$conn->query(query: "DELETE FROM banco_conciliacion_importarcion 
	WHERE conciliado = 'NO' AND id_banco = $var_id_banco AND fecha_banco <= '$var_fecha_banco'");
	echo '2.- ELIMINAMDO IMPORTACION DE banco_conciliacion_importarcion...';
	echo '';
}

$conn->query("UPDATE banco_resumen_conciliacion SET numero_conciliacion = ' ', estatus ='En espera', fecha_conciliacion = '0000-00-00'
WHERE id_conciliacion = $var_id_conciliacion"); 

echo '3.- ACTUALIZADA TABLA banco_resumen_conciliacion...';
echo '';

$conn->query("UPDATE ventas_transacciones_detalles SET conciliado = 'NO',  nro_conciliacion = '', tipo_conciliacion = '', id_conciliacion = 0
WHERE id_conciliacion = $var_id_conciliacion"); 
echo '4.-ACTUALIZADA TABLA ventas_transacciones_detalles...';
echo '';

$conn->query("UPDATE compras_transacciones_detalles SET conciliado = 'NO',  nro_conciliacion = '', tipo_conciliacion = '', id_conciliacion = 0
WHERE id_conciliacion = $var_id_conciliacion "); 
echo '5.- ACTUALIZADA TABLA compras_transacciones_detalles...';
echo '';

$conn->query("UPDATE banco_transferencias_movimientos SET conciliado = 'NO',  nro_conciliacion = '', tipo_conciliacion = '', id_conciliacion = 0
WHERE id_conciliacion = $var_id_conciliacion "); 
echo '6.- ACTUALIZADA TABLA banco_transferencias_movimientos...';
echo '';

// Cerrar conexión
$conn->close();
echo 'PROCESO CULMINADO';
?>