<?php
//actualiza_menu(); // metodo para corregir menu en developer y admin de usuarios

    //chat_asistente
    echo '
    <style>
    #chat_iframe {
        position: fixed;
        bottom: 95px;
        right: 25px;
        width: 800px;
        height: 600px;
        border: none;
        border-radius: 12px;
        display: none;
        z-index: 9999;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        background: white;
    }

    #chat_button {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 10000;
        background: #727cf5;
        color: white;
        border: none;
        border-radius: 50%;
        width: 70px;
        height: 70px;
        font-size: 22px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

    #chat_button:hover {
        background: #833471;
    }

    /* Overlay para detectar clics fuera del chat */
    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
        z-index: 9998;
    }
    </style>

    <!-- Botón flotante -->
    <button id="chat_button" onclick="toggleChat()" title="ICARO_Bot">
    <video src="https://storage.googleapis.com/icarosoft-data/fileupload/d62b606f48b88e7819507986847c1e34.webm" 
            class="chat-logo"
            loop autoplay muted disablePictureInPicture></video>
    </button>

    <!-- Iframe del chat asistente -->
    <iframe id="chat_iframe" src="../chat_asistente/index.php"></iframe>

    <!-- Overlay invisible para capturar clics fuera del chat -->
    <div id="overlay" onclick="toggleChat()"></div>

    <script>
    function toggleChat() {
        const iframe = document.getElementById("chat_iframe");
        const overlay = document.getElementById("overlay");
        const video = document.querySelector("#chat_button video");
        const isOpen = iframe.style.display === "block";

        iframe.style.display = isOpen ? "none" : "block";
        overlay.style.display = isOpen ? "none" : "block";

        // Si el iframe se está abriendo, envíale un mensaje para que enfoque el input
        if (!isOpen) {
        // Espera un momento para asegurar que el iframe sea renderizado y esté listo
        // before attempting to send the message. 50-100ms usually sufficient.
        setTimeout(() => {
            if (iframe.contentWindow) {

            iframe.contentWindow.postMessage("focusChatInput", "*"); 
            }
        }, 100); 
        }

        // Asegurarse de que el video siga reproduciéndose
        if (video) {
        video.play().catch(() => {
            console.warn("No se pudo reanudar el video automáticamente.");
        });
        }
    }

    // Cerrar con tecla ESC
    document.addEventListener("keydown", function(event) {
        const iframe = document.getElementById("chat_iframe");
        const video = document.querySelector("#chat_button video");

        if (event.key === "Escape" && iframe.style.display === "block") {
        iframe.style.display = "none";
        document.getElementById("overlay").style.display = "none";

        if (video) {
            video.play().catch(() => {
            console.warn("No se pudo reanudar el video después de cerrar con Escape.");
            });
        }
        }
    });

    // Opcional: Reproducir el video al cargar la página
    window.addEventListener("load", () => {
        const video = document.querySelector("#chat_button video");
        if (video) {
        video.play().catch(() => {
            console.warn("El video no se reprodujo automáticamente. Requiere interacción del usuario.");
        });
        }
    });
    </script>
    ';

?>