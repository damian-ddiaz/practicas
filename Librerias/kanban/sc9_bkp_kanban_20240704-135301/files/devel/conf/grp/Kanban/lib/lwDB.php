<?php
//__NM____NM__FUNCTION__NM__//
	
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

function lwprint($var, $label = '') {
	echo "<hr>$label<pre>";print_r($var);echo '</pre>';
}

function lwdump($var, $label = '') {
	echo "<hr>$label<pre>";var_dump($var);echo '</pre>';
}
?>