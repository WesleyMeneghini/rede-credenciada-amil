<?php

$path = __DIR__;
require_once "$path/services/viacep.php";
require_once "$path/repository/EstadoCidadeBairroRepository.php";
require_once "$path/utils/mask.php";
require_once "$path/../includes/config.php";

$conect = conexaoMysqlTestDev();

$salvarNovaEspecialidade = true;
$salvarNovoEstabelecimento = true;

$curl = curl_init();

$url = "https://www.gndi.com.br/pesquisa-de-rede
?p_p_id=pesquisarede_WAR_pesquisarede100SNAPSHOT
&p_p_lifecycle=2
&p_p_state=normal
&p_p_mode=view
&p_p_cacheability=cacheLevelPage
&p_p_col_id=column-1
&p_p_col_pos=2
&p_p_col_count=5
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_acao=listaUnidade
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_unidadeNegocio=1
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_tipoRede=SAUDE
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_codigoPlano=735
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_cidade=9881
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_tipoRegiao=1
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_especialidade=
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_tipoAtendimento=2
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_idRede=
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_idLivreto=
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_flagExibe=
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_estado=SP
&_pesquisarede_WAR_pesquisarede100SNAPSHOT_nomePrestador=";

$url = preg_replace("/\r|\n/", "",  $url);
$url = str_replace(" ", "", $url);

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "",
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $listaUnidades = json_decode($response, true);

    foreach ($listaUnidades as $unidade) {

        $razaoSocial = $unidade['razaoSocial'];
        $nomeFantasia = $unidade['nome'];
        $site = $unidade['site'];
        $codigoPrestador = $unidade['codigoPrestador'];
        $codigoEndereco = $unidade['codigo'];
        $enderecoUnidade = $unidade['endereco'];

        $telefone = Mask("telefone",  $unidade['fone']);
        $documento = Mask("cnpj",  $unidade['cnpj']);


        echo "------------------------\n$razaoSocial \n$nomeFantasia \n$telefone \n$documento \n$site \n\n";

        $sqlEstabelecimento = "SELECT * FROM estabelecimento where documento like '$documento';";
        $selectEstabelecimento = mysqli_query($conect, $sqlEstabelecimento);
        if ($rsEstabelecimento = mysqli_fetch_assoc($selectEstabelecimento)) {
            $idEstabelecimento = $rsEstabelecimento['id'];
        } else {

            if ($salvarNovoEstabelecimento) {
                $insert = "INSERT INTO estabelecimento 
                (id_tipo_estabelecimento, documento, razao_social, nome_fantasia, site) VALUES 
                ('2', '$documento', '$razaoSocial', '$nomeFantasia', '$site');";

                mysqli_query($conect, $insert);
                $idEstabelecimento = mysqli_insert_id($conect);
            }
        }

        if ($idEstabelecimento > 0) {

            if ($nomeFantasia == "HOSPITAL SALVALUS" && true) {

                $arrayEnderecoCep = explode("CEP:", $enderecoUnidade);
                $arrayEnderecoLogradouro = explode(",", $enderecoUnidade);

                $cep = end($arrayEnderecoCep);
                $numeroEndereco = trim($arrayEnderecoLogradouro[1]);
                $complementoEndereco = trim(explode("-", $arrayEnderecoLogradouro[2])[0]);


                $resCep = getViaCep($cep);

                $rua = $resCep['logradouro'];
                $bairro = $resCep['bairro'];
                $cidade = $resCep['localidade'];
                $uf = $resCep['uf'];
                $cep = $resCep['cep'];

                // Verificar se tem o bairro cadastrado no sistema, se nao tiver cadastrar
                $sql = "SELECT * from tbl_bairro where nome like '$bairro' and id_cidade = 5270;";
                $result = mysqli_query($conect, $sql);
                if (mysqli_num_rows($result) > 0) {
                    // echo "Já Existe => $nomeBairro\n";
                } else {
                    $insert = "INSERT INTO tbl_bairro (nome, id_cidade) VALUES ('$bairro', 5270);";
                    mysqli_query($conect, $insert);
                    echo "INSERIDO => $nomeBairro\n";
                }

                if ($resEstadoCidadeBairro = getByEstadoCidadeBairro("SP", "SAO Paulo", $bairro)) {

                    $idEstado = $resEstadoCidadeBairro->getIdEstado();
                    $idCidade = $resEstadoCidadeBairro->getIdCidade();
                    $idBairro = $resEstadoCidadeBairro->getIdBairro();


                    $sql = "SELECT * from tbl_estabelecimento_endereco where cep like '$cep' and id_estabelecimento = $idEstabelecimento;";
                    $result =  mysqli_query($conect, $sql);
                    if (mysqli_num_rows($result) == 0) {
                        $insertEndereco = "INSERT INTO tbl_estabelecimento_endereco
                            (id_estabelecimento, cep, complemento, logradouro, numero, id_bairro, id_cidade, id_estado, telefone1) VALUES 
                            ($idEstabelecimento, '$cep', '$complementoEndereco', '$rua', '$numeroEndereco', $idBairro, $idCidade, $idEstado, '$telefone');";

                        mysqli_query($conect, $insertEndereco);
                    }


                    // Detalhes prestador

                    $curl = curl_init();

                    $urlDetalhesPrestador = "https://www.gndi.com.br/pesquisa-de-rede?p_p_id=pesquisarede_WAR_pesquisarede100SNAPSHOT
                    &p_p_lifecycle=2
                    &p_p_state=normal
                    &p_p_mode=view
                    &p_p_cacheability=cacheLevelPage
                    &p_p_col_id=column-1
                    &p_p_col_pos=2
                    &p_p_col_count=5
                    &_pesquisarede_WAR_pesquisarede100SNAPSHOT_acao=detalhePrestador
                    &_pesquisarede_WAR_pesquisarede100SNAPSHOT_codigoEndereco=$codigoEndereco
                    &_pesquisarede_WAR_pesquisarede100SNAPSHOT_codigoPrestador=$codigoPrestador
                    &_pesquisarede_WAR_pesquisarede100SNAPSHOT_unidadeNegocio=1
                    &_pesquisarede_WAR_pesquisarede100SNAPSHOT_codigoPlano=735";


                    $urlDetalhesPrestador = preg_replace("/\r|\n/", "",  $urlDetalhesPrestador);
                    $urlDetalhesPrestador = str_replace(" ", "", $urlDetalhesPrestador);

                    curl_setopt_array($curl, [
                        CURLOPT_URL => $urlDetalhesPrestador,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => "",
                    ]);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        echo "cURL Error #:" . $err;
                    } else {
                        $detalhesPrestador = json_decode($response, true);

                        $listEspecialidade = explode("#", $detalhesPrestador['listEspecialidade']);

                        var_dump($listEspecialidade);

                        foreach ($listEspecialidade as $especialidade) {

                            $sql = "SELECT * from tbl_especialidade where nome like '$especialidade';";
                            $select = mysqli_query($conect, $sql);

                            if ($rs = mysqli_fetch_assoc($select)) {
                                $idEspecialidade = $rs['id'];
                            } else {
                                if ($salvarNovaEspecialidade) {
                                    $insert = "INSERT INTO tbl_especialidade (nome) VALUES ('$especialidade');";
                                    mysqli_query($conect, $insert);
                                    $idEspecialidade = mysqli_insert_id($conect);
                                }
                            }

                            if ($idEspecialidade > 0) {
                            }
                        }
                    }
                } else {
                    echo "Nao achou o EstadoCidadeBairro: $bairro";
                }
            }
        }
    }
}
