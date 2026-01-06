'<?php
// 1. PRECAUCIONES Y CONFIGURACIÓN DE SALIDA EN TIEMPO REAL
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);
@apache_setenv('no-gzip', '1');
set_time_limit(0);
ob_implicit_flush(true);
while (ob_get_level() > 0) { ob_end_flush(); }

// -------------------------------------------------
// FUNCIONES AUXILIARES PARA PROGRESO
// -------------------------------------------------
function send_progress_js($percent, $message) {
    $p = (int)$percent;
    $m = addslashes($message);
    echo "<script>
            if (document.getElementById('progress-bar')) {
                document.getElementById('progress-bar').style.width = '{$p}%';
                document.getElementById('progress-label').innerText = '{$p}% - {$m}';
            }
          </script>\n";
    echo str_repeat(' ', 1024); 
    @flush();
    @ob_flush();
}

// -------------------------------------------------
// INTERFAZ HTML Y CSS
// -------------------------------------------------


?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Sincronización de Sucursal</title>
<style>
  body { background-color: #f0f2f5; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
  .progress-wrap{ width:90%; max-width:800px; margin:40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
  .progress { background:#e9ecef; border-radius:10px; height:28px; overflow:hidden; border:1px solid #dee2e6; margin-bottom:10px; }
  .progress-bar { height:100%; width:1%; background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%); transition: width .4s ease; }
  .progress-text { text-align:center; font-size:15px; font-weight:600; color:#495057; margin-bottom:20px; }
  
  .log-container { margin-top:20px; padding:15px; background:#212529; color: #33ff33; border-radius:8px; max-height:250px; overflow-y:auto; font-size:13px; font-family: "Courier New", Courier, monospace; }
  
  .btn-salir {
    display: block; width: 100%; max-width: 250px; margin: 25px auto 0; padding: 12px;
    background-color: #1877F2; color: white; text-align: center;
    text-decoration: none; border-radius: 6px; font-weight: bold;
    border: none; cursor: pointer; transition: all 0.3s;
  }
  .btn-salir:hover { background-color: #1877F2; transform: translateY(-1px); }
  .btn-success { background-color: #1877F2 !important; }
  .btn-success:hover { background-color: #1877F2 !important; }
</style>
</head>
<body>

<div class="progress-wrap">
  <h2 style="text-align:center; color:#1a202c; margin-top:0;">Sincronización con Matriz</h2>
  <p style="text-align:center; color:#718096; font-size:14px;">Procesando datos...</p>
  
  <div class="progress"><div id="progress-bar" class="progress-bar"></div></div>
  <div id="progress-label" class="progress-text">Iniciando...</div>
  
  <div id="log-result"></div>
  <div id="status" style="text-align:center; margin-top:15px;"></div>

  <!-- Botón -->
  <button id="btn-exit" class="btn-salir" type="button">Procesando...</button>

</div>

<script>
    var btn = document.getElementById('btn-exit');
    
    // Cambiar estado del botón a "Finalizado"
    btn.className = 'btn-salir btn-success'; 
    btn.innerText = 'Finalizar y Salir';     
    
    // Acción al hacer clic
    btn.onclick = function() { 
        // INTENTO 1: Comando específico para cerrar Modales de ScriptCase
        try {
            if (typeof parent.tb_remove === 'function') {
                parent.tb_remove();
                return;
            }
        } catch (e) { console.log('No es un modal Thickbox'); }

        // INTENTO 2: Si es un modal moderno o iframe simple
        try {
            if (parent && parent.document) {
                // A veces recargar el padre cierra el modal en ciertos temas de SC
                // O puedes usar: parent.location.reload(); 
            }
        } catch (e) {}

        // INTENTO 3: Cierre estándar (por si se abrió en ventana aparte)
        window.close();
        
        // INTENTO 4: Si todo falla, redirigir a una página en blanco o atrás
        // window.history.back(); 
    }; 
</script>

</body>
</html>
<?php
					  
					  
flush(); ob_flush();

// -------------------------------------------------
// LÓGICA DE NEGOCIO: BUSCAR MATRIZ
// -------------------------------------------------
send_progress_js(5, 'Localizando sucursal matriz...');

sc_lookup_field(sucursal_matris, "SELECT codigo AS codigo_matriz FROM configuracion_sucursal WHERE empresa = '[par_usr_empresa]' AND sucursal_matriz = 'SI'");

if (!isset({sucursal_matris[0]['codigo_matriz']})) {
    send_progress_js(0, 'Error: No se encontró sucursal matriz.');
    echo "<div style='color:red; text-align:center; margin-top:20px;'>Fallo crítico: No existe una sucursal configurada como MATRIZ.</div></body></html>";
    exit;
}

$var_codigo_matriz = {sucursal_matris[0]['codigo_matriz']};
send_progress_js(10, 'Matriz detectada. Analizando cambios...');

// -------------------------------------------------
// 1) ANALIZAR PRODUCTOS ANTES DE INSERTAR (PARA EL LOG)
// -------------------------------------------------
$sql_check = "SELECT nombre_productos FROM inventario_productos t1 
              WHERE t1.empresa = '[par_usr_empresa]' 
              AND t1.sucursal = '$var_codigo_matriz' 
              AND t1.producto_matriz = 'SI'
              AND NOT EXISTS (
                  SELECT 1 FROM inventario_productos t2 
                  WHERE t2.empresa = t1.empresa 
                  AND t2.sucursal = '[par_usr_sucursal]' 
                  AND t2.codigo_productos = t1.codigo_productos
              )";

sc_select(rs_prod, $sql_check);
$lista_nombres = [];
if ($rs_prod !== false) {
    while (!$rs_prod->EOF) {
        $lista_nombres[] = $rs_prod->fields[0];
        $rs_prod->MoveNext();
    }
}
$total_productos = count($lista_nombres);

// -------------------------------------------------
// EJECUCIÓN DE INSERCIONES SQL
// -------------------------------------------------

// A. PRODUCTOS
send_progress_js(20, 'Insertando productos nuevos...');
$var_sql_productos = "INSERT INTO inventario_productos (codigo_productos, nombre_productos, codigo_grupo_productos, codigo_subgrupo_productos, codigo_color_productos, marca_productos, modelo_productos, referencia_productos, peso_productos, codigo_provee_productos, codigo_proced_productos, codigo_arancel_productos, impuesto_productos, codigo_tiposerv_productos, manejaser_productos, costo_anterior_productos, costo_promedio_productos, costo_ultimo_productos, precio1_productos, precio2_productos, precio3_productos, ubicacion_productos, utilidad_precio1, utilidad_precio2, utilidad_precio3, facturar_impuestos_incluidos, precio_oferta, fecha_inicio_oferta, fecha_final_oferta, stock_min_productos, stock_max_productos, codigo_unidad_productos, estatus_productos, usuario, fecha, ip_estacion, empresa, sucursal, imagen, visible_icarobot_ia, codigo_padre, codigo_hijo, url_imagen, producto_matriz) 
SELECT t1.codigo_productos, t1.nombre_productos, t1.codigo_grupo_productos, t1.codigo_subgrupo_productos, t1.codigo_color_productos, t1.marca_productos, t1.modelo_productos, t1.referencia_productos, t1.peso_productos, t1.codigo_provee_productos, t1.codigo_proced_productos, t1.codigo_arancel_productos, t1.impuesto_productos, t1.codigo_tiposerv_productos, t1.manejaser_productos, t1.costo_anterior_productos, t1.costo_promedio_productos, t1.costo_ultimo_productos, t1.precio1_productos, t1.precio2_productos, t1.precio3_productos, t1.ubicacion_productos, t1.utilidad_precio1, t1.utilidad_precio2, t1.utilidad_precio3, t1.facturar_impuestos_incluidos, t1.precio_oferta, t1.fecha_inicio_oferta, t1.fecha_final_oferta, t1.stock_min_productos, t1.stock_max_productos, t1.codigo_unidad_productos, t1.estatus_productos, t1.usuario, t1.fecha, t1.ip_estacion, t1.empresa, '[par_usr_sucursal]', t1.imagen, t1.visible_icarobot_ia, t1.codigo_padre, t1.codigo_hijo, t1.url_imagen, t1.producto_matriz 
FROM inventario_productos t1 WHERE t1.empresa = '[par_usr_empresa]' AND t1.sucursal = '$var_codigo_matriz' AND t1.producto_matriz = 'SI' 
AND NOT EXISTS (SELECT 1 FROM inventario_productos t2 WHERE t2.empresa = t1.empresa AND t2.sucursal = '[par_usr_sucursal]' AND t2.codigo_productos = t1.codigo_productos)";
sc_exec_sql($var_sql_productos);

// B. GRUPOS
send_progress_js(50, 'Sincronizando grupos...');
$var_sql_grupos = "INSERT INTO inventario_grupo (codigo_grupo, nombre_grupo, usuario, fecha, ip_estacion, grupo_matriz, empresa, sucursal) 
SELECT t1.codigo_grupo, t1.nombre_grupo, t1.usuario, t1.fecha, t1.ip_estacion, t1.grupo_matriz, t1.empresa, '[par_usr_sucursal]' 
FROM inventario_grupo t1 WHERE t1.empresa = '[par_usr_empresa]' AND t1.sucursal = '$var_codigo_matriz' AND t1.grupo_matriz = 'SI' 
AND NOT EXISTS (SELECT 1 FROM inventario_grupo t2 WHERE t2.empresa = t1.empresa AND t2.sucursal = '[par_usr_sucursal]' AND t2.codigo_grupo = t1.codigo_grupo)";
sc_exec_sql($var_sql_grupos);

// C. SUBGRUPOS
send_progress_js(75, 'Sincronizando subgrupos...');
$var_sql_subgrupos = "INSERT INTO inventario_subgrupo (codigo_subgrupo, nombre_subgrupo, codigo_grupo, subgrupo_matriz, usuario, fecha, ip_estacion, empresa, sucursal) 
SELECT t1.codigo_subgrupo, t1.nombre_subgrupo, t1.codigo_grupo, t1.subgrupo_matriz, t1.usuario, t1.fecha, t1.ip_estacion, t1.empresa, '[par_usr_sucursal]' 
FROM inventario_subgrupo t1 WHERE t1.empresa = '[par_usr_empresa]' AND t1.sucursal = '$var_codigo_matriz' AND t1.subgrupo_matriz = 'SI' 
AND NOT EXISTS (SELECT 1 FROM inventario_subgrupo t2 WHERE t2.empresa = t1.empresa AND t2.sucursal = '[par_usr_sucursal]' AND t2.codigo_subgrupo = t1.codigo_subgrupo AND t2.codigo_grupo = t1.codigo_grupo)";
sc_exec_sql($var_sql_subgrupos);

// D. TIPOS
send_progress_js(90, 'Sincronizando tipos de productos...');
$var_sql_tipos = "INSERT INTO inventario_tipo_productos (codigo_tipo_productos, nombre_tipo_productos, maneja_stock, tipo_matriz, usuario, fecha, ip_estacion, empresa, sucursal, cuenta_padre, cuenta_hijo) 
SELECT t1.codigo_tipo_productos, t1.nombre_tipo_productos, t1.maneja_stock, t1.tipo_matriz, t1.usuario, t1.fecha, t1.ip_estacion, t1.empresa, '[par_usr_sucursal]', t1.cuenta_padre, t1.cuenta_hijo 
FROM inventario_tipo_productos t1 WHERE t1.empresa = '[par_usr_empresa]' AND t1.sucursal = '$var_codigo_matriz' AND t1.tipo_matriz = 'SI' 
AND NOT EXISTS (SELECT 1 FROM inventario_tipo_productos t2 WHERE t2.empresa = t1.empresa AND t2.sucursal = '[par_usr_sucursal]' AND t2.codigo_tipo_productos = t1.codigo_tipo_productos)";
sc_exec_sql($var_sql_tipos);

// -------------------------------------------------
// FINALIZACIÓN Y LOG VISUAL
// -------------------------------------------------
$log_final = "<div class='log-container'>";
$log_final .= "<div class='log-title'>PRODUCTOS INSERTADOS: $total_productos</div>";
if ($total_productos > 0) {
    foreach ($lista_nombres as $nombre) {
        $log_final .= "> Sincronizado: " . htmlspecialchars($nombre) . "<br>";
    }
} else {
    $log_final .= "> No se encontraron productos nuevos para agregar.";
}
$log_final .= "</div>";

send_progress_js(100, 'Sincronización Completada');

echo "<script>
    // Mostrar Log
    document.getElementById('log-result').innerHTML = '" . str_replace(["\r", "\n"], '', addslashes($log_final)) . "';
    
    // Actualizar Status
    document.getElementById('status').innerHTML = '<h4 style=\"color:#28a745;\">¡Proceso terminado con éxito!</h4>';
    
    // Modificar Botón
    var btn = document.getElementById('btn-exit');
    btn.className = 'btn-salir btn-success';
    btn.innerText = 'Finalizar y Salir';
</script>";

echo "</body></html>";
flush(); ob_flush();
exit;
?>