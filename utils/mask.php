<?php


function Mask($tipoMask, $str)
{

    if (strlen($tipoMask) == 10 && ($tipoMask == "telefone" || $tipoMask == "celular")) {
        $tipoMask = "telefone";
    } elseif (strlen($tipoMask) == 11 && ($tipoMask == "telefone" || $tipoMask == "celular")) {
        $tipoMask = "celular";
    }

    switch ($tipoMask) {
        case "telefone":
            $mask = "(##) ####-####";
            break;
        case "celular":
            $mask = "(##) #####-####";
            break;
        case "cnpj":
            $mask = "##.###.###/####-##";
            break;
        case "cpf":
            $mask = "###.###.###-##";
            break;
        case "cep":
            $mask = "#####-###";
            break;
        default:
            $mask = "";
    }

    if ($mask == "") {
        return null;
    } else {

        $str = str_replace(" ", "", $str);

        for ($i = 0; $i < strlen($str); $i++) {
            $mask[strpos($mask, "#")] = $str[$i];
        }

        return $mask;
    }
}
