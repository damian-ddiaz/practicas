<?php
	function buscar_correlativo_retencion($tipo_documento){	
		//consultar correlativo compartido
		$check_sql = "select sucursal_principal from vSucursal_principal where empresa = '[usr_empresa]' and sucursal_origen = '[usr_sucursal]'";
		sc_lookup(rs, $check_sql,'conn_example');
		if (isset({rs[0][0]})){
			$sucu = {rs[0][0]};
		}
		else{
			$sucu = [usr_sucursal];
		}
	
		$sql_buscar_correlativo = "SELECT valor 
		FROM configuracion_correlativos 
		WHERE  (tipo_documento = '".$tipo_documento."') AND (empresa = '".[usr_empresa]."') AND (sucursal = '".$sucu."')";	
		
		sc_lookup(ds_buscar_correlativo, $sql_buscar_correlativo,'conn_example');
		if (!empty ({ds_buscar_correlativo})){
			$correlativo = {ds_buscar_correlativo[0][0]};
			return $correlativo;
		}
		else{
			/*no se encontro resultado*/
			sc_alert("El documento no tiene correlativos .  $ds_buscar_correlativo_erro ");
		}
	}	
	
/*esta es la funciona a utilizar cuando se genere un nuevo numero de correlativo este automaticamente usara la funcion anterior para saber sl correlativo anterior*/
function suma_correlativo_retencion($tipo_documento){
	//consultar correlativo compartido
	$check_sql = "select sucursal_principal from vSucursal_principal where empresa = '[usr_empresa]' and sucursal_origen = '[usr_sucursal]'";
	sc_lookup(rs, $check_sql,'conn_example');

	if (isset({rs[0][0]})){
		$sucu = {rs[0][0]};
	}
	else{
		$sucu = [usr_sucursal];
	}
	/*Buscar correlativos por el tipo de documento y lo almacena en $correlativo*/
	$correlativo = buscar_correlativo_retencion($tipo_documento);
	/*suma +1 el correlativo*/
	$correlativo++;
	/*actualiza el correlativo */
	$correlativo_nuevo = str_pad($correlativo, "10", "0", STR_PAD_LEFT);
	
	$sql_update_suma_correlativo = "UPDATE configuracion_correlativos
		 SET valor = '".$correlativo_nuevo."'
		 WHERE (tipo_documento = '".$tipo_documento."') AND (empresa = '".[usr_empresa]."') AND 				(sucursal = '".$sucu."')";			
	sc_exec_sql ($sql_update_suma_correlativo,'conn_example');	
}
	
?>