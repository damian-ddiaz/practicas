<?php

// 1. Variables de configuración
$var_id_modulo_maestro = 15;
$var_table_back_color = "#7D7C7C";
$var_table_fore_color = "#212020";

// --- PROCESAMIENTO AJAX (INICIO) ---

// AJAX: Cargar Aplicaciones por Grupo
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

// AJAX: Obtener Log con FILTROS y PAGINACIÓN DE 9
if (isset($_GET['ajax_fetch_log'])) 
{
    $app_name   = (isset($_GET['app_name'])) ? $_GET['app_name'] : '';
    $filtro_suc = (isset($_GET['f_suc']) && $_GET['f_suc'] != '') ? $_GET['f_suc'] : '';
    $filtro_usu = (isset($_GET['f_usu']) && $_GET['f_usu'] != '') ? $_GET['f_usu'] : '';
    $f_desde    = (isset($_GET['f_desde']) && $_GET['f_desde'] != '') ? $_GET['f_desde'] : '';
    $f_hasta    = (isset($_GET['f_hasta']) && $_GET['f_hasta'] != '') ? $_GET['f_hasta'] : '';
    
    // Configuración de Paginación
    $pagina      = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    $por_pagina  = 9;
    $inicio      = ($pagina - 1) * $por_pagina;

    $where_extra = "";
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
    
    // 1. Contar total de registros para calcular páginas
    $sql_count = "SELECT COUNT(*) FROM sc_log_inventario scl 
                  WHERE scl.application = '$app_name' 
                  AND SUBSTRING_INDEX(SUBSTRING_INDEX(scl.username, 'Emp: ', -1), '-', 1) = '[usr_empresa]'
                  $where_extra";
    sc_lookup(ds_count, $sql_count);
    $total_registros = (isset({ds_count[0][0]})) ? {ds_count[0][0]} : 0;

    // 2. Obtener los 9 registros de la página actual
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
                ORDER BY scl.inserted_date DESC
                LIMIT $inicio, $por_pagina";
    
    sc_lookup(ds_log, $sql_log);
    
    $res_data = array();
    if (!empty({ds_log})) {
        foreach ({ds_log} as $f) {
            $res_data[] = array(
                'date' => $f[0], 'suc' => $f[1], 'user' => $f[2],
                'ip'   => $f[3], 'act' => $f[4], 'desc' => $f[5]
            );
        }
    }
    
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
	echo json_encode([
		'data'          => $res_data,
		'total_pages'   => ceil($total_records / 9),
		'current_page'  => $pagina,
		'total_records' => $total_records
	]);
    exit;
}

// 2. CARGA INICIAL (SELECTORES)
$sql_grupos = "SELECT group_id, description FROM seguridad_groups WHERE modulo = $var_id_modulo_maestro";
sc_lookup(ds_grupos, $sql_grupos);

$sql_sucursales = "SELECT codigo, descripcion FROM configuracion_sucursal WHERE empresa = '[usr_empresa]' ORDER BY descripcion ASC";
sc_lookup(ds_suc, $sql_sucursales);

$sql_usuarios = "SELECT login, name FROM seguridad_users WHERE codigo_empresa = '[usr_empresa]' ORDER BY name ASC";
sc_lookup(ds_usu, $sql_usuarios);
?>

<style>
    .main-container { display: flex; gap: 20px; padding: 20px; font-family: 'Segoe UI', Tahoma, sans-serif; background: #f8f9fa; min-height: 100vh; }
    .selectors-column { flex: 0 0 300px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); height: fit-content; }
    .table-column { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    
    .filter-group { margin-bottom: 15px; }
    .filter-group label { display:block; font-weight:600; margin-bottom:5px; font-size: 13px; color: #495057; }
    .filter-group select, .filter-group input { width:100%; padding:8px; border:1px solid #ced4da; border-radius:4px; font-size: 13px; }

    .button-row { display: flex; gap: 10px; margin-top: 15px; }
    .btn-search { flex: 1; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .btn-exit { flex: 0 0 100px; padding: 10px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px; }
    
    .log-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
    .log-table th { background: <?php echo $var_table_back_color; ?>; color: <?php echo $var_table_fore_color; ?>; padding: 10px; text-align: left; }
    .log-table td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: top; }
    
    .badge-action { padding: 3px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
    .action-access { background: #d4edda; color: #155724; }
    .action-update { background: #fff3cd; color: #856404; }

    /* Estilos Paginación */
	/*
    .pagination-container { 
		display: flex; 
		justify-content: center; 
		align-items: center; 
		gap: 15px; 
		margin-top: 20px; 
		padding-top: 15px; 
		border-top: 1px solid #eee; 
	}*/
	.pagination-container { 
		display: flex; 
		justify-content: center; 
		align-items: center; 
		gap: 15px; 
		margin-top: 20px; 
		padding: 15px; /* Aumentado para que el fondo luzca mejor */
		border-top: 1px solid #eee;
		border-radius: 4px; 
	}	
	
    .btn-page { 
		padding: 5px 12px; 
		border: 1px solid #dee2e6; 
		background: white; 
		cursor: pointer; 
		border-radius: 4px; 
		background-color: #007bff; 
		color: white; 
	}
	
    .btn-page:disabled { 
		background: #e9ecef; 
		cursor: not-allowed; 
		color: #adb5bd; 
	}
</style>

<div class="main-container">
    <div class="selectors-column">
        <h3 style="margin:0 0 15px 0; font-size:18px; color:#333;">Auditoría</h3>
        
        <div class="filter-group">
            <label>Grupo de Seguridad:</label>
            <select id="sel_grupos" onchange="fn_get_apps(this.value)">
                <option value="">-- Seleccione --</option>
                <?php
                if (!empty({ds_grupos})) {
                    foreach ({ds_grupos} as $g) echo "<option value='{$g[0]}'>{$g[1]}</option>";
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Aplicación:</label>
            <select id="sel_apps" disabled>
                <option value="">-- Seleccione Grupo --</option>
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

        <div class="button-row">
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

function fn_get_apps(id) {
    const target = document.getElementById('sel_apps');
    if (!id) {
        target.disabled = true;
        target.innerHTML = '<option value="">-- Seleccione Grupo --</option>';
        return;
    }
    fetch(window.location.origin + window.location.pathname + '?ajax_fetch_apps=1&ajax_group_id=' + id)
        .then(r => r.json())
        .then(data => {
            target.disabled = false;
            target.innerHTML = '<option value="">-- Seleccione Aplicación --</option>';
            data.forEach(i => {
                let o = document.createElement('option');
                o.value = o.textContent = i.app_name;
                target.appendChild(o);
            });
        });
}

function fn_get_log(page = 1) {
    current_page = page;
    const app = document.getElementById('sel_apps').value;
    if (!app) { alert("Seleccione una aplicación."); return; }

    const container = document.getElementById('log_container');
    container.innerHTML = '<p style="text-align:center;">Cargando...</p>';

    const params = new URLSearchParams({
        ajax_fetch_log: 1,
        app_name: app,
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
				let html = `<table class="log-table">
					<thead>
						<tr>
							<th style="width: 80px; min-width: 80px;">Fecha/Hora</th>
							<th style="width: 110px; min-width: 110px;">Sucursal</th>
							<th style="width: 100px; min-width: 100px;">Usuario</th>
							<th>IP</th>
							<th>Acción</th>
							<th>Descripción</th>
						</tr>
					</thead>
					<tbody>`;

                res.data.forEach(row => {
                    const [fecha, hora] = row.date.split(' ');
                    const bCls = row.act === 'access' ? 'action-access' : (row.act === 'update' ? 'action-update' : '');
                    html += `<tr>
                        <td><strong>${fecha}</strong><br><small style="color:#888">${hora}</small></td>
                        <td>${row.suc || '-'}</td>
                        <td>${row.user || '-'}</td>
                        <td>${row.ip}</td>
                        <td><span class="badge-action ${bCls}">${row.act}</span></td>
                        <td style="font-family:monospace; font-size:11px; color:#666;">${row.desc || ''}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
				
				// ... después de cerrar la tabla (</table>)
				if (res.total_pages > 1) {
					html += `
					<div class="pagination-container" style="display: flex; justify-content: center; gap: 10px; padding: 20px;">
						<button onclick="fn_get_log(${res.current_page - 1})" ${res.current_page == 1 ? 'disabled' : ''}> Anterior </button>
						<span> Página ${res.current_page} de ${res.total_pages} </span>
						<button onclick="fn_get_log(${res.current_page + 1})" ${res.current_page == res.total_pages ? 'disabled' : ''}> Siguiente </button>
					</div>`;
				}							

                // Renderizar Paginación
                html += `<div class="pagination-container">
                    <button class="btn-page" onclick="fn_get_log(${res.current_page - 1})" ${res.current_page == 1 ? 'disabled' : ''}>&laquo; Anterior</button>
                    <span style="font-size:13px;">Página <strong>${res.current_page}</strong> de ${res.total_pages} (${res.total_records} registros)</span>
                    <button class="btn-page" onclick="fn_get_log(${res.current_page + 1})" ${res.current_page == res.total_pages ? 'disabled' : ''}>Siguiente &raquo;</button>
                </div>`;

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
</script>
<?php

?>