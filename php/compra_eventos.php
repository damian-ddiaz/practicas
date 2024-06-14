<?php
	/* Evento ONoload */
	sc_field_readonly({id_proveedor});

	sc_btn_display("credito", "off");
	/*
	?>
	<style>
	#hidden_bloco_2{
		height: 72px !important;
	}
	</style>
	<?php
	*/

	if(empty({tasa_cambio})){
		{tasa_cambio} = [tasa_de_cambio];
		}

	if(empty({estado})){
		{estado} = "EN ESPERA";
	}
	sc_field_readonly({estado}, on);


	$rs = buscar_renglones({id_compra});
	$res = buscar_renglones_pagos({id_compra});

	if($rs == 'SI' AND $res == 'SI'){
		sc_btn_display("Totalizar","on");
	}
	else{
		sc_btn_display("Totalizar","off");
	}

	if($rs == 'SI' and {saldo} == {total}){
		sc_btn_display("credito", "on");
	}
	if({estado} == 'EN INVENTARIO' OR {estado} == 'ANULADO'){
		sc_btn_display("credito", "off");
	}


	if({estado} == 'EN INVENTARIO' OR {estado} == 'ANULADO' OR {saldo} <> 0.00 AND !empty({saldo})){
		
		sc_btn_display("Totalizar", "off");
		sc_field_readonly({cod_almacen}, on);
		sc_field_readonly({cod_ubicacion}, on);
		sc_field_readonly({cod_nivel}, on);
		
		sc_field_readonly({moneda}, on);
		sc_field_readonly({tasa_cambio}, on);
		sc_field_readonly({numero}, on);
		
		sc_field_readonly({numero_factura}, on);
		sc_field_readonly({numero_control}, on);
		sc_field_readonly({codigo_proveedor}, on);
		sc_field_readonly({fecha_factura}, on);
		sc_field_readonly({fecha_vencimiento}, on);
		sc_field_readonly({descripcion}, on);
		sc_field_readonly({descuento}, on);
		
		sc_btn_display("update","off");
		sc_btn_display("delete","off");
		//sc_btn_display("Pagos","off");
		sc_btn_display("anular","off");
		/*
		?>
		<style>
			#cap_codigo_proveedor{
				display:none !important;
			}
		</style>
		<?php
		*/
	}
	elseif({estado} == 'EN ESPERA' AND $rs == 'SI' AND $res == 'SI'){
		
		sc_btn_display("Totalizar","on");
		sc_field_readonly({cod_almacen}, off);
		sc_field_readonly({cod_ubicacion}, off);
		sc_field_readonly({cod_nivel}, off);
		
		sc_field_readonly({moneda}, off);
		sc_field_readonly({tasa_cambio}, off);
		sc_field_readonly({numero}, off);
		
		sc_field_readonly({numero_factura}, off);
		sc_field_readonly({numero_control}, off);
		sc_field_readonly({codigo_proveedor}, off);
		
		sc_field_readonly({fecha_factura}, off);
		sc_field_readonly({fecha_vencimiento}, off);
		sc_field_readonly({descripcion}, off);
		sc_field_readonly({descuento}, off);	
	}

	if({estado} == 'EN INVENTARIO'){
		sc_btn_display("anular","on");
	}
	else{
		sc_btn_display("anular","off");
	}

	// validando que ninguno de los productos posea seriales //
	$id_comp={id_compra};
		sc_lookup (rs,"select * from movimientos_producto_seriales where id_resumen=$id_comp and empresa='[usr_empresa]' and sucursal ='[usr_sucursal]'");
		if(!isset({rs[0][0]})){
			sc_btn_display("imprimir_nuevo","off");
		}


	/* FIN Evento ONoload */

	/* Evento ONoload */
		if(sc_btn_delete){
			$rs = buscar_renglones({id_compra});
			$res = buscar_renglones_pagos({id_compra});
			if($rs == 'SI'){
			sc_error_message("La compra tiene productos asociados");
		}
		elseif($res == 'SI'){
			sc_error_message("La compra tiene pagos asociados");
		}
		}

	/* FIN Evento ONoload */

	/* Evento  onAfterUpdate */
	{estado} = 'FACTURADO';
	/* FIN Evento  onAfterUpdate */

	/* Evento   onLoadRecord */
	sc_field_readonly(tasa_cambio, on);
	/* FIN Evento  onLoadRecord */

?>