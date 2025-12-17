<?php
  if({sc_script_name} == 'seguridadv3Login'){ // Control para Autenticar
    if(isset($_COOKIE['usr_data'])){
        unset($_COOKIE['usr_data']);
    }
    }
    if({sc_script_name} == 'seguridadv2_Login'){
        if(isset($_COOKIE['usr_data'])){
            unset($_COOKIE['usr_data']);
        }
    }
    if({sc_script_name} == 'seguridadv2_Login'){
        if(isset($_COOKIE['usr_data'])){
            unset($_COOKIE['usr_data']);
        }
    }
    ?>
    <style>
        @media (max-width: 500px) {
            .scMenuTHeader {
                display: none;
            }
            .sc-layer-0, .sc-layer-1{
                display: none;
            }
            #idDivMenu{
                padding-top:15px !important;
            }
        }
    </style>
    <?php


?>