'<?php
    $err = actualizar_seriales({id_ajustes_resumen},{tipo_de_movimiento});


    //Leonardo Urdaneta 13-09-2022
    //listar registros
    $tabla = "movimientos_producto_seriales";
    $campos = "codigo_producto, serial";
    $condicion = "id_resumen = $id_resumen and modulo = 'inventario' and proceso = 'ajuste cargo'";
    sc_select(my_data, "SELECT $campos FROM $tabla WHERE $condicion");
    $res = 0;
    if ({my_data} === false){
        echo "Error al acceder a la base de datos =". {my_data_erro};
    }
    else{
        $err = 0;
        $msg = '';
        if ($tipo=='CARGO DE PRODUCTOS'){
            while (!$my_data->EOF){
                $cod_prod = $my_data->fields[0];
                $ser = $my_data->fields[1];
                $check_sql = "SELECT serial FROM inventario_productos_seriales WHERE empresa = '[usr_empresa]' AND sucursal = '[usr_sucursal]' and serial = '$ser' AND codigo_productos  = '$cod_prod'";
                sc_lookup(rs, $check_sql);

                if (isset({rs[0][0]})){
                    $err = $err + 1;
                    $msg .= "El serial $ser del codigo producto $cod_prod ya se ha ingresado anteriormente\n";
                }
                $my_data->MoveNext();
            }
            $my_data->Close();
        }
    }

    if($err == 0){
        if ($tipo=='CARGO DE PRODUCTOS'){
            sc_exec_sql ("INSERT INTO inventario_productos_seriales
                        SELECT
                            0 AS id_seriales,
                            codigo_producto AS codigo_productos,
                            serial,
                            'disponible',
                            empresa,
                            sucursal 
                        FROM
                            movimientos_producto_seriales 
                        WHERE
                            modulo = 'inventario' 
                            AND proceso = 'ajuste cargo' 
                            AND id_resumen = '$id_resumen'");
        }
        else{
            
            //Leonardo Urdaneta 13-09-2022
            //listar registros
            $tabla2 = "movimientos_producto_seriales";
            $campos2 = "serial, codigo_producto";
            $condicion2 = "id_resumen = $id_resumen";
            sc_select(my_data2, "SELECT $campos2 FROM $tabla2 WHERE $condicion2");
            if ({my_data2} === false){
                echo "Error al acceder a la base de datos =". {my_data2_erro};
            }
            else{
                while (!$my_data2->EOF){
                    $serial = $my_data2->fields[0];
                    $co_prod = $my_data2->fields[1];
                    
                    sc_exec_sql ("DELETE FROM inventario_productos_seriales WHERE serial = '$serial' AND codigo_productos = '$co_prod' and empresa = '[usr_empresa]' and sucursal='[usr_sucursal]'");
                    $my_data2->MoveNext();
                }
                $my_data2->Close();
            }

            
        }
    }
    return $msg;


?>