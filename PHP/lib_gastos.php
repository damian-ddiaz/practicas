<?phselect monto_permiti... #15p
function recalcularPlantillaGasto($idGastoResumen){
$ip_estacion = $_SERVER['REMOTE_ADDR'];
sc_select(my_data, "SELECT
    cr.id_gasto_resumen,
    IF(ip.codigo_hijo IS NULL OR ip.codigo_hijo = '', 'SIN CLASIFICAR', ip.codigo_padre) AS codigo_padre,
    IF(ip.codigo_hijo IS NULL OR ip.codigo_hijo = '', 'SIN CLASIFICAR', ip.codigo_hijo) AS codigo_hijo,
    cd.codigo_producto,
    cd.nombre_producto,
    cd.tipo_impuesto,
    SUM(CASE WHEN cd.estado = 'EN INVENTARIO' THEN cd.subtotal_renglon ELSE 0 END) AS subtotal_renglon,
    SUM(CASE WHEN cd.estado = 'EN INVENTARIO' THEN cd.total_renglon ELSE 0 END) AS total_renglon,
    cd.empresa,
    cd.sucursal,
    grr.fecha AS fecha_mes,
    grr.fecha AS fecha_ano
FROM
    compras_detalles cd
    INNER JOIN compras_resumen cr ON cr.id_compra = cd.id_compra
    INNER JOIN inventario_productos ip ON ip.codigo_productos = cd.codigo_producto
    INNER JOIN gastos_recurrentes_resumen grr ON grr.id_gasto_resumen = cr.id_gasto_resumen
WHERE
    cd.empresa = cr.empresa
    AND cd.sucursal = cr.sucursal
    AND ip.empresa = cr.empresa
    AND ip.sucursal = cr.sucursal
    AND (cd.estado = 'EN INVENTARIO' OR cd.estado = 'ANULADO')
    AND cr.id_gasto_resumen = $idGastoResumen
GROUP BY
    IF(ip.codigo_hijo IS NULL OR ip.codigo_hijo = '', 'SIN CLASIFICAR', ip.codigo_hijo)");

if ({my_data} === false) {
    $msg_error = {my_data_error};
    echo "Error al acceder a la base de datos = " . $msg_error;
} 
else {
    while (!$my_data->EOF) {
        $var_codigo_padre     = $my_data->fields[1];
        $var_codigo_hijo      = $my_data->fields[2];
        $var_subtotal_renglon = $my_data->fields[6];
        $var_total_renglon    = $my_data->fields[7];

        // Verificando si el Gasto Concepto Existe en la Plantilla */
        sc_lookup(gastos_detalles, "SELECT 
                id_gastos_resumen, 
                codigo_cuenta_padre, 
                codigo_cuenta_hijo, 
                monto_permitido, 
                gasto_concepto 
            FROM gastos_recurrentes_detalles 
            WHERE id_gastos_resumen = $idGastoResumen
            AND codigo_cuenta_padre = '$var_codigo_padre'
            AND codigo_cuenta_hijo = '$var_codigo_hijo'");
		
		/* Eliminado registros con codig_padre vacio */
         sc_exec_sql("Delete from gastos_recurrentes_detalles 
                WHERE id_gastos_resumen = $idGastoResumen 
                AND codigo_cuenta_padre = ''
                AND codigo_cuenta_hijo = ''");  
		
		/*Eliminar registro que no existan en la tabla contabilidad_plan_de_cuentas_hijo */
		sc_exec_sql("Delete g
			FROM gastos_recurrentes_detalles g
			LEFT JOIN contabilidad_plan_de_cuentas_hijo c 
				ON g.codigo_cuenta_padre = c.codigo_padre 
				AND g.codigo_cuenta_hijo = c.codigo_hijo
				AND g.empresa = c.empresa
				AND g.sucursal = c.sucursal
			WHERE g.empresa = '[usr_empresa]' 
			  AND g.id_gastos_resumen = $idGastoResumen
			  AND c.codigo_padre IS NULL");  
		
		
        // Si NO existe el Registro en la Plantilla se Crea el Registro
        if (!isset({gastos_detalles[0][2]})) {
            $insert_table  = 'gastos_recurrentes_detalles';
            $insert_fields = array(
                'id_gastos_resumen'   => "'$idGastoResumen'",
                'monto_permitido'     => "'0.00'",
                'codigo_cuenta_padre' => "'$var_codigo_padre'",
                'codigo_cuenta_hijo'  => "'$var_codigo_hijo'",
                'usuario'             => "'[usr_login]'",
                'empresa'             => "'[usr_empresa]'",
                'sucursal'            => "'[usr_sucursal]'",
                'ip_estacion'         => "'$ip_estacion'", 
                'gasto_concepto'      => "'$var_total_renglon'"
            );

            $insert_sql = 'INSERT INTO ' . $insert_table
                . ' ('   . implode(', ', array_keys($insert_fields))   . ')'
                . ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';
            sc_exec_sql($insert_sql);        
        }

        // Verificando si el Gasto Concepto es Diferente para actualizarlo
        sc_lookup(gastos_detalles_diff, "SELECT gasto_concepto 
            FROM gastos_recurrentes_detalles 
            WHERE id_gastos_resumen = $idGastoResumen
            AND codigo_cuenta_padre = '$var_codigo_padre'
            AND codigo_cuenta_hijo = '$var_codigo_hijo'
            AND gasto_concepto <> $var_total_renglon");

        if (isset({gastos_detalles_diff[0][0]})) {
            sc_exec_sql("UPDATE gastos_recurrentes_detalles SET gasto_concepto = $var_total_renglon
                WHERE id_gastos_resumen = $idGastoResumen 
                AND codigo_cuenta_padre = '$var_codigo_padre'
                AND codigo_cuenta_hijo = '$var_codigo_hijo'");    
        }
        $my_data->MoveNext();
    }
    $my_data->Close();
}

// Calculando Gasto Total de la Plantilla 
sc_lookup(gastos_sumatoria, "SELECT IFNULL(SUM(gasto_concepto), 0) FROM gastos_recurrentes_detalles
    WHERE id_gastos_resumen = $idGastoResumen");

$var_total_gasto = {gastos_sumatoria[0][0]};

sc_exec_sql("UPDATE gastos_recurrentes_resumen 
    SET gasto_total = $var_total_gasto
    WHERE id_gasto_resumen = $idGastoResumen");	
}
?>