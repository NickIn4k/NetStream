<?php
    $config = require __DIR__ . '/../assets/configurations/config.php'; //Chiave condivisa con Bet271

    if ($_POST['secret'] !== $config['BET271_SECRET']) {
        http_response_code(403);
        die("Accesso negato");
    }

    $email = $_POST['email'];
    $value = $_POST['value'];

    $config = require __DIR__ . '/../assets/configurations/configDB.php';
    $conn = new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);

    //controlla se esiste l'utente con la stessa email
    $stmt = $conn->prepare("SELECT idUtente, bet271Points FROM utente WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0)
        die("Utente non presente su NetStream");

    $user = $result->fetch_assoc();

    //aggiorna la tabella
    $newBet271Points = $user['bet271Points'] + $value;
    $update = $conn->prepare("UPDATE utente SET bet271Points = ? WHERE idUtente = ?");
    $update->bind_param("si", $newBet271Points, $user['idUtente']);
    $update->execute();

    echo "OK: DB NetStream aggiornato";
?>