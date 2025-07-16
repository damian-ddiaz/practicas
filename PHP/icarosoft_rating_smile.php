icarosoft_rating_smile.php - Calificacion de estado de animo de clientes

 0| <?php   
 4|     function verificarSmileRating($nivel_felicidad_ia)
 5| {
 6|     switch ($nivel_felicidad_ia) {
 7|         case "0":
 8|             return "https://storage.googleapis.com/icarosoft-data/data-aplicacion/smile_lessrating.png";
 9|         case "1":
10|             return "https://storage.googleapis.com/icarosoft-data/data-aplicacion/smile_verybad.png";
11|         case "2":
12|             return "https://storage.googleapis.com/icarosoft-data/data-aplicacion/smile_bad.png";
13|         case "3":
14|             return "https://storage.googleapis.com/icarosoft-data/data-aplicacion/smile_ok.png";
15|         case "4":
16|             return "https://storage.googleapis.com/icarosoft-data/data-aplicacion/smile_good.png";
17|         case "5":
18|             return "https://storage.googleapis.com/icarosoft-data/data-aplicacion/smile_verygood.png";
19|         default:
20|             return "https://storage.googleapis.com/icarosoft-data/data-aplicacion/smile_lessrating.png";
21|     }

29| }     

$check_sql = "select cod_cliente, nombre_cliente, nivel_felicidad_ia from clientes_datos where id_cliente = {id_cliente}";
sc_lookup(cli, $check_sql);

if (isset({cli[0][0]})){
    {cod_cliente} = {cli[0][0]};
    {nombre_cliente} = {cli[0][1]};
	$nivel_felicidad_iac = {cli[0][2]}; // idalwin 7/04/2025
}
// idalwin 07/04/2025   agregar libreria y funcion para smile rating
$icono = verificarSmileRating($nivel_felicidad_iac);
{smile_actual} = "<img src='" . $icono . "' width='40' height='40'>";
// Aineando la Imagen a la Izquierda
{smile_actual} = "<div style='text-align:left;'><img src='" . $icono . "' width='40' height='40'></div>"; 


/*{smile_actual} Evento Onclick*/
/*
 * 
 */

// Con parámetro y mostrando en modal.
sc_redir('clientes_datos_analisis_ia', par_id_cliente ={id_cliente}; par_usr_empresa ={empresa}, 'modal', , '800', '1000');









52| ?>


