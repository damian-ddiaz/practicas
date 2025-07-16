//$color = {color}; // Suponiendo que 'color' es el nombre del campo en la base de datos
echo "<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[id^=\"hidden_field_data_color_\"]').forEach(function (element) {
            let colorHex = element.innerText.trim(); // Extrae el color hexadecimal
            
            if (colorHex.match(/^#([0-9A-F]{3}){1,2}$/i)) { // Verifica si es un código hexadecimal válido
                element.style.backgroundColor = colorHex; 
                
                // Convertir Hex a RGB
                let r = parseInt(colorHex.substring(1, 3), 16);
                let g = parseInt(colorHex.substring(3, 5), 16);
                let b = parseInt(colorHex.substring(5, 7), 16);
                
                // Calcular luminancia (según W3C)
                let luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                
                // Determinar color de texto: Negro si el fondo es claro, Blanco si el fondo es oscuro
                let textColor = luminance > 0.5 ? '#000000' : '#FFFFFF';

                // Aplicar el color al ID relacionado con el texto
                let textElement = document.querySelector('[id^=\"id_read_on_color_\"][id$=\"' + element.id.split('_').pop() + '\"]');
                if (textElement) {
                    textElement.style.color = textColor;
                    textElement.style.fontWeight = 'bold'; // Hacer el texto más legible
                }
            }
        });
    });
</script>";