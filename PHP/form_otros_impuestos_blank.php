<?php
// =========================================================================
// 1. CONTROLADORES AJAX (Al inicio del Blank)
// =========================================================================

$usr_empresa_global = [usr_empresa];
$var_usr_login      = [usr_login];
$var_ip_estacion    = $_SERVER['REMOTE_ADDR'];

// --- ACCIÓN: Eliminar (POST) ---
if (isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $id = (int)$_POST['id'];
    sc_exec_sql("DELETE FROM configuracion_otros_impuestos WHERE id_otros_impuestos = $id AND empresa = '$usr_empresa_global'");
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

// --- ACCIÓN: Insertar o Actualizar (POST) ---
if (isset($_POST['action']) && ($_POST['action'] == 'insertar' || $_POST['action'] == 'actualizar')) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    ob_start(); 

    $action    = $_POST['action'];
    $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validación de campos vacíos en el servidor
    if (empty($_POST['nombre_impuesto']) || $_POST['impuesto'] === "" || empty($_POST['frecuencia']) || empty($_POST['fecha_inicio']) || $_POST['id_proveedor'] == "0" || empty($_POST['codigo_productos'])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos marcados con (*) son obligatorios.']);
        exit;
    }

    $v_nombre  = sc_sql_injection($_POST['nombre_impuesto']);
    $v_imp     = (float)$_POST['impuesto'];
    $v_prov    = (int)$_POST['id_proveedor'];
    $v_prod    = sc_sql_injection($_POST['codigo_productos']);
    $v_frec    = sc_sql_injection($_POST['frecuencia']);
    $v_f_ini   = sc_sql_injection($_POST['fecha_inicio']);

    if ($action == 'insertar') {
        $sql = "INSERT INTO configuracion_otros_impuestos 
                (nombre_impuesto, impuesto, id_proveedor, codigo_productos, fecha_inicio_impuesto, frecuencia, empresa, usuario, ip_estacion, fecha_registro) 
                VALUES ($v_nombre, $v_imp, $v_prov, $v_prod, $v_f_ini, $v_frec, '$usr_empresa_global', '$var_usr_login', '$var_ip_estacion', NOW())";
    } else {
        $sql = "UPDATE configuracion_otros_impuestos 
                SET nombre_impuesto = $v_nombre, 
                    impuesto = $v_imp,
                    id_proveedor = $v_prov,
                    codigo_productos = $v_prod,
                    fecha_inicio_impuesto = $v_f_ini,
                    frecuencia = $v_frec,
                    usuario = '$var_usr_login',
                    ip_estacion = '$var_ip_estacion'
                WHERE id_otros_impuestos = $id AND empresa = '$usr_empresa_global'";
    }
    
    sc_error_continue("sql");
    sc_exec_sql($sql);
    $error_sql = {sc_error_sql};
    ob_end_clean(); 

    header('Content-Type: application/json');
    echo json_encode(['status' => empty($error_sql) ? 'success' : 'error', 'message' => $error_sql]);
    exit;
}

// --- ACCIÓN: Carga de Tabla (GET) ---
if (isset($_GET['ajax_cargar_tabla'])) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    $sql_tabla = "SELECT 
                    coi.id_otros_impuestos, 
                    coi.nombre_impuesto, 
                    coi.impuesto, 
                    coi.id_proveedor, 
                    coi.codigo_productos, 
                    coi.fecha_inicio_impuesto,
                    coi.frecuencia,
                    pd.nombre_proveedor,
                    ip.nombre_productos
                  FROM configuracion_otros_impuestos coi
                  LEFT JOIN proveedores_datos pd ON coi.id_proveedor = pd.id_proveedor AND pd.empresa = coi.empresa
                  LEFT JOIN inventario_productos ip ON coi.codigo_productos = ip.codigo_productos 
                       AND ip.empresa = coi.empresa 
                       AND ip.producto_matriz = 'SI'
                  WHERE coi.empresa = '$usr_empresa_global'
                  GROUP BY coi.id_otros_impuestos
                  ORDER BY coi.id_otros_impuestos DESC";

    sc_lookup(ds_tabla, $sql_tabla);
    $datos_tabla = array();
    if (!empty({ds_tabla})) {
        foreach ({ds_tabla} as $fila) {
            $datos_tabla[] = array(
                'id'            => $fila[0],
                'nombre'        => $fila[1],
                'tasa'          => $fila[2],
                'id_prov'       => $fila[3],
                'cod_prod'      => $fila[4],
                'fecha_ini'     => $fila[5],
                'frecuencia'    => $fila[6],
                'nombre_prov'   => ($fila[7] ? $fila[7] : "N/A"),
                'nombre_prod'   => ($fila[8] ? $fila[8] : "N/A")
            );
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos_tabla);
    exit;
}

// =========================================================================
// 2. CONSULTAS PARA LLENAR SELECTORES
// =========================================================================
sc_lookup(ds_prov, "SELECT id_proveedor, nombre_proveedor FROM proveedores_datos WHERE empresa = '$usr_empresa_global' ORDER BY nombre_proveedor ASC");
sc_lookup(ds_prod, "SELECT codigo_productos, nombre_productos FROM inventario_productos 
WHERE empresa = '$usr_empresa_global' AND producto_matriz = 'SI' 
GROUP BY codigo_productos
ORDER BY nombre_productos ASC");

// =========================================================================
// 3. VISTA HTML & CSS
// =========================================================================
echo '
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<style>
    body { background-color: #f8f9fa; font-family: sans-serif; }
    .main-container { padding: 20px; }
    .table-container { background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .table-header-custom { background-color: #2c3e50; color: #ffffff; }
    .header-panel { background: #fff; padding: 15px; border-bottom: 2px solid #e2e8f0; border-radius: 8px 8px 0 0; }
    .badge-frec { font-size: 0.85rem; padding: 5px 10px; width: 100px; }
    .info-icon { color: #17a2b8; cursor: help; margin-left: 5px; font-size: 0.9rem; font-weight: bold; }
    .table td { vertical-align: middle; }
</style>

<div class="main-container">
    <div class="table-container">
        <div class="header-panel d-flex justify-content-between align-items-center">
            <h4 class="m-0 text-dark font-weight-bold">⚙ Otros Impuestos</h4>
            <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nuevo Impuesto</button>
        </div>
        
        <div class="table-responsive p-4">
            <table class="table table-hover table-bordered">
                <thead class="table-header-custom text-center">
                    <tr>
                        <th class="text-left">Nombre Impuesto</th>
                        <th class="text-left">Proveedor</th>
                        <th class="text-left">Producto</th>
                        <th class="text-left">Fecha Inicio</th>
                        <th>Frecuencia</th>
                        <th class="text-right">Impuesto (%)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody_impuestos">
                    <tr><td colspan="7" class="py-4 text-center text-muted">Cargando datos...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CRUD -->
<div class="modal fade" id="modalImpuesto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTitulo">Gestionar Impuesto</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="imp_id" value="">
                
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label class="font-weight-bold">Nombre del Impuesto *</label>
                        <span class="info-icon" data-toggle="tooltip" title="Nombre oficial del tributo según el ente regulador (ej: ISLR, CONATEL RF-..., FIDETEL y otros).">(?)</span>
                        <input type="text" id="imp_nombre" class="form-control" placeholder="Ej: CONATEL RF-006">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Proveedor *</label>
                        <span class="info-icon" data-toggle="tooltip" title="Seleccione el proveedor asociado a este impuesto, contra el cual se realizara el gasto">(?)</span>
                        <select id="imp_id_proveedor" class="form-control">
                            <option value="0">-- Seleccione Proveedor --</option>';
                            if (!empty({ds_prov})) {
                                foreach({ds_prov} as $p) { echo "<option value='".$p[0]."'>".$p[1]."</option>"; }
                            }
echo '                  </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Producto *</label>
                        <span class="info-icon" data-toggle="tooltip" title="Seleccione el producto de inventario, el cual estara vinculado al auxiliar contable">(?)</span>
                        <select id="imp_codigo_productos" class="form-control">
                            <option value="">-- Seleccione Producto --</option>';
                            if (!empty({ds_prod})) {
                                foreach({ds_prod} as $pr) { echo "<option value='".$pr[0]."'>".$pr[1]."</option>"; }
                            }
echo '                  </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Fecha Inicio Impuesto *</label>
                        <span class="info-icon" data-toggle="tooltip" title="Fecha exacta en la que empieza la vigencia del impuesto.">(?)</span>
                        <input type="date" id="imp_fecha_inicio" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Frecuencia de Pago *</label>
                        <span class="info-icon" data-toggle="tooltip" title="Indica cada cuánto tiempo se genera la obligación tributaria, Ej: Quincenal, Mensual. Trimestral, Anual">(?)</span>
                        <select id="imp_frecuencia" class="form-control">
                            <option value="">-- Seleccione Frecuencia --</option>
                            <option value="Quincenal">Quincenal</option>
                            <option value="Mensual">Mensual</option>
                            <option value="Trimestral">Trimestral</option>
                            <option value="Anual">Anual</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Impuesto (%) *</label>
                        <span class="info-icon" data-toggle="tooltip" title="Ingrese el valor porcentual (ej: 1.00). No incluya el símbolo %.">(?)</span>
                        <input type="number" step="0.01" id="imp_tasa" class="form-control" placeholder="0.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="guardarRegistro()">Guardar Impuesto</button>
            </div>
        </div>
    </div>
</div>

<script>
function getUrlLimpia() { return window.location.protocol + "//" + window.location.host + window.location.pathname; }

function cargarTabla() {
    let tbody = document.getElementById("tbody_impuestos");
    fetch(getUrlLimpia() + "?ajax_cargar_tabla=1")
    .then(res => res.json())
    .then(data => {
        tbody.innerHTML = "";
        if(data.length === 0) {
            tbody.innerHTML = "<tr><td colspan=\'7\' class=\'text-center\'>No hay impuestos configurados</td></tr>";
            return;
        }
		data.forEach(item => {
            let itemSafe = JSON.stringify(item).replace(/"/g, \'&quot;\');
            
            // Lógica de colores por frecuencia (Uso de comillas dobles para evitar error PHP)
            let colorClase = "badge-secondary"; 
            if (item.frecuencia === "Quincenal")  colorClase = "badge-primary"; 
            if (item.frecuencia === "Mensual")    colorClase = "badge-success"; 
            if (item.frecuencia === "Trimestral") colorClase = "badge-warning"; 
            if (item.frecuencia === "Anual")      colorClase = "badge-danger"; 

            tbody.innerHTML += `<tr>
                <td class="text-left font-weight-bold">${item.nombre}</td>
                <td class="text-left"><small>${item.nombre_prov}</small></td>
                <td class="text-left"><small>${item.nombre_prod}</small></td>
                <td class="text-left">${item.fecha_ini || "---"}</td>
                <td class="text-center"><span class="badge ${colorClase} badge-frec">${item.frecuencia}</span></td>
                <td class="text-right font-weight-bold text-primary">${parseFloat(item.tasa).toFixed(2)}%</td>
                <td class="text-center">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="prepararEdicion(${itemSafe})">✎</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarRegistro(${item.id})">×</button>
                    </div>
                </td>
            </tr>`;
        });
        // Reinicializar tooltips para nuevos elementos
        $(\'[data-toggle="tooltip"]\').tooltip();
    });
}

function abrirModalNuevo() {
    document.getElementById("imp_id").value = "";
    document.getElementById("imp_nombre").value = "";
    document.getElementById("imp_tasa").value = "";
    document.getElementById("imp_id_proveedor").value = "0";
    document.getElementById("imp_codigo_productos").value = "";
    document.getElementById("imp_frecuencia").value = "";
    document.getElementById("imp_fecha_inicio").value = "";
    $("#modalTitulo").text("Nuevo Impuesto");
    $("#modalImpuesto").modal("show");
}

function prepararEdicion(item) {
    document.getElementById("imp_id").value = item.id;
    document.getElementById("imp_nombre").value = item.nombre;
    document.getElementById("imp_tasa").value = item.tasa;
    document.getElementById("imp_id_proveedor").value = item.id_prov;
    document.getElementById("imp_codigo_productos").value = item.cod_prod;
    document.getElementById("imp_frecuencia").value = item.frecuencia;
    document.getElementById("imp_fecha_inicio").value = item.fecha_ini;
    $("#modalTitulo").text("Editar Impuesto");
    $("#modalImpuesto").modal("show");
}

function guardarRegistro() {
    let id     = document.getElementById("imp_id").value;
    let nom    = document.getElementById("imp_nombre").value;
    let tasa   = document.getElementById("imp_tasa").value;
    let prov   = document.getElementById("imp_id_proveedor").value;
    let prod   = document.getElementById("imp_codigo_productos").value;
    let frec   = document.getElementById("imp_frecuencia").value;
    let fecha  = document.getElementById("imp_fecha_inicio").value;

    // Validación de todos los campos obligatorios
    if (!nom || tasa === "" || prov === "0" || !prod || !frec || !fecha) {
        return alert("Error: Todos los campos marcados con (*) son obligatorios.");
    }

    let formData = new FormData();
    formData.append("action", id ? "actualizar" : "insertar");
    if (id) formData.append("id", id);
    formData.append("nombre_impuesto", nom);
    formData.append("impuesto", tasa);
    formData.append("id_proveedor", prov);
    formData.append("codigo_productos", prod);
    formData.append("frecuencia", frec);
    formData.append("fecha_inicio", fecha);

    fetch(getUrlLimpia(), { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success") {
            $("#modalImpuesto").modal("hide");
            cargarTabla();
        } else {
            alert("Error del Servidor: " + data.message);
        }
    }).catch(err => alert("Error en la petición: " + err));
}

function eliminarRegistro(id) {
    if(!confirm("¿Está seguro de eliminar este registro permanentemente?")) return;
    let formData = new FormData();
    formData.append("action", "eliminar");
    formData.append("id", id);
    fetch(getUrlLimpia(), { method: "POST", body: formData }).then(() => cargarTabla());
}

// Carga inicial e inicialización de tooltips
document.addEventListener("DOMContentLoaded", function() {
    cargarTabla();
    $(\'[data-toggle="tooltip"]\').tooltip();
});
</script>
';
?>