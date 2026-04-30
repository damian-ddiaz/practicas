<?php
    /**
     * SCRIPT DE MIGRACIÓN DE DATOS
     * De: Producción (45.179.164.7)
     * A:  Developer  (172.16.7.50)
     */

    // 1. CONEXIÓN PRODUCCIÓN (LECTURA)
    $host_prod = '45.179.164.7';
    $db_prod   = 'webservices';
    $user_prod = 'scryptcase';
    $pass_prod = 'Mt*1329*--1';

    $conn_prod = new mysqli($host_prod, $user_prod, $pass_prod, $db_prod);

    if ($conn_prod->connect_error) {
        die("Error de conexión Producción: " . $conn_prod->connect_error);
    }
    echo "CONEXION EXITOSA PRODUCCION<br>".PHP_EOL;

    // 2. CONEXIÓN DEVELOPER (INSERCIÓN)
    $host_dev = '172.16.7.50';
    $db_dev   = 'webservices';
    $user_dev = 'scryptcase';
    $pass_dev = 'Mt*1329*--1';

    $conn_dev = new mysqli($host_dev, $user_dev, $pass_dev, $db_dev);

    if ($conn_dev->connect_error) {
        die("Error de conexión Developer: " . $conn_dev->connect_error);
    }
    echo "CONEXION EXITOSA DEVELOPER<hr>".PHP_EOL;

    // --- INICIO MIGRACIÓN: TABLA seguridad_groups ---

    $sql_select_groups = "SELECT group_id, description, codigo_nivel, descripcion_detallada, modulo, proceso FROM seguridad_groups";
    $resultado_groups = $conn_prod->query($sql_select_groups);

    if ($resultado_groups && $resultado_groups->num_rows > 0) {
        // 1. Limpiar tabla destino correcta
        $conn_dev->query("DELETE FROM seguridad_groups");
        echo "Tabla 'seguridad_groups' en Developer limpiada.<br>";

        // 2. Preparar inserción
        $sql_insert_groups = "INSERT INTO seguridad_groups (group_id, description, codigo_nivel, descripcion_detallada, modulo, proceso) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn_dev->prepare($sql_insert_groups);

        if ($stmt) {
            while ($row = $resultado_groups->fetch_assoc()) {
                $stmt->bind_param(
                    "isssii", 
                    $row['group_id'], 
                    $row['description'], 
                    $row['codigo_nivel'], 
                    $row['descripcion_detallada'], 
                    $row['modulo'], 
                    $row['proceso']
                );
                $stmt->execute();
            }
            echo "Datos migrados correctamente a 'seguridad_groups': " . $resultado_groups->num_rows . " filas procesadas.<br>".PHP_EOL;
            $stmt->close();
        }
    } else {
        echo "No se encontraron datos o hubo error en 'seguridad_groups'.<br>".PHP_EOL;
    }

    echo "<hr>".PHP_EOL;

    // --- INICIO MIGRACIÓN: TABLA seguridad_users ---
    $sql_select_users = "SELECT 
        login, pswd, name, email, cedula, active, activation_code, priv_admin, mfa, 
        codigo_nivel, codigo_empresa, codigo_sucursal, telefono, imagen, ultima_sesion, 
        ventas_sucursales, cargo, ultimo_cambio_pswd, tiempo_revalidacion, 
        url_portal_aliado, url_portal_cliente, url_portal_dispositos, url_portal_intranet, 
        url_portal_rrhh, url_portal_teblero_gerencial, url_portal_administrativo, 
        url_portal_atencion_comercial, url_portal_contabilidad, url_portal_helpdesk, 
        url_portal_mantenimiento, url_portal_soporte, url_portal_vendedor, 
        url_portal_proyectos, url_portal_eventos, url_portal_finanzas, 
        url_portal_tributario, url_portal_gestionredes, url_portal_tickets, 
        url_portal_inventario, url_portal_puntodeventa, url_portal_reportes, 
        url_portal_formularios, url_portal_herramientas, url_portal_reportes_matriz, 
        url_portal_apps_compartidas, url_portal_configuracion, url_portal_patrocinadores 
    FROM seguridad_users";

    $resultado_users = $conn_prod->query($sql_select_users);

    if ($resultado_users && $resultado_users->num_rows > 0) {
        // 1. Limpiar tabla destino (OJO: Esto puede fallar si hay hijos en otras tablas, 
        // podrías necesitar SET FOREIGN_KEY_CHECKS = 0; antes de esto)
        $conn_dev->query("DELETE FROM seguridad_users");
        echo "Tabla 'seguridad_users' en Developer limpiada.<br>" . PHP_EOL;

        // 2. Preparar inserción (47 campos en total)
        // Usamos "b" para el campo 'imagen' (mediumblob) y "s" para el resto (strings/dates)
        $sql_insert_users = "INSERT INTO seguridad_users (
            login, pswd, name, email, cedula, active, activation_code, priv_admin, mfa, 
            codigo_nivel, codigo_empresa, codigo_sucursal, telefono, imagen, ultima_sesion, 
            ventas_sucursales, cargo, ultimo_cambio_pswd, tiempo_revalidacion, 
            url_portal_aliado, url_portal_cliente, url_portal_dispositos, url_portal_intranet, 
            url_portal_rrhh, url_portal_teblero_gerencial, url_portal_administrativo, 
            url_portal_atencion_comercial, url_portal_contabilidad, url_portal_helpdesk, 
            url_portal_mantenimiento, url_portal_soporte, url_portal_vendedor, 
            url_portal_proyectos, url_portal_eventos, url_portal_finanzas, 
            url_portal_tributario, url_portal_gestionredes, url_portal_tickets, 
            url_portal_inventario, url_portal_puntodeventa, url_portal_reportes, 
            url_portal_formularios, url_portal_herramientas, url_portal_reportes_matriz, 
            url_portal_apps_compartidas, url_portal_configuracion, url_portal_patrocinadores
        ) VALUES (" . str_repeat("?, ", 46) . "?)";

        $stmt_u = $conn_dev->prepare($sql_insert_users);

        if ($stmt_u) {
            while ($row = $resultado_users->fetch_assoc()) {
                // Definimos los tipos: 47 parámetros. 
                // Casi todos son strings (s). El campo 14 (imagen) es blob (b).
                $tipos = "sssssssssssssbsssssssssssssssssssssssssssssssss";
                
                $stmt_u->bind_param($tipos, 
                    $row['login'], $row['pswd'], $row['name'], $row['email'], $row['cedula'], 
                    $row['active'], $row['activation_code'], $row['priv_admin'], $row['mfa'], 
                    $row['codigo_nivel'], $row['codigo_empresa'], $row['codigo_sucursal'], 
                    $row['telefono'], $row['imagen'], $row['ultima_sesion'], $row['ventas_sucursales'], 
                    $row['cargo'], $row['ultimo_cambio_pswd'], $row['tiempo_revalidacion'], 
                    $row['url_portal_aliado'], $row['url_portal_cliente'], $row['url_portal_dispositos'], 
                    $row['url_portal_intranet'], $row['url_portal_rrhh'], $row['url_portal_teblero_gerencial'], 
                    $row['url_portal_administrativo'], $row['url_portal_atencion_comercial'], 
                    $row['url_portal_contabilidad'], $row['url_portal_helpdesk'], 
                    $row['url_portal_mantenimiento'], $row['url_portal_soporte'], $row['url_portal_vendedor'], 
                    $row['url_portal_proyectos'], $row['url_portal_eventos'], $row['url_portal_finanzas'], 
                    $row['url_portal_tributario'], $row['url_portal_gestionredes'], $row['url_portal_tickets'], 
                    $row['url_portal_inventario'], $row['url_portal_puntodeventa'], $row['url_portal_reportes'], 
                    $row['url_portal_formularios'], $row['url_portal_herramientas'], $row['url_portal_reportes_matriz'], 
                    $row['url_portal_apps_compartidas'], $row['url_portal_configuracion'], $row['url_portal_patrocinadores']
                );
                $stmt_u->execute();
            }
            echo "Datos migrados correctamente a 'seguridad_users': " . $resultado_users->num_rows . " filas procesadas.<br>" . PHP_EOL;
            $stmt_u->close();
        }
    } else {
        echo "No se encontraron datos o hubo error en 'seguridad_users'.<br>" . PHP_EOL;
    }

    // --- INICIO MIGRACIÓN OPTIMIZADA: TABLA seguridad_users_groups ---
        $sql_select_users_groups = "SELECT login, group_id FROM seguridad_users_groups";
        $resultado_users_groups = $conn_prod->query($sql_select_users_groups);

        if ($resultado_users_groups && $resultado_users_groups->num_rows > 0) {
            // 1. Limpiar tabla destino
            $conn_dev->query("DELETE FROM seguridad_users_groups");
            echo "Tabla 'seguridad_users_groups' en Developer limpiada.<br>" . PHP_EOL;

            // 2. Configuración de Lotes (Batch)
            $batch_size = 500; // Tamaño del lote (ajustable según memoria)
            $rows_to_insert = [];
            $total_processed = 0;

            // Desactivar autocommit para mayor velocidad
            $conn_dev->autocommit(FALSE);

            while ($row = $resultado_users_groups->fetch_assoc()) {
                // Escapar valores para evitar inyecciones en la construcción manual
                $login = $conn_dev->real_escape_string($row['login']);
                $group_id = (int)$row['group_id'];
                
                $rows_to_insert[] = "('$login', $group_id)";
                $total_processed++;

                // Cuando alcanzamos el tamaño del lote, ejecutamos la inserción
                if (count($rows_to_insert) >= $batch_size) {
                    $sql_batch = "INSERT INTO seguridad_users_groups (login, group_id) VALUES " . implode(', ', $rows_to_insert);
                    $conn_dev->query($sql_batch);
                    $rows_to_insert = []; // Limpiar buffer
                }
            }

            // Insertar los registros restantes que no completaron el último lote
            if (!empty($rows_to_insert)) {
                $sql_batch = "INSERT INTO seguridad_users_groups (login, group_id) VALUES " . implode(', ', $rows_to_insert);
                $conn_dev->query($sql_batch);
            }

            // Confirmar cambios
            $conn_dev->commit();
            $conn_dev->autocommit(TRUE);

            echo "Datos migrados correctamente a 'seguridad_users_groups' (por lotes): " . $total_processed . " filas procesadas.<br>" . PHP_EOL;

        } else {
            echo "No se encontraron datos o hubo error en 'seguridad_users_groups'.<br>" . PHP_EOL;
        }

    // --- INICIO MIGRACIÓN OPTIMIZADA: TABLA seguridad_apps ---
        $sql_select_apps_list = "SELECT app_name, app_type, description, modulo, proceso FROM seguridad_apps";
        $resultado_apps_list = $conn_prod->query($sql_select_apps_list);

        if ($resultado_apps_list && $resultado_apps_list->num_rows > 0) {
            // 1. Limpiar tabla destino
            $conn_dev->query("DELETE FROM seguridad_apps");
            echo "Tabla 'seguridad_apps' en Developer limpiada.<br>" . PHP_EOL;

            // 2. Configuración de Lotes (Batch)
            $batch_size = 500; 
            $rows_to_insert = [];
            $total_processed = 0;

            // Desactivar autocommit para máxima velocidad
            $conn_dev->autocommit(FALSE);

            while ($row = $resultado_apps_list->fetch_assoc()) {
                // Sanitización y manejo de NULLs para evitar errores en PHP 8.1+
                $app_name    = $conn_dev->real_escape_string($row['app_name'] ?? '');
                $app_type    = $conn_dev->real_escape_string($row['app_type'] ?? '');
                $description = $conn_dev->real_escape_string($row['description'] ?? '');
                $modulo      = isset($row['modulo']) ? (int)$row['modulo'] : "NULL";
                $proceso     = isset($row['proceso']) ? (int)$row['proceso'] : "NULL";
                
                // Construcción de la fila
                // Nota: modulo y proceso van sin comillas si son números o la palabra NULL
                $rows_to_insert[] = "('$app_name', '$app_type', '$description', $modulo, $proceso)";
                $total_processed++;

                // Ejecutar el lote cuando se alcance el tamaño definido
                if (count($rows_to_insert) >= $batch_size) {
                    $sql_batch = "INSERT INTO seguridad_apps (app_name, app_type, description, modulo, proceso) VALUES " . implode(', ', $rows_to_insert);
                    $conn_dev->query($sql_batch);
                    $rows_to_insert = []; 
                }
            }

            // Insertar registros restantes
            if (!empty($rows_to_insert)) {
                $sql_batch = "INSERT INTO seguridad_apps (app_name, app_type, description, modulo, proceso) VALUES " . implode(', ', $rows_to_insert);
                $conn_dev->query($sql_batch);
            }

            // Confirmar transacción
            $conn_dev->commit();
            $conn_dev->autocommit(TRUE);

            echo "Datos migrados correctamente a 'seguridad_apps': " . $total_processed . " filas procesadas.<br>" . PHP_EOL;

        } else {
            echo "No se encontraron datos o hubo error en 'seguridad_apps'.<br>" . PHP_EOL;
        }

    // --- INICIO MIGRACIÓN OPTIMIZADA: TABLA seguridad_groups_apps ---
        $sql_select_apps = "SELECT group_id, app_name, priv_access, priv_insert, priv_delete, priv_update, priv_export, priv_print FROM seguridad_groups_apps";
        $resultado_apps = $conn_prod->query($sql_select_apps);

         // ... (código anterior igual)

        if ($resultado_apps && $resultado_apps->num_rows > 0) {
            $conn_dev->query("DELETE FROM seguridad_groups_apps");
            echo "Tabla 'seguridad_groups_apps' en Developer limpiada.<br>" . PHP_EOL;

            $batch_size = 500; 
            $rows_to_insert = [];
            $total_processed = 0;

            // IMPORTANTE: No desactivamos autocommit globalmente si vamos a manejar lotes manuales
            // o simplemente hacemos commit en cada lote.

            while ($row = $resultado_apps->fetch_assoc()) {
                $group_id    = (int)$row['group_id'];
                $app_name    = $conn_dev->real_escape_string($row['app_name'] ?? '');
                $priv_access = $conn_dev->real_escape_string($row['priv_access'] ?? 'N');
                $priv_insert = $conn_dev->real_escape_string($row['priv_insert'] ?? 'N');
                $priv_delete = $conn_dev->real_escape_string($row['priv_delete'] ?? 'N');
                $priv_update = $conn_dev->real_escape_string($row['priv_update'] ?? 'N');
                $priv_export = $conn_dev->real_escape_string($row['priv_export'] ?? 'N');
                $priv_print  = $conn_dev->real_escape_string($row['priv_print'] ?? 'N');
                
                $rows_to_insert[] = "($group_id, '$app_name', '$priv_access', '$priv_insert', '$priv_delete', '$priv_update', '$priv_export', '$priv_print')";
                $total_processed++;

                if (count($rows_to_insert) >= $batch_size) {
                    $sql_batch = "INSERT INTO seguridad_groups_apps (group_id, app_name, priv_access, priv_insert, priv_delete, priv_update, priv_export, priv_print) VALUES " . implode(', ', $rows_to_insert);
                    
                    $conn_dev->query($sql_batch);
                    // EN GALERA: Confirmamos cada lote para no exceder wsrep_max_ws_rows
                    $conn_dev->commit(); 
                    
                    $rows_to_insert = []; 
                }
            }

            // Insertar el último remanente
            if (!empty($rows_to_insert)) {
                $sql_batch = "INSERT INTO seguridad_groups_apps (group_id, app_name, priv_access, priv_insert, priv_delete, priv_update, priv_export, priv_print) VALUES " . implode(', ', $rows_to_insert);
                $conn_dev->query($sql_batch);
                $conn_dev->commit();
            }

            echo "Datos migrados correctamente a 'seguridad_groups_apps': " . $total_processed . " filas procesadas.<br>" . PHP_EOL;
        }
    
    // --- INICIO MIGRACIÓN: TABLA configuracion_modulos_maestro ---

    // Verificar y reconectar si es necesario
    // mysqli::ping() is deprecated in PHP 8.1+. Use a simple query to check connection.
    $result = $conn_prod->query("SELECT 1");
    if (!$result) {
        $conn_prod = new mysqli($host_prod, $user_prod, $pass_prod, $db_prod);
        if ($conn_prod->connect_error) {
            die("Error de reconexión Producción: " . $conn_prod->connect_error);
        }
    }

    $sql_select_maestro = "SELECT id_modulo_maestro, descripcion FROM configuracion_modulos_maestro";

        $resultado_maestro = $conn_prod->query($sql_select_maestro);

    if ($resultado_maestro && $resultado_maestro->num_rows > 0) {
        // 1. Limpiar tabla destino
        $conn_dev->query("DELETE FROM configuracion_modulos_maestro");
        echo "Tabla 'configuracion_modulos_maestro' en Developer limpiada.<br>".PHP_EOL;

        // 2. Preparar inserción
        $sql_insert_maestro = "INSERT INTO configuracion_modulos_maestro (id_modulo_maestro, descripcion) VALUES (?, ?)";
        $stmt_m = $conn_dev->prepare($sql_insert_maestro);

        if ($stmt_m) {
            while ($row = $resultado_maestro->fetch_assoc()) {
                $stmt_m->bind_param("is", $row['id_modulo_maestro'], $row['descripcion']);
                $stmt_m->execute();
            }
            echo "Datos migrados correctamente a 'configuracion_modulos_maestro': " . $resultado_maestro->num_rows . " filas procesadas.<br>".PHP_EOL;
            $stmt_m->close();
        }
    } else {
        echo "No se encontraron datos o hubo error en 'configuracion_modulos_maestro'.<br>".PHP_EOL;
    }

    // 3. Cierre de conexiones
    $conn_prod->close();
    $conn_dev->close();

    echo "<br><strong>PROCESO CULMINADO CON ÉXITO</strong>";
?>