<?php

$path = __DIR__;
require_once "$path/services/viacep.php";
require_once "$path/repository/EstadoCidadeBairroRepository.php";
require_once "$path/utils/mask.php";

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
        $nomeFantasia= $unidade['nome'];
        $site = $unidade['site'];

        $telefone = Mask("telefone",  $unidade['fone']);
        $documento = Mask("cnpj",  $unidade['cnpj']);
        

        echo "------------------------\n$razaoSocial \n$nomeFantasia \n$telefone \n$documento \n$site \n\n";


        
        


        $enderecoUnidade = $unidade['endereco'];
        if ($nomeFantasia == "HOSPITAL SALVALUS" && false) {
            var_dump($unidade);








            $arrayEnderecoCep = explode("CEP:", $enderecoUnidade);
            $arrayEnderecoLogradouro = explode(",", $enderecoUnidade);

            $cep = end($arrayEnderecoCep);
            $numeroEndereco = $arrayEnderecoLogradouro[1];
            $complementoEndereco = $arrayEnderecoLogradouro[2];


            $resCep = getViaCep($cep);

            $rua = $resCep['logradouro'];
            $bairro = $resCep['bairro'];
            $cidade = $resCep['localidade'];
            $uf = $resCep['uf'];

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

                
            }else{
                echo "Nao achou o EstadoCidadeBairro: $bairro";
            }
        }


    }
}
