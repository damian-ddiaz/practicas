<?php
    if({codigo_nivel} >= 8){
        actualiza_url_portal();	
    }
    // Asignado Group Default
    sc_exec_sql("INSERT INTO seguridad_users_groups (login, group_id)
                                VALUES ('{login}', 2)");
?>