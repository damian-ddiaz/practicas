// 1. ASIGNACIÓN DE VARIABLES LOCALES SCRIPTCASE
$usr_empresa  		= [usr_empresa];
$usr_login    		= [usr_login];
$usr_sucursal 		= [usr_sucursal];
$current_url  		= $_SERVER['PHP_SELF'];
$var_ip_estacion    = $_SERVER['REMOTE_ADDR'];
$var_wiki	  		= [wiki];

$duplicate_error_msg = ""; 

// --- ACCIÓN: Validar Duplicado de Código (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] == 'check_duplicate_codigo') {
    while (ob_get_level()) ob_end_clean();
//    $cod_check = sc_sql_injection($_GET['codigo']);
    $cod_check = sc_sql_injection(strtoupper($_GET['codigo'])); // Forzamos mayúsculas

    sc_lookup(ds_dup, "SELECT COUNT(*) FROM banco_tipo_pago 
                       WHERE codigo_tipo_pago = $cod_check 
                       AND empresa = '$usr_empresa' 
                       AND sucursal = '$usr_sucursal'");
    echo (isset($ds_dup[0][0]) && $ds_dup[0][0] > 0) ? 'existe' : 'ok';
    exit;
}

// --- LÓGICA PARA ELIMINAR TIPO DE PAGO CON VALIDACIÓN ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_tipo') {
    $id_del = sc_sql_injection($_GET['id_tipo']);

    // 1. Obtenemos el código_tipo_pago
    sc_lookup(ds_temp, "SELECT codigo_tipo_pago FROM banco_tipo_pago WHERE id_banco_tipo_pago = $id_del");
    
    if (isset($ds_temp[0][0])) {
        $codigo_tipo = $ds_temp[0][0];

        // 2. Validamos si existen registros asociados
        sc_lookup(ds_check, "SELECT COUNT(*) FROM banco_formas_pago 
                             WHERE empresa='$usr_empresa' 
                             AND sucursal = '$usr_sucursal' 
                             AND codigo_tipo_pago = '$codigo_tipo'");

        if ($ds_check[0][0] > 0) {
            // ERROR: Existen registros asociados - Mostrar SweetAlert y redirigir con JS
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<body><script>
                Swal.fire({
                    title: 'No se puede eliminar',
                    text: 'Existen formas de pago asociadas a este tipo.',
                    icon: 'error'
                }).then(() => { window.location.href = '$current_url'; });
            </script></body>";
            exit;
        } else {
            // OK: Procedemos a eliminar
            sc_exec_sql("DELETE FROM banco_tipo_pago WHERE id_banco_tipo_pago = $id_del");
            // Redirección limpia mediante JS para evitar pantalla en blanco por headers
            echo "<body><script>window.location.href = '$current_url';</script></body>";
            exit;
        }
    }
}
/*NUEVO*/

// --- ACCIÓN: Validar Duplicado de Código de Forma (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] == 'check_duplicate_forma') {
    while (ob_get_level()) ob_end_clean();
    //$cod_f_check = sc_sql_injection($_GET['codigo_forma']);
    $cod_f_check = sc_sql_injection(strtoupper($_GET['codigo_forma'])); // Forzamos mayúsculas	
    sc_lookup(ds_dup_f, "SELECT COUNT(*) FROM banco_formas_pago 
                       WHERE codigo_formas_pago = $cod_f_check 
                       AND empresa = '$usr_empresa' 
                       AND sucursal = '$usr_sucursal'");
    echo (isset($ds_dup_f[0][0]) && $ds_dup_f[0][0] > 0) ? 'existe' : 'ok';
    exit;
}

/*NUEVO*/
// --- LÓGICA PARA ELIMINAR FORMA DE PAGO CON VALIDACIÓN CRUZADA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_forma') {
    $id_f_del = sc_sql_injection($_GET['id_forma']);

    // 1. Obtenemos los códigos necesarios (Tipo y Forma) para la validación
    sc_lookup(ds_f_info, "SELECT codigo_tipo_pago, codigo_formas_pago FROM banco_formas_pago WHERE id_banco_formas_pago = $id_f_del");
    
    if (isset($ds_f_info[0][0])) {
        $c_tp = $ds_f_info[0][0]; // codigo_tipo_pago
        $c_fp = $ds_f_info[0][1]; // codigo_formas_pago

        // 2. Ejecutamos la validación en Ventas y Compras
        $sql_valida = "SELECT 
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM ventas_transacciones_detalles 
                    WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal' 
                      AND tipo_pago = '$c_tp' AND forma_pago = '$c_fp'
                ) 
                OR EXISTS (
                    SELECT 1 FROM compras_transacciones_detalles 
                    WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal' 
                      AND tipo_pago = '$c_tp' AND forma_pago = '$c_fp'
                ) 
                THEN '1' ELSE '0'
            END";
        
        sc_lookup(ds_res, $sql_valida);

        if ($ds_res[0][0] == '1') {
            // ERROR: La forma de pago ya ha sido utilizada en transacciones
            echo "<body><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    title: 'No se puede eliminar',
                    text: 'Esta forma de pago ya tiene movimientos registrados en el sistema.',
                    icon: 'error'
                }).then(() => { window.location.href = '$current_url'; });
            </script></body>";
            exit;
        } else {
            // OK: No hay registros, procedemos a eliminar
            sc_exec_sql("DELETE FROM banco_formas_pago WHERE id_banco_formas_pago = $id_f_del");
            echo "<body><script>window.location.href = '$current_url';</script></body>";
            exit;
        }
    }
}


// --- ACCIÓN: Verificar Proveedor del Banco (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] == 'verificar_banco_proveedor') {
    while (ob_get_level()) ob_end_clean();
    $ban_check = sc_sql_injection($_GET['codigo_banco']);
    sc_lookup(ds_ban_v, "SELECT id_proveedor FROM bancos 
                         WHERE codigo_banco = $ban_check 
                         AND empresa = '$usr_empresa'");
    
    // Si no hay proveedor, es 0 o es NULL, devolvemos 'error'
    if (empty($ds_ban_v[0][0]) || $ds_ban_v[0][0] <= 0) {
        echo 'sin_proveedor';
    } else {
        echo 'ok';
    }
    exit;
}

// --- LOGICA AJAX 1: CARGAR TABLA PRINCIPAL ---
if (isset($_GET['ajax_mode'])) {
    while (ob_get_level()) ob_end_clean(); 
    $search = isset($_GET['search']) ? $_GET['search'] : "";
    $pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
    $registros_por_pagina = 10;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
    $where_f = " WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal'";
    if (!empty($search)) { $where_f .= " AND (codigo_tipo_pago LIKE '%$search%' OR nombre_tipo_pago LIKE '%$search%')"; }

    sc_lookup(ds_count, "SELECT COUNT(*) FROM banco_tipo_pago $where_f");
    $total_paginas = ceil($ds_count[0][0] / $registros_por_pagina);

    $sql_data = "SELECT id_banco_tipo_pago, codigo_tipo_pago, nombre_tipo_pago, estatus, visible_cliente, visible_soporte, visible_aliado, visible_adm, retencion,
                (SELECT COUNT(*) FROM banco_formas_pago WHERE codigo_tipo_pago = banco_tipo_pago.codigo_tipo_pago AND empresa = '$usr_empresa' AND sucursal = '$usr_sucursal') as total_formas
                 FROM banco_tipo_pago $where_f ORDER BY id_banco_tipo_pago DESC LIMIT $offset, $registros_por_pagina";
    sc_select(ds, $sql_data);
    
    $html_rows = "";
    if ($ds) {
        while (!$ds->EOF) {
            $f_id = $ds->fields[0];
            // Construimos un array limpio para evitar duplicados de Scriptcase
            $clean_row = [];
            for($i=0; $i<10; $i++) { $clean_row[] = $ds->fields[$i]; }
            $json = json_encode($clean_row);
            
            $badge = ($ds->fields[3] == 'Activo') ? 'bg-activo' : 'bg-inactivo';
            $vis = [];
            if($ds->fields[4]) $vis[] = "Clientes"; if($ds->fields[5]) $vis[] = "Soporte"; if($ds->fields[6]) $vis[] = "Aliados"; if($ds->fields[7]) $vis[] = "Administracion"; if($ds->fields[8]) $vis[] = "Ret. (Portal Clientes)";
            
            $html_rows .= "<tr id='tr_parent_{$f_id}'>
                <td>{$ds->fields[1]}</td> 
                <td><strong>{$ds->fields[2]}</strong></td>
                <td class='text-center'><span class='badge-custom $badge'>{$ds->fields[3]}</span></td>
                <td style='color:#007bff; font-weight:500; font-size:0.9rem;'>".implode(", ", $vis)."</td>
                <td class='text-center'>
                    <button class='btn btn-sm btn-outline-success' title='Ver Formas' onclick='toggleSubTable(this, \"{$ds->fields[1]}\", {$f_id})'><i class='fas fa-eye'></i></button>
                    <button class='btn btn-sm btn-outline-primary mr-1' onclick='editRow($json)'><i class='fas fa-pencil-alt'></i></button>
                    <button class='btn btn-sm btn-outline-danger'
 onclick='confirmDelete({$f_id})'><i class='fas fa-trash'></i></button>
                </td>
            </tr>
            <tr id='child_{$f_id}' class='row-child' style='display:none;'><td colspan='5'><div id='container_{$f_id}'></div></td></tr>";
            $ds->MoveNext();
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['rows' => $html_rows, 'pagination' => '']); // Simplificado para brevedad
    exit;
}

// --- LOGICA AJAX 2: CARGAR SUB-TABLA (FORMAS DE PAGO) ---
if (isset($_GET['get_formas_pago'])) {
    while (ob_get_level()) ob_end_clean();
    $codigo_tipo = sc_sql_injection($_GET['get_formas_pago']);
    
    // El orden de este SELECT es vital para el JS
	$sql_formas = "SELECT 
					id_banco_formas_pago,        /* 0 */
					codigo_formas_pago,          /* 1 */
					nombre_formas_pago,          /* 2 */
					codigo_banco,                /* 3 */
					codigo_moneda,               /* 4 */
					comision,                    /* 5 */
					moneda_convertible,          /* 6 */
					porc_reten,                  /* 7 */
					requiere_referencia,         /* 8 */
					mensaje_cliente,             /* 9 */
					visible_cliente,             /* 10 */
					visible_soporte,             /* 11 */
					fact_auto,                   /* 12 */
					generar_comision_bancaria,   /* 13 */
					tipo_documento,              /* 14 */
					cuenta_padre,                /* 15 */
					cuenta_hijo,                 /* 16 */
					codigo_productos,            /* 17 */
					visible_icarobot_ia,         /* 18 */
					visible_aliado,              /* 19 */
					generar_comision_bancaria_cxp, /* 20 */
					porcentaje_comision_bancaria,  /* 21 */
					fecha_inicio_comision,        /* 22 */
					/* NUEVA COLUMNA: VERIFICACIÓN DE USO (Índice 23) */
					(SELECT CASE WHEN EXISTS (SELECT 1 FROM ventas_transacciones_detalles WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal' AND tipo_pago = banco_formas_pago.codigo_tipo_pago AND forma_pago = banco_formas_pago.codigo_formas_pago) 
							  OR EXISTS (SELECT 1 FROM compras_transacciones_detalles WHERE empresa = '$usr_empresa' AND sucursal = '$usr_sucursal' AND tipo_pago = banco_formas_pago.codigo_tipo_pago AND forma_pago = banco_formas_pago.codigo_formas_pago) 
					THEN 1 ELSE 0 END) as en_uso
				   FROM banco_formas_pago 
				   WHERE codigo_tipo_pago = $codigo_tipo AND empresa = '$usr_empresa' AND sucursal = '$usr_sucursal'";
    sc_select(ds_f, $sql_formas);
    
    $sub_table = "<div class='p-3 bg-light border-bottom'><div class='d-flex justify-content-between align-items-center mb-2'><h6 class='m-0 font-weight-bold text-secondary'>Formas de Pago</h6><button class='btn btn-success btn-sm' onclick='openModalNuevaForma(\"".$_GET['get_formas_pago']."\")'><i class='fas fa-plus-circle'></i> Nueva Forma</button></div><div class='table-responsive shadow-sm'>";
    $sub_table .= "<table class='table table-sm table-bordered bg-white mb-0' style='font-size:14px;'><thead class='thead-dark'><tr><th>Código</th><th class='text-left'>Nombre</th><th>Comisión</th><th>Conv.</th><th>% Ret.</th><th>Ref.</th><th>Acciones</th></tr></thead><tbody>";
    
    if ($ds_f && !$ds_f->EOF) {
        while (!$ds_f->EOF) {
            $clean_f = [];
            for($i=0; $i<24; $i++){
				$clean_f[] = $ds_f->fields[$i]; 
			}
            $json_f = json_encode($clean_f);

            $sub_table .= "<tr>
                <td class='text-center'>{$ds_f->fields[1]}</td>
                <td class='text-left'>{$ds_f->fields[2]}</td> 
                <td class='text-right'>".number_format($ds_f->fields[5], 2)."</td>
                <td class='text-center'>{$ds_f->fields[6]}</td>
                <td class='text-center'>".number_format($ds_f->fields[7], 2)."%</td>
                <td class='text-center'>{$ds_f->fields[8]}</td>
                <td class='text-center'>
                    <button class='btn btn-sm btn-outline-primary mr-1' onclick='editFormaPago($json_f, \"".$_GET['get_formas_pago']."\")'><i class='fas fa-pencil-alt'></i></button>
                    <button class='btn btn-sm btn-outline-danger' onclick='confirmDeleteForma({$ds_f->fields[0]})'><i class='fas fa-trash'></i></button>
                </td></tr>";
            $ds_f->MoveNext();
        }
    } else { $sub_table .= "<tr><td colspan='7' class='text-center text-muted'>Sin registros.</td></tr>"; }
    echo $sub_table . "</tbody></table></div></div>";
    exit;
}

// 1. PROCESAMIENTO TIPO DE PAGO (Guardado)
if (isset($_POST['btn_save_tipo'])) {
//	$v_cod_raw = $_POST['t_codigo'];
	$v_cod_raw = strtoupper($_POST['t_codigo']); // Forzamos mayúsculas aquí
    $v_nom_raw = $_POST['t_nombre'];

    if (empty($v_cod_raw) || empty($v_nom_raw) || strlen($v_cod_raw) > 10) {
        $msg = (strlen($v_cod_raw) > 10) ? "El código no puede exceder 10 caracteres." : "Todos los campos con (*) son obligatorios.";
        echo "<body><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({ icon: 'error', title: 'Error', text: '$msg' }).then(() => { window.history.back(); });
        </script></body>";
        exit;
    }
    // --- FIN VALIDACIÓN ---

    $id_t  = $_POST['t_id_pk'];
    $cod   = sc_sql_injection($_POST['t_codigo']);
    $nom   = sc_sql_injection($_POST['t_nombre']);
    $est   = sc_sql_injection($_POST['t_estatus']);
    
    // Captura de visibilidad (Checkboxes/Sliders)
    $v_cli = isset($_POST['t_v_cli']) ? 1 : 0;
    $v_sop = isset($_POST['t_v_sop']) ? 1 : 0;
    $v_ali = isset($_POST['t_v_ali']) ? 1 : 0;
    $v_adm = isset($_POST['t_v_adm']) ? 1 : 0;
	$v_ret = isset($_POST['t_retencion']) ? 1 : 0;

    if (empty($id_t)) {
		
		// --- NUEVA VALIDACIÓN DE DUPLICADOS PARA INSERCIÓN ---
        sc_lookup(ds_dup, "SELECT COUNT(*) FROM banco_tipo_pago 
                           WHERE codigo_tipo_pago = $cod 
                           AND empresa = '$usr_empresa' 
                           AND sucursal = '$usr_sucursal'");

        if (isset($ds_dup[0][0]) && $ds_dup[0][0] > 0) {
            // ERROR: Código duplicado detectado
            while (ob_get_level()) ob_end_clean(); // Limpiar buffer
            echo "<body><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Código Duplicado',
                    text: 'El código de pago ' + $cod + ' ya se encuentra registrado para esta empresa y sucursal.',
                    confirmButtonColor: '#3085d6'
                }).then(() => { window.history.back(); });
            </script></body>";
            exit;
        }
        // --- FIN VALIDACIÓN ---
			
        // INSERTAR NUEVO TIPO
        $sql = "INSERT INTO banco_tipo_pago (codigo_tipo_pago, nombre_tipo_pago, estatus, empresa, sucursal, visible_cliente, visible_soporte, visible_aliado, visible_adm, retencion, usuario,ip_estacion,fecha) 
                VALUES ($cod, $nom, $est, '$usr_empresa', '$usr_sucursal', $v_cli, $v_sop, $v_ali, $v_adm, $v_ret,'$usr_login','$var_ip_estacion',NOW())";
    } else {
        // ACTUALIZAR EXISTENTE
        $sql = "UPDATE banco_tipo_pago 
                SET codigo_tipo_pago=$cod, nombre_tipo_pago=$nom, estatus=$est, visible_cliente=$v_cli, visible_soporte=$v_sop, visible_aliado=$v_ali, visible_adm=$v_adm, retencion=$v_ret 
                WHERE id_banco_tipo_pago=" . sc_sql_injection($id_t);
    }
    
    sc_exec_sql($sql);
    header("Location: ".$current_url); 
    exit;
}

// 2. PROCESAMIENTO CRUD (Guardado de Formas de Pago)
if (isset($_POST['btn_save_forma'])) {
    // --- INICIO DE VALIDACIONES PROFESIONALES ---
//    $raw_f_cod = $_POST['f_codigo_formas_pago'];
	$raw_f_cod = strtoupper($_POST['f_codigo_formas_pago']); // Forzamos mayúsculas aquí
    $raw_f_nom = $_POST['f_nombre_formas_pago'];
    $raw_f_ban = $_POST['f_codigo_banco'];
    $raw_f_mon = $_POST['f_codigo_moneda'];

    $error_msg = "";

    // 1. Validar campos obligatorios
    if (empty($raw_f_cod) || empty($raw_f_nom) || empty($raw_f_ban) || empty($raw_f_mon)) {
        $error_msg = "Por favor, complete todos los campos marcados con (*) antes de guardar.";
    } 
    // 2. Validar longitud del código (Máximo 10)
    elseif (strlen($raw_f_cod) > 10) {
        $error_msg = "El Código de Forma ('$raw_f_cod') es demasiado largo. El límite es de 10 caracteres.";
    }

    if (!empty($error_msg)) {
        // Limpiamos cualquier salida previa para que el SweetAlert cargue limpio
        while (ob_get_level()) ob_end_clean();
        echo "<body>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Validación de Datos',
                text: '$error_msg',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Regresar al formulario'
            }).then(() => { window.history.back(); });
        </script></body>";
        exit;
    }
		
	$v_cxp = isset($_POST['f_generar_comision_bancaria_cxp']) ? 1 : 0;
    $raw_porc = $_POST['f_porcentaje_comision_bancaria'];
    $raw_fecha = $_POST['f_fecha_inicio_comision'];
    $raw_prod = $_POST['f_codigo_productos'];


	 // VALIDACIÓN CONDICIONAL CXP Y PROVEEDOR DE BANCO
    if ($v_cxp == 1) {
        // 1. Validar campos obligatorios de la interfaz
        $campos_incompletos = (empty($raw_porc) || empty($raw_fecha) || empty($raw_prod));

        // 2. Validar que el banco tenga un proveedor asignado en la DB
        sc_lookup(ds_ban_check, "SELECT id_proveedor FROM bancos 
                                 WHERE codigo_banco = '$raw_f_ban' 
                                 AND empresa = '$usr_empresa'");
        
      //  $banco_sin_proveedor = (!isset($ds_ban_check[0][0]) || $ds_ban_check[0][0] <= 0);
		
		$banco_sin_proveedor = (empty($ds_ban_check[0][0]) || $ds_ban_check[0][0] <= 0);

        if ($campos_incompletos || $banco_sin_proveedor) {
            while (ob_get_level()) ob_end_clean();
            
            // Construcción dinámica del mensaje según el error
            $msg_final = "Los campos:  Comis. Bancaria, Fecha Inicio Comisión, Producto son obligatorios cuando CxP está activo.";
            if ($banco_sin_proveedor) {
                $msg_final .= " Además, el banco seleccionado debe tener un proveedor asignado en su configuración.";
            }

            echo "<body><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Validación CxP Fallida',
                    text: '$msg_final',
                    confirmButtonColor: '#3085d6'
                }).then(() => { window.history.back(); });
            </script></body>";
            exit;
        }
    }
	
    // --- FIN DE VALIDACIONES ---

    // Proseguimos con la sanitización de datos (Lógica original)
    $id_f = $_POST['f_id_pk'];
    $c_tp = sc_sql_injection($_POST['f_codigo_tipo_pago']);
    $c_fp = sc_sql_injection($raw_f_cod); 
    $n_fp = sc_sql_injection($raw_f_nom);
    
    $m_cv = sc_sql_injection($_POST['f_moneda_convertible']);
    $p_rt = !empty($_POST['f_porc_reten']) ? $_POST['f_porc_reten'] : 0;
    $c_ba = sc_sql_injection($raw_f_ban);
    $c_mo = sc_sql_injection($raw_f_mon);
    $r_re = sc_sql_injection($_POST['f_requiere_referencia']);
    $m_cl = sc_sql_injection($_POST['f_mensaje_cliente']);
    $comi = !empty($_POST['f_comision']) ? $_POST['f_comision'] : 0;
    
    $v_cl = isset($_POST['f_visible_cliente']) ? 1 : 0;
    $v_so = isset($_POST['f_visible_soporte']) ? 1 : 0;
    $f_au = isset($_POST['f_fact_auto']) ? 1 : 0;
    $g_cb = isset($_POST['f_generar_comision_bancaria']) ? 1 : 0;
	$v_ia = isset($_POST['f_visible_icarobot_ia']) ? 1 : 0; // Agregado
    $v_al = isset($_POST['f_visible_aliado']) ? 1 : 0;  // Agregado	
	$v_cxp = isset($_POST['f_generar_comision_bancaria_cxp']) ? 1 : 0;
	
	if ($v_cxp == 0) {
		$v_f_comi = "'0000-00-00'"; 
	} else {
		$v_f_comi = sc_sql_injection($_POST['f_fecha_inicio_comision']);
	}
		
	$v_p_comi = !empty($_POST['f_porcentaje_comision_bancaria']) ? 	$_POST['f_porcentaje_comision_bancaria'] : 0.01;
		
    $c_prod = sc_sql_injection($_POST['f_codigo_productos']); 

    if(empty($id_f)) {
        // INSERTAR NUEVA FORMA (Columnas duplicadas eliminadas)
        $sql = "INSERT INTO banco_formas_pago (
			codigo_formas_pago,             /* 01 */
			nombre_formas_pago,             /* 02 */
			moneda_convertible,             /* 03 */
			porc_reten,                     /* 04 */
			codigo_tipo_pago,               /* 05 */
			codigo_banco,                   /* 06 */
			codigo_moneda,                  /* 07 */
			requiere_referencia,            /* 08 */
			usuario,                        /* 09 */
			fecha,                          /* 10 */
			empresa,                        /* 11 */
			sucursal,                       /* 12 */
			ip_usuario,                     /* 13 */
			mensaje_cliente,                /* 14 */
			comision,                       /* 15 */
			fact_auto,                      /* 16 */
			generar_comision_bancaria,      /* 17 */
			codigo_productos,               /* 18 */
			cuenta_padre,                   /* 19 */
			cuenta_hijo,                    /* 20 */
			visible_cliente,                /* 21 */
			visible_soporte,                /* 22 */
			visible_aliado,                 /* 23 */
			visible_icarobot_ia,            /* 24 */
			generar_comision_bancaria_cxp, /* 25 */
			porcentaje_comision_bancaria,   /* 26 */
			fecha_inicio_comision           /* 27 */
		) VALUES (
			$c_fp,                          /* 01 */
			$n_fp,                          /* 02 */
			$m_cv,                          /* 03 */
			$p_rt,                          /* 04 */
			$c_tp,                          /* 05 */
			$c_ba,                          /* 06 */
			$c_mo,                          /* 07 */
			$r_re,                          /* 08 */
			'$usr_login',                   /* 09 */
			'".date('Y-m-d')."',            /* 10 */
			'$usr_empresa',                 /* 11 */
			'$usr_sucursal',                /* 12 */
			'".$_SERVER['REMOTE_ADDR']."',  /* 13 */
			$m_cl,                          /* 14 */
			$comi,                          /* 15 */
			$f_au,                          /* 16 */
			$g_cb,                          /* 17 */
			$c_prod,                        /* 18 */
			' ',                            /* 19 */
			' ',                            /* 20 */
			$v_cl,                          /* 21 */
			$v_so,                          /* 22 */
			$v_al,                          /* 23 */
			$v_ia,                          /* 24 */
			$v_cxp,                         /* 25 */
			$v_p_comi,                      /* 26 */
			$v_f_comi                       /* 27 */
		)";
    } else {
        // ACTUALIZAR FORMA EXISTENTE (Asignaciones duplicadas eliminadas)
        $sql = "UPDATE banco_formas_pago SET 
                codigo_formas_pago          	=   $c_fp, 
                nombre_formas_pago          	=   $n_fp, 
                moneda_convertible          	=   $m_cv, 
                porc_reten                  	=   $p_rt, 
                codigo_banco                	=   $c_ba, 
                codigo_moneda               	=   $c_mo, 
                requiere_referencia         	=   $r_re, 
                mensaje_cliente             	=   $m_cl, 
                comision                    	=   $comi, 
                fact_auto                   	=   $f_au, 
                generar_comision_bancaria   	=   $g_cb, 
                codigo_productos            	=   $c_prod,
                visible_cliente             	=   $v_cl, 
                visible_soporte            		=   $v_so, 
                visible_aliado          	    =   $v_al, 
                visible_icarobot_ia  	       	=   $v_ia,
	            generar_comision_bancaria_cxp 	= 	$v_cxp,
	            porcentaje_comision_bancaria  	= 	$v_p_comi,
				fecha_inicio_comision 			=   $v_f_comi
                WHERE id_banco_formas_pago=".sc_sql_injection($id_f);
    }

    sc_exec_sql($sql);
    header("Location: ".$current_url); 
    exit;
}
// (Resto de la lógica btn_save para Tipo Pago se mantiene igual)

sc_lookup(ds_bancos, "SELECT codigo_banco, nombre_banco FROM bancos WHERE empresa = '$usr_empresa' ORDER BY nombre_banco");
sc_lookup(ds_monedas, "SELECT codigo_moneda, nombre FROM configuracion_moneda ORDER BY nombre");

// Cargar productos para el selector
sc_lookup(ds_productos_inv, "SELECT codigo_productos, nombre_productos 
    FROM inventario_productos 
    WHERE empresa = '$usr_empresa' 
      AND sucursal = '$usr_sucursal' 
      AND producto_matriz = 'SI' 
      AND codigo_hijo IS NOT NULL AND codigo_hijo <> ''
      AND codigo_padre IS NOT NULL AND codigo_padre <> ''
    ORDER BY nombre_productos ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { 
			background-color: #f8f9fa; 
			padding: 20px; font-family: 'Segoe UI', sans-serif; 
		}
		
        .main-card { 
			border-radius: 5px; 
			box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
			background: #fff; 
		}
		
        .table thead th { 
			background-color: #2d3e50; 
			color: #fff; 
			padding: 12px; 
		}
		
		.badge-custom { 
			border-radius: 4px; 
			padding: 6px 12px; 
			font-weight: 700; 
			color: #fff; 
			display: inline-block; /* Permite aplicar un ancho fijo */
			width: 120px;           /* Ajusta este valor al ancho que desees */
			text-align: center;    /* Centra el texto dentro del badge */
		}
		
        .bg-activo { 
			background-color: #2d3e50; 
		} 
		
		.bg-inactivo { 
			background-color: #dc3545; 
		}
		
        .ios-switch { 
			position: relative; 
			display: inline-block; 
			width: 44px; 
			height: 22px; 
		}
		
        .ios-switch input { 
			opacity: 0; 
			width: 0; 
			height: 0; 
		}
		
        .slider { 
			position: absolute;
			cursor: pointer; 
			top: 0; 
			left: 0; 
			right: 0; 
			bottom: 0; 
			background-color: #ccc; 
			transition: .4s; 
			border-radius: 34px; 
		}	
		
        .slider:before { 
			position: absolute; 
			content: ""; 
			height: 18px; 
			width: 18px; 
			left: 2px; 
			bottom: 2px; 
			background-color: white; 
			transition: .4s; 
			border-radius: 50%; 
		}
        input:checked + .slider { 
			background-color: #5dade2; 
		}
		
        input:checked + .slider:before { 
			transform: translateX(22px); 
			content: '✓'; font-size: 10px; 
			color: #5dade2; 
			text-align: center; 
			line-height: 18px; 
		}
		
		.info-icon { 
			color: #17a2b8; 
			cursor: help; 
			margin-left: 5px; 
			font-size: 0.8rem; 
			vertical-align: middle;
		}		
    </style>
</head>
<body>

<!-- ACCIONES SUPERIORES (FUERA DE LA CARD) -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Tipos y Formas de Pago</h4>
    <div class="d-flex align-items-center">
        <input type="text" id="quick_search" class="form-control form-control-sm mr-2" style="width: 280px;" placeholder="Filtrar registros...">
        <button class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="fas fa-plus"></i> Nuevo Registro
        </button>		
		<!-- BOTÓN WIKI CORREGIDO -->
        <?php 
            $wiki_url = !empty(trim($var_wiki)) ? (strpos(trim($var_wiki), 'http') === 0 ? trim($var_wiki) : 'https://'.trim($var_wiki)) : '#';
        ?>
        <a href="<?php echo $wiki_url; ?>" target="_blank" class="btn btn-info btn-sm ml-2 shadow-none" style="height: 31px; display: inline-flex; align-items: center; justify-content: center; border: none; outline: none; gap: 5px;" <?php if($wiki_url == '#') echo 'onclick="return false;" style="opacity:0.5; cursor:not-allowed;"'; ?>>
            <i class="fas fa-question-circle"></i> Wiki
        </a>		
    </div>
</div>

<!-- TABLA PRINCIPAL -->
<div class="card main-card">
    <div class="p-0"> <!-- Quitamos el header para que la tabla empiece directamente o puedes dejar un p-3 -->
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Tipo Pago</th>
                        <th class='text-center'>Estatus</th>
                        <th>Visibilidad</th>
                        <th class='text-center'>Acciones</th>
                    </tr>
                </thead>
                <tbody id="table_body"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TIPO PAGO -->
<div class="modal fade" id="modalTipoPago" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form_tipo" method="POST" novalidate>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="lblTitleTipo">Gestionar Tipo de Pago</h5>				
                </div>
                <div class="modal-body">
                    <input type="hidden" name="t_id_pk" id="t_id_pk">
                    <div class="form-group">
                        <label class="small font-weight-bold">CÓDIGO TIPO *</label>
						<span class="info-icon" data-toggle="tooltip" title="Código único alfanumérico para identificar el tipo de pago (máx. 10 carac.).">(?)</span>
						<input type="text" name="t_codigo" id="t_codigo" class="form-control" maxlength="10" 
							   style="text-transform: uppercase;" 
							   oninput="this.value = this.value.toUpperCase()" 
							   required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">NOMBRE TIPO PAGO *</label>
						<span class="info-icon" data-toggle="tooltip" title="Nombre del Tipo Pago (Ej: EFECTIVO, TRANSFERENCIA, TARJETA).">(?)</span>
                        <input type="text" name="t_nombre" id="t_nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">ESTATUS</label>
						<span class="info-icon" data-toggle="tooltip" title="Segun su Estatus estara Disponible o NO, al momento de Realizar una Transaccion.">(?)</span>
                        <select name="t_estatus" id="t_estatus" class="form-control">
                            <option value="Activo">ACTIVO</option>
                            <option value="Inactivo">INACTIVO</option>
                        </select>
                    </div>
                    <hr>
                   
					<hr>
					<label class="small font-weight-bold">
						CONFIGURACIÓN DE VISIBILIDAD</label>
						<span class="info-icon" data-toggle="tooltip" title="Este bloque activamos la visibilidad en los diferentes portales">(?)</span>
					<div class="row text-center">
						<div class="col-3">
							<label class="small d-block">Clientes</label>
							<label class="ios-switch">
								<input type="checkbox" name="t_v_cli" id="t_v_cli">
								<span class="slider"></span>
							</label>
						</div>
						<div class="col-3">
							<label class="small d-block">Soporte</label>
							<label class="ios-switch">
								<input type="checkbox" name="t_v_sop" id="t_v_sop">
								<span class="slider"></span>
							</label>
						</div>
						<div class="col-3">
							<label class="small d-block">Aliados</label>
							<label class="ios-switch">
								<input type="checkbox" name="t_v_ali" id="t_v_ali">
								<span class="slider"></span>
							</label>
						</div>
						<div class="col-3">
							<label class="small d-block">Administración</label>
							<label class="ios-switch">
								<input type="checkbox" name="t_v_adm" id="t_v_adm">
								<span class="slider"></span>
							</label>
						</div>
						<div class="col-4">
							<label class="small d-block">Retención (Portal Clientes)</label>
							<label class="ios-switch"><input type="checkbox" name="t_retencion" id="t_retencion"><span class="slider"></span></label>
						</div>							
					</div>					
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
					<button type="button" onclick="validarGuardarTipo()" class="btn btn-primary btn-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>	
	
	
<!-- MODAL FORMA PAGO (EL DE LA IMAGEN) -->
<div class="modal fade" id="modalFormaPago" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form id="form_forma" method="POST" novalidate>
                <div class="modal-header bg-dark text-white"><h5 class="modal-title" id="lblTitleForma">Gestionar Forma</h5>
				</div>
                <div class="modal-body">
                    <input type="hidden" name="f_id_pk" id="f_id_pk">
                    <input type="hidden" name="f_codigo_tipo_pago" id="f_codigo_tipo_pago">
                    
                    <div class="row"><!-- LINEA 1 -->
                        <div class="col-md-3 form-group">
							<label class="small font-weight-bold">CÓDIGO FORMA *</label>
							<span class="info-icon" data-toggle="tooltip" title="Código abreviado de la forma de pago (Ej: ZELLE, P_MOVIL).">(?)</span>
							<input type="text" name="f_codigo_formas_pago" id="f_codigo_formas_pago" class="form-control" maxlength="10" 
								   style="text-transform: uppercase;" 
								   oninput="this.value = this.value.toUpperCase()" 
								   required>
							
						</div>                        
						<div class="col-md-4 form-group">
							<label class="small font-weight-bold">NOMBRE FORMA *</label>
							<span class="info-icon" data-toggle="tooltip" title="Nombre completo que aparecerá en los puntos de venta o facturación.">(?)</span>
							<input type="text" name="f_nombre_formas_pago" id="f_nombre_formas_pago" class="form-control" required>
						</div>
												
						  <div class="col-md-5 form-group">
							<label class="small font-weight-bold">BANCO *</label>
							<span class="info-icon" data-toggle="tooltip" title="Seleccione el banco donde se recibe el dinero de esta forma de pago.">(?)</span>
							<select name="f_codigo_banco" id="f_codigo_banco" class="form-control"><option value="">-- Seleccione --</option><?php foreach($ds_bancos as $b) echo "<option value='".$b[0]."'>".$b[1]."</option>"; ?></select>
						</div>
                    </div> <!-- LINEA 1 FIN -->

                    <div class="row"> <!-- LINEA 2  -->                     
                        <div class="col-md-4 form-group">
							<label class="small font-weight-bold">MONEDA *</label>
							<span class="info-icon" data-toggle="tooltip" title="Moneda en la que se registra el ingreso (Bs, USD, etc).">(?)</span>							
							<select name="f_codigo_moneda" id="f_codigo_moneda" class="form-control"><option value="">-- Seleccione --</option><?php foreach($ds_monedas as $m) echo "<option value='".$m[0]."'>".$m[1]."</option>"; ?></select>
						</div>				
						<div class="col-md-4 form-group">
							<label class="small font-weight-bold">MONEDA CONV. *</label>
							<span class="info-icon" data-toggle="tooltip" title="¿Esta forma de pago permite conversión de divisas automáticamente?">(?)</span>
							<select name="f_moneda_convertible" id="f_moneda_convertible" class="form-control">
								<option value="SI">SI</option><option value="NO">NO</option></select>
						</div>			
										
                        <div class="col-md-4 form-group">
							<label class="small font-weight-bold">REQUIERE REF. *</label>
							 <span class="info-icon" data-toggle="tooltip" title="Indica si el sistema debe pedir obligatoriamente un número de referencia/comprobante.">(?)</span>
							<select name="f_requiere_referencia" id="f_requiere_referencia" class="form-control"><option value="SI">SI</option><option value="NO">NO</option></select>
						</div>
                    </div> <!-- LINEA 2 FIN -->    

                    <div class="row"> <!-- LINEA 3  -->    						
						<div class="col-md-3 form-group text-center">
							<label class="small font-weight-bold" style="white-space: nowrap;">Genera Comisión Bancaria Cliente</label>
							<br>
							<label class="ios-switch">
								<input type="checkbox" name="f_generar_comision_bancaria" id="f_generar_comision_bancaria">
								<span class="slider"></span>
							</label>
    					</div>																
                        <div class="col-md-2 form-group">
							<label class="small font-weight-bold">COMISIÓN</label>
							 <span class="info-icon" data-toggle="tooltip" title="Porcentaje de comisión bancaria o de plataforma aplicado por cada transacción realizada.">(?)</span>
							<input type="number" step="0.01" name="f_comision" id="f_comision" class="form-control">
						</div>
                        <div class="col-md-2 form-group">
							<label class="small font-weight-bold">% RETENCIÓN</label>
							<span class="info-icon" data-toggle="tooltip" title="Porcentaje de retención de impuestos (IVA/ISLR) que se aplica automáticamente a esta forma de pago.">(?)</span>
							<input type="number" step="0.01" name="f_porc_reten" id="f_porc_reten" class="form-control">
						</div>
                        <div class="col-md-5 form-group">
							<label class="small font-weight-bold">MENSAJE CLIENTE</label>	
							<span class="info-icon" data-toggle="tooltip" title="Instrucciones o notas aclaratorias que el cliente verá en su pantalla al momento de seleccionar este método de pago.">(?)</span>
							<input type="text" name="f_mensaje_cliente" id="f_mensaje_cliente" class="form-control">
						</div>
                    </div> <!-- LINEA 3 FIN-->    
					
					
					<!-- LINEA 4: CONFIGURACIÓN DE VISIBILIDAD -->
					<div class="row border-top mt-3 pt-2"> 
						<div class="col-md-12">								
							<label class="font-weight-bold text-secondary">CONFIGURACIÓN DE VISIBILIDAD</label>
							<span class="info-icon" data-toggle="tooltip" title="Active los portales donde será visible esta forma de pago.">(?)</span>

							<div class="row mt-2">
								<div class="col-md-3 text-center">
									<label class="small d-block font-weight-bold">Clientes</label>
									<label class="ios-switch"><input type="checkbox" name="f_visible_cliente" id="f_visible_cliente"><span class="slider"></span></label>
								</div>
								<div class="col-md-3 text-center">
									<label class="small d-block font-weight-bold">Soporte</label>
									<label class="ios-switch"><input type="checkbox" name="f_visible_soporte" id="f_visible_soporte"><span class="slider"></span></label>
								</div>
								<div class="col-md-3 text-center">
									<label class="small d-block font-weight-bold">Aliados</label>
									<label class="ios-switch"><input type="checkbox" name="f_visible_aliado" id="f_visible_aliado"><span class="slider"></span></label>
								</div>
								<div class="col-md-3 text-center">
									<label class="small d-block font-weight-bold">IcaroBot IA</label>
									<label class="ios-switch"><input type="checkbox" name="f_visible_icarobot_ia" id="f_visible_icarobot_ia"><span class="slider"></span></label>
								</div>
							</div>	  	
						</div>
					</div>
					<!-- LINEA 4 FIN -->	
                    <hr>				
					<!-- LINEA 5: CONFIGURACIÓN FINANCIERA CXP -->
					<div class="row mt-3"> 
						<div class="col-md-5 form-group">
							<label class="small font-weight-bold">Generar Comisión Bancaria CxP</label>
							<span class="info-icon" data-toggle="tooltip" title="¿Esta forma de pago genera una comisión automática en las cuentas por pagar?">(?)</span>
							<br>
							<label class="ios-switch">
								<input type="checkbox" name="f_generar_comision_bancaria_cxp" id="f_generar_comision_bancaria_cxp">
								<span class="slider"></span>
							</label>
						</div>

						<div class="col-md-3 form-group">
							<label class="small font-weight-bold">% Comis. Bancaria</label>
							<span class="info-icon" data-toggle="tooltip" title="Porcentaje de comisión bancaria para el cálculo automático.">(?)</span>
							<input type="number" step="0.01" name="f_porcentaje_comision_bancaria" id="f_porcentaje_comision_bancaria" class="form-control">
						</div>

						<div class="col-md-4 form-group">
							<label class="small font-weight-bold">Fecha Inicio Comisión</label>
							<span class="info-icon" data-toggle="tooltip" title="Fecha a partir de la cual se aplica el porcentaje de comisión.">(?)</span>
							<input type="date" name="f_fecha_inicio_comision" id="f_fecha_inicio_comision" class="form-control">
						</div>
					</div> 

					<!-- LINEA 6: AUXILIAR CONTABLE -->
					<div class="row">
						<div class="col-md-12 form-group">
							<label class="small font-weight-bold text-muted">PRODUCTO (CÓDIGO)</label>
							<span class="info-icon" data-toggle="tooltip" title="Producto de inventario vinculado para la integración contable.">(?)</span>
							<select name="f_codigo_productos" id="f_codigo_productos" class="form-control">
								<option value="">-- Seleccione un Producto --</option>
								<?php 
								if (isset($ds_productos_inv) && is_array($ds_productos_inv)) {
									foreach($ds_productos_inv as $p) {
										echo "<option value='{$p[0]}'>{$p[1]} ({$p[0]})</option>";
									}
								}
								?>
							</select>					
						</div>
					</div>
					
				</div>
                <div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
					<button type="button" onclick="validarGuardarForma()" class="btn btn-primary btn-sm">Guardar Forma</button>
				</div>
            </form>
        </div>
    </div>
</div>

<script>
    const myAppUrl = '<?php echo $current_url; ?>';

	$(document).ready(function() {
		// CORRECCIÓN PARA SCRIPTCASE: Concatenamos el string del selector
		// para que no se interprete como una Variable Global [variable]
		$('[' + 'data-toggle="tooltip"' + ']').tooltip();

		loadTable(1);

		// Temporizador para búsqueda rápida
		$('#quick_search').on('input', function() { 
			clearTimeout(window.searchTimer); 
			window.searchTimer = setTimeout(() => loadTable(1), 300); 
		});
		
		// Escuchar cambios en el switch de Comisión Bancaria Cliente
		$('#f_generar_comision_bancaria').on('change', function() {
			if ($(this).is(':checked')) {
				$('#f_comision').prop('disabled', false);
			} else {
				$('#f_comision').prop('disabled', true).val('0.00'); // Deshabilita y resetea a 0
			}
		});		
		
		// Escuchar cambios en el switch de Generar Comisión Bancaria CxP
		$('#f_generar_comision_bancaria_cxp').on('change', function() {
			const isChecked = $(this).is(':checked');
			// Habilitar o deshabilitar los 3 campos dependientes
			$('#f_porcentaje_comision_bancaria, #f_fecha_inicio_comision, #f_codigo_productos').prop('disabled', !isChecked);

			if (!isChecked) {
				// Opcional: Limpiar valores al deshabilitar
				$('#f_porcentaje_comision_bancaria').val('0.00');
				$('#f_fecha_inicio_comision').val('');
				$('#f_codigo_productos').val('');
			}
		});		
	});

    // --- FUNCIONES TABLA PRINCIPAL (TIPO DE PAGO) ---

    function loadTable(pag) {
        $.ajax({ 
            url: myAppUrl, 
            type: 'GET', 
            data: { ajax_mode: 1, pag: pag, search: $('#quick_search').val() }, 
            success: function(res) { 
                $('#table_body').html(res.rows); 
            } 
        });
    }

	function openModal() {
		$('#form_tipo')[0].reset();
		$('#t_id_pk').val('');

		// IMPORTANTE: Asegurar que los campos estén habilitados para nuevos registros
		$('#t_codigo, #t_nombre').prop('disabled', false);

		$('#lblTitleTipo').text('Nuevo Tipo de Pago');
		$('#modalTipoPago').modal('show');
	}
	
	function editRow(data) {
		$('#form_tipo')[0].reset(); 
		$('#t_id_pk').val(data[0]);
		$('#t_codigo').val(data[1]);
		$('#t_nombre').val(data[2]);
		$('#t_estatus').val(data[3]);

		// Checkboxes de visibilidad (mantén tu lógica actual)
		$('#t_v_cli').prop('checked', data[4] == 1);
		$('#t_v_sop').prop('checked', data[5] == 1);
		$('#t_v_ali').prop('checked', data[6] == 1);
		$('#t_v_adm').prop('checked', data[7] == 1);
		$('#t_retencion').prop('checked', data[8] == 1);

		// --- NUEVA LÓGICA DE BLOQUEO ---
		// data[9] contiene el conteo de banco_formas_pago (total_formas en tu SQL)
		const tieneRegistrosVinculados = (parseInt(data[9]) > 0);

		// Deshabilita si tiene registros, habilita si no
		$('#t_codigo, #t_nombre').prop('disabled', tieneRegistrosVinculados);

		$('#lblTitleTipo').text('Editar Registro');
		// --- FIN LÓGICA DE BLOQUEO ---

		$('#modalTipoPago').modal('show');
	}

	function confirmDelete(id) {
        Swal.fire({ 
            title: '¿Borrar Tipo de Pago?', 
            text: "Esta acción no se puede deshacer.",
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, borrar',
            cancelButtonText: 'Cancelar'
        }).then((r) => { 
            if (r.isConfirmed) {
                // Al presionar Sí, recargamos la página con los parámetros de acción
                window.location.href = myAppUrl + '?action=delete_tipo&id_tipo=' + id;
            }
        });
    }

    // --- FUNCIONES SUB-TABLA (FORMAS DE PAGO) ---

    function toggleSubTable(btn, codigo, id) {
        const row = $(`#child_${id}`);
        if (row.is(':visible')) { 
            row.hide(); 
        } else { 
            $.ajax({ 
                url: myAppUrl, 
                type: 'GET', 
                data: { get_formas_pago: codigo }, 
                success: function(h) { 
                    $(`#container_${id}`).html(h); 
                    row.show(); 
					// REINICIALIZAR AYUDAS EN LA SUBTABLA (Sintaxis Scriptcase)
					$('[' + 'data-toggle="tooltip"' + ']').tooltip();
                } 
            }); 
        }
    }

    function openModalNuevaForma(codigo_tipo) {
        $('#form_forma')[0].reset();
        $('#f_id_pk').val('');
        $('#f_codigo_tipo_pago').val(codigo_tipo);
        $('#lblTitleForma').text('Nueva Forma para: ' + codigo_tipo);
		
		// Iniciar campos dependientes deshabilitados por defecto
    	$('#f_porcentaje_comision_bancaria, #f_fecha_inicio_comision, #f_codigo_productos').prop('disabled', true);
		
		$('#f_codigo_formas_pago, #f_nombre_formas_pago').prop('disabled', false);		
        $('#modalFormaPago').modal('show');
    }

	function editFormaPago(data, codigo_tipo) {
		// 1. Limpiar el formulario antes de cargar datos
		$('#form_forma')[0].reset();

		// 2. Asignación de IDs y códigos de relación
		$('#f_id_pk').val(data[0]);              // id_banco_formas_pago
		$('#f_codigo_tipo_pago').val(codigo_tipo); 
		
		// Iniciar campo de comisión deshabilitado por defecto
    	$('#f_comision').prop('disabled', true); 

		// 3. CAMPOS DE TEXTO Y SELECTS (Mapeo según SELECT de Logica AJAX 2)
		$('#f_codigo_formas_pago').val(data[1]); // <--- Aquí se asigna el Código de la Forma
		$('#f_nombre_formas_pago').val(data[2]);
		$('#f_codigo_banco').val(data[3]);
		$('#f_codigo_moneda').val(data[4]);
		$('#f_comision').val(data[5]);
		$('#f_moneda_convertible').val(data[6]);
		$('#f_porc_reten').val(data[7]);
		$('#f_requiere_referencia').val(data[8]);
		$('#f_mensaje_cliente').val(data[9]);

		// 4. CHECKBOXES (Visibilidad y Funciones)
		// Convertimos a boolean comparando con 1
		$('#f_visible_cliente').prop('checked', data[10] == 1);
		$('#f_visible_soporte').prop('checked', data[11] == 1);
		$('#f_fact_auto').prop('checked', data[12] == 1);
		$('#f_generar_comision_bancaria').prop('checked', data[13] == 1);

		// 5. OTROS CAMPOS
		$('#f_codigo_productos').val(data[17]);
		
		// NUEVOS CAMPOS
 	    $('#f_visible_icarobot_ia').prop('checked', data[18] == 1);
	    $('#f_visible_aliado').prop('checked', data[19] == 1);

		$('#f_generar_comision_bancaria_cxp').prop('checked', data[20] == 1);
        $('#f_porcentaje_comision_bancaria').val(data[21]);		
		$('#f_fecha_inicio_comision').val(data[22]);

		// 6. INTERFAZ
		$('#lblTitleForma').text('Editar Forma: ' + data[2]); // Muestra el nombre en el título
		
		
		// --- NUEVA LÓGICA DE BLOQUEO POR USO ---
		const estaEnUso = (data[23] == 1); // El nuevo índice que agregamos en el SQL

		// Deshabilitar CÓDIGO y NOMBRE si tiene transacciones
		$('#f_codigo_formas_pago, #f_nombre_formas_pago').prop('disabled', estaEnUso);

//		if (estaEnUso) {
//			$('#lblTitleForma').text('Consultar Forma: ' + data[2] + ' (Protegida por Movimientos)');
//		} else {
			$('#lblTitleForma').text('Editar Forma: ' + data[2]);
	//	}
		// --- FIN LÓGICA DE BLOQUEO ---	
		
		$('#modalFormaPago').modal('show');
		
		// Lógica para habilitar/deshabilitar campo COMISIÓN según el valor cargado
		const generaComision = (data[13] == 1); // Índice 13 es generar_comision_bancaria
		$('#f_generar_comision_bancaria').prop('checked', generaComision);
		$('#f_comision').prop('disabled', !generaComision); 		
		
		 // Lógica para habilitar/deshabilitar según el valor cargado (Índice 20)
		const generaCxP = (data[20] == 1); 
		$('#f_generar_comision_bancaria_cxp').prop('checked', generaCxP);
		$('#f_porcentaje_comision_bancaria, #f_fecha_inicio_comision, #f_codigo_productos').prop('disabled', !generaCxP);	
	}

	function confirmDeleteForma(id) {
		Swal.fire({ 
			title: '¿Borrar Forma de Pago?', 
			text: "Se validará si tiene movimientos antes de borrar.",
			icon: 'warning', 
			showCancelButton: true,
			confirmButtonColor: '#dc3545',
			confirmButtonText: 'Sí, borrar'
		}).then((r) => { 
			if (r.isConfirmed) {
				window.location.href = myAppUrl + '?action=delete_forma&id_forma=' + id;
			}
		});
	}
	
	function validarGuardarTipo() {
		// jQuery .val() sí obtiene el valor aunque esté disabled
		const cod = $('#t_codigo').val().trim();
		const nom = $('#t_nombre').val().trim();
		const id_pk = $('#t_id_pk').val(); 

		if (cod === "" || nom === "") {
			Swal.fire({ 
				icon: 'error', 
				title: 'Campos Obligatorios', 
				text: 'Todos los campos marcados con (*) son obligatorios.' 
			});
			return;
		}

		if (id_pk === "") {
			// Lógica de duplicados para NUEVOS registros
			$.get(myAppUrl, { action: 'check_duplicate_codigo', codigo: cod }, function(res) {
				if (res === 'existe') {
					Swal.fire({ icon: 'error', title: 'Código Duplicado', text: 'El código ' + cod + ' ya existe.' });
				} else {
					enviarFormularioTipo();
				}
			});
		} else {
			// Para EDICIÓN: habilitamos antes de enviar para que el PHP reciba los datos
			enviarFormularioTipo();
		}
	}

	// Función auxiliar para habilitar y enviar
	function enviarFormularioTipo() {
		$('#t_codigo, #t_nombre').prop('disabled', false); // <--- CLAVE: Habilitar antes de enviar
		$('<input>').attr({type: 'hidden', name: 'btn_save_tipo', value: '1'}).appendTo('#form_tipo');
		$('#form_tipo').submit();
	}
	
	// Función para validar y enviar Forma de Pago
	function validarGuardarForma() {
		const cod = $('#f_codigo_formas_pago').val().trim();
		const nom = $('#f_nombre_formas_pago').val().trim();
		const banco = $('#f_codigo_banco').val();
		const moneda = $('#f_codigo_moneda').val();
		const id_pk = $('#f_id_pk').val();

		// 1. Validaciones básicas
		if (cod === "" || nom === "" || banco === "" || moneda === "") {
			Swal.fire({ icon: 'error', title: 'Atención', text: 'Todos los campos marcados con (*) son obligatorios.' });
			return;
		}

		// 2. Validación de campos CxP si el switch está activo
		if ($('#f_generar_comision_bancaria_cxp').is(':checked')) {
			const porc = $('#f_porcentaje_comision_bancaria').val().trim();
			const fecha = $('#f_fecha_inicio_comision').val().trim();
			const prod = $('#f_codigo_productos').val();

			if (porc === "" || fecha === "" || prod === "" || fecha === "0000-00-00") {
				Swal.fire({ icon: 'warning', title: 'Campos CxP Requeridos', text: 'El porcentaje, la fecha y el producto son obligatorios cuando CxP está activo.' });
				return;
			}

			// 3. NUEVA VALIDACIÓN AJAX: Verificar proveedor del banco en la base de datos
			$.get(myAppUrl, { action: 'verificar_banco_proveedor', codigo_banco: banco }, function(res) {
				if (res === 'sin_proveedor') {
					Swal.fire({
						icon: 'error',
						title: 'Validación CxP Fallida',
						text: 'El banco seleccionado no tiene un proveedor válido asignado (NULL o 0). Configure el banco antes de activar CxP.',
						confirmButtonColor: '#3085d6'
					});
					// El modal NO se cierra porque no hemos llamado a enviarFormularioForma()
				} else {
					// Si el banco es válido, verificamos duplicados de código (lógica que ya tenías)
					verificarDuplicadoYEnviar(id_pk, cod);
				}
			});
		} else {
			// Si CxP no está activo, validamos duplicados directamente
			verificarDuplicadoYEnviar(id_pk, cod);
		}
	}

	// Función auxiliar para no repetir código de duplicados
	function verificarDuplicadoYEnviar(id_pk, cod) {
		if (id_pk === "") {
			$.get(myAppUrl, { action: 'check_duplicate_forma', codigo_forma: cod }, function(res) {
				if (res === 'existe') {
					Swal.fire({ icon: 'error', title: 'Código Duplicado', text: 'El código de forma "' + cod + '" ya existe.' });
				} else {
					enviarFormularioForma();
				}
			});
		} else {
			enviarFormularioForma();
		}
	}

	// NUEVA FUNCIÓN AUXILIAR PARA FORMAS
	function enviarFormularioForma() {
		// CLAVE: Habilitar todos los campos antes del submit para que el PHP reciba los datos
		$('#form_forma').find(':input').prop('disabled', false); 

		$('<input>').attr({type: 'hidden', name: 'btn_save_forma', value: '1'}).appendTo('#form_forma');
		$('#form_forma').submit();
	}	
</script>
</body>
</html>
<?php
