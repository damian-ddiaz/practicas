<?php

    // Conectar a la base de datos (asegúrate de usar tus credenciales)
    // DEVELOPER

    $host = '10.10.10.120';
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
        die("Error de conexión: " . $conn->connect_error);
    }else{
        echo 'CONEXION EXITOSA'.'<br>';

    }

/*
    $sql_create_tabla_tempopral="CREATE TEMPORARY TABLE temp_resumen_gerencial(
        id VARCHAR(10),
        descripcion VARCHAR(100),
        base_imponible DECIMAL(15,2),
        iva DECIMAL(15,2),
        total DECIMAL(15,2)
    )";

    $result = $conn->query(query: $sql_create_tabla_tempopral);

*/
    $var_fecha_ini = '2025/01/01'; // Fecha Inicial 
    $var_fecha_fin     = '2025/02/28'; // Fecha Final

    $var_empresa = 'tecnoven';
    $var_sucursal = 'maracaibo';

    // Limpiando tabla 

    $sql_limpiando_tablas =  "delete from resumen_gerencial where empresa = '$var_empresa' and sucursal = '$var_sucursal'";
    $result = $conn->query(query: $sql_limpiando_tablas);

    // Consulta compras_resumen
    $sql_ventas_productos_bs = "select
    vtd.id_ventas_transacciones,
    vtd.fecha_transaccion,
    vtd.tipo_pago,
    btp.nombre_tipo_pago,
    vtd.forma_pago,
    bfp.codigo_moneda,
    vtd.tasa_cambio,
    ip.nombre_productos,
    SUM(vtd.monto) as monto,
    SUM(vtd.monto_bs) AS monto_bs,
    vtd.conciliado
    from
    ventas_transacciones_detalles vtd
    left join banco_tipo_pago btp on btp.codigo_tipo_pago = vtd.tipo_pago
    left join banco_formas_pago bfp on bfp.codigo_formas_pago = vtd.forma_pago
    left join ventas_detalles vd on vd.id_detalle = vtd.id_ventas_transacciones
    left join inventario_productos ip on ip.codigo_productos = vd.codigo
    where
    vtd.empresa = '$var_empresa'
    and vtd.sucursal = '$var_sucursal'
    and btp.empresa = '$var_empresa'
    and btp.sucursal = '$var_sucursal'
    and bfp.empresa = '$var_empresa'
    and bfp.sucursal = '$var_sucursal'
    and ip.empresa = '$var_empresa'
    and ip.sucursal = '$var_sucursal'
    and ip.codigo_tiposerv_productos = 'V001'
    AND vtd.status = 'FACTURADO'
    and bfp.codigo_moneda = '0001'
    and vtd.fecha_transaccion between '$var_fecha_ini' and '$var_fecha_fin'";

   //  echo $sql_ventas_productos_bs;
    
    $result = $conn->query(query: $sql_ventas_productos_bs);

    $row = $result ? $result->fetch_assoc() : null; // Obtiene la fila o null si no hay resultado

    if ($row) { // Si hay un resultado
        $id              = 'ventas_pro_bs';
        $descripcion     = 'Venta Productos';
        $base_imponible  = $row['monto_bs'];

        $sql_ventas_productos_bs_insert = "INSERT INTO `resumen_gerencial` (
            `id`, 
            `descripcion`, 
            `base_imponible`,
            `empresa`,
            `sucursal`
        ) VALUES (
            '$id', 
            '$descripcion', 
             $base_imponible,
            '$var_empresa',
            '$var_sucursal')";
   
        echo $sql_ventas_productos_bs_insert;
        $conn->query($sql_ventas_productos_bs_insert);        
        }
    echo 'PROCESO CULMINADO';
    $conn->close();
?>