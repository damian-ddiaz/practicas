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
$var_empresa = 'tecnoven';

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: {$conn->connect_error}");
}else{
    echo "CONEXION EXITOSA<br>";
}
// Cuentas Padre 
$$sql_contabilidad_plan_de_cuentas_padre = "select codigo_padre, 
nombre_padre,
 debe, haber, 
 empresa, 
 sucursal
  from contabilidad_plan_de_cuentas_padre 
  where empresa = '$var_empresa'";

 // Cuentas Hijo 
$$sql_contabilidad_plan_de_cuentas_hijo = "select 
codigo_padre,
codigo_hijo, 
nombre_hijo, 
debe, 
haber, 
empresa, 
ucursal
  from contabilidad_plan_de_cuentas_hijo 
  where empresa = '$var_empresa'";


  
/*
$conn->query("UPDATE compras_resumen SET codigo_retencion_formato = 'formato_01' WHERE 
iva_ret > 0 fecha_factura < '$var_fecha_formato_02'");

9*/

// Cerrar conexión
$conn->close();
echo 'PROCESO CULMINADO';
?>