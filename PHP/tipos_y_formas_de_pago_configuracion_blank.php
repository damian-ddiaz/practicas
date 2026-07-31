// 1. ASIGNACIÓN DE VARIABLES LOCALES
$usr_empresa  = [usr_empresa];
$usr_login    = [usr_login];
$usr_sucursal = [usr_sucursal];

// --- LOGICA AJAX PARA BUSQUEDA Y PAGINACION ---
if (isset($_GET['ajax_mode'])) {
    while (ob_get_level()) ob_end_clean(); // Limpiar buffers
    
    $search = isset($_GET['search']) ? $_GET['search'] : "";
    $pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
    $registros_por_pagina = 10;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
    
    $where_search = "";
    if (!empty($search)) {
        $s_val = sc_sql_injection($search);
        $where_search = " AND (codigo_tipo_pago LIKE '%$search%' OR nombre_tipo_pago LIKE '%$search%') ";
    }

    // Conteo para paginación AJAX
    $sql_count = "SELECT COUNT(*) FROM banco_tipo_pago WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal' $where_search";
    sc_lookup(ds_count, $sql_count);
    $total_paginas = ceil($ds_count[0][0] / $registros_por_pagina);

    // Consulta de datos AJAX
    $sql_data = "SELECT id_banco_tipo_pago, codigo_tipo_pago, nombre_tipo_pago, estatus, visible_cliente, visible_soporte, visible_aliado, visible_adm,
                (SELECT COUNT(*) FROM banco_formas_pago WHERE codigo_tipo_pago = banco_tipo_pago.codigo_tipo_pago AND empresa = '$usr_empresa' AND sucursal = '$usr_sucursal') as total_formas
                 FROM banco_tipo_pago WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal' $where_search
                 ORDER BY id_banco_tipo_pago DESC LIMIT $offset, $registros_por_pagina";
    
    sc_select(ds, $sql_data);
    
    // Generar las filas
    $html_rows = "";
    if ($ds) {
        while (!$ds->EOF) {
            $f = (object) $ds->fields;
            $json = json_encode($ds->fields);
            $vis = [];
            if($f->visible_cliente) $vis[] = "Cliente";
            if($f->visible_soporte) $vis[] = "Soporte";
            if($f->visible_aliado)  $vis[] = "Aliado";
            if($f->visible_adm)     $vis[] = "Administración";
            
            $badge = ($f->estatus == 'ACTIVO') ? 'bg-activo' : 'bg-inactivo';
            
            $html_rows .= "<tr>
                <td>{$f->codigo_tipo_pago}</td>
                <td><strong>{$f->nombre_tipo_pago}</strong></td>
                <td class='text-center'><span class='badge-custom $badge'>{$f->estatus}</span></td>
                <td style='color:#007bff; font-weight:500;'>".implode(", ", $vis)."</td>
                <td class='text-center'>
                    <button class='btn-action btn-edit' onclick='editRow(".htmlspecialchars($json, ENT_QUOTES).")'><i class='fas fa-pencil-alt'></i></button>
                    <button class='btn-action btn-delete' onclick='confirmarEliminar({$f->id_banco_tipo_pago})'><i class='fas fa-times'></i></button>
                </td>
            </tr>";
            $ds->MoveNext();
        }
    }

    // Generar la paginación
    $html_pag = "";
    if ($total_paginas > 1) {
        $html_pag .= '<ul class="pagination">';
        $html_pag .= '<li class="page-item '.($pagina_actual <= 1 ? 'disabled' : '').'"><a class="page-link" href="javascript:loadTable('.($pagina_actual-1).')">Anterior</a></li>';
        for($i=1; $i<=$total_paginas; $i++) {
            $active = ($i == $pagina_actual) ? "active" : "";
            $html_pag .= "<li class='page-item $active'><a class='page-link' href='javascript:loadTable($i)'>$i</a></li>";
        }
        $html_pag .= '<li class="page-item '.($pagina_actual >= $total_paginas ? 'disabled' : '').'"><a class="page-link" href="javascript:loadTable('.($pagina_actual+1).')">Siguiente</a></li>';
        $html_pag .= '</ul>';
    }

    header('Content-Type: application/json');
    echo json_encode(['rows' => $html_rows, 'pagination' => $html_pag]);
    exit;
}

// 2. LÓGICA DE PROCESAMIENTO NORMAL (Eliminar y Guardar)
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_del = sc_sql_injection($_GET['id']);
    sc_exec_sql("DELETE FROM banco_tipo_pago WHERE id_banco_tipo_pago = $id_del AND empresa = '$usr_empresa'");
    sc_redir(index.php);
}

if (isset($_POST['btn_save'])) {
    $id      = $_POST['id_banco_tipo_pago'];
    $codigo  = sc_sql_injection($_POST['codigo_tipo_pago']);
    $nombre  = sc_sql_injection($_POST['nombre_tipo_pago']);
    $estatus = sc_sql_injection($_POST['estatus']);
    $v_cli = isset($_POST['visible_cliente']) ? 1 : 0;
    $v_sop = isset($_POST['visible_soporte']) ? 1 : 0;
    $v_ali = isset($_POST['visible_aliado']) ? 1 : 0;
    $v_adm = isset($_POST['visible_adm']) ? 1 : 0;

    if (empty($id)) {
        $sql = "INSERT INTO banco_tipo_pago (codigo_tipo_pago, nombre_tipo_pago, estatus, usuario, sucursal, ip_estacion, empresa, fecha, visible_cliente, visible_soporte, visible_aliado, visible_adm) 
                VALUES ($codigo, $nombre, $estatus, '$usr_login', '$usr_sucursal', '".$_SERVER['REMOTE_ADDR']."', '$usr_empresa', '".date('Y-m-d')."', $v_cli, $v_sop, $v_ali, $v_adm)";
    } else {
        $sql = "UPDATE banco_tipo_pago SET codigo_tipo_pago=$codigo, nombre_tipo_pago=$nombre, estatus=$estatus, visible_cliente=$v_cli, visible_soporte=$v_sop, visible_aliado=$v_ali, visible_adm=$v_adm WHERE id_banco_tipo_pago=$id";
    }
    sc_exec_sql($sql);
    sc_redir(index.php);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tipos y Formas de Pago</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body { background-color: #f8f9fa; padding: 30px; font-family: 'Segoe UI', sans-serif; }
        .main-card { border: 1px solid #dee2e6; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: #fff; }
        .card-header { background-color: #ffffff; border-bottom: 1px solid #eeeeee; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; }
        .header-title { font-size: 1.5rem; font-weight: 700; color: #333; }
        .btn-new { background-color: #007bff; color: white; border-radius: 4px; padding: 8px 18px; font-weight: 600; border: none; }
        .table thead th { background-color: #2d3e50; color: #ffffff; font-weight: 600; padding: 12px 15px; border: none; }
        .table tbody td { vertical-align: middle; padding: 12px 15px; color: #444; border-top: 1px solid #eee; }
        .badge-custom { border-radius: 4px; padding: 6px 12px; font-weight: 700; font-size: 0.85rem; color: #fff; min-width: 80px; display: inline-block; text-align: center; }
        .bg-activo { background-color: #28a745; }
        .bg-inactivo { background-color: #dc3545; }
        .btn-action { background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 4px 8px; transition: 0.2s; }
        .btn-edit { color: #007bff; border-color: #007bff; }
        .btn-delete { color: #dc3545; border-color: #dc3545; }
        input[readonly] { background-color: #e9ecef !important; cursor: not-allowed; }

        /* SWITCH IOS */
        .switch-container { display: flex; align-items: center; margin-bottom: 10px; }
        .switch-label-title { font-weight: 700; color: #5a6268; display: block; margin-bottom: 5px; font-size: 0.9rem; }
        .ios-switch { position: relative; display: inline-block; width: 44px; height: 22px; margin-right: 10px; }
        .ios-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: #5dade2; } 
        input:checked + .slider:before { transform: translateX(22px); content: '✓'; font-size: 12px; color: #5dade2; text-align: center; line-height: 18px; }
        .switch-text { font-size: 1rem; color: #000; font-weight: 500; }
        .help-mark { color: #17a2b8; cursor: help; font-weight: bold; font-size: 0.9rem; margin-left: 5px; }

        .pagination { margin: 20px; justify-content: center; }
        .page-link { color: #2d3e50; }
        .page-item.active .page-link { background-color: #2d3e50; border-color: #2d3e50; }

        .custom-swal-popup { background: #1c1c1c !important; color: #ffffff !important; border-radius: 0 0 15px 15px !important; padding: 15px 25px !important; width: 500px !important; }
        .custom-swal-title { color: #ffffff !important; font-size: 1.1rem !important; text-align: left !important; font-weight: 600 !important; }
        .custom-swal-html { color: #ffffff !important; text-align: left !important; font-size: 0.95rem !important; }
        .custom-swal-button { background-color: #a8c4f3 !important; color: #1c1c1c !important; font-weight: bold !important; border-radius: 25px !important; padding: 10px 35px !important; border: none !important; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="card main-card">
        <div class="card-header">
            <div class="header-title"> Tipos y Formas de Pago</div>
            <div class="d-flex align-items-center">
                <!-- BARRA DE BUSQUEDA MEJORADA -->
                <div class="input-group mr-3" style="width: 350px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="input_search" class="form-control border-left-0" placeholder="Escriba para filtrar..." autocomplete="off">
                </div>
                <button class="btn btn-new" onclick="openModal()"><i class="fas fa-plus"></i> Nuevo Registro</button>
            </div>
        </div>
        
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre del Tipo Pago</th>
                    <th class="text-center">Estatus</th>
                    <th>Visibilidad</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="table_body">
                <!-- Se carga vía AJAX -->
            </tbody>
        </table>
        <div id="pagination_container">
            <!-- Se carga vía AJAX -->
        </div>
    </div>
</div>

<!-- MODAL CRUD -->
<div class="modal fade" id="modalTipoPago" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form id="form_pago" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="lblTitle">Detalle de Registro</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_banco_tipo_pago" id="id_banco_tipo_pago">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Código *</label>
                            <span class="help-mark" data-toggle="tooltip" title="Abreviación única (Ej: TRA, EF)">(?)</span>
                            <input type="text" name="codigo_tipo_pago" id="codigo_tipo_pago" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Estatus *</label>
                            <select name="estatus" id="estatus" class="form-control">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nombre del Tipo de Pago *</label>
                        <input type="text" name="nombre_tipo_pago" id="nombre_tipo_pago" class="form-control">
                    </div>
                    <hr>
                    <div class="row">
                        <?php 
                        $labels = ['visible_cliente' => 'Visible Cliente', 'visible_soporte' => 'Visible Soporte', 'visible_aliado' => 'Visible Aliado', 'visible_adm' => 'Visible Administración'];
                        foreach ($labels as $id_campo => $texto) {
                            echo '<div class="col-6 mb-3">
                                    <label class="switch-label-title">'.$texto.'</label>
                                    <div class="switch-container">
                                        <label class="ios-switch">
                                            <input type="checkbox" name="'.$id_campo.'" id="'.$id_campo.'">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="switch-text">Activo</span>
                                    </div>
                                  </div>';
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="validarYGuardar()">Guardar Datos</button>
                    <input type="hidden" name="btn_save" value="1">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let searchTimer;

    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        loadTable(1); // Carga inicial

        // EVENTO LIVE SEARCH CON DEBOUNCE
        $('#input_search').on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                loadTable(1); // Siempre vuelve a la pag 1 al buscar
            }, 300); // 300ms de retraso
        });
    });

    function loadTable(pag) {
        const search = $('#input_search').val();
        $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: { ajax_mode: 1, pag: pag, search: search },
            success: function(res) {
                $('#table_body').html(res.rows);
                $('#pagination_container').html(res.pagination);
            }
        });
    }

    function mostrarMensajeError(mensaje) {
        Swal.fire({
            position: 'top',
            title: 'configuracion.icarosoft.com dice',
            text: mensaje,
            customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-html', confirmButton: 'custom-swal-button' },
            buttonsStyling: false,
            confirmButtonText: 'Aceptar'
        });
    }

    function validarYGuardar() {
        if (!document.getElementById("codigo_tipo_pago").value.trim() || !document.getElementById("nombre_tipo_pago").value.trim()) {
            mostrarMensajeError("Error: Todos los campos marcados con (*) son obligatorios.");
            return;
        }
        document.getElementById("form_pago").submit();
    }

    function openModal() {
        $('#id_banco_tipo_pago').val('');
        $('#codigo_tipo_pago, #nombre_tipo_pago').val('').prop('readOnly', false);
        $('#estatus').val('ACTIVO');
        $('input[type="checkbox"]').prop('checked', false);
        $('#lblTitle').text('Nuevo Registro');
        $('#modalTipoPago').modal('show');
    }

    function editRow(data) {
        $('#lblTitle').text('Editar Registro');
        $('#id_banco_tipo_pago').val(data[0]);
        $('#codigo_tipo_pago').val(data[1]);
        $('#nombre_tipo_pago').val(data[2]);
        $('#estatus').val(data[3]);
        $('#visible_cliente').prop('checked', data[4] == 1);
        $('#visible_soporte').prop('checked', data[5] == 1);
        $('#visible_aliado').prop('checked', data[6] == 1);
        $('#visible_adm').prop('checked', data[7] == 1);

        const block = (parseInt(data[8]) > 0);
        $('#codigo_tipo_pago').prop('readOnly', block);
        $('#nombre_tipo_pago').prop('readOnly', block);

        $('#modalTipoPago').modal('show');
    }

    function confirmarEliminar(id) {
        if (confirm('¿Eliminar registro permanentemente?')) {
            window.location.href = window.location.pathname + '?action=delete&id=' + id;
        }
    }
</script>
</body>
</html>
<?php