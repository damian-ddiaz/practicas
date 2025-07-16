pdf->MultiCell(140, $font_atl, utf8_decode(substr({rs[0]['direccion']},0,81)), 0,'L');

if(substr({rs[0]['direccion']},82,83) <>''){
	$line_ini = $line_ini + $line_salto;
	$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y
	$pdf->MultiCell(80, $font_atl, '-'.utf8_decode(substr({rs[0]['direccion']},81,150)), 0,'L');
}
