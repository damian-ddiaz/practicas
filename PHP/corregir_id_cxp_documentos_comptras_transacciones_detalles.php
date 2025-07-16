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
}else{
    echo 'CONEXION EXITOSA'.'<br>';
}

$var_empresa   = 'J181228500'; 
$var_sucursal  = 'J181228500-SUC01';
$var_id_banco = '413';
// $var_decripcion = 'Probando Adelantos #2';

// Consulta SQL
$sql = "SELECT
  if(ctd.id_compra=0,'ADELANTO CUENTA POR PAGAR', 'COMPRAS') as modulo,
  ctd.id_banco,
  ctd.tipo_pago,
  ctd.forma_pago,
  ctd.tasa_cambio,
  ctd.referencia,
  ctd.descripcion,
  ctd.origen,
  ctd.monto,
  ctd.monto_bs,
  ctd.conciliado,
  ctd.status,
  ctd.fecha_transaccion,
  ctd.empresa,
  ctd.sucursal,
  ctd.id_compras_transacciones_detalles,
  ctd.id_compra,
  ctd.id_cxp_documentos,
  cr.id_proveedor,
  cr.numero
from
  compras_transacciones_detalles ctd
left join compras_resumen cr on cr.id_compra = ctd.id_compra 
where
  ctd.empresa = '$var_empresa'
  and  ctd.status = 'PROCESADO'
  and ctd.id_compra = 0
and ctd.id_cxp_documentos = 0
  and ctd.id_banco = '$var_id_banco'";

// Ejecutar la consulta
$result = $conn->query($sql);

if (!$result) {
    echo "Error al acceder a la base de datos: " . $conn->error;
} else {
    while ($row = $result->fetch_assoc()) {
        // bUSCANDO ADELANTO EN LA VISTA estado_cuenta_proveedor
     
        $var_referencia = $row['referencia'];
        $var_id_cxp_documentos = null; 

        $sql_abono = "SELECT id_cxp_documentos 
            FROM estado_cuenta_proveedor 
            WHERE empresa = '$var_empresa' 
            AND sucursal = '$var_sucursal' and nro_fiscal = '' and nro_control = ''  
            AND descripcion = '$var_referencia'";     
        
        $result_abono = $conn->query($sql_abono);

        if (!$result_abono) {
            echo "Error al NO se Encontro el abono: " . $conn->error;
        } else {
            if ($abono_row = $result_abono->fetch_row()) {
                $var_id_cxp_documentos = $abono_row[0];  // Accede al primer campo
                $conn->query("UPDATE compras_transacciones_detalles SET id_cxp_documentos = $var_id_cxp_documentos WHERE empresa = '$var_empresa' 
                and sucursal = '$var_sucursal' and status = 'PROCESADO' and id_compra = 0 and referencia ='$var_referencia'");
            }
        }
  //     echo 'id_cxp_documentos '.$var_id_cxp_documentos;
    }
    $result->free(); // Liberar el conjunto de resultados
}

// Cerrar conexión
$conn->close();

echo 'PROCESO CULMINADO';

?>