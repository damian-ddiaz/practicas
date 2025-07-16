<?php
//__NM__Acesso ao banco de dados.__NM__FUNCTION__NM__//
	/*teste*/
function lw_lookup($sql, $conn = false) {
	if ($conn) {
		// sc_select(ds, $sql, $conn);
	} else {
		sc_select(ds, $sql);
	}
	$res = lw_select({ds});
	return $res;
}

function lw_select( $pdo ) {
	$arrSelect = array();
	if($pdo) {
		while ( ! $pdo -> EOF ) {
			$arr = $pdo -> fields;

			$campos = array_filter( $arr, function( $k ) {
				return ( ! is_int( $k ) );
			}, ARRAY_FILTER_USE_KEY );

			$arrSelect[] = $campos;
			$pdo -> MoveNext();
		}
	}
	return $arrSelect;
}

function getConfigs($where = 1) {
	sc_select(ds, "SELECT cfgchave chave, cfgvalor valor FROM configs WHERE $where");
	$lw = lw_select({ds});
	$configs = array_column($lw, 'valor', 'chave');
	return $configs;
}

function lwprint($var, $rotulo = '') {
	echo "<hr>$rotulo<pre>";print_r($var);echo '</pre>';
}

function lwdump($var, $rotulo = '') {
	echo "<hr>$rotulo<pre>";var_dump($var);echo '</pre>';
}
?>