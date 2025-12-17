'<?php
// Validando si la sucursal_matriz es NO - Damian Diaz - 21-10-2025
		sc_lookup_field(dt_sucursal, "select sucursal_matriz from configuracion_sucursal where codigo ='[usr_sucursal]' and empresa = '[usr_empresa]'");
		$var_sucursal_matriz = {dt_sucursal[0]['sucursal_matriz']};

		if($var_sucursal_matriz == 'NO'){ // NO ES MATRIZ
			echo 'Condicion 1 MATRIZ NO'.'<br>';
			//sc_btn_display("new","off");
			// PHP se ejecuta primero en el servidor.
			$id_del_boton = 'sc_b_new_top'; // BOTON NUEVO - APAGAR

			// Imprimir el código CSS que se ejecutará en el navegador:
			echo '<style>';
			echo '#' . $id_del_boton . ' { display: none !important; }';
			echo '</style>';
				
		}else{  // SI ES MATRIZ
			echo 'Condicion 2 MATRIZ SI'.'<br>';
			$id_del_boton = 'sc_b_new_top'; // BOTON NUEVO - ENCENDER 
			// Imprimir el código JavaScript que se ejecutará en el navegador:
			echo '<script>';
			// Usamos DOMContentLoaded para asegurar que el elemento existe antes de intentar modificarlo
			echo 'document.addEventListener("DOMContentLoaded", function() {';
			echo '  var boton = document.getElementById("' . $id_del_boton . '");';
			echo '  if (boton) {';
			echo '    // Restablece el display. Si no estás seguro del valor original (block, inline, etc.),';
			echo '    // puedes usar una cadena vacía "" para que el navegador use el valor por defecto del elemento.';
			echo '    boton.style.display = "block"; // o usa "" (cadena vacía) para valor por defecto';
			echo '  }';
			echo '});';
			echo '</script>';
			
			// BOTON - SINCRONIZAR - APAGAR 
			// Define el ID del botón que deseas ocultar
			$id_boton_a_ocultar = 'sc_btn_sincronizar_top';

			// Imprime un bloque <style> que contiene la regla CSS para ocultar el elemento por su ID.
			// La propiedad 'display: none !important;' asegura que se aplique la regla.
			echo '<style>';
			echo '#' . $id_boton_a_ocultar . ' { display: none !important; }';
			echo '</style>';
			//sc_btn_display("new","on");
			//sc_btn_display("btn_sincronizar","on");
		}	

?>