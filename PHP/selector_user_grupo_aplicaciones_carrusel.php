// =========================================================================
// Usuario
// =========================================================================
$user_login = isset([par_login]) ? [par_login] : '';
$login_esc = addslashes($user_login);

// =========================================================================
// Guardar permisos
// =========================================================================
if (isset($_POST['user_to_save']) && $_POST['user_to_save'] != '') {

    $login_to_save = trim($_POST['user_to_save']);
    $login_esc = addslashes($login_to_save);

    $selected_groups = isset($_POST['groups']) ? $_POST['groups'] : [];
    $selected_groups = array_unique(array_map('intval', $selected_groups));

    if (empty($selected_groups)) {

        sc_exec_sql("DELETE FROM seguridad_users_groups WHERE login = '$login_esc'");

    } else {

        $ids_list = implode(',', $selected_groups);

        sc_exec_sql("DELETE FROM seguridad_users_groups
                     WHERE login = '$login_esc'
                     AND group_id NOT IN ($ids_list)");

        foreach ($selected_groups as $group_id) {
            sc_exec_sql("INSERT IGNORE INTO seguridad_users_groups (login, group_id)
                         VALUES ('$login_esc', $group_id)");
        }
    }

    sc_alert("Permisos guardados correctamente para el usuario: $login_to_save");
}

// =========================================================================
// Permisos actuales
// =========================================================================
$assigned_groups = [];
sc_lookup(rs_assigned, "SELECT group_id FROM seguridad_users_groups WHERE login = '$login_esc'");
if (is_array({rs_assigned})) {
    foreach ({rs_assigned} as $row) {
        $assigned_groups[] = $row[0];
    }
}

// =========================================================================
// Módulos
// =========================================================================
$modules = [];
sc_lookup(rs_modules, "SELECT id_modulo_maestro, descripcion, icono
                        FROM seguridad_grupos_maestros
                        ORDER BY descripcion");
if (is_array({rs_modules})) {
    foreach ({rs_modules} as $row) {
		$modules[] = ['id' => $row[0], 'name' => $row[1], 'icono' => $row[2]]; // <-- AQUÍ FALTA EL ÍCONO
    }
}

// =========================================================================
// HTML
// =========================================================================
echo '
<style>
	body { font-family: Arial; 
	background:#f0f2f5;
	color: b+lack}
.container { max-width:1200px; margin:auto; padding:10px; color:#aab8c5; }

/* ---------------------------------------------------------------------- */
/* ESTILOS CARRUSEL (MÓDULOS) */
/* ---------------------------------------------------------------------- */

/* Nuevo contenedor para posicionar botones sobre el carrusel */
.carousel-wrapper {
    position: relative;
    margin: 15px 0;
}

.modules-grid {
    display: flex;
    overflow-x: auto; /* Permite el desplazamiento con scroll */
    gap: 15px;
    padding: 0 50px 15px 50px; /* Ajuste: Incrementado el padding horizontal para los botones */
    white-space: nowrap;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
/* Ocultar la barra de desplazamiento para navegadores basados en WebKit */
.modules-grid::-webkit-scrollbar {
    display: none;
}

.module-card {
    flex: 0 0 auto;
    width: 100px; 
    height: 70px; 
    color: black;
	background:#f0f2f5;
    padding:15px;
    border-radius:10px;
    text-align:center;
    cursor:pointer;
    box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.5); 
    
    /* Zoom y transición para el hover */
    transition: transform 0.3s ease-in-out, background 0.3s, box-shadow 0.3s;
}

.module-card:hover, .module-card.active {
    background:#1C48E8;
    color:#fff;
    /* Zoom en hover */
    transform: scale(1.05); 
    box-shadow: 0 0 20px rgba(28, 72, 232, 0.7); 
}


/* ---------------------------------------------------------------------- */
/* ESTILOS BOTONES DE NAVEGACIÓN */
/* ---------------------------------------------------------------------- */

.nav-btn {
    position: absolute;
    top: 45%;
    transform: translateY(-50%);
	background: rgba(0, 0, 0, 0.4); /* ¡MODIFICADO a 40% de opacidad! */
    color: white;
    border: none;
    cursor: pointer;
    padding: 40px 15px;
    z-index: 10;
    font-size: 1.5em;
    line-height: 1;
    border-radius: 5px;
    transition: background 0.2s;
}

.nav-btn:hover {
    background: #1C48E8;
}

.left-btn {
    left: 0;
}

.right-btn {
    right: 0;
}

/* ---------------------------------------------------------------------- */
/* ESTILOS BOTÓN GUARDAR */
/* ---------------------------------------------------------------------- */
input[type="submit"] {
    /* Padding controla el tamaño interno del botón */
    padding: 10px 25px; /* Altura (10px arriba/abajo) y Ancho (25px izq/der) */
    
    /* Fuente y color */
    font-size: 1.1em;
    color: black;
    
    /* Fondo y borde */
    background: #e6e9f0; /* El color azul que ya usamos */
	border: none;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.5); 

    /* Margen superior para separarlo de la lista de permisos */
    margin-top: 30px; 
    
    /* Zoom y transición para el hover */
    transition: transform 0.3s ease-in-out, background 0.3s, box-shadow 0.3s;
}

input[type="submit"]:hover {
    background: #0030c0; /* Un azul ligeramente más oscuro */
	color: white;
}


/* ---------------------------------------------------------------------- */
/* ESTILOS PERMISOS */
/* ---------------------------------------------------------------------- */
.permissions-list {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
    margin-top:15px;
}

</style>

<div class="container">
<!--<h3>Asignación de Permisos</h3>-->
';

if ($user_login != '') {

    echo '<form method="post">
          <input type="hidden" name="user_to_save" value="'.htmlspecialchars($user_login, ENT_QUOTES).'">

          <label>Seleccione un módulo (Deslice Horizontalmente):</label>
          <br>
          <div class="carousel-wrapper">
              <button type="button" class="nav-btn left-btn" onclick="scrollCarousel(-1)">&#60;</button>
              <div class="modules-grid" id="modulesGrid">'; // ID crucial para JS

    foreach ($modules as $m) {
        // Lógica de salto de línea condicional y formato de título
        $module_name_display 	= ucfirst(strtolower($m['name']));
        $first_space_pos 		= strpos($module_name_display, ' ');
		$icono 					= $m['icono']; // Aquí se obtiene el icono (¡Correcto!)
        if ($first_space_pos !== false) {
            $module_name_display = substr_replace($module_name_display, '<br>', $first_space_pos, 1);
        }
		
		// LÍNEA MODIFICADA: Agrega un <br> después de la etiqueta <img>.
		echo '<div class="module-card" onclick="selectModule(\'mod_'.$m['id'].'\', this)">
			<img src="'.$icono.'" style="width:30px;height:30px; margin-bottom: 5px;" alt="'.$m['name'].'">
			<br>
			'.$module_name_display.'</div>';
    }

    echo '    </div>
              <button type="button" class="nav-btn right-btn" onclick="scrollCarousel(1)">&#62;</button>
          </div>'; // Fin carousel-wrapper

    foreach ($modules as $m) {

        // Las listas de permisos se ocultan/muestran
        echo '<div class="permissions-list" id="mod_'.$m['id'].'" style="display:none;">';

        sc_lookup(rs_groups, "SELECT group_id, description, descripcion_detallada
                              FROM seguridad_groups
                              WHERE modulo = '".$m['id']."'
                              ORDER BY description");

        if (is_array({rs_groups})) {
            foreach ({rs_groups} as $row) {
                $checked = in_array($row[0], $assigned_groups) ? 'checked' : '';
				
				echo '<label title="'.htmlspecialchars($row[2], ENT_QUOTES).'" style="color: black;"> 
				<input type="checkbox" name="groups[]" value="'.$row[0].'" '.$checked.'>
				  '.$row[1].'
			  </label>';
            }
        }

        echo '</div>';
    }

    echo '<input type="submit" value="Guardar Permisos"></form>';
}

echo '</div>

<script>
// La función scrollCarousel está aquí, fuera del bloque style.

// Función para desplazar el carrusel horizontalmente
function scrollCarousel(direction) {
    const carousel = document.getElementById(\'modulesGrid\');
    
    // El valor de desplazamiento es ajustado: (100px ancho tarjeta + 15px gap) * 1.1 (margen de seguridad)
    // const scrollAmount = 126.5; 
	const scrollAmount = 850; // <-- LÍNEA A MODIFICAR

    // Mueve el desplazamiento horizontal
    carousel.scrollLeft += direction * scrollAmount;
}


function selectModule(id, el) {
    // Oculta todas las listas de permisos
    document.querySelectorAll(".permissions-list").forEach(d => d.style.display="none");
    
    // Quita la clase activa de todas las tarjetas
    document.querySelectorAll(".module-card").forEach(c => c.classList.remove("active"));
    
    // Muestra solo la lista de permisos seleccionada
    document.getElementById(id).style.display="grid";
    
    // Marca la tarjeta de módulo activa
    el.classList.add("active");
}

// Opcional: Para asegurar que al cargar la página se marque la primera tarjeta
document.addEventListener("DOMContentLoaded", function() {
    const firstModuleCard = document.querySelector(".module-card");
    if (firstModuleCard) {
        // Ejecuta la función con la primera tarjeta para inicializar la vista
        const firstModuleId = firstModuleCard.getAttribute("onclick").match(/selectModule\(\'(.*?)\'/)[1];
        selectModule(firstModuleId, firstModuleCard);
    }
});
</script>';