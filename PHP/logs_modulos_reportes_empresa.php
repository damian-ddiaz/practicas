// 1. Variables de configuración
$var_table_back_color = "#7D7C7C";
$var_table_fore_color = "#212020";

// --- CARGA DE DATOS MAESTROS (Necesaria para procesos AJAX y el HTML) ---
$sql_modulos = "SELECT id_modulo_maestro, descripcion 
                FROM seguridad_grupos_maestros 
                WHERE visible_usuario = 'SI' 
                ORDER BY descripcion ASC";
sc_lookup(ds_modulos, $sql_modulos);

// Definir el mod_id por defecto (el primero de la lista) para uso interno si es necesario
$default_mod_id = (isset({ds_modulos[0][0]})) ? {ds_modulos[0][0]} : 0;

// AJAX: Cargar Grupos por Módulo Maestro
if (isset($_GET['ajax_fetch_groups'])) 
{
    // Si se recibe un ID por GET se usa, de lo contrario se usa el default capturado del SQL inicial
    $mod_id = (isset($_GET['ajax_mod_id']) && $_GET['ajax_mod_id'] != '') ? (int)$_GET['ajax_mod_id'] : $default_mod_id;
    
    $sql_groups = "SELECT group_id, description 
                   FROM seguridad_groups 
                   WHERE modulo = $mod_id 
                   ORDER BY description ASC";
    
    sc_lookup(ds_ajax_groups, $sql_groups);
    
    $res_json = array();
    if (!empty({ds_ajax_groups})) {
        foreach ({ds_ajax_groups} as $f) {
            $res_json[] = array('id' => $f[0], 'name' => $f[1]);
        }
    }
    
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode($res_json);
    exit; 
}

// AJAX: Obtener Log con FILTROS y PAGINACIÓN
// AJAX: Obtener Log con FILTROS y PAGINACIÓN
if (isset($_GET['ajax_fetch_log'])) 
{
	$var_empresa = (isset($_GET['f_emp']) && $_GET['f_emp'] != '') ? $_GET['f_emp'] : $var_empresa;
	
    // 1. Captura de parámetros
    $filtro_grupo = (isset($_GET['ajax_group_id']) && $_GET['ajax_group_id'] != '') ? (int)$_GET['ajax_group_id'] : 0;
    $filtro_suc   = (isset($_GET['f_suc']) && $_GET['f_suc'] != '') ? $_GET['f_suc'] : '';
    $filtro_usu   = (isset($_GET['f_usu']) && $_GET['f_usu'] != '') ? $_GET['f_usu'] : '';
    $f_desde      = (isset($_GET['f_desde']) && $_GET['f_desde'] != '') ? $_GET['f_desde'] : '';
    $f_hasta      = (isset($_GET['f_hasta']) && $_GET['f_hasta'] != '') ? $_GET['f_hasta'] : '';
    $pagina       = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    
    $por_pagina   = 9;
    $inicio       = ($pagina - 1) * $por_pagina;
        
    $where_extra = "";

    // Filtros dinámicos
    if ($filtro_suc != '') {
        $where_extra .= " AND SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Suc: ', -1), ' - ', 1) = '$filtro_suc'";
    }
    if ($filtro_usu != '') {
        $where_extra .= " AND SUBSTRING_INDEX(scl.username, 'Usu: ', -1) = '$filtro_usu'";
    }
    if ($f_desde != '') {
        $where_extra .= " AND scl.inserted_date >= '$f_desde 00:00:00'";
    }
    if ($f_hasta != '') {
        $where_extra .= " AND scl.inserted_date <= '$f_hasta 23:59:59'";
    }
        
    // --- CONTEO TOTAL (Importante para el botón Siguiente) ---
    $sql_count = "SELECT COUNT(*) 
                  FROM sc_log_modulos scl
                  INNER JOIN seguridad_groups_apps apps ON scl.application = apps.app_name
                  WHERE apps.group_id = $filtro_grupo 
                  AND SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Emp: ', -1), '-', 1) = '$var_empresa'
                  $where_extra";
    
    sc_lookup(ds_count, $sql_count);
    $total_registros = (isset({ds_count[0][0]})) ? {ds_count[0][0]} : 0;
    $total_paginas = ceil($total_registros / $por_pagina);

    // --- CONSULTA DE DATOS ---
    $sql_log = "SELECT 
                    scl.inserted_date, 
                    sc.descripcion as nombre_sucursal,
                    su.name as nombre_usuario,
                    scl.application,
                    scl.ip_user, 
                    scl.action, 
                    scl.description,
					scl.id
                FROM sc_log_modulos AS scl
                INNER JOIN seguridad_groups_apps AS apps ON scl.application = apps.app_name
                LEFT JOIN configuracion_sucursal sc ON sc.codigo = SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Suc: ', -1), ' - ', 1)
                LEFT JOIN seguridad_users su ON su.login = SUBSTRING_INDEX(scl.username, 'Usu: ', -1)
                WHERE apps.group_id = $filtro_grupo
                  AND apps.priv_access = 'Y'
                  AND SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Emp: ', -1), '-', 1) = '$var_empresa'
                  $where_extra 
                ORDER BY scl.inserted_date DESC
                LIMIT $inicio, $por_pagina";
    
    sc_lookup(ds_log, $sql_log);
    
    $res_data = array();
    if (!empty({ds_log})) {
        foreach ({ds_log} as $f) {
            $res_data[] = [
                'date' => $f[0], 'suc' => $f[1], 'user' => $f[2],
                'app' => $f[3], 'ip' => $f[4], 'act' => $f[5], 'desc' => $f[6],
				'id'   => $f[7] // <--- AGREGAR ESTA LÍNEA
            ];
        }
    }
    
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode([
        'data'          => $res_data,
        'total_pages'   => $total_paginas,
        'current_page'  => $pagina,
        'total_records' => (int)$total_registros
    ]);    
    exit;
}

// 2. RESTO DE CARGA INICIAL PARA HTML
$sql_empresas = "select codigo, descripcion  from configuracion_empresa 
where codigo ='J181228500' order by descripcion";
sc_lookup(ds_emp, $sql_empresas);

$sql_sucursales = "SELECT codigo, descripcion FROM configuracion_sucursal WHERE empresa = '$var_empresa' ORDER BY descripcion ASC";
sc_lookup(ds_suc, $sql_sucursales);

$sql_usuarios = "SELECT login, name FROM seguridad_users WHERE codigo_empresa = '$var_empresa' ORDER BY name ASC";
sc_lookup(ds_usu, $sql_usuarios);
?>

<style>

	/* Esto afecta al contenedor del modal de ScriptCase */
	#nm_sc_iframe_menu, .TB_window {
		top: 10% !important; /* Lo baja un 10% desde el tope */
	}
	
	/* Esto bajará el modal desde el tope de la pantalla */
	#nm_sc_iframe_menu {
		top: 100px !important; /* Aumenta este valor para bajarlo más */
	}
	
	#TB_window {
		margin-top: 50px !important; /* Ajusta este valor según necesites */
	}
	
	.btn-view-log {
		padding: 4px 8px;
		background: #17a2b8;
		color: white;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-size: 11px;
		transition: background 0.2s;
	}
	.btn-view-log:hover {
		background: #138496;
	}
	
    .main-container { 
		display: flex; 
		gap: 20px; 
		padding: 20px; 
		font-family: 'Segoe UI', Tahoma, sans-serif; 
		background: #f8f9fa; 
		min-height: 100vh; 
	}
	
    .selectors-column { 
		flex: 0 0 300px; 
		background: white; 
		padding: 20px; 
		border-radius: 8px; 
		box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
		height: fit-content; 
	}
	
    .table-column { 
		flex: 1; 
		background: white; 
		padding: 20px; 
		border-radius: 8px; 
		box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
	}
	
    .filter-group { 
		margin-bottom: 15px; 
	}
	
    .filter-group label { 
		display:block; 
		font-weight:600;
		margin-bottom:5px; 
		font-size: 13px; 
		color: #495057; 
	}
	
    .filter-group select, .filter-group input {
    	width:100%; 
		padding:8px; 
		border:1px solid #ced4da; 
		border-radius:4px; 
		font-size: 13px; 
	}
	
    .btn-search { 
		flex: 1; 
		padding: 10px; 
		background: #007bff; 
		color: white; 
		border: none; 
		border-radius: 4px; 
		cursor: pointer; 
		font-weight: bold; 
	}
	
    .btn-exit { 
		flex: 0 0 100px; 
		padding: 10px; 
		background: #6c757d; 
		color: white; 
		border: none; 
		border-radius: 4px; 
		cursor: pointer; 
	}
	
    .log-table { 
		width: 100%; 
		border-collapse: collapse; 
		margin-top: 10px; 
		font-size: 12px; 
	}
   
	.log-table th {
    	background: <?php echo $var_table_back_color; ?>; 
		color: <?php echo $var_table_fore_color; ?>; 
		padding: 10px; 
		text-align: left; 
	}

	.log-table td { 
		padding: 10px; 
		border-bottom: 1px solid #eee; 
		vertical-align: top; 
		word-break: break-word; /* Agrega esto para que textos largos no rompan la tabla */
	}

	.badge-action { 
		padding: 3px 6px; 
		border-radius: 3px; 
		font-size: 10px; 
		font-weight: bold; 
		text-transform: uppercase; 
	}
	
    .action-access { 
		background: #d4edda; 
		color: #155724; 
	}
	
    .action-update { 
		background: #fff3cd; 
		color: #856404; 
	}
	
    .pagination-container { 
		display: flex; 
		justify-content: center; 
		align-items: center; 
		gap: 15px; 
		margin-top: 20px; 
		padding: 15px; 
		border-top: 1px solid #eee; }
   
	.btn-page { 
		padding: 5px 12px; 
		border: 1px solid #dee2e6; 
		background-color: #007bff; 
		color: white; 
		cursor: pointer; 
		border-radius: 4px; 
	}
	
    .btn-page:disabled { 
		background: #e9ecef; 
		cursor: not-allowed; 
		color: #adb5bd; 
	}
	
	.desc-container {
		height: 60px;    /* Altura fija deseada */
		overflow-y: auto; /* Scroll vertical si el texto es largo */
		font-family: monospace;
		font-size: 11px;
		color: #666;
		padding-right: 5px;
	}	
</style>

<div class="main-container">
    <div class="selectors-column">
        <h3 style="margin:0 0 15px 0; font-size:18px; color:#333;">Auditoría</h3>
    
        <div class="filter-group">
            <label>Módulo:</label>
            <select id="sel_modulos" onchange="fn_get_groups(this.value)">
                <option value="">-- Seleccione --</option>
                <?php
                if (!empty({ds_modulos})) {
                    foreach ({ds_modulos} as $m) {
                        echo "<option value='{$m[0]}'>{$m[1]}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Grupo de Seguridad:</label>
            <select id="sel_grupos" onchange="fn_get_apps(this.value)">
                <option value="">-- Seleccione Módulo --</option>
            </select>
        </div>

		<div class="filter-group">
			<label>Empresa:</label>
			<!-- Aquí es donde incluyes el evento onchange -->
			<select id="f_emp" onchange="document.getElementById('log_container').innerHTML = '<p style=\'text-align:center; color:#999; margin-top:50px;\'>Filtros cambiados. Haga clic en BUSCAR.</p>';">
				<option value="">-- Seleccione una Empresa --</option>
				<?php
				if (!empty({ds_emp})) {
					foreach ({ds_emp} as $s) echo "<option value='{$s[0]}'>{$s[1]}</option>";
				}
				?>
			</select>
		</div>
				
        <div class="filter-group">
            <label>Sucursal:</label>
            <select id="f_suc">
                <option value="">-- Todas --</option>
                <?php
                if (!empty({ds_suc})) {
                    foreach ({ds_suc} as $s) echo "<option value='{$s[0]}'>{$s[1]}</option>";
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Usuario:</label>
            <select id="f_usu">
                <option value="">-- Todos --</option>
                <?php
                if (!empty({ds_usu})) {
                    foreach ({ds_usu} as $u) echo "<option value='{$u[0]}'>{$u[1]}</option>";
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Desde / Hasta:</label>
            <div style="display: flex; gap: 5px;">
                <input type="date" id="f_desde">
                <input type="date" id="f_hasta">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button class="btn-search" onclick="fn_get_log(1)">BUSCAR</button>
            <button class="btn-exit" onclick="fn_salir_modulo()">Salir</button>
        </div>
    </div>

    <div class="table-column">
        <div id="log_container">
            <p style="text-align:center; color:#999; margin-top:50px;">Seleccione filtros para comenzar.</p>
        </div>
    </div>
</div>

<script>
let current_page = 1;
		
	function fn_get_groups(mod_id) {
		const selGrupos = document.getElementById('sel_grupos');
		selGrupos.innerHTML = '<option value="">-- Cargando... --</option>';

		let url = window.location.origin + window.location.pathname + '?ajax_fetch_groups=1';
		if(mod_id) url += '&ajax_mod_id=' + mod_id;

		fetch(url)
			.then(r => r.json())
			.then(data => {
				selGrupos.innerHTML = '<option value="">-- Seleccione Grupo --</option>';
				data.forEach(i => {
					let o = document.createElement('option');
					o.value = i.id;
					o.textContent = i.name;
					selGrupos.appendChild(o);
				});
			});
	}
	

function fn_get_log(page = 1) {
	
	const grupoId = document.getElementById('sel_grupos').value;
    if (!grupoId) { alert("Seleccione un Grupo de Seguridad."); return; }
    
    current_page = page;
    const container = document.getElementById('log_container');
    container.innerHTML = '<p style="text-align:center;">Cargando...</p>';
    
	const params = new URLSearchParams({
        ajax_fetch_log: 1,
        ajax_group_id: grupoId,
        // Esta línea envía el valor del selector al PHP:
        f_emp: document.getElementById('f_emp').value, 
        f_suc: document.getElementById('f_suc').value,
        f_usu: document.getElementById('f_usu').value,
        f_desde: document.getElementById('f_desde').value,
        f_hasta: document.getElementById('f_hasta').value,
        page: page
    });

    fetch(window.location.origin + window.location.pathname + '?' + params.toString())
        .then(r => r.json())
        .then(res => {
            if (res.data && res.data.length > 0) {
				// ... dentro de res.data.length > 0
				let html = `<table class="log-table">
					<thead>
						<tr>
							<th style="width: 50px;"></th> <!-- Nueva columna -->
							<th style="width: 80px;">Fecha/Hora</th>
							<th style="width: 110px;">Sucursal</th>
							<th style="width: 100px;">Usuario</th>
							<th style="width: 160px;">Aplicacion</th>
							<th style="width: 57px;">Ip</th>
							<th style="width: 55px;">Accion</th>
							<th>Descripción</th>
						</tr>
					</thead>
					<tbody>`;

				// --- BUSCA ESTA SECCIÓN EN TU JS ---

				res.data.forEach(row => {
					const [fecha, hora] = row.date.split(' ');
					const actLower = (row.act || '').toLowerCase();
					const bCls = actLower === 'access' ? 'action-access' : (actLower === 'update' ? 'action-update' : '');

					// AQUÍ EL CAMBIO: Se actualiza el onclick a fn_abrir_modal_detalle
					html += `<tr>
						<td style="text-align:center;">
							<button class="btn-view-log" onclick="fn_abrir_modal_detalle('${row.id}')">Ver</button>
						</td>
						<td><strong>${fecha}</strong><br><small style="color:#888">${hora}</small></td>
						<td>${row.suc || '-'}</td>
						<td>${row.user || '-'}</td>
						<td>${row.app || '-'}</td> 
						<td>${row.ip || '-'}</td>
						<td><span class="badge-action ${bCls}">${row.act || ''}</span></td>
						<td style="vertical-align: top;">
							<div style="height: 60px; overflow-y: auto; font-family: monospace; font-size: 11px; color: #666; line-height: 1.2;">
								${row.desc || ''}
							</div>
						</td>
					</tr>`;
				});				
				
				

                html += '</tbody></table>';
                
                if (res.total_pages > 1) {
                    html += `
                    <div class="pagination-container">
                        <button class="btn-page" 
							onclick="fn_get_log(${res.current_page - 1})" 
							${res.current_page <= 1 ? 'disabled' : ''}>
							&laquo; Anterior
						</button>
                        <span style="font-size:13px;">Pág <strong>${res.current_page}</strong> de ${res.total_pages}</span>

						<button class="btn-page" 
							onclick="fn_get_log(${res.current_page + 1})" 
							${Number(res.current_page) >= Number(res.total_pages) ? 'disabled' : ''}>
							Siguiente &raquo;
						</button>
                    </div>`;            
                }                             
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p style="text-align:center; padding:20px;">No se encontraron registros.</p>';
            }
        });
}

function fn_salir_modulo() {
    if (window.parent && window.parent !== window) {
        window.parent.location.href = '../menu_inventario/menu_inventario.php'; 
    } else {
        if (!window.close()) window.history.back();
    }
}

function fn_ver_detalle(id) {
    // Aquí puedes abrir un modal de ScriptCase o un alert para probar
    console.log("Consultando detalle del ID: " + id);
    alert("Visualizando registro ID: " + id);
}
	
function fn_abrir_modal_detalle(id_log) {
    var url = '../logs_modulos_panel_de_control_descripcion/logs_modulos_panel_de_control_descripcion.php';
    url += '?id_log=' + id_log;

    // Forzamos la llamada al modal de ScriptCase
    if (typeof parent.nm_gp_submit8 === 'function') {
        // Esta es la función principal de SC para modales
        // Parámetros: URL, Título, Altura, Anchura, Tipo
        parent.nm_gp_submit8(url, "Detalle del Log", 600, 900, "modal");
        
        // Para bajar el modal un poco más
        setTimeout(function() {
            var m = parent.document.getElementById('nm_sc_iframe_menu');
            if(m) m.style.top = '100px'; 
        }, 200);
        
    } else {
        // Si no detecta el entorno SC, usamos un popup centrado (no pestaña)
        var left = (screen.width/2)-(900/2);
        var top = (screen.height/2)-(600/2);
        window.open(url, 'Detalle', 'width=900,height=600,top='+top+',left='+left);
    }
}
	
</script>
<?php