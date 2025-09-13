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

$sql_borrando_bancos_sucursales = "delete from bancos_sucursales";

// Ejecutar la consulta
$result = $conn->query($sql_borrando_bancos_sucursales);

$sql_bancos = "select 
id,, 
codigo_banco, 
codigo_moneda, 
saldo_teorico, 
saldo_conciliado,
  from bancos";

 // Ejecutar la consulta
$result = $conn->query($sql_bancos);

if (!$result) {
    echo "Error al acceder a la base de datos: {$conn->error}";
} else {
    while ($row = $result->fetch_assoc()) {
        // bUSCANDO ADELANTO EN LA VISTA estado_cuenta_proveedor
        $var_id_banco               = $row['id'];
        $var_codigo_moneda          = $row['codigo_moneda'];
        $var_saldo_teorico          = $row['saldo_teorico'];
        $var_saldo_conciliado       = $row['saldo_conciliado'];

        $sql_banco = "SELECT id 
            FROM bancos 
            WHERE empresa = '$var_empresa_corregir' 
            AND codigo_banco = '$var_codigo_banco'";     
        
        $result_banco = $conn->query($sql_banco);

        if (!$result_banco) {
            echo "Error al NO se Encontro el abono: {$conn->error}";
        } else {
        //    echo 'CONDICION 1';
             if ($banco_row = $result_banco->fetch_row()) {
                $var_id_banco = $banco_row[0];  // Accede al primer campo
              
             //   echo 'CONDICION BANCO '.$var_id_banco;
                $conn->query("UPDATE banco_resumen_conciliacion SET id_banco = $var_id_banco WHERE 
                id_conciliacion = $var_id_conciliacion");
            }
                
        }
    }
    echo 'ACTUALIZADA TABLA banco_resumen_conciliacion...';
    echo '';
}

// Actualizar id_banco en la tabla banco_conciliacion_importarcion 
$sql_banco_conciliacion_importarcion = "select 
id_banco_conciliacion_importacion, 
codigo_banco, 
empresa, 
sucursal, 
id_banco
  from banco_conciliacion_importarcion
where id_banco = 0 or isnull(id_banco) and empresa = '$var_empresa_corregir' and sucursal = '$var_sucursal_corregir'";

// Ejecutar la consulta
$result = $conn->query($sql_banco_conciliacion_importarcion);

if (!$result) {
    echo "Error al acceder a la base de datos: {$conn->error}";
} else {
    while ($row = $result->fetch_assoc()) {
        // bUSCANDO ADELANTO EN LA VISTA estado_cuenta_proveedor
        $var_id_banco_conciliacion_importacion    = $row['id_banco_conciliacion_importacion'];
        $var_codigo_banco                         = $row['codigo_banco'];
      //  $var_empresa                              = $row['empresa'];

        $sql_banco = "SELECT id 
            FROM bancos 
            WHERE empresa = '$var_empresa_corregir' 
            AND codigo_banco = '$var_codigo_banco'";     
        
        $result_banco = $conn->query($sql_banco);

        if (!$result_banco) {
            echo "Error al NO se Encontro el abono: {$conn->error}";
        } else {
        //    echo 'CONDICION 1';
             if ($banco_row = $result_banco->fetch_row()) {
                $var_id_banco = $banco_row[0];  // Accede al primer campo
              //  echo 'CONDICION BANCO '.$var_id_banco;
                $conn->query("UPDATE banco_conciliacion_importarcion SET id_banco = $var_id_banco
                                 WHERE codigo_banco = '$var_codigo_banco' and empresa = '$var_empresa_corregir' and sucursal = '$var_sucursal_corregir'");
            }          
        }
    }
    echo 'ACTUALIZADA TABLA banco_conciliacion_importarcion...';
    echo '';
}

// Cerrar conexión
$conn->close();
echo 'PROCESO CULMINADO';
?>