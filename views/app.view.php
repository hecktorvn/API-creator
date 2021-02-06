<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>API - Exemplos</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
    <div class="container pt-2">
        <h3 class="p-3 border rounded text-center bg-danger text-white">Opa, nenhum parâmetro foi informado!</h3>
        <h5 class="p-4 border-bottom mb-3 border-top">Parâmetros</h5>
        <div class="form pl-5 mb-5">
            <div class="col-12 mb-3">
                <h5 class="text-danger">*key <small>- Chave de acesso</small></h5>
                <ul>
                    <li class="text-danger">É obrigatório informar a chave de acesso para poder utilizar a API.</li>
                </ul>
            </div>

            <div class="col-12 mb-3">
                <h5 class="text-danger">*usuario <small>- Usuário de acesso</small></h5>
                <ul>
                    <li class="text-danger">É obrigatório informar o usuário de acesso (CNPJ ou CPF).</li>
                    <lt class="text-danger">Formato: Númerico</lt>
                </ul>
            </div>

            <div class="col-12 mb-3">
                <h5 class="text-danger">*senha <small>- Senha de acesso</small></h5>
                <ul>
                    <li class="text-danger">É obrigatório informar a senha de acesso.</li>
                </ul>
            </div>

            <div class="col-12 mb-3">
                <h5>coleta <small>- Cidade de coleta</small></h5>
            </div>

            <div class="col-12 mb-3">
                <h5>entrega <small>- Cidade de entrega</small></h5>
            </div>

            <div class="col-12">
                <h5>periodo <small>- Determina qual o filtro de data deve ser aplicado caso "dtini" e "dtfim" sejam preenchidos.</small></h5>
                <ul>
                    <li>entrega</li>
                    <li>previsao</li>
                    <li>emissao <small><b>- Default</b></small></li>
                </ul>
            </div>

            <div class="col-12">
                <h5>dtini <small>- Especifica a data inicial dos CTe's que deseja obter.</small></h5>
                <ul>
                    <li>{{ date('dmY') }} <small><b>- Default</b></small></li>
                    <lt>Formato : ddmmaaaa</lt>
                </ul>
            </div>

            <div class="col-12">
                <h5>dtfim <small>- Especifica a data final dos CTe's que deseja obter.</small></h5>
                <ul>
                    <li>{{ date('dmY') }} <small><b>- Default</b></small></li>
                    <lt>Formato : ddmmaaaa</lt>
                </ul>
            </div>

            <div class="col-12">
                <h5>tipo_cliente <small class="text-danger">- Especifica qual o tipo de cliente do CTe.</small></h5>
                <ul>
                    <li>remetente</li>
                    <li>destinatario</li>
                    <li>consignatario</li>
                    <li>todos <small><b>- Default</b></small></li>
                </ul>
            </div>
            
            <div class="col-12">
                <h5>situacao <small>- Situação do CTe e do Manifesto.</small></h5>
                <ul>
                    <li>manifesto</li>
                    <li>romaneio</li>
                    <li>entregue</li>
                    <li>origem</li>
                    <li>destino</li>
                    <li>retida</li>
                    <li>ativos <small><b>- Default</b></small></li>
                    <li>cancelado</li>
                </ul>
            </div>
        </div>


        <h5 class="p-4 border-bottom mb-3 border-top">Exemplos</h5>
        <div class="form pl-5 pb-5" style="word-break: break-all;">
            @php
                $dt_ini = date('dmY', strtotime('-10 days'));
                $dt_fim = date('dmY');
                $key = '{CHAVE DE ACESSO}';

                if( isset($_GET['key']) ) {
                    $key = urlencode($_GET['key']);
                    $link = url('extrato') . '?dtini=' . $dt_ini . '&dtfim=' . $dt_fim;
                    $link2 = $link . '&cliente={NOME DO CLIENTE}&tipo_cliente=destinatario';
                    
                    $link .= '&key=' . $key;
                    $link2 .= '&key=' . $key;
                } else {
                    $link = url('extrato') . '?key=' . $key . '&dtini=' . $dt_ini . '&dtfim=' . $dt_fim;
                    $link2 = $link . '&cliente={NOME DO CLIENTE}&tipo_cliente=destinatario';
                }

            @endphp
            <a href="{{ $link }}">
                {{ $link }}
            </a>
            <br><br>
            <a>
                {{ $link2 }} 
            </a>
        </div>

        <h5 class="p-4 border-bottom mb-3 border-top">Requisição via <b>POST</b></h5>
        <div class="form pl-5 pb-5 mb-5" style="word-break: break-all;">
            <code>
            > POST /API/extrato HTTP/1.1<br>
            > Host: unicanet.com.br<br>
            > Accept: application/json<br>
            > Authorization: APP-KEY {{ $key }}<br>
            > Content-Length: 93<br>
            <br>
            {<br>
            &nbsp;&nbsp;&nbsp;&nbsp; "dtini": "{{ date('dmY') }}",<br>
            &nbsp;&nbsp;&nbsp;&nbsp; "dtfim": "{{ date('dmY') }}",<br>
            &nbsp;&nbsp;&nbsp;&nbsp; "usuario": "00000000000",<br>
            &nbsp;&nbsp;&nbsp;&nbsp; "senha": "11111111111"<br>
            }
            </code>
        </div>
    </div>
</body>
</html>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>