<?php
// =========================================================================
// 1. CONTROLADORES AJAX (Deben estar al extremo inicio del Blank)
// =========================================================================

$usr_empresa_global = [usr_empresa];
$emp_pais_global    = [emp_pais];
$var_usr_login      = [usr_login];
$var_ip_estacion    = $_SERVER['REMOTE_ADDR'];

// --- ACCIÓN: Eliminar registro (POST) ---
if (isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $id = (int)$_POST['id'];
    sc_exec_sql("DELETE FROM configuracion_municipio_clasificador WHERE id_clasificacion = $id AND empresa = '$usr_empresa_global'");
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

// --- ACCIÓN: Insertar o Actualizar registro (POST) ---
if (isset($_POST['action']) && ($_POST['action'] == 'insertar' || $_POST['action'] == 'actualizar')) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    ob_start(); 

    $action    = $_POST['action'];
    $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $v_emp     = $usr_empresa_global;
    $v_est     = $_POST['estado_id']; 
    $v_muni    = $_POST['muni_cod']; 
    $v_nom     = sc_sql_injection($_POST['nombre']);
    $v_tipo    = sc_sql_injection($_POST['tipo_prod']);
    $v_imp     = (float)$_POST['impuesto'];

    if ($action == 'insertar') {
        $sql = "INSERT INTO configuracion_municipio_clasificador 
                (empresa, codigo_estado, cod_municipio, nombre_clasificacion, codigo_tipo_productos, impuesto, usuario, ip_estacion) 
                VALUES ('$v_emp', '$v_est', '$v_muni', $v_nom, $v_tipo, $v_imp, '$var_usr_login', '$var_ip_estacion')";
    } else {
        $sql = "UPDATE configuracion_municipio_clasificador 
                SET nombre_clasificacion = $v_nom, codigo_tipo_productos = $v_tipo, impuesto = $v_imp 
                WHERE id_clasificacion = $id AND empresa = '$v_emp'";
    }
    
    sc_error_continue("sql");
    sc_exec_sql($sql);
    $error_sql = {sc_error_sql};
    ob_end_clean(); 

    header('Content-Type: application/json');
    echo json_encode(['status' => empty($error_sql) ? 'success' : 'error', 'message' => $error_sql]);
    exit;
}

// --- ACCIÓN: Carga de Tabla de Clasificación (GET) ---
if (isset($_GET['ajax_municipio']) && isset($_GET['estado_id'])) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $muni_cod = sc_sql_injection($_GET['ajax_municipio']);
    $est_id   = sc_sql_injection($_GET['estado_id']);
    $usr_emp  = $usr_empresa_global;    
	
	// Agregamos cmc.codigo_tipo_productos al final para que el JS lo use al editar
	$sql_tabla = "SELECT cmc.id_clasificacion, cmc.nombre_clasificacion, itp.nombre_tipo_productos, cmc.impuesto, cmc.codigo_tipo_productos 
                  FROM configuracion_municipio_clasificador cmc
                  INNER JOIN inventario_tipo_productos itp ON itp.codigo_tipo_productos = cmc.codigo_tipo_productos
                  WHERE cmc.empresa = '$usr_emp' 
                  AND cmc.codigo_estado = $est_id 
                  AND cmc.cod_municipio = $muni_cod 
                  AND itp.empresa = '$usr_emp'
                  GROUP BY cmc.id_clasificacion
                  ORDER BY cmc.nombre_clasificacion ASC";
		
    sc_lookup(ds_tabla, $sql_tabla);
    $datos_tabla = array();
    if (!empty({ds_tabla})) {
		foreach ({ds_tabla} as $fila) {
			$datos_tabla[] = array(
				'id'          => $fila[0],
				'nombre'      => $fila[1],
				'tipo_nombre' => $fila[2],
				'impuesto'    => $fila[3],
				'tipo_cod'    => $fila[4]
			);
		}
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos_tabla);
    exit;
}

// --- ACCIÓN: Carga de Municipios (GET) ---
if (isset($_GET['ajax_estado'])) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $estado_id = sc_sql_injection($_GET['ajax_estado']);
    $sql_muni = "SELECT cod_municipio, nombre_municipio FROM configuracion_municipio WHERE codigo_estado = $estado_id ORDER BY nombre_municipio ASC";
    sc_lookup(ds_muni, $sql_muni);
    $municipios = array();
    if (!empty({ds_muni})) {
        foreach ({ds_muni} as $fila) {
            $municipios[] = array('codigo' => $fila[0], 'nombre' => $fila[1]);
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($municipios);
    exit;    
}

// Carga inicial de datos para la vista
sc_lookup(ds_estados, "SELECT codigo_estado, nombre FROM configuracion_estado WHERE iso3 = '$emp_pais_global' ORDER BY nombre ASC");
sc_lookup(ds_tipos_prod, "SELECT nombre_tipo_productos, codigo_tipo_productos FROM inventario_tipo_productos WHERE empresa = 'tecnoven' GROUP BY codigo_tipo_productos ORDER BY nombre_tipo_productos ASC");

// =========================================================================
// 2. VISTA HTML & CSS
// =========================================================================
echo '
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<style>
    .card-selector { background: #fff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; width: 350px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .main-title { font-weight: 700; color: #1e293b; font-size: 1.15rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; }
    .main-wrapper { display: flex; gap: 25px; padding: 10px; align-items: flex-start; }
    .table-container { flex: 1; background: #fff; border: 1px solid #dee2e6; border-radius: 5px; }
    .table-header-custom { background-color: #34495e; color: #ffffff; }
</style>

<div class="main-wrapper">
    <div>
        <h5 class="main-title">⇄ Clasificación de Servicios</h5>
        <div class="card-selector"> 
            <div class="form-group">
                <label>Estado:</label>
                <select id="sel_estado" class="form-control" onchange="cargarMunicipios(this.value)">
                    <option value="">-- Seleccione Estado --</option>';
                    if (!empty({ds_estados})) {
                        foreach({ds_estados} as $estado) { echo "<option value='".$estado[0]."'>".$estado[1]."</option>"; }
                    }
echo '          </select>
            </div>
            <div class="form-group">
                <label>Municipio:</label>
                <select id="sel_municipio" class="form-control" onchange="cargarTablaClasificacion(this.value)">
                    <option value="">-- Seleccione un Municipio --</option>
                </select>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="p-3 bg-light d-flex justify-content-between align-items-center border-bottom">
            <span class="font-weight-bold">📋 Clasificador de Actividades</span>
            <button class="btn btn-success btn-sm" onclick="abrirModalNuevo()">+ Nuevo Registro</button>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-bordered table-striped m-0">
                <thead class="table-header-custom">
                    <tr><th>Clasificación</th><th>Tipo de Productos</th><th class="text-right">Impuesto (%)</th><th class="text-center">Acciones</th></tr>
                </thead>
                <tbody id="tbody_clasificacion">
                    <tr><td colspan="4" class="text-center text-muted py-4">Seleccione estado y municipio</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nuevo Registro</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ins_id" value="">
                <div class="form-group"><label>Nombre Clasificación</label><input type="text" id="ins_nombre" class="form-control"></div>
                <div class="form-group">
                    <label>Tipo Productos</label>
                    <select id="ins_tipo" class="form-control">
                        <option value="">-- Seleccione un tipo --</option>';
                        if (!empty({ds_tipos_prod})) {
                            foreach({ds_tipos_prod} as $tp) { echo "<option value='".$tp[1]."'>".$tp[0]."</option>"; }
                        }
echo '              </select>
                </div>
                <div class="form-group"><label>Impuesto (%)</label><input type="number" step="0.01" id="ins_impuesto" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarRegistro()">Guardar</button>
            </div>
        </div>
    </div>
</div>';

// =========================================================================
// 3. JAVASCRIPT
// =========================================================================
echo '
<script>
function getUrlLimpia() { return window.location.protocol + "//" + window.location.host + window.location.pathname; }

function cargarMunicipios(codigo_estado) {
    let selectMuni = document.getElementById("sel_municipio");
    if (!codigo_estado) return;
    fetch(getUrlLimpia() + "?ajax_estado=" + codigo_estado)
        .then(res => res.json())
        .then(data => {
            selectMuni.innerHTML = "<option value=\"\">-- Seleccione Municipio --</option>";
            data.forEach(muni => { selectMuni.innerHTML += `<option value="${muni.codigo}">${muni.nombre}</option>`; });
        });
}

function cargarTablaClasificacion(cod_municipio) {
    let tbody = document.getElementById("tbody_clasificacion");
    let estado_id = document.getElementById("sel_estado").value;
    if (!cod_municipio) return;
    tbody.innerHTML = "<tr><td colspan=\'4\' class=\'text-center\'>Cargando...</td></tr>";

    fetch(getUrlLimpia() + "?ajax_municipio=" + cod_municipio + "&estado_id=" + estado_id)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = "";
            if(data.length === 0) { tbody.innerHTML = "<tr><td colspan=\'4\' class=\'text-center\'>Sin datos</td></tr>"; return; }
            data.forEach(item => {
                tbody.innerHTML += `<tr>
                    <td>${item.nombre}</td>
                    <td>${item.tipo_nombre}</td>
                    <td class="text-right">${parseFloat(item.impuesto).toFixed(2)}%</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick=\'prepararEdicion(${JSON.stringify(item)})\'>✎</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarRegistro(${item.id})">×</button>
                        </div>
                    </td>
                </tr>`;
            });
        });
}

function abrirModalNuevo() {
    if (!document.getElementById("sel_municipio").value) return alert("Seleccione Estado y Municipio");
    document.getElementById("ins_id").value = "";
    document.getElementById("ins_nombre").value = "";
    document.getElementById("ins_tipo").value = "";
    document.getElementById("ins_impuesto").value = "0.00";
    $(".modal-title").text("Nuevo Registro");
    $("#modalNuevo").modal("show");
}

function prepararEdicion(item) {
    document.getElementById("ins_id").value = item.id;
    document.getElementById("ins_nombre").value = item.nombre;
    document.getElementById("ins_tipo").value = item.tipo_cod;
    document.getElementById("ins_impuesto").value = item.impuesto;
    $(".modal-title").text("Editar Registro");
    $("#modalNuevo").modal("show");
}

function eliminarRegistro(id) {
    if (!confirm("¿Está seguro de eliminar esta clasificación?")) return;
    let formData = new FormData();
    formData.append("action", "eliminar");
    formData.append("id", id);
    fetch(getUrlLimpia(), { method: "POST", body: formData })
        .then(() => cargarTablaClasificacion(document.getElementById("sel_municipio").value));
}

function guardarRegistro() {
    let id = document.getElementById("ins_id").value;
    let nom = document.getElementById("ins_nombre").value;
    let imp = document.getElementById("ins_impuesto").value;
    let tip = document.getElementById("ins_tipo").value;

    if (!nom || imp === "" || !tip) return alert("Todos los campos son obligatorios");

    let formData = new FormData();
    formData.append("action", id ? "actualizar" : "insertar");
    if (id) formData.append("id", id);
    formData.append("estado_id", document.getElementById("sel_estado").value);
    formData.append("muni_cod", document.getElementById("sel_municipio").value);
    formData.append("nombre", nom);
    formData.append("tipo_prod", tip);
    formData.append("impuesto", imp);

    fetch(getUrlLimpia(), { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            $("#modalNuevo").modal("hide");
            cargarTablaClasificacion(document.getElementById("sel_municipio").value);
        } else {
            alert("Error: " + data.message);
        }
    }).catch(e => console.error(e));
}
</script>
';
?>