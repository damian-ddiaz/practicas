<?php
sc_include_library("sys", "fpdf", "fpdf.php", true, true);
$nombre_reporte='Listado de Postulaciones Aprobadas';

function fechaEs($fecha) {
	$fecha = substr($fecha, 0, 10);
	$numeroDia = date('d', strtotime($fecha));
	$dia = date('l', strtotime($fecha));
	$mes = date('F', strtotime($fecha));
	$ano = date('Y', strtotime($fecha));
	$dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
	$dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
	$nombredia = str_replace($dias_EN, $dias_ES, $dia);
	$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
	$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	$nombreMes = str_replace($meses_EN, $meses_ES, $mes);
	return $nombreMes;
}

$id_emp = 1;
$sql_ins ="select
  nombre_institucion,
  direccion,
  coordinador,
  telefonos
from
  configuracion_insitucion where id = $id_emp";
sc_lookup_field(rs_ins,$sql_ins);

$sql ="SELECT
	codigo_proyecto,
	nombre, 
	dirigida,
	cantidad_maxima,
	status,
	desde,
	hasta,
	fecha_aprobacion,
	fecha_culminacion
FROM
	proyectos 
WHERE
	codigo_proyecto = '[par_codigo_proyecto]'";
sc_lookup_field(rs,$sql); 

// Crear un nuevo objeto PDF
$pdf = new FPDF('P', 'mm', 'Letter');
// Agregar una página al PDF
$pdf->AddPage();

// Agregando el Membrete de la institucion 
$imgArr = logo_emp($id_emp);
if(!empty($imgArr['logo'])){
	// Mostrar la imagen en el PDF
	$pdf->Image($imgArr['logo'], 5, 3, 60, 25);
}
// Eliminar el archivo temporal
unlink($imgArr['logo']);

if(!empty($imgArr['firma'])){
	// Mostrar la imagen en el PDF
	$pdf->Image($imgArr['firma'], 65, 210, 80, 40);
}
// Eliminar el archivo temporal
unlink($imgArr['firma']);

if(!empty($imgArr['sello'])){
	// Mostrar la imagen en el PDF
	$pdf->Image($imgArr['sello'], 110, 210, 80, 40);
}
// Eliminar el archivo temporal
unlink($imgArr['sello']);

$font_nom = 'Arial';
$font_tam = 9;
$font_atl= 4;
$line_ini= 9;
$line_salto = 3;
$pdf->SetFont($font_nom, 'B', 10);

$fecha_desde = {rs[0]['desde']};
$fecha_des = DateTime::createFromFormat('Y-m-d', trim($fecha_desde));
$fecha_desde_formateada = $fecha_des->format('d-m-Y');

$fecha_hasta = {rs[0]['hasta']};
$fecha_has = DateTime::createFromFormat('Y-m-d', trim($fecha_hasta));
$fecha_hasta_formateada = $fecha_has->format('d-m-Y');

$mes_desde = fechaEs($fecha_desde);
$ano_desde = date('Y', strtotime($fecha_desde));
$nombre = {rs[0]['nombre']};
$fecha_hasta = {rs[0]['hasta']};
$mes_hasta = fechaEs($fecha_hasta);
$ano_hasta = date('Y', strtotime($fecha_hasta));


$fecha_aprobacion = {rs[0]['fecha_aprobacion']};
$mes_aprobacion = fechaEs($fecha_aprobacion);
$ano_aprobacion = date('Y', strtotime($fecha_aprobacion));
$dia_aprobacion = date('d', strtotime($fecha_aprobacion));
	
$pdf->SetFont($font_nom, '', 9);
$colu_ini = 5;
$line_ini= 12;
$line_salto = 4;

$line_ini = $line_ini + $line_salto;
$pdf->SetXY(70, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(135,$font_atl, utf8_decode('Caracas, ' .utf8_decode($dia_aprobacion).' de ' .utf8_decode($mes_aprobacion).' de ' .utf8_decode($ano_aprobacion)), $font_atl,'R');

$line_salto = 15;
$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(250,$font_atl, utf8_decode('Señores:'), $font_atl,'L');

$line_salto = 5;

$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(50,$font_atl, utf8_decode({rs[0]['dirigida']}), 0,'L'); 	

$line_ini = $line_ini + $line_salto;
$pdf->SetXY($colu_ini, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(120,$font_atl, utf8_decode('Presente.-'), $font_atl,'L');

$line_salto = 6;
$pdf->SetFont($font_nom, 'B', 10);
$line_ini = $line_ini + $line_salto;

$pdf->SetXY(20, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(180,$font_atl, utf8_decode('POSTULACION NRO. ').utf8_decode({rs[0]['codigo_proyecto']}), $font_atl,'C'); 

$line_salto = 10;
$pdf->SetFont($font_nom, '', 10);
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(220,$font_atl, utf8_decode('			Por medio del presente, me dirijo a usted, en la oportunidad de postular a los Ing. en Formación que se mencionan a continuación:'), $font_atl,'L'); 

$font_tam = 8;
$line_salto = 10;
$line_ini = $line_ini + $line_salto;
$pdf->SetFont($font_nom, 'B', $font_tam);
$pdf->SetFillColor(146, 140, 139);
$pdf->SetXY(5,$line_ini);
$pdf->MultiCell(25,4, utf8_decode('Nro.'."\n".' '), 1,'C',true); 
$pdf->SetXY(30,$line_ini);
$pdf->SetFont($font_nom, 'B', $font_tam);
$pdf->MultiCell(120,4, utf8_decode('Nombre y Apellidos'."\n".' '), 1,'C',true);
$pdf->SetXY(150,$line_ini);
$pdf->MultiCell(60,4, utf8_decode('C.I. Nro.'."\n".' '), 1,'C',true);

// Leyendo Detalle
$sql_det ="SELECT
    ROW_NUMBER() OVER (ORDER BY par.codigo_proyecto) AS registro,
	par.codigo_proyecto,
	par.login,
	su.NAME AS particpante,
	su.email,
	pro.STATUS 
FROM
	postulaciones par
	INNER JOIN sec_users su ON su.login = par.login
	INNER JOIN proyectos pro ON pro.codigo_proyecto = par.codigo_proyecto 
WHERE
	par.codigo_proyecto = '[par_codigo_proyecto]'
	";
sc_lookup_field(rs_det, $sql_det); 

$font_tam = 9;
$font_atl= 5;
$line_ini = $line_ini + 8; // Salto de Bloque
$pdf->SetXY(5, $line_ini);
$pdf->SetFont($font_nom, '', $font_tam);
if (is_array($rs_det) && count($rs) > 0) {//Verificar si se 
	foreach ($rs_det as $row) {		
		$pdf->SetXY(5, $line_ini);
		$pdf->Cell(25, $font_atl, $row['registro'], 0,0,'C');
		$pdf->Cell(120, $font_atl, $row['particpante'], 0,0,'L');
		$pdf->Cell(60, $font_atl, $row['login'], 0,0,'L');
		$line_ini = $line_ini + 5; // Salto de Bloque	
	}	
}
$line_ini = 119;
$pdf->SetFont($font_nom, '', 10);
// $line_ini = $line_ini + $line_salto;
$pdf->SetXY(5, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(205,$font_atl, utf8_decode('			Quienes son estudiantes del Programa Nacional de Formación en Informática 
y Telecomunicaciones, de esta casa de estudio y tienen previsto realizar el Proyecto de Servicio Comunitario en la institución 
que usted dirige, ya que están cursando: PROYECTO SERVICIO COMUNITARIO.  Dicho proyecto se denomina: '.utf8_decode($nombre). ', 
PERIODO '.utf8_decode($fecha_desde_formateada).' - '.utf8_decode($fecha_hasta_formateada).' y es atutorizado por la Profesora 
Maribel Moreno, quien es miembro del personal docente de la Universidad Nacional Experimental de las Telecomunicaciones e 
Informática (UNETI).
Al respecto, agradecemos la posibilidad de acceso y permanencia controlada en dichas instalaciones durante el desarrollo de sus 
actividades con fines netamente académico para la elaboración de un proyecto de bienestar social de la comunidad en el área de:  
conocimientos científicos, técnicos, culturales, deportivos o humanísticos. El   período  de  realización  de  dichas   
actividades   será  comprendido   a   partir del '.utf8_decode($fecha_desde_formateada).' hasta el '.utf8_decode($fecha_hasta_formateada).',
 con una duración de 12 semanas (120 horas), de acuerdo al horario que establezca la empresa.
Entre las actividades y compromisos que debe asumir el estudiante son:
    - Cumplir con el horario, previamente acordado con la institución o comunidad.
    - Cumplir con las normas y procedimientos de la institución o comunidad beneficiada con el proyecto.
    - Desarrollar planes, para la realización del Proyecto Servicio Comunitario.
    - Utilizar el Formato de Acompañamiento del Proyecto.'), $font_atl,'J'); 

$line_salto = 85;
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(20, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(220,$font_atl, utf8_decode('			Sin otro particular a que hacer referencia, se despide de usted.'), $font_atl,'L'); 

$line_salto = 15;
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(0, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(220,$font_atl, utf8_decode('Atentamente,'), $font_atl,'C'); 

$line_salto = 20;
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(60, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(90, $font_atl,utf8_decode({rs_ins[0]['coordinador']}), 0,'C');

$line_salto = 5;
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(45, $line_ini);//Coordenadas X/Y 
$pdf->MultiCell(130, $font_atl,utf8_decode('Coordinación de Servicio Comunitario'), 0,'C');

$line_salto = 5;
$line_ini = $line_ini + $line_salto;
$pdf->SetXY(45, $line_ini);//Coordenadas X/Y 
$pdf->SetFont($font_nom, 'B', 10);
$pdf->MultiCell(130, $font_atl,utf8_decode('Universidad Nacional Experimental
de las Telecomunicaciones e informática'), 0,'C');


// Generar el archivo PDF
$pdf->Output($nombre_reporte, 'I');
?>
