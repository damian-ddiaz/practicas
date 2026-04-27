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

    // --- INICIO MIGRACIÓN: TABLA seguridad_users_groups ---

    $sql_select_users_groups = "SELECT login, group_id FROM seguridad_users_groups";
    $resultado_users_groups = $conn_prod->query($sql_select_users_groups);

    if ($resultado_users_groups && $resultado_users_groups->num_rows > 0) {
        // 1. Limpiar tabla destino
        $conn_dev->query("DELETE FROM seguridad_users_groups");
        echo "Tabla 'seguridad_users_groups' en Developer limpiada.<br>" . PHP_EOL;

        // 2. Preparar inserción (s = string para login, i = integer para group_id)
        $sql_insert_users_groups = "INSERT INTO seguridad_users_groups (login, group_id) VALUES (?, ?)";
        $stmt_ug = $conn_dev->prepare($sql_insert_users_groups);

        if ($stmt_ug) {
            while ($row = $resultado_users_groups->fetch_assoc()) {
                // "si" indica que el primer parámetro es String y el segundo es Integer
                $stmt_ug->bind_param("si", $row['login'], $row['group_id']);
                $stmt_ug->execute();
            }
            echo "Datos migrados correctamente a 'seguridad_users_groups': " . $resultado_users_groups->num_rows . " filas procesadas.<br>" . PHP_EOL;
            $stmt_ug->close();
        }
    } else {
        echo "No se encontraron datos o hubo error en 'seguridad_users_groups'.<br>" . PHP_EOL;
    }

    // --- INICIO MIGRACIÓN: TABLA configuracion_modulos_maestro ---

    // Verificar y reconectar si es necesario
    if (!$conn_prod->ping()) {
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