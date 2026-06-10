// Inicializamos variables de control para la interfaz
$mensaje_registro = "";
$turno_abierto    = false;
$id_turno_actual  = null;
$login_usuario    = [usr_login];

// Variables de contexto (Filtros automáticos)
$empresa  = isset([usr_empresa]) ? [usr_empresa] : 'Tecnoven Matriz'; 
$sucursal = isset([usr_sucursal]) ? [usr_sucursal] : 'Maracaibo'; 

// ==========================================
// 1. PROCESAR ACCIONES (POST)
// ==========================================

// ACCIÓN A: Abrir Turno
if (isset($_POST['btn_abrir_turno'])) {
    $estado_abierto = 'Abierto';
    $fecha_act      = date("Y-m-d H:i:s");
    
    // Captura de IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_estacion = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_estacion = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_estacion = $_SERVER['REMOTE_ADDR'];
    }

    $sql_insert = "INSERT INTO seguridad_users_turnos 
                   (login, estado, fecha_apertura, usuario, empresa, sucursal, ip_estacion) 
                   VALUES 
                   (
                    " . sc_sql_injection($login_usuario) . ", 
                    " . sc_sql_injection($estado_abierto) . ", 
                    '" . $fecha_act . "', 
                    (SELECT name FROM seguridad_users WHERE login = " . sc_sql_injection($login_usuario) . "), 
                    " . sc_sql_injection($empresa) . ", 
                    " . sc_sql_injection($sucursal) . ", 
                    " . sc_sql_injection($ip_estacion) . "
                   )";

    sc_exec_sql($sql_insert);
    $mensaje_registro = "<div class='alert success'>¡Turno abierto exitosamente!</div>";
}

// ACCIÓN B: Cerrar Turno
if (isset($_POST['btn_cerrar_turno'])) {
    $id_turno_update = $_POST['id_turno'];
    $estado_cerrado  = 'Cerrado';
    $fecha_cierre    = date("Y-m-d H:i:s");
    
    $sql_update = "UPDATE seguridad_users_turnos 
                   SET estado = " . sc_sql_injection($estado_cerrado) . ", 
                       fecha_cierre = '" . $fecha_cierre . "' 
                   WHERE id = " . sc_sql_injection($id_turno_update);
                   
    sc_exec_sql($sql_update);
    $mensaje_registro = "<div class='alert danger'>¡Turno cerrado exitosamente!</div>";
}

// ==========================================
// 2. VALIDAR ESTADO ACTUAL DEL TURNO (PANEL IZQUIERDO)
// ==========================================
$sql_check_turno = "SELECT id FROM seguridad_users_turnos 
                    WHERE login = '" . $login_usuario . "' 
                    AND estado = 'Abierto' 
                    AND (fecha_cierre IS NULL OR fecha_cierre = '') 
                    ORDER BY id DESC LIMIT 1";

sc_lookup(ds_turno, $sql_check_turno);

if (isset({ds_turno}[0][0])) {
    $turno_abierto   = true;
    $id_turno_actual = {ds_turno}[0][0];
}

// Consultar nombre del usuario para el saludo
$sql_user = "SELECT name FROM seguridad_users WHERE login = '" . $login_usuario . "'";
sc_lookup(ds_user, $sql_user);
$nombre_usuario = isset({ds_user}[0][0]) ? {ds_user}[0][0] : "Usuario no encontrado";


// ==========================================
// 3. LÓGICA DE PAGINACIÓN Y CONSULTA DE LA TABLA (PANEL DERECHO)
// ==========================================
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Query para contar el total de registros con los filtros aplicados
$sql_count = "SELECT COUNT(*) FROM seguridad_users_turnos 
              WHERE login = " . sc_sql_injection($login_usuario) . " 
              AND empresa = " . sc_sql_injection($empresa) . " 
              AND sucursal = " . sc_sql_injection($sucursal);

sc_lookup(ds_count, $sql_count);
$total_registros = isset({ds_count}[0][0]) ? {ds_count}[0][0] : 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Query para obtener los registros paginados
$sql_tabla = "SELECT id, estado, fecha_apertura, fecha_cierre, ip_estacion 
              WHERE login = " . sc_sql_injection($login_usuario) . " 
              AND empresa = " . sc_sql_injection($empresa) . " 
              AND sucursal = " . sc_sql_injection($sucursal) . " 
              ORDER BY id DESC 
              LIMIT $inicio, $registros_por_pagina";

// Usamos sc_select para recorrer múltiples filas cómodamente
sc_select(rs_tabla, $sql_tabla);

// ==========================================
// 4. RENDERIZAR LA INTERFAZ (HTML + CSS)
// ==========================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Turnos e Historial</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 30px;
        }
        /* Contenedor principal de dos columnas */
        .main-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            align-items: start;
        }
        /* Columna Izquierda: Panel */
        .card-panel {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 25px;
            text-align: center;
            border-top: 5px solid <?php echo $turno_abierto ? '#e74c3c' : '#27ae60'; ?>;
        }
        /* Columna Derecha: Tabla */
        .card-table {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 25px;
            border-top: 5px solid #34495e;
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 22px;
        }
        h3 {
            color: #34495e;
            margin-top: 0;
            font-size: 18px;
            border-bottom: 2px solid #f4f6f9;
            padding-bottom: 10px;
        }
        .username {
            color: #2980b9;
            font-weight: 600;
        }
        p.subtitle {
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .btn {
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 100%;
        }
        .btn-abrir { background-color: #27ae60; }
        .btn-abrir:hover { background-color: #219150; }
        .btn-cerrar { background-color: #e74c3c; }
        .btn-cerrar:hover { background-color: #c0392b; }
        
        /* Estilos de la Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #eef2f5;
        }
        th {
            background-color: #f8f9fa;
            color: #7f8c8d;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        tr:hover { background-color: #fdfefe; }
        
        /* Badges de estado en la tabla */
        .badge {
            padding: 3px 8px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 12px;
        }
        .badge-abierto { background-color: #d1f2e9; color: #117a65; }
        .badge-cerrado { background-color: #fadbd8; color: #78281f; }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 50px;
            margin-bottom: 15px;
        }
        .status-abierto { background-color: #e8f8f5; color: #117a65; }
        .status-cerrado { background-color: #f5f5f5; color: #7f8c8d; }

        /* Paginación */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
        }
        .pagination a {
            color: #34495e;
            padding: 6px 12px;
            text-decoration: none;
            border: 1px solid #dcdde1;
            border-radius: 4px;
            font-size: 13px;
        }
        .pagination a.active {
            background-color: #34495e;
            color: white;
            border-color: #34495e;
        }
        .pagination a:hover:not(.active) {
            background-color: #eef2f5;
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-context {
            font-size: 11px;
            color: #95a5a6;
            margin-top: 15px;
            text-align: left;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="main-container">
    
    <div class="card-panel">
        <?php echo $mensaje_registro; ?>

        <?php if ($turno_abierto): ?>
            <span class="status-badge status-abierto">● TURNO ACTIVO</span>
        <?php else: ?>
            <span class="status-badge status-cerrado">○ SIN TURNO ACTIVO</span>
        <?php endif; ?>

        <h2>Bienvenido,</h2>
        <h2 class="username"><?php echo $nombre_usuario; ?></h2>
        
        <form method="POST" action="">
            <?php if ($turno_abierto): ?>
                <p class="subtitle">Tienes un turno activo. No olvides cerrarlo al finalizar tus actividades de hoy.</p>
                <input type="hidden" name="id_turno" value="<?php echo $id_turno_actual; ?>">
                <button type="submit" name="btn_cerrar_turno" class="btn btn-cerrar">
                    🛑 Cerrar Turno
                </button>
            <?php else: ?>
                <p class="subtitle">Presiona el siguiente botón para registrar la apertura de tu jornada laboral.</p>
                <button type="submit" name="btn_abrir_turno" class="btn btn-abrir">
                    🚀 Abrir Turno
                </button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card-table">
        <h3>Historial de Turnos</h3>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estado</th>
                    <th>Apertura</th>
                    <th>Cierre</th>
                    <th>IP Estación</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($rs_tabla !== false && !$rs_tabla->EOF) {
                    while (!$rs_tabla->EOF) {
                        $id_t   = $rs_tabla->fields[0];
                        $est_t  = $rs_tabla->fields[1];
                        $ap_t   = $rs_tabla->fields[2];
                        $cie_t  = $rs_tabla->fields[3] ? $rs_tabla->fields[3] : '---';
                        $ip_t   = $rs_tabla->fields[4];
                        
                        $badge_class = ($est_t == 'Abierto') ? 'badge-abierto' : 'badge-cerrado';
                        
                        echo "<tr>";
                        echo "<td><strong>#$id_t</strong></td>";
                        echo "<td><span class='badge $badge_class'>$est_t</span></td>";
                        echo "<td>$ap_t</td>";
                        echo "<td>$cie_t</td>";
                        echo "<td>$ip_t</td>";
                        echo "</tr>";
                        
                        $rs_tabla->MoveNext();
                    }
                    $rs_tabla->Close();
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; color:#95a5a6; padding: 20px;'>No se encontraron registros de turnos para este contexto.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php if ($total_paginas > 1): ?>
            <div class="pagination">
                <?php if ($pagina_actual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_actual - 1; ?>">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?pagina=<?php echo $i; ?>" class="<?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
<?php