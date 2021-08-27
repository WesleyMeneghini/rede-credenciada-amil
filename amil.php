<?php

require_once("./includes/config.php");
require_once("./rede-credenciada/repository/TipoPlanoRepository.php");
require_once("./rede-credenciada/model/TipoPlano.php");
require_once "utils/mask.php";

$conect = conexaoMysqlTestDev();


$logTipoServicos = true;
$logProcedimentos = true;
$logPrestadores = true;



$salvarNovoTipoServico = false;
$salvarNovoProcedimento = false;
$salvarNovoPrestadore = false;



$estado = "SP";
$municipio = "SAO PAULO";
$bairro = "TODOS OS BAIRROS";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://www.amil.com.br/institucional/api/InstitucionalMiddleware/RedeCredenciadaPlanos?operadora=SAUDE",
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


    $res = json_decode($response, true);

    $planosEncontrados = array_filter($res, function ($e) {
        if ($e['tipo'] == 'PLANO') {
            if (getByNameTipoPlano($e['nomePlanoCartao'], [2, 5, 8])) return true;
        }
        return false;
    });

    foreach ($planosEncontrados as $plano) {
        $codigoRede = $plano['codigoRede'];
        $contexto = $plano['contexto'];
        $nomePlano = $plano['nomePlanoCartao'];
        $operadora = $plano['operadora'];

        echo "\n-------------------------------------------------------------\n";
        echo "NOME PLANO: $nomePlano ($codigoRede)\n\n";

        // obter os tipos de serviços

        $curl = curl_init();

        $url =  str_replace(" ", "%20", "https://www.amil.com.br/institucional/api/InstitucionalMiddleware/RedeCredenciadaTipoServico/$codigoRede/SAUDE/$estado/$municipio/$bairro/");
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

            $resTipoServico = json_decode($response, true);

            foreach ($resTipoServico as $tipoServico) {
                $idEspecialidade = 0;
                $nomeTipoServico = $tipoServico['NIVEL1_CAPITULO'];

                if ($logTipoServicos) {
                    echo "TIPO SERVIÇO: $nomeTipoServico \n";
                }

                $sql = "SELECT * from tbl_especialidade where nome like '$nomeTipoServico';";
                $select = mysqli_query($conect, $sql);
                if ($rs = mysqli_fetch_assoc($select)) {
                    $idEspecialidade = $rs['id'];
                } else {
                    if ($salvarNovoTipoServico) {
                        $insert = "INSERT INTO tbl_especialidade (nome) VALUES ('$nomeTipoServico');";
                        mysqli_query($conect, $insert);
                        $idEspecialidade = mysqli_insert_id($conect);
                    }
                }

                if ($idEspecialidade > 0) {

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

                        $resProcedimentos = json_decode($response, true);

                        foreach ($resProcedimentos as $procedimento) {
                            $idProcedimento = 0;
                            $nomeProcedimento = $procedimento['NIVEL2_ELEMENTODIVULGACAO'];

                            if ($logProcedimentos) {
                                echo "\nPROCEDIMENTO: $nomeProcedimento\n";
                            }

                            $sql = "SELECT * from tbl_procedimento where titulo like '$nomeProcedimento';";
                            $select = mysqli_query($conect, $sql);

                            if ($rs = mysqli_fetch_assoc($select)) {
                                $idProcedimento = $rs['id'];
                            } else {
                                if ($salvarNovoProcedimento) {
                                    $insert = "INSERT INTO tbl_procedimento (titulo) VALUES ('$nomeProcedimento');";
                                    mysqli_query($conect, $insert);
                                    $idProcedimento = mysqli_insert_id($conect);
                                }
                            }

                            if ($idProcedimento > 0) {
                                // ULTIMA ETAPA
                                // Pegar os resultados da Rede Credenciada, Retorna os prestadores

                                $dados = [
                                    "codigoRede" => $codigoRede,
                                    "uf" => $estado,
                                    "municipio" => $municipio,
                                    "bairro" => $bairro,
                                    "especialidade" => $nomeProcedimento,
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

                                        $nomePrestador = $prestador['nomePrestador'];
                                        $nomeFantasia = $prestador['nomeFantasia'];
                                        $subRegiao = $prestador['subRegiao'];
                                        $subRegiao = $prestador['subRegiao'];
                                        $emails = $prestador['email'];

                                        $documento = $prestador['documento'];
                                        $tipoPessoa = $prestador['tipoPessoa'];

                                        if ($tipoPessoa == "PJ") {
                                            $tipoMask = "cnpj";
                                        } elseif ($tipoPessoa == "PF") {
                                            $tipoMask = "cpf";
                                        }
                                        $documento = str_replace("#", "0", Mask($tipoMask, $documento));

                                        $site = $prestador['site'];

                                        if ($logPrestadores) {
                                            echo "\n\nNOME PRESTADOR: $nomePrestador \n$tipoMask: $documento\n";
                                        }

                                        $enderecos = $prestador['enderecoRedeCredenciada'];
                                        foreach ($enderecos as $endereco) {
                                            $tipoLogradouro = $endereco['tipoLogradouro'];
                                            $numero = $endereco['numero'];
                                            $logradouro = $endereco['logradouro'];
                                            $complemento = $endereco['complemento'];
                                            $municipio = $endereco['municipio'];
                                            $bairroRedeCredenciada = $endereco['bairroRedeCredenciada'];
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

                                            if ($logPrestadores) {
                                                echo "ENDEREÇO: $tipoLogradouro $logradouro \nCEP: $cep \nTELEFONE1: $telefone1 \nTELEFONE2: $telefone2\n";
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
