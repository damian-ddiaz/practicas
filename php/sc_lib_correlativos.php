<?php
function traer_todos_los_correlativos(){
    /*   CORRELATIVO FACTURA VENTAS FACTURV     */
    
        $sql_FACTURV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'FACTURV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_FACTURV, $sql_FACTURV);
    
    if (!empty ({ds_FACTURV})){
         $FACTURV = {ds_FACTURV[0][0]};
         sc_set_global($FACTURV);
         
    }
    else{
        $sql_udp_FACTURV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','FACTURV','FACTURA DE VENTAS','0000000000','VENTAS','NO','DEBITO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_FACTURV);
    
        //echo "Error en consulta FACTURV $ds_FACTURV_erro";
    } 
    /*   CORRELATIVO FACTURA VENTAS FACTURV     */
    
    /*   CORRELATIVO FACTURA VENTAS DEFACTV     */
    
        $sql_DEFACTV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'DEFACTV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_DEFACTV, $sql_DEFACTV);
    
    if (!empty ({ds_DEFACTV})){
         $DEFACTV = {ds_DEFACTV[0][0]};
         sc_set_global($DEFACTV);
         
    }
    else{
        $sql_udp_DEFACTV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','DEFACTV','DEVOLUCION FACTURAS DE VENTAS','0000000000','VENTAS','NO','CREDITO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_DEFACTV);
    
        //echo "Error en consulta DEFACTV $ds_DEFACTV_erro";
    } 
    /*   CORRELATIVO FACTURA VENTAS DEFACTV     */
        
    /*   CORRELATIVO FACTURA VENTAS      */
    
        $sql_PRESUPV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'PRESUPV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_PRESUPV, $sql_PRESUPV);
    
    if (!empty ({ds_PRESUPV})){
         $PRESUPV = {ds_PRESUPV[0][0]};
         sc_set_global($PRESUPV);
         
    }
    else{
        $sql_udp_PRESUPV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','PRESUPV','PRESUPUESTOS DE VENTAS','0000000000','VENTAS','NO','DEBITO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_PRESUPV);
    
        //echo "Error en consulta PRESUPV $ds_PRESUPV_erro";
    } 
    /*   CORRELATIVO PRESUPUESTO VENTAS PRESUPV     */
    
        /*   CORRELATIVO NOTA ENTREGA VENTAS      */
    
        $sql_NOTENTV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTENTV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTENTV, $sql_NOTENTV);
    
    if (!empty ({ds_NOTENTV})){
         $NOTENTV = {ds_NOTENTV[0][0]};
         sc_set_global($NOTENTV);
         
    }
    else{
        $sql_udp_NOTENTV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','NOTENTV','NOTA DE ENTREGA DE VENTAS','0000000000','VENTAS','NO','DEBITO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTENTV);
    
        //echo "Error en consulta NOTENTV ".$ds_NOTENTV_erro;
    } 
    /*   CORRELATIVO NOTA ENTREGA VENTAS NOTENTV     */
    
    /*   CORRELATIVO DEVOLUCION NOTAS DE ENTREGA VENTAS      */
    
        $sql_DNOTENTV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'DNOTENTV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_DNOTENTV, $sql_DNOTENTV);
    
    if (!empty ({ds_DNOTENTV})){
         $DNOTENTV = {ds_DNOTENTV[0][0]};
         sc_set_global($DNOTENTV);
         
    }
    else{
        $sql_udp_DNOTENTV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','DNOTENTV','DEVOLUCION NOTAS DE ENTREGA VENTAS','0000000000','VENTAS','NO','CREDITO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_DNOTENTV);
    
        //echo "Error en consulta DNOTENTV ".$ds_DNOTENTV_erro;
    } 
    /*   CORRELATIVO DEVOLUCION NOTAS DE ENTREGA VENTAS    */
        
    
    /*   CORRELATIVO PEDIDOS DE VENTAS     */
    
        $sql_PEDIDOV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'PEDIDOV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_PEDIDOV, $sql_PEDIDOV);
    
    if (!empty ({ds_PEDIDOV})){
         $PEDIDOV = {ds_PEDIDOV[0][0]};
         sc_set_global($PEDIDOV);
         
    }
    else{
        $sql_udp_PEDIDOV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','PEDIDOV','PEDIDOS DE VENTAS','0000000000','VENTAS','NO','DEBITO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_PEDIDOV);
    
        //echo "Error en consulta PEDIDOV ".$ds_PEDIDOV_erro;
    } 
    /*   CORRELATIVO PEDIDOS DE VENTAS    */
    
    /*   CORRELATIVO NOTA DE DEBITO VENTAS   */
    
        $sql_NOTDEBV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTDEBV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTDEBV, $sql_NOTDEBV);
    
    if (!empty ({ds_NOTDEBV})){
         $NOTDEBV = {ds_NOTDEBV[0][0]};
         sc_set_global($NOTDEBV);
         
    }
    else{
        $sql_udp_NOTDEBV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','NOTDEBV','NOTA DE DEBITO VENTAS','0000000000','VENTAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTDEBV);
    
        //echo "Error en consulta NOTDEBV ".$ds_NOTDEBV_erro;
    } 
    /*   CORRELATIVO NOTA DE DEBITO VENTAS     */
    
    /*   CORRELATIVO NOTA DE CREDITO VENTAS   */
    
        $sql_NOTCREV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTCREV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTCREV, $sql_NOTCREV);
    
    if (!empty ({ds_NOTCREV})){
         $NOTCREV = {ds_NOTCREV[0][0]};
         sc_set_global($NOTCREV);
         
    }
    else{
        $sql_udp_NOTCREV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','NOTCREV','NOTA DE CREDITO VENTAS','0000000000','VENTAS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTCREV);
    
        //echo "Error en consulta NOTCREV ".$ds_NOTCREV_erro;
    } 
    /*   CORRELATIVO NOTA DE CREDITO VENTAS  */
    
    /*   CORRELATIVO RECIBO DE VENTAS  */
    
        $sql_RECIBOV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'RECIBOV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_RECIBOV, $sql_RECIBOV);
    
    if (!empty ({ds_RECIBOV})){
         $RECIBOV = {ds_RECIBOV[0][0]};
         sc_set_global($RECIBOV);
         
    }
    else{
        $sql_udp_RECIBOV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','RECIBOV','RECIBO DE VENTAS','0000000000','VENTAS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_RECIBOV);
    
        //echo "Error en consulta RECIBOV ".$ds_RECIBOV_erro;
    } 
    /*   CORRELATIVO RECIBO DE VENTAS  */
    
    /*   CORRELATIVO DESPACHO DE VENTAS  */
    
        $sql_DESPACV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'DESPACV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_DESPACV, $sql_DESPACV);
    
    if (!empty ({ds_DESPACV})){
         $DESPACV = {ds_DESPACV[0][0]};
         sc_set_global($DESPACV);
         
    }
    else{
        $sql_udp_DESPACV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','DESPACV','DESPACHO DE VENTAS','0000000000','VENTAS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_DESPACV);
    
        //echo "Error en consulta DESPACV ".$ds_DESPACV_erro;
    } 
    /*   CORRELATIVO DESPACHO DE VENTAS  */
    
        /*   CORRELATIVO ORDEN DE COMPRAS  */
    
        $sql_ORDENDC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'ORDENDC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_ORDENDC, $sql_ORDENDC);
    
    if (!empty ({ds_ORDENDC})){
         $ORDENDC = {ds_ORDENDC[0][0]};
         sc_set_global($ORDENDC);
         
    }
    else{
        $sql_udp_ORDENDC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','ORDENDC','ORDEN DE COMPRAS','0000000000','COMPRAS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_ORDENDC);
    
        //echo "Error en consulta ORDENDC ".$ds_ORDENDC_erro;
    } 
    /*   CORRELATIVO ORDEN DE COMPRAS  */
        
        /*   CORRELATIVO FACTURA DE COMPRAS  */
    
        $sql_FACTURC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'FACTURC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_FACTURC, $sql_FACTURC);
    
    if (!empty ({ds_FACTURC})){
         $FACTURC = {ds_FACTURC[0][0]};
         sc_set_global($FACTURC);
         
    }
    else{
        $sql_udp_FACTURC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','FACTURC','FACTURA DE COMPRAS','0000000000','COMPRAS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_FACTURC);
    
        //echo "Error en consulta FACTURC ".$ds_FACTURC_erro;
    } 
    /*   CORRELATIVO FACTURA DE COMPRAS  */
        
        /*   CORRELATIVO DEVOLUCION FACTURAS DE COMPRAS  */
    
        $sql_DEFACTC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'DEFACTC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_DEFACTC, $sql_DEFACTC);
    
    if (!empty ({ds_DEFACTC})){
         $DEFACTC = {ds_DEFACTC[0][0]};
         sc_set_global($DEFACTC);
         
    }
    else{
        $sql_udp_DEFACTC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','DEFACTC','DEVOLUCION FACTURAS DE COMPRAS','0000000000','COMPRAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_DEFACTC);
    
        //echo "Error en consulta DEFACTC ".$ds_DEFACTC_erro;
    } 
    /*   CORRELATIVO DEVOLUCION FACTURAS DE COMPRAS  */
        
        /*   CORRELATIVO DEVOLUCION NOTA DE ENTREGA COMPRAS */
    
        $sql_NOTENTC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTENTC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTENTC, $sql_NOTENTC);
    
    if (!empty ({ds_NOTENTC})){
         $NOTENTC = {ds_NOTENTC[0][0]};
         sc_set_global($NOTENTC);
         
    }
    else{
        $sql_udp_NOTENTC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','NOTENTC','NOTA DE ENTREGA COMPRAS','0000000000','COMPRAS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTENTC);
    
        //echo "Error en consulta NOTENTC ".$ds_NOTENTC_erro;
    } 
    /*   CORRELATIVO NOTA DE ENTREGA COMPRAS  */
        
        /*   CORRELATIVO DEVOLUCION NOTA DE ENTREGA COMPRAS */
    
        $sql_DNOENTC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'DNOENTC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_DNOENTC, $sql_DNOENTC);
    
    if (!empty ({ds_DNOENTC})){
         $DNOENTC = {ds_DNOENTC[0][0]};
         sc_set_global($DNOENTC);
         
    }
    else{
        $sql_udp_DNOENTC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','DNOENTC','DEVOLUCION NOTAS DE ENTREGA COMPRAS','0000000000','COMPRAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_DNOENTC);
    
        //echo "Error en consulta DNOENTC ".$ds_DNOENTC_erro;
    } 
    /*   CORRELATIVO DEVOLUCION NOTAS DE ENTREGA COMPRAS  */
        
        /*   CORRELATIVO NOTA DE DEBITO COMPRAS  */
    
        $sql_NOTDEBC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTDEBC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTDEBC, $sql_NOTDEBC);
    
    if (!empty ({ds_NOTDEBC})){
         $NOTDEBC = {ds_NOTDEBC[0][0]};
         sc_set_global($NOTDEBC);
         
    }
    else{
        $sql_udp_NOTDEBC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','NOTDEBC','NOTA DE DEBITO COMPRAS','0000000000','COMPRAS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTDEBC);
    
        //echo "Error en consulta NOTDEBC ".$ds_NOTDEBC_erro;
    } 
    /*   CORRELATIVO NOTA DE DEBITO COMPRAS    */
        
        /*   CORRELATIVO NOTA DE CREDITO COMPRAS  */
    
        $sql_NOTCREC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTCREC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTCREC, $sql_NOTCREC);
    
    if (!empty ({ds_NOTCREC})){
         $NOTCREC = {ds_NOTCREC[0][0]};
         sc_set_global($NOTCREC);
         
    }
    else{
        $sql_udp_NOTCREC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','NOTCREC','NOTA DE CREDITO COMPRAS','0000000000','COMPRAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTCREC);
    
        //echo "Error en consulta NOTCREC ".$ds_NOTCREC_erro;
    } 
    /*   CORRELATIVO NOTA DE CREDITO COMPRAS    */
        
        /*   CORRELATIVO INGRESO POR BANCOS  */
    
        $sql_INGRESB = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'INGRESB') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_INGRESB, $sql_INGRESB);
    
    if (!empty ({ds_INGRESB})){
         $INGRESB = {ds_INGRESB[0][0]};
         sc_set_global($INGRESB);
         
    }
    else{
        $sql_udp_INGRESB = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','INGRESB','INGRESO POR BANCOS','0000000000','BANCOS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_INGRESB);
    
        //echo "Error en consulta INGRESB ".$ds_INGRESB_erro;
    } 
    /*   CORRELATIVO INGRESO POR BANCOS    */
        
        /*   CORRELATIVO EGRESOS POR BANCOS  */
    
        $sql_EGRESEB = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'EGRESEB') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_EGRESEB, $sql_EGRESEB);
    
    if (!empty ({ds_EGRESEB})){
         $EGRESEB = {ds_EGRESEB[0][0]};
         sc_set_global($EGRESEB);
         
    }
    else{
        $sql_udp_EGRESEB = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','EGRESEB','EGRESOS POR BANCOS','0000000000','BANCOS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_EGRESEB);
    
        //echo "Error en consulta EGRESEB ".$ds_EGRESEB_erro;
    } 
    /*   CORRELATIVO EGRESOS POR BANCOS    */
        
        /*   CORRELATIVO NOTA DE DEBITO BANCOS  */
    
        $sql_NOTDEBB = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTDEBB') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTDEBB, $sql_NOTDEBB);
    
    if (!empty ({ds_NOTDEBB})){
         $NOTDEBB = {ds_NOTDEBB[0][0]};
         sc_set_global($NOTDEBB);
         
    }
    else{
        $sql_udp_NOTDEBB = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','NOTDEBB','NOTA DE DEBITO BANCOS','0000000000','BANCOS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTDEBB);
    
        //echo "Error en consulta NOTDEBB ".$ds_NOTDEBB_erro;
    } 
    /*   CORRELATIVO NOTA DE DEBITO BANCOS    */
        
        /*   CORRELATIVO NOTA DE CREDITO BANCOS */
    
        $sql_NOTCREB = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'NOTCREB') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_NOTCREB, $sql_NOTCREB);
    
    if (!empty ({ds_NOTCREB})){
         $NOTCREB = {ds_NOTCREB[0][0]};
         sc_set_global($NOTCREB);
         
    }
    else{
        $sql_udp_NOTCREB = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','NOTCREB','NOTA DE CREDITO BANCOS','0000000000','BANCOS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_NOTCREB);
    
        //echo "Error en consulta NOTCREB ".$ds_NOTCREB_erro;
    } 
    /*   CORRELATIVO NOTA DE CREDITO BANCOS  */
        
        /*   CORRELATIVO DEPOSITO POR BANCOS */
    
        $sql_DEPOSIB = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'DEPOSIB') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_DEPOSIB, $sql_DEPOSIB);
    
    if (!empty ({ds_DEPOSIB})){
         $DEPOSIB = {ds_DEPOSIB[0][0]};
         sc_set_global($DEPOSIB);
         
    }
    else{
        $sql_udp_DEPOSIB = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','DEPOSIB','DEPOSITO POR BANCOS','0000000000','BANCOS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_DEPOSIB);
    
        //echo "Error en consulta DEPOSIB ".$ds_DEPOSIB_erro;
    } 
    /*   CORRELATIVO DEPOSITO POR BANCOS        */
        
        /*   CORRELATIVO TRANSFERENCIA POR BANCOS */
    
        $sql_TRANSFB = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'TRANSFB') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_TRANSFB, $sql_TRANSFB);
    
    if (!empty ({ds_TRANSFB})){
         $TRANSFB = {ds_TRANSFB[0][0]};
         sc_set_global($TRANSFB);
         
    }
    else{
        $sql_udp_TRANSFB = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','TRANSFB','TRANSFERENCIA POR BANCOS','0000000000','BANCOS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_TRANSFB);
    
        //echo "Error en consulta TRANSFB ".$ds_TRANSFB_erro;
    } 
    /*   CORRELATIVO TRANSFERENCIA POR BANCOS
        */
        
        /*   CORRELATIVO CARGOS DE INVENTARIO */
    
        $sql_CARGOSI = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CARGOSI') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CARGOSI, $sql_CARGOSI);
    
    if (!empty ({ds_CARGOSI})){
         $CARGOSI = {ds_CARGOSI[0][0]};
         sc_set_global($CARGOSI);
         
    }
    else{
        $sql_udp_CARGOSI = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CARGOSI','CARGOS DE INVENTARIO','0000000000','INVENTARIO','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CARGOSI);
    
        //echo "Error en consulta CARGOSI ".$ds_CARGOSI_erro;
    } 
    /*   CORRELATIVO CARGOS DE INVENTARIO
        */
        
        /*   CORRELATIVO DESCARGOS DE INVENTARIO */
    
        $sql_DESCARI = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'DESCARI') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_DESCARI, $sql_DESCARI);
    
    if (!empty ({ds_DESCARI})){
         $DESCARI = {ds_DESCARI[0][0]};
         sc_set_global($DESCARI);
         
    }
    else{
        $sql_udp_DESCARI = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','DESCARI','DESCARGOS DE INVENTARIO','0000000000','INVENTARIO','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_DESCARI);
    
        //echo "Error en consulta DESCARI ".$ds_DESCARI_erro;
    } 
    /*   CORRELATIVO DESCARGOS DE INVENTARIO
        */
        
        /*   CORRELATIVO TRASLADO DE ALMACEN INVENTARIO */
    
        $sql_TRASLAI = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'TRASLAI') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_TRASLAI, $sql_TRASLAI);
    
    if (!empty ({ds_TRASLAI})){
         $TRASLAI = {ds_TRASLAI[0][0]};
         sc_set_global($TRASLAI);
         
    }
    else{
        $sql_udp_TRASLAI = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','TRASLAI','TRASLADO DE ALMACEN INVENTARIO','0000000000','INVENTARIO','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_TRASLAI);
    
        //echo "Error en consulta TRASLAI ".$ds_TRASLAI_erro;
    } 
    /*   CORRELATIVO TRASLADO DE ALMACEN INVENTARIO   */
        
        /*   CORRELATIVO RETENCION DE I.V.A. */
    
        $sql_RETIVAC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'RETIVAC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_RETIVAC, $sql_RETIVAC);
    
    if (!empty ({ds_RETIVAC})){
         $RETIVAC = {ds_RETIVAC[0][0]};
         sc_set_global($RETIVAC);
         
    }
    else{
        $sql_udp_RETIVAC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','RETIVAC','RETENCION DE I.V.A.','0000000000','VENTAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_RETIVAC);
    
        //echo "Error en consulta RETIVAC ".$ds_RETIVAC_erro;
    } 
    /*   CORRELATIVO RETENCION DE I.V.A.
        */
        
        /*   CORRELATIVO RETENCION I.S.L.R. */
    
        $sql_RETISLR = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'RETISLR') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_RETISLR, $sql_RETISLR);
    
    if (!empty ({ds_RETISLR})){
         $RETISLR = {ds_RETISLR[0][0]};
         sc_set_global($RETISLR);
         
    }
    else{
        $sql_udp_RETISLR = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','RETISLR','RETENCION I.S.L.R.','0000000000','VENTAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_RETISLR);
    
        //echo "Error en consulta RETISLR ".$ds_RETISLR_erro;
        
    } 
        /*   CORRELATIVO RETENCION I.S.L.R.      */

        /*   CORRELATIVO RETENCION MUNICIPAL */
    
        $sql_RETMUN = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'RETMUN') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_RETIMUN, $sql_RETMUN);
    
    if (!empty ({ds_RETIMUN})){
         $RETMUN = {ds_RETIMUN[0][0]};
         sc_set_global($RETMUN);
         
    }
    else{
        $sql_udp_RETMUN = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','RETMUN','RETENCION MUNICIPAL','0000000000','VENTAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_RETMUN);
    
        //echo "Error en consulta RETMUN ".$ds_RETMUN_erro;
        
    } 
        /*   CORRELATIVO RETENCION MUNICIPAL      */
        
        /*   CORRELATIVO FACTURA FISCAL    */
    
        $sql_FACFISC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'FACFISC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_FACFISC, $sql_FACFISC);
    
    if (!empty ({ds_FACFISC})){
         $FACFISC = {ds_FACFISC[0][0]};
         sc_set_global($FACFISC);
         
    }
    else{
        $sql_udp_FACFISC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','FACFISC','FACTURAS FISCALES','0000000000','FACTURACION','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_FACFISC);
    
        //echo "Error en consulta FACFISC $ds_FACFISC_erro";
    } 
    
        /*   CORRELATIVO FACTURA FISCAL    */
        
        
    /*   CORRELATIVO AJUSTE DE INVENTARIO    */
    
        $sql_AJUSTEI = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'AJUSTEI') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_AJUSTEI, $sql_AJUSTEI);
    
    if (!empty ({ds_AJUSTEI})){
         $AJUSTEI = {ds_AJUSTEI[0][0]};
         sc_set_global($AJUSTEI);
         
    }
    else{
        $sql_udp_AJUSTEI = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','AJUSTEI','AJUSTE DE INVENTARIO','0000000000','INVENTARIO','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_AJUSTEI);
    
        //echo "Error en consulta AJUSTEI $ds_AJUSTEI_erro";
    } 
    
        /*   CORRELATIVO AJUSTE DE INVENTARIO    */	
        
        
        /*   CORRELATIVO CUENTAS X COBRAR NOTA DEBITO    */	
            $sql_CXCND = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXCND') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXCND, $sql_CXCND);
    
    if (!empty ({ds_CXCND})){
         $CXCND = {ds_CXCND[0][0]};
         sc_set_global($CXCND);
         
    }
    else{
        $sql_udp_CXCND = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXCND','CUENTAS X COBRAR NOTA DEBITO','0000000000','FACTURACION','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXCND);
    
        //echo "Error en consulta CXCND $ds_CXCND_erro";
    } 
        /*   CORRELATIVO CUENTAS X COBRAR NOTA DEBITO    */	
        
        /*   CORRELATIVO CUENTAS X COBRAR NOTA CREDITO    */	
            $sql_CXCNC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXCNC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXCNC, $sql_CXCNC);
    
    if (!empty ({ds_CXCNC})){
         $CXCNC = {ds_CXCNC[0][0]};
         sc_set_global($CXCNC);
         
    }
    else{
        $sql_udp_CXCNC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXCNC','CUENTAS X COBRAR NOTA CREDITO','0000000000','FACTURACION','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXCNC);
    
        //echo "Error en consulta CXCNC $ds_CXCNC_erro";
    } 
        /*   CORRELATIVO CUENTAS X COBRAR NOTA CREDITO    */	
        
        /*   CORRELATIVO CUENTAS X COBRAR AJUSTE POSITIVO    */	
            $sql_CXCAJUP = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXCAJUP') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXCAJUP, $sql_CXCAJUP);
    
    if (!empty ({ds_CXCAJUP})){
         $CXCAJUP = {ds_CXCAJUP[0][0]};
         sc_set_global($CXCAJUP);
         
    }
    else{
        $sql_udp_CXCAJUP = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXCAJUP','CUENTAS X COBRAR AJUSTE POSITIVO','0000000000','FACTURACION','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXCAJUP);
    
        //echo "Error en consulta CXCAJUP $ds_CXCAJUP_erro";
    } 
        /*   CORRELATIVO CUENTAS X COBRAR AJUSTE POSITIVO    */	
        
        /*   CORRELATIVO CUENTAS X COBRAR AJUSTE NEGATIVO   */	
            $sql_CXCAJUN = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXCAJUN') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXCAJUN, $sql_CXCAJUN);
    
    if (!empty ({ds_CXCAJUN})){
         $CXCAJUN = {ds_CXCAJUN[0][0]};
         sc_set_global($CXCAJUN);
         
    }
    else{
        $sql_udp_CXCAJUN = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXCAJUN','CUENTAS X COBRAR AJUSTE NEGATIVO','0000000000','FACTURACION','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXCAJUN);
    
        //echo "Error en consulta CXCAJUN $ds_CXCAJUN_erro";
    } 
        /*   CORRELATIVO CUENTAS X COBRAR AJUSTE NEGATIVO    */	
        
        /*   CORRELATIVO CUENTAS X PAGAR NOTA DEBITO   */	
            $sql_CXPND = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXPND') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXPND, $sql_CXPND);
    
    if (!empty ({ds_CXPND})){
         $CXPND = {ds_CXPND[0][0]};
         sc_set_global($CXPND);
         
    }
    else{
        $sql_udp_CXPND = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXPND','CUENTAS X PAGAR NOTA DEBITO','0000000000','PAGOS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXPND);
    
        //echo "Error en consulta CXPND $ds_CXPND_erro";
    } 
        /*   CORRELATIVO CUENTAS X PAGAR NOTA DEBITO   */	
        
        /*   CORRELATIVO CUENTAS X PAGAR NOTA CREDITO   */	
            $sql_CXPNC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXPNC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXPNC, $sql_CXPNC);
    
    if (!empty ({ds_CXPNC})){
         $CXPNC = {ds_CXPNC[0][0]};
         sc_set_global($CXPNC);
         
    }
    else{
        $sql_udp_CXPNC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXPNC','CUENTAS X PAGAR NOTA CREDITO','0000000000','PAGOS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXPNC);
    
        //echo "Error en consulta CXPNC $ds_CXPNC_erro";
    } 
        /*   CORRELATIVO CUENTAS X PAGAR NOTA CREDITO   */	
        
        /*   CORRELATIVO CUENTAS X PAGAR AJUSTES POSITIVOS  */	
            $sql_CXPAJUP = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXPAJUP') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXPAJUP, $sql_CXPAJUP);
    
    if (!empty ({ds_CXPAJUP})){
         $CXPAJUP = {ds_CXPAJUP[0][0]};
         sc_set_global($CXPAJUP);
         
    }
    else{
        $sql_udp_CXPAJUP = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXPAJUP','CUENTAS X PAGAR AJUSTES POSITIVOS','0000000000','PAGOS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXPAJUP);
    
        //echo "Error en consulta CXPAJUP $ds_CXPAJUP_erro";
    } 
        /*   CORRELATIVO CUENTAS X PAGAR AJUSTES POSITIVOS   */	
        
        /*   CORRELATIVO CUENTAS X PAGAR AJUSTES NEGATIVOS */	
            $sql_CXPAJUN = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CXPAJUN') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CXPAJUN, $sql_CXPAJUN);
    
    if (!empty ({ds_CXPAJUN})){
         $CXPAJUN = {ds_CXPAJUN[0][0]};
         sc_set_global($CXPAJUN);
         
    }
    else{
        $sql_udp_CXPAJUN = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CXPAJUN','CUENTAS X PAGAR AJUSTES NEGATIVOS','0000000000','PAGOS','DEBITO','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CXPAJUN);
    
        //echo "Error en consulta CXPAJUN $ds_CXPAJUN_erro";
    } 
        /*   CORRELATIVO CUENTAS X PAGAR AJUSTES NEGATIVOS   */	
	
         /*   CORRELATIVO CUENTAS X COBRAR */	
            $sql_CORRCOB = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CORRCOB') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CORRCOB, $sql_CORRCOB);
    
    if (!empty ({ds_CORRCOB})){
         $CORRCOB = {ds_CORRCOB[0][0]};
         sc_set_global($CORRCOB);
         
    }
    else{
        $sql_udp_CORRCOB = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CORRCOB','CUENTAS X COBRAR','0000000000','COBRO','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CORRCOB);
    
    echo "Error en consulta CORRCOB $ds_CORRCOB_erro";
    } 
        /*   CORRELATIVO CUENTAS X COBRAR   */	
        
        /*   CORRELATIVO CUENTAS X PAGAR  */	
            $sql_CORRPAG = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CORRPAG') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CORRPAG, $sql_CORRPAG);
    
    if (!empty ({ds_CORRPAG})){
         $CORRPAG = {ds_CORRPAG[0][0]};
         sc_set_global($CORRPAG);
         
    }
    else{
        $sql_udp_CORRPAG = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CORRPAG','CUENTAS X PAGAR','0000000000','PAGOS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CORRPAG);
    
	echo "Error en consulta CORRPAG $ds_CORRPAG_erro";
    } 
        /*   CORRELATIVO CUENTAS X PAGAR   */
	
	/*   CORRELATIVO TICKET CLIENTES   */
    
        $sql_CORTICK = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CORTICK') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
        
    ";
    
    sc_lookup(ds_CORTICK, $sql_CORTICK);
    
    if (!empty ({ds_CORTICK})){
         $CORTICK = {ds_CORTICK[0][0]};
         sc_set_global($CORTICK);
         
    }
    else{
        $sql_udp_CORTICK = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CORTICK','TICKET CLIENTES','0000000000','CLIENTE','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    
    sc_exec_sql ($sql_udp_CORTICK);
    
    echo "Error en consulta CORTICK ".$ds_CORTICK_erro;
    } 
 	/*   CORRELATIVO TICKET CLIENTES   */
	
	/*   CORRELATIVO CONTROL FACTURA DE VENTA  */
        $sql_CONTROL = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CONTROL') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_CONTROL, $sql_CONTROL);
    
    if (!empty ({ds_CONTROL})){
         $CONTROL = {ds_CONTROL[0][0]};
         sc_set_global($CONTROL);   
    }
    else{
        $sql_udp_CONTROL = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','CONTROL','NRO CONTROL FACT VENTA','0000000000','FACTURACION','NO','DEBITO','[usr_empresa]','[usr_sucursal]')";
    sc_exec_sql ($sql_udp_CONTROL);
    echo "Error en consulta CORTICK ".$ds_CONTROL_erro;
    } 
 	/*   CORRELATIVO CONTROL FACTURA DE VENTA  */
	
	/*   CORRELATIVO ADELANTO CUENTA POR COBRAR  */
        $sql_ADEL = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'ADEL') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_ADEL, $sql_ADEL);
    
    if (!empty ({ds_ADEL})){
         $ADEL = {ds_ADEL[0][0]};
         sc_set_global($ADEL);   
    }
    else{
        $sql_udp_ADEL = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,movimiento_banco,tipo,empresa,sucursal)  
    VALUES
    ('0','ADEL','ADELANTO CUENTA POR COBRAR','0000000000','VENTAS','NO','CREDITO','[usr_empresa]','[usr_sucursal]')";
    sc_exec_sql ($sql_udp_ADEL);
    echo "Error en consulta ADEL ".$ds_ADEL_erro;
    } 
 	/*   CORRELATIVO ADELANTO CUENTA POR COBRAR  */
	
	/*   CORRELATIVO NUMERO DE CONTROL NOTA DE CREDITO VENTA */
        $sql_CONTROL_DEV_VENTA = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CONDEVV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_CONTROL_DEV_VENTA, $sql_CONTROL_DEV_VENTA);
    
    if (!empty ({ds_CONTROL_DEV_VENTA})){
         $CONTROL_DEV = {ds_CONTROL_DEV_VENTA[0][0]};
         sc_set_global($CONTROL_DEV);   
    }
    else{
        $sql_udp_CONTROL_DEV_VENTA = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CONDEVV','CONTROL DEVOLUCION DE VENTAS','0000000000','VENTAS','CREDITO','NO','[usr_empresa]','[usr_sucursal]')";
    sc_exec_sql ($sql_udp_CONTROL_DEV_VENTA);
    echo "Error en consulta CONDEVV ".$ds_CONTROL_DEV_VENTA_erro;
    } 
 	/*   CORRELATIVO NUMERO DE CONTROL NOTA DE CREDITO VENTA */
	
	/*   CORRELATIVO NUMERO DE CIERRE PUNTO DE VENTA */
        $sql_CIERRE_VENTA = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CIERRPV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_CIERRE_VENTA , $sql_CIERRE_VENTA);
    
    if (!empty ({ds_CIERRE_VENTA})){
         $CIERRE_VENTA = {ds_CIERRE_VENTA[0][0]};
         sc_set_global($CIERRE_VENTA);   
    }
    else{
        $sql_udp_CIERRE_VENTA = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CIERRPV','CIERRE PUNTO DE VENTAS','0000000000','VENTAS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    sc_exec_sql ($sql_udp_CIERRE_VENTA);
    echo "Error en consulta CIERRPV ".$ds_CIERRE_VENTA_erro;
    } 
 	/*   CORRELATIVO NUMERO DE CIERRE PUNTO DE VENTA */
	
	/*   CORRELATIVO SORTEOS POR NUMERO DE WHATSAPP */
        $sql_SORTEO = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'SORTWS') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_SORTEO , $sql_SORTEO);
    
    if (!empty ({ds_SORTEO})){
         $corr_sorteo = {ds_SORTEO[0][0]};
         sc_set_global($corr_sorteo);   
    }
    else{
        $sql_udp_SORTEO = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','SORTWS','SORTEO DE PREMIOS','0000000000','HELPDESK','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    sc_exec_sql ($sql_udp_SORTEO);
    echo "Error en consulta correlativo SORTWS ".$ds_SORTEO_erro;
    } 
 	/*   CORRELATIVO SORTEOS POR NUMERO DE WHATSAPP */
	
	/*   CORRELATIVO NOTA */
        $sql_NOTA = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CORNOTA') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_NOTA , $sql_NOTA);
    
    if (!empty ({ds_NOTA})){
         $corr_NOTA = {ds_NOTA[0][0]};
         sc_set_global($corr_NOTA);   
    }
    else{
        $sql_udp_NOTA = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CORNOTA','NOTAS PARA TICKET','0000000000','NOTA','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    sc_exec_sql ($sql_udp_NOTA);
    echo "Error en consulta correlativo CORNOTA ".$ds_NOTA_erro;
    } 
 	/*   CORRELATIVO NOTA */
	
	
	/*   CORRELATIVO TICKET */
        $sql_CORTICK = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CORTICK') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_CORTICK , $sql_CORTICK);
    
    if (!empty ({ds_CORTICK})){
         $corr_CORTICK = {ds_CORTICK[0][0]};
         sc_set_global($corr_CORTICK);   
    }
    else{
        $sql_udp_CORTICK = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CORTICK','TICKETS','0000000000','TICKETS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
		sc_exec_sql ($sql_udp_CORTICK);
		echo "Error en consulta correlativo CORNOTA ".$ds_CORTICK_erro;
    } 
	
	/*   CORRELATIVO INSTALACIONES */
        $sql_INSTCLI = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'INSTCLI') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_INSTCLI, $sql_INSTCLI);
    
    if (!empty ({ds_INSTCLI})){
         $corr_INSTCLI = {ds_INSTCLI[0][0]};
         sc_set_global($corr_INSTCLI);   
    }
    else{
        $sql_udp_INSTCLI = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','INSTCLI','INSTALACIONES','0000000000','INSTALACIONES','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
    sc_exec_sql ($sql_udp_INSTCLI);
    echo "Error en consulta correlativo INSTCLI ".$ds_INSTCLI_erro;
    }
	
 	/*   CORRELATIVO TICKET */
	
	/*   CORRELATIVO IMPORTACION BANCO */
        $sql_IMPBANC = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'IMPBANC') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_IMPBANC, $sql_IMPBANC);
    
    if (!empty ({ds_IMPBANC})){
         $corr_IMPBANC = {ds_IMPBANC[0][0]};
         sc_set_global($corr_IMPBANC);   
    }
    else{
        $sql_udp_IMPBANC = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','IMPBANC','IMPORTACIONES','0000000000','IMPORTACIONES','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
		sc_exec_sql ($sql_udp_IMPBANC);
		echo "Error en consulta correlativo IMPBANC ".$ds_IMPBANC_erro;
    }
	
	/*   CORRELATIVO CONCILIACION BANCO */
        $sql_CONCBAN = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'CONCBAN') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_CONCBAN, $sql_CONCBAN);
    
    if (!empty ({ds_CONCBAN})){
         $corr_IMPBANC = {ds_CONCBAN[0][0]};
         sc_set_global($corr_CONCBAN);   
    }
    else{
        $sql_udp_CONCBAN = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','CONCBAN','CONCILIACIONES','0000000000','BANCOS','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
		sc_exec_sql ($sql_udp_CONCBAN);
		echo "Error en consulta correlativo CONCBAN ".$ds_CONCBANerro;
    }
	
	/*   CORRELATIVO TOMA INVENTARIO */
        $sql_TOMAINV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'TOMAINV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_TOMAINV, $sql_TOMAINV);
    
    if (!empty ({ds_TOMAINV})){
         $corr_TOMAINV = {ds_TOMAINV[0][0]};
         sc_set_global($corr_TOMAINV);   
    }
    else{
        $sql_udp_TOMAINV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','TOMAINV','TOMA DE INVENTARIO','0000000000','INVENTARIO','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
		sc_exec_sql ($sql_udp_TOMAINV);
		echo "Error en consulta correlativo TOMAINV ".$ds_TOMAINVerro;
    }
	/*   CORRELATIVO TOMA INVENTARIO */

	/*   CORRELATIVO MOVIMIENTO DE INVENTARIO */
        $sql_MOVIINV = "SELECT
       valor
    FROM
       configuracion_correlativos
    WHERE 
       (tipo_documento = 'MOVIINV') AND
       (empresa = '[usr_empresa]') AND 
       (sucursal = '[usr_sucursal]')
    ";
    sc_lookup(ds_MOVIINV, $sql_MOVIINV);
    
    if (!empty ({ds_MOVIINV})){
         $corr_MOVIINV = {ds_MOVIINV[0][0]};
         sc_set_global($corr_MOVIINV);   
    }
    else{
        $sql_udp_MOVIINV = " 
        INSERT INTO
        configuracion_correlativos
      (id_correlativos,tipo_documento,nombre,valor,modulo,tipo,movimiento_banco,empresa,sucursal)  
    VALUES
    ('0','MOVIINV','MOVIMIENTOS DE INVENTARIO','0000000000','INVENTARIO','NO APLICA','NO','[usr_empresa]','[usr_sucursal]')";
		sc_exec_sql ($sql_udp_MOVIINV);
		echo "Error en consulta correlativo MOVIINV ".$ds_MOVIINVerro;
    }
	/*   CORRELATIVO MOVIMIENTO DE INVENTARIO*/
}
?>