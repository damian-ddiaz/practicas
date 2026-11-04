<?php
    sc_confirm("¿Desea confirmar el traslado? una vez realizado no podrá revertir la operación...");
    $serial_producto_validar = 0;
    sc_select(my_data, "SELECT
        p.manejaser_productos 
    FROM
        inventario_traslados_detalle aj
        INNER JOIN inventario_productos p ON p.codigo_productos = aj.codigo_producto_det_traslado 
        AND aj.empresa = p.empresa 
        AND aj.sucursal = p.sucursal 
    WHERE
        aj.numero_det_traslado_almacen = {id_res_traslado_almacen}");
    if ({my_data} === false){
        echo "Error al acceder a la base de datos =". {my_data_erro};
    }
    else{
        while (!$my_data->EOF){
            $maneja_serial = $my_data->fields[0];
            if($maneja_serial == 'SI'){
                $serial_producto_validar = $serial_producto_validar+1;
            }
            $my_data->MoveNext();
        }
        $my_data->Close();
    }

    $res_err = verifica_cantidades({id_res_traslado_almacen});

    if(($res_err == true or $res_err == 1 ) and $serial_producto_validar > 0){
        sc_alert("Existen Seriales Pendientes por Incluir");
    }
    else{
        $totrenglones1 = "
            select
            count(numero_det_traslado_almacen) 
            from 
            inventario_traslados_detalle 
            where 
            numero_det_traslado_almacen = '{id_res_traslado_almacen}'
            ";

        sc_lookup(Dataset1, $totrenglones1);
        $t_renglones1 = {Dataset1 [0][0]};

        if ($t_renglones1 == 0 ) {

            $params = array(
        'title' => 'Ingrese Registros',
        'type' => 'success',
        'timer' => '2000',
        'showConfirmButton' => false,
        'position' => 'center',
        'toast' => false
        );

            sc_alert("Mensaje no Existen Registros", $params);

        }
        else
        {


            // actualiza correlativo en las talas de configuracion correlativo
        actualiza_nuevo_correlativo();
        // actualizar estatus en inventario_traslado_resumen y inventario_traslado_detalle	
        actualiza_estatus();


        // FUNCION EN SC_LIB_INVENTARIO.PHP
        actualiza_stock_traslado();

        //sc_exit (sel);
        // funcion confirmar_traslado se encuentra en sc_lib_inventario de las librerias internas
        confirmar_traslado();
        estado_de_botones_campos_traslados_res();	
        // sc_redir(traslados_nuevo_resumen);

        } // finaliza el if
    }
?>