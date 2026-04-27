<?php
// ======================================================================
// --- 2. Consultar la plataforma de facturación en la base de datos ---
// ======================================================================

// Preparamos la consulta SQL para evitar inyección SQL
$sql = "SELECT plataforma FROM configuracion_factura_electronica WHERE empresa = '[usr_empresa]' and activo = '1'";

// sc_lookup() es ideal para obtener un solo valor
sc_lookup(rs_plataforma, $sql);



// Obtenemos el nombre de la plataforma del resultado de la consulta
$plataforma = {rs_plataforma[0][0]};


// ======================================================================
// --- 3. Ejecutar el código según la plataforma encontrada ---
// ======================================================================

if ($plataforma == 'unidigital') {
    
    // --- CÓDIGO PARA UNIDIGITAL ---
    
    sc_redir(ver_pdf_fact_difital, id_fact_digital = {id_fact_digital}, "T");
    

    
} elseif ($plataforma == 'the_factory') {
    
    // --- CÓDIGO PARA THE_FACTORY ---
 
     sc_redir(pdf_documentos_THK, ncontrol={corr_fiscal}; tipod='01' , "T");
    
    
} else {
    
    // --- SI LA PLATAFORMA NO ES NINGUNA DE LAS DOS, O ES NULL/VACÍO ---
    
    // No se hace nada, o se muestra un mensaje informativo.
    sc_ajax_message("La plataforma de facturación ('" . $plataforma . "') no requiere una acción.", "Sin Acción", "timeout=4");
    
    // El script terminará aquí sin hacer nada más.
}

?>
