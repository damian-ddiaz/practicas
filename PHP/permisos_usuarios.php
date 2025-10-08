// =========================================================================
// Definir usuario desde variable global [usr_login]
// =========================================================================
$user_login = isset([usr_login]) ? [usr_login] : '';
$login_esc  = addslashes($user_login); // seguro para SQL

<?php
<style>	
    body { 
        font-family: Arial, sans-serif; 
        background-color: #3C4858; 
        padding: 0px; 
    }
    .container { 
        max-width: 1200px; 
        margin: 0px auto; 
        background: #3C4858; 
        padding: 0px; 
        border-radius: 8px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        font-size: 13px; 
    }
    h3 { 
        color: #aab8c5; 
        margin-bottom: 10px;
    }
    .permissions-list { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); 
        gap: 8px 15px; 
        color: #aab8c5; 
        font-size: 12px;
        margin-top: 15px;
    }
    .permissions-list label {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }
    select {
        width: 100%;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
        margin-top: 10px;
        margin-bottom: 15px;
        font-size: 13px;
    }
    input[type="submit"] {
        background-color: #1C48E8; 
        color: white; 
        padding: 8px 16px; 
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 15px;
    }
    input[type="submit"]:hover { 
        background-color: #665D5D; 
    }
</style>

<div class="container">
    <h3>Asignación de Permisos de Usuario</h3>

    <?php
    // =========================================================================
    // Guardar permisos
    // =========================================================================
    if (isset($_POST['user_to_save']) && !empty($_POST['user_to_save'])) {
        $login_to_save = trim($_POST['user_to_save']);
        $login_esc     = addslashes($login_to_save);

        $selected_groups = isset($_POST['groups']) ? $_POST['groups'] : [];
        $selected_groups = array_values(array_unique(array_map('intval', $selected_groups)));

        if (empty($selected_groups)) {
            sc_exec_sql("DELETE FROM seguridad_users_groups WHERE login = '$login_esc'");
        } else {
            $ids_list = implode(',', $selected_groups);
            sc_exec_sql("DELETE FROM seguridad_users_groups 
                         WHERE login = '$login_esc' 
                           AND group_id NOT IN ($ids_list)");
            foreach ($selected_groups as $group_id_int) {
                sc_exec_sql("INSERT IGNORE INTO seguridad_users_groups (login, group_id) 
                             VALUES ('$login_esc', $group_id_int)");
            }
        }

        sc_alert("Permisos guardados correctamente para el usuario: " . $login_to_save);
    }

    // =========================================================================
    // Obtener permisos actuales
    // =========================================================================
    $assigned_groups = [];
    sc_lookup(rs_assigned, "SELECT group_id FROM seguridad_users_groups WHERE login = '$login_esc'");
    if (is_array({rs_assigned})) {
        foreach ({rs_assigned} as $row_assigned) {
            $assigned_groups[] = $row_assigned[0];
        }
    }

    // =========================================================================
    // Obtener lista de módulos
    // =========================================================================
    $modules = [];
    sc_lookup(rs_modules, "SELECT id_modulo_maestro, descripcion 
                           FROM seguridad_grupos_maestros 
                           ORDER BY descripcion");
    if (is_array({rs_modules})) {
        foreach ({rs_modules} as $row_module) {
            $modules[] = [
                "id"   => $row_module[0],
                "name" => $row_module[1]
            ];
        }
    }
    ?>

    <?php if (!empty($user_login)) : ?>
        <form name="form_permissions_save" method="post" action="">
            <input type="hidden" name="user_to_save" value="<?= htmlspecialchars($user_login, ENT_QUOTES) ?>">

            <!-- Selector de módulos -->
            <label style="color:#aab8c5;">Seleccione un módulo:</label>
            <select id="moduleSelector" onchange="showModuleGroups(this.value)">
                <option value="">-- Seleccionar Módulo --</option>
                <?php foreach ($modules as $m): ?>
                    <option value="mod_<?= $m['id'] ?>"><?= $m['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Listas de grupos por módulo -->
            <?php
            if (!empty($modules)) {
                foreach ($modules as $m) {
                    $id_modulo     = $m['id'];
                    $nombre_modulo = $m['name'];

                    echo "<div class='permissions-list' id='mod_{$id_modulo}' style='display:none;'>";

                    sc_lookup(rs_groups, "SELECT group_id, description, descripcion_detallada
                                          FROM seguridad_groups 
                                          WHERE modulo = '$id_modulo' 
                                          ORDER BY description");
                    if (is_array({rs_groups})) {
                        foreach ({rs_groups} as $row_group) {
                            $group_id   = $row_group[0];
                            $group_desc = $row_group[1];
                            $group_tooltip = $row_group[2]; // descripcion_detallada
                            $checked    = in_array($group_id, $assigned_groups) ? 'checked' : '';

                            // Tooltip usando title
                            echo "<label title='". htmlspecialchars($group_tooltip, ENT_QUOTES) ."'>
                                    <input type='checkbox' name='groups[]' value='{$group_id}' {$checked}> 
                                    {$group_desc}
                                  </label>";
                        }
                    }
                    echo "</div>";
                }
            }
            ?>

            <input type="submit" value="Guardar Permisos">
        </form>
    <?php endif; ?>
</div>

<script>
// Mostrar solo los grupos del módulo seleccionado
function showModuleGroups(moduleId) {
    document.querySelectorAll('.permissions-list').forEach(div => {
        div.style.display = "none";
    });
    if (moduleId) {
        document.getElementById(moduleId).style.display = "grid";
    }
}

// Evitar doble envío accidental
document.addEventListener('submit', function(e){
    if (e.target && e.target.name === 'form_permissions_save') {
        var btn = e.target.querySelector('input[type="submit"]');
        if (btn) btn.disabled = true;
    }
});
</script>
?>