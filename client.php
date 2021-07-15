<?php

function apiCall($user, $date_time)
{

    $time_variables = [
        'year' => 'Y',
        'month' => 'm',
        'day' => 'd',
        'hour' => 'H',
        'minute' => 'i',
        'second' => 's',
    ];

    foreach ($time_variables as $time_variable => $date_time_tag) {
        $$time_variable = $date_time->format($date_time_tag);
    }

    echo "Gerando string base...\n";

    // Generates TOTP-Token
    $pre_hash_token = "{$day}-DHW-{$month}-ABH-" . ((int)($second / 30)) . "-ADC-{$year}-VFG-{$hour}-REW-{$minute}";

    echo "String base: $pre_hash_token\n\n";

    echo "Gerando TOTP...\n";

    $hashed_token = hash('sha512', $pre_hash_token);

    echo "TOTP: $hashed_token\n\n";

    echo "Criando requisição...\n\n";

    // Sends packet    
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "http://localhost/api.php?user=$user&token=$hashed_token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
    ]);

    $response = curl_exec($curl);

    $http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $err = curl_error($curl);

    curl_close($curl);

    return ['status' => $http_status_code, 'response' => $response];
}

// Syncs clock
// http://worldtimeapi.org/api/timezone/America/Sao_Paulo
$world_time_response = file_get_contents('http://worldtimeapi.org/api/timezone/America/Sao_Paulo');

$world_time = json_decode($world_time_response, true);

$date_time = new DateTime();

$date_time->setTimezone(new DateTimeZone("America/Sao_Paulo"));

$date_time->setTimestamp($world_time['unixtime']);

$user_id = null;

do {
    $user = readline("Insira um id usuário: ");

    try {
        $user_id = (int)$user;
    } catch (\Throwable $th) {
        echo "Entrada inválida\n";
    }
} while (is_null($user_id));

$api_call = apiCall($user_id, $date_time);

$http_status_code = $api_call['status'];

$response = $api_call['response'];

// Displays returned data (or error)

echo "Resposta - Status $http_status_code\n";
switch ($http_status_code) {
    case 200:
        print_r(json_decode($response, true));
        break;
    case 404:
        echo "Usuário não encontrado\n";
        break;
    case 403:
        echo "Acesso negado, tentando novamente...\n";
        $api_call = apiCall($user_id, $date_time);

        $http_status_code = $api_call['status'];

        $response = $api_call['response'];

        echo "Resposta - Status $http_status_code\n";
        switch ($http_status_code) {
            case 200:
                print_r(json_decode($response, true));
                break;
            case 404:
                echo "Usuário não encontrado\n";
                break;
            case 403:
                echo "Acesso negado\n";

                break;
        }

        break;
}


return;
