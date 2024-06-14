<?php
function numero_a_texto($numero)
{
    $unidades = array(
        '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'
    );
    $decenas = array(
        '', '', 'veinti', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'
    );
    $texto = '';

    if ($numero < 20) {
        $texto = $unidades[$numero];
    } elseif ($numero < 100) {
        $decena = (int)($numero / 10);
        $unidad = $numero % 10;
        $texto = $decenas[$decena];
        if ($unidad > 0) {
            $texto .= $unidades[$unidad];
        }
    } elseif ($numero < 1000) {
        $centena = (int)($numero / 100);
        $resto = $numero % 100;
        $texto = $unidades[$centena] . ' cientos';
        if ($resto > 0) {
            $texto .= ' ' . numero_a_texto($resto);
        }
    } elseif ($numero == 1000) {
        $texto = 'mil';
    } else {
        $texto = 'El número ' . $numero . ' no se puede convertir a texto.';
    }
    return $texto;
}

function formato_moneda($numero)
{
    $entero = floor($numero);
    $decimal = round(($numero - $entero) * 100);

    $texto_entero = numero_a_texto($entero);
    $texto_decimal = numero_a_texto($decimal);

    return $texto_entero . ' con ' . $texto_decimal . ' céntimos';
}

// Ejemplo de uso
$total = 80.60;
$total_texto = formato_moneda($total);
echo $total_texto; // Salida: ochenta con sesenta céntimos
