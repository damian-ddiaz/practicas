?>
<style>
    body { font-family: Arial, sans-serif; background-color: #f0f2f5; padding: 20px; }
    .container { max-width: 800px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    h3, h4 { color: #333; }
    .module-section { margin-top: 20px; border-top: 2px solid #ddd; padding-top: 15px; }
    .permissions-list { margin-left: 20px; }
    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 20px;
    }
    input[type="submit"]:hover { background-color: #45a049; }
    select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
</style>

<div class="container">
    <h3>Asignación de Permisos de Usuario</h3>
    <p>Seleccione un usuario para visualizar y modificar sus permisos.</p>

    <?php
    // =========================================================================
    // Lógica para GUARDAR los permisos (mejorada y a prueba de duplicados)
    // =========================================================================
    if (isset($_POST['user_to_save']) && !empty($_POST['user_to_save'])) {
        // 1) Normalizar y escapar input
        $login_to_save = trim($_POST['user_to_save']);
        $login_esc = addslashes($login_to_save); // evita problemas con comillas en la SQL

        // 2) Obtener y normalizar grupos seleccionados: ints y únicos
        $selected_groups = isset($_POST['groups']) ? $_POST['groups'] : [];
        // Convierte a enteros y remueve duplicados
        $selected_groups = array_values(array_unique(array_map('intval', $selected_groups)));

        // 3) Si no hay grupos seleccionados --> eliminar todos los registros de usuario
        if (empty($selected_groups)) {
            sc_exec_sql("DELETE FROM seguridad_users_groups WHERE login = '{$login_esc}'");
        } else {
            // 4) Construir lista segura de IDs para la cláusula IN
            $ids_list = implode(',', $selected_groups); // ya son ints

            // 5) Eliminar solo los group_id que NO están en la lista seleccionada (mantiene los que se conservan)
            sc_exec_sql("DELETE FROM seguridad_users_groups WHERE login = '$login_esc' AND group_id NOT IN ($ids_list)");

            // 6) Insertar los group_id seleccionados que falten. Uso INSERT IGNORE para evitar error si existe.
            //    INSERT IGNORE es MySQL-specific; coincide con el error que reportaste.
            foreach ($selected_groups as $group_id_int) {
                sc_exec_sql("INSERT IGNORE INTO seguridad_users_groups (login, group_id) VALUES ('$login_esc', $group_id_int)");
            }
        }

        // 7) Mensaje de éxito
        sc_alert("Permisos guardados correctamente para el usuario: " . $login_to_save);
    }
    ?>

    <form name="form_permisos" method="post" action="">
        <select name="select_user" onchange="this.form.submit();">
            <option value="">-- Seleccionar Usuario --</option>
            <?php
            sc_lookup(rs_users, "SELECT login, name FROM seguridad_users ORDER BY name");
            if (is_array({rs_users})) {
                foreach ({rs_users} as $row_user) {
                    $selected = (isset($_POST['select_user']) && $_POST['select_user'] == $row_user[0]) ? 'selected' : '';
                    echo "<option value='{$row_user[0]}' {$selected}>{$row_user[1]} ({$row_user[0]})</option>";
                }
            }
            ?>
        </select>
    </form>

    <?php
    // =========================================================================
    // Lógica para MOSTRAR la interfaz de permisos
    // =========================================================================
    $user_login = isset($_POST['select_user']) ? $_POST['select_user'] : '';
    if (!empty($user_login)) {

        // Obtener los permisos actuales del usuario
        $assigned_groups = [];
        sc_lookup(rs_assigned, "SELECT group_id FROM seguridad_users_groups WHERE login = '". addslashes($user_login) ."'");
        if (is_array({rs_assigned})) {
            foreach ({rs_assigned} as $row_assigned) {
                $assigned_groups[] = $row_assigned[0];
            }
        }

        echo "<div class='module-section'>";
        echo "<form name='form_permissions_save' method='post' action=''>";
        echo "<input type='hidden' name='user_to_save' value='". htmlspecialchars($user_login, ENT_QUOTES) ."'>";

        // Consulta para obtener todos los módulos
        sc_lookup(rs_modules, "SELECT id_modulo_maestro, descripcion FROM seguridad_grupos_maestros ORDER BY descripcion");

        if (is_array({rs_modules})) {
            foreach ({rs_modules} as $row_module) {
                $id_modulo = $row_module[0];
                $nombre_modulo = $row_module[1];

                echo "<h4>Módulo: " . $nombre_modulo . "</h4>";
                echo "<div class='permissions-list'>";

                // Consulta para obtener los grupos de aplicaciones para el módulo actual
                sc_lookup(rs_groups, "SELECT group_id, description FROM seguridad_groups WHERE modulo = '$id_modulo' ORDER BY description");
                if (is_array({rs_groups})) {
                    foreach ({rs_groups} as $row_group) {
                        $group_id = $row_group[0];
                        $group_desc = $row_group[1];
                        $checked = in_array($group_id, $assigned_groups) ? 'checked' : '';

                        echo "<input type='checkbox' name='groups[]' value='{$group_id}' {$checked}> {$group_desc}<br>";
                    }
                }
                echo "</div>";
            }
        }

        echo "<input type='submit' value='Guardar Permisos'>";
        echo "</form>";
        echo "</div>";
    }
    ?>

    <script>
    // Evitar doble envío accidental (deshabilita el botón submit al enviar)
    document.addEventListener('submit', function(e){
        if (e.target && e.target.name === 'form_permissions_save') {
            var btn = e.target.querySelector('input[type="submit"]');
            if (btn) btn.disabled = true;
        }
    });
    </script>
</div>
<?php
