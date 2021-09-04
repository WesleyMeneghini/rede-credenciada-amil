<?php

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://www.gndi.com.br/pesquisa-de-rede?p_p_id=pesquisarede_WAR_pesquisarede100SNAPSHOT&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&p_p_col_pos=2&p_p_col_count=5&_pesquisarede_WAR_pesquisarede100SNAPSHOT_acao=listaPlano",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => ""

]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $listaPlanos = json_decode($response, true);

    // echo sizeof($listaPlanos);

    foreach ($listaPlanos as $plano) {

        $codigoPlano = $plano['codigo'];
        $nomePlano = $plano['descricao'];
        $unidadeNegocio = $plano['unidadeNegocio'];

        if ($nomePlano == "ADVANCE 600") {
            echo "NOME PLANO: $nomePlano\n";
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
            &_pesquisarede_WAR_pesquisarede100SNAPSHOT_acao=listaTipoAtendimento
            &_pesquisarede_WAR_pesquisarede100SNAPSHOT_unidadeNegocio=$unidadeNegocio";

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
                $listaTipoAtendimento = json_decode($response, true);

                foreach ($listaTipoAtendimento as $tipoAtendimento) {

                    $codigoTipoAtendimento = $tipoAtendimento['codigo'];
                    $nomeTipoAtendimento = $tipoAtendimento['descricao'];
                    $complementoTipoAtendimento = $tipoAtendimento['complemento'];

                    if ($nomeTipoAtendimento == "PRONTO SOCORRO/PRONTO-ATENDIMENTO") {

                        echo "TIPO ATENDIMENTO: $nomeTipoAtendimento\n";

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
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_acao=listaEstado
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_tipoAtendimento=$codigoTipoAtendimento
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_codigoPlano=$codigoPlano
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_unidadeNegocio=$unidadeNegocio
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_especialidade=
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_idRede=
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_idLivreto=
                        &_pesquisarede_WAR_pesquisarede100SNAPSHOT_flagExibe=";

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
                            $listaEstados = json_decode($response, true);

                            foreach ($listaEstados as $estado) {

                                $codigoEstado = $estado['codigo'];
                                $uf = $estado['complemento'];
                                $tipoRegiaoEstado = $estado['tipoRegiao'];
                                $cidade = $estado['descricao'];

                                if ($uf == "SP" && $cidade == "SAO PAULO") {
                                    $curl = curl_init();

                                    curl_setopt_array($curl, [
                                        CURLOPT_URL => "https://www.gndi.com.br/pesquisa-de-rede?p_p_id=pesquisarede_WAR_pesquisarede100SNAPSHOT&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&p_p_col_pos=2&p_p_col_count=5&_pesquisarede_WAR_pesquisarede100SNAPSHOT_acao=listaUnidade&_pesquisarede_WAR_pesquisarede100SNAPSHOT_unidadeNegocio=1&_pesquisarede_WAR_pesquisarede100SNAPSHOT_tipoRede=SAUDE&_pesquisarede_WAR_pesquisarede100SNAPSHOT_codigoPlano=735&_pesquisarede_WAR_pesquisarede100SNAPSHOT_cidade=9881&_pesquisarede_WAR_pesquisarede100SNAPSHOT_tipoRegiao=1&_pesquisarede_WAR_pesquisarede100SNAPSHOT_especialidade=&_pesquisarede_WAR_pesquisarede100SNAPSHOT_tipoAtendimento=2&_pesquisarede_WAR_pesquisarede100SNAPSHOT_idRede=&_pesquisarede_WAR_pesquisarede100SNAPSHOT_idLivreto=&_pesquisarede_WAR_pesquisarede100SNAPSHOT_flagExibe=&_pesquisarede_WAR_pesquisarede100SNAPSHOT_estado=SP&_pesquisarede_WAR_pesquisarede100SNAPSHOT_nomePrestador=",
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => "",
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 30,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => "POST",
                                        CURLOPT_POSTFIELDS => "",
                                        CURLOPT_COOKIE => "JSESSIONID=5921DD339FB6504D149199F2A52F396E.worker5; COOKIE_SUPPORT=true; GUEST_LANGUAGE_ID=pt_BR; GNDI_CK=rd2o00000000000000000000ffffac150854o8080",
                                    ]);

                                    $response = curl_exec($curl);
                                    $err = curl_error($curl);

                                    curl_close($curl);

                                    if ($err) {
                                        echo "cURL Error #:" . $err;
                                    } else {
                                        echo $response;
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
