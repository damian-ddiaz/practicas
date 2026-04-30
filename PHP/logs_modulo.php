<?php
// 1. Variables de configuración
$var_id_modulo_maestro = 15;

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

// NUEVO: AJAX para obtener el log de inventario (MODIFICADO)
if (isset($_GET['ajax_fetch_log'])) 
{
    $app_name = (isset($_GET['app_name'])) ? $_GET['app_name'] : '';
    
    // Nueva consulta con extracción de Sucursal y Usuario
    $sql_log = "SELECT 
                    inserted_date, 
                    SUBSTRING_INDEX(SUBSTRING_INDEX(username, 'Suc: ', -1), ' - ', 1) AS sucursal, 
                    SUBSTRING_INDEX(username, 'Usu: ', -1) AS usuario, 
                    ip_user, 
                    action, 
                    description  
                FROM sc_log_inventario 
                WHERE application = '$app_name'
                ORDER BY inserted_date DESC";
    
    sc_lookup(ds_log, $sql_log);
    
    $res_log = array();
    if (!empty({ds_log})) {
        foreach ({ds_log} as $f) {
            $res_log[] = array(
                'date' => $f[0],
                'suc'  => $f[1], // Sucursal extraída
                'user' => $f[2], // Usuario extraído
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

// 2. CARGA DE DATOS PARA EL PRIMER SELECTOR
$sql_grupos = "SELECT group_id, description FROM seguridad_groups WHERE modulo = " . $var_id_modulo_maestro;
sc_lookup(ds_grupos, $sql_grupos);
?>

<!-- INTERFAZ HTML Y JAVASCRIPT -->
<style>
    .main-container { display: flex; gap: 30px; padding: 30px; font-family: sans-serif; }
    .selectors-column { flex: 0 0 350px; }
    .table-column { flex: 1; overflow-x: auto; }
    .log-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .log-table th { background-color: #2c3e50; color: white; text-align: left; padding: 10px; }
    .log-table td { padding: 10px; border-bottom: 1px solid #ddd; }
    .log-table tr:hover { background-color: #f1f1f1; }
    .badge-action { padding: 4px 8px; border-radius: 4px; font-weight: bold; text-transform: uppercase; font-size: 11px; }
    .action-access { background: #e8f5e9; color: #2e7d32; }
    .action-update { background: #fff3e0; color: #ef6c00; }
</style>

<div class="main-container">
    <div class="selectors-column">
        <div style="margin-bottom: 20px;">
            <label style="display:block; font-weight:bold; margin-bottom:8px;">Seleccione un Grupo:</label>
            <select id="sel_grupos" onchange="fn_get_apps(this.value)" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                <option value="">-- Seleccione un grupo --</option>
                <?php
                if (!empty({ds_grupos})) {
                    foreach ({ds_grupos} as $g) {
                        echo "<option value='{$g[0]}'>{$g[1]}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div>
            <label style="display:block; font-weight:bold; margin-bottom:8px;">Nombre de la Aplicación:</label>
            <select id="sel_apps" disabled onchange="fn_get_log(this.value)" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background-color: #f9f9f9;">
                <option value="">-- Seleccione un grupo primero --</option>
            </select>
        </div>
    </div>

    <div class="table-column">
        <div id="log_container">
            <p style="color: #666; font-style: italic;">Seleccione una aplicación para ver el historial.</p>
        </div>
    </div>
</div>

<script>
function fn_get_apps(id) {
    const target = document.getElementById('sel_apps');
    document.getElementById('log_container').innerHTML = '<p style="color: #666; font-style: italic;">Seleccione una aplicación para ver el historial.</p>';
    if (!id) {
        target.disabled = true;
        target.innerHTML = '<option value="">-- Seleccione un grupo primero --</option>';
        return;
    }
    const url = window.location.origin + window.location.pathname + '?ajax_fetch_apps=1&ajax_group_id=' + id;
    fetch(url).then(r => r.json()).then(data => {
        target.disabled = false;
        target.style.backgroundColor = "#fff";
        target.innerHTML = '<option value="">-- Seleccione Aplicación --</option>';
        if (data && data.length > 0) {
            data.forEach(i => {
                let o = document.createElement('option');
                o.value = i.app_name;
                o.textContent = i.app_name;
                target.appendChild(o);
            });
        }
    });
}

function fn_get_log(appName) {
    const container = document.getElementById('log_container');
    if (!appName) {
        container.innerHTML = '<p style="color: #666; font-style: italic;">Seleccione una aplicación para ver el historial.</p>';
        return;
    }
    container.innerHTML = 'Cargando registros...';
    const url = window.location.origin + window.location.pathname + '?ajax_fetch_log=1&app_name=' + encodeURIComponent(appName);

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data && data.length > 0) {
                let html = `
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Sucursal</th>
                                <th>Usuario</th>
                                <th>IP</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>`;

                data.forEach(row => {
                    let badgeClass = row.act === 'access' ? 'action-access' : (row.act === 'update' ? 'action-update' : '');
                    html += `
                        <tr>
                            <td style="color:#e67e22;">${row.date}</td>
                            <td style="font-weight:bold; color:#34495e;">${row.suc}</td>
                            <td style="color:#27ae60;">${row.user}</td>
                            <td style="color:#2980b9;">${row.ip}</td>
                            <td><span class="badge-action ${badgeClass}">${row.act}</span></td>
                            <td style="color:#7f8c8d; font-family:monospace;">${row.desc || ''}</td>
                        </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div style="padding:20px; border:1px dashed #ccc; text-align:center; color:#999;">No hay registros para esta aplicación.</div>';
            }
        })
        .catch(e => {
            container.innerHTML = "Error al cargar el log.";
        });
}
</script>
<?php




?>