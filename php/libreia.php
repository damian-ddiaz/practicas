<?php
function buscar_correlativo($tipo_documento){
	//consultar correlativo compartido
	$check_sql = "SELECT sucursal_dependencia 
	FROM configuracion_sucursal 
	WHERE tipo_empresa = '[usr_empresa]' AND codigo = '[usr_sucursal]' AND dependencia = 'SI'";
	sc_lookup(rs, $check_sql,'conn_example');

	if (isset({rs[0][0]})){
		$sucu = {rs[0][0]};
	}
	else{
		$sucu = [usr_sucursal];
	}
	/*Buscar correlativos por el tipo de documento*/
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
?>