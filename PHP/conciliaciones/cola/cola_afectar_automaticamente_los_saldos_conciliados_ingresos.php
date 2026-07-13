// 1. Inicialización del Log (Encabezado)
$html_log = "
<div style='margin: 20px; font-family: Arial, sans-serif;'>
    <h3 style='color: #2c3e50;'>Resumen de Conciliación Automática (Ventas/Ingresos)</h3>
    <table border='1' style='border-collapse: collapse; width: 100%; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
        <thead>
            <tr style='background-color: #2980b9; color: white; text-align: left;'>
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

// Consulta principal
$sql_principal = "SELECT
  bci.id_banco_conciliacion_importacion,
  bci.referencia AS referencia_banco,
  bci.fecha_banco,
  bci.monto AS monto_banco,
  bci.id_banco,
  vtd.id_ventas_transacciones_detalles,
  vtd.id_ventas_transacciones,
  vtd.referencia AS referencia_transaccion,
  vtd.fecha AS fecha_registro,
  vtd.fecha AS fecha_transaccion,
  IF(bc.codigo_moneda = '0001', vtd.monto_bs, vtd.monto) AS monto,
  vtd.status,
  vtd.empresa,
  vtd.sucursal,
  bc.codigo_moneda,
  if(bc.codigo_moneda = '0001','Bolivares','Dolares') as nombre_moneda,
  bc.nombre_banco,
  vtd.origen
FROM
  banco_conciliacion_importarcion bci
  INNER JOIN ventas_transacciones_detalles vtd ON bci.id_banco = vtd.id_banco
    AND bci.fecha_banco = vtd.fecha
    AND RIGHT(bci.referencia, 6) = RIGHT(vtd.referencia, 6)
  INNER JOIN bancos bc ON bc.id = vtd.id_banco
  INNER JOIN configuracion_reglas_de_negocio crn ON
  crn.nombre_variable = 'ejecuta_tarea_de_concliacion_auomatica' 
  AND crn.valor = 'SI'AND crn.empresa = bci.empresa
WHERE
  bci.tipo = 'C'
  AND vtd.fecha = bci.fecha_banco
  AND COALESCE(bci.conciliado, 'NO') IN ('NO', '')
  AND bci.conciliado <> 'BL'
  AND COALESCE(vtd.conciliado, 'NO') IN ('NO', '')
  AND vtd.status = 'FACTURADO'
  /*AND vtd.origen = 'ATENCION COMERCIAL'*/
  AND (bci.referencia_padre = '' OR ISNULL(bci.referencia_padre)) 
  AND EXISTS (
    SELECT 1
    FROM banco_resumen_conciliacion brc
    WHERE brc.id_banco = vtd.id_banco
    AND brc.estatus <> 'Anulado'
  )
ORDER BY RAND() Limit 11";

sc_select(my_data_cruces, $sql_principal);
$var_tipo_conciliacion = 'Tarea Automatica';

if ({my_data_cruces} === false) {
    echo "Error al acceder a la base de datos: " . {my_data_cruces_erro};
} else {
    while (!$my_data_cruces->EOF) {
        
        // Asignación de variables desde el recordset (Indices basados en tu SELECT)
        $var_id_banco_conciliacion_importacion  = $my_data_cruces->fields[0];
        $var_referencia_banco                   = $my_data_cruces->fields[1];
        $var_fecha_banco                        = $my_data_cruces->fields[2];
        $var_var_monto_banco                    = $my_data_cruces->fields[3];
        $var_id_banco                           = $my_data_cruces->fields[4];
        
        $var_id_ventas_transacciones_detalles   = $my_data_cruces->fields[5];
        $var_referencia_transaccion             = $my_data_cruces->fields[7];
        
        // Nuevos campos para el log
        $var_nombre_moneda                      = $my_data_cruces->fields[15];
        $var_nombre_banco                       = $my_data_cruces->fields[16];
        
        $sql_check = "SELECT id_conciliacion, numero_conciliacion, 
                        estatus, fecha_desde, fecha_hasta
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

            // 2. AGREGAR FILA AL LOG ANTES DEL UPDATE
            $html_log .= "
            <tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_nombre_moneda . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_nombre_banco . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_fecha_banco . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_referencia_banco . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . $var_referencia_transaccion . "</td>
                <td style='padding: 8px; border: 1px solid #ddd; text-align: right; font-weight: bold;'>" . number_format($var_var_monto_banco, 2) . "</td>
                <td style='padding: 8px; border: 1px solid #ddd; color: #27ae60; font-weight: bold;'>EXITOSO</td>
            </tr>";

            // Update banco_conciliacion_importarcion
            sc_exec_sql("UPDATE banco_conciliacion_importarcion SET 
                         conciliado             = 'SI', 
                         referencia_transaccion = '$var_referencia_transaccion',
                         id_conciliacion        = $var_id_conciliacion,
                         tipo_conciliacion      = '$var_tipo_conciliacion'
                         WHERE id_banco_conciliacion_importacion = $var_id_banco_conciliacion_importacion");

            // Update ventas_transacciones_detalles
            sc_exec_sql("UPDATE ventas_transacciones_detalles SET 
                         conciliado             = 'SI',
                         id_conciliacion        = $var_id_conciliacion, 
                         nro_conciliacion       = '$var_numero_conciliacion',
                         tipo_conciliacion      = '$var_tipo_conciliacion'
                         WHERE id_ventas_transacciones_detalles = $var_id_ventas_transacciones_detalles");                 
            
            // Recalcular Totales
            $sql_totales = "SELECT 
                id_conciliacion, 
                codigo_banco,
                SUM(CASE WHEN conciliado = 'SI' AND tipo = 'C' THEN monto ELSE 0 END) AS monto_credito_si,
                SUM(CASE WHEN conciliado = 'SI' AND tipo = 'D' THEN monto ELSE 0 END) AS monto_debito_si,
                SUM(CASE WHEN conciliado = 'NO' AND tipo = 'C' THEN monto ELSE 0 END) AS monto_credito_no,
                SUM(CASE WHEN conciliado = 'NO' AND tipo = 'D' THEN monto ELSE 0 END) AS monto_debito_no,
                COUNT(CASE WHEN conciliado = 'SI' AND tipo = 'C' THEN 1 END) AS cant_credito_si,
                COUNT(CASE WHEN conciliado = 'SI' AND tipo = 'D' THEN 1 END) AS cant_debito_si,
                COUNT(CASE WHEN conciliado = 'NO' AND tipo = 'C' THEN 1 END) AS cant_credito_no,
                COUNT(CASE WHEN conciliado = 'NO' AND tipo = 'D' THEN 1 END) AS cant_debito_no
            FROM banco_conciliacion_importarcion
            WHERE id_banco = $var_id_banco
                AND id_conciliacion = $var_id_conciliacion
                AND fecha_banco <= '$var_fecha_hasta'
                AND TRIM(referencia_transaccion) <> ''";
            sc_select(totales, $sql_totales);        
                        
            if (!empty({totales})) {
                $sql_totales_pendientes = "SELECT
                        tipo, 
                        fecha_transaccion,
                        codigo_moneda,
                        CASE WHEN tipo = 'D' THEN IF(codigo_moneda = '0001', SUM(monto_bs), SUM(monto)) ELSE 0 END AS monto_debito,
                        CASE WHEN tipo = 'C' THEN IF(codigo_moneda = '0001', SUM(monto_bs), SUM(monto)) ELSE 0 END AS monto_credito,
                        SUM(CASE WHEN tipo = 'D' THEN 1 ELSE 0 END) OVER() AS cantidad_debitos,
                        SUM(CASE WHEN tipo = 'C' THEN 1 ELSE 0 END) OVER() AS cantidad_creditos
                    FROM Vista_movimiento_conciliacion_concliados_no
                    WHERE (id_banco_origen = $var_id_banco OR id_banco_destino = $var_id_banco)
                        AND (id_conciliacion = 0 OR id_conciliacion IS NULL)
                        AND fecha_transaccion <= '$var_fecha_hasta'";  
                    sc_lookup(totales_pendientes, $sql_totales_pendientes);                  
                                        
                // Variables para Creditos (Se actualizan en este bloque de Ventas)
                $var_credito_conciliado                       = {totales}->fields[2];
                $var_ie_credito_penidiente_conciliar_monto     = {totales_pendientes}->fields[4];
                $var_ie_credito_penidiente_conciliar_cantidad  = {totales_pendientes}->fields[6];    
                $var_ec_credito_penidiente_conciliar_monto     = {totales}->fields[4];
                $var_ec_credito_penidiente_conciliar_cantidad  = {totales}->fields[8];
                                        
                sc_exec_sql("UPDATE banco_resumen_conciliacion SET 
                             credito_conciliado                          = $var_credito_conciliado,
                             ec_credito_penidiente_conciliar_monto       = $var_ec_credito_penidiente_conciliar_monto,
                             ec_credito_penidiente_conciliar_cantidad    = $var_ec_credito_penidiente_conciliar_cantidad
                             WHERE id_conciliacion  = $var_id_conciliacion");            
                {totales}->Close();
            }
        }
        $my_data_cruces->MoveNext();
    }
    $my_data_cruces->Close();
}

// 3. CIERRE DEL LOG Y MOSTRAR EN PANTALLA
$html_log .= "</tbody></table></div>";

// Verificación final para mostrar el log o un mensaje de "vacío"
if (strpos($html_log, 'EXITOSO') === false) {
    echo "<div style='padding: 20px; color: #e67e22; font-family: Arial; font-weight: bold;'>
            ⚠️ No se encontraron créditos (Ventas) pendientes para conciliar automáticamente.
          </div>";
} else {
    echo $html_log;
}