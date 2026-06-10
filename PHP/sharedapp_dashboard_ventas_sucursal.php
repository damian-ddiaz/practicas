$embed = isset($_GET['embed']) ? $_GET['embed'] : '0';
if ($embed != '1') { echo "<div style='padding:12px;font-family:Arial'>Usa ?embed=1</div>"; exit; }

// ================================
// CONFIG
// ================================

$tabla_ventas = "otros_clientes";
$tabla = "servicio_cliente";
$tabla_seguridad_users = "seguridad_users";
$colFechaVenta = "fecha_registro";
$colFechaVentaInstalacion = "fecha_instalacion";
$colMonto = "costo_instalacion";
$TOP_TIPOS = (int)6;

// ✅ FILTRO GLOBAL POR VENDEDOR
$VENDEDOR_FIJO = "[usr_login]";
$CODIGO_VENDEDOR = "[usr_login]";

/* ORIGINAL 
$whereVend = "TRIM(IFNULL(vendedor,'')) = '" . addslashes($VENDEDOR_FIJO) . "' 
              AND empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'";

$whereCodigoVend = "TRIM(IFNULL(codigo_vendedor,'')) = '" . addslashes($CODIGO_VENDEDOR) . "' 
                    AND empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'";
*/

// NUEVO
$whereVend = "empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'";
$whereCodigoVend = "empresa = '[usr_empresa]' and sucursal = '[usr_sucursal]'";


// ================================
// KPIs: Hoy / Mes (Cantidad + Monto)
// ================================
$kpi = array('ventas_hoy'=>0,'ventas_mes'=>0,'ticket'=>0,'clientes'=>0,'monto_hoy'=>0,'monto_mes'=>0);

$sqlKpiHoy = "
  SELECT COUNT(*) AS cant, IFNULL(SUM(IFNULL($colMonto,0)),0) AS monto
  FROM $tabla_ventas
  WHERE $whereCodigoVend
    AND DATE($colFechaVentaInstalacion) = CURDATE()
";
sc_lookup(rk1, $sqlKpiHoy);
if(isset({rk1[0][0]})){
  $kpi['ventas_hoy'] = (int){rk1[0][0]};
  $kpi['monto_hoy']  = (float){rk1[0][1]};
}

$sqlKpiMes = "
  SELECT COUNT(*) AS cant, IFNULL(SUM(IFNULL($colMonto,0)),0) AS monto
  FROM $tabla_ventas
  WHERE $whereCodigoVend
    AND YEAR($colFechaVentaInstalacion)=YEAR(CURDATE())
    AND MONTH($colFechaVentaInstalacion)=MONTH(CURDATE())
	AND estatus = 3
";
sc_lookup(rk2, $sqlKpiMes);
if(isset({rk2[0][0]})){
  $kpi['ventas_mes'] = (int){rk2[0][0]};
  $kpi['monto_mes']  = (float){rk2[0][1]};
}
$kpi['ticket'] = ($kpi['ventas_mes'] > 0) ? ($kpi['monto_mes'] / $kpi['ventas_mes']) : 0;

$sqlClientes = "
  SELECT COUNT(DISTINCT id_cliente)
  FROM $tabla
  WHERE $whereVend
    AND YEAR($colFechaVenta)=YEAR(CURDATE())
    AND MONTH($colFechaVenta)=MONTH(CURDATE())
";
sc_lookup(rk3, $sqlClientes);
if(isset({rk3[0][0]})) $kpi['clientes'] = (int){rk3[0][0]};


// ================================
// 1) ESTADO (Activo/Inactivo/Retirado) - HISTÓRICO DESDE SIEMPRE
// ================================
$vendActivo = array(0);
$vendInact  = array(0);
$vendRet    = array(0);
$vendOtros  = array(0);
$vendTable  = array();

$sqlVend = "
	SELECT 
		SUM(CASE WHEN TRIM(IFNULL(ts.estado,'')) = 'Activo' THEN 1 ELSE 0 END) AS activo, 
		SUM(CASE WHEN TRIM(IFNULL(ts.estado,'')) = 'Inactivo' THEN 1 ELSE 0 END) AS inactivo, 
		SUM(CASE WHEN TRIM(IFNULL(ts.estado,'')) = 'Retirado' THEN 1 ELSE 0 END) AS retirado, 
		SUM(CASE WHEN TRIM(IFNULL(ts.estado,'')) IN ('Activo','Inactivo','Retirado') THEN 0 ELSE 1 END) AS otros, 
		tsu.name AS nombre_usuario 
	FROM 
		servicio_cliente ts 
	INNER JOIN 
		seguridad_users tsu ON tsu.login = ts.vendedor 
	WHERE empresa = '[usr_empresa]' AND sucursal = '[usr_sucursal]'
	GROUP BY 
		ts.vendedor, 
		tsu.name
	ORDER BY 
		nombre_usuario ASC
	";
sc_lookup(rvend, $sqlVend);

// Inicializamos los arreglos para acumular los totales que usará la gráfica de torta global
$vendActivo = array(0);
$vendInact  = array(0);
$vendRet    = array(0);
$vendOtros  = array(0);

if (isset({rvend}) && is_array({rvend})) {
  foreach ({rvend} as $fila) {
    $a = (int)$fila[0];
    $b = (int)$fila[1];
    $c = (int)$fila[2];
    $d = (int)$fila[3];
    $e = $fila[4];

    // Acumulamos para los totales de la gráfica de torta
    $vendActivo[0] += $a;
    $vendInact[0]  += $b;
    $vendRet[0]    += $c;
    $vendOtros[0]  += $d;

    // Agregamos cada vendedor de forma independiente a la tabla
    $vendTable[] = array(
      'vendedor'=>$e, 'activo'=>$a, 'inactivo'=>$b, 'retirado'=>$c, 'otros'=>$d,
      'total'=>($a+$b+$c+$d)
    );
  }
}

if (empty($vendTable)) {
  $vendTable[] = array('vendedor'=>'Sin datos','activo'=>0,'inactivo'=>0,'retirado'=>0,'otros'=>0,'total'=>0);
}


// ================================
// 2) DRILLDOWN: AÑOS -> MESES (STACK tipo_servicio + LÍNEA TOTAL)
// ================================
$labelsMeses = array('Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');

// Top tipos histórico (para este vendedor)
$tiposTopAll = array();
$sqlTiposAll = "
  SELECT IFNULL(tipo_servicio,'SIN_TIPO') AS tp, COUNT(*) AS total
  FROM $tabla
  WHERE $whereVend
  GROUP BY tp
  ORDER BY total DESC
  LIMIT ".$TOP_TIPOS."
";
sc_lookup(rta, $sqlTiposAll);
if(isset({rta[0][0]})){
  for($i=0; $i<count({rta}); $i++) $tiposTopAll[] = {rta[$i][0]};
}
if(!count($tiposTopAll)) $tiposTopAll = array('SIN_TIPO');

// Años + total por año
$labelsYears = array();
$yearsTotal  = array(); // total general por año

$sqlYears = "
  SELECT YEAR($colFechaVenta) AS y, COUNT(*) AS cant
  FROM $tabla
  WHERE $whereVend
  GROUP BY y
  ORDER BY y
";
sc_lookup(ryears, $sqlYears);
if(isset({ryears[0][0]})){
  for($i=0; $i<count({ryears}); $i++){
    $y = (string){ryears[$i][0]};
    $c = (int){ryears[$i][1]};
    $labelsYears[] = $y;
    $yearsTotal[]  = $c;
  }
}

// Series por tipo en AÑOS
$mapYearTipo = array(); // [tipo] => [por año index]
foreach($tiposTopAll as $tp) $mapYearTipo[$tp] = array_fill(0, count($labelsYears), 0);
$otrosYear = array_fill(0, count($labelsYears), 0);

// Util: índice de año
$idxYear = array();
for($i=0;$i<count($labelsYears);$i++) $idxYear[$labelsYears[$i]] = $i;

// Query año-tipo
$sqlYearTipo = "
  SELECT YEAR($colFechaVenta) AS y, IFNULL(tipo_servicio,'SIN_TIPO') AS tp, COUNT(*) AS cant
  FROM $tabla
  WHERE $whereVend
  GROUP BY y, tp
  ORDER BY y
";
sc_lookup(ryt, $sqlYearTipo);
$hayOtrosYear = false;
if(isset({ryt[0][0]})){
  for($i=0;$i<count({ryt});$i++){
    $y  = (string){ryt[$i][0]};
    $tp = {ryt[$i][1]};
    $c  = (int){ryt[$i][2]};
    if(!isset($idxYear[$y])) continue;
    $pos = $idxYear[$y];

    if(in_array($tp, $tiposTopAll)){
      $mapYearTipo[$tp][$pos] = $c;
    } else {
      $otrosYear[$pos] += $c;
      $hayOtrosYear = true;
    }
  }
}

// Empaquetar datasets Año
$tipoYearLabels = array();
$tipoYearSeries = array();
foreach($tiposTopAll as $tp){
  $tipoYearLabels[] = $tp;
  $tipoYearSeries[] = $mapYearTipo[$tp];
}
if($hayOtrosYear){
  $tipoYearLabels[] = 'OTROS';
  $tipoYearSeries[] = $otrosYear;
}

// Meses por año y tipo: mapYearMonthTipo[year][tipo] => [12]
$mapYearMonthTipo = array();
$mapYearMonthTotal = array();
foreach($labelsYears as $y){
  $mapYearMonthTipo[$y] = array();
  foreach($tiposTopAll as $tp) $mapYearMonthTipo[$y][$tp] = array_fill(0,12,0);
  $mapYearMonthTipo[$y]['OTROS'] = array_fill(0,12,0);
  $mapYearMonthTotal[$y] = array_fill(0,12,0);
}

$sqlYearMonthTipo = "
  SELECT YEAR($colFechaVenta) AS y, MONTH($colFechaVenta) AS m, IFNULL(tipo_servicio,'SIN_TIPO') AS tp, COUNT(*) AS cant
  FROM $tabla
  WHERE $whereVend
  GROUP BY y, m, tp
  ORDER BY y, m
";
sc_lookup(rymt, $sqlYearMonthTipo);
if(isset({rymt[0][0]})){
  for($i=0;$i<count({rymt});$i++){
    $y  = (string){rymt[$i][0]};
    $m  = (int){rymt[$i][1]};
    $tp = {rymt[$i][2]};
    $c  = (int){rymt[$i][3]};

    if(!isset($mapYearMonthTipo[$y])) continue;
    $mi = $m - 1;
    if($mi < 0 || $mi > 11) continue;

    $mapYearMonthTotal[$y][$mi] += $c;

    if(in_array($tp, $tiposTopAll)){
      $mapYearMonthTipo[$y][$tp][$mi] = $c;
    } else {
      $mapYearMonthTipo[$y]['OTROS'][$mi] += $c;
    }
  }
}


// ================================
// 3) GRAFICA MENSUAL (días del mes): Cantidad total + tipo_servicio - SOLO dcastro
// ================================
$anio = date('Y');
$mesN = date('m');
$primerDia = date('Y-m-01');
$ultimoDia = date('Y-m-t');
$diasMes = (int)date('t');

$labelsMes = array();
for($d=1;$d<=$diasMes;$d++) $labelsMes[] = str_pad($d,2,'0',STR_PAD_LEFT);

$mapDiaTotal = array();
for($d=1;$d<=$diasMes;$d++){
  $ds = str_pad($d,2,'0',STR_PAD_LEFT);
  $mapDiaTotal["$anio-$mesN-$ds"] = 0;
}

$sqlMesDia = "
  SELECT DATE($colFechaVenta) AS dia, COUNT(*) AS cant
  FROM $tabla
  WHERE $whereVend
    AND $colFechaVenta >= '".$primerDia."'
    AND $colFechaVenta <  DATE_ADD('".$ultimoDia."', INTERVAL 1 DAY)
  GROUP BY DATE($colFechaVenta)
";
sc_lookup(rmd, $sqlMesDia);
if(isset({rmd[0][0]})){
  for($i=0;$i<count({rmd});$i++){
    $dia = {rmd[$i][0]};
    $c   = (int){rmd[$i][1]};
    if(isset($mapDiaTotal[$dia])) $mapDiaTotal[$dia] = $c;
  }
}
$mesCant = array();
foreach($labelsMes as $ds) $mesCant[] = (int)$mapDiaTotal["$anio-$mesN-$ds"];

$tiposTopMes = array();
$sqlTiposMes = "
  SELECT IFNULL(tipo_servicio,'SIN_TIPO') AS tp, COUNT(*) AS total
  FROM $tabla
  WHERE $whereVend
    AND $colFechaVenta >= '".$primerDia."'
    AND $colFechaVenta <  DATE_ADD('".$ultimoDia."', INTERVAL 1 DAY)
  GROUP BY tp
  ORDER BY total DESC
  LIMIT ".$TOP_TIPOS."
";
sc_lookup(rtmm, $sqlTiposMes);
if(isset({rtmm[0][0]})){
  for($i=0;$i<count({rtmm});$i++) $tiposTopMes[] = {rtmm[$i][0]};
}
if(!count($tiposTopMes)) $tiposTopMes = array('SIN_TIPO');

$mapTipoDia = array();
foreach($tiposTopMes as $tp){
  $mapTipoDia[$tp] = array();
  foreach($labelsMes as $ds) $mapTipoDia[$tp]["$anio-$mesN-$ds"] = 0;
}
$otrosDia = array();
foreach($labelsMes as $ds) $otrosDia["$anio-$mesN-$ds"] = 0;

$sqlTipoDia = "
  SELECT DATE($colFechaVenta) AS dia, IFNULL(tipo_servicio,'SIN_TIPO') AS tp, COUNT(*) AS cant
  FROM $tabla
  WHERE $whereVend
    AND $colFechaVenta >= '".$primerDia."'
    AND $colFechaVenta <  DATE_ADD('".$ultimoDia."', INTERVAL 1 DAY)
  GROUP BY DATE($colFechaVenta), tp
";
sc_lookup(rtdm, $sqlTipoDia);
if(isset({rtdm[0][0]})){
  for($i=0;$i<count({rtdm});$i++){
    $dia = {rtdm[$i][0]};
    $tp  = {rtdm[$i][1]};
    $c   = (int){rtdm[$i][2]};
    if(in_array($tp,$tiposTopMes)){
      if(isset($mapTipoDia[$tp][$dia])) $mapTipoDia[$tp][$dia] = $c;
    }else{
      if(isset($otrosDia[$dia])) $otrosDia[$dia] += $c;
    }
  }
}

$tipoMesLabels = array();
$tipoMesSeries = array();
foreach($tiposTopMes as $tp){
  $tipoMesLabels[] = $tp;
  $serie = array();
  foreach($labelsMes as $ds){
    $serie[] = (int)$mapTipoDia[$tp]["$anio-$mesN-$ds"];
  }
  $tipoMesSeries[] = $serie;
}
$hayOtrosMes = false;
foreach($otrosDia as $x){ if((int)$x>0){ $hayOtrosMes=true; break; } }
if($hayOtrosMes){
  $tipoMesLabels[] = 'OTROS';
  $serie = array();
  foreach($labelsMes as $ds){
    $serie[] = (int)$otrosDia["$anio-$mesN-$ds"];
  }
  $tipoMesSeries[] = $serie;
}


// ================================
// IDs únicos
// ================================
$uid = uniqid("dash_");
$chVend = $uid."_vend";
$chYear = $uid."_year";
$chMonth= $uid."_month";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
  .cardx{ background:#fff; border:1px solid rgba(0,0,0,.06); border-radius:18px; box-shadow:0 14px 40px rgba(0,0,0,.08); }
  .chart-box{ height:320px; }
  .mini{ font-size:.85rem; color:#6c757d; }
</style>

<div class="row g-3">

  <!-- KPI -->
  <div class="col-12">
	<!-- ORIGINAL
    <div class="p-2 text-secondary small">
      Filtro aplicado: <b>vendedor = <?php /*echo htmlspecialchars($VENDEDOR_FIJO); */?></b>
    </div> -->
	
	 <!-- NUEVO -->
	<div class="p-2 text-secondary small">
      Filtro aplicado: <b>sucursal = <?php echo htmlspecialchars([usr_sucursal]); ?></b>
    </div>
	  
    <div class="row g-2">
      <div class="col-6 col-lg-3">
        <div class="p-3 border rounded-4 bg-light">
          <div class="text-secondary small">Ventas hoy</div>
          <div class="fs-5 fw-bold"><?php echo (int)$kpi['ventas_hoy']; ?></div>
          <div class="mini">Monto: <?php echo number_format($kpi['monto_hoy'],2,',','.'); ?></div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="p-3 border rounded-4 bg-light">
          <div class="text-secondary small">Ventas Concretadas (mes)</div>
          <div class="fs-5 fw-bold"><?php echo (int)$kpi['ventas_mes']; ?></div>
          <div class="mini">Monto: <?php echo number_format($kpi['monto_mes'],2,',','.'); ?></div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="p-3 border rounded-4 bg-light">
          <div class="text-secondary small">Venta promedio por cliente (mes)</div>
          <div class="fs-5 fw-bold"><?php echo number_format($kpi['ticket'],2,',','.'); ?></div>
		<div class="mini">Monto vendido/Cantidad (mes)</div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="p-3 border rounded-4 bg-light">
          <div class="text-secondary small">Clientes (mes)</div>
          <div class="fs-4 fw-bold"><?php echo (int)$kpi['clientes']; ?></div>
		  <div class="mini">Cantidad De Clientes captados en el mes</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Estado del vendedor (HISTÓRICO) - Torta -->
  <div class="col-12 col-xl-6">
    <div class="cardx p-3">
      <div class="fw-bold mb-2"><i class="bi bi-person-check me-2"></i>Estado servicios (histórico) - <?php echo htmlspecialchars([usr_sucursal]); ?></div>
      <div class="chart-box"><canvas id="<?php echo $chVend; ?>"></canvas></div>

	<div class="table-responsive mt-2 overflow-y-auto" style="max-height: 185;">
	  <table class="table table-sm align-middle mb-0">
		<thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
		  <tr><th>Vendedor</th><th>Activo</th><th>Inactivo</th><th>Retirado</th><th>Otros</th><th>Total</th></tr>
		</thead>
		<tbody>
		<?php 
		// Ahora el ciclo recorre TODOS los registros sin detenerse en 5
		foreach($vendTable as $r){ 
		?>
		  <tr>
			<td class="fw-semibold"><?php echo htmlspecialchars($r['vendedor']); ?></td>
			<td><?php echo (int)$r['activo']; ?></td>
			<td><?php echo (int)$r['inactivo']; ?></td>
			<td><?php echo (int)$r['retirado']; ?></td>
			<td><?php echo (int)$r['otros']; ?></td>
			<td class="fw-bold"><?php echo (int)$r['total']; ?></td>
		  </tr>
		<?php 
		} 
		?>
		</tbody>
	  </table>
	</div>

    </div>
  </div>

  <!-- DRILLDOWN Años -> Meses con tipo_servicio -->
	<div class="col-12 col-xl-6">
	  <div class="cardx p-3">
		<div class="d-flex align-items-center justify-content-between mb-2">
		  <div class="fw-bold">
			<i class="bi bi-calendar3 me-2"></i>
			<span id="<?php echo $uid; ?>_ttl">Ventas por Año: tipo_servicio</span>
		  </div>
		  <button id="<?php echo $uid; ?>_back" class="btn btn-sm btn-outline-secondary" style="display:none">
			<i class="bi bi-arrow-left"></i> Volver
		  </button>
		</div>

		  <div class="chart-box" style="height: 465px;"><canvas id="<?php echo $chYear; ?>"></canvas></div>
		<div class="mini mt-2" id="<?php echo $uid; ?>_hint">Click en un año para ver meses</div>
	  </div>
	</div>

  <!-- Mensual -->
  <div class="col-12">
    <div class="cardx p-3">
      <div class="fw-bold mb-2"><i class="bi bi-graph-up me-2"></i>Mensual (<?php echo date('m/Y'); ?>): cantidad + tipo_servicio</div>
      <div class="chart-box"><canvas id="<?php echo $chMonth; ?>"></canvas></div>
    </div>
  </div>

</div>

<script>
(function(){
  if(typeof Chart === 'undefined') return;

  var palette = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#fd7e14','#20c997'];

  // ==========================
  // ESTADO (TORTA) - HISTÓRICO (labels correctas)
  // ==========================
  (function(){
    var canvas = document.getElementById(<?php echo json_encode($chVend); ?>);
    if(!canvas) return;

    var labels = ['Activo','Inactivo','Retirado','Otros'];
    var data = [
      <?php echo (int)$vendActivo[0]; ?>,
      <?php echo (int)$vendInact[0]; ?>,
      <?php echo (int)$vendRet[0]; ?>,
      <?php echo (int)$vendOtros[0]; ?>
    ];

    var total = 0;
    for(var i=0;i<data.length;i++){ total += (+data[i]||0); }
    if(total <= 0) total = 1;

    var pieLabelsPlugin = {
      id: 'pieLabelsPlugin',
      afterDatasetsDraw: function(chart){
        var ctx = chart.ctx;
        var dataset = chart.data.datasets[0];
        var sum = 0;
        for(var i=0;i<dataset.data.length;i++){ sum += (+dataset.data[i]||0); }
        if(sum <= 0) sum = 1;

        var meta = chart.getDatasetMeta(0);

        ctx.save();
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        for(var j=0;j<meta.data.length;j++){
          var v = (+dataset.data[j]||0);
          if(v <= 0) continue;

          var p = (v/sum)*100;
          var pos = meta.data[j].tooltipPosition();
          var txt = String(v) + ' (' + p.toFixed(1) + '%)';

          ctx.lineWidth = 3;
          ctx.strokeStyle = 'rgba(0,0,0,.45)';
          ctx.strokeText(txt, pos.x, pos.y);
          ctx.fillStyle = '#ffffff';
          ctx.fillText(txt, pos.x, pos.y);
        }
        ctx.restore();
      }
    };

    new Chart(canvas,{
      type:'pie',
      data:{
        labels: labels,
        datasets:[{
          data: data,
          backgroundColor: [palette[1], palette[5], palette[4], palette[3]],
          borderWidth: 1
        }]
      },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
          legend:{
            position:'bottom',
            labels:{
              generateLabels: function(chart){
                var d = chart.data.datasets[0].data;
                var sum = 0;
                for(var i=0;i<d.length;i++){ sum += (+d[i]||0); }
                if(sum <= 0) sum = 1;

                var meta = chart.getDatasetMeta(0);

                var out = [];
                for(var k=0;k<chart.data.labels.length;k++){
                  var v = (+d[k]||0);
                  var p = (v/sum)*100;

                  var style = meta.controller.getStyle(k);
                  out.push({
                    text: chart.data.labels[k] + ': ' + v + ' (' + p.toFixed(1) + '%)',
                    fillStyle: style.backgroundColor,
                    strokeStyle: style.borderColor,
                    lineWidth: style.borderWidth,
                    hidden: isNaN(d[k]) || meta.data[k].hidden,
                    index: k
                  });
                }
                return out;
              }
            }
          },
          tooltip:{
            callbacks:{
              label: function(context){
                var v = (+context.parsed||0);
                var p = (v/total)*100;
                return ' ' + context.label + ': ' + v + ' (' + p.toFixed(1) + '%)';
              }
            }
          }
        }
      },
      plugins:[pieLabelsPlugin]
    });

  })();


  // ==========================
  // DRILLDOWN AÑOS -> MESES (STACK tipo_servicio + LINEA Total)
  // ==========================
  (function(){
    var canvas = document.getElementById(<?php echo json_encode($chYear); ?>);
    if(!canvas) return;

    var btnBack = document.getElementById(<?php echo json_encode($uid.'_back'); ?>);
    var ttl     = document.getElementById(<?php echo json_encode($uid.'_ttl'); ?>);
    var hint    = document.getElementById(<?php echo json_encode($uid.'_hint'); ?>);

    var labelsYears = <?php echo json_encode($labelsYears, JSON_UNESCAPED_UNICODE); ?>;
    var labelsMeses = <?php echo json_encode($labelsMeses, JSON_UNESCAPED_UNICODE); ?>;

    var tipoYearLabels = <?php echo json_encode($tipoYearLabels, JSON_UNESCAPED_UNICODE); ?>;
    var tipoYearSeries = <?php echo json_encode($tipoYearSeries); ?>;
    var yearsTotal     = <?php echo json_encode($yearsTotal); ?>;

    var mapYearMonthTipo  = <?php echo json_encode($mapYearMonthTipo); ?>;
    var mapYearMonthTotal = <?php echo json_encode($mapYearMonthTotal); ?>;

    function buildStackDatasets(tipoLabels, tipoSeries, totalSeries){
      var datasets = [];
      for(var i=0;i<tipoLabels.length;i++){
        datasets.push({
          type:'bar',
          label: tipoLabels[i],
          data: tipoSeries[i],
          backgroundColor: palette[i % palette.length],
          stack:'s1'
        });
      }
      datasets.push({
        type:'line',
        label:'Total',
        data: totalSeries,
        borderColor:'#111827',
        tension:0.35,
        borderWidth:2,
        pointRadius:2,
        fill:false
      });
      return datasets;
    }

    function buildMonthDatasetsForYear(year){
      var tipoLabels = tipoYearLabels.slice(0); // mismos labels (incluye OTROS si existe)
      var series = [];
      for(var i=0;i<tipoLabels.length;i++){
        var tp = tipoLabels[i];
        var arr = (mapYearMonthTipo && mapYearMonthTipo[year] && mapYearMonthTipo[year][tp]) ? mapYearMonthTipo[year][tp] : [0,0,0,0,0,0,0,0,0,0,0,0];
        series.push(arr);
      }
      var totalArr = (mapYearMonthTotal && mapYearMonthTotal[year]) ? mapYearMonthTotal[year] : [0,0,0,0,0,0,0,0,0,0,0,0];
      return buildStackDatasets(tipoLabels, series, totalArr);
    }

    function setYearView(){
      if(ttl) ttl.textContent = 'Ventas por Año: tipo_servicio';
      if(hint) hint.textContent = 'Click en un año para ver meses (apilado por tipo_servicio)';
      if(btnBack) btnBack.style.display = 'none';

      chart.data.labels = labelsYears;
      chart.data.datasets = buildStackDatasets(tipoYearLabels, tipoYearSeries, yearsTotal);

      chart.options.onClick = onClickYear;
      chart.options.scales = {
        x: { stacked:true },
        y: { stacked:true, beginAtZero:true }
      };
      chart.update();
    }

    function setMonthView(year){
      if(ttl) ttl.textContent = 'Ventas por Mes (' + year + '): tipo_servicio';
      if(hint) hint.textContent = 'Detalle mensual del año ' + year + ' (click Volver para regresar)';
      if(btnBack) btnBack.style.display = '';

      chart.data.labels = labelsMeses;
      chart.data.datasets = buildMonthDatasetsForYear(year);

      chart.options.onClick = null;
      chart.options.scales = {
        x: { stacked:true },
        y: { stacked:true, beginAtZero:true }
      };
      chart.update();
    }

    function onClickYear(evt, elements){
      if(!elements || !elements.length) return;
      var idx = elements[0].index;
      var year = labelsYears[idx];
      if(year) setMonthView(String(year));
    }

    var chart = new Chart(canvas,{
      data:{
        labels: labelsYears,
        datasets: buildStackDatasets(tipoYearLabels, tipoYearSeries, yearsTotal)
      },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ position:'bottom' } },
        interaction:{ mode:'index', intersect:false },
        scales:{ x:{ stacked:true }, y:{ stacked:true, beginAtZero:true } },
        onClick: onClickYear
      }
    });

    if(btnBack){
      btnBack.addEventListener('click', function(){ setYearView(); });
    }

  })();


  // ==========================
  // MENSUAL (stack + línea)
  // ==========================
  (function(){
    var labels = <?php echo json_encode($labelsMes, JSON_UNESCAPED_UNICODE); ?>;
    var total  = <?php echo json_encode($mesCant); ?>;

    var tipoLabels = <?php echo json_encode($tipoMesLabels, JSON_UNESCAPED_UNICODE); ?>;
    var tipoSeries = <?php echo json_encode($tipoMesSeries); ?>;

    var datasets = [];
    for(var i=0;i<tipoLabels.length;i++){
      datasets.push({ type:'bar', label: tipoLabels[i], data: tipoSeries[i], backgroundColor: palette[i % palette.length], stack:'s1' });
    }
    datasets.push({ type:'line', label:'Total', data: total, borderColor:'#111827', tension:0.35, borderWidth:2, pointRadius:1, fill:false });

    new Chart(document.getElementById(<?php echo json_encode($chMonth); ?>),{
      data:{ labels: labels, datasets: datasets },
      options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ position:'bottom' } },
        interaction:{ mode:'index', intersect:false },
        scales:{ x:{ stacked:true, ticks:{ maxTicksLimit: 12 } }, y:{ stacked:true, beginAtZero:true } }
      }
    });
  })();

})();
</script>
<?php
