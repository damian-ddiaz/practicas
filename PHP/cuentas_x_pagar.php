<?php
	function abonoPendiente($idProv){
		sc_lookup(rs, "SELECT * FROM cxp_documentos WHERE id_proveedor = $idProv AND tipo_documento IN ('ADELC','NOTCREC') AND saldo > 0");

		if (isset({rs[0][0]})){
			return true;
		}
		else{
			return false;
		}


	}

	function generarPagoPendiente($arrDocs, $idProv, $tasa, $emp, $suc, $login){

		$ip = $_SERVER['REMOTE_ADDR'];
		// Insertar pago resumen
		$insert_table_resumen  = 'cxp_pago_resumen';      // Table name
		$insert_fields_resumen = array(   // Field list, add as many as needed
			'numero_documento' => "''", 
			'cod_proveedor' => "''",
			'id_proveedor' => "$idProv",
			'fecha_emision' => "NOW()",
			'descripcion' => "'Pago de Factura'",
			'estatus' => "'PENDIENTE'",
			'tipo' => "'MANUAL'",
			'saldo' => "0.00",
			'saldo_bs' => "0.00",
			'tasa_cambio' => "$tasa",
			'fecha' => "NOW()",
			'empresa' => "'$emp'",
			'sucursal' => "'$suc'",
			'usuario' => "'$login'",
			'ip_estacion' => "'$ip'"
		);

		$insert_sql_resumen = 'INSERT INTO ' . $insert_table_resumen
			. ' ('   . implode(', ', array_keys($insert_fields_resumen))   . ')'
			. ' VALUES ('    . implode(', ', array_values($insert_fields_resumen)) . ')';

		sc_exec_sql($insert_sql_resumen);
		sc_commit_trans();
		sc_lookup(sc, "SELECT LAST_INSERT_ID()");

		$idResumen = $sc[0][0]; // Correcto acceso al valor

		$descPago = 'Pago de factura: ';
		

		// Combinar los elementos de 'fact' y 'abono' en un solo array para recorrerlos
		$items = array_merge($arrDocs['fact'] ?? [], $arrDocs['abonos'] ?? []);

		foreach ($items as $item) {
			// Acceder a cada elemento
			$idFact = $item['id'];
			$montoFact = $item['monto'];

			// Consultar detalles del documento
			sc_lookup(rs, "SELECT id_cxp_documentos, cod_proveedor, tipo_documento, numero_documento, total_neto, $montoFact, tasa_cambio, $montoFact*tasa_cambio, descripcion, nro_fiscal FROM cxp_documentos WHERE id_cxp_documentos = $idFact");

			if (isset($rs[0][0])) {
				// Extraer y asignar valores
				$idCxp = $rs[0][0];
				$codProv = $rs[0][1];
				$tipoDocumento = $rs[0][2];
				$numeroDocumento = $rs[0][3];
				$totalNeto = $rs[0][4];
				$saldoDoc = $rs[0][5];
				$tasaDoc = $rs[0][6];
				$saldoBsDoc = $rs[0][7];
				$descripcionDoc = $rs[0][8];
				$nroFiscal = $rs[0][9];

				// Insertar pago detalles
				$insert_table_detalle  = 'cxp_pago_detalles';
				$insert_fields_detalle = array(
					'id_cxp_pago_resumen' => "$idResumen",
					'id_cxp_documento' => "$idCxp",
					'cod_proveedor' => "'$codProv'",
					'tipo_documento' => "'$tipoDocumento'",
					'numero_documento' => "'$numeroDocumento'",
					'estatus' => "'PENDIENTE'",
					'total_neto' => "$totalNeto",
					'saldo' => "0",
					'tasa' => "$tasaDoc",
					'monto' => "$saldoDoc",
					'fecha' => "NOW()",
					'empresa' => "'$emp'",
					'sucursal' => "'$suc'",
					'usuario' => "'$login'",
					'monto_bs' => "$saldoBsDoc",
					'saldo_bs' => '0',
					'descripcion' => "'$descripcionDoc'"
				);

				$insert_sql_detalle = 'INSERT INTO ' . $insert_table_detalle
					. ' ('   . implode(', ', array_keys($insert_fields_detalle))   . ')'
					. ' VALUES ('    . implode(', ', array_values($insert_fields_detalle)) . ')';

				sc_exec_sql($insert_sql_detalle);

				$descPago .= "$nroFiscal ";
			}
		}

		

		sc_lookup(rsResumen, "SELECT COALESCE(
			SUM(
				CASE 
					WHEN tipoDocumento(tipo_documento, empresa, sucursal) <> 'CREDITO' 
					THEN monto 
					ELSE monto * -1 
				END
			), 
			0.00
		) AS total
		FROM cxp_pago_detalles
		WHERE id_cxp_pago_resumen = $idResumen");
		$saldoResumen = $rsResumen[0][0]; // Correcto acceso al valor

		sc_exec_sql("UPDATE cxp_pago_resumen SET descripcion = '$descPago', cod_proveedor = '$codProv', saldo = $saldoResumen, saldo_bs = $saldoResumen*$tasa WHERE id_cxp_pago_resumen = '$idResumen'");
		sc_commit_trans();
		return $idResumen;
		
	
	}




?>