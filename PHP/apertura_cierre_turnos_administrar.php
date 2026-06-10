<?php
// Inicializamos variables de control para la interfaz
$mensaje_registro = "";
$turno_abierto    = false;
$id_turno_actual  = null;
$login_usuario    = [usr_login];

// Variables de contexto (Filtros automáticos)
$empresa  = isset([usr_empresa]) ? [usr_empresa] : ''; 
$sucursal = isset([usr_sucursal]) ? [usr_sucursal] : ''; 

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
                   WHERE id = " . sc_sql_injection($id_turno_update) . " 
                   AND empresa = " . sc_sql_injection($empresa) . " 
                   AND sucursal = " . sc_sql_injection($sucursal);
                   
    sc_exec_sql($sql_update);
	
	// Liberando los Clientes Potenciales del Usuario
	$sql_update_cp = "UPDATE 
		cliente_potencial
		SET 
			usuario_vendedor = ' '
		WHERE 
			empresa = '[usr_empresa]' 
			AND sucursal = '[usr_sucursal]'
			AND usuario_vendedor = '[usr_login]'
			AND status = 'PEND'";               
    sc_exec_sql($sql_update_cp);
	
    $mensaje_registro = "<div class='alert danger'>¡Turno cerrado exitosamente!</div>";
}

// ==========================================
// 2. VALIDAR ESTADO ACTUAL DEL TURNO (PANEL IZQUIERDO)
// ==========================================
$sql_check_turno = "SELECT id FROM seguridad_users_turnos sut
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

// Link base seguro para la paginación dentro de Scriptcase
$script_actual = $_SERVER['PHP_SELF'];

// Query para contar el total de registros con los filtros aplicados
$sql_count = "SELECT COUNT(*) FROM seguridad_users_turnos 
              WHERE login = " . sc_sql_injection($login_usuario) . " 
              AND empresa = " . sc_sql_injection($empresa) . " 
              AND sucursal = " . sc_sql_injection($sucursal);

sc_lookup(ds_count, $sql_count);
$total_registros = isset({ds_count}[0][0]) ? {ds_count}[0][0] : 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// CORRECCIÓN: Agregado el 'FROM seguridad_users_turnos' que faltaba
$sql_tabla = "SELECT su.name, sut.estado, sut.fecha_apertura, sut.fecha_cierre 
              FROM seguridad_users_turnos sut
			  left join seguridad_users su 
			  ON su.login = sut.login 
              WHERE sut.empresa = " . sc_sql_injection($empresa) . " 
              AND sut.sucursal = " . sc_sql_injection($sucursal) . " 
              AND sut.estado = 'Abierto' 
              ORDER BY sut.id DESC 
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
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            align-items: start;
        }

        /* Columna Derecha: Tabla */
/* Busca esta sección en tu <style> y asegúrate de que quede así: */
		table {
			width: 100%;
			table-layout: fixed; /* Fuerza a las columnas a respetar el porcentaje asignado */
			border-collapse: collapse;
			margin-top: 15px;
			font-size: 14px;
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
            padding: 12px 15px;
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
		
        .badge-abierto { 
			color: #117a65; 
		}
		
        .badge-cerrado { 
			color: #78281f; 
		}

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 50px;
            margin-bottom: 15px;
        }
        .status-abierto {
        	background-color: #e8f8f5; 
			color: #117a65; 
		}
        .status-cerrado { 
			background-color: #f5f5f5; 
			color: #7f8c8d; 
		}

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
		/* Estilos adicionales para la sub-tabla de clientes */
        .card-table table table th {
            background-color: #eef2f5;
            color: #2c3e50;
            text-transform: none;
            font-size: 11px;
        }		
    </style>
	
	<script>
        function toggleClientes(id) {
            var fila = document.getElementById(id);
            if (fila) {
                if (fila.style.display === "none") {
                    fila.style.display = "table-row";
                } else {
                    fila.style.display = "none";
                }
            }
        }
    </script>	
</head>
<body>

<div class="main-container">
    <div class="card-table">
        <h3>Administrar Turnos</h3>
        
        <table>
			<thead>
				<tr>
					<th style="width: 10%; text-align: center;">Turno</th>
					<th style="width: 10%; text-align: center;">Clientes</th>
					<th style="width: 40%;">Usuario</th>
					<th style="width: 20%;">Estado</th>
					<th style="width: 40%;">Fecha Apertura</th>
					<th style="width: 40%;">Fecha Cierre</th>
				</tr>
			</thead>
			<tbody>
                <?php 
                if ($rs_tabla !== false && !$rs_tabla->EOF) {
                    while (!$rs_tabla->EOF) {
                        $usr_name = $rs_tabla->fields[0];
                        $est_t    = $rs_tabla->fields[1];
                        $ap_t     = $rs_tabla->fields[2];
                        $cie_t    = $rs_tabla->fields[3] ? $rs_tabla->fields[3] : '---';

                        $badge_class = ($est_t == 'Abierto') ? 'badge-abierto' : 'badge-cerrado';

// 1. Botón Acción A: Cerrar Turno
                        $action_bar = '---';
                        if ($est_t == 'Abierto') {
                            $action_bar = "<form method='POST' action='' onsubmit=\"return confirm('¿Está seguro de que desea cerrar este turno? Se liberarán todos sus clientes potenciales.');\" style='margin:0; display:inline;'>
                                            <input type='hidden' name='id_turno' value='" . (isset($id_turno_actual) ? $id_turno_actual : '') . "'>
                                            <button type='submit' name='btn_cerrar_turno' style='background:#e74c3c; color:white; border:none; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:3px; cursor:pointer;'>
                                                Cerrar
                                            </button>
                                           </form>";
                        }

                        // 2. Botón Acción B: Desplegar Clientes Potenciales
                        $vendedor_actual = $login_usuario; 
                        $btn_clientes = "<button type='button' onclick=\"toggleClientes('clientes_" . preg_replace('/[^A-Za-z0-9\-]/', '', $usr_name) . "_" . $rs_tabla->CurrentRow() . "')\" style='background:#2980b9; color:white; border:none; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:3px; cursor:pointer;'>
                                            Ver (v)
                                         </button>";

                        // Identificador único para la fila desplegable
                        $id_desplegable = "clientes_" . preg_replace('/[^A-Za-z0-9\-]/', '', $usr_name) . "_" . $rs_tabla->CurrentRow();

                        // 2. Botón Acción B: Desplegar Clientes Potenciales (Usando variables de contexto del bucle si aplica)
                        // Para este ejemplo usamos las variables del contexto actual de la fila si estuvieran disponibles, o los globales del script.
                        $vendedor_actual = $login_usuario; // O sube el login desde la consulta principal si sut.login está disponible
                        $btn_clientes = "<button type='button' onclick=\"toggleClientes('clientes_" . $rs_tabla->fields[0] . "_" . $rs_tabla->AbsolutePage() . "_" . $rs_tabla->fields[2] . "')\" style='background:#2980b9; color:white; border:none; padding:4px 8px; font-size:11px; font-weight:bold; border-radius:3px; cursor:pointer;'>
                                            Ver (v)
                                         </button>";

                        // Identificador único para la fila desplegable
                        $id_desplegable = "clientes_" . preg_replace('/[^A-Za-z0-9\-]/', '', $usr_name) . "_" . $rs_tabla->CurrentRow();

                        // Fila Principal del Turno
                        echo "<tr>";
                        echo "<td style='text-align: center;'>$action_bar</td>"; 
                        echo "<td style='text-align: center;'>$btn_clientes</td>"; 
                        echo "<td><strong>$usr_name</strong></td>";
                        echo "<td><span class='badge $badge_class'>$est_t</span></td>";
                        echo "<td>$ap_t</td>";
                        echo "<td>$cie_t</td>";
                        echo "</tr>";

                        // 3. Consulta y Generación de la Fila Desplegable Oculta
                        $sql_cp = "SELECT nombre_apellido, telefono, observacion, comentario_vendedores, correo
                                   FROM cliente_potencial 
                                   WHERE empresa = " . sc_sql_injection($empresa) . " 
                                   AND sucursal = " . sc_sql_injection($sucursal) . "
                                   AND usuario_vendedor = " . sc_sql_injection($vendedor_actual) . "
                                   AND status = 'PEND'";
                        
                        sc_select(rs_cp, $sql_cp);

                        echo "<tr id='$id_desplegable' style='display: none; background-color: #fcfcfc;'>";
                        echo "<td colspan='6' style='padding: 15px 30px; border-left: 4px solid #2980b9;'>";
                        echo "<h4 style='margin: 0 0 10px 0; color: #2980b9;'>Clientes Potenciales Pendientes</h4>";
                        
                        if ($rs_cp !== false && !$rs_cp->EOF) {
                            echo "<table style='width:100%; border: 1px solid #dcdde1; background:#fff; font-size:12px;'>";
                            echo "<thead style='background:#f1f2f6;'><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th></tr></thead>";
                            echo "<tbody>";
                            while (!$rs_cp->EOF) {
                                echo "<tr>";
                                echo "<td>" . $rs_cp->fields[0] . "</td>";
                                echo "<td>" . $rs_cp->fields[1] . "</td>";
                                echo "<td>" . $rs_cp->fields[2] . "</td>";
                                echo "<td>" . $rs_cp->fields[3] . "</td>";
                                echo "</tr>";
                                $rs_cp->MoveNext();
                            }
                            echo "</tbody></table>";
                            $rs_cp->Close();
                        } else {
                            echo "<p style='margin:0; color:#7f8c8d; font-style:italic;'>No hay clientes potenciales pendientes asignados a este usuario.</p>";
                        }
                        echo "</td>";
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
                    <a href="<?php echo $script_actual; ?>?pagina=<?php echo $pagina_actual - 1; ?>">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="<?php echo $script_actual; ?>?pagina=<?php echo $i; ?>" class="<?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?php echo $script_actual; ?>?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
<?php
?>