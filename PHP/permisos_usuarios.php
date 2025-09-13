// =========================================================================
// Definir usuario desde variable global [usr_login]
// =========================================================================
$user_login = isset([usr_login]) ? [usr_login] : '';
$login_esc  = addslashes($user_login); // seguro para SQL
?>
<style>	
    body { 
        font-family: Arial, sans-serif; 
        background-color: #3C4858; 
        padding: 0px; 
    }
    .container { 
        max-width: 1100px; 
        margin: 0px; 
        background: #3C4858; 
        padding: 0px; /* <-- reducido */
        border-radius: 8px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        font-size: 12px; 
    }
    h3, h4 { 
        color: #aab8c5; 
    }
    .permissions-list { 
        display: grid; 
        grid-template-columns: repeat(4, 1fr); /* <-- ahora 4 columnas */
        gap: 8px 15px; /* un poco más compacto */
        color: #aab8c5; 
        font-size: 12px;
        margin-bottom: 15px;
    }
    .permissions-list label {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    input[type="submit"] {
        background-color: #1C48E8; /* Botón Color */
        color: white; /* Color de fuente */
        padding: 8px 16px; /* más compacto */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 15px;
    }
    input[type="submit"]:hover { 
        background-color: #665D5D; /* Hover */
    }
</style>

<div class="container">
    <?php
    // =========================================================================
    // Lógica para GUARDAR los permisos
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
    sc_lookup(rs_assigned, "SELECT group_id 
                            FROM seguridad_users_groups 
                            WHERE login = '$login_esc'");
    if (is_array({rs_assigned})) {
        foreach ({rs_assigned} as $row_assigned) {
            $assigned_groups[] = $row_assigned[0];
        }
    }

    // =========================================================================
    // Mostrar interfaz de permisos
    // =========================================================================
    if (!empty($user_login)) {
        echo "<div class='module-section'>";
        echo "<form name='form_permissions_save' method='post' action=''>";
        echo "<input type='hidden' name='user_to_save' value='". htmlspecialchars($user_login, ENT_QUOTES) ."'>";

        sc_lookup(rs_modules, "SELECT id_modulo_maestro, descripcion 
                               FROM seguridad_grupos_maestros 
                               ORDER BY descripcion");

        if (is_array({rs_modules})) {
            foreach ({rs_modules} as $row_module) {
                $id_modulo     = $row_module[0];
                $nombre_modulo = $row_module[1];

//                echo "<h4><Módulo: " . $nombre_modulo . "</h4>";
                echo "<h4>" . $nombre_modulo . "</h4>";

                echo "<div class='permissions-list'>";

                sc_lookup(rs_groups, "SELECT group_id, description 
                                      FROM seguridad_groups 
                                      WHERE modulo = '$id_modulo' 
                                      ORDER BY description");

                if (is_array({rs_groups})) {
                    foreach ({rs_groups} as $row_group) {
                        $group_id   = $row_group[0];
                        $group_desc = $row_group[1];
                        $checked    = in_array($group_id, $assigned_groups) ? 'checked' : '';

                        echo "<label><input type='checkbox' name='groups[]' value='{$group_id}' {$checked}> {$group_desc}</label>";
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
    // Evitar doble envío accidental
    document.addEventListener('submit', function(e){
        if (e.target && e.target.name === 'form_permissions_save') {
            var btn = e.target.querySelector('input[type="submit"]');
            if (btn) btn.disabled = true;
        }
    });
    </script>
</div>
<?php
