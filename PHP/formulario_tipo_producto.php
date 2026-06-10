<?php
// ====================================================
// DECLARAR VARIABLES GLOBALES DE SCRIPTCASE
// ====================================================
sc_apl_status('Tipos de Productos', 'on');
sc_set_global([usr_empresa]);
sc_set_global([usr_sucursal]);
sc_set_global([usr_login]);
sc_set_global([par_codigo_tipo_productos]);
sc_set_global([par_nuevo]);

// Asignación de valores a variables locales para mayor seguridad
$fecha         = date('Y-m-d H:i:s');
$usuario       = [usr_login];
$empresa       = [usr_empresa];
$sucursal      = [usr_sucursal];
$ip_estacion   = $_SERVER['REMOTE_ADDR'];
$es_matriz     = [sucursal_matriz]; // Variable para control de UI

// =============================================
// VARIABLES DE CONTROL
// =============================================
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$codigo_tipo_productos = '';
$nombre_tipo_productos = '';
$maneja_stock = 'SI';
$readonly = '';

// =============================================
// CARGAR DATOS SI [par_codigo_tipo_productos] NO ESTÁ VACÍO
// =============================================
$codigoRef = [par_codigo_tipo_productos];

if (!empty($codigoRef) AND [par_nuevo] == 'NO') {
    $sql_load = "SELECT codigo_tipo_productos, nombre_tipo_productos, maneja_stock 
                  FROM inventario_tipo_productos 
                  WHERE codigo_tipo_productos = '$codigoRef' AND empresa = '$empresa'";
    
    sc_lookup(ds, $sql_load, 'conn_example');

    if (isset({ds[0][0]})) {
        $codigo_tipo_productos = {ds[0][0]};
        $nombre_tipo_productos = {ds[0][1]};
        $maneja_stock          = {ds[0][2]};
        $readonly = 'readonly';
    }
}

// =============================================
// ✅ SI [par_nuevo] == "SI"
// =============================================
if ([par_nuevo] == 'SI') {
    $codigo_tipo_productos = '';
    $nombre_tipo_productos = '';
    $maneja_stock = 'NO';
    $readonly = '';
    [par_codigo_tipo_productos] = '';
    [par_nuevo] = 'NO'; 
}

// =============================================
// ACCIONES CRUD
// =============================================

// NUEVO
if ($accion == 'nuevo') {
    [par_codigo_tipo_productos] = '';
    sc_redir($_SERVER['PHP_SELF']); 
}

// GUARDAR
if ($accion == 'guardar') {
    $codigo   = sc_sql_injection($_POST['codigo_tipo_productos']);
    $nombre   = sc_sql_injection($_POST['nombre_tipo_productos']);
    $stock    = sc_sql_injection($_POST['maneja_stock']);

    $sql_check = "SELECT codigo_tipo_productos FROM inventario_tipo_productos 
                  WHERE codigo_tipo_productos = $codigo AND empresa = '$empresa'";
    sc_lookup(check_exists, $sql_check, 'conn_example');

    if (isset({check_exists[0][0]})) {
        // Lógica de Update
        sc_lookup(suc_mat, "SELECT count(*) FROM configuracion_sucursal WHERE empresa ='$empresa' AND sucursal_matriz = 'SI'");
        $var_sucursal_cantidad = {suc_mat[0][0]};

        if ($var_sucursal_cantidad == 1){
            sc_lookup(cant_suc, "SELECT count(*) FROM configuracion_sucursal WHERE empresa = '$empresa'");
            $var_cant_sucursal = {cant_suc[0][0]};

            if($var_cant_sucursal > 1){
                $update_sql = "UPDATE inventario_tipo_productos SET nombre_tipo_productos = $nombre, maneja_stock = $stock, usuario = '$usuario', fecha = '$fecha', ip_estacion = '$ip_estacion' WHERE codigo_tipo_productos = $codigo AND empresa = '$empresa'";
                sc_exec_sql($update_sql, 'conn_example');
            }
        } else {
            $update_sql = "UPDATE inventario_tipo_productos SET nombre_tipo_productos = $nombre, maneja_stock = $stock, usuario = '$usuario', fecha = '$fecha', ip_estacion = '$ip_estacion' WHERE codigo_tipo_productos = $codigo AND empresa = '$empresa' AND sucursal = '$sucursal'";
            sc_exec_sql($update_sql, 'conn_example');
        }
        [par_codigo_tipo_productos] = $codigo;
        sc_redir('grid_inventario_tipo_productos');
    } else {
        // Lógica de Insert
        $tipo_matriz = ([sucursal_matriz] == 'SI') ? 'SI' : 'NO';
        $insert_sql = "INSERT INTO inventario_tipo_productos (codigo_tipo_productos, nombre_tipo_productos, maneja_stock, usuario, fecha, ip_estacion, empresa, sucursal, tipo_matriz) VALUES ($codigo, $nombre, $stock, '$usuario', '$fecha', '$ip_estacion', '$empresa', '$sucursal', '$tipo_matriz')";
        sc_exec_sql($insert_sql, 'conn_example');
        sc_alert('✅ Registro insertado correctamente');
        sc_redir('grid_inventario_tipo_productos');
    }
}

// ELIMINAR
if ($accion == 'eliminar' && (!empty($_POST['codigo_tipo_productos']))) {
    $codigoEliminar = $_POST['codigo_tipo_productos'];
    $sql_inv = "SELECT codigo_productos FROM inventario_productos WHERE codigo_tiposerv_productos = '$codigoEliminar' AND empresa = '$empresa'";
    sc_lookup(check_dep, $sql_inv, 'conn_example');

    if (isset({check_dep[0][0]})) {
        sc_alert("❌ No se puede eliminar: tiene productos relacionados.");
    } else {
        $delete_sql = "DELETE FROM inventario_tipo_productos WHERE codigo_tipo_productos = '$codigoEliminar' AND empresa = '$empresa'";
        sc_exec_sql($delete_sql, 'conn_example');
        sc_alert('🗑️ Registro eliminado');
        [par_codigo_tipo_productos] = '';
        sc_redir('grid_inventario_tipo_productos');
    }
}

// SALIR
if ($accion == 'salir') {
    sc_redir('grid_inventario_tipo_productos');
}

// =============================================
// SALIDA HTML
// =============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fafafa; margin: 0; padding: 0; }
    .container { max-width: 900px; background: #fff; margin: 60px auto; padding: 40px 50px; border-radius: 5px; box-shadow: 0 4px 16px rgba(0,0,0,0.05); border: 1px solid #e6e6f0; }
    h2 { text-align: left; color: #080808; font-size: 22px; margin-bottom: 25px; }
    .actions { display: flex; align-items: center; gap: 8px; margin-bottom: 25px; }
    button { padding: 8px 20px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; transition: all 0.2s; }
    
    #formCrud { display: grid; grid-template-columns: 200px 1fr 200px; gap: 30px; }
    #formCrud > div { display: flex; flex-direction: column; }
    label { font-weight: 600; color: #080808; margin-bottom: 6px; font-size: 14px; }
    input, select { padding: 8px 10px; border: 1px solid #c3b5f5; border-radius: 5px; background-color: #fdfcff; }
    input[readonly] { background-color: #f0f0f5; cursor: not-allowed; }

    /* Modal Styles */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 1000; }
    .modal-overlay.show { display: flex; }
    .modal-content { background: #fff; padding: 30px; border-radius: 5px; text-align: center; }
    .modal-buttons { display: flex; justify-content: center; gap: 15px; margin-top: 20px; }

    <?php if ($es_matriz == 'NO'): ?>
        button[value="nuevo"], button[value="guardar"], .btn-danger { display: none !important; }
    <?php endif; ?>
</style>
</head>
<body>
<div class="container">
    <h2>Gestión de Tipos de Productos</h2>
    <div class="actions">
        <button type="submit" form="formCrud" name="accion" value="nuevo" style="background-color: #0acf97; color: #fff;">+ Nuevo</button>
        <button type="submit" form="formCrud" name="accion" value="guardar" style="background-color: #e3eaef; color: #080808;">💾 Guardar</button>
        <button type="button" class="btn-danger" onclick="showDeleteModal()" style="background-color: #fa5c7c; color: #fff;">🗑️ Eliminar</button>
        <button type="submit" form="formCrud" name="accion" value="salir" style="background-color: #e3eaef; color: #080808;">➔ Salir</button>
    </div>

    <form method="POST" id="formCrud">
        <div>
            <label>Código:</label>
            <input type="text" name="codigo_tipo_productos" value="<?php echo $codigo_tipo_productos; ?>" <?php echo $readonly; ?> style="text-transform: uppercase;" required>
        </div>
        <div>
            <label>Nombre:</label>
            <input type="text" name="nombre_tipo_productos" value="<?php echo $nombre_tipo_productos; ?>" required>
        </div>
        <div>
            <label>Maneja Stock:</label>
            <select name="maneja_stock" required>
                <option value="SI" <?php if($maneja_stock=='SI') echo 'selected'; ?>>SI</option>
                <option value="NO" <?php if($maneja_stock=='NO') echo 'selected'; ?>>NO</option>
            </select>
        </div>
    </form>
</div>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Confirmación</h3>
        <p>¿Está seguro de eliminar este registro?</p>
        <div class="modal-buttons">
            <button type="button" onclick="closeDeleteModal()">Cancelar</button>
            <button type="submit" form="formCrud" name="accion" value="eliminar" style="background-color: #fa5c7c; color: #fff;">Eliminar</button>
        </div>
    </div>
</div>

<script>
    function showDeleteModal() { document.getElementById("deleteModal").classList.add("show"); }
    function closeDeleteModal() { document.getElementById("deleteModal").classList.remove("show"); }
</script>
</body>
</html>

?>