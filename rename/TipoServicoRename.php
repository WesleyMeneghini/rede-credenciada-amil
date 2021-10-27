<?php

function renameTipoServico($idOperadora, $nome)
{

    if ($idOperadora == 1) {
        
        switch ($nome) {

            case "PRONTO SOCORRO/PRONTO-ATENDIMENTO":
                $nome = "";
                break;
            default:
                break;
        }
    }
}
