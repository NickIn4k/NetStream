<?php
    session_start();
    $IP = "10.68.90.203";
    $PathAlFile = "projects/bet_271/comms";
    $nomeFile = "updateBet271.php";

    if (!isset($_SESSION['email']))
        die("Utente non loggato");

    $email = $_SESSION['email'];
    $secret = "CHIAVE_SUPER_SEGRETA"; //Chiave condivisa con bet271

    echo "<script>console.log('Messaggio da PHP: Utente loggato con email: " . $email . "');</script>";

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
