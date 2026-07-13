// 1. Inicialización del Log (Encabezado)
$html_log = "
<div style='margin: 20px; font-family: Arial, sans-serif;'>
    <h3 style='color: #2c3e50;'>Resumen de Conciliación Automática (Compras/Egresos)</h3>
    <table border='1' style='border-collapse: collapse; width: 100%; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
        <thead>
            <tr style='background-color: #34495e; color: white; text-align: left;'>
                <th style='padding: 10px;'>Moneda</th>
                <th style='padding: 10px;'>Banco</th>
                <th style='padding: 10px;'>Fecha Banco</th>
                <th style='padding: 10px;'>Referencia Banco</th>
                <th style='padding: 10px;'>Ref. Transacción</th>
                <th style='padding: 10px;'>Monto</th>
                <th style='padding: 10px;'>Resultado</th>
            </tr>
        </thead>
        <tbody>";

// Consulta principal corregida
$sql_principal = "SELECT
  bci.id_banco_conciliacion_importacion,
  bci.referencia AS referencia_banco,
  bci.fecha_banco,
  bci.monto AS monto_banco,
  bci.id_banco,
  ctd.id_compras_transacciones_detalles,
  ctd.id_compra,
  ctd.referencia AS referencia_transaccion,
  ctd.fecha as fecha_registro,
  ctd.fecha_transaccion,
  IF(bc.codigo_moneda = '0001', ctd.monto_bs, ctd.monto) as monto,
  ctd.status,
  ctd.empresa,
  ctd.sucursal,
  bc.codigo_moneda,
  if(bc.codigo_moneda = '0001','Bolivares','Dolares') as nombre_moneda,
  bc.nombre_banco
FROM
  banco_conciliacion_importarcion bci
  INNER JOIN compras_transacciones_detalles ctd ON bci.id_banco = ctd.id_banco 
    AND bci.fecha_banco = ctd.fecha 
    AND RIGHT(bci.referencia, 6) = RIGHT(ctd.referencia, 6)
  INNER JOIN bancos bc ON bc.id = ctd.id_banco
  INNER JOIN configuracion_reglas_de_negocio crn ON
  crn.nombre_variable = 'ejecuta_tarea_de_concliacion_auomatica' 
  AND crn.valor = 'SI'AND crn.empresa = bci.empresa
WHERE
  if(bc.codigo_moneda = '0001', IF(bci.monto > 0, bci.monto *-1, bci.monto) = ctd.monto_bs * -1, IF(bci.monto > 0, bci.monto *-1, bci.monto) = ctd.monto * -1)
  AND bci.tipo = 'D'
  AND ctd.fecha_transaccion = bci.fecha_banco
  AND COALESCE(bci.conciliado, 'NO') IN ('NO', '')
  AND bci.conciliado <> 'BL'
  AND COALESCE(ctd.conciliado, 'NO') IN ('NO', '')
  AND ctd.status = 'PROCESADO'
  AND ctd.usuario <> 'SISTEMA'
  AND ctd.ip_estacion <> 'SISTEMA'
  AND EXISTS (
    SELECT 1 
    FROM banco_resumen_conciliacion brc 
    WHERE brc.id_banco = ctd.id_banco 
    AND brc.estatus <> 'Anulado') ORDER BY RAND() Limit 11";

sc_select(my_data_cruces, $sql_principal);
$var_tipo_conciliacion = 'Tarea Automatica';

if ({my_data_cruces} === false) {
    echo "Error al acceder a la base de datos: " . {my_data_cruces_erro};
} else {
    while (!$my_data_cruces->EOF) {
        
        // Variables desde el recordset
        $var_id_banco_conciliacion_importacion  = $my_data_cruces->fields[0];
        $var_referencia_banco                   = $my_data_cruces->fields[1];
        $var_fecha_banco                        = $my_data_cruces->fields[2];
        $var_var_monto_banco                    = $my_data_cruces->fields[3];
        $var_id_banco                           = $my_data_cruces->fields[4];
        $var_id_compras_transacciones_detalles   = $my_data_cruces->fields[5];
        $var_referencia_transaccion             = $my_data_cruces->fields[7];
        $var_codigo_moneda                      = $my_data_cruces->fields[15];
        $var_nombre_banco                       = $my_data_cruces->fields[16];
        
        $sql_check = "SELECT id_conciliacion, numero_conciliacion, estatus, fecha_desde, fecha_hasta
                        FROM banco_resumen_conciliacion 
                        WHERE id_banco = '$var_id_banco' 
                            AND (estatus = 'En Espera' OR estatus = 'Confirmado') 
                            AND '$var_fecha_banco' <= fecha_hasta
                        LIMIT 1";  
        sc_lookup(rs_conciliacion, $sql_check);
        
        if (!empty({rs_conciliacion})) {

            $var_id_conciliacion        = {rs_conciliacion[0][0]};
            $var_numero_conciliacion    = {rs_conciliacion[0][1]};
            $var_fecha_hasta            = {rs_conciliacion[0][4]};

            // 2. AGREGAR FILA AL LOG
            $html_log .= "
            <tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_codigo_moneda . "</td>
				<td style='padding: 8px; border: 1px solid #ddd;'>" . $var_nombre_banco . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_fecha_banco . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_referencia_banco . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_referencia_transaccion . "</td>
                <td style='padding: 8px; border: 1px solid #ddd; text-align: right;'>" . number_format($var_var_monto_banco, 2) . "</td>
                <td style='padding: 8px; border: 1px solid #ddd; color: #27ae60; font-weight: bold;'>EXITOSO</td>
            </tr>";

            // Updates de Conciliación
            sc_exec_sql("UPDATE banco_conciliacion_importarcion SET 
                         conciliado = 'SI', 
                         referencia_transaccion = '$var_referencia_transaccion',
                         id_conciliacion = $var_id_conciliacion,
                         tipo_conciliacion = '$var_tipo_conciliacion'
                         WHERE id_banco_conciliacion_importacion = $var_id_banco_conciliacion_importacion");

            sc_exec_sql("UPDATE compras_transacciones_detalles SET 
                         conciliado = 'SI',
                         id_conciliacion = $var_id_conciliacion, 
                         nro_conciliacion = '$var_numero_conciliacion',
                         tipo_conciliacion = '$var_tipo_conciliacion'
                         WHERE id_compras_transacciones_detalles = $var_id_compras_transacciones_detalles");                 
            
            // Lógica de actualización de totales (se mantiene igual)
            $sql_totales = "SELECT id_conciliacion, codigo_banco,
                SUM(CASE WHEN conciliado = 'SI' AND tipo = 'C' THEN monto ELSE 0 END),
                SUM(CASE WHEN conciliado = 'SI' AND tipo = 'D' THEN monto ELSE 0 END),
                SUM(CASE WHEN conciliado = 'NO' AND tipo = 'C' THEN monto ELSE 0 END),
                SUM(CASE WHEN conciliado = 'NO' AND tipo = 'D' THEN monto ELSE 0 END),
                COUNT(CASE WHEN conciliado = 'SI' AND tipo = 'C' THEN 1 END),
                COUNT(CASE WHEN conciliado = 'SI' AND tipo = 'D' THEN 1 END),
                COUNT(CASE WHEN conciliado = 'NO' AND tipo = 'C' THEN 1 END),
                COUNT(CASE WHEN conciliado = 'NO' AND tipo = 'D' THEN 1 END)
                FROM banco_conciliacion_importarcion
                WHERE id_banco = $var_id_banco AND id_conciliacion = $var_id_conciliacion
                AND fecha_banco <= '$var_fecha_hasta' AND TRIM(referencia_transaccion) <> ''";
            
            sc_select(totales, $sql_totales);        
            if (!empty({totales})) {
                $sql_totales_pendientes = "SELECT tipo, fecha_transaccion, codigo_moneda,
                    CASE WHEN tipo = 'D' THEN IF(codigo_moneda = '0001', SUM(monto_bs), SUM(monto)) ELSE 0 END,
                    CASE WHEN tipo = 'C' THEN IF(codigo_moneda = '0001', SUM(monto_bs), SUM(monto)) ELSE 0 END,
                    SUM(CASE WHEN tipo = 'D' THEN 1 ELSE 0 END) OVER(),
                    SUM(CASE WHEN tipo = 'C' THEN 1 ELSE 0 END) OVER()
                    FROM Vista_movimiento_conciliacion_concliados_no
                    WHERE (id_banco_origen = $var_id_banco OR id_banco_destino = $var_id_banco)
                    AND (id_conciliacion = 0 OR id_conciliacion IS NULL)
                    AND fecha_transaccion <= '$var_fecha_hasta'";
                
                sc_lookup(totales_pendientes, $sql_totales_pendientes);

                $var_debito_conciliado = {totales}->fields[3];    
                $var_ie_debito_penidiente_conciliar_monto = {totales_pendientes}->fields[3];
                $var_ie_debito_penidiente_conciliar_cantidad = {totales_pendientes}->fields[5];    
                $var_ec_debito_penidiente_conciliar_monto = {totales}->fields[5];
                $var_ec_debito_penidiente_conciliar_cantidad = {totales}->fields[9];
                
                sc_exec_sql("UPDATE banco_resumen_conciliacion SET 
                             debito_conciliado = $var_debito_conciliado,
                             ec_debito_penidiente_conciliar_monto = $var_ec_debito_penidiente_conciliar_monto,
                             ec_debito_penidiente_conciliar_cantidad = $var_ec_debito_penidiente_conciliar_cantidad
                             WHERE id_conciliacion = $var_id_conciliacion");                
                {totales}->Close();
            }
        }
        $my_data_cruces->MoveNext();
    }
    $my_data_cruces->Close();
}

// 3. CIERRE DEL LOG Y MOSTRAR EN PANTALLA
$html_log .= "</tbody></table></div>";

// Si no hubo registros, mostrar mensaje informativo
if (strpos($html_log, 'EXITOSO') === false) {
    echo "<div style='padding: 20px; color: #e67e22; font-weight: bold;'>No se encontraron registros pendientes para conciliar con estos parámetros.</div>";
} else {
    echo $html_log;
}