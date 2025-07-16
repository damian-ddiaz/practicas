<?php
    sc_select(my_data, "SELECT
    id_relacion,
    origen,
    empresa,
    sucursal,
    status,
    mensaje,
    id_transaccion_banco,
    accion,
    tipo
    FROM
    cola_transacciones_bancos 
    WHERE
    status = 'PENDIENTE'");
    if ({my_data} === false){
    echo "Error al acceder a la base de datos =". {my_data_erro};
    }
    else{
    while (!$my_data->EOF){
    $id_relacion = $my_data->fields[0]; // id_relacion
    $var_origen = $my_data->fields[1]; // origen
    $empe = $my_data->fields[2]; // empresa
    $sucu = $my_data->fields[3]; // sucursal
    $id_transaccion_banco = $my_data->fields[6];
    $var_tipo = $my_data->fields[8]; // CREDITO O DEBITO

    if($var_origen == 'VENTAS'){
    	sc_lookup_field(transac, "select idbanco(tipo_pago,forma_pago,empresa,sucursal) as id_banco,tipo_pago,tasa_cambio,referencia,monto,monto_bs,conciliado,fecha_transaccion, status from ventas_transacciones_detalles where id_ventas_transacciones_detalles = $id_relacion");
    }elseif($var_origen == 'COMPRA'){// COMPRAS
    	sc_lookup_field(transac, "select idbanco(tipo_pago,forma_pago,empresa,sucursal) as id_banco,tipo_pago, tasa_cambio,referencia,monto,monto_bs,conciliado,fecha_transaccion, status from compras_transacciones_detalles where tipo_pago<>'RT'and id_compras_transacciones_detalles = $id_relacion");		
    }elseif($var_origen == 'BANCO'){// BANCO
		sc_lookup_field(tiṕo, "select tipo from banco_transferencias_movimientos where  id_movimiento = $id_relacion");		
		$var_tipo = {tiṕo[0]['tiṕo']};
		if($var_tipo=='CREDITO'){// CREDITO
			sc_lookup_field(transac, "select id_movimiento,id_banco_destino as id_banco,  monto_a_creditar as monto, monto_a_creditar_bs as monto_bs,  status, tipo  from banco_transferencias_movimientos where  id_movimiento = $id_relacion");	
		}else{//DEBITO
			sc_lookup_field(transac, "select id_movimiento,id_banco_destino as id_banco,  monto_debito_origen as monto, monto_debito_origen_bs as monto_bs,  status, tipo  from banco_transferencias_movimientos where  id_movimiento = $id_relacion");	
		}
    }

    //	$var_tipo_pago	= {transac[0]['tipo_pago']};
    $id_banco		= {transac[0]['id_banco']};
    $var_conciliado = {transac[0]['conciliado']};

   //Buscando Moneda
	sc_lookup_field(banco, "select id,codigo_moneda from bancos where id = $id_banco");		
	$cod_moneda		= {banco[0]['codigo_moneda']};
	
	if(isset($id_banco)){
    // VERIFICANDO QUE EXISEN BANCO SUCURSALES
    sc_lookup_field(banco_sucursal, "select id_banco,empresa,moneda from bancos_sucursales where sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");		
    sc_lookup_field(banco, "select id, codigo_moneda, codigo_banco from bancos where id=$id_banco");
    $cod_moneda = {banco[0]['codigo_moneda']};


    if(!isset({banco_sucursal[0]['empresa']})){
    //				echo 'Este banco No Existe en banco sucursales';
    $insert_tab  = 'bancos_sucursales';      // Table name
    $insert_fields = array(   // Field list, add as many as needed
    'id_banco' => "'$id_banco'",
    'empresa' => "'$empe'",
    'sucursal' => "'$sucu'",
    'saldo_teorico' => "0",
    'saldo_conciliado' => "0",
    'moneda' => "'$cod_moneda'",
    );

    // Insert record
    $insert_sql = 'INSERT INTO ' . $insert_tab
    . ' ('   . implode(', ', array_keys($insert_fields))   . ')'
    . ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';		
    sc_exec_sql($insert_sql);vrntas_tra
    }


    if($cod_moneda == '0001'){ //Banco Bolivares
		$var_monto_teorico		= {transac[0]['monto_bs']};
		$var_monto_conciliado	= {transac[0]['monto_bs']};
    }else{ // Banco Dolares
		$var_monto_teorico		= {transac[0]['monto']};
		$var_monto_conciliado	= {transac[0]['monto']};
    }

    if($var_origen == 'VENTAS'){
    if($var_conciliado == 'SI'){// CONCILIADO	
    echo 'Venta Conciliada'; 						
    // ACTUALIZANDO BANCOS 
    if({transac[0]['status']}=='ANULADO'){
        sc_exec_sql("update bancos set saldo_conciliado = saldo_conciliado - $var_monto_conciliado, saldo_teorico = saldo_teorico - 		$var_monto_teorico  where id = $id_banco");
        $var_mensaje = 'Tranasaccion ANULADA';
    }else{// PROCESADO
        sc_exec_sql("update bancos set saldo_conciliado = saldo_conciliado + $var_monto_conciliado, saldo_teorico = saldo_teorico + 		$var_monto_teorico  where id = $id_banco");	
        $var_mensaje = 'Conciliado: Suma Teorico y Concliado';
    }
    // ACTUALIZANDO BANCOS SUCURSALES
    if({transac[0]['status']}=='ANULADO'){
        sc_exec_sql("update bancos_sucursales set saldo_conciliado = saldo_conciliado - $var_monto_conciliado, saldo_teorico = saldo_teorico - $var_monto_teorico  where sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");	
        $var_mensaje = 'Tranasaccion ANULADA';
    }else{// PROCESADO
        sc_exec_sql("update bancos_sucursales set saldo_conciliado = saldo_conciliado + $var_monto_conciliado, saldo_teorico = saldo_teorico + 	$var_monto_teorico  where sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");		
        $var_mensaje = 'Conciliado: Suma Teorico y Concliado';
    }	
    }else{ // NO CONCILIADO
    echo 'Venta NO Conciliada'; 						

    // ACTUALIZANDO BANCOS 
    if({transac[0]['status']}=='ANULADO'){
        sc_exec_sql("update bancos set saldo_teorico = saldo_teorico - $var_monto_teorico  where id = $id_banco");
        $var_mensaje = 'Tranasaccion ANULADA ';
    }else{// PROCESADO
        sc_exec_sql("update bancos set saldo_teorico = saldo_teorico + $var_monto_teorico  where id = $id_banco");	
        $var_mensaje = 'NO Conciliado: Suma Teorico';
    }
    // ACTUALIZANDO BANCOS SUCURSALES
    if({transac[0]['status']}=='ANULADO'){
        sc_exec_sql("update bancos_sucursales set saldo_teorico = saldo_teorico - $var_monto_teorico  where  sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");				
        $var_mensaje = 'Tranasaccion ANULADA';
    }else{// PROCESADO
        sc_exec_sql("update bancos_sucursales set saldo_teorico = saldo_teorico + $var_monto_teorico  where sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");				
    	$var_mensaje = 'NO Conciliado: Suma Teorico';
    }
    }

    }

    elseif($var_origen == 'COMPRA'){
    if($var_conciliado == 'SI'){ // CONCILIADO
    // ACTUALIZANDO BANCOS
    if({transac[0]['status']} == 'ANULADO'){
        sc_exec_sql("UPDATE bancos SET saldo_conciliado = saldo_conciliado + $var_monto_conciliado, saldo_teorico = saldo_teorico + $var_monto_teorico WHERE id = $id_banco");
        $var_mensaje = 'Transacción ANULADA';
    } else { // PROCESADO
        sc_exec_sql("UPDATE bancos SET saldo_conciliado = saldo_conciliado - $var_monto_conciliado, saldo_teorico = saldo_teorico - $var_monto_teorico WHERE id = $id_banco");
        $var_mensaje = 'Conciliado: Resta Teórico y Conciliado';
    }
    // ACTUALIZANDO BANCOS SUCURSALES
    if({transac[0]['status']} == 'ANULADO'){
        sc_exec_sql("UPDATE bancos_sucursales SET saldo_conciliado = saldo_conciliado + $var_monto_conciliado, saldo_teorico = saldo_teorico + $var_monto_teorico WHERE  sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");
        $var_mensaje = 'Transacción ANULADA';
    } else { // PROCESADO
        sc_exec_sql("UPDATE bancos_sucursales SET saldo_conciliado = saldo_conciliado - $var_monto_conciliado, saldo_teorico = saldo_teorico - $var_monto_teorico WHERE sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");
        $var_mensaje = 'Conciliado: Resta Teórico y Conciliado';
    }
    } else { // NO CONCILIADO
    // ACTUALIZANDO BANCOS
    if({transac[0]['status']} == 'ANULADO'){
        sc_exec_sql("UPDATE bancos SET saldo_teorico = saldo_teorico + $var_monto_teorico WHERE id = $id_banco");
        $var_mensaje = 'Transacción ANULADA';
    } else { // PROCESADO
        sc_exec_sql("UPDATE bancos SET saldo_teorico = saldo_teorico - $var_monto_teorico WHERE id = $id_banco");
        $var_mensaje = 'NO Conciliado: Resta Teórico';
    }
    // ACTUALIZANDO BANCOS SUCURSALES
    if({transac[0]['status']} == 'ANULADO'){
        sc_exec_sql("UPDATE bancos_sucursales SET saldo_teorico = saldo_teorico + $var_monto_teorico WHERE sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");
        $var_mensaje = 'Transacción ANULADA';
    } else { // PROCESADO
        sc_exec_sql("UPDATE bancos_sucursales SET saldo_teorico = saldo_teorico - $var_monto_teorico WHERE  sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");
        $var_mensaje = 'NO Conciliado: Resta Teórico';
    }
    }
    }

    elseif($var_origen == 'BANCO'){
		echo 'Estoy en el banco'.'<br>';
        // ACTUALIZANDO BANCOS
		if({transac[0]['tipo']} == 'CREDITO'){
			echo 'Es un CREDITO'.'<br>';
			if({transac[0]['status']} == 'ANULADO'){
				sc_exec_sql("UPDATE bancos SET saldo_conciliado = saldo_conciliado - $var_monto_conciliado, saldo_teorico = saldo_teorico - $var_monto_teorico WHERE id = $id_banco");
				$var_mensaje = 'Transacción ANULADA';
			} else { // PROCESADO
				sc_exec_sql("UPDATE bancos SET saldo_conciliado = saldo_conciliado + $var_monto_conciliado, saldo_teorico = saldo_teorico + $var_monto_teorico WHERE id = $id_banco");
				$var_mensaje = 'Conciliado: Resta Teórico y Conciliado';
			}
			// ACTUALIZANDO BANCOS SUCURSALES
			if({transac[0]['status']} == 'ANULADO'){
				sc_exec_sql("UPDATE bancos_sucursales SET saldo_conciliado = saldo_conciliado - $var_monto_conciliado, saldo_teorico = saldo_teorico - $var_monto_teorico WHERE  sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");
				$var_mensaje = 'Transacción ANULADA';
			} else { // PROCESADO
				sc_exec_sql("UPDATE bancos_sucursales SET saldo_conciliado = saldo_conciliado + $var_monto_conciliado, saldo_teorico = saldo_teorico + $var_monto_teorico WHERE sucursal = '$sucu' AND empresa = '$empe' AND moneda = '$cod_moneda'");
				$var_mensaje = 'Conciliado: Resta Teórico y Conciliado';
			} 
        }else{//DEBITO
			echo 'Es un DEBITO'.'<br>';
			
		}
	}// Cerrando Banco
  /*  sc_exec_sql("UPDATE cola_transacciones_bancos 
        SET mensaje = '$var_mensaje', status = 'PROCESADO', fecha_procesado = CURDATE() 
        WHERE id_transaccion_banco = id_transaccion_banco AND status = 'PENDIENTE'");*/
//    }
    //}
    $my_data->MoveNext();



    }
    $my_data->Close();
    }


?>