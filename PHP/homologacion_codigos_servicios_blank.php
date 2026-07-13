// --- 1. CARGA INICIAL DE PRODUCTOS PARA EL SELECT2 ---
$options_array = array(); // Optimización de memoria
$check_sql = "SELECT codigo_productos, nombre_productos
              FROM inventario_productos 
              WHERE empresa = '[usr_empresa]' 
              AND sucursal = '[usr_sucursal]'
              AND (codigo_tiposerv_productos = 'S001'
			  OR codigo_tiposerv_productos = 'TV001')	  
              AND estatus_productos = 'A'
              ORDER BY nombre_productos ASC";

sc_lookup(ds_prod, $check_sql);

if (!empty({ds_prod})) {
    foreach ({ds_prod} as $fila) {
        $prod_cod   = $fila[0];
        // Un solo paso de limpieza y formateo
        $prod_nom   = str_pad(substr(strtoupper($fila[1]), 0, 40), 40, " ", STR_PAD_RIGHT);
        $prod_data  = addslashes($prod_nom); 
        
        $options_array[] = "<option value='$prod_cod' data-nombre='$prod_data'>$prod_nom-$prod_cod</option>";        
    }
}
$productos_options = implode("", $options_array);

// --- 2. LÓGICA AJAX: PROCESO TRANSACCIONAL DE MIGRACIÓN Y VALIDACIÓN ---
if (isset($_POST['accion'])) {
    
    // CASO A: VALIDACIÓN DE EXISTENCIA
    if ($_POST['accion'] == 'validar') {
        $codigo = $_POST['codigo'];
        if (empty($codigo)) { echo "error_codigo"; exit; }
        $check_existe = "SELECT COUNT(*) FROM inventario_productos WHERE codigo_productos = '$codigo' AND (codigo_tiposerv_productos = 'S001' OR codigo_tiposerv_productos = 'TV001')
		AND estatus_productos = 'A' AND empresa = '[usr_empresa]' AND sucursal = '[usr_sucursal]'";
        sc_lookup(ds_existe, $check_existe);
        if (empty({ds_existe}) || {ds_existe[0][0]} == 0) { echo "error_inexistente"; } else { echo "OK"; }
        exit;
    }

    // CASO B: EJECUCIÓN REAL DE LA MIGRACIÓN
// CASO B: EJECUCIÓN REAL DE LA MIGRACIÓN
    if ($_POST['accion'] == 'migrar') {
        $codigo_servicio_anterior = $_POST['codigo_anterior'];
        $codigo_servicio_nuevo    = $_POST['codigo_nuevo']; 
        
        if (empty($codigo_servicio_anterior) || empty($codigo_servicio_nuevo)) {
            echo "error_campos_vacios";
            exit;
        }

        // A) Obtener el nombre del nuevo servicio
        $sql_nombre = "SELECT nombre_productos FROM inventario_productos 
                       WHERE codigo_productos = '$codigo_servicio_nuevo' 
                         AND empresa = '[usr_empresa]' 
                         AND sucursal = '[usr_sucursal]'";
        sc_lookup(ds_nom_nuevo, $sql_nombre);
        
        if (empty({ds_nom_nuevo}) || !isset({ds_nom_nuevo[0][0]})) {
            echo "error_inexistente";
            exit;
        }
        $nombre_nuevo = addslashes({ds_nom_nuevo[0][0]});

        // =====================================================================
        // DETECCIÓN EN VIVO: Guardamos los IDs de lo que SÍ se va a modificar
        // =====================================================================
        $ids_factura_a_modificar = array(-1);
        $sql_pre_fac = "SELECT cfp.id_configuracion_factura 
                        FROM configuracion_factura_productos cfp
                        JOIN configuracion_factura cf ON cfp.id_configuracion_factura = cf.id
                        JOIN servicio_cliente sc ON cf.id_servicio = sc.id_servicio_cliente
                        WHERE sc.empresa = '[usr_empresa]'
                          AND sc.sucursal = '[usr_sucursal]'
                          AND sc.codigo_producto = '$codigo_servicio_anterior'";
        sc_lookup(ds_pre_fac, $sql_pre_fac);
        if (!empty({ds_pre_fac})) {
            foreach({ds_pre_fac} as $p_fac) { $ids_factura_a_modificar[] = $p_fac[0]; }
        }

        $ids_cliente_a_modificar = array(-1);
        $sql_pre_cli = "SELECT id_servicio_cliente 
                        FROM servicio_cliente 
                        WHERE empresa = '[usr_empresa]' 
                          AND sucursal = '[usr_sucursal]' 
                          AND codigo_producto = '$codigo_servicio_anterior'";
        sc_lookup(ds_pre_cli, $sql_pre_cli);
        if (!empty({ds_pre_cli})) {
            foreach({ds_pre_cli} as $p_cli) { $ids_cliente_a_modificar[] = $p_cli[0]; }
        }
        // =====================================================================

        // B) Iniciar la transacción segura
        sc_begin_trans();

        // C) Ejecutar actualización en la tabla: configuracion_factura_productos
        $update_productos_sql = "UPDATE configuracion_factura_productos cfp
                                 JOIN configuracion_factura cf ON cfp.id_configuracion_factura = cf.id
                                 JOIN servicio_cliente sc ON cf.id_servicio = sc.id_servicio_cliente
                                 SET cfp.codigo_producto = '$codigo_servicio_nuevo',
                                     cfp.descripcion = '$nombre_nuevo'
                                 WHERE sc.empresa = '[usr_empresa]'
                                   AND sc.sucursal = '[usr_sucursal]'
                                   AND sc.codigo_producto = '$codigo_servicio_anterior'";
        sc_exec_sql($update_productos_sql);

        // D) Ejecutar actualización en la tabla: servicio_cliente
        $update_servicio_sql = "UPDATE servicio_cliente
                                SET codigo_producto = '$codigo_servicio_nuevo'
                                WHERE empresa = '[usr_empresa]'
                                  AND sucursal = '[usr_sucursal]'
                                  AND codigo_producto = '$codigo_servicio_anterior'";
        sc_exec_sql($update_servicio_sql);

        // E) Confirmar transacciones
        sc_commit_trans();		
		
		// =====================================================================
        // NUEVO: REGISTRO EXACTO EN TABLA DE LOGS sc_log_modulos
        // =====================================================================
        $total_fac  = max(0, count($ids_factura_a_modificar) - 1); 
        $total_cli  = max(0, count($ids_cliente_a_modificar) - 1);
        
      //  $log_user   = isset([usr_login]) ? [usr_login] : 'Scriptcase';		
		$log_user   = 'Emp: '. [usr_empresa] .' - Suc: ' .[usr_sucursal].' - Usu: ' .[usr_login];
				
        $log_app    = 'homologacion_codigos_servicios_blank'; // Nombre de tu aplicación Blank actual
        $log_ip     = $_SERVER['REMOTE_ADDR'];
        $log_action = 'Migracion';
        
        $log_desc   = "Migración exitosa. Código Anterior: '$codigo_servicio_anterior' -> Código Nuevo: '$codigo_servicio_nuevo' ($nombre_nuevo). ";
        $log_desc  .= "Afectados: Facturas ($total_fac), Clientes ($total_cli).";
        $log_desc   = addslashes($log_desc);

        $sql_insert_log = "INSERT INTO sc_log_modulos (inserted_date, username, application, creator, ip_user, action, description) 
                           VALUES (NOW(), '$log_user', '$log_app', 'Scriptcase', '$log_ip', '$log_action', '$log_desc')";
        
        sc_exec_sql($sql_insert_log);
        // =====================================================================
		
        
        // F) Consultar registros modificados filtrando ESTRICTAMENTE por los IDs guardados
        $detalles_factura = array();
        $str_ids_fac = implode(",", $ids_factura_a_modificar);
        
        /* ORGINAL
		$sql_res_fac = "SELECT cfp.id_configuracion_factura, cfp.codigo_producto, cfp.descripcion 
                        FROM configuracion_factura_productos cfp
                        WHERE cfp.id_configuracion_factura IN ($str_ids_fac)
                          AND cfp.codigo_producto = '$codigo_servicio_nuevo'";
		*/
		
		$sql_res_fac = "SELECT  cd.cod_cliente, cd.nombre_cliente, cfp.descripcion
			 FROM configuracion_factura_productos cfp 
			 INNER JOIN configuracion_factura cf 
			 ON cf.id = cfp.id_configuracion_factura
			 INNER JOIN clientes_datos cd 
			 ON cd.id_cliente = cf.id_cliente
                        WHERE cfp.id_configuracion_factura IN ($str_ids_fac)
                          AND cfp.codigo_producto = '$codigo_servicio_nuevo'";
		
        sc_lookup(ds_res_fac, $sql_res_fac);
        
        if (!empty({ds_res_fac}) && isset({ds_res_fac[0][0]}) && $str_ids_fac !== "-1") {
            foreach ({ds_res_fac} as $rfac) {
                $detalles_factura[] = array('id' => $rfac[0], 'codigo' => $rfac[1], 'descripcion' => $rfac[2]);
            }
        }

        $detalles_cliente = array();
        $str_ids_cli = implode(",", $ids_cliente_a_modificar);
        
        $sql_res_cli = "SELECT id_servicio_cliente, codigo_producto 
                        FROM servicio_cliente 
                        WHERE id_servicio_cliente IN ($str_ids_cli)
                          AND codigo_producto = '$codigo_servicio_nuevo'";
        sc_lookup(ds_res_cli, $sql_res_cli);

        if (!empty({ds_res_cli}) && isset({ds_res_cli[0][0]}) && $str_ids_cli !== "-1") {
            foreach ({ds_res_cli} as $rcli) {
                $detalles_cliente[] = array('id' => $rcli[0], 'codigo' => $rcli[1]);
            }
        }

        echo json_encode(array(
            'status' => 'MIGRACION_OK',
            'log_factura' => $detalles_factura,
            'log_cliente' => $detalles_cliente
        ));
        exit;
    }
}

// --- 3. MENSAJES INICIALES DE TABLAS VACÍAS ---
$log_factura_html = "<tr><td colspan='3' style='text-align:center; color:#94a3b8;'>Presione 'Migrar Servicio' para ver los cambios</td></tr>";
$log_cliente_html = "<tr><td colspan='2' style='text-align:center; color:#94a3b8;'>Presione 'Migrar Servicio' para ver los cambios</td></tr>";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex; flex-direction: column; align-items: center;
            min-height: 100vh; margin: 0; background: #eceff1; padding: 20px; box-sizing: border-box;
        }
        .main-wrapper { display: flex; width: 100%; max-width: 1200px; gap: 25px; margin-top: 10px; }
        .left-column { flex: 1; max-width: 450px; }
        .right-column { flex: 2; display: flex; flex-direction: column; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .card-title { font-size: 16px; font-weight: bold; color: #1e293b; margin: 0 0 15px 0; border-bottom: 2px solid #cbd5e1; padding-bottom: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; color: #37474f; margin-bottom: 6px; font-size: 13px; }
        input { width: 100%; padding: 10px; border: 1px solid #cfd8dc; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        .success-msg { color: #1b5e20; background: #c8e6c9; padding: 12px; border-radius: 4px; margin-bottom: 15px; display: none; text-align: center; font-weight: bold; width: 100%; max-width: 1200px; box-sizing: border-box;}
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single { height: 38px !important; padding: 5px; border: 1px solid #cfd8dc !important; border-radius: 4px !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 26px !important; color: #37474f; }
        .btn-migrate { background-color: #0088cc; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; width: 100%; transition: background 0.2s; }
        .btn-migrate:hover { background-color: #006699; }
        .btn-migrate:disabled { background-color: #b0bec5 !important; color: #000000 !important; cursor: not-allowed; opacity: 0.7; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; text-align: left; }
        th { background-color: #37474f; color: white; padding: 8px 10px; font-weight: 600; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }
		
		/* Contenedor para alinear los botones uno al lado del otro */
        .buttons-container { display: flex; gap: 12px; width: 100%; }
        
        /* Estilos del Botón Salir basados en image_945eec.png */
        .btn-exit { 
            background-color: #ff5274; color: white; padding: 12px; border: none; 
            border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; 
            width: 40%; transition: background 0.2s; display: flex; 
            align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 2px 5px rgba(255, 82, 116, 0.2);
        }
        .btn-exit:hover { background-color: #e03b5c; }
        
        /* Ajuste para que el botón de migrar ocupe el resto del espacio */
        .btn-migrate { width: 60% !important; }
		
		/* Estilos de realce para los botones de las páginas inferiores */
        #paginacion_factura button, #paginacion_cliente button {
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        #paginacion_factura button:hover, #paginacion_cliente button:hover {
            background-color: #f8fafc !important;
        }
		
		
		
		
    </style>
</head>
<body>

<div id="msg" class="success-msg"><i class="fas fa-check-circle"></i> Migración completada con éxito</div>

<div class="main-wrapper">
    <div class="left-column">
        <div class="card">     
            <div class="card-title"><i class="fas fa-exchange-alt"></i> Migración de Servicios</div>
            
            <div class="form-group">
                <label>Servicio Anterior:</label>
                <select id="codigo_servicio_anterior">
                    <option value=""></option>
                    <?php echo $productos_options; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Descripción Servicio Anterior:</label>
                <input type="text" id="nombre_producto" readonly style="background: #eceff1; border-color: #b0bec5;">
            </div>        
        
            <div class="form-group">
                <label>Servicio Nuevo:</label>
                <select id="codigo_servicio_nuevo">
                    <option value=""></option>
                    <?php echo $productos_options; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Descripción Servicio Nuevo:</label>
                <input type="text" id="nombre_servicio_nuevo" readonly style="background: #eceff1; border-color: #b0bec5;">
            </div>
           			
			<!-- Contenedor con el botón Salir (Izquierda) y Migrar Servicio (Derecha) -->
            <div class="buttons-container">
                <button type="button" class="btn-exit" onclick="window.close();">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </button>
                
                <button type="button" id="boton_migrar" class="btn-migrate" onclick="migrarServicio()" disabled>
                    <i class="fas fa-sync-alt"></i> Migrar Servicio
                </button>
            </div>		
			
        </div>
    </div>

	<div class="right-column">
        <div class="card table-container">
            <div class="card-title"><i class="fas fa-file-invoice-dollar"></i> Últimos Cambios: Listado de Clientes - Servicios - Facturacion - Productos </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Codigo Cliente</th>
                        <th style="width: 25%;">Nombre del Cliente</th>
                        <th style="width: 60%;">Descripción del Nuevo Servicio</th>
                    </tr>
                </thead>
                <tbody id="tbody_factura">
                    <?php echo $log_factura_html; ?>
                </tbody>
            </table>
            <!-- Paginador Factura -->
            <div id="paginacion_factura" style="margin-top: 10px; display: flex; gap: 5px; justify-content: flex-end;"></div>
        </div>

        <div class="card table-container">
            <div class="card-title"><i class="fas fa-users"></i> Últimos Cambios: Listado de Clientes - Servicios - Configuracion Red</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">ID Servicio Cliente</th>
                        <th style="width: 70%;">Código Producto Asignado</th>
                    </tr>
                </thead>
                <tbody id="tbody_cliente">
                    <?php echo $log_cliente_html; ?>
                </tbody>
            </table>
            <!-- Paginador Cliente -->
            <div id="paginacion_cliente" style="margin-top: 10px; display: flex; gap: 5px; justify-content: flex-end;"></div>
        </div>
    </div> 
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>    
    $(document).ready(function() {  
        $('#codigo_servicio_anterior').select2({
            placeholder: "-- Buscar Servicio Anterior --",
            allowClear: true, width: '100%'
        });

        $('#codigo_servicio_nuevo').select2({
            placeholder: "-- Buscar Servicio Nuevo --",
            allowClear: true, width: '100%'
        });
        
        $(document).on('select2:open', () => {
            const searchField = document.querySelector('.select2-search__field');
            if (searchField) { searchField.focus(); }
        });

        setTimeout(function() { $('#codigo_servicio_anterior').select2('open'); }, 150);

        $('#codigo_servicio_anterior').on('change', function() {
            $('#nombre_producto').val($(this).find(':selected').data('nombre') || '');
            controlarEstadoBoton(); 
        }); 

        $('#codigo_servicio_nuevo').on('change', function() {
            $('#nombre_servicio_nuevo').val($(this).find(':selected').data('nombre') || '');
            controlarEstadoBoton();
        }); 

        $(document).on('keydown', '.select2-search__field', function(e) {
            if (e.which == 13) {
                const cod = $('#codigo_servicio_anterior').val();
                if (cod) { e.preventDefault(); ejecutarValidacion(); }
            }
        });
		
        crearPaginacionClasica('#tbody_factura', '#paginacion_factura', 10);
        crearPaginacionClasica('#tbody_cliente', '#paginacion_cliente', 10);
		
    });
    
    function ejecutarValidacion() {
        const cod = $('#codigo_servicio_anterior').val();
        if(!cod) return;

        $.ajax({
            url: window.location.href, type: 'POST',
            data: { accion: 'validar', codigo: cod },
            success: function(respuesta) {
                if (respuesta.trim() !== "OK") {
                    alert("Aviso: El código seleccionado no cumple los requisitos o está inactivo.");
                }
            }
        });
    }
    
function migrarServicio() {
        const codAnterior = $('#codigo_servicio_anterior').val();
        const codNuevo = $('#codigo_servicio_nuevo').val();

        if (!codAnterior || !codNuevo) {
            alert("Por favor seleccione ambos servicios para proceder.");
            return;
        }

        if (confirm("¿Está seguro de que desea proceder con la migración del servicio?")) {
            $.ajax({
                url: window.location.href, type: 'POST',
                data: { accion: 'migrar', codigo_anterior: codAnterior, codigo_nuevo: codNuevo },
                success: function(respuesta) {
                    try {
                        const datos = JSON.parse(respuesta);

                        if (datos.status === "MIGRACION_OK") {
                            $('#tbody_factura').empty();
                            $('#tbody_cliente').empty();

                            if (datos.log_factura.length > 0) {
                                datos.log_factura.forEach(fila => {
                                    $('#tbody_factura').append(`<tr><td>${fila.id}</td><td>${fila.codigo}</td><td>${fila.descripcion}</td></tr>`);
                                });
                            } else {
                                $('#tbody_factura').append("<tr><td colspan='3' style='text-align:center; color:#ef4444; font-weight:bold;'>0 registros actualizados en esta tabla</td></tr>");
                            }

                            if (datos.log_cliente.length > 0) {
                                datos.log_cliente.forEach(fila => {
                                    $('#tbody_cliente').append(`<tr><td>${fila.id}</td><td>${fila.codigo}</td></tr>`);
                                });
                            } else {
                                $('#tbody_cliente').append("<tr><td colspan='2' style='text-align:center; color:#ef4444; font-weight:bold;'>0 registros actualizados en esta tabla</td></tr>");
                            }

                            // === LLAMADO A LA PAGINACIÓN (10 registros por página) ===
                            crearPaginacionClasica('#tbody_factura', '#paginacion_factura', 10);
                            crearPaginacionClasica('#tbody_cliente', '#paginacion_cliente', 10);
                            // ========================================================

                            $('#codigo_servicio_anterior').val('').trigger('change');
                            $('#nombre_producto').val('');
                            $('#codigo_servicio_nuevo').val('').trigger('change'); 
                            $('#nombre_servicio_nuevo').val('');
                            
                            controlarEstadoBoton();
                            $('#msg').fadeIn().delay(1500).fadeOut();
                            setTimeout(function() { $('#codigo_servicio_anterior').select2('open'); }, 200);
                        } else {
                            alert("Error devuelto por el servidor: " + respuesta);
                        }
                    } catch (e) {
                        alert("Error inesperado en el formato de respuesta del servidor: " + respuesta);
                    }
                },
                error: function() { alert("Error crítico: No se pudo comunicar con el proceso PHP."); }
            });
        }
    }
        
    function controlarEstadoBoton() {
        const activo = $('#codigo_servicio_anterior').val() && $('#codigo_servicio_nuevo').val();
        $('#boton_migrar').prop('disabled', !activo);
    }   
	

function crearPaginacionClasica(tbodySelector, paginadorSelector, filasPorPagina) {
    const $tbody = $(tbodySelector);
    const $paginador = $(paginadorSelector);
    const $filas = $tbody.find('tr');
    const totalFilas = $filas.length;
    
    $paginador.empty();
    
    // Si la tabla no tiene suficientes registros válidos o está vacía, los muestra todos y sale
    if (totalFilas <= filasPorPagina || $tbody.find('td').length <= 1) {
        $filas.show();
        return; 
    }

    const totalPaginas = Math.ceil(totalFilas / filasPorPagina);
    let paginaActual = 1;

    function mostrarPagina(pagina) {
        paginaActual = pagina;
        $filas.hide();
        
        const inicio = (pagina - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;
        $filas.slice(inicio, fin).show();

        // Controlar el estado de habilitación/deshabilitación de los botones de control
        $paginador.find('.btn-prev').prop('disabled', paginaActual === 1);
        $paginador.find('.btn-next').prop('disabled', paginaActual === totalPaginas);

        // Actualizar el indicador de página de texto central de forma profesional
        $paginador.find('.page-indicator').text(`Pág. ${paginaActual} de ${totalPaginas}`);
    }

    // Estilos base compartidos para los botones de control de navegación
    const estilosBoton = {
        'padding': '5px 12px', 'border': '1px solid #cbd5e1', 'border-radius': '4px',
        'cursor': 'pointer', 'font-size': '12px', 'font-weight': 'bold', 'transition': 'all 0.2s',
        'background-color': '#fff', 'color': '#0088cc'
    };

    // 1. Botón Anterior
    const $btnPrev = $('<button type="button" class="btn-prev"><i class="fas fa-chevron-left"></i> Anterior</button>')
        .css(estilosBoton)
        .on('click', function() { if (paginaActual > 1) { mostrarPagina(paginaActual - 1); } });

    // 2. Indicador de texto intermedio
    const $indicador = $('<span class="page-indicator"></span>').css({
        'font-size': '12px', 'font-weight': '600', 'color': '#475569', 'align-self': 'center', 'margin': '0 8px'
    });

    // 3. Botón Siguiente
    const $btnNext = $('<button type="button" class="btn-next">Siguiente <i class="fas fa-chevron-right"></i></button>')
        .css(estilosBoton)
        .on('click', function() { if (paginaActual < totalPaginas) { mostrarPagina(paginaActual + 1); } });

    // Armar el paginador dinámico en el DOM
    $paginador.append($btnPrev).append($indicador).append($btnNext);

    // Renderizar la página inicial
    mostrarPagina(1);
}
</script>
</body>
</html>
<?php
