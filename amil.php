<?php

$path = __DIR__;

require_once "$path/../includes/config.php";
require_once "$path/repository/RedeRepository.php";
require_once "$path/model/TipoPlano.php";
require_once "$path/utils/mask.php";
require_once "$path/services/requests.php";

$conect = conexaoMysql();

$logTipoServicos = true;
$logEspecialidade = true;
$logPrestadores = true;

$salvarNovoTipoServico = true;
$salvarNovoPrestadore = true;
$salvarNovaEspecialidade = true;
$salvarNovoEstabelecimento = true;

$estado = "SP";
$municipio = "SAO PAULO";
$bairro = "TODOS OS BAIRROS";

$res = sendRequest(
    "https://www.amil.com.br/institucional/api/InstitucionalMiddleware/RedeCredenciadaPlanos?operadora=SAUDE",
    "GET",
);

$redeEncontradas = array_filter($res, function ($e) {

    if ($e['tipo'] == 'REDE') {

        $contexto = $e['contexto'];
        if ($contexto == "ONEHEALTH") {
            $idOperadora = 5;
        } elseif ($contexto == "AMIL") {
            $idOperadora = 2;
        } elseif ($contexto == "NEXT") {
            $idOperadora = 8;
        }

        if (getByNameRede($e['nomeDoPlano'], $idOperadora)) {
            return true;
        }
    }
    return false;
});


foreach ($redeEncontradas as $rede) {

    $codigoRede = $rede['codigoRede'];
    $contexto = $rede['contexto'];
    $nomeRede = $rede['nomeDoPlano'];
    $operadora = $rede['operadora'];

    if ($contexto == "ONEHEALTH") {
        $idOperadora = 5;
    } elseif ($contexto == "AMIL") {
        $idOperadora = 2;
    } elseif ($contexto == "NEXT") {
        $idOperadora = 8;
    }

    $redeRes = getByNameRede($nomeRede, $idOperadora);
    $idRede = $redeRes->getId();

    echo "\n-------------------------------------------------------------\n";
    echo "NOME REDE: $nomeRede (ID: $idRede)\n\n";

    $resMunicipios = sendRequest(
        "https://www.amil.com.br/institucional/api/InstitucionalMiddleware/RedeCredenciadaMunicipio/$codigoRede/SAUDE/$estado",
        "GET",
    );

    foreach ($resMunicipios as $municipioRes) {

        $municipio = $municipioRes['Municipio'];

        $sql = "SELECT * from tbl_cidade where nome like '$municipio' and estado = 26;";
        $select = mysqli_query($conect, $sql);

        if ($rs = mysqli_fetch_assoc($select)) {
            $idCidade = $rs['id'];

            $resTipoServico = sendRequest(
                "https://www.amil.com.br/institucional/api/InstitucionalMiddleware/RedeCredenciadaTipoServico/$codigoRede/SAUDE/$estado/$municipio/$bairro/",
                "GET",
            );

            foreach ($resTipoServico as $tipoServico) {
                $idTipoServico = 0;
                $nomeTipoServico = $tipoServico['NIVEL1_CAPITULO'];

                if ($logTipoServicos) {
                    echo "TIPO SERVIÇO: $nomeTipoServico \n";
                }

                $sql = "SELECT * from tbl_tipo_servico where nome like '$nomeTipoServico';";
                $select = mysqli_query($conect, $sql);
                if ($rs = mysqli_fetch_assoc($select)) {
                    $idTipoServico = $rs['id'];
                } else {
                    if ($salvarNovoTipoServico) {
                        $insert = "INSERT INTO tbl_tipo_servico (nome) VALUES ('$nomeTipoServico');";
                        mysqli_query($conect, $insert);
                        $idTipoServico = mysqli_insert_id($conect);
                    }
                }

                if ($idTipoServico > 0) {

                    $curl = curl_init();

                    $nomeTipoServicoFormatado = str_replace(" / ", "-", $nomeTipoServico);

                    $url =  str_replace(" ", "%20", "https://www.amil.com.br/institucional/api/InstitucionalMiddleware/RedeCredenciadaEspecialidade/$codigoRede/SAUDE/$estado/$municipio/$bairro/$nomeTipoServicoFormatado/");
                    // echo $url . "\n";

                    curl_setopt_array($curl, [
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_POSTFIELDS => "",
                        CURLOPT_COOKIE => "",
                    ]);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        // echo $response;

                        $resPespecialidades = json_decode($response, true);

                        foreach ($resPespecialidades as $especialidade) {
                            $idEspecialidade = 0;
                            $nomeEspecialidade = $especialidade['NIVEL2_ELEMENTODIVULGACAO'];

                            if ($logEspecialidade) {
                                echo "\nESPECIALIDADE: $nomeEspecialidade\n";
                            }

                            $sql = "SELECT * from tbl_especialidade where nome like '$nomeEspecialidade';";
                            $select = mysqli_query($conect, $sql);

                            if ($rs = mysqli_fetch_assoc($select)) {
                                $idEspecialidade = $rs['id'];
                            } else {
                                if ($salvarNovaEspecialidade) {
                                    $insert = "INSERT INTO tbl_especialidade (nome) VALUES ('$nomeEspecialidade');";
                                    mysqli_query($conect, $insert);
                                    $idEspecialidade = mysqli_insert_id($conect);
                                }
                            }

                            if ($idEspecialidade > 0) {
                                // ULTIMA ETAPA
                                // Pegar os resultados da Rede Credenciada, Retorna os prestadores

                                $dados = [
                                    "codigoRede" => $codigoRede,
                                    "uf" => $estado,
                                    "municipio" => $municipio,
                                    "bairro" => $bairro,
                                    "especialidade" => $nomeEspecialidade,
                                    "tipoServico" => $nomeTipoServico,
                                    "operadora" => $operadora,
                                    "modalidade" => "SAUDE",
                                    "contexto" => $contexto,
                                ];

                                $dadosJson = json_encode($dados, JSON_UNESCAPED_UNICODE);

                                $curl = curl_init();

                                curl_setopt_array($curl, [
                                    CURLOPT_URL => "https://www.amil.com.br/institucional/api/InstitucionalMiddleware/RedeCredenciadaCredenciado",
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "POST",
                                    CURLOPT_POSTFIELDS => $dadosJson,
                                    CURLOPT_HTTPHEADER => [
                                        "Content-Type: application/json"
                                    ],
                                ]);

                                $response = curl_exec($curl);
                                $err = curl_error($curl);

                                curl_close($curl);

                                if ($err) {
                                    echo "cURL Error #:" . $err;
                                } else {
                                    // echo $response;

                                    $prestadores = json_decode($response, true);

                                    foreach ($prestadores as $prestador) {
                                        $idEstabelecimento = 0;
                                        $nomePrestador = $prestador['nomePrestador'];
                                        $documento = $prestador['documento'];
                                        $tipoPessoa = $prestador['tipoPessoa'];

                                        if ($tipoPessoa == "PJ") {
                                            $tipoMask = "cnpj";
                                            $id_tipo_estabelecimento = 2;
                                        } elseif ($tipoPessoa == "PF") {
                                            $tipoMask = "cpf";
                                            $id_tipo_estabelecimento = 1;
                                        }
                                        $documento = str_replace("#", "0", Mask($tipoMask, $documento));

                                        if ($nomePrestador != "" && $nomePrestador != "U" && $nomePrestador != "N" && $documento != "00.000.000/0000-00") {
                                            $nomeFantasia = $prestador['nomeFantasia'];
                                            $subRegiao = $prestador['subRegiao'];
                                            $subRegiao = $prestador['subRegiao'];
                                            $emails = $prestador['email'];
                                            $site = $prestador['site'];





                                            $sqlEstabelecimento = "SELECT * FROM estabelecimento where documento like '$documento';";
                                            $selectEstabelecimento = mysqli_query($conect, $sqlEstabelecimento);
                                            if ($rsEstabelecimento = mysqli_fetch_assoc($selectEstabelecimento)) {
                                                $idEstabelecimento = $rsEstabelecimento['id'];
                                            } else {
                                                if ($salvarNovoEstabelecimento) {
                                                    $insert = "INSERT INTO estabelecimento 
                                                                (id_tipo_estabelecimento, documento, razao_social, nome_fantasia, email, site) VALUES 
                                                                ('$id_tipo_estabelecimento', '$documento', '$nomePrestador', '$nomeFantasia', '$emails', '$site');";
                                                    mysqli_query($conect, $insert);
                                                    $idEstabelecimento = mysqli_insert_id($conect);
                                                }
                                            }

                                            if ($logPrestadores) {
                                                echo "\n\nNOME PRESTADOR: $nomePrestador \n$tipoMask: $documento\n";
                                            }



                                            if ($idEstabelecimento > 0) {
                                                $enderecos = $prestador['enderecoRedeCredenciada'];
                                                foreach ($enderecos as $endereco) {
                                                    $tipoLogradouro = $endereco['tipoLogradouro'];
                                                    $numero = $endereco['numero'];
                                                    $logradouro = $endereco['logradouro'];
                                                    $complemento = $endereco['complemento'];
                                                    $municipio = $endereco['municipio'];
                                                    $nomeBairro = $endereco['bairroRedeCredenciada'];
                                                    $uf = $endereco['uf'];
                                                    $cep = Mask("cep", $endereco['cep']);

                                                    $ddd1 = $endereco['ddd1'];
                                                    $fone1 = $endereco['fone1'];

                                                    $telefone1 = "";
                                                    if ($ddd1 > 0) {
                                                        $telefone1 = Mask("telefone", $ddd1 . $fone1);
                                                    }

                                                    $ddd2 = $endereco['ddd2'];
                                                    $fone2 = $endereco['fone2'];
                                                    $telefone2 = "";
                                                    if ($ddd2 > 0) {
                                                        $telefone2 = Mask("telefone", $ddd2 . $fone2);
                                                    }

                                                    // Verificar se tem o bairro cadastrado no sistema, se nao tiver cadastrar
                                                    $sql = "SELECT * from tbl_bairro where nome like '$nomeBairro' and id_cidade = $idCidade;";
                                                    $result = mysqli_query($conect, $sql);
                                                    if (mysqli_num_rows($result) > 0) {
                                                        // echo "Já Existe => $nomeBairro\n";
                                                    } else {
                                                        $insert = "INSERT INTO tbl_bairro (nome, id_cidade) VALUES ('$nomeBairro', $idCidade);";
                                                        mysqli_query($conect, $insert);
                                                        echo "INSERIDO => $nomeBairro\n";
                                                    }

                                                    $sql = "SELECT 
                                                                e.id AS id_estado, c.id AS id_cidade, b.id AS id_bairro
                                                            FROM
                                                                tbl_estado AS e
                                                                    inner JOIN
                                                                tbl_cidade AS c ON c.estado = e.id
                                                                    inner JOIN
                                                                tbl_bairro AS b ON b.id_cidade = c.id
                                                            WHERE
                                                                e.uf LIKE '$estado'
                                                                    AND c.nome LIKE '$municipio'
                                                                    AND b.nome LIKE '$nomeBairro';";

                                                    $select = mysqli_query($conect, $sql);

                                                    if ($rsRegiao = mysqli_fetch_assoc($select)) {
                                                        $idEstado = $rsRegiao['id_estado'];
                                                        $idCidade = $rsRegiao['id_cidade'];
                                                        $idBairro = $rsRegiao['id_bairro'];
                                                    }

                                                    $sql = "SELECT * from tbl_estabelecimento_endereco where cep like '$cep' and id_estabelecimento = $idEstabelecimento;";
                                                    $result =  mysqli_query($conect, $sql);
                                                    if (mysqli_num_rows($result) == 0) {
                                                        $insertEndereco = "INSERT INTO tbl_estabelecimento_endereco
                                                                        (id_estabelecimento, cep, complemento, logradouro, numero, id_bairro, id_cidade, id_estado, telefone1, telefone2) VALUES 
                                                                        ($idEstabelecimento, '$cep', '$complemento', '$tipoLogradouro $logradouro', '$numero', $idBairro, $idCidade, $idEstado, '$telefone1', '$telefone2' );";

                                                        mysqli_query($conect, $insertEndereco);
                                                    }






                                                    if ($logPrestadores) {
                                                        echo "ENDEREÇO: $tipoLogradouro $logradouro \nCEP: $cep \nTELEFONE1: $telefone1 \nTELEFONE2: $telefone2\n";
                                                    }
                                                }

                                                // Cadastrar Rede Credenciada
                                                $sql = "SELECT * from rede_credenciada where id_rede = $idRede and id_estabelecimento = $idEstabelecimento and id_tipo_servico = $idTipoServico and id_especialidade = $idEspecialidade;";
                                                $result =  mysqli_query($conect, $sql);
                                                if (mysqli_num_rows($result) == 0) {
                                                    $insertRedeCredenciada = "INSERT INTO rede_credenciada
                                                                        (id_rede, id_estabelecimento, id_tipo_servico, id_especialidade) VALUES 
                                                                        ($idRede, $idEstabelecimento, $idTipoServico, $idEspecialidade);";

                                                    mysqli_query($conect, $insertRedeCredenciada);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
