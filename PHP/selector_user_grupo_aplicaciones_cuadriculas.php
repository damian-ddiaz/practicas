// =========================================================================
// Usuario
// =========================================================================
$user_login = isset([par_login]) ? [par_login] : '';
$login_esc  = addslashes($user_login);

// =========================================================================
// Guardar permisos
// =========================================================================
if (isset($_POST['user_to_save']) && $_POST['user_to_save'] != '') {

    $login_to_save = trim($_POST['user_to_save']);
    $login_esc     = addslashes($login_to_save);

    $selected_groups = isset($_POST['groups']) ? $_POST['groups'] : [];
    $selected_groups = array_unique(array_map('intval', $selected_groups));

    if (empty($selected_groups)) {

        sc_exec_sql("DELETE FROM seguridad_users_groups WHERE login = '$login_esc'");

    } else {

        $ids_list = implode(',', $selected_groups);

        sc_exec_sql("DELETE FROM seguridad_users_groups 
                     WHERE login = '$login_esc' 
                     AND group_id NOT IN ($ids_list)");

        foreach ($selected_groups as $group_id) {
            sc_exec_sql("INSERT IGNORE INTO seguridad_users_groups (login, group_id) 
                         VALUES ('$login_esc', $group_id)");
        }
    }

    sc_alert("Permisos guardados correctamente para el usuario: $login_to_save");
}

// =========================================================================
// Permisos actuales
// =========================================================================
$assigned_groups = [];
sc_lookup(rs_assigned, "SELECT group_id FROM seguridad_users_groups WHERE login = '$login_esc'");
if (is_array({rs_assigned})) {
    foreach ({rs_assigned} as $row) {
        $assigned_groups[] = $row[0];
    }
}

// =========================================================================
// Módulos
// =========================================================================
$modules = [];
sc_lookup(rs_modules, "SELECT id_modulo_maestro, descripcion
                       FROM seguridad_grupos_maestros
                       ORDER BY descripcion");
if (is_array({rs_modules})) {
    foreach ({rs_modules} as $row) {
        $modules[] = ['id' => $row[0], 'name' => $row[1]];
    }
}

// =========================================================================
// HTML
// =========================================================================
echo '
<style>
body { font-family: Arial; background:#3C4858; }
.container { max-width:1200px; margin:auto; padding:10px; color:#aab8c5; }
.modules-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:15px; }
.module-card { background:#2F3A48; padding:15px; border-radius:10px; text-align:center; cursor:pointer; }
.module-card:hover, .module-card.active { background:#1C48E8; color:#fff; }
.permissions-list { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:15px; }
</style>

<div class="container">
<h3>Asignación de Permisos</h3>
';

if ($user_login != '') {

    echo '<form method="post">
          <input type="hidden" name="user_to_save" value="'.htmlspecialchars($user_login, ENT_QUOTES).'">

          <label>Seleccione un módulo:</label>
          <div class="modules-grid">';

    foreach ($modules as $m) {
        echo '<div class="module-card" onclick="selectModule(\'mod_'.$m['id'].'\', this)">
              📦<br>'.$m['name'].'</div>';
    }

    echo '</div>';

    foreach ($modules as $m) {

        echo '<div class="permissions-list" id="mod_'.$m['id'].'" style="display:none;">';

        sc_lookup(rs_groups, "SELECT group_id, description, descripcion_detallada
                              FROM seguridad_groups
                              WHERE modulo = '".$m['id']."'
                              ORDER BY description");

        if (is_array({rs_groups})) {
            foreach ({rs_groups} as $row) {
                $checked = in_array($row[0], $assigned_groups) ? 'checked' : '';
                echo '<label title="'.htmlspecialchars($row[2], ENT_QUOTES).'">
                      <input type="checkbox" name="groups[]" value="'.$row[0].'" '.$checked.'>
                      '.$row[1].'
                      </label>';
            }
        }

        echo '</div>';
    }

    echo '<input type="submit" value="Guardar Permisos"></form>';
}

echo '</div>

<script>
function selectModule(id, el) {
    document.querySelectorAll(".permissions-list").forEach(d => d.style.display="none");
    document.querySelectorAll(".module-card").forEach(c => c.classList.remove("active"));
    document.getElementById(id).style.display="grid";
    el.classList.add("active");
}
</script>';
