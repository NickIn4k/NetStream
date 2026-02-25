<?php
    session_start();

    if (!isset($_SESSION['email']))
        die("Utente non loggato");

    $config = require __DIR__ . '/../assets/configurations/config.php';

    $email = $_SESSION['email'];

    echo "<script>console.log('Messaggio da PHP: Utente loggato con email: " . $email . "');</script>";

    $data = [
        'email' => $email,
        'secret' => $config['BET271_SECRET'], // Key comune a Bet271 per evitare Man-In-The-Middle attacks
        'value' => '250' //Punti da aggiungere
    ];

    $url = "http://{$config['BET271_IP']}/{$config['BET271_PATH']}/{$config['BET271_FILE']}";

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    echo $response;
?>
