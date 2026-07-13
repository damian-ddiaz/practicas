<?php
// =========================================================================
// 1. CONTROLADORES AJAX (Deben estar al extremo inicio del Blank)
// =========================================================================

// Asignación de variables globales de Scriptcase a locales para evitar errores de parseo
$usr_empresa_global = [usr_empresa];
$emp_pais_global    = [emp_pais];

// --- ACCIÓN: Insertar nuevo registro (POST) ---
if (isset($_POST['action']) && $_POST['action'] == 'insertar') {
    // 1. Limpieza total y absoluta de cualquier salida previa
    while (ob_get_level() > 0) { ob_end_clean(); }
    ob_start(); // Iniciamos un nuevo buffer para capturar basura

    // 2. Variables locales (usando los globales definidos al inicio del Blank)
    $v_emp   = (int)$usr_empresa_global;
    $v_est   = (int)$_POST['estado_id'];
    $v_muni  = (int)$_POST['muni_cod'];
    $v_imp   = (float)$_POST['impuesto'];
    $v_nom   = sc_sql_injection($_POST['nombre']);
    $v_tipo  = sc_sql_injection($_POST['tipo_prod']);

    $sql_ins = "INSERT INTO configuracion_municipio_clasificador 
                (empresa, codigo_estado, cod_municipio, nombre_clasificacion, codigo_tipo_productos, impuesto) 
                VALUES ($v_emp, $v_est, $v_muni, $v_nom, $v_tipo, $v_imp)";
    
    // 3. Ejecutar silenciando errores de salida de Scriptcase
    sc_error_continue("sql");
    sc_exec_sql($sql_ins);
    
    // Capturamos el error si existe
    $error_sql = {sc_error_sql};
    
    // 4. Limpiamos cualquier "eco" o mensaje que sc_exec_sql haya escupido
    ob_end_clean(); 

    header('Content-Type: application/json');
    if (!empty($error_sql)) {
        echo json_encode(['status' => 'error', 'message' => $error_sql]);
    } else {
        echo json_encode(['status' => 'success']);
    }
    exit;
}
// --- ACCIÓN: Carga de Tabla de Clasificación (GET) ---
if (isset($_GET['ajax_municipio']) && isset($_GET['estado_id'])) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    $muni_cod = sc_sql_injection($_GET['ajax_municipio']);
    $est_id   = sc_sql_injection($_GET['estado_id']);
    $usr_emp  = (int)$usr_empresa_global;
    
    $sql_tabla = "SELECT id_clasificacion, nombre_clasificacion, codigo_tipo_productos, impuesto 
                  FROM configuracion_municipio_clasificador 
                  WHERE empresa = $usr_emp 
                    AND codigo_estado = $est_id 
                    AND cod_municipio = $muni_cod 
                  ORDER BY nombre_clasificacion ASC";
                  
    sc_lookup(ds_tabla, $sql_tabla);
    
    $datos_tabla = array();
    if (!empty({ds_tabla})) {
        foreach ({ds_tabla} as $fila) {
            $datos_tabla[] = array(
                'nombre' => $fila[1],
                'codigo_tipo_productos' => $fila[2],
                'impuesto' => $fila[3]
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
    
    $sql_muni = "SELECT cod_municipio, nombre_municipio 
                 FROM configuracion_municipio 
                 WHERE codigo_estado = $estado_id 
                 ORDER BY nombre_municipio ASC";
                 
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

// =========================================================================
// 2. VISTA HTML & CSS
// =========================================================================
echo '
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<style>
    .card-selector { background: #fff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; width: 350px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .main-title-container { width: 350px; margin-bottom: 15px; padding-left: 5px; }
    .main-title { font-weight: 700; color: #1e293b; font-size: 1.15rem; display: flex; align-items: center; margin: 0; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0; }
    .main-title-icon { font-size: 1.25rem; margin-right: 10px; color: #1e293b; display: inline-block; }       
    .main-wrapper { display: flex; gap: 25px; align-items: flex-start; padding: 10px; }
    .table-container { flex: 1; background: #fff; border: 1px solid #dee2e6; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .table-header-custom { background-color: #34495e; color: #ffffff; }
</style>
';

sc_lookup(ds_estados, "SELECT codigo_estado, nombre FROM configuracion_estado WHERE iso3 = '$emp_pais_global' ORDER BY nombre ASC");
    
echo '
<div class="main-wrapper">
    <div>
        <div class="main-title-container">
            <h5 class="main-title"><span class="main-title-icon">⇄</span> Clasificación de Servicios</h5>
        </div>
        <div class="card-selector"> 
            <div class="form-group">
                <label>Estado:</label>
                <select id="sel_estado" class="form-control" onchange="cargarMunicipios(this.value)">
                    <option value="">-- Seleccione Estado --</option>';
                    if (!empty({ds_estados})) {
                        foreach({ds_estados} as $estado) {
                            echo "<option value='".$estado[0]."'>".$estado[1]."</option>";
                        }
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
            <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">📋 Clasificador de Actividades</span>
            <button class="btn btn-success btn-sm" onclick="abrirModalNuevo()">+ Nuevo Registro</button>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-bordered table-striped m-0">
                <thead class="table-header-custom">
                    <tr><th>Clasificación</th><th>Tipo de Productos</th><th class="text-right">Impuesto (%)</th></tr>
                </thead>
                <tbody id="tbody_clasificacion">
                    <tr><td colspan="3" class="text-center text-muted py-4">Seleccione estado y municipio</td></tr>
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
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Nombre Clasificación</label><input type="text" id="ins_nombre" class="form-control"></div>
        <div class="form-group"><label>Código Tipo Productos</label><input type="text" id="ins_tipo" class="form-control"></div>
        <div class="form-group"><label>Impuesto (%)</label><input type="number" step="0.01" id="ins_impuesto" class="form-control" value="0.00"></div>
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
function getUrlLimpia() {
    return window.location.protocol + "//" + window.location.host + window.location.pathname;
}

function cargarMunicipios(codigo_estado) {
    let selectMuni = document.getElementById("sel_municipio");
    if (!codigo_estado) return;
    fetch(getUrlLimpia() + "?ajax_estado=" + codigo_estado)
        .then(res => res.json())
        .then(data => {
            selectMuni.innerHTML = "<option value=\"\">-- Seleccione Municipio --</option>";
            data.forEach(muni => {
                selectMuni.innerHTML += `<option value="${muni.codigo}">${muni.nombre}</option>`;
            });
        });
}

function cargarTablaClasificacion(cod_municipio) {
    let tbody = document.getElementById("tbody_clasificacion");
    let estado_id = document.getElementById("sel_estado").value;
    if (!cod_municipio) return;
    tbody.innerHTML = "<tr><td colspan=\"3\" class=\"text-center\">Cargando...</td></tr>";
    fetch(getUrlLimpia() + "?ajax_municipio=" + cod_municipio + "&estado_id=" + estado_id)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = "";
            if (data.length > 0) {
                data.forEach(item => {
                    tbody.innerHTML += `<tr>
                        <td>${item.nombre}</td>
                        <td>${item.codigo_tipo_productos || ""}</td>
                        <td class="text-right">${parseFloat(item.impuesto).toFixed(2)}%</td>
                    </tr>`;
                });
            } else {
                tbody.innerHTML = "<tr><td colspan=\"3\" class=\"text-center\">Sin datos</td></tr>";
            }
        });
}

function abrirModalNuevo() {
    if (!document.getElementById("sel_municipio").value) {
        alert("Seleccione Estado y Municipio");
        return;
    }
    $("#modalNuevo").modal("show");
}

function guardarRegistro() {
    let nom = document.getElementById("ins_nombre").value;
    let imp = document.getElementById("ins_impuesto").value;

    if (!nom || imp === "") {
        alert("El nombre y el impuesto son obligatorios");
        return;
    }

    let formData = new FormData();
    formData.append("action", "insertar");
    formData.append("estado_id", document.getElementById("sel_estado").value);
    formData.append("muni_cod", document.getElementById("sel_municipio").value);
    formData.append("nombre", nom);
    formData.append("tipo_prod", document.getElementById("ins_tipo").value);
    formData.append("impuesto", imp);

    fetch(getUrlLimpia(), { method: "POST", body: formData })
    .then(res => res.text()) // Recibimos texto puro primero
    .then(text => {
        try {
            // Intentamos convertir a JSON
            let data = JSON.parse(text);
            if (data.status === "success") {
                $("#modalNuevo").modal("hide");
                // Limpiar campos manualmente
                document.getElementById("ins_nombre").value = "";
                document.getElementById("ins_tipo").value = "";
                document.getElementById("ins_impuesto").value = "0.00";
                // Recargar tabla
                cargarTablaClasificacion(document.getElementById("sel_municipio").value);
            } else {
                alert("Error de Base de Datos: " + data.message);
            }
        } catch(e) {
            // Si falla el JSON, mostramos el texto real que envió el servidor
            console.error("Respuesta no JSON detectada:", text);
            alert("Error del servidor. El sistema devolvió un mensaje no válido. Detalle en consola.");
            
            // Si el texto contiene "Duplicate entry", "Column not found", etc, lo verás en la consola (F12)
        }
    })
    .catch(err => {
        console.error("Error de red:", err);
        alert("No se pudo conectar con el servidor.");
    });
}
</script>
';
?>