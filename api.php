<?php
$mock_data = [
    0 => [
        'name' => 'Fulano',
        'birthday' => '1997-09-07'
    ],
    1 => [
        'name' => 'Ciclano',
        'birthday' => '1996-10-08'
    ],
    2 => [
        'name' => 'Beltrano',
        'birthday' => '1995-11-09'
    ],
    3 => [
        'name' => 'Loki',
        'birthday' => '1610-04-01'
    ],
];

// Syncs clock
// http://worldtimeapi.org/api/timezone/America/Sao_Paulo
$world_time_response = file_get_contents('http://worldtimeapi.org/api/timezone/America/Sao_Paulo');

$world_time = json_decode($world_time_response, true);

$date_time = new DateTime();

$date_time->setTimezone(new DateTimeZone("America/Sao_Paulo"));

$date_time->setTimestamp($world_time['unixtime']);

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


if (isset($_GET['token'])) {
    // Gets token
    $token = $_GET['token'];

    // Generates TOTP-Token for validation
    $pre_hash_token = "{$day}-DHW-{$month}-ABH-".((int)($second / 30))."-ADC-{$year}-VFG-{$hour}-REW-{$minute}";

    $hashed_verification_token = hash('sha512', $pre_hash_token);    

    // Checks TOTP-Token and returns or not data
    if ($token == $hashed_verification_token) {
        $user = isset($_GET['user']) ? $_GET['user'] : null;
        if (isset($mock_data[$user])) {
            $response = $mock_data[$user];

            $response['received_token'] = $token;
            $response['verification_token'] = $hashed_verification_token;

            echo json_encode($response);
            return;
        } else {
            http_response_code(404);
            return;
        }
    } else {
        http_response_code(403);
        return;
    }
}else{
    http_response_code(403);
        return;
}
