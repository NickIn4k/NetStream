<?php
    session_start();
    $_SESSION['email'] = 'samk@gmail.com';
    $IP = "192.168.1.60";
    $PathAlFile = "Prova_ConnessioneLocale-main";
    $nomeFile = "updateBet271.php";

    if (!isset($_SESSION['email']))
        die("Utente non loggato");

    $email = $_SESSION['email'];
    $secret = "CHIAVE_SUPER_SEGRETA"; //Chiave condivisa con bet271

    $data = [
        'email' => $email,
        'secret' => $secret,
        'value' => '250' //Punti da aggiungere
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents("http://$IP/$PathAlFile/$nomeFile", false, $context);

    echo $response;
?>