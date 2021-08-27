<?php


require_once("./includes/config.php");
require_once("./rede-credenciada/model/TipoPlano.php");


$conect = conexaoMysqlTestDev();

function getByNameTipoPlano($plano, $idOperadoras)
{   
    global $conect;

    $listaIdOperadoras = implode(",", $idOperadoras);

    $sql = "SELECT * FROM tbl_tipo_plano where titulo like '$plano' and id_operadora in ($listaIdOperadoras);";
    // echo $sql;
    $select = mysqli_query($conect, $sql);
    if ($rs = mysqli_fetch_assoc($select)) {
        $plano = new TipoPlano();
        $plano->setId($rs['id']);
        $plano->setTitulo($rs['titulo']);
        $plano->setIdOperadora($rs['id_operadora']);
        $plano->setIdCategoriaPlano($rs['id_categoria_plano']);
        $plano->setReembolso($rs['reembolso']);

        return $plano;
    }
    return false;
}

