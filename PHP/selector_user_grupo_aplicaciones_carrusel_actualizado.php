'<?php

    // =========================================================================
    // USUARIO
    // =========================================================================
    $user_login = isset([par_login]) ? [par_login] : '';
    $login_esc  = addslashes($user_login);

    // =========================================================================
    // GUARDAR PERMISOS
    // =========================================================================
    if (isset($_POST['user_to_save']) && $_POST['user_to_save'] != '') {

        $login_to_save = trim($_POST['user_to_save']);
        $login_esc     = addslashes($login_to_save);

        $selected_groups = isset($_POST['groups']) ? $_POST['groups'] : [];
        $selected_groups = array_unique(array_map('intval', $selected_groups));

        sc_exec_sql("DELETE FROM seguridad_users_groups WHERE login = '$login_esc'");

        if (!empty($selected_groups)) {
            foreach ($selected_groups as $group_id) {
                sc_exec_sql("INSERT IGNORE INTO seguridad_users_groups (login, group_id)
                            VALUES ('$login_esc', $group_id)");
            }
            // =========================================================================
            // Asignando Modulos Automaticamente
            // =========================================================================
            $assigned_modulos = [];
            sc_lookup(rs_assigned_modulos, "select id_modulo_maestro, descripcion from seguridad_grupos_maestros");
            if (is_array({rs_assigned_modulos})) {
                foreach ({rs_assigned_modulos} as $row) {
                    $id_modulo_maestro  = $row[0];
                    $assigned_modulos[] = $row[0];
                    
                    sc_lookup(rs_assigned_grupo, " SELECT sug.login, sug.group_id, sg.description, sg.modulo 
                    FROM seguridad_users_groups sug 
                    LEFT JOIN seguridad_groups sg ON sg.group_id = sug.group_id 
                    WHERE  sug.login = '$login_esc'  
                    AND  sg.modulo = $id_modulo_maestro");
                        // Actualizando Portales SI o NO
                        if($id_modulo_maestro == 1){ // ADMINISTRATIVO SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_administrativo = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_administrativo = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }	
                        if($id_modulo_maestro == 2){ // INTRANET Y REPOSITORIO SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_intranet = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_intranet = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }					
                        if($id_modulo_maestro == 3){ // CONTABILIDAD SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_contabilidad = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_contabilidad = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }					
                        if($id_modulo_maestro == 4){ // RRHH SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_rrhh = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_rrhh = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }				
                        if($id_modulo_maestro == 5){ // ALIADO COMERCIAL SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_aliado = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_aliado = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }				
                        if($id_modulo_maestro == 6){ // ATENCION COMERCIAL SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_atencion_comercial = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_atencion_comercial = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 7){ // VENDEDORL SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_vendedor = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_vendedor = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 8){ // SOPORTE TECNICO SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_soporte = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_soporte = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 9){ // HELPDESK SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_helpdesk = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_helpdesk = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }			
                        if($id_modulo_maestro == 10){ // DISPOSITIVOS SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_dispositos = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_dispositos = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 11){ // MANTENIMIENTO SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_mantenimiento = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_mantenimiento = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        /*
                        if($id_modulo_maestro == 12){ // API SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_api = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_api = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        */
                        if($id_modulo_maestro == 13){ // HERRAMIENTAS SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_herramientas = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_herramientas = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }		
                        if($id_modulo_maestro == 14){ // GESTIÓN DE REDES SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_gestionredes = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_gestionredes = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 15){ // INVENTARIO SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_inventario = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_inventario = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 16){ // BANCA Y FINANZAS SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_finanzas = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_finanzas = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }				
                        if($id_modulo_maestro == 17){ // TAREAS, PROYECTOS Y TICKETS SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_proyectos = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_proyectos = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 18){ // REPORTES - MATRIZ SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_reportes_matriz = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_reportes_matriz = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 19){ // PUNTO DE VENTA SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_puntodeventa = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_puntodeventa = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 20){ // REPORTES - SUCURSAL SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_reportes = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_reportes = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        /*
                        if($id_modulo_maestro == 21){ // PANEL DE CONTROL  SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_reportes = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_reportes = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        */
                        if($id_modulo_maestro == 22){ // CONFIGURACIÓN SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_configuracion = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_configuracion = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }
                        if($id_modulo_maestro == 23){ // APLICACONES COMPARTIDAS SI 
                            if (!empty({rs_assigned_grupo})) {
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_configuracion = 'SI'
                                WHERE login = '$login_esc'");
                            }else{// NO
                                sc_exec_sql("UPDATE seguridad_users SET url_portal_configuracion = 'NO'
                                WHERE login = '$login_esc'");
                            }
                        }			
                }
            }
            // =========================================================================	
        
        }
        sc_alert("Permisos guardados correctamente para el usuario: $login_to_save");
    }

    // =========================================================================
    // PERMISOS ACTUALES
    // =========================================================================
    $assigned_groups = [];
    sc_lookup(rs_assigned, "SELECT group_id FROM seguridad_users_groups WHERE login = '$login_esc'");
    if (is_array({rs_assigned})) {
        foreach ({rs_assigned} as $row) {
            $assigned_groups[] = $row[0];
        }
    }

    // =========================================================================
    // MODULOS
    // =========================================================================
    $modules = [];
    sc_lookup(rs_modules, "
        SELECT id_modulo_maestro, descripcion, icono
        FROM seguridad_grupos_maestros
        WHERE visible_usuario = 'SI'
        ORDER BY descripcion
    ");
    if (is_array({rs_modules})) {
        foreach ({rs_modules} as $row) {
            $modules[] = [
                'id'    => $row[0],
                'name'  => $row[1],
                'icono' => $row[2]
            ];
        }
    }

    ?>
    <script>
    function filterPermissions(input){
        const filter = input.value.toLowerCase();
        const box = input.closest('.permission-box');

        box.querySelectorAll('.permission-item').forEach(function(item){
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(filter) ? '' : 'none';
        });
    }
    </script>
    <?php


    // =========================================================================
    // HTML + CSS
    // =========================================================================
    echo '
    <style>
    body{font-family:Arial;background:#f0f2f5}
    .container{max-width:1200px;margin:auto}

    /* Carrusel */
    .carousel-wrapper{position:relative;margin:15px 0}
    .modules-grid{display:flex;overflow-x:auto;gap:15px;padding:0 50px 15px;white-space:nowrap}
    .modules-grid::-webkit-scrollbar{display:none}

    /* Nuevo */
    .permission-filter{
        width:100%;
        padding:6px 8px;
        margin-bottom:10px;
        border-radius:6px;
        border:1px solid #ccc;
        font-size:12px;
    }

    .module-card{
        width:auto;
        min-width:135px;
        font-size: 12px;   /* 👈 AQUÍ cambias el tamaño del texto del módulo */
        height:50px;
        background:#f0f2f5;
    
        cursor:pointer;
        box-shadow:5px 5px 15px rgba(0,0,0,.5);
        transition:.3s
        
        /* 3. Centrado flexible (Mejor que text-align:center para bloques de texto) */
        display: flex;
        align-items: center;     /* Centra verticalmente */
        justify-content: center;  /* Centra horizontalmente */
        text-align: justify;	
        text-justify: inter-word; /* Mejora el espacio entre palabras */
        display: flex;
        align-items: center;      /* Centra el bloque de texto verticalmente */
        hyphens: auto;
        
        /* 2. Ajustamos el padding para dar más aire lateral */
        padding: 15px 0px 30px; 
        margin: 0px;
        border-radius: 10px;
    }

    .module-card:hover,.module-card.active{
        background:#1C48E8;color:#fff;transform:scale(1.05)
    }

    .nav-btn{
        position:absolute;top:0%;
        background:rgba(0,0,0,.4);
        color:#fff;
        border:none;
        padding:30px 15px;
        font-size:2em;
        cursor:pointer;
        border-radius:5px
    }
    .left-btn{left:0}
    .right-btn{right:0}

    /* DUAL LIST */
    .dual-permissions{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:20px;
        margin-top:20px
    }
    .permission-box{
        background:#f0f2f5;
        border-radius:10px;
        padding:10px;

        height:600px;          /* 👈 ALTURA FIJA (ajustable) */
        overflow-y:auto;       /* 👈 SCROLL VERTICAL */

        box-shadow:inset 0 0 6px rgba(0,0,0,.2);

        scroll-behavior:smooth;
    }
    .permission-box h4{text-align:center;color:black}

    .permission-item{
        background:white;
        padding:6px 10px;
        margin-bottom:6px;
        border-radius:6px;
        cursor:pointer;
        box-shadow:0 2px 4px rgba(0,0,0,.2);
        transition:.2s
    }
    .permission-item:hover{background:#1C48E8;color:white}

    input[type=submit]{
        margin-top:30px;padding:10px 25px;
        font-size:1.1em;border:none;border-radius:10px;
        cursor:pointer;box-shadow:5px 5px 15px rgba(0,0,0,.5)
    }
    </style>

    <div class="container">
    ';

    if ($user_login != '') {

        echo '
        <form method="post">
        <input type="hidden" name="user_to_save" value="'.$user_login.'">

        <div class="carousel-wrapper">
            <button type="button" class="nav-btn left-btn" onclick="scrollCarousel(-1)">&#60;</button>
            <div class="modules-grid" id="modulesGrid">
        ';

        foreach ($modules as $m) {
            $name = ucfirst(strtolower($m['name']));
            $name = str_replace(' ', '<br>', $name);

            echo '
            <div class="module-card" onclick="selectModule(\'mod_'.$m['id'].'\',this)">
                <img src="'.$m['icono'].'" width="40"><br>'.$name.'
            </div>';
        }

        echo '
            </div>
            <button type="button" class="nav-btn right-btn" onclick="scrollCarousel(1)">&#62;</button>
        </div>
        ';

        foreach ($modules as $m) {

            echo '<div id="mod_'.$m['id'].'" style="display:none">
                <div class="dual-permissions">';

            $asig = [];
            $disp = [];

            sc_lookup(rs_groups,"
                SELECT group_id, description, descripcion_detallada
                FROM seguridad_groups
                WHERE modulo = '".$m['id']."'
                ORDER BY description
            ");

            if (is_array({rs_groups})) {
                foreach ({rs_groups} as $r) {
                    if (in_array($r[0], $assigned_groups)) {
                        $asig[] = $r;
                    } else {
                        $disp[] = $r;
                    }
                }
            }

            echo '<div class="permission-box">
                <h4>Permisos Disponibles (Click para Asignar)</h4>
                <input type="text"
                    class="permission-filter"
                    placeholder="Buscar Modulo..."
                    onkeyup="filterPermissions(this)">';
        
            foreach ($disp as $p) {
                echo '<div class="permission-item"
                    onclick="addPermission(this, '.$p[0].')"
                    title="'.htmlspecialchars($p[2], ENT_QUOTES).'">
                    '.$p[1].'
                    </div>';
            }

            echo '</div>';
                    
            echo '<div class="permission-box">
                <h4>Permisos Asignados (Click para Eliminar)</h4>
                <input type="text"
                    class="permission-filter"
                    placeholder="Buscar Modulo..."
                    onkeyup="filterPermissions(this)">';
            
            foreach ($asig as $p) {
                echo '<div class="permission-item"
                    onclick="removePermission(this, '.$p[0].')"
                    title="'.htmlspecialchars($p[2], ENT_QUOTES).'">
                    '.$p[1].'
                    <input type="hidden" name="groups[]" value="'.$p[0].'">
                    </div>';
            }

            echo '</div></div></div>';
        }

        echo '<input type="submit" value="Guardar Permisos"></form>';
    }

    echo '
    </div>

    <script>

    function scrollCarousel(d){
        document.getElementById("modulesGrid").scrollLeft += d * 850;
    }

    function selectModule(id, el){
        document.querySelectorAll("[id^=mod_]").forEach(d=>d.style.display="none");
        document.querySelectorAll(".module-card").forEach(c=>c.classList.remove("active"));
        document.getElementById(id).style.display="block";
        el.classList.add("active");
    }

    function addPermission(el, id){
        const box = el.closest(".dual-permissions").children[1];
        el.onclick = function(){ removePermission(el, id); };
        el.innerHTML += "<input type=\"hidden\" name=\"groups[]\" value=\""+id+"\">";
        box.appendChild(el);
    }

    function removePermission(el, id){
        const box = el.closest(".dual-permissions").children[0];
        el.onclick = function(){ addPermission(el, id); };
        const h = el.querySelector("input");
        if (h) h.remove();
        box.appendChild(el);
    }

    document.addEventListener("DOMContentLoaded", function(){
        const first = document.querySelector(".module-card");
        if (first) first.click();
    });
    </script>
    ';
?>