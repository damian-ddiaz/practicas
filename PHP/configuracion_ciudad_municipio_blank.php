<?php
// =========================================================================
// 1. CONTROLADORES AJAX (Deben estar al extremo inicio del Blank)
// =========================================================================

$usr_empresa_global = [usr_empresa]; 
$emp_pais_global    = [emp_pais];
$var_usr_login      = [usr_login];
$var_ip_estacion    = $_SERVER['REMOTE_ADDR'];

// --- ACCIÓN: Eliminar Ciudad (POST) ---
if (isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $id = (int)$_POST['id'];
    sc_exec_sql("DELETE FROM configuracion_ciudad WHERE id_configuracion_ciudad = $id");
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
    
    // Captura de valores originales para validación de vacíos
    $raw_est_id = isset($_POST['estado_id']) ? trim($_POST['estado_id']) : '';
    $raw_codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
    $raw_nombre = isset($_POST['nombre_ciudad']) ? trim($_POST['nombre_ciudad']) : '';
    $raw_codciu = isset($_POST['cod_ciudad']) ? trim($_POST['cod_ciudad']) : '';
    $raw_promo  = isset($_POST['aplica_promocion']) ? trim($_POST['aplica_promocion']) : '';

    // 1. VALIDACIÓN: Campos Obligatorios
    if ($raw_est_id === '' || $raw_codigo === '' || $raw_nombre === '' || $raw_codciu === '' || $raw_promo === '') {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    // Preparación de variables con limpieza SQL
    $v_est_id  = sc_sql_injection($raw_est_id); 
    $v_codigo  = sc_sql_injection($raw_codigo); 
    $v_nombre  = sc_sql_injection($raw_nombre);
    $v_cod_ciu = sc_sql_injection($raw_codciu);
    $v_promo   = sc_sql_injection($raw_promo);

    // 2. VALIDACIÓN DE DUPLICADOS
    $condicion_extra = ($action == 'actualizar') ? " AND id_configuracion_ciudad <> $id" : "";
    
    // Validar duplicado de 'codigo'
    sc_lookup(ds_check_cod, "SELECT COUNT(*) FROM configuracion_ciudad WHERE codigo = $v_codigo $condicion_extra");
    if (!empty({ds_check_cod}) && {ds_check_cod}[0][0] > 0) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'El "Código" ya existe.']);
        exit;
    }

    // Validar duplicado de 'cod_ciudad'
    sc_lookup(ds_check_ciu, "SELECT COUNT(*) FROM configuracion_ciudad WHERE cod_ciudad = $v_cod_ciu $condicion_extra");
    if (!empty({ds_check_ciu}) && {ds_check_ciu}[0][0] > 0) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'El "Cod. Ciudad" ya existe.']);
        exit;
    }

    // 3. EJECUCIÓN DEL SQL
    if ($action == 'insertar') {
        $sql = "INSERT INTO configuracion_ciudad 
                (codigo, nombre_ciudad, codigo_estado, cod_ciudad, usuario, fecha, ip_estacion, aplica_promocion) 
                VALUES ($v_codigo, $v_nombre, $v_est_id, $v_cod_ciu, '$var_usr_login', NOW(), '$var_ip_estacion', $v_promo)";
    } else {
        $sql = "UPDATE configuracion_ciudad 
                SET codigo = $v_codigo, 
                    nombre_ciudad = $v_nombre, 
                    cod_ciudad = $v_cod_ciu, 
                    aplica_promocion = $v_promo,
                    usuario = '$var_usr_login',
                    fecha = NOW(),
                    ip_estacion = '$var_ip_estacion'
                WHERE id_configuracion_ciudad = $id";
    }

    sc_error_continue("sql");
    sc_exec_sql($sql);
    $error_sql = {sc_error_sql};
    ob_end_clean(); 

    header('Content-Type: application/json');
    echo json_encode(['status' => empty($error_sql) ? 'success' : 'error', 'message' => $error_sql]);
    exit;
} // <-- AQUÍ FALTABA ESTA LLAVE QUE CAUSABA EL ERROR

// --- ACCIÓN: Carga de Tabla de Ciudades (GET) ---
if (isset($_GET['ajax_cargar_ciudades']) && isset($_GET['estado_id'])) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $est_id = sc_sql_injection($_GET['estado_id']);
	
	$sql_tabla = "SELECT id_configuracion_ciudad, codigo, nombre_ciudad, cod_ciudad, aplica_promocion 
                  FROM configuracion_ciudad 
                  WHERE codigo_estado = $est_id 
                  ORDER BY nombre_ciudad ASC";
		
    sc_lookup(ds_tabla, $sql_tabla);
    $datos_tabla = array();
    if (!empty({ds_tabla})) {
		foreach ({ds_tabla} as $fila) {
			$datos_tabla[] = array(
				'id'        => $fila[0],
				'codigo'    => $fila[1],
				'nombre'    => $fila[2],
				'cod_ciu'   => $fila[3],
				'promo'     => $fila[4]
			);
		}
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos_tabla);
    exit;
}

// Carga inicial de estados para el selector
sc_lookup(ds_estados, "SELECT codigo_estado, nombre FROM configuracion_estado WHERE iso3 = '$emp_pais_global' ORDER BY nombre ASC");

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
        <h5 class="main-title">⇄ Ubicación</h5>
        <div class="card-selector"> 
            <div class="form-group">
                <label>Estado:</label>
                <select id="sel_estado" class="form-control" onchange="cargarTablaCiudades(this.value)">
                    <option value="">-- Seleccione Estado --</option>';
                    if (!empty({ds_estados})) {
                        foreach({ds_estados} as $estado) { echo "<option value='".$estado[0]."'>".$estado[1]."</option>"; }
                    }
echo '          </select>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="p-3 bg-light d-flex justify-content-between align-items-center border-bottom">
            <span class="font-weight-bold">📋 Ciudades del Estado</span>
            <button class="btn btn-success btn-sm" onclick="abrirModalNuevo()">+ Nueva Ciudad</button>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-bordered table-striped m-0">
                <thead class="table-header-custom">
                    <tr>
                        <th>Código</th>
                        <th>Nombre Ciudad</th>
                        <th>Cod. Ciudad</th>
                        <th class="text-center">Promo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody_ciudades">
                    <tr><td colspan="5" class="text-center text-muted py-4">Seleccione un estado para ver las ciudades</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCiudad" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Gestión de Ciudad</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ciu_id" value="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Código</label>
                            <input type="text" id="ciu_codigo" class="form-control" maxlength="20">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Codigo Ciudad</label>
                            <input type="text" id="ciu_cod_ciudad" class="form-control" maxlength="10">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nombre de la Ciudad</label>
                    <input type="text" id="ciu_nombre" class="form-control" maxlength="254">
                </div>
                <div class="form-group">
                    <label>¿Aplica Promoción?</label>
                    <select id="ciu_promo" class="form-control">
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCiudad()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
function getUrlLimpia() { return window.location.protocol + "//" + window.location.host + window.location.pathname; }

function cargarTablaCiudades(estado_id) {
    let tbody = document.getElementById("tbody_ciudades");
    if (!estado_id) {
        tbody.innerHTML = "<tr><td colspan=\'5\' class=\'text-center text-muted py-4\'>Seleccione un estado</td></tr>";
        return;
    }
    tbody.innerHTML = "<tr><td colspan=\'5\' class=\'text-center\'>Cargando...</td></tr>";
    fetch(getUrlLimpia() + "?ajax_cargar_ciudades=1&estado_id=" + estado_id)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = "";
            if(data.length === 0) { 
                tbody.innerHTML = "<tr><td colspan=\'5\' class=\'text-center\'>Sin ciudades registradas</td></tr>"; 
                return; 
            }
            data.forEach(item => {
                let badgeClass = item.promo === "SI" ? "badge-success" : "badge-secondary";
                tbody.innerHTML += `<tr>
                    <td>${item.codigo}</td>
                    <td>${item.nombre}</td>
                    <td>${item.cod_ciu || ""}</td>
                    <td class="text-center"><span class="badge ${badgeClass}">${item.promo}</span></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick=\'prepararEdicion(${JSON.stringify(item)})\'>✎</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarCiudad(${item.id})">×</button>
                        </div>
                    </td>
                </tr>`;
            });
        });
}

function abrirModalNuevo() {
    if (!document.getElementById("sel_estado").value) return alert("Seleccione un estado primero");
    document.getElementById("ciu_id").value = "";
    document.getElementById("ciu_codigo").value = "";
    document.getElementById("ciu_nombre").value = "";
    document.getElementById("ciu_cod_ciudad").value = "";
    document.getElementById("ciu_promo").value = "NO";
    $(".modal-title").text("Nueva Ciudad");
    $("#modalCiudad").modal("show");
}

function prepararEdicion(item) {
    document.getElementById("ciu_id").value = item.id;
    document.getElementById("ciu_codigo").value = item.codigo;
    document.getElementById("ciu_nombre").value = item.nombre;
    document.getElementById("ciu_cod_ciudad").value = item.cod_ciu;
    document.getElementById("ciu_promo").value = item.promo || "NO";
    $(".modal-title").text("Editar Ciudad");
    $("#modalCiudad").modal("show");
}

function eliminarCiudad(id) {
    if (!confirm("¿Está seguro de eliminar esta ciudad?")) return;
    let formData = new FormData();
    formData.append("action", "eliminar");
    formData.append("id", id);
    fetch(getUrlLimpia(), { method: "POST", body: formData })
        .then(() => cargarTablaCiudades(document.getElementById("sel_estado").value));
}

function guardarCiudad() {
    let id     = document.getElementById("ciu_id").value;
    let cod    = document.getElementById("ciu_codigo").value;
    let nom    = document.getElementById("ciu_nombre").value;
    let codCiu = document.getElementById("ciu_cod_ciudad").value;
    let promo  = document.getElementById("ciu_promo").value;
    let est    = document.getElementById("sel_estado").value;

    let formData = new FormData();
    formData.append("action", id ? "actualizar" : "insertar");
    if (id) formData.append("id", id);
    formData.append("estado_id", est);
    formData.append("codigo", cod);
    formData.append("nombre_ciudad", nom);
    formData.append("cod_ciudad", codCiu);
    formData.append("aplica_promocion", promo);

    fetch(getUrlLimpia(), { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            $("#modalCiudad").modal("hide");
            cargarTablaCiudades(est);
        } else {
            alert("Error: " + data.message);
        }
    }).catch(e => console.error(e));
}
</script>';
?>