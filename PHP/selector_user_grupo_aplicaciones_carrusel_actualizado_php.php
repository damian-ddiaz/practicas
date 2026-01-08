<?php
// =========================================================================
// CONEXIÓN A LA BASE DE DATOS
// =========================================================================
$host = '172.16.7.50';
$db   = 'webservices';
$user = 'scryptcase';
$pass = 'Mt*1329*--1';

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// =========================================================================
// CAPTURA DE PARÁMETROS (Equivalente a [par_login])
// =========================================================================
// Asumimos que el login viene por GET o POST, si no, vacío.
/*
$user_login = isset($_REQUEST['par_login']) ? $_REQUEST['par_login'] : '';
$login_esc  = $conn->real_escape_string($user_login);
*/
// Forzamos el valor a 'ddiaz' directamente
$user_login = 'ddiaz'; 
$login_esc  = $conn->real_escape_string($user_login);

// =========================================================================
// GUARDAR PERMISOS (LÓGICA POST)
// =========================================================================
if (isset($_POST['user_to_save']) && $_POST['user_to_save'] != '') {

    $login_to_save = trim($_POST['user_to_save']);
    $login_esc     = $conn->real_escape_string($login_to_save);

    $selected_groups = isset($_POST['groups']) ? $_POST['groups'] : [];
    $selected_groups = array_unique(array_map('intval', $selected_groups));

    // Eliminar permisos previos
    $conn->query("DELETE FROM seguridad_users_groups WHERE login = '$login_esc'");

    if (!empty($selected_groups)) {
        foreach ($selected_groups as $group_id) {
            $conn->query("INSERT IGNORE INTO seguridad_users_groups (login, group_id) VALUES ('$login_esc', $group_id)");
        }

        // =========================================================================
        // ASIGNANDO MÓDULOS AUTOMÁTICAMENTE (Lógica de Portales)
        // =========================================================================
        $res_maestros = $conn->query("SELECT id_modulo_maestro, descripcion FROM seguridad_grupos_maestros");
        
        if ($res_maestros && $res_maestros->num_rows > 0) {
            // Mapeo de IDs a nombres de columna en la tabla seguridad_users
            $column_map = [
                1 => 'url_portal_administrativo',
                2 => 'url_portal_intranet',
                3 => 'url_portal_contabilidad',
                4 => 'url_portal_rrhh',
                5 => 'url_portal_aliado',
                6 => 'url_portal_atencion_comercial',
                7 => 'url_portal_vendedor',
                8 => 'url_portal_soporte',
                9 => 'url_portal_helpdesk',
                10 => 'url_portal_dispositos',
                11 => 'url_portal_mantenimiento',
                13 => 'url_portal_herramientas',
                14 => 'url_portal_gestionredes',
                15 => 'url_portal_inventario',
                16 => 'url_portal_finanzas',
                17 => 'url_portal_proyectos',
                18 => 'url_portal_reportes_matriz',
                19 => 'url_portal_puntodeventa',
                20 => 'url_portal_reportes',
                22 => 'url_portal_configuracion',
                23 => 'url_portal_configuracion' // Según tu código original, el 23 también apunta a configuración
            ];

            while ($row_m = $res_maestros->fetch_row()) {
                $id_modulo_maestro = $row_m[0];

                // Verificar si el usuario tiene algún grupo de este módulo asignado
                $check_sql = "SELECT sug.login 
                              FROM seguridad_users_groups sug 
                              LEFT JOIN seguridad_groups sg ON sg.group_id = sug.group_id 
                              WHERE sug.login = '$login_esc' AND sg.modulo = $id_modulo_maestro LIMIT 1";
                
                $res_check = $conn->query($check_sql);
                $estado = ($res_check && $res_check->num_rows > 0) ? 'SI' : 'NO';

                if (isset($column_map[$id_modulo_maestro])) {
                    $col_name = $column_map[$id_modulo_maestro];
                    $conn->query("UPDATE seguridad_users SET $col_name = '$estado' WHERE login = '$login_esc'");
                }
            }
        }
    }
    echo "<script>alert('Permisos guardados correctamente para el usuario: $login_to_save');</script>";
}

// =========================================================================
// PERMISOS ACTUALES
// =========================================================================
$assigned_groups = [];
$res_assigned = $conn->query("SELECT group_id FROM seguridad_users_groups WHERE login = '$login_esc'");
if ($res_assigned) {
    while ($row = $res_assigned->fetch_row()) {
        $assigned_groups[] = $row[0];
    }
}

// =========================================================================
// MÓDULOS PARA EL CARRUSEL
// =========================================================================
$modules = [];
$res_mod = $conn->query("SELECT id_modulo_maestro, descripcion, icono FROM seguridad_grupos_maestros WHERE visible_usuario = 'SI' ORDER BY descripcion");
if ($res_mod) {
    while ($row = $res_mod->fetch_assoc()) {
        $modules[] = [
            'id'    => $row['id_modulo_maestro'],
            'name'  => $row['descripcion'],
            'icono' => $row['icono']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Permisos</title>
    <style>
        body{font-family:Arial;background:#f0f2f5; padding: 20px;}
        .container{max-width:1200px;margin:auto}
        .carousel-wrapper{position:relative;margin:15px 0}
        .modules-grid{display:flex;overflow-x:auto;gap:15px;padding:0 50px 15px;white-space:nowrap; scroll-behavior: smooth;}
        .modules-grid::-webkit-scrollbar{display:none}
        .permission-filter{width:100%;padding:6px 8px;margin-bottom:10px;border-radius:6px;border:1px solid #ccc;font-size:12px;}
        .module-card{
            width:auto; min-width:135px; font-size: 12px; height:50px; background:#f0f2f5;
            cursor:pointer; box-shadow:5px 5px 15px rgba(0,0,0,.1); transition:.3s;
            display: flex; align-items: center; justify-content: center; text-align: center;
            padding: 15px 10px; border-radius: 10px; flex-direction: column;
        }
        .module-card:hover, .module-card.active{background:#1C48E8;color:#fff;transform:scale(1.05)}
        .nav-btn{position:absolute;top:10%;background:rgba(0,0,0,.4);color:#fff;border:none;padding:15px 10px;font-size:1.5em;cursor:pointer;border-radius:5px;z-index:10}
        .left-btn{left:0} .right-btn{right:0}
        .dual-permissions{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px}
        .permission-box{background:#fff;border-radius:10px;padding:15px;height:400px;overflow-y:auto;box-shadow:0 2px 10px rgba(0,0,0,.05);}
        .permission-box h4{text-align:center;color:#333;margin-top:0}
        .permission-item{background:#f9f9f9;padding:8px 12px;margin-bottom:6px;border-radius:6px;cursor:pointer;border:1px solid #eee;transition:.2s; font-size: 13px;}
        .permission-item:hover{background:#1C48E8;color:white}
        input[type=submit]{margin:30px auto; display:block; padding:12px 40px; background:#28a745; color:white; font-size:1.1em;border:none;border-radius:10px;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,.2)}
        input[type=submit]:hover{background:#218838}
    </style>
</head>
<body>

<div class="container">
    <?php if ($user_login != ''): ?>
        <form method="post">
            <input type="hidden" name="user_to_save" value="<?php echo htmlspecialchars($user_login); ?>">

            <div class="carousel-wrapper">
                <button type="button" class="nav-btn left-btn" onclick="scrollCarousel(-1)">&#60;</button>
                <div class="modules-grid" id="modulesGrid">
                    <?php foreach ($modules as $m): 
                        $clean_name = ucfirst(strtolower($m['name']));
                        $display_name = str_replace(' ', '<br>', $clean_name);
                    ?>
                        <div class="module-card" onclick="selectModule('mod_<?php echo $m['id']; ?>', this)">
                            <img src="<?php echo $m['icono']; ?>" width="30" onerror="this.style.display='none'"><br>
                            <?php echo $display_name; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="nav-btn right-btn" onclick="scrollCarousel(1)">&#62;</button>
            </div>

            <?php foreach ($modules as $m): ?>
                <div id="mod_<?php echo $m['id']; ?>" class="module-content" style="display:none">
                    <div class="dual-permissions">
                        <?php
                        $asig = [];
                        $disp = [];
                        $m_id = $m['id'];
                        $res_groups = $conn->query("SELECT group_id, description, descripcion_detallada FROM seguridad_groups WHERE modulo = '$m_id' ORDER BY description");
                        
                        if ($res_groups) {
                            while ($r = $res_groups->fetch_assoc()) {
                                if (in_array($r['group_id'], $assigned_groups)) {
                                    $asig[] = $r;
                                } else {
                                    $disp[] = $r;
                                }
                            }
                        }
                        ?>

                        <div class="permission-box">
                            <h4>Disponibles (Click para Asignar)</h4>
                            <input type="text" class="permission-filter" placeholder="Buscar..." onkeyup="filterPermissions(this)">
                            <div class="items-container">
                                <?php foreach ($disp as $p): ?>
                                    <div class="permission-item" onclick="addPermission(this, <?php echo $p['group_id']; ?>)" title="<?php echo htmlspecialchars($p['descripcion_detallada']); ?>">
                                        <?php echo $p['description']; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="permission-box">
                            <h4>Asignados (Click para Quitar)</h4>
                            <input type="text" class="permission-filter" placeholder="Buscar..." onkeyup="filterPermissions(this)">
                            <div class="items-container">
                                <?php foreach ($asig as $p): ?>
                                    <div class="permission-item" onclick="removePermission(this, <?php echo $p['group_id']; ?>)" title="<?php echo htmlspecialchars($p['descripcion_detallada']); ?>">
                                        <?php echo $p['description']; ?>
                                        <input type="hidden" name="groups[]" value="<?php echo $p['group_id']; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <input type="submit" value="Guardar Permisos">
        </form>
    <?php else: ?>
        <div style="text-align:center; margin-top:50px;">
            <h2>Por favor, proporcione un login de usuario (par_login)</h2>
        </div>
    <?php endif; ?>
</div>

<script>
function scrollCarousel(d){
    document.getElementById("modulesGrid").scrollLeft += d * 400;
}

function selectModule(id, el){
    document.querySelectorAll(".module-content").forEach(d => d.style.display="none");
    document.querySelectorAll(".module-card").forEach(c => c.classList.remove("active"));
    document.getElementById(id).style.display="block";
    el.classList.add("active");
}

function filterPermissions(input){
    const filter = input.value.toLowerCase();
    const box = input.closest('.permission-box');
    box.querySelectorAll('.permission-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(filter) ? '' : 'none';
    });
}

function addPermission(el, id){
    const targetBox = el.closest(".dual-permissions").querySelectorAll(".items-container")[1];
    el.onclick = function(){ removePermission(el, id); };
    el.innerHTML += `<input type="hidden" name="groups[]" value="${id}">`;
    targetBox.appendChild(el);
}

function removePermission(el, id){
    const targetBox = el.closest(".dual-permissions").querySelectorAll(".items-container")[0];
    el.onclick = function(){ addPermission(el, id); };
    const input = el.querySelector("input");
    if (input) input.remove();
    targetBox.appendChild(el);
}

document.addEventListener("DOMContentLoaded", function(){
    const first = document.querySelector(".module-card");
    if (first) first.click();
});
</script>

</body>
</html>