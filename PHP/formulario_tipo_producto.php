<?php
// ====================================================
// DECLARAR VARIABLES GLOBALES DE SCRIPTCASE
// ====================================================
sc_apl_status('Gestión de Tipos de Productos', 'on');
sc_set_global([usr_empresa]);
sc_set_global([usr_login]);
sc_set_global([par_codigo_tipo_productos]);

// Asignacion de valores a las variables
$fecha   = date('Y-m-d H:i:s');
$usuario = [usr_login];
$empresa = [usr_empresa];
$ip_estacion = $_SERVER['REMOTE_ADDR'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Gestión de Tipos de Productos</title>

<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background-color: #fafafa;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 900px;
        background: #ffffff;
        margin: 60px auto;
        padding: 40px 50px;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        border: 1px solid #e6e6f0;
    }

    h2 {
        text-align: left;
        color: #3d3d66;
        font-size: 22px;
        margin-bottom: 25px;
        border-bottom: 2px solid #d7d7f7;
        padding-bottom: 10px;
    }

    /* === BOTONES SUPERIORES === */
    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
    }

    button {
        padding: 8px 20px;
        border: 1px solid #5a32a3;
        border-radius: 20px;
        background: #fff;
        color: #5a32a3;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    button:hover {
        background: #5a32a3;
        color: #fff;
        box-shadow: 0 2px 6px rgba(90, 50, 163, 0.3);
    }

    /* === FORMULARIO GRID === */
    form {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 25px;
        margin-top: 20px;
    }

    form > div {
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: 600;
        color: #3d3d66;
        margin-bottom: 6px;
        font-size: 14px;
    }

    /* === CAMPOS DE TEXTO Y SELECT === */
    input[type=text], select {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #c3b5f5;
        border-radius: 8px;
        font-size: 14px;
        background-color: #fdfcff;
        color: #333;
        transition: all 0.2s ease-in-out;
    }

    input:focus, select:focus {
        border-color: #5a32a3;
        box-shadow: 0 0 4px rgba(90, 50, 163, 0.4);
        outline: none;
        background-color: #fff;
    }

    input[readonly] {
        background-color: #f0f0f5;
        cursor: not-allowed;
        color: #666;
    }

    /* === BOTONES ESPECÍFICOS === */
    .btn-primary { border-color: #5a32a3; color: #5a32a3; }
    .btn-danger { border-color: #b53c3c; color: #b53c3c; }
    .btn-exit { border-color: #777; color: #777; }

    .btn-primary:hover { background: #5a32a3; color: #fff; }
    .btn-danger:hover { background: #b53c3c; color: #fff; }
    .btn-exit:hover { background: #777; color: #fff; }

</style>
</head>

<body>
<div class="container">
    <h2>Gestión de Tipos de Productos</h2>

<?php
// =============================================
// VARIABLES DE CONTROL
// =============================================
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$id_tipo_productos = isset($_POST['id_tipo_productos']) ? $_POST['id_tipo_productos'] : '';

// Variables globales Scriptcase
$empresa   = [usr_empresa];
$usuario   = [usr_login];
$codigoRef = [par_codigo_tipo_productos];

// =============================================
// CARGAR DATOS SI [par_codigo_tipo_productos] NO ESTÁ VACÍO
// =============================================
$codigo_tipo_productos = '';
$nombre_tipo_productos = '';
$maneja_stock = '';
$readonly = '';

if (!empty($codigoRef)) {
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
// ACCIONES CRUD
// =============================================
// NUEVO
if ($accion == 'nuevo') {
    // Limpiar variables para que el formulario quede vacío
    $codigo_tipo_productos = '';
    $nombre_tipo_productos = '';
    $maneja_stock = 'NO';
    $readonly = '';
    
    // También limpiar la variable de referencia global
    $codigoRef = '';
}
// GUARDAR
if ($accion == 'guardar') {
    $codigo   = sc_sql_injection($_POST['codigo_tipo_productos']);
    $nombre   = sc_sql_injection($_POST['nombre_tipo_productos']);
    $stock    = sc_sql_injection($_POST['maneja_stock']);
    $fecha = date('Y-m-d H:i:s');
  //  $ip_estacion = $_SERVER['REMOTE_ADDR'];

    if (empty($codigoRef)) {
        // INSERT
        $insert_sql = "
            INSERT INTO inventario_tipo_productos
            (codigo_tipo_productos, nombre_tipo_productos, maneja_stock, usuario, fecha, ip_estacion, empresa)
            VALUES
            ('$codigo', '$nombre', '$stock', '$usuario', '$fecha', '$ip_estacion', '$empresa')
        ";
        sc_exec_sql($insert_sql, 'conn_example');
        sc_alert('✅ Registro insertado correctamente');
    } else {
        // UPDATE
        $update_sql = "
            UPDATE inventario_tipo_productos SET
                nombre_tipo_productos = $nombre,
                maneja_stock = $stock,
                usuario = '$usuario',
                fecha = '$fecha',
                ip_estacion = '$ip_estacion',
                empresa = '$empresa'
            WHERE codigo_tipo_productos = '$codigoRef'
			AND empresa = '$empresa'
        ";
        sc_exec_sql($update_sql, 'conn_example');
       sc_alert('📝 Registro Actualizado correctamente');
    }

    sc_redir($_SERVER['PHP_SELF']);
}

// ELIMINAR
if ($accion == 'eliminar' && (!empty($codigoRef) || !empty($_POST['codigo_tipo_productos']))) {
    $codigoEliminar = !empty($codigoRef) ? $codigoRef : $_POST['codigo_tipo_productos'];
	// Validando si el Tipo de Producto posee dependencia
	$sql_inventario = "
		select codigo_productos, nombre_productos,codigo_tiposerv_productos,empresa,sucursal 
		from inventario_productos where codigo_tiposerv_productos = '$codigoEliminar'
		AND empresa = '$empresa'"; 	
	sc_lookup(check_dependencia, $sql_inventario, 'conn_example');
	// Verificando que NO sea Data Predeterminada
	 $sql_data_predeterminada = "
		select codigo_productos, nombre_productos,codigo_tiposerv_productos,empresa,sucursal 
		from inventario_productos where codigo_tiposerv_productos = '$codigoEliminar'
		AND ip_estacion = 'DEFECTO'
		AND empresa = '$empresa'"; 	
	sc_lookup(check_data_predeterminada, $sql_data_predeterminada, 'conn_example');
	
	// Si existen productos relacionados, lanzar alerta y detener
    if (isset({check_dependencia[0][0]})) {
        sc_alert("❌ No se puede eliminar este Tipo de Producto porque tiene productos relacionados.");
	}elseif(isset({check_data_predeterminada[0][0]})){
		sc_alert("❌ No se puede eliminar este Tipo de Producto porque es Data Predeterminada del Sistema");
    } else {
		$delete_sql = "
			DELETE FROM inventario_tipo_productos 
			WHERE codigo_tipo_productos = '$codigoEliminar' AND empresa = '$empresa'
		";
    sc_exec_sql($delete_sql, 'conn_example');
    sc_alert('🗑️ Registro eliminado correctamente');
  //  sc_redir($_SERVER['PHP_SELF']);
	}
}

// SALIR
if ($accion == 'salir') {
    sc_redir('grid_inventario_tipo_productos'); // Cambia por tu app destino
}
?>

<!-- BOTONES CRUD ARRIBA -->
<div class="actions">
	<button type="submit" form="formCrud" name="accion" value="nuevo" class="btn-primary">✨ Nuevo</button>
    <button type="submit" form="formCrud" name="accion" value="guardar" class="btn-primary">💾 Guardar</button>
    <button type="submit" form="formCrud" name="accion" value="eliminar" class="btn-danger" onclick="return confirm('¿Eliminar este registro?')">🗑️ Eliminar</button>
    <button type="submit" form="formCrud" name="accion" value="salir" class="btn-exit">🚪 Salir</button>
</div>

<form method="POST" id="formCrud">
    <div>
        <label>Código:</label>
        <input type="text" name="codigo_tipo_productos" id="codigo_tipo_productos" 
               value="<?php echo $codigo_tipo_productos; ?>" <?php echo $readonly; ?> required>
    </div>

    <div>
        <label>Nombre:</label>
        <input type="text" name="nombre_tipo_productos" id="nombre_tipo_productos"
               value="<?php echo $nombre_tipo_productos; ?>" required>
    </div>

    <div>
        <label>Maneja Stock:</label>
        <select name="maneja_stock" id="maneja_stock" required>
            <option value="">Seleccione...</option>
            <option value="SI" <?php if($maneja_stock=='SI') echo 'selected'; ?>>SI</option>
            <option value="NO" <?php if($maneja_stock=='NO') echo 'selected'; ?>>NO</option>
        </select>
    </div>
</form>
</div>
</body>
</html>
<?php

?>