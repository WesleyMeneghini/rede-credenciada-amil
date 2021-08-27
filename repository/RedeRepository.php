<?php


require_once("./includes/config.php");
require_once("./rede-credenciada/model/Rede.php");


$conect = conexaoMysqlTestDev();

function getByNameRede($rede, $idOperadora)
{   
    global $conect;

    $sql = "SELECT * FROM tbl_rede where nome like '$rede' and id_operadora = $idOperadora;";
    // echo $sql;
    $select = mysqli_query($conect, $sql);
    if ($rs = mysqli_fetch_assoc($select)) {
        $rede = new Rede();
        $rede->setId($rs['id']);
        $rede->setNome($rs['nome']);
        $rede->setIdOperadora($rs['id_operadora']);

        return $rede;
    }
    return false;
}

