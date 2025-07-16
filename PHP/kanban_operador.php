<?php
// Ejecutar un comando SQL para configurar el charset en UTF-8
sc_exec_sql("SET NAMES 'utf8'");

// Consultar los datos principales
sc_select(my_data, "
SELECT codigo,nombre,color,empresa FROM configuracion_tipo_prioridad 
    WHERE empresa = '[usr_empresa]'", "conn_example");

if ({my_data} === false) {
    echo "Error al acceder a la base de datos: " . {my_data_erro};
    exit;
} 

$array = array();

$row = 1; // Inicializar un contador para los IDs

while (!$my_data->EOF) {
    $var_codigo = $my_data->fields[0];
    $var_nombre	= $my_data->fields[1];
    $var_color 	= $my_data->fields[2];
	$var_empresa= $my_data->fields[3];


    // ID único basado en el código de departamento
    $id_unico = "elemento-".$var_codigo; // Corrección aquí

    $contenido = array(
        "id" => $var_codigo,
        "title" => utf8_decode($var_nombre),
        "class" => $id_unico, // Aplicar ID único como clase CSS
        "item" => array(),
        "id_unico" => trim($id_unico) // Agregar el ID único
    );
	//Asinando color al Departamento
	?>
	<style>
		.kanban-board-header{
		
		}
		.<?php echo $id_unico; ?>{
				background-color: <?php echo $var_color; ?>;  /* Color de fondo del encabezado */
		}
		
	</style>
	<?php
    // Consulta secundaria para los items
	
    sc_select(my_item, "SELECT 
    id_resumen, 
    concat(substr(REGEXP_REPLACE(CONCAT(
        UPPER(SUBSTRING(nombre_cliente, 1, 1)), 
        LOWER(SUBSTRING(nombre_cliente, 2))
    ), '[^\x20-\x7E]', ''),1,25) ,'...') AS nombre_cliente, status, tipo_prioridad, numero_perfil,empresa,cantidad_mensajes
FROM 
    ws_conversacion_resumen 
WHERE 
    empresa = '[usr_empresa]' AND tipo_prioridad = '$var_codigo' and usuario = '[usr_login]'
    /*AND status <> 3*/ ORDER BY nombre_cliente", "conn_helpdesk");
	
	/*
	sc_select(my_item, "SELECT 
    id_resumen, 
    nombre_cliente, status, tipo_prioridad, numero_perfil,empresa,cantidad_mensajes
FROM 
    ws_conversacion_resumen 
WHERE 
    empresa = '[usr_empresa]'AND tipo_prioridad = '$var_codigo' and usuario = '[usr_login]'
    AND status <> 3 ORDER BY nombre_cliente", "conn_helpdesk");
	*/
    if ({my_item} === false) {
        echo "Error al acceder a la base de datos: " . {my_item_erro};
        exit;
    } else {
        $items = array();
        while (!$my_item->EOF) {
            $id_resumen 		= $my_item->fields[0];
            $nombre_cliente 	= $my_item->fields[1];
			$var_status			= $my_item->fields[2];
			$var_tipo_priorodad = $my_item->fields[3];
			$numeroPerfil  		= $my_item->fields[4];
			$var_empresa  		= $my_item->fields[5];
			$var_cantidad  		= $my_item->fields[6];		
					
            $items[] = array(
                "id" => $id_resumen,
   			    "title" => '<span class="mensaje-cuadro">' . $var_cantidad . '</span>'. $nombre_cliente . 
				'<div class="caja-boton"><button class="boton-detalle" onclick="abrirDetalle
				(' . $id_resumen . ', \'' . $nombre_cliente . '\')">Detalle 
				</button>
				<button class="boton-cerrar" onclick="cerrar_chat(' . $id_resumen . ', \'' . $var_empresa . '\')">Cerrar
				</button></div>',
                "class" => "bgblue"
            );
				
            $my_item->MoveNext();
        }
        $contenido["item"] = $items;
        $my_item->Close();
    }

    $array[] = $contenido;
    $my_data->MoveNext();
    $row++;
}
$my_data->Close();
$json = json_encode($array, JSON_UNESCAPED_UNICODE);
/*
echo '<pre>';
var_dump($json);
echo '</pre>';
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kanban</title>
	<script src=" https://cdn.jsdelivr.net/npm/jkanban@1.3.1/dist/jkanban.min.js "></script>
	 <link href=" https://cdn.jsdelivr.net/npm/jkanban@1.3.1/dist/jkanban.min.css " rel="stylesheet"> 
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

	<!--  sc_include_lib("Jquery");
	sc_include_lib('jkanban', 'jkanban.min.css', 'css');
	sc_include_lib('jkanban', 'jkanban.min.js', 'js');  --> 

<?php
 	 estilos_tarjetas();
?>-
</head>
<body>
<div id="container" class="trello"></div>
	<script>
		var jsonData = <?php echo $json; ?>;
		var KanbanTest = new jKanban({
        element: "#container",
        gutter: "10px",
        widthBoard: "450px",
        itemHandleOptions:{
          enabled: true,
        },
        click: function(el) {
          console.log("Trigger on all items click!");
        },
        context: function(el, e) {
          console.log("Trigger on all items right-click!");
        },
		
        dropEl: function(el, target, source, sibling){
          let conversationId = el.getAttribute('data-eid');//Obtener el ID de la conversación
			let newDepartment = target.parentElement.getAttribute('data-id');//Nuevo departamento

			console.log("ID de la conversación: " + conversationId);
			console.log("Nuevo departamento: " + newDepartment);
		  
			// Realizar la solicitud POST con fetch
			fetch('../updatePrioridad/updatePrioridad.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: `id_conversacion=${conversationId}&codigo_departamento=${newDepartment}`
			})
			.then(response => response.json()) // Esperar respuesta en formato JSON
			.then(data => {
				// Manejar la respuesta del servidor
				console.log(data);
				if (data.status) {
					location.reload();
				} else {
					alert("Error: " + data.message);
				}
			})
			.catch(error => {
				console.error("Error en la solicitud:", error);
				alert("Hubo un error al actualizar la conversación.");
			});
			
			
		 console.log(el, target, source, sibling)
        },	
		buttonClick: function(el, boardId) {
          console.log(el);
          console.log('el id es'+boardId);
          // create a form to enter element
          var formItem = document.createElement("form");
          formItem.setAttribute("class", "itemform");

          KanbanTest.addForm(boardId, formItem);
          formItem.addEventListener("submit", function(e) {
            e.preventDefault();
            var text = e.target[0].value;
            KanbanTest.addElement(boardId, {
              title: text
            });
            formItem.parentNode.removeChild(formItem);
          });
          document.getElementById("CancelBtn").onclick = function() {
            formItem.parentNode.removeChild(formItem);
          };
        },
		boards: jsonData});
			
		function cerrarModal() {
			document.getElementById('modalDetalle').style.display = "none";
			document.getElementById('detalleFrame').src = ""; // Limpiar el src al cerrar
		}

		// Cerrar el modal al hacer clic fuera del contenido
		window.onclick = function(event) {
			var modal = document.getElementById('modalDetalle');
			if (event.target == modal) {
				cerrarModal();
			}
		}
		
				function abrirDetalle(idResumen, nombre_cliente, numeroPerfil) {
		    var url = '../chat_detalle/chat_detalle.php?par_id_resume=' + encodeURIComponent(idResumen) +
			'&par_nombre_cliente=' + encodeURIComponent(nombre_cliente) + '&par_perfil=' + encodeURIComponent(numeroPerfil);

		    // Crear el overlay (fondo oscuro)
		    var overlay = document.createElement('div');
		  overlay.style.cssText = "position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center;";

		    // Crear el iframe
		    var iframe = document.createElement('iframe');
			iframe.src = url;
			iframe.style.cssText = "width: 800px; height: 600px;";

		    // Crear el botón de cerrar
		    var cerrarButton = document.createElement('button');
		  	cerrarButton.textContent = "X";
		    cerrarButton.style.cssText = "position: absolute; top: 10px; right: 100px; color: red";
			
			// Agregar el evento mouseover para mostrar el mensaje
			cerrarButton.addEventListener('mouseover', function(event) {
			// Crea el mensaje tooltip
			var tooltip = document.createElement('span');
			tooltip.textContent = "Cerrar Ventana"; // El mensaje que quieres mostrar
			tooltip.style.cssText = `
			position: absolute; 
			top: 25px; /* Ajusta la posición vertical del tooltip */
			left: 50%; 
			transform: translateX(-50%); /* Centrar el tooltip */
			background-color: black; 
			color: white; 
			padding: 5px; 
			border-radius: 5px; 
			font-size: 12px;
			white-space: nowrap;/*Evitar que el tooltip se divida en varias líneas */
			`;

    		// Agrega el tooltip al botón
           cerrarButton.appendChild(tooltip);
			// Función para ocultar el tooltip cuando el mouse sale del botón
           cerrarButton.addEventListener('mouseout', function hideTooltip() {
           cerrarButton.removeChild(tooltip);
           cerrarButton.removeEventListener('mouseout', hideTooltip);//Elimina el listener para que no se acumule
				});
			});

		  cerrarButton.onclick = function() {
		  overlay.remove();
		  };

		  // Agregar el iframe y el botón al overlay
		  overlay.appendChild(iframe);
		  overlay.appendChild(cerrarButton);

		  // Agregar el overlay al body
		  document.body.appendChild(overlay);
		}	
		
		
		// Funcion para abrir el enlace del detalle 
	/*	function abrirDetalle(idResumen,nombre_cliente,numeroPerfil) {
			var url = '../chat_detalle/chat_detalle.php?par_id_resumen=' + encodeURIComponent(idResumen) +
			'&par_nombre_cliente=' + encodeURIComponent(nombre_cliente)+'&par_perfil=' + encodeURIComponent(numeroPerfil);
			console.log("URL Generada:", url);
			window.open(url, '_blank'); //_parent
		}*/

		// Funcion para cambiar el status
		function cerrar_chat(idResumen,var_empresa) {
		    var url = '../mis_chats_cierre_kanban/mis_chats_cierre_kanban.php?id_resumen=' + 		encodeURIComponent(idResumen) +
              '&usr_empresa=' + encodeURIComponent(var_empresa);

		   // Crear el overlay (fondo oscuro)
		    var overlay = document.createElement('div');
		  overlay.style.cssText = "position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center;";

		    // Crear el iframe
		    var iframe = document.createElement('iframe');
			iframe.src = url;
			iframe.style.cssText = "width: 800px; height: 600px;";

		    // Crear el botón de cerrar
		    var cerrarButton = document.createElement('button');
		  	cerrarButton.textContent = "X";
		    cerrarButton.style.cssText = "position: absolute; top: 10px; right: 100px; color: red";
			
			// Agregar el evento mouseover para mostrar el mensaje
			cerrarButton.addEventListener('mouseover', function(event) {
			// Crea el mensaje tooltip
			var tooltip = document.createElement('span');
			tooltip.textContent = "Cerrar Ventana"; // El mensaje que quieres mostrar
			tooltip.style.cssText = `
			position: absolute; 
			top: 25px; // Ajusta la posición vertical del tooltip 
			left: 50%; 
			transform: translateX(-50%); // Centrar el tooltip 
			background-color: black; 
			color: white; 
			padding: 5px; 
			border-radius: 5px; 
			font-size: 12px;
			white-space: nowrap;//Evitar que el tooltip se divida en varias líneas 
			`;

    		// Agrega el tooltip al botón
           cerrarButton.appendChild(tooltip);
			// Función para ocultar el tooltip cuando el mouse sale del botón
           cerrarButton.addEventListener('mouseout', function hideTooltip() {
           cerrarButton.removeChild(tooltip);
           cerrarButton.removeEventListener('mouseout', hideTooltip);//Elimina el listener para que no se acumule
				});
			});

		  cerrarButton.onclick = function() {
		  overlay.remove();
		  };

		  // Agregar el iframe y el botón al overlay
		  overlay.appendChild(iframe);
		  overlay.appendChild(cerrarButton);

		  // Agregar el overlay al body
		  document.body.appendChild(overlay);
		  // window.open(url, '_blank'); //_blank	
		}
		
		
		
		
</script>
</body>
</html>
<?php

