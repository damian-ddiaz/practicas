<?php
    //  onApplicationInit	

    actualiza_menu(); // metodo para corregir menu en developer y admin de usuarios

    //chat_asistente
    echo '
    <style>
    #chat_iframe {
        position: fixed;
        bottom: 95px;
        right: 25px;
        width: 800px;
        height: 600px;
        border: none;
        border-radius: 12px;
        display: none;
        z-index: 9999;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        background: white;
    }

    #chat_button {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 10000;
        background: #727cf5;
        color: white;
        border: none;
        border-radius: 50%;
        width: 70px;
        height: 70px;
        font-size: 22px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

    #chat_button:hover {
        background: #833471;
    }

    /* Overlay para detectar clics fuera del chat */
    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
        z-index: 9998;
    }
    </style>

    <!-- Botón flotante -->
    <button id="chat_button" onclick="toggleChat()" title="ICARO_Bot">
    <video src="https://storage.googleapis.com/icarosoft-data/fileupload/d62b606f48b88e7819507986847c1e34.webm" 
            class="chat-logo"
            loop autoplay muted disablePictureInPicture></video>
    </button>

    <!-- Iframe del chat asistente -->
    <iframe id="chat_iframe" src="../chat_asistente/index.php"></iframe>

    <!-- Overlay invisible para capturar clics fuera del chat -->
    <div id="overlay" onclick="toggleChat()"></div>

    <script>
    function toggleChat() {
        const iframe = document.getElementById("chat_iframe");
        const overlay = document.getElementById("overlay");
        const video = document.querySelector("#chat_button video");
        const isOpen = iframe.style.display === "block";

        iframe.style.display = isOpen ? "none" : "block";
        overlay.style.display = isOpen ? "none" : "block";

        // Si el iframe se está abriendo, envíale un mensaje para que enfoque el input
        if (!isOpen) {
        // Espera un momento para asegurar que el iframe sea renderizado y esté listo
        // before attempting to send the message. 50-100ms usually sufficient.
        setTimeout(() => {
            if (iframe.contentWindow) {

            iframe.contentWindow.postMessage("focusChatInput", "*"); 
            }
        }, 100); 
        }

        // Asegurarse de que el video siga reproduciéndose
        if (video) {
        video.play().catch(() => {
            console.warn("No se pudo reanudar el video automáticamente.");
        });
        }
    }

    // Cerrar con tecla ESC
    document.addEventListener("keydown", function(event) {
        const iframe = document.getElementById("chat_iframe");
        const video = document.querySelector("#chat_button video");

        if (event.key === "Escape" && iframe.style.display === "block") {
        iframe.style.display = "none";
        document.getElementById("overlay").style.display = "none";

        if (video) {
            video.play().catch(() => {
            console.warn("No se pudo reanudar el video después de cerrar con Escape.");
            });
        }
        }
    });

    // Opcional: Reproducir el video al cargar la página
    window.addEventListener("load", () => {
        const video = document.querySelector("#chat_button video");
        if (video) {
        video.play().catch(() => {
            console.warn("El video no se reprodujo automáticamente. Requiere interacción del usuario.");
        });
        }
    });
    </script>
    ';

    //  onExecute	
    if({sc_script_name} == 'seguridadv3Login'){ // Control para Autenticar
    if(isset($_COOKIE['usr_data'])){
        unset($_COOKIE['usr_data']);
    }
    }
    if({sc_script_name} == 'seguridadv2_Login'){
        if(isset($_COOKIE['usr_data'])){
            unset($_COOKIE['usr_data']);
        }
    }
    if({sc_script_name} == 'seguridadv2_Login'){
        if(isset($_COOKIE['usr_data'])){
            unset($_COOKIE['usr_data']);
        }
    }
    ?>
    <style>
        @media (max-width: 500px) {
            .scMenuTHeader {
                display: none;
            }
            .sc-layer-0, .sc-layer-1{
                display: none;
            }
            #idDivMenu{
                padding-top:15px !important;
            }
        }
    </style>
    <?php

    // onLoad
    sc_exec_sql ("SET collation_connection = 'utf8mb4_general_ci';");

    // version actual de aplicativo
    [version_actual] = '2.3.6';


    //librerio para analisis de google analitycs
    //sc_include_library("prj", "google_analitycs", "google_analitycs.html", true, true);

    // actualiza_menu(); // metodo para corregir menu en developer y admin de usuarios
    //librerio para analisis de google analitycs

    sc_menu_force_mobile(true);
    /*Comienza funcion datos empresa todos los datos basicos de la empresa como variables globales*/	 
    selecciona_datos_empresa();
    /*termina funcion pais empresa*/	


    // buscara el nombreAND (sucursal = 'vidanet') y datos de la sucursal seleccionar variables globales idalwin 03/05/2023
    selecciona_datos_sucursal();
    // final de datos sucursal

    //llama la funcion para datos fijos de la marca icarosoft.com
    datos_icarosoft();
    // finaliza la funcion de llamada website y wiki

    /*function empresa grupo ventas desde la regla de negocio*/
    selecciona_grupo_ventas();

    /*finaliza funcion empresa grupo ventas*/

    traer_todos_los_correlativos();

    //Willfredo garcia 11/06/2025
    /*tasa_cambio_automatica_bcv();*///actualiza la tasa de cambio
    //tasa_dolar_today(); //tasa dolar today

    /*Funcion para traer todas las reglas de negocio y publicar variables globales con el mismo nombre */
    traer_todas_las_reglas_de_negocios();

    //traer_todas_las_reglas_de_negocios_V2();

    // obtiene la ip del servicdor utilizado idalwin salas 03/05/2023
    $usr_server = $_SERVER['SERVER_ADDR'];

    sc_set_global($usr_server);


    // optiene la ip del cliente idalwin 03/05/2023
    $usr_remote = $_SERVER['REMOTE_ADDR'];

    sc_set_global($usr_remote);


    //Llama Registros Predefinidos
    registros_predefinidos(); // libreria de registro predefinidos 


    /* Por el momento */
    //corregir_status_fact_transacciones();
    //corregir_status_abonos_transacciones();
    corregir_campo_fact_fiscal();
    /* Por el momento */

    if(empty([usr_empresa])){
        
        /*echo "<script>
            alert('Su sesion ha caducado');
            window.location.href = '../logout_session';
            </script>";
        exit();
        */
        
        header("Location: ../logout_session");
        //sc_redir (logout_session);
    }
    if(empty([usr_login])){
        
        /*echo "<script>
            alert('Su sesion ha caducado');
            window.location.href = '../logout_session';
            </script>";
        exit();
        */
        
        header("Location: ../logout_session");
        //sc_redir (logout_session);
    }


    if(empty([usr_sucursal])){
        
        /*echo "<script>
            alert('Su sesion ha caducado');
            window.location.href = '../logout_session';
            </script>";
        exit();
        */
        
        header("Location: ../logout_session");
        //sc_redir (logout_session);
    }

    interceptar();

    actualiza_menu(); // metodo para corregir menu en developer y admin de usuarios
    //------------------------------actualizacion idalwin salas 11/8/2024----------------------------------
    // funcion de decodificar usr_token  es necesario enviar login, empresa y libreria icarosoft-jwt.php publica

    buscardatoslogin([usr_login]);
    buscardatosempresa([usr_empresa]);
    buscardatossucursal([usr_sucursal]);


    # VALIDACION DE LA APLICACION CON TOKEN   ---------------------------------------------------------
    #   variables usuario,empresa, token, redir(true o false), log (true o false)
            sc_include_library("sys", "jwt", "JWT.php", true, true);
            sc_include_library("sys", "jwt", "Key.php", true, true);

            $jwta = encodeToken([usr_login]); // buscar token de este usuario
            sc_set_global($jwta);  // colocacion global de la variable

            $expiry_jwta = 1; // 1 día
        // $expiry_jwtr = 7; // 7 días
            $dominio_cookie = ".icarosoft.com";

            setcookie("jwta", $jwta, time() + (86400 * $expiry_jwta), '/', $dominio_cookie);
        //  setcookie("jwtr", $jwtr, time() + (86400 * $expiry_jwtr), '/', $dominio_cookie);



    # VALIDACION DE LA APLICACION CON TOKEN  - -------------------------------------------------------

// MetodoS PHP
// 1.-  actualiza_menu
sc_reset_menu_delete();
sc_reset_menu_disable();

//    CONFIGURACION PARA USUARIOS DE RANGO O NIVEL MEDIO 
if ([usr_nivel] <= 5){
	sc_menu_delete(item_473);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_160);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_529);   // usuarios, usuarios por sucursal , listado de permisos
    sc_menu_delete(item_590);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_217);  // empresa y sucursal
	sc_menu_delete(item_175);  // empresa y sucursal
    sc_menu_delete(item_608);
    sc_menu_delete(item_609);
    sc_menu_delete(item_552);
    sc_menu_delete(item_539);
    sc_menu_delete(item_540);
    sc_menu_delete(item_541);
	// agregado 02/08/2024   ------------------------idalwin salas -------------------------
		sc_menu_delete(item_156);
		sc_menu_delete(item_225);
		sc_menu_delete(item_618);
		sc_menu_delete(item_593);
		sc_menu_delete(item_639);
		sc_menu_delete(item_350);
		sc_menu_delete(item_481);
		sc_menu_delete(item_315);
		sc_menu_delete(item_404);
		sc_menu_delete(item_614);
		sc_menu_delete(item_395);
		sc_menu_delete(item_313);
		sc_menu_delete(item_543);
		sc_menu_delete(item_362);
		sc_menu_delete(item_380);
		sc_menu_delete(item_190);
		sc_menu_delete(item_265);
		sc_menu_delete(item_193);
		sc_menu_delete(item_188);
		sc_menu_delete(item_194);
		sc_menu_delete(item_179);
		sc_menu_delete(item_178);
		sc_menu_delete(item_266);
		sc_menu_delete(item_159);
		

} 


if ([usr_nivel] <= 6){
 //   sc_menu_delete(item_607);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_475);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_95);   // usuarios, usuarios por sucursal , listado de permisos
    sc_menu_delete(item_609);
    /*sc_menu_delete(item_682);*/
  
   
} 
//---------------------APLICACIONES A OCULTAR DE DESARROLLO------------------------//

if ([usr_nivel] <= 8){
    sc_menu_delete(item_647);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_475);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_95);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_619);
	sc_menu_delete(item_645);
	sc_menu_delete(item_97);
	sc_menu_delete(item_591);
	sc_menu_delete(item_596);
		
		// agregado 02/08/2024   ------------------------idalwin salas -------------------------
	    sc_menu_delete(item_599);
		sc_menu_delete(item_463);
		sc_menu_delete(item_559);
		sc_menu_delete(item_555);
		sc_menu_delete(item_548);
		sc_menu_delete(item_213);
		sc_menu_delete(item_527);
		sc_menu_delete(item_475);
		sc_menu_delete(item_640);
		sc_menu_delete(item_641);
		sc_menu_delete(item_642);
		sc_menu_delete(item_647);
	    sc_menu_delete(item_648);	
		sc_menu_delete(item_582);
} 

if ([usr_nivel] < 8){ //ddiaz 21/10/2024 Finanzas - Saldos  y Operaciones Bancarias
	sc_menu_delete(item_653); 
}

if ([usr_nivel] < 6){ //ddiaz 21/10/2024 Finazas - Mis Saldos
	sc_menu_delete(item_658); 
}



sc_menu_delete(item_397);

/*if ([usr_nivel] = 8){

    sc_menu_delete(item_607);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_475);   // usuarios, usuarios por sucursal , listado de permisos
	sc_menu_delete(item_95);   // usuarios, usuarios por sucursal , listado de permisos


}*/
	
	
  // funcion agregada 12-12-2023 idalwin salas


//ocultar beta pre registro
//sc_menu_delete(item_604); 
//ocultar la ia
//sc_menu_delete(item_611);
//ocultar licencia 
sc_menu_delete(item_609);


sc_menu_delete(item_317);

// 2.-  actualizar_tasa_cambio

// funcion de tasa de cambio automatica si el valor es si actualizar tabla en la regla de negocio tasa de cambio

function tasa_cambio_automatica_bcv(){
	
	if ([tasa_de_cambio_automatica] == 'SI'){

		$check_sql = "select TasaBcvXFecha(CURDATE())";
		sc_lookup(rs, $check_sql);

		if (isset({rs[0][0]})){
			$tasaCambio = {rs[0][0]};
			// comienza la toma de decicion depende del valor global si la actualizacion esta automatica
			$udp_tasa_de_cambio = "
			UPDATE configuracion_reglas_de_negocio
			SET valor = '$tasaCambio',
				  fecha = NOW(),
				  usuario = 'funcion del sistema'
			WHERE 
			   (nombre_variable = 'tasa_de_cambio') AND
			   (empresa = '[usr_empresa]')";
			sc_exec_sql($udp_tasa_de_cambio);
				
			$udp_tasa_de_cambio_bcv = "
			UPDATE configuracion_reglas_de_negocio
			SET valor = '$tasaCambio',
				fecha = NOW(),
				usuario = 'funcion del sistema'
			WHERE 
				(nombre_variable = 'tasa_de_cambio_bcv') AND
				(empresa = '[usr_empresa]')";
			sc_exec_sql($udp_tasa_de_cambio_bcv);
		}
	}
}


// 3.-  interceptar
$dominio = $_SERVER['HTTP_HOST'];


if ($dominio == 'developer.icarosoft.com:8092' or $dominio == 'https://demo.icarosoft.com' or $dominio == 'https://beta.icarosoft.com'){

} else {




$usr_empresa = [usr_empresa];
$usr_login = [usr_login];
$sqlverificar = "SELECT estado, user_login FROM webservices.empresas_clientes_icarosoft WHERE rif = '" . $usr_empresa . "'";
sc_lookup(ds, $sqlverificar, 'conn_example');

if (isset({ds[0][0]}) && {ds[0][0]} == 'Inactivo') {
    // Se encontró un registro en la base de datos con el rif proporcionado y el estado es "Inactivo"

$sqlverificar2 = "SELECT codigo_nivel FROM webservices.seguridad_users WHERE login = '" . $usr_login . "'";
sc_lookup(ds2, $sqlverificar2, 'conn_example');

$sqlverificar3 = "SELECT url_portal_administrativo FROM webservices.configuracion_empresa WHERE codigo = '" . $usr_empresa . "'";
sc_lookup(ds3, $sqlverificar3, 'conn_example');

$url_redir = {ds3[0][0]};

    if ({ds2[0][0]} > 3) {
        // El user_login coincide con el valor proporcionado
        header("Location: ../Ventana_Redireccion_Empresas_Clientes");
        exit(); // Termina la ejecución del script después de la redirección
    } else {
        // El user_login no coincide con el valor proporcionado
        $mensaje = "La licencia de Icarosoft ha caducado";
        echo "<script>
        alert('$mensaje');
        window.location.href = '$url_redir';
        </script>";
        exit(); // Termina la ejecución del script después de la redirección
    }
} else {
    // No se encontró ningún registro con el rif proporcionado o el estado no es "Inactivo"
    // Continuar con el flujo normal del programa
    // ...
}}

// 4.-  traer_todas_las_reglas_de_negocios_V2

/*sc_select(my_data, "SELECT nombre_variable, valor FROM configuracion_reglas_de_negocio WHERE empresa = '[usr_empresa]'");
if ({my_data} === false){
	echo "Error al acceder a la base de datos =". {my_data_erro};
}
else{
	while (!$my_data->EOF){
		$nomb_variable = $my_data->fields[0];
		$$val = $my_data->fields[1];
		
		
		$$nombre_variable = $val;
		sc_set_global(${$nombre_variable}); // Imprimirá 
		
		
		$my_data->MoveNext();
	}
	$my_data->Close();
} 


$nombre_variable = "grupo_ventas";
$val = 4;

$$nombre_variable = $val;

echo $grupo_ventas;*/

?>