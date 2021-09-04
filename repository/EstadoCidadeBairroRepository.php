<?php


require_once("./includes/config.php");
require_once("./rede-credenciada/model/EstadoCidadeBairro.php");


$conect = conexaoMysqlTestDev();

function getByEstadoCidadeBairro($uf, $cidade, $bairro)
{   
    global $conect;

    $sql = "SELECT 
                e.id AS id_estado, c.id AS id_cidade, b.id AS id_bairro
            FROM
                tbl_estado AS e
                    inner JOIN
                tbl_cidade AS c ON c.estado = e.id
                    inner JOIN
                tbl_bairro AS b ON b.id_cidade = c.id
            WHERE
                e.uf LIKE '$uf'
                    AND c.nome LIKE '$cidade'
                    AND b.nome LIKE '$bairro';";
    // echo $sql;



    $select = mysqli_query($conect, $sql);
    if ($rs = mysqli_fetch_assoc($select)) {
        $estadoCidadeBairro = new EstadoCidadeBairro();
        $estadoCidadeBairro->setIdEstado($rs['id_estado']);
        $estadoCidadeBairro->setIdCidade($rs['id_cidade']);
        $estadoCidadeBairro->setIdBairro($rs['id_bairro']);

        return $estadoCidadeBairro;
    }
    
    return false;
}

