<?php
    $data = precios_producto({codigo},{tipo_precio});
    $precio = $data['precio_unitario'];
    $tipo_iva = $data['tipo_iva'];
    {costo} = $data['costo'];

    $equiv = consultar_equivalencia({codigo}, {tipo_unidad});

    if($tipo_iva <> '003'){
        $valor = buscar_iva($tipo_iva);
        {precio_unitario} = ($equiv*$precio);
        {total_iva} = round(($equiv*$precio)*($valor/100),2);
        {iva} = $valor;
    }

    else{
        {precio_unitario} = $precio*$equiv;
        {total_iva} = 0.00;
    }

    {sub_total} = {precio_unitario}*{cantidad};
    {total_renglon} = ({precio_unitario}+{total_iva})*{cantidad};
?>