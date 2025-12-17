<?php
   // sc_exec_sql ("SET collation_connection = 'utf8mb4_general_ci';");

// version actual de aplicativo
[version_actual] = '1.1.3';




//librerio para analisis de google analitycs
//sc_include_library("prj", "google_analitycs", "google_analitycs.html", true, true);
//sc_include_library("prj", "google_analitycs", "google_analitycs.html", true, true);

 actualiza_menu(); // metodo para corregir menu en developer y admin de usuarios
//librerio para analisis de google analitycs

/*# VALIDACION DE LA APLICACION CON TOKEN
#   variables usuario,empresa, token, redir(true o false), log (true o false)
sc_include_library("sys", "composer", "vendor/autoload.php", true, true);




//validateApp([jwta],false,true);  // sin redirt y con log generamente para pruebas
validateApp([jwta],true,false);  // con funcionamiento normal*/

sc_menu_force_mobile(true);
/*Comienza funcion datos empresa todos los datos basicos de la empresa como variables globales*/	 
//selecciona_datos_empresa();
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

buscardatoslogin([usr_login]);
buscardatosempresa([usr_empresa]);
buscardatossucursal([usr_sucursal]);

/* Por el momento */
//corregir_status_fact_transacciones();
//corregir_status_abonos_transacciones();
// corregir_campo_fact_fiscal();
/* Por el momento */
if (empty([usr_nivel]) || empty([usr_sucursal]) || empty([usr_empresa]) || empty([usr_login])) {
    sc_redir("../logout_session/");
}

//ocultar items temporales

//sc_menu_delete(item_39);

?>