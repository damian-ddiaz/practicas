<?php

// Conectar a la base de datos (asegúrate de usar tus credenciales)
// DEVELOPER
/*
$host = '10.10.10.120';
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

// Iniciar la transacción
// $conn->begin_transaction();

$var_no_com_ret_org = '20250200002725'; // Retencion Original
$var_no_com_ret_cop = '20250200002723'; // Retencion Copia

$var_empresa = 'tecnoven';
// Consulta compras_resumen
$sql_compras_resumen = "SELECT 
    id_compra, 
    numero, 
    numero_factura, 
    numero_control, 
    no_maquina_fisc, 
    proveedor, 
    substr(descripcion,1,5) as descripcion, 
    fecha_factura, 
    fecha_vencimiento, 
    fecha_registro, 
    moneda, 
    tasa_cambio, 
    descuento, 
    subtotal, 
    IVA, 
    total, 
    usuario, 
    empresa, 
    sucursal, 
    ip_estacion, 
    total_bolivares, 
    codigo_proveedor, 
    cod_almacen, 
    cod_ubicacion, 
    cod_nivel, 
    estado, 
    if(compras_proveedor='','0.00',compras_proveedor) as compras_proveedor,
    saldo, 
    id_proveedor, 
    direccion, 
    telefono, 
    tasa_iva, 
    tasa_iva_redu, 
    no_com_ret, 
    ret_fac_afec, 
    iva_bs, 
    iva_reduc_bs, 
    iva_ret, 
    iva_ret_porc, 
    sub_total_bs, 
    no_com_ret_islr, 
    islr_ret, 
    islr_porc, 
    islr_sust, 
    num_planilla, 
    num_exp_impor, 
    nacio_extran, 
    base_impo_bs, 
    base_exenta_bs, 
    base_exonera_bs, 
    base_alicu_redu_bs, 
    original, 
    codigo_padre, 
    codigo_hijo, 
    id_gasto_resumen 
FROM 
    compras_resumen where no_com_ret = '$var_no_com_ret_org' and  empresa = '$var_empresa'";

$result = $conn->query(query: $sql_compras_resumen);

$row = $result ? $result->fetch_assoc() : null; // Obtiene la fila o null si no hay resultado

if ($row) { // Si hay un resultado
    $id_compra_original      = $row['id_compra'];
    $numero                  = $row['numero'];
    $numero_factura          = $row['numero_factura'];
    $numero_control          = $row['numero_control'];
    $no_maquina_fisc         = $row['no_maquina_fisc'];
    $proveedor               = $row['proveedor'];
    $descripcion             = $row['descripcion'];
    $fecha_factura           = $row['fecha_factura'];
    $fecha_vencimiento       = $row['fecha_vencimiento'];
    $fecha_registro          = $row['fecha_registro'];
    $moneda                  = $row['moneda'];
    $tasa_cambio             = $row['tasa_cambio'];
    $descuento               = $row['descuento'];
    $subtotal                = $row['subtotal'];
    $IVA                     = $row['IVA'];
    $total                   = $row['total'];
    $usuario                 = $row['usuario'];
    $empresa                 = $row['empresa'];
    $sucursal                = $row['sucursal'];
    $ip_estacion             = $row['ip_estacion'];
    $total_bolivares         = $row['total_bolivares'];
    $codigo_proveedor        = $row['codigo_proveedor'];
    $cod_almacen             = $row['cod_almacen'];
    $cod_ubicacion           = $row['cod_ubicacion'];
    $cod_nivel               = $row['cod_nivel'];
    $estado                  = 'ANULADO';//$row['estado'];
    $compras_proveedor       = $row['compras_proveedor'];
    $saldo                   = $row['saldo'];
    $id_proveedor            = $row['id_proveedor'];
    $direccion               = $row['direccion'];
    $telefono                = $row['telefono'];
    $tasa_iva                = $row['tasa_iva'];
    $tasa_iva_redu           = $row['tasa_iva_redu'];
    $no_com_ret              = $var_no_com_ret_cop; //$row['no_com_ret'];
    $ret_fac_afec            = $row['ret_fac_afec'];
    $iva_bs                  = $row['iva_bs'];
    $iva_reduc_bs            = $row['iva_reduc_bs'];
    $iva_ret                 = $row['iva_ret'];
    $iva_ret_porc            = $row['iva_ret_porc'];
    $sub_total_bs            = $row['sub_total_bs'];
    $no_com_ret_islr         = $row['no_com_ret_islr'];
    $islr_ret                = $row['islr_ret'];
    $islr_porc               = $row['islr_porc'];
    $islr_sust               = $row['islr_sust'];
    $num_planilla            = $row['num_planilla'];
    $num_exp_impor           = $row['num_exp_impor'];
    $nacio_extran            = $row['nacio_extran'];
    $base_impo_bs            = $row['base_impo_bs'];
    $base_exenta_bs          = $row['base_exenta_bs'];
    $base_exonera_bs         = $row['base_exonera_bs'];
    $base_alicu_redu_bs      = $row['base_alicu_redu_bs'];
    $original                = $row['original'];
    $codigo_padre            = $row['codigo_padre'];
    $codigo_hijo             = $row['codigo_hijo'];
    $id_gasto_resumen        = $row['id_gasto_resumen'];

    $sql_compras_resumen_insert = "INSERT INTO `compras_resumen` (
        `numero`, 
        `numero_factura`, 
        `numero_control`, 
        `no_maquina_fisc`, 
        `proveedor`, 
        `descripcion`, 
        `fecha_factura`, 
        `fecha_vencimiento`, 
        `fecha_registro`, 
        `moneda`, 
        `tasa_cambio`, 
        `descuento`, 
        `subtotal`, 
        `IVA`, 
        `total`, 
        `usuario`, 
        `empresa`, 
        `sucursal`, 
        `ip_estacion`, 
        `total_bolivares`, 
        `codigo_proveedor`, 
        `cod_almacen`, 
        `cod_ubicacion`, 
        `cod_nivel`, 
        `estado`, 
        `compras_proveedor`, 
        `saldo`, 
        `id_proveedor`, 
        `direccion`, 
        `telefono`, 
        `tasa_iva`, 
        `tasa_iva_redu`, 
        `no_com_ret`, 
        `ret_fac_afec`, 
        `iva_bs`, 
        `iva_reduc_bs`, 
        `iva_ret`, 
        `iva_ret_porc`, 
        `sub_total_bs`, 
        `no_com_ret_islr`, 
        `islr_ret`, 
        `islr_porc`, 
        `islr_sust`, 
        `num_planilla`, 
        `num_exp_impor`, 
        `nacio_extran`, 
        `base_impo_bs`, 
        `base_exenta_bs`, 
        `base_exonera_bs`, 
        `base_alicu_redu_bs`, 
        `original`, 
        `codigo_padre`, 
        `codigo_hijo`, 
        `id_gasto_resumen`
    ) VALUES (
        '$numero', 
        '$numero_factura', 
        '$numero_control', 
        '$no_maquina_fisc', 
        '$proveedor', 
        '$descripcion', 
        '$fecha_factura', 
        '$fecha_vencimiento', 
        '$fecha_registro', 
        '$moneda', 
        $tasa_cambio, 
        $descuento, 
        $subtotal, 
        $IVA, 
        $total, 
        '$usuario', 
        '$empresa', 
        '$sucursal', 
        '$ip_estacion', 
        $total_bolivares, 
        '$codigo_proveedor', 
        '$cod_almacen', 
        '$cod_ubicacion', 
        '$cod_nivel', 
        '$estado', 
        $compras_proveedor, 
        $saldo, 
        $id_proveedor, 
        '$direccion', 
        '$telefono', 
        $tasa_iva, 
        $tasa_iva_redu, 
        '$no_com_ret', 
        '$ret_fac_afec', 
        $iva_bs, 
        $iva_reduc_bs, 
        $iva_ret, 
        $iva_ret_porc, 
        $sub_total_bs, 
        '$no_com_ret_islr', 
        $islr_ret, 
        $islr_porc, 
        $islr_sust, 
        '$num_planilla', 
        '$num_exp_impor', 
        '$nacio_extran', 
        $base_impo_bs, 
        $base_exenta_bs, 
        $base_exonera_bs, 
        $base_alicu_redu_bs, 
        '$original', 
        '$codigo_padre', 
        '$codigo_hijo', 
        $id_gasto_resumen
    )";

// echo $sql_compras_resumen_insert;

$conn->query($sql_compras_resumen_insert);
    // Buscando el id_compra de la nueva Compra 

    $sql_compras_resumen_nueva = "SELECT 
        id_compra
    FROM 
        compras_resumen where no_com_ret = '$var_no_com_ret_cop' and  empresa = '$var_empresa'";

        $result = $conn->query(query: $sql_compras_resumen_nueva);
        $row = $result ? $result->fetch_assoc() : null; // Obtiene la fila o null si no hay resultado

      //  echo $sql_compras_resumen_nueva;

        if ($row) { // Si hay un resultado
            $var_id_compra_nueva = $row['id_compra']; // El ID de la Nueva Compra 
        }

}else{// No Existe la Compra
    echo 'NO Eiste una Compra con ese Numero de Retencion de IVA ';
}

// Consulta compras_detalles
$sql_compras_detalles = "SELECT 
    id_compra, 
    estado, 
    nombre_producto, 
    precio_unitario, 
    impuesto_productos, 
    tipo_impuesto, 
    cantidad, 
    subtotal_renglon, 
    total_renglon, 
    empresa, 
    sucursal, 
    usuario, 
    fecha, 
    ip_estacion, 
    codigo_producto, 
    tasa_cambio, 
    tipo_unidad, 
    codigo_almacen, 
    iva, 
    iva_total, 
    precio_unitario_bs, 
    subtotal_renglon_bs, 
    total_renglon_bs, 
    total_iva_bs 
FROM 
    compras_detalles where id_compra = $id_compra_original and  empresa = '$var_empresa'";

// Ejecutar la consulta
$result = $conn->query($sql_compras_detalles);
$row = $result ? $result->fetch_assoc() : null; // Obtiene la fila o null si no hay resultado

if ($row) { // Si hay un resultado
    foreach ($result as $row) {	
        $id_compra              = $var_id_compra_nueva;
        $estado                 = 'ANULADO';//$row['estado'];
        $nombre_producto        = $row['nombre_producto'];
        $precio_unitario        = $row['precio_unitario'];
        $impuesto_productos     = $row['impuesto_productos'];
        $tipo_impuesto          = $row['tipo_impuesto'];
        $cantidad               = $row['cantidad'];
        $subtotal_renglon       = $row['subtotal_renglon'];
        $total_renglon          = $row['total_renglon'];
        $empresa                = $row['empresa'];
        $sucursal               = $row['sucursal'];
        $usuario                = $row['usuario'];
        $fecha                  = $row['fecha'];
        $ip_estacion            = $row['ip_estacion'];
        $codigo_producto        = $row['codigo_producto'];
        $tasa_cambio            = $row['tasa_cambio'];
        $tipo_unidad            = $row['tipo_unidad'];
        $codigo_almacen         = $row['codigo_almacen'];
        $iva                    = $row['iva'];
        $iva_total              = $row['iva_total'];
        $precio_unitario_bs     = $row['precio_unitario_bs'];
        $subtotal_renglon_bs    = $row['subtotal_renglon_bs'];
        $total_renglon_bs       = $row['total_renglon_bs'];
        $total_iva_bs           = $row['total_iva_bs'];

        $sql_compras_detalles_insert = "INSERT INTO `compras_detalles` (
            `id_compra`, 
            `estado`, 
            `nombre_producto`, 
            `precio_unitario`, 
            `impuesto_productos`, 
            `tipo_impuesto`, 
            `cantidad`, 
            `subtotal_renglon`, 
            `total_renglon`, 
            `empresa`, 
            `sucursal`, 
            `usuario`, 
            `fecha`, 
            `ip_estacion`, 
            `codigo_producto`, 
            `tasa_cambio`, 
            `tipo_unidad`, 
            `codigo_almacen`, 
            `iva`, 
            `iva_total`, 
            `precio_unitario_bs`, 
            `subtotal_renglon_bs`, 
            `total_renglon_bs`, 
            `total_iva_bs`
        ) VALUES (
            $var_id_compra_nueva, 
            '$estado', 
            '$nombre_producto', 
            $precio_unitario, 
            '$impuesto_productos', 
            $tipo_impuesto, 
            $cantidad, 
            $subtotal_renglon, 
            $total_renglon, 
            '$empresa', 
            '$sucursal', 
            '$usuario', 
            '$fecha', 
            '$ip_estacion', 
            '$codigo_producto', 
            $tasa_cambio, 
            '$tipo_unidad', 
            '$codigo_almacen', 
            $iva, 
            $iva_total, 
            $precio_unitario_bs, 
            $subtotal_renglon_bs, 
            $total_renglon_bs, 
            $total_iva_bs
        )";

      //  echo $sql_compras_detalles_insert;
        $conn->query($sql_compras_detalles_insert);
    }
}

// Consulta compras_transacciones_detalles
$sql_compras_transacciones_detalles = "SELECT 
    `id_compras_transacciones_detalles`, 
    `id_compra`, 
    `id_conciliacion`, 
    `tipo_pago`, 
    `forma_pago`, 
    `tasa_cambio`, 
    `origen`, 
    `cod_forma_pago`, 
    `referencia`, 
    `descripcion`, 
    `monto`, 
    `monto_bs`, 
    `fecha_transaccion`, 
    `conciliado`, 
    `nro_conciliacion`, 
    `revisado`, 
    `status`, 
    `tipo_conciliacion`, 
    `fecha`, 
    `empresa`, 
    `sucursal`, 
    `usuario`, 
    `usr_nivel`, 
    `ip_estacion`, 
    `id_cxp_pago_resumen`, 
    `id_factura_resumen`, 
    `tipo_movimiento`, 
    `id_cxp_documentos`, 
    `id_banco`
FROM 
    `compras_transacciones_detalles` where id_compra = $id_compra_original and  empresa = '$var_empresa'";

// Ejecutar la consulta
$result = $conn->query($sql_compras_transacciones_detalles);
$row = $result ? $result->fetch_assoc() : null; // Obtiene la fila o null si no hay resultado

if ($row) { // Si hay un resultado
    foreach ($result as $row) {	
        $id_compras_transacciones_detalles = $row['id_compras_transacciones_detalles'];
        $id_compra                          = $var_id_compra_nueva;
        $id_conciliacion                    = $row['id_conciliacion'];
        $tipo_pago                          = $row['tipo_pago'];
        $forma_pago                         = $row['forma_pago'];
        $tasa_cambio                        = $row['tasa_cambio'];
        $origen                             = $row['origen'];
        $cod_forma_pago                     = $row['cod_forma_pago'];
        $referencia                         = $row['referencia'];
        $descripcion                        = $row['descripcion'];
        $monto                              = $row['monto'];
        $monto_bs                           = $row['monto_bs'];
        $fecha_transaccion                  = $row['fecha_transaccion'];
        $conciliado                         = $row['conciliado'];
        $nro_conciliacion                   = $row['nro_conciliacion'];
        $revisado                           = $row['revisado'];
        $status                             = 'ANULADO';//$row['status'];
        $tipo_conciliacion                  = $row['tipo_conciliacion'];
        $fecha                              = $row['fecha'];
        $empresa                            = $row['empresa'];
        $sucursal                           = $row['sucursal'];
        $usuario                            = $row['usuario'];
        $usr_nivel                          = ' '; //$row['usr_nivel'];
        $ip_estacion                        = $row['ip_estacion'];
        $id_cxp_pago_resumen                = $row['id_cxp_pago_resumen'];
        $id_factura_resumen                 = $row['id_factura_resumen'];
        $tipo_movimiento                    = $row['tipo_movimiento'];
        $id_cxp_documentos                  = ' '; //$row['id_cxp_documentos'];
        $id_banco                           = $row['id_banco'];

        $sql_compras_transacciones_detalles_insert = "INSERT INTO `compras_transacciones_detalles` (
            `id_compra`, 
            `id_conciliacion`, 
            `tipo_pago`, 
            `forma_pago`, 
            `tasa_cambio`, 
            `origen`, 
            `cod_forma_pago`, 
            `referencia`, 
            `descripcion`, 
            `monto`, 
            `monto_bs`, 
            `fecha_transaccion`, 
            `conciliado`, 
            `nro_conciliacion`, 
            `revisado`, 
            `status`, 
            `tipo_conciliacion`, 
            `fecha`, 
            `empresa`, 
            `sucursal`, 
            `usuario`, 
            `usr_nivel`, 
            `ip_estacion`, 
            `id_cxp_pago_resumen`, 
            `id_factura_resumen`, 
            `id_banco`
        ) VALUES (
            $var_id_compra_nueva, 
            $id_conciliacion, 
            '$tipo_pago', 
            '$forma_pago', 
            $tasa_cambio, 
            '$origen', 
            '$cod_forma_pago', 
            '$referencia', 
            '$descripcion', 
            $monto, 
            $monto_bs, 
            '$fecha_transaccion', 
            '$conciliado', 
            '$nro_conciliacion', 
            '$revisado', 
            '$status', 
            '$tipo_conciliacion', 
            '$fecha', 
            '$empresa', 
            '$sucursal', 
            '$usuario', 
            '$usr_nivel', 
            '$ip_estacion', 
            $id_cxp_pago_resumen, 
            $id_factura_resumen, 
            $id_banco
        )";   
      //  echo $sql_compras_transacciones_detalles_insert;
        $conn->query($sql_compras_transacciones_detalles_insert);
    }
}

// Consulta cxp_documentos
$sql_cxp_documentos = "SELECT 
    id_cxp_documentos, 
    tipo_documento, 
    numero_documento, 
    nro_fiscal, 
    nro_control, 
    descripcion, 
    cod_proveedor, 
    sub_total, 
    total_neto, 
    saldo, 
    tasa_cambio, 
    fecha_emision, 
    fecha_vencimiento, 
    tipo_documento_afect, 
    numero_documento_afect, 
    estatus, 
    tipo, 
    fecha, 
    usuario, 
    empresa, 
    sucursal, 
    ip_estacion, 
    id_proveedor, 
    id_compra
FROM 
    cxp_documentos where id_compra = $id_compra_original and  empresa = '$var_empresa'";

$result = $conn->query($sql_cxp_documentos);
$row = $result ? $result->fetch_assoc() : null; // Obtiene la fila o null si no hay resultado

$id_cxp_documentos      = $row['id_cxp_documentos'];
$tipo_documento         = $row['tipo_documento'];
$numero_documento       = $row['numero_documento'];
$nro_fiscal             = $row['nro_fiscal'];
$nro_control            = $row['nro_control'];
$descripcion            = $row['descripcion'];
$cod_proveedor          = $row['cod_proveedor'];
$sub_total              = $row['sub_total'];
$total_neto             = $row['total_neto'];
$saldo                  = $row['saldo'];
$tasa_cambio            = $row['tasa_cambio'];
$fecha_emision          = $row['fecha_emision'];
$fecha_vencimiento      = $row['fecha_vencimiento'];
$tipo_documento_afect   = $row['tipo_documento_afect'];
$numero_documento_afect = $row['numero_documento_afect'];
$estatus                = 'ANULADO';//$row['estatus'];
$tipo                   = $row['tipo'];
$fecha                  = $row['fecha'];
$usuario                = $row['usuario'];
$empresa                = $row['empresa'];
$sucursal               = $row['sucursal'];
$ip_estacion            = $row['ip_estacion'];
$id_proveedor           = $row['id_proveedor'];
$id_compra              = $row['id_compra'];

$sql_cxp_documentos_insert = "INSERT INTO `cxp_documentos` (
    tipo_documento, 
    numero_documento, 
    nro_fiscal, 
    nro_control, 
    descripcion, 
    cod_proveedor, 
    sub_total, 
    total_neto, 
    saldo, 
    tasa_cambio, 
    fecha_emision, 
    fecha_vencimiento, 
    tipo_documento_afect, 
    numero_documento_afect, 
    estatus, 
    tipo, 
    fecha, 
    usuario, 
    empresa, 
    sucursal, 
    ip_estacion, 
    id_proveedor, 
    id_compra
) VALUES (
    '$tipo_documento', 
    '$numero_documento', 
    '$nro_fiscal', 
    '$nro_control', 
    '$descripcion', 
    '$cod_proveedor', 
    $sub_total, 
    $total_neto, 
    $saldo, 
    $tasa_cambio, 
    '$fecha_emision', 
    '$fecha_vencimiento', 
    '$tipo_documento_afect', 
    '$numero_documento_afect', 
    '$estatus', 
    '$tipo', 
    '$fecha', 
    '$usuario', 
    '$empresa', 
    '$sucursal', 
    '$ip_estacion', 
    $id_proveedor, 
    $var_id_compra_nueva
)";
// echo $sql_cxp_documentos_insert;
$result = $conn->query($sql_cxp_documentos_insert);

// Consulta gastos_recurrentes_facturas_resumen
$sql_gastos_recurrentes_facturas_resumen = "SELECT
  corr_interno,
  empresa2,
  sucursal2,
  usuario,
  ip_estacion,
  fecha,
  codigo_padre,
  fecha_emision,
  fecha_vencimiento,
  fecha_reg,
  empresa,
  tasa_cambio,
  porcentaje_desc,
  monto_desc,
  porcentaje_iva,
  monto_exento,
  total_neto,
  total_neto_bs,
  monto_iva,
  monto_iva_bs,
  sub_total,
  status,
  descripcion,
  sucursal,
  saldo,
  codigo_hijo,
  gasto_concepto,
  referencia,
  codigo_proveedor,
  saldo_fact,
  original,
  id_compra,
  total,
  total_bs,
  base_exenta_bs,
  base_exonera_bs
from
  gastos_recurrentes_facturas_resumen
where
  id_compra  = $id_compra_original and  empresa = '$var_empresa'";

$result = $conn->query($sql_gastos_recurrentes_facturas_resumen);
$row = $result ? $result->fetch_assoc() : null; // Obtiene la fila o null si no hay resultado

if ($row) {
    // Asignar valores a variables
    $corr_interno      = $row['corr_interno'];
    $empresa2          = $row['empresa2'];
    $sucursal2         = $row['sucursal2'];
    $usuario           = $row['usuario'];
    $ip_estacion       = $row['ip_estacion'];
    $fecha             = $row['fecha'];
    $codigo_padre      = $row['codigo_padre'];
    $fecha_emision     = $row['fecha_emision'];
    $fecha_vencimiento = $row['fecha_vencimiento'];
    $fecha_reg         = $row['fecha_reg'];
    $empresa           = $row['empresa'];
    $tasa_cambio       = $row['tasa_cambio'];
    $porcentaje_desc    = $row['porcentaje_desc'];
    $monto_desc        = $row['monto_desc'];
    $porcentaje_iva    = $row['porcentaje_iva'];
    $monto_exento      = $row['monto_exento'];
    $total_neto        = $row['total_neto'];
    $total_neto_bs     = $row['total_neto_bs'];
    $monto_iva         = $row['monto_iva'];
    $monto_iva_bs      = $row['monto_iva_bs'];
    $sub_total         = $row['sub_total'];
    $status            = 'ANULADO'; //$row['status'];
    $descripcion       = $row['descripcion'];
    $sucursal          = $row['sucursal'];
    $saldo             = $row['saldo'];
    $codigo_hijo       = $row['codigo_hijo'];
    $gasto_concepto    = $row['gasto_concepto'];
    $referencia        = $row['referencia'];
    $codigo_proveedor   = $row['codigo_proveedor'];
    $saldo_fact        = $row['saldo_fact'];
    $original          = $row['original'];
    $id_compra         = $row['id_compra'];
    $total             = $row['total'];
    $total_bs          = $row['total_bs'];
    $base_exenta_bs    = $row['base_exenta_bs'];
    $base_exonera_bs   = $row['base_exonera_bs'];

    $sql_gastos_recurrentes_facturas_resumen_insert = "INSERT INTO gastos_recurrentes_facturas_resumen (
        corr_interno,
        empresa2,
        sucursal2,
        usuario,
        ip_estacion,
        fecha,
        codigo_padre,
        fecha_emision,
        fecha_vencimiento,
        fecha_reg,
        empresa,
        tasa_cambio,
        porcentaje_desc,
        monto_desc,
        porcentaje_iva,
        monto_exento,
        total_neto,
        total_neto_bs,
        monto_iva,
        monto_iva_bs,
        sub_total,
        status,
        descripcion,
        sucursal,
        saldo,
        codigo_hijo,
        gasto_concepto,
        referencia,
        codigo_proveedor,
        saldo_fact,
        original,
        id_compra,
        total,
        total_bs,
        base_exenta_bs,
        base_exonera_bs
    ) VALUES (
        '$corr_interno',
        '$empresa2',
        '$sucursal2',
        '$usuario',
        '$ip_estacion',
        '$fecha',
        '$codigo_padre',
        '$fecha_emision',
        '$fecha_vencimiento',
        '$fecha_reg',
        '$empresa',
        $tasa_cambio,
        $porcentaje_desc,
        $monto_desc,
        $porcentaje_iva,
        $monto_exento,
        $total_neto,
        $total_neto_bs,
        $monto_iva,
        $monto_iva_bs,
        $sub_total,
        '$status',
        '$descripcion',
        '$sucursal',
        $saldo,
        '$codigo_hijo',
        '$gasto_concepto',
        '$referencia',
        '$codigo_proveedor',
        $saldo_fact,
        '$original',
        $var_id_compra_nueva,
        $total,
        $total_bs,
        $base_exenta_bs,
        $base_exonera_bs
    )";
   //  echo $sql_gastos_recurrentes_facturas_resumen_insert;
    $result = $conn->query($sql_gastos_recurrentes_facturas_resumen_insert);
} 
echo 'PROCESO CULMINADO';
$conn->close();
?>