<?php

/*

##### #####    ##    ####  #        ##   #####   ####   ####  
  #   #    #  #  #  #      #       #  #  #    # #    # #      
  #   #    # #    #  ####  #      #    # #    # #    #  ####  
  #   #####  ######      # #      ###### #    # #    #      # 
  #   #   #  #    # #    # #      #    # #    # #    # #    # 
  #   #    # #    #  ####  ###### #    # #####   ####   ####  


*/	
	
	
	
	/*variables de botones de traslados solo etiqueta para estatus confirmado resumen*/
function estado_de_botones_campos_traslados_res()
{

	[tras_status] = {estatus};
	
 

	if ([tras_status] == 'CONFIRMADO')     // Set as editable
{
    sc_field_readonly({codigo_almacen_origen}, 'on');
	sc_field_readonly({codigo_almacen_destino}, 'on');
	sc_field_readonly({descripcion_traslado}, 'on');
	sc_btn_display('update', 'off');
	sc_btn_display('delete', 'off');
	sc_btn_display  ('Confirmar Traslado', 'off');
	
}
	else
		{
    sc_field_readonly({codigo_almacen_origen}, 'off');
	sc_field_readonly({codigo_almacen_destino}, 'off');
	sc_field_readonly({descripcion_traslado}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('Confirmar Traslado', 'on');
}	
if ([tras_status] !== 'CONFIRMADO')     // Set as editable
{
    sc_field_readonly({codigo_almacen_origen}, 'off');
	sc_field_readonly({codigo_almacen_destino}, 'off');
	sc_field_readonly({descripcion_traslado}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('Confirmar Traslado', 'on');
}	

	if ([tras_status] == '')     // Set as editable
{
    sc_field_readonly({codigo_almacen_origen}, 'off');
	sc_field_readonly({codigo_almacen_destino}, 'off');
	sc_field_readonly({descripcion_traslado}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('Confirmar Traslado', 'on');
}	
	
}

/*variables de botones de traslados solo etiqueta para estatus confirmado resumen*/

/*variables de botones de traslados solo etiqueta para estatus confirmado detalles*/
function estado_de_botones_campos_traslados_det()
{


	
	if ([tras_status] == 'CONFIRMADO')     // Set as editable
{
    sc_btn_disabled('insert', 'on');
	sc_btn_display('new', 'off');
		
	sc_btn_display('update', 'off');
	sc_btn_display('delete', 'off');
	
	
}

		if ([tras_status] !== 'CONFIRMADO')     // Set as editable
{
    sc_btn_disabled('insert', 'off');
	sc_btn_display('new', 'on');
			
			
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	
}


	
	
}

/*variables de botones de traslados solo etiqueta para estatus confirmado detalles*/


/*

  ##        # #    #  ####  ##### ######  ####  
 #  #       # #    # #        #   #      #      
#    #      # #    #  ####    #   #####   ####  
######      # #    #      #   #   #           # 
#    # #    # #    # #    #   #   #      #    # 
#    #  ####   ####   ####    #   ######  ####  


*/
/*variables de botones de ajustes solo etiqueta para estatus confirmado resumen*/
function estado_de_botones_campos_ajustes_res()
{

	[ajustes_status] = {estatus};
	
 

	if ([ajustes_status] == 'CONFIRMADO')     // Set as editable
{
    sc_field_readonly({codigo_almacen}, 'on');
	sc_field_readonly({tipo_de_movimiento}, 'on');
	sc_field_readonly({descripcion}, 'on');
	sc_field_readonly({fecha}, 'on');
	sc_field_readonly({concepto}, 'on');
	sc_btn_display('update', 'off');
	sc_btn_display('delete', 'off');
	sc_btn_display  ('CONFIRMAR', 'off');
	
}
	else
		{
    sc_field_readonly({codigo_almacen}, 'off');
	sc_field_readonly({tipo_de_movimiento}, 'off');
	sc_field_readonly({descripcion}, 'off');
	sc_field_readonly({fecha}, 'off');
	sc_field_readonly({concepto}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('CONFIRMAR', 'on');
}	
if ([ajustes_status] !== 'CONFIRMADO')     // Set as editable
{
    sc_field_readonly({codigo_almacen}, 'off');
	sc_field_readonly({tipo_de_movimiento}, 'off');
	sc_field_readonly({descripcion}, 'off');
	sc_field_readonly({fecha}, 'off');
	sc_field_readonly({concepto}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('CONFIRMAR', 'on');
}	

	if ([ajustes_status] == '')     // Set as editable
{
    sc_field_readonly({codigo_almacen}, 'off');
	sc_field_readonly({tipo_de_movimiento}, 'off');
	sc_field_readonly({descripcion}, 'off');
	sc_field_readonly({fecha}, 'off');
	sc_field_readonly({concepto}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('CONFIRMAR', 'on');
}	
	
}

/*variables de botones de ajustes solo etiqueta para estatus confirmado resumen*/
	

/*variables de botones de ajustes solo etiqueta para estatus confirmado detalles*/
function estado_de_botones_campos_ajustes_det()
{


	
	if ([ajustes_status] == 'CONFIRMADO')     // Set as editable
{
    sc_btn_disabled('insert', 'on');
	sc_btn_display('new', 'off');
		
	sc_btn_display('update', 'off');
	sc_btn_display('delete', 'off');
	
	
}

		if ([ajustes_status] !== 'CONFIRMADO')     // Set as editable
{
    sc_btn_disabled('insert', 'off');
	sc_btn_display('new', 'on');
			
			
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	
}


	
	
}

/*variables de botones de ajustes solo etiqueta para estatus confirmado detalles*/

/*
#    #  ####  #####   ##      ###### #    # ##### #####  ######  ####    ##   
##   # #    #   #    #  #     #      ##   #   #   #    # #      #    #  #  #  
# #  # #    #   #   #    #    #####  # #  #   #   #    # #####  #      #    # 
#  # # #    #   #   ######    #      #  # #   #   #####  #      #  ### ###### 
#   ## #    #   #   #    #    #      #   ##   #   #   #  #      #    # #    # 
#    #  ####    #   #    #    ###### #    #   #   #    # ######  ####  #    # 


*/
//nota de entrega
/*variables de botones de nota_entrega solo etiqueta para estatus confirmado nota_entrega*/
function estado_de_botones_campos_nota_entrega_res($estatus)
{

	
	
 

	if ($estatus == 'CONFIRMADO')     // Set as editable
{

	sc_field_readonly({descripcion}, 'on');
	sc_field_readonly({fecha}, 'on');

	sc_btn_display('update', 'off');
	sc_btn_display('delete', 'off');
	sc_btn_display  ('CONFIRMAR', 'off');
//	sc_btn_disabled('insert', 'on');
//	sc_btn_display('new', 'off');
}
	else
		{

	sc_field_readonly({descripcion}, 'off');
	sc_field_readonly({fecha}, 'off');

	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('CONFIRMAR', 'on');
//	sc_btn_disabled('insert', 'off');
//	sc_btn_display('new', 'on');
}	
/*if ($estatus !== 'CONFIRMADO')     // Set as editable
{
    sc_field_readonly({codigo_almacen}, 'off');
	sc_field_readonly({tipo_de_movimiento}, 'off');
	sc_field_readonly({descripcion}, 'off');
	sc_field_readonly({fecha}, 'off');
	sc_field_readonly({concepto}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('CONFIRMAR', 'on');
}	

	if ($estatus == '')     // Set as editable
{
    sc_field_readonly({codigo_almacen}, 'off');
	sc_field_readonly({tipo_de_movimiento}, 'off');
	sc_field_readonly({descripcion}, 'off');
	sc_field_readonly({fecha}, 'off');
	sc_field_readonly({concepto}, 'off');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	sc_btn_display ('CONFIRMAR', 'on');
}	*/
	
}

/*variables de botones de nota_entrega solo etiqueta para estatus confirmado resumen*/
	

/*variables de botones de nota_entrega solo etiqueta para estatus confirmado detalles*/
function estado_de_botones_campos_nota_entrega_det($estatus)
{

	
	if ($estatus == 'CONFIRMADO')     // Set as editable
{
    sc_btn_disabled('insert', 'on');
	sc_btn_display('new', 'off');
		
	sc_btn_display('update', 'off');
	sc_btn_display('delete', 'off');
	
	
}
	else
		
		{
	
	sc_btn_disabled('insert', 'off');
	sc_btn_display('new', 'on');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	
	
	
	
	}
	

		if ($estatus !== 'CONFIRMADO')     // Set as editable
{
    sc_btn_disabled('insert', 'off');
	sc_btn_display('new', 'on');
	sc_btn_display('update', 'on');
	sc_btn_display('delete', 'on');
	
}else
{
    sc_btn_disabled('insert', 'on');
	sc_btn_display('new', 'off');
	sc_btn_display('update', 'off');
	sc_btn_display('delete', 'off');
		
		
		
		}


	
	
}

/*variables de botones de ajustes solo etiqueta para estatus confirmado nota entrega*/





	
	
?>