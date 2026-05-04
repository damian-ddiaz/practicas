<?php

// 1. Variables de configuración
$var_id_modulo_maestro = 15;
/* Variables para ajustar colores */
$var_table_back_color = "#7D7C7C";
$var_table_fore_color = "#212020";


// --- PROCESAMIENTO AJAX (INICIO) ---
if (isset($_GET['ajax_fetch_apps'])) 
{
    $group_id = (isset($_GET['ajax_group_id'])) ? (int)$_GET['ajax_group_id'] : 0;
    
    $sql_apps = "SELECT apps.app_name 
                 FROM seguridad_groups_apps AS apps
                 INNER JOIN seguridad_groups AS grupos ON apps.group_id = grupos.group_id
                 WHERE grupos.modulo = $var_id_modulo_maestro 
                 AND apps.group_id = $group_id
                 AND apps.priv_access = 'Y'";
    
    sc_lookup(ds_ajax, $sql_apps);
    
    $res_json = array();
    if (!empty({ds_ajax})) {
        foreach ({ds_ajax} as $f) {
            $res_json[] = array('app_name' => $f[0]);
        }
    }
    
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode($res_json);
    exit; 
}

// AJAX para obtener el log con FILTROS REFINADOS
// AJAX para obtener el log con FILTROS REFINADOS (CORREGIDO)
if (isset($_GET['ajax_fetch_log'])) 
{
    $app_name   = (isset($_GET['app_name'])) ? $_GET['app_name'] : '';
    $filtro_suc = (isset($_GET['f_suc']) && $_GET['f_suc'] != '') ? $_GET['f_suc'] : '';
    $filtro_usu = (isset($_GET['f_usu']) && $_GET['f_usu'] != '') ? $_GET['f_usu'] : '';
    $f_desde    = (isset($_GET['f_desde']) && $_GET['f_desde'] != '') ? $_GET['f_desde'] : '';
    $f_hasta    = (isset($_GET['f_hasta']) && $_GET['f_hasta'] != '') ? $_GET['f_hasta'] : '';

    $where_extra = "";
    if ($filtro_suc != '') {
        $where_extra .= " AND SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Suc: ', -1), ' - ', 1) = '$filtro_suc'";
    }
    if ($filtro_usu != '') {
        $where_extra .= " AND SUBSTRING_INDEX(scl.username, 'Usu: ', -1) = '$filtro_usu'";
    }
    
    // --- NUEVO: Validación de Rango de Fechas ---
    if ($f_desde != '') {
        $where_extra .= " AND scl.inserted_date >= '$f_desde 00:00:00'";
    }
    if ($f_hasta != '') {
        $where_extra .= " AND scl.inserted_date <= '$f_hasta 23:59:59'";
    }
    // --------------------------------------------
    
    $sql_log = "SELECT 
                    scl.inserted_date, 
                    sc.descripcion as nombre_sucursal,
                    su.name as nombre_usuario,
                    scl.ip_user, 
                    scl.action, 
                    scl.description  
                FROM sc_log_inventario scl
                LEFT JOIN configuracion_sucursal sc ON sc.codigo = SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Suc: ', -1), ' - ', 1)
                LEFT JOIN seguridad_users su ON su.login = SUBSTRING_INDEX(scl.username, 'Usu: ', -1)
                WHERE scl.application = '$app_name'
                AND SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Emp: ', -1), '-', 1) = '[usr_empresa]'
                $where_extra 
                ORDER BY scl.inserted_date DESC";
    
    sc_lookup(ds_log, $sql_log);
    
    $res_log = array();
    if (!empty({ds_log})) {
        foreach ({ds_log} as $f) {
            $res_log[] = array(
                'date' => $f[0],
                'suc'  => $f[1],
                'user' => $f[2],
                'ip'   => $f[3],
                'act'  => $f[4],
                'desc' => $f[5]
            );
        }
    }
    
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode($res_log);
    exit;
}
// --- FIN PROCESAMIENTO AJAX ---

// 2. CARGA DE DATOS PARA SELECTORES INICIALES
$sql_grupos = "SELECT group_id, description FROM seguridad_groups WHERE modulo = " . $var_id_modulo_maestro;
sc_lookup(ds_grupos, $sql_grupos);

$sql_sucursales = "SELECT codigo, descripcion 
FROM configuracion_sucursal 
WHERE empresa = '[usr_empresa]' ORDER BY descripcion ASC";
sc_lookup(ds_suc, $sql_sucursales);

$sql_usuarios = "SELECT login, name FROM seguridad_users 
WHERE codigo_empresa = '[usr_empresa]' ORDER BY name ASC";
sc_lookup(ds_usu, $sql_usuarios);
?>

<!-- INTERFAZ HTML Y JAVASCRIPT -->
<style>
	
		/* Contenedor para alinear botones en una fila */
	.button-row {
		display: flex;
		gap: 10px;
		margin-top: 10px;
	}

	/* Ajuste al botón de búsqueda para que comparta el espacio */
	.btn-search {
		margin-top: 0;
		flex: 1;
		/* Aseguramos que la altura sea consistente */
		height: 45px; 
		padding: 0 12px;
	}

	/* Estilo específico para el botón Salir (basado en tu imagen) */
	.btn-exit {
		margin-top: 10px;
		flex: 0 0 120px;
		/* Ajustamos la altura igual a la de btn-search */
		height: 45px; 
		padding: 0 12px;

		/* Gris más oscuro (puedes probar con #ced4da o #adb5bd) */
		background-color: #ced4da; 
		color: #212529;
		border: 1px solid #adb5bd;

		border-radius: 4px;
		cursor: pointer;
		font-weight: bold;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 5px;
		transition: background 0.3s;
	}

	.btn-exit:hover {
		background-color: #adb5bd; /* Tono más oscuro al pasar el mouse */
	}

    .main-container { 
		display: flex; 
		gap: 30px; 
		padding: 30px; 
		font-family: sans-serif; 
		background-color: #f4f7f6; 
		min-height: 100vh; 
	}
    .selectors-column { 
		flex: 0 0 320px; 
		background: white; 
		padding: 20px; 
		border-radius: 8px; 
		box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
		height: fit-content; 
	}
    .table-column { 
		flex: 1; background: 
		white; padding: 20px; 
		border-radius: 8px; 
		box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
		overflow-x: auto; 
	}
    
    .filter-group { 
		margin-bottom: 15px; 
	}
    .filter-group label { 
		display:block; 
		font-weight:bold; 
		margin-bottom:6px; 
		color: #34495e; 
		font-size: 14px; 
	}
    .filter-group select { 
		width:100%; 
		padding:10px; 
		border:1px solid #dcdde1; 
		border-radius:4px; 
		outline: none; 
		transition: border 0.3s; 
	}
    .filter-group select:focus {
    	 border-color: #3498db; 
	}

    .log-table { 
		width: 100%; 
		border-collapse: collapse; 
		font-size: 13px; 
	}
	
    .log-table th { 
	   background-color: <?php echo $var_table_back_color; ?>;
       color: <?php echo $var_table_fore_color; ?>; 
	   text-align: left; 
	   padding: 12px 10px; 
	}
	
    .log-table td { 
		padding: 12px 10px; 
		border-bottom: 1px solid #eee; 
	}
	
    .log-table tr:hover { 
		background-color: #f9f9f9; 
	}
    
    .badge-action { 
		padding: 4px 8px; 
		border-radius: 4px; 
		font-weight: bold; 
		text-transform: uppercase; 
		font-size: 11px; 
	}
	
    .action-access { 
		background: #e8f5e9; 
		color: #2e7d32;
	}
    .action-update { 
		background: #fff3e0; 
		color: #ef6c00; 
	}
    .btn-search { 
		width: 100%; 
		padding: 12px; 
		background: #3498db; 
		color: white; 
		border: none; 
		border-radius: 4px; 
		cursor: pointer; 
		font-weight: bold; 
		margin-top: 10px; 
	}
	
    .btn-search:hover {
    	 background: #2980b9; 
	}
	
</style>

<div class="main-container">
    <div class="selectors-column">
        <h3 style="margin-top:0; color:#2c3e50; border-bottom: 2px solid #3498db; padding-bottom:10px;">Filtros de Auditoría</h3>
        
        <div class="filter-group">
            <label>Grupo de Seguridad:</label>
            <select id="sel_grupos" onchange="fn_get_apps(this.value)">
                <option value="">-- Seleccione --</option>
                <?php
                if (!empty({ds_grupos})) {
                    foreach ({ds_grupos} as $g) {
                        echo "<option value='{$g[0]}'>{$g[1]}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Aplicación:</label>
            <select id="sel_apps" disabled onchange="fn_check_ready()">
                <option value="">-- Primero elija grupo --</option>
            </select>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <div class="filter-group">
            <label>Sucursal (Opcional):</label>
            <select id="f_suc">
                <option value="">-- Todas las sucursales --</option>
                <?php
                if (!empty({ds_suc})) {
                    foreach ({ds_suc} as $s) {
                        echo "<option value='{$s[0]}'>{$s[1]}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Usuario (Opcional):</label>
            <select id="f_usu">
                <option value="">-- Todos los usuarios --</option>
                <?php
                if (!empty({ds_usu})) {
                    foreach ({ds_usu} as $u) {
                        echo "<option value='{$u[0]}'>{$u[1]}</option>";
                    }
                }
                ?>
            </select>
        </div>
		
		<!-- Agregar debajo del select de Usuario -->
		<div class="filter-group">
			<label>Rango de Fechas:</label>
			<div style="display: flex; gap: 5px;">
				<input type="date" id="f_desde" style="width:50%; padding:8px; border:1px solid #dcdde1; border-radius:4px;">
				<input type="date" id="f_hasta" style="width:50%; padding:8px; border:1px solid #dcdde1; border-radius:4px;">
			</div>
		</div>

      <!-- <button class="btn-search" onclick="fn_get_log()">BUSCAR REGISTROS</button> -->
	
	 <div class="button-row">
		<button class="btn-search" onclick="fn_get_log()">BUSCAR REGISTROS</button>
		<!--<button class="btn-exit" onclick="window.close();">
			<span>&#10142;</span> Salir
		</button>-->
		 <button class="btn-exit" onclick="fn_salir_modulo()">
			<span>&#10142;</span> Salir
		</button>	 
	 </div>		
    </div>

    <div class="table-column">
        <div id="log_container">
            <p style="color: #666; font-style: italic; text-align: center; margin-top: 50px;">Use los filtros de la izquierda para consultar el historial.</p>
        </div>
    </div>
</div>

<script>
function fn_get_apps(id) {
    const target = document.getElementById('sel_apps');
    if (!id) {
        target.disabled = true;
        target.innerHTML = '<option value="">-- Primero elija grupo --</option>';
        return;
    }
    
    fetch(window.location.origin + window.location.pathname + '?ajax_fetch_apps=1&ajax_group_id=' + id)
        .then(r => r.json())
        .then(data => {
            target.disabled = false;
            target.innerHTML = '<option value="">-- Seleccione Aplicación --</option>';
            data.forEach(i => {
                let o = document.createElement('option');
                o.value = i.app_name;
                o.textContent = i.app_name;
                target.appendChild(o);
            });
        });
}

function fn_get_log() {
    const appName = document.getElementById('sel_apps').value;
    const suc = document.getElementById('f_suc').value;
    const usu = document.getElementById('f_usu').value;
    
    // --- CORRECCIÓN: Captura de fechas ---
    const desde = document.getElementById('f_desde').value;
    const hasta = document.getElementById('f_hasta').value;
    // -------------------------------------

    const container = document.getElementById('log_container');

    if (!appName) {
        alert("Por favor, seleccione un Grupo de Seguridad y una Aplicación.");
        return;
    }

    container.innerHTML = '<div style="text-align:center; padding:50px;">Cargando registros...</div>';
    
    const params = new URLSearchParams({
        ajax_fetch_log: 1,
        app_name: appName,
        f_suc: suc,
        f_usu: usu,
        // --- CORRECCIÓN: Envío de fechas al servidor ---
        f_desde: desde,
        f_hasta: hasta
        // -----------------------------------------------
    });

    fetch(window.location.origin + window.location.pathname + '?' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (data && data.length > 0) {
                let html = `
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Fecha</th>
                                <th style="width: 130px;">Sucursal</th>
                                <th style="width: 100px;">Usuario</th>
                                <th style="width: 40px; text-align: left;">IP</th>
                                <th style="width: 40px; text-align: left;">Acción</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>`;

                data.forEach(row => {
                    let badgeClass = row.act === 'access' ? 'action-access' : (row.act === 'update' ? 'action-update' : '');

                    // Separamos fecha y hora
                    const [soloFecha, soloHora] = row.date.split(' ');

                    html += `
                        <tr>
                            <td style="color:#e67e22; font-family: monospace; line-height: 1.2;">
                                <div style="font-weight: bold;">${soloFecha}</div>
                                <div style="font-size: 11px; color: #7f8c8d;">${soloHora}</div>
                            </td>
                            <td style="font-weight:bold; color:#34495e;">${row.suc || 'N/A'}</td>
                            <td style="color:#27ae60;">${row.user || 'N/A'}</td>
                            <td style="color:#2980b9; text-align: left;">${row.ip}</td>
                            <td style="text-align: left;"><span class="badge-action ${badgeClass}">${row.act}</span></td>
                            <td style="color:#7f8c8d; font-family:monospace; font-size:11px;">${row.desc || ''}</td>
                        </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div style="padding:50px; border:1px dashed #ccc; text-align:center; color:#999;">No se encontraron registros con los filtros seleccionados.</div>';
            }
        })
        .catch(e => {
            container.innerHTML = "Error al conectar con el servidor.";
            console.error("Error en fetch:", e);
        });
}

function fn_check_ready() {
    // Podrías disparar la búsqueda automática aquí si prefieres no usar el botón
}
	
function fn_salir_modulo() {
    // Si la app está abierta en un Iframe (dentro del menú de Scriptcase)
    if (window.parent && window.parent !== window) {
        // Opción 1: Redirección forzada al padre para limpiar el frame
        window.parent.location.href = '../menu_inventario/menu_inventario.php'; 
    } else {
        // Opción 2: Si por alguna razón se abrió en ventana aparte, intenta cerrar o volver
        if (!window.close()) {
            window.history.back();
        }
    }
}	
</script>
<?php

?>