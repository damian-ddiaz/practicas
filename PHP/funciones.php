<?php

	function WriteHTML($html)
{
    //HTML parser
    $html = strip_tags($html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); // Suprime todos los tags excepto los permitidos
    $html = str_replace("\n", ' ', $html); // Reemplaza retorno de línea por espacio
    $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); // Divide la cadena en partes con las etiquetas
    
    foreach($a as $i => $e)
    {
        if($i % 2 == 0)
        {
            // Texto
            if ($this->HREF)
                $this->PutLink($this->HREF, $e);  // Si hay un enlace, lo procesa
            else
                $this->Write(5, txtentities($e));  // Escribe el texto normal
        }
        else
        {
            // Etiqueta
            if ($e[0] == '/')
                $this->CloseTag(strtoupper(substr($e, 1))); // Cierra la etiqueta
            else
            {
                // Extrae atributos
                $a2 = explode(' ', $e);
                $tag = strtoupper(array_shift($a2));
                $attr = array();
                foreach ($a2 as $v)
                {
                    if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                        $attr[strtoupper($a3[1])] = $a3[2];
                }
                
                // Nueva sección: Si la etiqueta es <p>, usa MultiCell con justificación
                if ($tag == 'P') {
                    $this->Ln(5); // Añade un salto de línea antes del párrafo
                    $this->MultiCell(0, 10, utf8_decode(txtentities($e)), 0, 'J'); // Justifica el texto dentro del <p>
                } else {
                    $this->OpenTag($tag, $attr);  // Abre la etiqueta normal
                }
            }
        }
    }
}
?>
