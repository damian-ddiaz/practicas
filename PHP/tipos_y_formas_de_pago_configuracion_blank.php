// 1. ASIGNACIÓN DE VARIABLES LOCALES SCRIPTCASE
$usr_empresa  = [usr_empresa];
$usr_login    = [usr_login];
$usr_sucursal = [usr_sucursal];
$current_url  = $_SERVER['PHP_SELF'];

$duplicate_error_msg = ""; 

// --- LOGICA AJAX 1: CARGAR TABLA PRINCIPAL ---
if (isset($_GET['ajax_mode'])) {
    while (ob_get_level()) ob_end_clean(); 
    
    $search = isset($_GET['search']) ? $_GET['search'] : "";
    $pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
    $registros_por_pagina = 10;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
    
    $where_filter = " WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal'";
    if (!empty($search)) {
        $where_filter .= " AND (codigo_tipo_pago LIKE '%$search%' OR nombre_tipo_pago LIKE '%$search%' OR estatus LIKE '%$search%')";
    }

    $sql_count = "SELECT COUNT(*) FROM banco_tipo_pago $where_filter";
    sc_lookup(ds_count, $sql_count);
    $total_paginas = ceil($ds_count[0][0] / $registros_por_pagina);

    $sql_data = "SELECT id_banco_tipo_pago, codigo_tipo_pago, nombre_tipo_pago, estatus, 
                        visible_cliente, visible_soporte, visible_aliado, visible_adm,
                        (SELECT COUNT(*) FROM banco_formas_pago WHERE codigo_tipo_pago = banco_tipo_pago.codigo_tipo_pago AND empresa = '$usr_empresa' AND sucursal = '$usr_sucursal') as total_formas
                 FROM banco_tipo_pago $where_filter
                 ORDER BY id_banco_tipo_pago DESC LIMIT $offset, $registros_por_pagina";
    sc_select(ds, $sql_data);
    
    $html_rows = "";
    if ($ds) {
        while (!$ds->EOF) {
            $f_id     = $ds->fields[0];
            $f_codigo = $ds->fields[1];
            $f_nombre = $ds->fields[2];
            $f_est    = $ds->fields[3];
            $f_formas = $ds->fields[8]; 
            
            $clean_row = [$f_id, $f_codigo, $f_nombre, $f_est, $ds->fields[4], $ds->fields[5], $ds->fields[6], $ds->fields[7], $f_formas];
            $json = json_encode($clean_row);
            
            $vis = [];
            if($ds->fields[4]) $vis[] = "Cliente";
            if($ds->fields[5]) $vis[] = "Soporte";
            if($ds->fields[6]) $vis[] = "Aliado";
            if($ds->fields[7]) $vis[] = "Administración";
            
            $badge = ($f_est == 'ACTIVO') ? 'bg-activo' : 'bg-inactivo';
            
            $html_rows .= "<tr id='tr_parent_{$f_id}'>
                <td>{$f_codigo}</td> 
                <td><strong>{$f_nombre}</strong></td>
                <td class='text-center'><span class='badge-custom $badge'>{$f_est}</span></td>
                <td style='color:#007bff; font-weight:500; font-size:0.9rem;'>".implode(", ", $vis)."</td>
                <td class='text-center'>
                    <button class='btn-action btn-view' title='Ver Formas' onclick='toggleSubTable(this, \"{$f_codigo}\", {$f_id})'><i class='fas fa-eye'></i></button>
                    <button class='btn-action btn-edit' onclick='editRow($json)'><i class='fas fa-pencil-alt'></i></button>
                    <button class='btn-action btn-delete' onclick='confirmDelete({$f_id})'><i class='fas fa-times'></i></button>
                </td>
            </tr>
            <tr id='child_{$f_id}' class='row-child' style='display:none;'><td colspan='5'><div id='container_{$f_id}'></div></td></tr>";
            $ds->MoveNext();
        }
    }

    $html_pag = '<ul class="pagination">';
    $html_pag .= '<li class="page-item '.($pagina_actual <= 1 ? 'disabled' : '').'"><a class="page-link" href="javascript:void(0)" onclick="loadTable('.($pagina_actual-1).')">Anterior</a></li>';
    for($i=1; $i<=$total_paginas; $i++) {
        $active = ($i == $pagina_actual) ? "active" : '';
        $html_pag .= "<li class='page-item $active'><a class='page-link' href='javascript:void(0)' onclick='loadTable($i)'>$i</a></li>";
    }
    $html_pag .= '<li class="page-item '.($pagina_actual >= $total_paginas ? 'disabled' : '').'"><a class="page-link" href="javascript:void(0)" onclick="loadTable('.($pagina_actual+1).')">Siguiente</a></li>';
    $html_pag .= '</ul>';

    header('Content-Type: application/json');
    echo json_encode(['rows' => $html_rows, 'pagination' => $html_pag]);
    exit;
}

// --- LOGICA AJAX 2: CARGAR SUB-TABLA (ACTUALIZADA CON JOIN DE BANCOS) ---
if (isset($_GET['get_formas_pago'])) {
    while (ob_get_level()) ob_end_clean();
    $codigo_tipo = sc_sql_injection($_GET['get_formas_pago']);
    
    // SQL actualizado: Traemos nombre_banco desde la tabla bancos
    $sql_formas = "SELECT 
                    fp.codigo_formas_pago, 
                    fp.nombre_formas_pago, 
                    b.nombre_banco, 
                    fp.codigo_moneda, 
                    fp.comision, 
                    fp.moneda_convertible, 
                    fp.porc_reten, 
                    fp.requiere_referencia 
                   FROM banco_formas_pago fp
                   LEFT JOIN bancos b ON fp.codigo_banco = b.codigo_banco AND fp.empresa = b.empresa
                   WHERE fp.codigo_tipo_pago = $codigo_tipo AND fp.empresa = '$usr_empresa' AND fp.sucursal = '$usr_sucursal'";
    
    sc_select(ds_f, $sql_formas);
    
    $sub_table = "<div class='table-responsive p-2 bg-light' style='max-width: 95%; margin: auto;'>";
    $sub_table .= "<table class='table table-sm table-bordered bg-white mb-0 shadow-sm' style='font-size:14px;'>
                        <thead class='thead-dark'>
                            <tr>
                                <th>Código</th>
                                <th width='40%' class='text-left'>Nombre Forma Pago</th> 
                                <th>Banco</th>
                                <th>Moneda</th>
                                <th>Comisión</th>
                                <th>Conv.</th>
                                <th>% Retención</th>
                                <th>Ref. Req.</th>
                            </tr>
                        </thead>
                        <tbody>";
    
    if ($ds_f && !$ds_f->EOF) {
        while (!$ds_f->EOF) {
            $sub_table .= "<tr>
                <td class='text-center'>{$ds_f->fields[0]}</td>
                <td class='text-left'>{$ds_f->fields[1]}</td> 
                <td class='text-left'>".($ds_f->fields[2] ? $ds_f->fields[2] : 'N/A')."</td> 
                <td class='text-center'>{$ds_f->fields[3]}</td>
                <td class='text-right'>".number_format($ds_f->fields[4], 2)."</td>
                <td class='text-center'>{$ds_f->fields[5]}</td>
                <td class='text-center'>".number_format($ds_f->fields[6], 2)."%</td>
                <td class='text-center'>{$ds_f->fields[7]}</td>
            </tr>";
            $ds_f->MoveNext();
        }
    } else {
        $sub_table .= "<tr><td colspan='8' class='text-center text-muted'>No hay formas de pago configuradas para este tipo.</td></tr>";
    }
    $sub_table .= "</tbody></table></div>";
    
    echo $sub_table;
    exit;
}

// 2. LÓGICA DE PROCESAMIENTO (Borrado con Validación)
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_del = sc_sql_injection($_GET['id']);
    sc_lookup(ds_get_code, "SELECT codigo_tipo_pago FROM banco_tipo_pago WHERE id_banco_tipo_pago = $id_del");
    $codigo_val = {ds_get_code}[0][0];
    sc_lookup(ds_check_del, "SELECT COUNT(*) FROM banco_formas_pago WHERE codigo_tipo_pago = '$codigo_val' AND empresa = '$usr_empresa' AND sucursal = '$usr_sucursal'");
    
    if (!empty({ds_check_del}) && {ds_check_del}[0][0] > 0) {
        $duplicate_error_msg = "Error: No se puede eliminar. Este tipo de pago ya está siendo utilizado en Formas de Pago.";
    } else {
        sc_exec_sql("DELETE FROM banco_tipo_pago WHERE id_banco_tipo_pago = $id_del AND empresa = '$usr_empresa'");
        header("Location: " . $current_url); exit;
    }
}

// LÓGICA DE GUARDADO
if (isset($_POST['btn_save'])) {
    $id      = $_POST['id_banco_tipo_pago'];
    $codigo  = sc_sql_injection($_POST['codigo_tipo_pago']);
    $nombre  = sc_sql_injection($_POST['nombre_tipo_pago']);
    $estatus = sc_sql_injection($_POST['estatus']);
    $v_cli = isset($_POST['visible_cliente']) ? 1 : 0;
    $v_sop = isset($_POST['visible_soporte']) ? 1 : 0;
    $v_ali = isset($_POST['visible_aliado']) ? 1 : 0;
    $v_adm = isset($_POST['visible_adm']) ? 1 : 0;

    $condicion_id = (!empty($id)) ? " AND id_banco_tipo_pago <> " . sc_sql_injection($id) : "";
    $sql_check = "SELECT COUNT(*) FROM banco_tipo_pago WHERE codigo_tipo_pago = $codigo AND empresa = '$usr_empresa' AND sucursal = '$usr_sucursal' $condicion_id";
    sc_lookup(ds_check, $sql_check);

    if (!empty({ds_check}) && {ds_check}[0][0] > 0) {
        $duplicate_error_msg = "Error: El Código de Tipo de Pago ya se encuentra registrado.";
    } else {
        if (empty($id)) {
            $sql = "INSERT INTO banco_tipo_pago (codigo_tipo_pago, nombre_tipo_pago, estatus, usuario, sucursal, ip_estacion, empresa, fecha, visible_cliente, visible_soporte, visible_aliado, visible_adm) VALUES ($codigo, $nombre, $estatus, '$usr_login', '$usr_sucursal', '".$_SERVER['REMOTE_ADDR']."', '$usr_empresa', '".date('Y-m-d')."', $v_cli, $v_sop, $v_ali, $v_adm)";
        } else {
            $sql = "UPDATE banco_tipo_pago SET codigo_tipo_pago=$codigo, nombre_tipo_pago=$nombre, estatus=$estatus, visible_cliente=$v_cli, visible_soporte=$v_sop, visible_aliado=$v_ali, visible_adm=$v_adm WHERE id_banco_tipo_pago=".sc_sql_injection($id);
        }
        sc_exec_sql($sql);
        header("Location: " . $current_url); exit;
    }
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
        .table thead th { background-color: #2d3e50; color: #ffffff; font-weight: 600; border: none; padding: 12px 15px; }
        .table tbody td { vertical-align: middle; padding: 12px 15px; color: #444; border-top: 1px solid #eee; }
        .badge-custom { border-radius: 4px; padding: 6px 12px; font-weight: 700; font-size: 0.85rem; color: #fff; min-width: 80px; display: inline-block; text-align: center; }
        .bg-activo { background-color: #28a745; }
        .bg-inactivo { background-color: #dc3545; }
        .btn-action { background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 4px 8px; transition: 0.2s; color: #555; cursor: pointer; }
        .btn-edit { color: #007bff; border-color: #007bff; }
        .btn-delete { color: #dc3545; border-color: #dc3545; }
        .btn-view { color: #17a2b8; border-color: #17a2b8; }
        input[readonly] { background-color: #e9ecef !important; cursor: not-allowed; }
        .ios-switch { position: relative; display: inline-block; width: 44px; height: 22px; margin-right: 10px; }
        .ios-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: #5dade2; } 
        input:checked + .slider:before { transform: translateX(22px); content: '✓'; font-size: 10px; color: #5dade2; text-align: center; line-height: 18px; }
        .custom-swal-popup { background: #1c1c1c !important; color: #ffffff !important; border-radius: 12px !important; padding: 25px !important; width: 500px !important; }
        .custom-swal-title { color: #ffffff !important; font-size: 1.5rem !important; font-weight: 700 !important; margin-bottom: 10px !important; text-align: left !important; }
        .custom-swal-html { color: #ffffff !important; font-size: 1.1rem !important; text-align: left !important; margin-bottom: 20px !important; }
        .custom-swal-button { background-color: #8c9eff !important; color: #ffffff !important; font-weight: bold !important; border-radius: 12px !important; padding: 10px 30px !important; border: none !important; cursor: pointer; }
        .pagination { margin-top: 20px; justify-content: center; }
        .row-child td { border-top: none !important; padding: 0 !important; }
        .row-child table { font-size: 1.1rem !important; }
        .row-child table thead th { font-size: 1.2rem; }        
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="card main-card">
        <div class="card-header">
            <h4 class="m-0 font-weight-bold">Tipos y Formas de Pago</h4>
            <div class="d-flex align-items-center">
                <div class="input-group mr-3" style="width: 350px;">
                    <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span></div>
                    <input type="text" id="quick_search" class="form-control border-left-0" placeholder="Escriba para filtrar...">
                </div>
                <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Nuevo Registro</button>
            </div>
        </div>
        <table class="table table-hover">
            <thead><tr><th>Código</th><th>Nombre del Tipo Pago</th><th class="text-center">Estatus</th><th>Visibilidad</th><th class="text-center">Acciones</th></tr></thead>
            <tbody id="table_body"></tbody>
        </table>
        <div id="pagination_container"></div>
    </div>
</div>

<!-- MODAL CRUD -->
<div class="modal fade" id="modalTipoPago" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form id="form_pago" method="POST">
                <div class="modal-header"><h5 class="modal-title" id="lblTitle">Detalle de Registro</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id_banco_tipo_pago" id="id_tp_id">
                    <div class="row">
                        <div class="col-md-6 form-group"><label class="font-weight-bold small">CÓDIGO *</label><input type="text" name="codigo_tipo_pago" id="id_tp_codigo" class="form-control"></div>
                        <div class="col-md-6 form-group"><label class="font-weight-bold small">ESTATUS *</label><select name="estatus" id="id_tp_estatus" class="form-control"><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
                    </div>
                    <div class="form-group"><label class="font-weight-bold small">NOMBRE DEL TIPO PAGO *</label><input type="text" name="nombre_tipo_pago" id="id_tp_nombre" class="form-control"></div>
                    <hr>
                    <div class="row">
                        <?php 
                        $labels = ['visible_cliente' => 'Cliente', 'visible_soporte' => 'Soporte', 'visible_aliado' => 'Aliado', 'visible_adm' => 'Administración'];
                        foreach ($labels as $id_campo => $texto) {
                            echo '<div class="col-6 mb-2"><label class="small font-weight-bold">VISIBLE '.strtoupper($texto).'</label><div class="d-flex align-items-center"><label class="ios-switch"><input type="checkbox" name="'.$id_campo.'" id="id_tp_'.$id_campo.'"><span class="slider"></span></label><span class="small">Activo</span></div></div>';
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-primary btn-sm" onclick="validarYGuardar()">Guardar Datos</button><input type="hidden" name="btn_save" value="1"></div>
            </form>
        </div>
    </div>
</div>

<script>
    let searchTimer;
    const myAppUrl = '<?php echo $current_url; ?>';

    $(document).ready(function() {
        loadTable(1);
        $('#quick_search').on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadTable(1), 300);
        });

        <?php if (!empty($duplicate_error_msg)): ?>
        Swal.fire({
            position: 'top',
            title: 'configuracion.icarosoft.com dice',
            text: '<?php echo $duplicate_error_msg; ?>',
            customClass: { popup: 'custom-swal-popup', title: 'custom-swal-title', htmlContainer: 'custom-swal-html', confirmButton: 'custom-swal-button' },
            buttonsStyling: false, confirmButtonText: 'OK'
        });
        <?php endif; ?>
    });

    function loadTable(pag) {
        const search = $('#quick_search').val();
        $.ajax({
            url: myAppUrl, type: 'GET', data: { ajax_mode: 1, pag: pag, search: search },
            success: function(res) {
                $('#table_body').html(res.rows);
                $('#pagination_container').html(res.pagination);
            }
        });
    }

    function toggleSubTable(btn, codigo, id) {
        const row = $(`#child_${id}`);
        const container = $(`#container_${id}`);
        const icon = $(btn).find('i');
        if (row.is(':visible')) {
            row.hide(); icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            icon.removeClass('fa-eye').addClass('fa-spinner fa-spin');
            $.ajax({
                url: myAppUrl, type: 'GET', data: { get_formas_pago: codigo },
                success: function(html) {
                    container.html(html); row.show();
                    icon.removeClass('fa-spinner fa-spin').addClass('fa-eye-slash');
                }
            });
        }
    }

    function editRow(data) {
        $('#lblTitle').text('Editar Registro');
        $('#id_tp_id').val(data[0]);
        $('#id_tp_codigo').val(data[1]);
        $('#id_tp_nombre').val(data[2]);
        $('#id_tp_estatus').val(data[3]);
        $('#id_tp_visible_cliente').prop('checked', data[4] == 1);
        $('#id_tp_visible_soporte').prop('checked', data[5] == 1);
        $('#id_tp_visible_aliado').prop('checked', data[6] == 1);
        $('#id_tp_visible_adm').prop('checked', data[7] == 1);

        const isUsed = (parseInt(data[8]) > 0);
        $('#id_tp_nombre').prop('readOnly', isUsed);
        $('#id_tp_codigo').prop('readOnly', isUsed);

        $('#modalTipoPago').modal('show');
    }

    function openModal() {
        $('#id_tp_id').val(''); $('#id_tp_codigo, #id_tp_nombre').val('');
        $('#id_tp_nombre, #id_tp_codigo').prop('readOnly', false); 
        $('#id_tp_estatus').val('ACTIVO'); $('input[type="checkbox"]').prop('checked', false);
        $('#lblTitle').text('Nuevo Registro'); $('#modalTipoPago').modal('show');
    }

    function validarYGuardar() {
        if (!$('#id_tp_codigo').val().trim() || !$('#id_tp_nombre').val().trim()) {
            Swal.fire({ position: 'top', title: 'configuracion.icarosoft.com dice', text: 'Error: Todos los campos marcados con (*) son obligatorios.', customClass: { popup: 'custom-swal-popup', confirmButton: 'btn btn-primary btn-sm' } });
            return;
        }
        $('#form_pago').submit();
    }

    function confirmDelete(id) {
        if (confirm('¿Confirmar eliminación?')) {
            window.location.href = myAppUrl + '?action=delete&id=' + id;
        }
    }
</script>
</body>
</html>
<?php